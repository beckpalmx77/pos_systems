<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 flex flex-col">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex-1 flex flex-col items-center justify-center p-12 transition-all duration-300">

      <div class="relative group">
        <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
        <img src="img/logo/sac_application.png"
             alt="Logo"
             class="relative w-64 md:w-80 h-auto transform transition duration-500 hover:scale-150 drop-shadow-xl">
      </div>

      <!--div class="mt-10 text-center">
        <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">
          ยินดีต้อนรับเข้าสู่ระบบ <span class="text-blue-600">POS SYSTEM</span>
        </h2>
        <p class="mt-3 text-gray-500 text-lg font-medium">
          <i class="far fa-calendar-alt mr-2"></i> วันนี้วันที่: <span id="current-date"><?php echo date('d/m/Y'); ?></span>
        </p>
      </div>

      <div class="mt-12 flex gap-4">
        <a href="pos.php" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-blue-200 transition transform active:scale-95">
          <i class="fas fa-shopping-cart"></i> เริ่มการขาย
        </a>
        <a href="reports.php" class="flex items-center gap-2 bg-white border-2 border-gray-100 hover:border-blue-200 hover:bg-blue-50 text-gray-600 px-8 py-3 rounded-xl font-bold transition">
          <i class="fas fa-file-invoice-dollar"></i> ดูรายงาน
        </a>
      </div-->

    </div>
  </div>
</main>

<?php include 'layouts/footer.php'; ?>

<script>
  // เพิ่มลูกเล่นอัปเดตเวลาแบบ Real-time (Optional)
  function updateTime() {
    const now = new Date();
    // คุณสามารถแต่ง Format วันที่ตามใจชอบได้ที่นี่
  }
</script>
