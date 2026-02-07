<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="max-w-4xl mx-auto w-full bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">

      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-white">
        <div>
          <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <span class="bg-green-100 text-green-600 p-2 rounded-lg"><i class="fas fa-file-excel"></i></span>
            นำเข้าสินค้า (Import Excel)
          </h2>
          <p class="text-sm text-gray-500 mt-1 ml-11">อัปเดตหรือเพิ่มสินค้าใหม่ด้วยไฟล์ Excel (.xlsx, .xls)</p>
        </div>
        <button onclick="downloadTemplate()" class="text-green-600 hover:text-green-800 text-sm font-bold border border-green-200 px-4 py-2 rounded-lg hover:bg-green-50 transition flex items-center gap-2">
          <i class="fas fa-download"></i> ดาวน์โหลดตัวอย่าง
        </button>
      </div>

      <div class="p-8">
        <div class="border-2 border-dashed border-gray-300 rounded-xl p-12 text-center hover:bg-gray-50 transition cursor-pointer relative group" id="dropArea">
          <input type="file" id="excelFile" accept=".xlsx, .xls" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="handleFileSelect(this)">

          <div id="uploadPlaceholder" class="transition-transform group-hover:scale-105 duration-300">
            <div class="bg-blue-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4 text-blue-500">
              <i class="fas fa-cloud-upload-alt text-4xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-700">คลิกเพื่อเลือกไฟล์ หรือ ลากไฟล์มาวางที่นี่</h3>
            <p class="text-sm text-gray-400 mt-2">รองรับไฟล์นามสกุล .xlsx หรือ .xls</p>
          </div>

          <div id="fileInfo" class="hidden">
            <i class="fas fa-file-excel text-6xl text-green-500 mb-4 drop-shadow-sm"></i>
            <h3 id="fileName" class="text-xl font-bold text-gray-800 break-all">filename.xlsx</h3>
            <p id="fileSize" class="text-sm text-gray-500 mt-1">0 KB</p>
            <button onclick="resetFile()" class="mt-6 text-red-500 hover:text-red-700 text-sm font-bold hover:underline z-20 relative px-4 py-1 rounded border border-transparent hover:border-red-200">
              <i class="fas fa-times mr-1"></i> ยกเลิกไฟล์นี้
            </button>
          </div>
        </div>

        <div class="mt-6 bg-blue-50 p-5 rounded-xl border border-blue-100 text-sm text-blue-800">
          <h4 class="font-bold mb-3 flex items-center gap-2"><i class="fas fa-info-circle text-lg"></i> เงื่อนไขการนำเข้าข้อมูล:</h4>
          <ul class="list-disc list-inside space-y-2 ml-1">
            <li>ไฟล์ต้องเป็นนามสกุล <b>.xlsx</b> (แนะนำ) หรือ <b>.xls</b></li>
            <li>ระบบจะอ่านข้อมูลจาก <b>Sheet แรก</b> เท่านั้น</li>
            <li>ลำดับคอลัมน์:
              <ol class="list-decimal list-inside ml-6 mt-1 space-y-1 text-blue-700">
                <li><b>Barcode</b> (รหัสบาร์โค้ด *ห้ามซ้ำ)</li>
                <li><b>Name</b> (ชื่อสินค้า)</li>
                <li><b>Price</b> (ราคาขาย *ใส่ตัวเลขเท่านั้น)</li>
              </ol>
            </li>
            <li class="pt-1 text-orange-600"><i class="fas fa-exclamation-triangle mr-1"></i> หาก Barcode ซ้ำกับที่มีอยู่ ระบบจะทำการ <b>อัปเดตชื่อและราคา</b> ให้ทันที</li>
          </ul>
        </div>

        <div class="mt-8 flex justify-end items-center gap-4">
          <div id="progressContainer" class="hidden flex-1 mr-4">
            <div class="flex justify-between mb-1">
              <span class="text-xs font-bold text-blue-600 animate-pulse">กำลังประมวลผล...</span>
              <span class="text-xs font-bold text-blue-600">Uploading</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-blue-600 h-2 rounded-full animate-progress" style="width: 100%"></div>
            </div>
          </div>

          <button onclick="uploadFile()" id="btnUpload" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-bold shadow-lg transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2" disabled>
            <i class="fas fa-upload"></i> เริ่มนำเข้าข้อมูล
          </button>
        </div>

      </div>
    </div>
  </div>
</main>

<?php include 'layouts/footer.php'; ?>

<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<script>
  const API_URL = 'api/basic_api.php';
  let selectedFile = null;

  // ตั้งค่า Alertify Defaults
  $(document).ready(function() {
    if(typeof alertify !== 'undefined') {
      alertify.defaults.glossary.title = 'ผลการทำงาน';
      alertify.defaults.glossary.ok = 'ตกลง';
    }
  });

  // 1. จัดการเมื่อมีการเลือกไฟล์
  function handleFileSelect(input) {
    if (input.files && input.files[0]) {
      selectedFile = input.files[0];

      // สลับหน้าจอ UI
      $('#uploadPlaceholder').addClass('hidden');
      $('#fileInfo').removeClass('hidden');

      // แสดงข้อมูลไฟล์
      $('#fileName').text(selectedFile.name);
      $('#fileSize').text((selectedFile.size / 1024).toFixed(2) + ' KB');

      // เปิดปุ่ม Upload
      $('#btnUpload').prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
    }
  }

  // 2. ยกเลิกไฟล์ที่เลือก
  function resetFile() {
    $('#excelFile').val(''); // Clear input
    selectedFile = null;

    // สลับหน้าจอ UI กลับ
    $('#uploadPlaceholder').removeClass('hidden');
    $('#fileInfo').addClass('hidden');

    // ปิดปุ่ม Upload
    $('#btnUpload').prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
    $('#progressContainer').addClass('hidden');
    $('#btnUpload').removeClass('hidden');
  }

  // 3. ส่งไฟล์ไป Backend
  async function uploadFile() {
    if (!selectedFile) return;

    // UI Loading State
    $('#btnUpload').addClass('hidden');
    $('#progressContainer').removeClass('hidden');

    // เตรียมข้อมูล
    const formData = new FormData();
    formData.append('file', selectedFile);

    try {
      const res = await fetch(`${API_URL}?action=import_products`, {
        method: 'POST',
        body: formData
      });

      const result = await res.json();

      // UI Reset State
      $('#progressContainer').addClass('hidden');
      $('#btnUpload').removeClass('hidden');

      if (result.success) {
        // Success: แสดง Popup และ Reset
        alertify.alert(
          'นำเข้าสำเร็จ',
          `<div class="text-center py-4">
                        <i class="fas fa-check-circle text-5xl text-green-500 mb-3"></i>
                        <h3 class="text-lg font-bold text-gray-700">${result.message}</h3>
                        <p class="text-sm text-gray-500 mt-1">ข้อมูลถูกบันทึกลงฐานข้อมูลเรียบร้อยแล้ว</p>
                    </div>`,
          function(){ resetFile(); }
        );
      } else {
        // Error: แจ้งเตือน
        alertify.alert('ข้อผิดพลาด',
          `<div class="text-center text-red-600">
                        <i class="fas fa-times-circle text-4xl mb-2"></i><br>
                        ${result.message}
                    </div>`
        );
      }

    } catch (e) {
      console.error(e);
      $('#progressContainer').addClass('hidden');
      $('#btnUpload').removeClass('hidden');
      alertify.error('ไม่สามารถเชื่อมต่อกับ Server ได้');
    }
  }

  // 4. สร้างและดาวน์โหลดไฟล์ Template (.xlsx)
  function downloadTemplate() {
    // ข้อมูลตัวอย่าง
    const data = [
      ["Barcode", "Name", "Price"],         // Header
      ["8851111111", "สินค้าตัวอย่าง A", 100],
      ["8852222222", "สินค้าตัวอย่าง B", 250.50]
    ];

    // สร้าง Worksheet และ Workbook
    const ws = XLSX.utils.aoa_to_sheet(data);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Template");

    // Save ไฟล์
    XLSX.writeFile(wb, "template_products.xlsx");
  }
</script>

<style>
  /* Animation สำหรับ Progress Bar */
  @keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 90%; }
  }
  .animate-progress {
    animation: progress 2s ease-in-out infinite;
  }
</style>
