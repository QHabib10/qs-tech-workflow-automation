<?php
?>
<aside class="sidebar">
    <h1 class="brand">QS Tech</h1>
    <nav class="nav">
        <?php $path = $_SERVER['REQUEST_URI'] ?? ''; ?>
        <a href="/src/pages/lead_capture.php" class="<?= strpos($path, '/src/pages/lead_capture.php') !== false ? 'active' : '' ?>">Add Lead</a>
        <a href="/src/pages/calendar.php" class="<?= strpos($path, '/src/pages/calendar.php') !== false ? 'active' : '' ?>">Calendar</a>
        <a href="/src/pages/inventory.php" class="<?= strpos($path, '/src/pages/inventory.php') !== false ? 'active' : '' ?>">Inventory and Alerts</a>
        <a href="#">Dashboard</a>
        <a href="#">Settings</a>
    </nav>
</aside>

