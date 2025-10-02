<?php

$private_env = '/path/to/env.php/in/cpanel';

if (file_exists($private_env)) 
{
    include $private_env;
} else {
    die("Environment file not found.");
}

// Get DB credentials from environment variables
$db_host = getenv("DB_HOST");
$db_user = getenv("DB_USER");
$db_pass = getenv("DB_PASS");
$db_name = getenv("DB_NAME");

// Create DB connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
} 
?>
