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

    // 1. ดึงข้อมูลสินค้าทั้งหมด
    case 'get_products':
      $stmt = $conn->prepare("SELECT * FROM products ORDER BY id DESC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 2. บันทึกสินค้า (เพิ่มใหม่ / แก้ไข)
    case 'save_product':
      $id = $request_data['id'] ?? '';
      $barcode = trim($request_data['barcode']);
      $name = trim($request_data['name']);
      $price = $request_data['price'];

      if(empty($barcode) || empty($name) || empty($price)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบ"]);
        break;
      }

      if (empty($id)) {
        // --- เพิ่มใหม่ (INSERT) ---

        // เช็คบาร์โค้ดซ้ำ
        $chk = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
        $chk->execute([$barcode]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "บาร์โค้ดนี้ ($barcode) มีอยู่ในระบบแล้ว"]);
          break;
        }

        $sql = "INSERT INTO products (barcode, name, price) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$barcode, $name, $price])) {
          echo json_encode(["success" => true, "message" => "เพิ่มสินค้าเรียบร้อย"]);
        }
      } else {
        // --- แก้ไข (UPDATE) ---

        // เช็คบาร์โค้ดซ้ำ (ต้องไม่ซ้ำกับคนอื่น ยกเว้นตัวเอง)
        $chk = $conn->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
        $chk->execute([$barcode, $id]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "บาร์โค้ดนี้ถูกใช้ไปแล้ว"]);
          break;
        }

        $sql = "UPDATE products SET barcode=?, name=?, price=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$barcode, $name, $price, $id])) {
          echo json_encode(["success" => true, "message" => "แก้ไขสินค้าเรียบร้อย"]);
        }
      }
      break;

    // 3. ลบสินค้า
    case 'delete_product':
      $id = $request_data['id'];
      $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
      if($stmt->execute([$id])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "message" => "ลบไม่สำเร็จ"]);
      }
      break;

    default:
      echo json_encode(["message" => "Invalid Product Action"]);
  }
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
