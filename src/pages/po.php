<?php 
require_once __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../config.php'; 

$itemName = $_GET['item'] ?? '';
$item = null;
if ($itemName !== '') {
    $stmt = $conn->prepare("SELECT id, name, qty, threshold FROM inventory WHERE name = ? LIMIT 1");
    $stmt->bind_param('s', $itemName);
    $stmt->execute();
    $res = $stmt->get_result();
    $item = $res->fetch_assoc();
    $stmt->close();
}

if (!$item) {
    $item = ['name' => $itemName, 'qty' => 0, 'threshold' => 0];
}

$qty = (int)$item['qty'];
$threshold = (int)$item['threshold'];
$suggested = max($threshold - $qty, 1);
$now = date('Y-m-d H:i');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Draft PO - <?= htmlspecialchars($item['name']) ?></title>
  <link rel="stylesheet" href="/assets/lead_form.css" />
  <style>
    .po { max-width: 800px; margin: 2rem auto; background:#fff; border:1px solid #dfe3ea; border-radius:10px; padding:20px; }
    .po h1 { margin:0 0 10px 0; }
    .meta { color:#475569; margin-bottom: 16px; }
    .tbl { width:100%; border-collapse: collapse; }
    .tbl th, .tbl td { border:1px solid #e5e7eb; padding:10px; text-align:left; }
    .tbl th { background:#f8fafc; }
    @media print {
      .no-print { display:none; }
      body { background:#fff; }
      .po { box-shadow:none; border-color:#000; }
    }
  </style>
</head>
<body>
  <div class="po">
    <h1>Draft Purchase Order</h1>
    <div class="meta">Generated: <?= htmlspecialchars($now) ?></div>
    <div class="meta">Supplier: To be confirmed</div>
    <table class="tbl">
      <thead>
        <tr>
          <th>Item</th>
          <th>Current Qty</th>
          <th>Threshold</th>
          <th>Suggested Order Qty</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= $qty ?></td>
          <td><?= $threshold ?></td>
          <td><?= $suggested ?></td>
        </tr>
      </tbody>
    </table>
    <div class="no-print" style="text-align:right; margin-top:12px;">
      <button onclick="window.print()" class="btn">Print</button>
    </div>
  </div>
</body>
</html>


