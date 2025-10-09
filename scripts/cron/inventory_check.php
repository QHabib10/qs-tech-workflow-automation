<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../src/config.php';
require_once __DIR__ . '/../../src/services/EmailService.php';

// Fetch low stock items
$lowItems = [];
$sql = "SELECT id, name, qty, threshold FROM inventory WHERE qty < threshold";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $lowItems[] = $row;
    }
    $result->close();
}

$email = new EmailService($conn);

foreach ($lowItems as $item) {
    $itemName = $item['name'];
    $qty = (int)$item['qty'];
    $threshold = (int)$item['threshold'];

    // Insert alert if not already open for this item and qty
    $check = $conn->prepare("SELECT id FROM alerts WHERE item_name = ? AND status = 'open' LIMIT 1");
    $check->bind_param('s', $itemName);
    $check->execute();
    $check->store_result();
    $hasOpen = $check->num_rows > 0;
    $check->close();

    if (!$hasOpen) {
        $ins = $conn->prepare("INSERT INTO alerts (item_name, item_qty, threshold, status) VALUES (?, ?, ?, 'open')");
        $ins->bind_param('sii', $itemName, $qty, $threshold);
        $ins->execute();
        $ins->close();
    }

    // Send notification
    $email->sendLowStockNotification($itemName, $qty, $threshold);

    // Audit log with entity_id (inventory id)
    $action = 'low_stock_detected';
    $details = json_encode(['item' => $itemName, 'qty' => $qty, 'threshold' => $threshold]);
    $stmt = $conn->prepare("INSERT INTO audit_log (action, entity, entity_id, details) VALUES (?, 'inventory', ?, ?)");
    $stmt->bind_param('sis', $action, $item['id'], $details);
    $stmt->execute();
    $stmt->close();
}

// Resolve alerts automatically when stock recovered
$resolveSql = "SELECT a.id AS alert_id, i.id AS inventory_id, a.item_name, i.qty, i.threshold
               FROM alerts a 
               JOIN inventory i ON i.name = a.item_name
               WHERE a.status = 'open' AND i.qty >= i.threshold";
if ($res = $conn->query($resolveSql)) {
    while ($row = $res->fetch_assoc()) {
        
        // Mark alert resolved
        $upd = $conn->prepare("UPDATE alerts SET status='resolved', resolved_at = NOW() WHERE id = ?");
        $upd->bind_param('i', $row['alert_id']);
        $upd->execute();
        $upd->close();

        // Audit log
        $action = 'low_stock_resolved';
        $details = json_encode(['item' => $row['item_name'], 'qty' => (int)$row['qty'], 'threshold' => (int)$row['threshold']]);
        $stmt = $conn->prepare("INSERT INTO audit_log (action, entity, entity_id, details) VALUES (?, 'inventory', ?, ?)");
        $stmt->bind_param('sis', $action, $row['inventory_id'], $details);
        $stmt->execute();
        $stmt->close();
    }
    $res->close();
}

exit(0);
