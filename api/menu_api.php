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

    // 1. ดึงข้อมูลเมนูทั้งหมด (เรียงตาม menu_id)
    case 'get_menus_list':
      // เปลี่ยน ORDER BY เป็น menu_id
      $stmt = $conn->prepare("SELECT * FROM menus ORDER BY menu_id ASC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 2. บันทึกเมนู (เพิ่มใหม่ / แก้ไข)
    case 'save_menu':
      $id = $request_data['id'] ?? '';

      // รับค่า menu_id (ถ้าไม่มีให้เป็น 0)
      $menu_id = isset($request_data['menu_id']) ? intval($request_data['menu_id']) : 0;

      $name = trim($request_data['name']);
      $link = trim($request_data['link']);
      $icon = trim($request_data['icon']);
      $roles_array = $request_data['allowed_roles'] ?? [];
      $allowed_roles = implode(',', $roles_array);

      if(empty($name) || empty($link) || empty($icon)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบ"]);
        break;
      }

      if (empty($id)) {
        // --- เพิ่มใหม่ (INSERT) ---
        // เพิ่ม menu_id ลงใน query
        $sql = "INSERT INTO menus (menu_id, name, link, icon, allowed_roles) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$menu_id, $name, $link, $icon, $allowed_roles])) {
          echo json_encode(["success" => true, "message" => "เพิ่มเมนูสำเร็จ"]);
        }
      } else {
        // --- แก้ไข (UPDATE) ---
        // เพิ่ม menu_id=? ลงใน query
        $sql = "UPDATE menus SET menu_id=?, name=?, link=?, icon=?, allowed_roles=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$menu_id, $name, $link, $icon, $allowed_roles, $id])) {
          echo json_encode(["success" => true, "message" => "แก้ไขเมนูสำเร็จ"]);
        }
      }
      break;

    // 3. ลบเมนู
    case 'delete_menu':
      $id = $request_data['id'];
      $chk = $conn->prepare("SELECT link FROM menus WHERE id = ?");
      $chk->execute([$id]);
      $m = $chk->fetch();
      if($m && $m['link'] == 'menus_manage.php') {
        echo json_encode(["success" => false, "message" => "ไม่สามารถลบเมนูระบบหลักได้"]);
        break;
      }

      $stmt = $conn->prepare("DELETE FROM menus WHERE id = ?");
      if($stmt->execute([$id])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "message" => "ลบไม่สำเร็จ"]);
      }
      break;

    default:
      echo json_encode(["message" => "Invalid Menu Action"]);
  }
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
