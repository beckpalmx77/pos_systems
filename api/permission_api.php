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

    // 1. ดึงรายการเมนูทั้งหมด และสิทธิ์ปัจจุบัน
    case 'get_permissions':
      $sql = "SELECT * FROM menus ORDER BY id ASC";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
      echo json_encode($menus);
      break;

    // 2. บันทึกสิทธิ์ (Logic ใหม่: ตัดต่อ String allowed_roles)
    case 'save_permission':
      $target_role = trim($request_data['role']); // เช่น 'staff' หรือ 'admin'
      $selected_menu_ids = $request_data['menu_ids'] ?? []; // array ของ id ที่ติ๊กถูก เช่น [1, 2, 5]

      if (empty($target_role)) {
        echo json_encode(["success" => false, "message" => "ไม่ระบุตำแหน่ง (Role)"]);
        break;
      }

      // 1. ดึงข้อมูลเมนูทั้งหมดมาก่อน เพื่อมาวนลูปเช็คทีละอัน
      $stmt = $conn->prepare("SELECT id, allowed_roles FROM menus");
      $stmt->execute();
      $all_menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

      $conn->beginTransaction();

      foreach ($all_menus as $menu) {
        // แปลง String "admin,staff" -> Array ["admin", "staff"]
        $current_roles = array_filter(explode(',', $menu['allowed_roles']));

        // ตัด whitespace ออกเพื่อความชัวร์
        $current_roles = array_map('trim', $current_roles);

        // เช็คว่าเมนูนี้ ถูกติ๊กเลือกมาหรือไม่?
        if (in_array($menu['id'], $selected_menu_ids)) {
          // --- กรณีถูกเลือก (ต้องมี role นี้) ---
          if (!in_array($target_role, $current_roles)) {
            $current_roles[] = $target_role; // เพิ่ม role เข้าไป
          }
        } else {
          // --- กรณีไม่ถูกเลือก (ต้องเอา role นี้ออก) ---
          if (($key = array_search($target_role, $current_roles)) !== false) {
            unset($current_roles[$key]); // ลบ role ออก
          }
        }

        // แปลง Array กลับเป็น String "admin,staff"
        $new_roles_str = implode(',', $current_roles);

        // อัปเดตลง Database (เฉพาะตัวที่เปลี่ยนแปลง)
        if ($new_roles_str !== $menu['allowed_roles']) {
          $update = $conn->prepare("UPDATE menus SET allowed_roles = ? WHERE id = ?");
          $update->execute([$new_roles_str, $menu['id']]);
        }
      }

      $conn->commit();
      echo json_encode(["success" => true, "message" => "บันทึกสิทธิ์เรียบร้อยแล้ว"]);
      break;

    default:
      echo json_encode(["success" => false, "message" => "Invalid Action"]);
      break;
  }

} catch (PDOException $e) {
  if ($conn->inTransaction()) $conn->rollBack();
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
