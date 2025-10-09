<?php
?>
<button class="sb-toggle-btn" onclick="(function(){var el=document.querySelector('.sidebar'); if(el){ el.classList.toggle('is-open'); }})()">Menu</button>
<aside class="sidebar">
    <h1 class="brand">QS Tech</h1>
    <nav class="nav">
        <?php $path = $_SERVER['REQUEST_URI'] ?? ''; ?>
        <a href="/src/pages/lead_capture.php" class="<?= strpos($path, '/src/pages/lead_capture.php') !== false ? 'active' : '' ?>">Add Lead</a>
        <a href="/src/pages/calendar.php" class="<?= strpos($path, '/src/pages/calendar.php') !== false ? 'active' : '' ?>">Calendar</a>
        <a href="/src/pages/inventory.php" class="<?= strpos($path, '/src/pages/inventory.php') !== false ? 'active' : '' ?>">Inventory and Alerts</a>
        <a href="/src/pages/dashboard.php" class="<?= strpos($path, '/src/pages/dashboard.php') !== false ? 'active' : '' ?>">Dashboard</a>
    </nav>
</aside>

