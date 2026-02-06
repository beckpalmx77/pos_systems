<?php
require_once 'config/connect_db.php';
$doc_id = $_GET['doc_id'];

// ดึงข้อมูลหัวบิล
$stmt = $conn->prepare("SELECT * FROM orders WHERE doc_id = ?");
$stmt->execute([$doc_id]);
$order = $stmt->fetch();

// ดึงรายการสินค้า
$stmt = $conn->prepare("SELECT * FROM order_items WHERE doc_id = ?");
$stmt->execute([$doc_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>Receipt <?php echo $doc_id; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Sarabun', sans-serif; width: 80mm; margin: 0 auto; padding: 10px; font-size: 12px; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { border-bottom: 1px dashed #000; padding: 5px 0; }
    td { padding: 5px 0; }
    .total-section { border-top: 1px dashed #000; margin-top: 10px; padding-top: 5px; }

    @media print {
      @page { margin: 0; }
      body { margin: 0; padding: 5px; }
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">

<div class="text-center">
  <h2 style="margin: 0;">POS PRO SHOP</h2>
  <p>ใบเสร็จรับเงินอย่างย่อ</p>
</div>

<div>
  เลขที่: <?php echo $order['doc_id']; ?><br>
  วันที่: <?php echo $order['order_date']; ?><br>
  พนักงาน: <?php echo $order['cashier_name']; ?>
</div>

<table>
  <thead>
  <tr>
    <th style="text-align:left">รายการ</th>
    <th width="30">Qty</th>
    <th style="text-align:right">รวม</th>
  </tr>
  </thead>
  <tbody>
  <?php foreach ($items as $item): ?>
    <tr>
      <td><?php echo $item['product_name']; ?></td>
      <td class="text-center"><?php echo $item['qty']; ?></td>
      <td class="text-right"><?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<div class="total-section">
  <div style="display: flex; justify-content: space-between; font-size: 16px;" class="bold">
    <span>ยอดสุทธิ</span>
    <span><?php echo number_format($order['total_amount'], 2); ?></span>
  </div>
</div>

<div class="text-center" style="margin-top: 20px;">
  ขอบคุณที่ใช้บริการ
</div>

<div class="text-center no-print" style="margin-top: 20px;">
  <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">ปิดหน้าต่าง</button>
</div>

</body>
</html>
