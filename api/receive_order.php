<?php
// ไฟล์นี้อยู่บน Server: 192.168.88.241
// Path: /pos_systems/api/receive_order.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// 1. รับข้อมูล JSON ที่ Kong ส่งมา
$json_input = file_get_contents("php://input");
$data = json_decode($json_input, true);

// ตรวจสอบว่ามีข้อมูลมาจริงไหม
if (empty($data)) {
  http_response_code(400);
  echo json_encode(["status" => "error", "message" => "No data received"]);
  exit();
}

// 2. [DEBUG] บันทึก Log เพื่อดูว่าข้อมูลมาถึงจริงไหม
// สร้างไฟล์ log_orders.txt ในโฟลเดอร์เดียวกัน (ต้องเปิด Permission ให้เขียนไฟล์ได้)
$log_entry = "--- Received Order at " . date("Y-m-d H:i:s") . " ---\n";
$log_entry .= "Transaction ID: " . ($data['transaction_id'] ?? 'Unknown') . "\n";
$log_entry .= "Amount: " . ($data['total_amount'] ?? 0) . "\n";
$log_entry .= "Raw Data: " . $json_input . "\n\n";

file_put_contents("log_orders.txt", $log_entry, FILE_APPEND);


// 3. จำลองการบันทึกลง Database กลาง (ส่วนนี้คุณต้องไปเขียนต่อเชื่อม DB จริง)
// ... เชื่อมต่อ Database Server กลาง ...
// ... Insert ลง Table orders_hq ...


// 4. ตอบกลับ Kong ว่าได้รับเรียบร้อย
echo json_encode([
  "status" => "success",
  "message" => "Order received at HQ Server",
  "received_tx" => $data['transaction_id'] ?? null,
  "hq_timestamp" => date("c")
]);
