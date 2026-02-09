<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>POS SYSTEMS</title>
  <link rel="icon" href="img/favicon/favicon.ico" type="image/x-icon">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
  <style>
    body { font-family: 'Sarabun', sans-serif; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .dataTables_wrapper .dataTables_filter input { border: 1px solid #ddd; padding: 5px; border-radius: 5px; margin-left: 5px; }
  </style>
</head>
<body class="bg-gray-100 text-gray-800 h-screen overflow-hidden">

<div id="loginModal" class="fixed inset-0 bg-black bg-opacity-80 z-50 flex justify-center items-center backdrop-blur-sm">
  <div class="bg-white p-8 rounded-xl shadow-2xl w-96">
    <div class="text-center mb-6">
      <div class="bg-blue-600 text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl shadow-lg">
        <i class="fas fa-cash-register"></i>
      </div>
      <h2 class="text-2xl font-bold text-gray-800">เข้าสู่ระบบ POS</h2>
      <p class="text-xs text-gray-400 mt-2">admin/1234</p>
    </div>
    <div class="space-y-4">
      <input type="text" id="l_username" class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none" placeholder="Username">
      <input type="password" id="l_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg outline-none" placeholder="Password">
      <button onclick="login()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg shadow-lg">เข้าสู่ระบบ</button>
    </div>
  </div>
</div>

<div id="appWrapper" class="hidden flex h-screen">

  <style>
    /* ปรับสี Alertify ให้เข้ากับธีม */
    .alertify-notifier .ajs-message.ajs-success { background-color: #10b981; color: white; }
    .alertify-notifier .ajs-message.ajs-error { background-color: #ef4444; color: white; }
    .alertify-notifier .ajs-message.ajs-warning { background-color: #f59e0b; color: white; }
  </style>
