<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/services/JiraService.php';
require_once __DIR__ . '/../../src/services/EmailService.php';

$jira = new JiraService($conn);
$email = new EmailService($conn);


$projectKey = getenv('JIRA_PROJECT_KEY');

$jql = sprintf("project = %s AND status = Done AND updated >= -2h ORDER BY updated DESC", $projectKey);

try {
    
    $client = new GuzzleHttp\Client([
        'base_uri' => getenv('JIRA_URL'),
        'auth' => [getenv('JIRA_EMAIL'), getenv('JIRA_API_TOKEN')],
        'headers' => [ 'Accept' => 'application/json' ]
    ]);

    $response = $client->get('/rest/api/3/search', [
        'query' => [
            'jql' => $jql,
            'maxResults' => 100,
            'fields' => 'summary,status'
        ]
    ]);
    $data = json_decode($response->getBody(), true);
    $issues = $data['issues'] ?? [];
} catch (Exception $e) {
   
    exit(0);
}

foreach ($issues as $issue) {
    $issueKey = $issue['key'] ?? null;
    if (!$issueKey) { continue; }

    // Find lead by Jira key
    $stmt = $conn->prepare("SELECT id, name, email FROM leads WHERE jira_issue_key = ? LIMIT 1");
    $stmt->bind_param('s', $issueKey);
    $stmt->execute();
    $res = $stmt->get_result();
    $lead = $res->fetch_assoc();
    $stmt->close();
    if (!$lead) { continue; }

    $leadId = (int)$lead['id'];

    // Check audit_log to avoid duplicate emails
    $chk = $conn->prepare("SELECT id FROM audit_log WHERE action = 'completion_email_sent' AND entity = 'leads' AND entity_id = ? LIMIT 1");
    $chk->bind_param('i', $leadId);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        $chk->close();
        continue; 
    }
    $chk->close();

    // Send completion email
    $ok = $email->sendCompletionEmail($lead['name'], $lead['email'], $issueKey);

    // Log to audit_log
    $action = $ok ? 'completion_email_sent' : 'completion_email_failed';
    $details = json_encode(['issue_key' => $issueKey, 'email' => $lead['email']]);
    $ins = $conn->prepare("INSERT INTO audit_log (action, entity, entity_id, details) VALUES (?, 'leads', ?, ?)");
    $ins->bind_param('sis', $action, $leadId, $details);
    $ins->execute();
    $ins->close();
}

exit(0);

