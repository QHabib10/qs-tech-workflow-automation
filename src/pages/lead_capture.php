<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php'; 
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/KeywordService.php';
require_once __DIR__ . '/../services/JiraService.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $budget  = $_POST['budget'] ?? 0;
    $message = trim($_POST['message']);

    // Insert Data into Database using prepared statement
    if (isset($name) && isset($email) && isset($phone) && isset($budget) && isset($message))
    {
        $stmt = $conn->prepare("INSERT INTO leads (name, email, phone, budget, message, status) VALUES (?, ?, ?, ?, ?, 'new')");
        $stmt->bind_param('sssds', $name, $email, $phone, $budget, $message);
    }

    if (isset($stmt)) {
        if ($stmt->execute()) {
            
            $leadId = $conn->insert_id;
            
            // Detect keywords and send auto-reply email
            $keywords = KeywordService::detect($message);
            $emailService = new EmailService($conn);
            $emailSent = $emailService->sendAutoReply($name, $email, $keywords, $leadId);
            
            // Create Jira issue
            $jiraService = new JiraService($conn);
            $leadData = [
                'id' => $leadId,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'budget' => $budget,
                'message' => $message
            ];
            $jiraResult = $jiraService->createIssue($leadData);
            
            // Update lead with Jira issue key if created successfully
            if ($jiraResult && isset($jiraResult['key'])) {
                $updateStmt = $conn->prepare("UPDATE leads SET jira_issue_key = ? WHERE id = ?");
                $updateStmt->bind_param('si', $jiraResult['key'], $leadId);
                $updateStmt->execute();
                $updateStmt->close();
            }
            
            // Redirect with lead, email, and Jira status
            $redirectUrl = '/src/pages/lead_capture.php?success=1';
            if ($emailSent) {
                $redirectUrl .= '&email=1';
            } else {
                $redirectUrl .= '&email=0';
            }
            if ($jiraResult && isset($jiraResult['key'])) {
                $redirectUrl .= '&jira=1';
            } else {
                $redirectUrl .= '&jira=0';
            }
            
            header('Location: ' . $redirectUrl, true, 303);
            exit;
        } else {
            $error = "Failed to add lead.";
        }
        $stmt->close();
    }

}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Lead Capture - QS Tech</title>
    <link rel="stylesheet" href="/assets/sidebar.css" />
    <link rel="stylesheet" href="/assets/lead_form.css" />
    <script src="/assets/alert.js"></script>
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
        <main class="content">
            <?php 
            // Lead capture success alert
            if (isset($_GET['success'])): 
                $type = 'success';
                $message = 'Lead added successfully.';
                include __DIR__ . '/../components/alert.php';
            endif; 
            
            // Email status alerts
            if (isset($_GET['email'])): 
                if ($_GET['email'] == '1'): 
                    $type = 'success';
                    $message = 'Auto-reply email sent successfully.';
                    $duration = 3000; 
                    include __DIR__ . '/../components/alert.php';
                elseif ($_GET['email'] == '0'): 
                    $type = 'warning';
                    $message = 'Lead saved, but auto-reply email failed to send.';
                    $duration = 4000; 
                    include __DIR__ . '/../components/alert.php';
                endif;
            endif;
            
            // Jira status alerts
            if (isset($_GET['jira'])): 
                if ($_GET['jira'] == '1'): 
                    $type = 'success';
                    $message = 'Jira issue created successfully.';
                    $duration = 3500;
                    include __DIR__ . '/../components/alert.php';
                elseif ($_GET['jira'] == '0'): 
                    $type = 'warning';
                    $message = 'Lead saved, but Jira issue creation failed.';
                    $duration = 4000;
                    include __DIR__ . '/../components/alert.php';
                endif;
            endif; 
            ?>
            <?php include __DIR__ . '/../components/lead_form.php'; ?>
        </main>
    </div>
    <script>
      (function(){
        var form = document.querySelector('.form');
        var btn = document.querySelector('.btn');
        if(form && btn)
        {
          form.addEventListener('submit', function(){
            btn.classList.add('is-loading');
            btn.setAttribute('disabled','disabled');
          });
        }
      })();
    </script>
</body>
</html>

