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

    // 1. ดึงข้อมูล
    case 'get_categories':
      $stmt = $conn->prepare("SELECT * FROM products_categories ORDER BY categories ASC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 2. บันทึก (เพิ่ม/แก้ไข)
    case 'save_category':
      $id = $request_data['id'] ?? '';
      $name = trim($request_data['name']); // รับค่าชื่อหมวดหมู่ (จะบันทึกลง detail)

      if(empty($name)) {
        echo json_encode(["success" => false, "message" => "กรุณาระบุชื่อหมวดหมู่"]);
        break;
      }

      if (empty($id)) {
        // --- Insert (Auto Generate Code: C-XXXX) ---

        // 1. หาเลขล่าสุด
        $stmt = $conn->prepare("SELECT categories FROM products_categories WHERE categories LIKE 'C-%' ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $lastCode = $stmt->fetchColumn();

        $nextNum = 1;
        if($lastCode) {
          // ตัด string เอาเฉพาะตัวเลขหลัง "C-" มาบวก 1
          $num = intval(substr($lastCode, 2));
          $nextNum = $num + 1;
        }
        // สร้างรหัสใหม่ เติม 0 ด้านหน้าให้ครบ 4 หลัก
        $newCode = "C-" . str_pad($nextNum, 4, "0", STR_PAD_LEFT);

        // 2. บันทึก (categories=รหัส, detail=ชื่อ)
        $sql = "INSERT INTO products_categories (categories, detail) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$newCode, $name])) {
          echo json_encode(["success" => true, "message" => "เพิ่มหมวดหมู่เรียบร้อย ($newCode)"]);
        }

      } else {
        // --- Update (แก้แค่ชื่อ รหัสไม่แก้) ---
        $sql = "UPDATE products_categories SET detail=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$name, $id])) {
          echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลเรียบร้อย"]);
        }
      }
      break;

    // 3. ลบ
    case 'delete_category':
      $id = $request_data['id'];
      $stmt = $conn->prepare("DELETE FROM products_categories WHERE id = ?");
      if($stmt->execute([$id])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "message" => "ลบไม่สำเร็จ"]);
      }
      break;

    default:
      echo json_encode(["message" => "Invalid Action"]);
  }
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
