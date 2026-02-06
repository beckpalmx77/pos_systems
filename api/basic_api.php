<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

require_once '../config/connect_db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$json_input = file_get_contents("php://input");
$request_data = json_decode($json_input, true);

try {
  switch ($action) {

    // --- ส่วน LOGIN & MENU ---
    case 'login':
      $u = $request_data['username'] ?? '';
      $p = $request_data['password'] ?? '';
      if(empty($u) || empty($p)) { echo json_encode(["success" => false, "message" => "Incomplete Data"]); break; }

      $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
      $stmt->execute([$u, $p]);
      $user = $stmt->fetch();

      if ($user) {
        unset($user['password']);
        echo json_encode(["success" => true, "user" => $user]);
      } else {
        echo json_encode(["success" => false, "message" => "Login Failed"]);
      }
      break;

    case 'get_menus':
      $role = $_GET['role'];
      $search = "%$role%";
      $stmt = $conn->prepare("SELECT * FROM menus WHERE allowed_roles LIKE ?");
      $stmt->execute([$search]);
      echo json_encode($stmt->fetchAll());
      break;

    // --- ส่วนข้อมูลสินค้า & สมาชิก ---
    case 'get_product':
      $barcode = $_GET['barcode'];
      $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
      $stmt->execute([$barcode]);
      $product = $stmt->fetch();
      if ($product) echo json_encode(["found" => true, "data" => $product]);
      else echo json_encode(["found" => false]);
      break;

    case 'get_member':
      $keyword = $_GET['keyword'];
      $stmt = $conn->prepare("SELECT * FROM members WHERE code = ?");
      $stmt->execute([$keyword]);
      $member = $stmt->fetch();
      if ($member) echo json_encode(["found" => true, "data" => $member]);
      else echo json_encode(["found" => false]);
      break;

    // --- ส่วนการขาย (Orders) ---
    case 'save_order':
      $cashier = $request_data['cashier'];
      $total = $request_data['total'];
      $items = $request_data['items'];
      $member_id = $request_data['member_id'] ?? null;

      $conn->beginTransaction();
      try {
        // Generate Running No: ORD-YYYYMM-XXXXX
        $ym = date("Ym");
        $prefix = "ORD-" . $ym . "-";

        $stmt = $conn->prepare("SELECT doc_id FROM orders WHERE doc_id LIKE ? ORDER BY doc_id DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $lastOrder = $stmt->fetch();

        $nextNo = $lastOrder ? intval(substr($lastOrder['doc_id'], -5)) + 1 : 1;
        $newDocId = $prefix . str_pad($nextNo, 5, '0', STR_PAD_LEFT);

        // Insert Order
        $stmt = $conn->prepare("INSERT INTO orders (doc_id, total_amount, cashier_name, member_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$newDocId, $total, $cashier, $member_id]);
        $order_id = $conn->lastInsertId();

        // Insert Items
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, doc_id, product_name, price, qty) VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
          $stmt_item->execute([$order_id, $newDocId, $item['name'], $item['price'], $item['qty']]);
        }

        $conn->commit();
        echo json_encode(["success" => true, "orderId" => $order_id, "docId" => $newDocId]);
      } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
      }
      break;

    case 'get_orders':
      $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
      $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

      if (!empty($start_date) && !empty($end_date)) {
        // กรณีมีการเลือกวันที่: ค้นหาตามช่วงเวลา (ใช้ DATE() เพื่อตัดเวลาออก เปรียบเทียบแค่วันที่)
        $sql = "SELECT o.*, m.name as member_name
                  FROM orders o
                  LEFT JOIN members m ON o.member_id = m.id
                  WHERE DATE(o.order_date) BETWEEN ? AND ?
                  ORDER BY o.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
      } else {
        // กรณีไม่เลือกวันที่: ดึง 100 รายการล่าสุด
        $sql = "SELECT o.*, m.name as member_name
                  FROM orders o
                  LEFT JOIN members m ON o.member_id = m.id
                  ORDER BY o.id DESC LIMIT 100";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
      }

      echo json_encode($stmt->fetchAll());
      break;

/*
    case 'get_orders':
      $sql = "SELECT o.*, m.name as member_name FROM orders o LEFT JOIN members m ON o.member_id = m.id ORDER BY o.id DESC LIMIT 100";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;
*/

    case 'get_order_detail':
      $doc_id = $_GET['doc_id'] ?? '';
      $stmt = $conn->prepare("SELECT * FROM order_items WHERE doc_id = ?");
      $stmt->execute([$doc_id]);
      echo json_encode($stmt->fetchAll());
      break;

    // --- [เพิ่มใหม่] ส่วนพักบิล (Hold Bills) ---

    // 1. บันทึกการพักบิล
    case 'hold_bill':
      $note = $request_data['note']; // หมายเหตุ เช่น ชื่อโต๊ะ
      // แปลง array สินค้าเป็น JSON string เพื่อเก็บใน DB
      $items = json_encode($request_data['items'], JSON_UNESCAPED_UNICODE);
      $total = $request_data['total'];

      $stmt = $conn->prepare("INSERT INTO held_bills (reference_note, items, total_amount) VALUES (?, ?, ?)");
      if($stmt->execute([$note, $items, $total])) {
        echo json_encode(["success" => true, "message" => "พักบิลเรียบร้อย"]);
      } else {
        echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการพักบิล"]);
      }
      break;

    // 2. ดึงรายการบิลที่พักไว้ทั้งหมด
    case 'get_held_bills':
      $stmt = $conn->prepare("SELECT * FROM held_bills ORDER BY id DESC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 3. ลบบิลที่พักไว้ (เมื่อเรียกคืนมาทำรายการ หรือกดยกเลิก)
    case 'delete_held_bill':
      $id = $request_data['id'];
      $stmt = $conn->prepare("DELETE FROM held_bills WHERE id = ?");
      if($stmt->execute([$id])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "message" => "ลบไม่สำเร็จ"]);
      }
      break;

    default: echo json_encode(["message" => "Invalid Action"]);
  }
} catch (PDOException $e) { echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]); }

