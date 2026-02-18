<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");

// เริ่ม session เพื่อใช้ตรวจสอบ user_id ในการเปลี่ยนรหัสผ่าน และเก็บสถานะการ Login
session_start();

require_once '../config/connect_db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$json_input = file_get_contents("php://input");
$request_data = json_decode($json_input, true);

try {
  switch ($action) {

    // -----------------------------------------------------------------------
    // GROUP 1: AUTHENTICATION & MENUS
    // -----------------------------------------------------------------------

    // 1. LOGIN
    case 'login':
      $u = $request_data['username'] ?? '';
      $p = $request_data['password'] ?? '';

      if(empty($u) || empty($p)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบ"]);
        break;
      }

      $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
      $stmt->execute([$u, $p]);
      $user = $stmt->fetch();

      if ($user) {
        // เก็บข้อมูลลง Session สำหรับการเช็คสิทธิ์ในหน้าอื่นๆ และใช้เปลี่ยนรหัสผ่าน
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        unset($user['password']); // ลบรหัสผ่านออกเพื่อความปลอดภัยก่อนส่งกลับหน้าบ้าน
        echo json_encode(["success" => true, "user" => $user]);
      } else {
        echo json_encode(["success" => false, "message" => "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง"]);
      }
      break;

    // 2. GET MENUS (ดึงเมนูตามสิทธิ์ของผู้ใช้คนนั้น)
    case 'get_menus':
      $role = $_GET['role'] ?? '';
      $search = "%$role%";
      $stmt = $conn->prepare("SELECT * FROM menus WHERE allowed_roles LIKE ?");
      $stmt->execute([$search]);
      echo json_encode($stmt->fetchAll());
      break;


    // -----------------------------------------------------------------------
    // GROUP 2: USER MANAGEMENT (CRUD) - สำหรับหน้า users.php
    // -----------------------------------------------------------------------

    // 3. GET ALL USERS
    case 'get_users':
      $stmt = $conn->prepare("SELECT id, username, fullname, role FROM users ORDER BY id ASC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 4. SAVE USER (เพิ่มใหม่ หรือ แก้ไข)
    case 'save_user':
      $id = $request_data['id'] ?? '';
      $username = $request_data['username'] ?? '';
      $password = $request_data['password'] ?? '';
      $fullname = $request_data['fullname'] ?? '';
      $role = $request_data['role'] ?? '';

      if (empty($id)) {
        // -- INSERT (เพิ่มผู้ใช้ใหม่) --
        // เช็ค Username ซ้ำ
        $chk = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $chk->execute([$username]);
        if($chk->fetch()) {
          echo json_encode(["success" => false, "message" => "Username นี้มีอยู่ในระบบแล้ว"]);
          break;
        }

        $sql = "INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$username, $password, $fullname, $role])) {
          echo json_encode(["success" => true, "message" => "เพิ่มผู้ใช้งานสำเร็จ"]);
        }
      } else {
        // -- UPDATE (แก้ไขผู้ใช้เดิม) --
        if (!empty($password)) {
          // ถ้ามีการกรอกรหัสใหม่ -> อัปเดตรหัสด้วย
          $sql = "UPDATE users SET username=?, password=?, fullname=?, role=? WHERE id=?";
          $params = [$username, $password, $fullname, $role, $id];
        } else {
          // ถ้าไม่กรอกรหัส -> ใช้รหัสเดิม (ไม่อัปเดต field password)
          $sql = "UPDATE users SET username=?, fullname=?, role=? WHERE id=?";
          $params = [$username, $fullname, $role, $id];
        }
        $stmt = $conn->prepare($sql);
        if($stmt->execute($params)) {
          echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลสำเร็จ"]);
        }
      }
      break;

    // 5. DELETE USER
    case 'delete_user':
      $id = $request_data['id'] ?? '';
      $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
      if($stmt->execute([$id])) {
        echo json_encode(["success" => true]);
      } else {
        echo json_encode(["success" => false, "message" => "ลบข้อมูลไม่สำเร็จ"]);
      }
      break;


    // -----------------------------------------------------------------------
    // GROUP 3: PERMISSION MANAGEMENT - สำหรับหน้า permissions.php
    // -----------------------------------------------------------------------

    // 6. GET ALL MENUS LIST (ดึงรายการเมนูทั้งหมด เพื่อไปทำ Checkbox)
    case 'get_all_menus_list':
      $stmt = $conn->prepare("SELECT id, name, allowed_roles FROM menus ORDER BY id ASC");
      $stmt->execute();
      echo json_encode($stmt->fetchAll());
      break;

    // 7. SAVE PERMISSION (บันทึกการกำหนดสิทธิ์)
    case 'save_permission':
      $target_role = $request_data['role'] ?? '';
      $allowed_menu_ids = $request_data['menu_ids'] ?? [];

      $stmt = $conn->prepare("SELECT id, allowed_roles FROM menus");
      $stmt->execute();
      $all_menus = $stmt->fetchAll();

      foreach ($all_menus as $menu) {
        $current_roles = array_filter(explode(',', $menu['allowed_roles']));
        $is_allowed = in_array($menu['id'], $allowed_menu_ids);

        if ($is_allowed) {
          if (!in_array($target_role, $current_roles)) {
            $current_roles[] = $target_role;
          }
        } else {
          $current_roles = array_diff($current_roles, [$target_role]);
        }

        $new_roles_str = implode(',', $current_roles);
        $update = $conn->prepare("UPDATE menus SET allowed_roles = ? WHERE id = ?");
        $update->execute([$new_roles_str, $menu['id']]);
      }

      echo json_encode(["success" => true, "message" => "บันทึกสิทธิ์เรียบร้อย"]);
      break;


    // -----------------------------------------------------------------------
    // GROUP 4: SELF-SERVICE
    // -----------------------------------------------------------------------

    // 8. CHANGE PASSWORD (สำหรับผู้ใช้งานเปลี่ยนเอง)
    case 'change_password':
      $user_id = $_SESSION['user_id'] ?? '';
      $old_pass = $request_data['old_password'] ?? '';
      $new_pass = $request_data['new_password'] ?? '';

      if (empty($user_id)) {
        echo json_encode(["success" => false, "message" => "เซสชั่นหมดอายุ กรุณาเข้าสู่ระบบใหม่"]);
        break;
      }

      if (empty($old_pass) || empty($new_pass)) {
        echo json_encode(["success" => false, "message" => "กรุณากรอกข้อมูลให้ครบ"]);
        break;
      }

      // ตรวจสอบรหัสผ่านปัจจุบัน
      $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND password = ?");
      $stmt->execute([$user_id, $old_pass]);
      $user = $stmt->fetch();

      if (!$user) {
        echo json_encode(["success" => false, "message" => "รหัสผ่านปัจจุบันไม่ถูกต้อง"]);
        break;
      }

      // อัปเดตรหัสผ่านใหม่
      $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
      if ($update->execute([$new_pass, $user_id])) {
        echo json_encode(["success" => true, "message" => "เปลี่ยนรหัสผ่านสำเร็จ"]);
      } else {
        echo json_encode(["success" => false, "message" => "เกิดข้อผิดพลาดในระบบฐานข้อมูล"]);
      }
      break;

    default:
      echo json_encode(["message" => "Invalid User Action"]);
  }
} catch (PDOException $e) {
  echo json_encode(["success" => false, "message" => "Database Error: " . $e->getMessage()]);
}
