<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php'; 

// Filters
$status = $_GET['status'] ?? 'all'; // all | low

$where = '';
if ($status === 'low') {
    $where = 'WHERE qty < threshold';
}

$items = [];
$sql = "SELECT id, name, qty, threshold, last_updated FROM inventory {$where} ORDER BY name ASC";
if ($res = $conn->query($sql)) {
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    $res->close();
}

// Alerts data (below table)
$alertsStatus = $_GET['alerts_status'] ?? 'all'; 
$alertsWhere = '';
if ($alertsStatus === 'open') { $alertsWhere = "WHERE status='open'"; }
else if ($alertsStatus === 'resolved') { $alertsWhere = "WHERE status='resolved'"; }

$alerts = [];
$asql = "SELECT item_name, item_qty, threshold, status, created_at, resolved_at FROM alerts {$alertsWhere} ORDER BY created_at DESC";
if ($ares = $conn->query($asql)) {
    while ($row = $ares->fetch_assoc()) {
        $alerts[] = $row;
    }
    $ares->close();
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Inventory - QS Tech</title>
    <link rel="stylesheet" href="/assets/sidebar.css" />
    <link rel="stylesheet" href="/assets/lead_form.css" />
    <link rel="stylesheet" href="/assets/inventory.css" />
</head>
<body>
  <div class="layout">
    <?php include __DIR__ . '/../components/sidebar.php'; ?>
    <main class="content">
      <div class="wrap">
        <h3 class="page-title" style="margin:0 0 12px 0;">Inventory</h3>
        <?php 
          $base = '/src/pages/inventory.php';
          $linkAll = $base . '?status=all';
          $linkLow = $base . '?status=low';
        ?>
        <div class="filters">
          <a class="<?= $status==='all' ? 'is-active' : '' ?>" href="<?= $linkAll ?>">All</a>
          <a class="<?= $status==='low' ? 'is-active' : '' ?>" href="<?= $linkLow ?>">Low only</a>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Qty</th>
              <th>Threshold</th>
              <th>Status</th>
              <th>Last updated</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($items)): ?>
              <tr><td colspan="5">No items found.</td></tr>
            <?php else: ?>
              <?php foreach ($items as $it): 
                $isLow = (int)$it['qty'] < (int)$it['threshold'];
              ?>
              <tr>
                <td><?= htmlspecialchars($it['name']) ?></td>
                <td><?= (int)$it['qty'] ?></td>
                <td><?= (int)$it['threshold'] ?></td>
                <td>
                  <?php if ($isLow): ?>
                    <span class="status-low">Low</span>
                  <?php else: ?>
                    <span class="status-ok">Available</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($it['last_updated']) ?></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="wrap" style="margin-top: 24px;">
        <h3 class="page-title" style="margin:0 0 12px 0;">Alerts</h3>
        <?php 
          $abase = '/src/pages/inventory.php';
          $qs = function($s) use ($status){ return '?status=' . urlencode($status) . '&alerts_status=' . urlencode($s); };
        ?>
        <div class="filters">
          <a class="<?= $alertsStatus==='all' ? 'is-active' : '' ?>" href="<?= $abase . $qs('all') ?>">All</a>
          <a class="<?= $alertsStatus==='open' ? 'is-active' : '' ?>" href="<?= $abase . $qs('open') ?>">Open</a>
          <a class="<?= $alertsStatus==='resolved' ? 'is-active' : '' ?>" href="<?= $abase . $qs('resolved') ?>">Resolved</a>
        </div>
        <table class="table">
          <thead>
            <tr>
              <th>Item</th>
              <th>Qty / Threshold</th>
              <th>Status</th>
              <th>Created</th>
              <th>Resolved</th>
              <th>PO</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($alerts)): ?>
              <tr><td colspan="6">No alerts.</td></tr>
            <?php else: ?>
              <?php foreach ($alerts as $al): 
                $isOpen = $al['status'] === 'open';
                $itemName = $al['item_name'];
                $poUrl = '/src/pages/po.php?item=' . urlencode($itemName);
              ?>
              <tr>
                <td><?= htmlspecialchars($itemName) ?></td>
                <td><?= (int)$al['item_qty'] ?> / <?= (int)$al['threshold'] ?></td>
                <td>
                  <?php if ($isOpen): ?>
                    <span class="status-low">Open</span>
                  <?php else: ?>
                    <span class="status-ok">Resolved</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($al['created_at']) ?></td>
                <td><?= $isOpen ? '-' : htmlspecialchars($al['resolved_at'] ?? '—') ?></td>
                <td><a class="open-link btn-open" href="<?= htmlspecialchars($poUrl) ?>" target="_blank" rel="noopener">View PO</a></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</body>
</html>


