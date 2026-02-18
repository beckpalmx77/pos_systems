<?php
date_default_timezone_set('Asia/Bangkok');
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

require_once '../config/connect_db.php';

$action = $_GET['action'] ?? '';
$json_input = file_get_contents("php://input");
$request_data = json_decode($json_input, true);

try {
  switch ($action) {
    // ดึงรายชื่อผู้จำหน่ายทั้งหมด
    case 'get_suppliers':
      $stmt = $conn->prepare("SELECT * FROM suppliers ORDER BY name ASC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
      break;

    // ค้นหาผู้จำหน่ายรายบุคคล
    case 'get_supplier':
      $keyword = $_GET['keyword'] ?? '';
      $stmt = $conn->prepare("SELECT * FROM suppliers WHERE name LIKE ? OR id = ? LIMIT 1");
      $stmt->execute(["%$keyword%", $keyword]);
      $res = $stmt->fetch(PDO::FETCH_ASSOC);
      echo json_encode($res ? ["found" => true, "data" => $res] : ["found" => false]);
      break;

    // บันทึกการสั่งซื้อและรับเข้าสต็อก
    case 'receive_stock':
      $supplier_id = $request_data['supplier_id'];
      $items = $request_data['items'];
      $total = $request_data['total'];
      $user = $request_data['user'] ?? 'Admin';

      $conn->beginTransaction();

      // 1. สร้างเลขที่ PO (PO-YYYYMMDD-XXXX)
      $prefix = "PO-" . date("Ymd") . "-";
      $stmt = $conn->prepare("SELECT po_number FROM purchase_orders WHERE po_number LIKE ? ORDER BY id DESC LIMIT 1");
      $stmt->execute([$prefix . '%']);
      $last = $stmt->fetch();
      $nextNo = $last ? intval(substr($last['po_number'], -4)) + 1 : 1;
      $newPO = $prefix . str_pad($nextNo, 4, '0', STR_PAD_LEFT);

      // 2. Insert ลงตาราง purchase_orders
      $stmt = $conn->prepare("INSERT INTO purchase_orders (po_number, supplier_id, total_amount, status, created_by) VALUES (?, ?, ?, 'received', ?)");
      $stmt->execute([$newPO, $supplier_id, $total, $user]);
      $po_id = $conn->lastInsertId();

      // 3. Loop รายการสินค้า
      $stmt_item = $conn->prepare("INSERT INTO po_items (po_id, product_barcode, cost_price, qty, received_qty) VALUES (?, ?, ?, ?, ?)");
      $stmt_stock = $conn->prepare("UPDATE products SET quantity = quantity + ?, cost_price = ? WHERE barcode = ?");

      foreach ($items as $item) {
        $stmt_item->execute([$po_id, $item['barcode'], $item['cost'], $item['qty'], $item['qty']]);
        // อัปเดตสต็อกและราคาต้นทุนล่าสุดในตาราง products
        $stmt_stock->execute([$item['qty'], $item['cost'], $item['barcode']]);
      }

      $conn->commit();
      echo json_encode(["success" => true, "po_number" => $newPO]);
      break;

    default:
      echo json_encode(["message" => "Invalid Action"]);
  }
} catch (Exception $e) {
  if($conn->inTransaction()) $conn->rollBack();
  echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
