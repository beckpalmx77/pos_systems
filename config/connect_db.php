<?php
date_default_timezone_set("Asia/Bangkok");
// เรียกไฟล์ db_value.inc ที่อยู่ในโฟลเดอร์เดียวกัน
require_once 'db_value.inc';

try {
  $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=".DB_PORT, DB_USER, DB_PASS,
    array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
  );
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
  // ส่ง Error เป็น JSON ป้องกันหน้าเว็บขาว
  header("Content-Type: application/json");
  echo json_encode(["success" => false, "message" => "Connection Error: " . $e->getMessage()]);
  exit();
}
