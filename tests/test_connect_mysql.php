<?php
// 1. ตั้งค่าตัวแปร
$host = "localhost";
$port = 3307;             // <--- ใส่ Port ตรงนี้ (เช่น 3307, 8889)
$username = "myadmin";
$password = "myadmin";
$dbname = "pos_system_dbs";      // อย่าลืมแก้ชื่อ Database ให้ตรงกับของคุณ

try {
  // 2. ระบุ port ลงใน DSN (mysql:host=...;port=...;dbname=...)
  $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

  $conn = new PDO($dsn, $username, $password);

  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "✅ Connected successfully on port $port";

} catch(PDOException $e) {
  echo "❌ Connection failed: " . $e->getMessage();
}

