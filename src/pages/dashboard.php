<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php'; 
require_once __DIR__ . '/../services/JiraService.php';

$jira = new JiraService($conn);

// 1) Open Jira issues count 
$openCount = 0; $completedThisWeek = 0; $overdueCount = 0;
try {
    $client = new GuzzleHttp\Client([
        'base_uri' => getenv('JIRA_URL'),
        'auth' => [getenv('JIRA_EMAIL'), getenv('JIRA_API_TOKEN')],
        'headers' => [ 'Accept' => 'application/json' ]
    ]);
    $projectKey = getenv('JIRA_PROJECT_KEY');

    $respOpen = $client->get('/rest/api/3/search', [ 'query' => [
        'jql' => sprintf("project = %s AND statusCategory != Done", $projectKey),
        'maxResults' => 0
    ]]);
    $openCount = json_decode($respOpen->getBody(), true)['total'] ?? 0;

    // 2) Overdue items (dueDate < now and not done)
    $respOverdue = $client->get('/rest/api/3/search', [ 'query' => [
        'jql' => sprintf("project = %s AND duedate < now() AND statusCategory != Done", $projectKey),
        'maxResults' => 0
    ]]);
    $overdueCount = json_decode($respOverdue->getBody(), true)['total'] ?? 0;

    // 5) Completed this week
    $respDoneWeek = $client->get('/rest/api/3/search', [ 'query' => [
        'jql' => sprintf("project = %s AND status = Done AND updated >= startOfWeek()", $projectKey),
        'maxResults' => 0
    ]]);
    $completedThisWeek = json_decode($respDoneWeek->getBody(), true)['total'] ?? 0;
} catch (Exception $e) {
    
}

// 3) Low-stock count
$lowStockCount = 0;
if ($res = $conn->query("SELECT COUNT(*) AS c FROM inventory WHERE qty < threshold")) {
    $lowStockCount = (int)($res->fetch_assoc()['c'] ?? 0); $res->close();
}

// 4) Average lead budget
$avgBudget = 0;
if ($res = $conn->query("SELECT AVG(budget) AS avg_b FROM leads WHERE budget IS NOT NULL")) {
    $avgBudget = (float)($res->fetch_assoc()['avg_b'] ?? 0); $res->close();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Dashboard - QS Tech</title>
  <link rel="stylesheet" href="/assets/sidebar.css" />
  <link rel="stylesheet" href="/assets/dashboard.css" />
</head>
<body>
  <div class="layout">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="content">
      <div class="wrap">
        <h3 class="page-title">KPI Dashboard</h3>
        <div class="tiles">
          <div class="tile">
            <h4>Open Jira Issues</h4>
            <div class="value value-blue"><?= (int)$openCount ?></div>
          </div>
          <div class="tile">
            <h4>Overdue Items</h4>
            <div class="value <?= ((int)$overdueCount === 0) ? 'value-green' : 'value-red' ?>"><?= (int)$overdueCount ?></div>
          </div>
          <div class="tile">
            <h4>Low-stock Items</h4>
            <div class="value <?= ((int)$lowStockCount === 0) ? 'value-green' : 'value-red' ?>"><?= (int)$lowStockCount ?></div>
          </div>
          <div class="tile">
            <h4>Average Lead Budget</h4>
            <div class="value value-blue">$<?= number_format($avgBudget, 0) ?></div>
          </div>
          <div class="tile">
            <h4>Completed this Week</h4>
            <div class="value value-green"><?= (int)$completedThisWeek ?></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</body>
</html>


