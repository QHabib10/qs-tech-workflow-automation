<?php require __DIR__ . '/../config.php'; 

// Handle form submission
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
            // PRG: redirect to avoid resubmission on refresh
            header('Location: /src/pages/lead_capture.php?success=1', true, 303);
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
</head>
<body>
    <div class="layout">
        <?php include __DIR__ . '/../components/sidebar.php'; ?>
        <main class="content">
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Lead added successfully.</div>
            <?php endif; ?>
            <?php include __DIR__ . '/../components/lead_form.php'; ?>
        </main>
    </div>
    <script>
      (function(){
        var form = document.querySelector('.form');
        var btn = document.querySelector('.btn');
        if(form && btn){
          form.addEventListener('submit', function(){
            btn.classList.add('is-loading');
            btn.setAttribute('disabled','disabled');
          });
        }
        var success = document.querySelector('.alert.alert-success');
        if (success) {
          setTimeout(function(){ success.style.display = 'none'; }, 2500);
          try {
            var url = new URL(window.location.href);
            if (url.searchParams.has('success')) {
              url.searchParams.delete('success');
              window.history.replaceState({}, '', url.toString());
            }
          } catch(e) {}
        }
      })();
    </script>
</body>
</html>

