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

    // 1. ดึงข้อมูลสมาชิกทั้งหมด
    case 'get_members':
      $stmt = $conn->prepare("SELECT * FROM members ORDER BY id DESC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 2. บันทึกสมาชิก (เพิ่มใหม่ / แก้ไข)
    case 'save_member':
      $id = $request_data['id'] ?? '';
      $code = trim($request_data['code']);
      $name = trim($request_data['name']);
      $points = isset($request_data['points']) ? intval($request_data['points']) : 0;

      if(empty($code) || empty($name)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกรหัสและชื่อสมาชิก"]);
        break;
      }

      if (empty($id)) {
        // --- เพิ่มใหม่ (INSERT) ---

        // เช็ค Code ซ้ำ (เช่น เบอร์โทรซ้ำ)
        $chk = $conn->prepare("SELECT id FROM members WHERE code = ?");
        $chk->execute([$code]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "รหัสสมาชิก/เบอร์โทรนี้ ($code) มีอยู่แล้ว"]);
          break;
        }

        $sql = "INSERT INTO members (code, name, points) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$code, $name, $points])) {
          echo json_encode(["success" => true, "message" => "เพิ่มสมาชิกเรียบร้อย"]);
        }
      } else {
        // --- แก้ไข (UPDATE) ---

        // เช็ค Code ซ้ำ (ต้องไม่ซ้ำกับคนอื่น ยกเว้นตัวเอง)
        $chk = $conn->prepare("SELECT id FROM members WHERE code = ? AND id != ?");
        $chk->execute([$code, $id]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "รหัสสมาชิก/เบอร์โทรนี้ถูกใช้ไปแล้ว"]);
          break;
        }

        $sql = "UPDATE members SET code=?, name=?, points=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$code, $name, $points, $id])) {
          echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
        }
      }
      break;

    // 3. ลบสมาชิก
    case 'delete_member':
      $id = $request_data['id'];
      // (Optional) อาจจะเช็คก่อนว่าสมาชิกนี้เคยซื้อของไหม ถ้าเคยอาจจะห้ามลบ หรือแค่ซ่อน
      $stmt = $conn->prepare("DELETE FROM members WHERE id = ?");
      if($stmt->execute([$id])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "message" => "ลบไม่สำเร็จ"]);
      }
      break;

    default:
      echo json_encode(["message" => "Invalid Member Action"]);
  }
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
