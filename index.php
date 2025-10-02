<?php
// Include DB config
require __DIR__ . '/src/config.php';

// Show homepage
echo "<h1>QS Tech Workflow Automation - Home Page</h1>";

// If DB connection is live
if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
    echo "<p style='color:green;'>Database connection successful!</p>";
} else {
    echo "<p style='color:red;'>Database connection failed!</p>";
}
?>
