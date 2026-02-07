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

    // --- 1. LOGIN & MENU ---
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

    // --- 2. PRODUCT & MEMBER ---
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

    // --- 3. SAVE ORDER (CORE LOGIC) ---
    case 'save_order':
      $cashier = $request_data['cashier'];
      $total = $request_data['total'];
      $items = $request_data['items'];
      $member_id = $request_data['member_id'] ?? null;

      $conn->beginTransaction();
      try {
        // -----------------------------------------------------------
        // 3.1 สร้างเลขที่เอกสาร (Running No: ORD-YYYYMM-XXXXX)
        // -----------------------------------------------------------
        $ym = date("Ym");
        $prefix = "ORD-" . $ym . "-";

        $stmt = $conn->prepare("SELECT doc_id FROM orders WHERE doc_id LIKE ? ORDER BY doc_id DESC LIMIT 1");
        $stmt->execute([$prefix . '%']);
        $lastOrder = $stmt->fetch();

        // ถ้าขึ้นเดือนใหม่ เริ่มนับ 1 ใหม่
        $nextNo = $lastOrder ? intval(substr($lastOrder['doc_id'], -5)) + 1 : 1;
        $newDocId = $prefix . str_pad($nextNo, 5, '0', STR_PAD_LEFT);

        // -----------------------------------------------------------
        // 3.2 บันทึกข้อมูลลง Database
        // -----------------------------------------------------------
        // Insert Order
        $stmt = $conn->prepare("INSERT INTO orders (doc_id, total_amount, cashier_name, member_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$newDocId, $total, $cashier, $member_id]);
        $order_id = $conn->lastInsertId();

        // Insert Items & เตรียม Data สำหรับ API
        $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, doc_id, product_name, price, qty, barcode) VALUES (?, ?, ?, ?, ?, ?)");

        $api_items = []; // ตัวแปรสำหรับส่ง API

        foreach ($items as $item) {
          // เตรียมค่าต่างๆ
          $p_id    = isset($item['id']) ? intval($item['id']) : 0;
          $barcode = isset($item['barcode']) ? $item['barcode'] : '';
          $name    = $item['name'];
          $price   = floatval($item['price']);
          $qty     = intval($item['qty']);
          $line_total = $price * $qty;

          // Save ลง DB (บันทึก Barcode ด้วย)
          $stmt_item->execute([$order_id, $newDocId, $name, $price, $qty, $barcode]);

          // จัด Format สำหรับส่ง API (Data Dictionary)
          $api_items[] = [
            "product_id" => $p_id,
            "barcode"    => $barcode,
            "name"       => $name,
            "price"      => $price,
            "qty"        => $qty,
            "line_total" => $line_total
          ];
        }

        $conn->commit(); // บันทึกสำเร็จ

        // -----------------------------------------------------------
        // 3.3 ส่งข้อมูลไปยัง KONG API GATEWAY
        // -----------------------------------------------------------

        // Target URL (IP 192.168.88.241 Port 8000)
        $kong_url = "http://192.168.88.241:8000/v1/orders";

        // Headers (Key Auth) -> แก้ไข Key ตรงนี้ครับ
        $headers = [
          "Content-Type: application/json",
          "apikey: pos991456", // <--- KEY ที่คุณระบุมา
          "Shop-ID: BRANCH_001"
        ];

        // Payload (Standardized)
        $payload = [
          "transaction_id" => $newDocId,
          "timestamp"      => date("c"),
          "shop_id"        => "BRANCH_001",
          "cashier"        => $cashier,
          "member_id"      => $member_id,
          "total_amount"   => floatval($total),
          "items"          => $api_items
        ];

        // [DEBUG] บันทึก Payload ลงไฟล์
        file_put_contents('debug_kong_payload.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // ส่ง Request
        $gatewayResponse = sendToKong($kong_url, $payload, $headers);

        // [DEBUG] บันทึก Response ลงไฟล์
        file_put_contents('debug_kong_response.json', json_encode($gatewayResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // ส่งผลลัพธ์กลับหน้าบ้าน
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

    // --- 4. HISTORY & REPORTS ---
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

    // --- 5. HOLD BILLS (พักบิล) ---
    case 'hold_bill':
      $note = $request_data['note'];
      $items = json_encode($request_data['items'], JSON_UNESCAPED_UNICODE);
      $total = $request_data['total'];

      $stmt = $conn->prepare("INSERT INTO held_bills (reference_note, items, total_amount) VALUES (?, ?, ?)");
      if($stmt->execute([$note, $items, $total])) {
        echo json_encode(["success" => true, "message" => "พักบิลเรียบร้อย"]);
      } else {
        echo json_encode(["success" => false, "message" => "Error"]);
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
      if($stmt->execute([$id])) echo json_encode(["success" => true]);
      else echo json_encode(["success" => false]);
      break;

// --- 6. IMPORT DATA (Excel .xlsx & .xls) ---
    case 'import_products':
      if (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
        echo json_encode(["success" => false, "message" => "กรุณาเลือกไฟล์ Excel"]);
        break;
      }

      $filename = $_FILES['file']['tmp_name'];
      $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

      if (!in_array($ext, ['xlsx', 'xls'])) {
        echo json_encode(["success" => false, "message" => "รองรับเฉพาะไฟล์ .xlsx หรือ .xls เท่านั้น"]);
        break;
      }

      // โหลด Library ทั้งคู่
      require_once 'SimpleXLSX.php';
      require_once 'SimpleXLS.php';

      $excel = null;

      // เลือกตัวอ่านตามนามสกุล
      if ($ext === 'xlsx') {
        $excel = SimpleXLSX::parse($filename);
      } elseif ($ext === 'xls') {
        $excel = SimpleXLS::parse($filename);
      }

      try {
        if ($excel) {
          $conn->beginTransaction();

          $sql = "INSERT INTO products (barcode, name, price) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE name = VALUES(name), price = VALUES(price)";
          $stmt = $conn->prepare($sql);

          $count = 0;
          $header_skipped = false;

          // วนลูปอ่านข้อมูล (Library ทั้งสองตัวใช้คำสั่ง rows() เหมือนกัน)
          foreach ($excel->rows() as $r) {
            // $r[0] = Barcode, $r[1] = Name, $r[2] = Price

            // เช็คเพื่อข้ามหัวตาราง (ถ้าเจอคำว่า barcode ในแถวแรก)
            if (!$header_skipped && isset($r[0]) && strtolower(trim((string)$r[0])) == 'barcode') {
              $header_skipped = true;
              continue;
            }

            // ข้ามถ้าไม่มีข้อมูล Barcode
            if (empty($r[0])) continue;

            $barcode = trim((string)$r[0]);
            $name    = trim((string)$r[1]);
            $price   = floatval($r[2]);

            $stmt->execute([$barcode, $name, $price]);
            $count++;
          }

          $conn->commit();
          echo json_encode(["success" => true, "message" => "นำเข้าข้อมูลสำเร็จ $count รายการ"]);

        } else {
          // ดึง Error จากตัวที่ทำงานผิดพลาด
          $error = ($ext === 'xlsx') ? SimpleXLSX::parseError() : SimpleXLS::parseError();
          echo json_encode(["success" => false, "message" => "อ่านไฟล์ไม่สำเร็จ: " . $error]);
        }

      } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
      }
      break;

    default: echo json_encode(["message" => "Invalid Action"]);
  }
} catch (PDOException $e) { echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]); }

// ============================================================
// HELPER FUNCTION: cURL to Kong
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

  // Disable SSL Verify (สำหรับ IP ภายใน)
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

  $result = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err = curl_error($ch);
  curl_close($ch);

  if ($err) {
    return ["status" => "error", "message" => $err, "http_code" => $httpCode];
  }

  return json_decode($result, true) ?: ["raw_response" => $result, "http_code" => $httpCode];
}
