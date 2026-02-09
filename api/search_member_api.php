<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

require_once '../config/connect_db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
  switch ($action) {
    case 'get_all_members':
      // ดึงข้อมูลสมาชิก (ID, Code, Name, Points)
      $sql = "SELECT id, code, name, points FROM members ORDER BY name ASC";
      $stmt = $conn->prepare($sql);
      $stmt->execute();
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

      echo json_encode($result);
      break;

    default:
      echo json_encode(["message" => "Invalid Action"]);
      break;
  }
} catch (PDOException $e) {
  echo json_encode(["error" => $e->getMessage()]);
}
