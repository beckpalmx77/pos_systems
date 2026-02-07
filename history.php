<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

      <div class="p-5 border-b bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
        <h2 class="text-xl font-bold text-gray-700 whitespace-nowrap">
          <i class="fas fa-history text-blue-500 mr-2"></i> ประวัติการขาย
        </h2>

        <div class="flex flex-wrap items-center gap-2">
          <div class="flex items-center bg-white border rounded-lg px-2 py-1 shadow-sm">
            <span class="text-sm text-gray-500 mr-2">จาก:</span>
            <input type="date" id="startDate" class="text-sm outline-none text-gray-700 bg-transparent">
          </div>
          <div class="flex items-center bg-white border rounded-lg px-2 py-1 shadow-sm">
            <span class="text-sm text-gray-500 mr-2">ถึง:</span>
            <input type="date" id="endDate" class="text-sm outline-none text-gray-700 bg-transparent">
          </div>

          <button onclick="reloadTable()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
            <i class="fas fa-search"></i> ค้นหา
          </button>
        </div>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="salesTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th width="15%">เลขที่</th>
            <th width="15%">วันที่</th>
            <th>ลูกค้า</th>
            <th>พนักงาน</th>
            <th class="text-right" width="15%">ยอดรวม</th>
            <th class="text-center" width="10%">Action</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="detailModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-3/4 max-w-4xl h-5/6 flex flex-col overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 class="text-lg font-bold text-gray-700">รายละเอียดใบเสร็จ</h3>
        <button id="btnCloseModal" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 bg-blue-50 border-b text-sm grid grid-cols-2 gap-4">
        <div>เอกสาร: <span id="mDoc" class="font-bold text-blue-700"></span></div>
        <div>วันที่: <span id="mDate"></span></div>
        <div>ลูกค้า: <span id="mMem"></span></div>
        <div>พนักงาน: <span id="mCash"></span></div>
      </div>

      <div class="flex-1 overflow-auto p-4">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 border-b">
          <tr>
            <th class="p-3">สินค้า</th>
            <th class="p-3 text-right">ราคา</th>
            <th class="p-3 text-center">จำนวน</th>
            <th class="p-3 text-right">รวม</th>
          </tr>
          </thead>
          <tbody id="mBody"></tbody>
        </table>
      </div>

      <div class="p-4 bg-gray-100 border-t text-right font-bold text-lg text-gray-600">
        ยอดสุทธิ: <span id="mTotal" class="text-3xl text-blue-600">0.00</span>
      </div>
    </div>
  </div>

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;
  const API_URL = 'api/basic_api.php';

  $(document).ready(function() {

    // ============================================================
    // 1. กำหนดค่าเริ่มต้นวันที่ (แก้ไขใหม่ ไม่ให้เพี้ยนเป็น UTC)
    // ============================================================
    const today = new Date();
    // วันที่ 1 ของเดือนปัจจุบัน
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    // ฟังก์ชันแปลง Date Object -> String "YYYY-MM-DD" (ตามเวลา Local)
    function formatDate(date) {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0'); // เดือนเริ่มที่ 0 ต้อง +1
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    }

    // ใส่ค่าลงใน Input
    $('#startDate').val(formatDate(firstDay));
    $('#endDate').val(formatDate(today));


    // ============================================================
    // 2. สร้าง DataTables
    // ============================================================
    table = $('#salesTable').DataTable({
      "ajax": {
        "url": `${API_URL}?action=get_orders`,
        "dataSrc": "",
        "data": function(d) {
          // ส่งค่าวันที่ไปกับ Request ทุกครั้งที่มีการโหลดตาราง
          d.start_date = $('#startDate').val();
          d.end_date = $('#endDate').val();
        }
      },
      "columns": [
        {
          "data": "doc_id",
          "className": "font-bold text-blue-600 font-mono"
        },
        {
          "data": "order_date",
          "render": function(data) {
            // ตัดแสดงแค่วันที่ (ถ้าต้องการ) หรือแสดงเวลาด้วยก็ได้
            return data;
          }
        },
        {
          "data": "member_name",
          "render": d => d ? `<span class="text-green-600 font-bold"><i class="fas fa-user-check"></i> ${d}</span>` : '<span class="text-gray-400">ทั่วไป</span>'
        },
        { "data": "cashier_name" },
        {
          "data": "total_amount",
          "className": "text-right font-bold",
          "render": $.fn.dataTable.render.number(',', '.', 2, '')
        },
        {
          "data": null,
          "className": "text-center",
          "render": (d,t,r) => `
            <button onclick="view('${r.doc_id}','${r.order_date}','${r.cashier_name}','${r.member_name||'ทั่วไป'}','${r.total_amount}')"
                class="bg-blue-50 hover:bg-blue-100 text-blue-600 py-1 px-3 rounded-full text-xs font-bold transition shadow-sm border border-blue-200">
                <i class="fas fa-eye"></i> ดู
            </button>`
        }
      ],
      "order": [[ 0, "desc" ]] // เรียงตามเลขที่ล่าสุดก่อน
    });

    // Event ปิด Modal
    $('#btnCloseModal').click(() => $('#detailModal').addClass('hidden'));

    // คลิกพื้นหลังปิด Modal
    $('#detailModal').click((e) => {
      if(e.target === document.getElementById('detailModal')) $('#detailModal').addClass('hidden');
    });
  });

  // ฟังก์ชัน Refresh ตาราง (กดปุ่มค้นหา)
  function reloadTable() {
    // คำสั่งนี้จะไปเรียก ajax.data ข้างบนใหม่อีกรอบ พร้อมค่าวันที่ปัจจุบันใน input
    table.ajax.reload();
  }

  // ฟังก์ชันดูรายละเอียด (เปิด Modal)
  async function view(id, date, cash, mem, total) {
    // ใส่ข้อมูล Header
    $('#mDoc').text(id);
    $('#mDate').text(date);
    $('#mCash').text(cash);
    $('#mMem').text(mem);
    $('#mTotal').text(parseFloat(total).toLocaleString('th-TH', {minimumFractionDigits:2}));

    // เปิด Modal และแสดง Loading
    $('#detailModal').removeClass('hidden');
    $('#mBody').html('<tr><td colspan="4" class="text-center p-8 text-gray-400"><i class="fas fa-circle-notch fa-spin text-2xl"></i><br>กำลังโหลด...</td></tr>');

    try {
      // ดึงข้อมูลรายการสินค้า
      const res = await fetch(`${API_URL}?action=get_order_detail&doc_id=${id}`);
      const items = await res.json();

      let html = '';
      if(items.length > 0) {
        items.forEach(i => {
          const sum = parseFloat(i.price) * parseInt(i.qty);
          html += `
                <tr class="border-b hover:bg-gray-50">
                    <td class="p-3">
                        <div class="font-bold text-gray-700">${i.product_name}</div>
                        ${i.barcode ? `<div class="text-xs text-gray-400 font-mono">${i.barcode}</div>` : ''}
                    </td>
                    <td class="p-3 text-right">${parseFloat(i.price).toFixed(2)}</td>
                    <td class="p-3 text-center"><span class="bg-gray-100 px-2 py-1 rounded text-xs font-bold">${i.qty}</span></td>
                    <td class="p-3 text-right font-bold text-blue-600">${sum.toFixed(2)}</td>
                </tr>`;
        });
      } else {
        html = '<tr><td colspan="4" class="text-center p-4 text-red-400">ไม่พบรายการสินค้า</td></tr>';
      }
      $('#mBody').html(html);

    } catch(e) {
      console.error(e);
      $('#mBody').html('<tr><td colspan="4" class="text-center p-4 text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
    }
  }
</script>
