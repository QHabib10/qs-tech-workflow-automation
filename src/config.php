<?php

function load_env_from_file(string $envPath): void
{
    if (!file_exists($envPath)) {
        return;
    }
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $trimmed = ltrim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }
        $key = trim($parts[0]);
        $value = trim($parts[1]);
        
        $value = preg_replace('/^\"|\"$/', '', $value);
        $value = preg_replace("/^'|'$/", '', $value);
    
        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

// Load .env 
$projectRoot = dirname(__DIR__);
$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';
load_env_from_file($envFile);

// Read DB configuration
$db_host = getenv('DB_HOST') ?: '127.0.0.1';
$db_user = getenv('DB_USER') ?: '';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: '';
$db_port = (int)(getenv('DB_PORT') ?: '3306');

// Establish MySQLi connection
$conn = @new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
if ($conn->connect_errno) {

    http_response_code(500);
    die('Database connection failed');
}

?>
