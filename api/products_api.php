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

      // แปลงค่าตัวเลข (ถ้าว่างให้เป็น 0)
      $price = !empty($request_data['price']) ? $request_data['price'] : 0;
      $cost = !empty($request_data['cost']) ? $request_data['cost'] : 0;
      $quantity = !empty($request_data['quantity']) ? $request_data['quantity'] : 0; // ชื่อ field ใน DB คือ quantity
      $min = !empty($request_data['min']) ? $request_data['min'] : 0;
      $max = !empty($request_data['max']) ? $request_data['max'] : 0;

      if(empty($barcode) || empty($name)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อและบาร์โค้ด"]);
        break;
      }

      if (empty($id)) {
        // --- เพิ่มใหม่ (INSERT) ---
        $chk = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
        $chk->execute([$barcode]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "บาร์โค้ดนี้ ($barcode) มีอยู่ในระบบแล้ว"]);
          break;
        }

        // เพิ่ม field min, max, quantity
        $sql = "INSERT INTO products (barcode, name, price, cost, quantity, min, max) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$barcode, $name, $price, $cost, $quantity, $min, $max])) {
          echo json_encode(["success" => true, "message" => "เพิ่มสินค้าเรียบร้อย"]);
        }
      } else {
        // --- แก้ไข (UPDATE) ---
        $chk = $conn->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
        $chk->execute([$barcode, $id]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "บาร์โค้ดนี้ถูกใช้ไปแล้ว"]);
          break;
        }

        // อัปเดต field min, max, quantity
        $sql = "UPDATE products SET barcode=?, name=?, price=?, cost=?, quantity=?, min=?, max=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$barcode, $name, $price, $cost, $quantity, $min, $max, $id])) {
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
