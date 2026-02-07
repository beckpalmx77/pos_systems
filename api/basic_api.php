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
        // ================================================================
        // 1. สร้างเลขที่เอกสาร (Running No)
        // รูปแบบ: ORD-YYYYMM-XXXXX (เช่น ORD-202602-00001)
        //Logic นี้จะรีเซ็ตเลขใหม่เป็น 1 ทุกครั้งที่ขึ้นเดือนใหม่
        // ================================================================
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

        // ------------------------------------------------------------
        // [แก้ไข] Insert Items (เพิ่ม field barcode)
        // ------------------------------------------------------------
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, doc_id, product_name, price, qty, barcode) VALUES (?, ?, ?, ?, ?, ?)");

        foreach ($items as $item) {
          // ตรวจสอบว่ามี barcode ส่งมาไหม ถ้าไม่มีให้เป็นค่าว่าง
          $item_barcode = isset($item['barcode']) ? $item['barcode'] : '';

          $stmt_item->execute([
            $order_id,
            $newDocId,
            $item['name'],
            $item['price'],
            $item['qty'],
            $item_barcode // เพิ่มค่า barcode ตรงนี้
          ]);
        }
        // ------------------------------------------------------------

        $conn->commit();

        // ============================================================
        // [ยิง API] ส่งข้อมูลไปยัง KONG หรือ Gateway ภายนอก
        // ============================================================

        $kong_url = "https://api.your-company.com/v1/orders";
        // $kong_url = "http://localhost/pos_pro/api/backend_service.php"; // Test Local

        $headers = [
          "Content-Type: application/json",
          "apikey: YOUR_SECRET_KONG_KEY",
          "Shop-ID: BRANCH_001"
        ];

        $payload = [
          "ref_no"    => $newDocId,
          "timestamp" => date("c"),
          "amount"    => $total,
          "cashier"   => $cashier,
          "items"     => $items,
          "member_id" => $member_id
        ];

        // [DEBUG LOG]
        file_put_contents('debug_kong_payload.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // เรียกฟังก์ชันส่งข้อมูล
        $gatewayResponse = sendToKong($kong_url, $payload, $headers);

        // [DEBUG LOG]
        file_put_contents('debug_kong_response.json', json_encode($gatewayResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo json_encode([
          "success" => true,
          "orderId" => $order_id,
          "docId" => $newDocId,
          "gateway_result" => $gatewayResponse
        ]);

      } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
      }
      break;

    case 'get_orders':
      $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
      $end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : '';

      if (!empty($start_date) && !empty($end_date)) {
        $sql = "SELECT o.*, m.name as member_name
                  FROM orders o
                  LEFT JOIN members m ON o.member_id = m.id
                  WHERE DATE(o.order_date) BETWEEN ? AND ?
                  ORDER BY o.id DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
      } else {
        $sql = "SELECT o.*, m.name as member_name
                  FROM orders o
                  LEFT JOIN members m ON o.member_id = m.id
                  ORDER BY o.id DESC LIMIT 100";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
      }
      echo json_encode($stmt->fetchAll());
      break;

    case 'get_order_detail':
      $doc_id = $_GET['doc_id'] ?? '';
      $stmt = $conn->prepare("SELECT * FROM order_items WHERE doc_id = ?");
      $stmt->execute([$doc_id]);
      echo json_encode($stmt->fetchAll());
      break;

    // --- ส่วนพักบิล ---
    case 'hold_bill':
      $note = $request_data['note'];
      $items = json_encode($request_data['items'], JSON_UNESCAPED_UNICODE);
      $total = $request_data['total'];

      $stmt = $conn->prepare("INSERT INTO held_bills (reference_note, items, total_amount) VALUES (?, ?, ?)");
      if($stmt->execute([$note, $items, $total])) {
        echo json_encode(["success" => true, "message" => "พักบิลเรียบร้อย"]);
      } else {
        echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในการพักบิล"]);
      }
      break;

    case 'get_held_bills':
      $stmt = $conn->prepare("SELECT * FROM held_bills ORDER BY id DESC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

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

// ============================================================
// ฟังก์ชันเสริม: ส่ง Request ไปยัง External API (Kong)
// ============================================================
function sendToKong($url, $data, $headers = []) {
  $ch = curl_init($url);
  $jsonData = json_encode($data);

  if (empty($headers)) { $headers = ['Content-Type: application/json']; }
  $headers[] = 'Content-Length: ' . strlen($jsonData);

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

  $result = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);

  if ($err) { return ["status" => "error", "message" => $err, "http_code" => $httpCode]; }

  return json_decode($result, true) ?: ["raw_response" => $result, "http_code" => $httpCode];
}
