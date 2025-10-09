<?php
/**
 * Alert Component
 * Usage: include with $type, $message, $autoHide (optional)
 */

// Default values
$type = $type ?? 'info';
$message = $message ?? '';
$autoHide = $autoHide ?? true;
$duration = $duration ?? 2500; // milliseconds

// Alert type classes
$alertClasses = [
    'success' => 'alert-success',
    'error' => 'alert-error', 
    'warning' => 'alert-warning',
    'info' => 'alert-info'
];

$alertClass = $alertClasses[$type] ?? 'alert-info';
?>

<div class="alert <?= $alertClass ?>" data-auto-hide="<?= $autoHide ? 'true' : 'false' ?>" data-duration="<?= $duration ?>">
    <?= htmlspecialchars($message) ?>
</div>
