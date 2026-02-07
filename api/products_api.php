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

    // 1. ดึงข้อมูลสินค้าทั้งหมด (JOIN กับตารางหมวดหมู่เพื่อเอาชื่อมาแสดง)
    case 'get_products':
      // ใช้ LEFT JOIN เพื่อให้สินค้าที่ไม่มีหมวดหมู่ยังแสดงอยู่
      $sql = "SELECT p.*, c.detail as category_name
              FROM products p
              LEFT JOIN products_categories c ON p.category_id = c.id
              ORDER BY p.id DESC";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 2. บันทึกสินค้า (เพิ่มใหม่ / แก้ไข)
    case 'save_product':
      $id = $request_data['id'] ?? '';
      $barcode = trim($request_data['barcode']);
      $name = trim($request_data['name']);

      // รับค่าหมวดหมู่ (ถ้าไม่เลือกจะเป็นค่าว่าง หรือ null)
      $category_id = !empty($request_data['category_id']) ? $request_data['category_id'] : null;

      // แปลงค่าตัวเลข (ถ้าว่างให้เป็น 0)
      $price = !empty($request_data['price']) ? $request_data['price'] : 0;
      $cost = !empty($request_data['cost']) ? $request_data['cost'] : 0;
      $quantity = !empty($request_data['quantity']) ? $request_data['quantity'] : 0;
      $min = !empty($request_data['min']) ? $request_data['min'] : 0;
      $max = !empty($request_data['max']) ? $request_data['max'] : 0;

      if(empty($barcode) || empty($name)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกชื่อและบาร์โค้ด"]);
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

        // เพิ่ม category_id ใน SQL Insert
        $sql = "INSERT INTO products (barcode, name, category_id, price, cost, quantity, min, max) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$barcode, $name, $category_id, $price, $cost, $quantity, $min, $max])) {
          echo json_encode(["success" => true, "message" => "เพิ่มสินค้าเรียบร้อย"]);
        }
      } else {
        // --- แก้ไข (UPDATE) ---
        // เช็คบาร์โค้ดซ้ำกับคนอื่น
        $chk = $conn->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
        $chk->execute([$barcode, $id]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "บาร์โค้ดนี้ถูกใช้ไปแล้ว"]);
          break;
        }

        // อัปเดตข้อมูลรวมถึง category_id
        $sql = "UPDATE products SET barcode=?, name=?, category_id=?, price=?, cost=?, quantity=?, min=?, max=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$barcode, $name, $category_id, $price, $cost, $quantity, $min, $max, $id])) {
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
