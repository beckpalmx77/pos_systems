<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">ประวัติการขาย</h2>
        <button onclick="reloadTable()" class="bg-white border hover:bg-gray-50 px-4 py-2 rounded text-sm shadow-sm transition">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="salesTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th>เลขที่</th>
            <th>วันที่</th>
            <th>ลูกค้า</th>
            <th>พนักงาน</th>
            <th class="text-right">ยอดรวม</th>
            <th class="text-center">Action</th>
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
          <tr><th class="p-3">สินค้า</th><th class="p-3 text-right">ราคา</th><th class="p-3 text-center">จำนวน</th><th class="p-3 text-right">รวม</th></tr>
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

  $(document).ready(function() {
    // 1. สร้าง DataTables
    table = $('#salesTable').DataTable({
      "ajax": {
        "url": "api/basic_api.php?action=get_orders",
        "dataSrc": ""
      },
      "columns": [
        { "data": "doc_id", "className": "font-bold text-blue-600" },
        { "data": "order_date" },
        { "data": "member_name", "render": d => d ? `<span class="text-green-600 font-bold">${d}</span>` : 'ทั่วไป' },
        { "data": "cashier_name" },
        { "data": "total_amount", "className": "text-right font-bold", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
        {
          "data": null,
          "className": "text-center",
          "render": (d,t,r) => `<button onclick="view('${r.doc_id}','${r.order_date}','${r.cashier_name}','${r.member_name||'ทั่วไป'}','${r.total_amount}')" class="bg-blue-100 text-blue-700 py-1 px-3 rounded text-xs">ดูรายการ</button>`
        }
      ],
      "order": [[ 0, "desc" ]]
    });

    // ------------------------------------------------------------
    // [ส่วนที่เพิ่มเข้ามา] : สั่งงานปุ่มปิด Modal
    // ------------------------------------------------------------

    // 1. เมื่อกดปุ่มกากบาท (X)
    $('#btnCloseModal').click(function() {
      $('#detailModal').addClass('hidden');
    });

    // 2. เมื่อคลิกที่พื้นหลังสีดำ (นอกกล่อง Modal) ก็ให้ปิดด้วย
    $('#detailModal').click(function(e) {
      if (e.target === this) {
        $(this).addClass('hidden');
      }
    });

  });

  // ฟังก์ชัน Refresh ตาราง
  function reloadTable() {
    table.ajax.reload();
  }

  // ฟังก์ชันดูรายละเอียด (เปิด Modal)
  async function view(id, date, cash, mem, total) {
    $('#mDoc').text(id); $('#mDate').text(date); $('#mCash').text(cash); $('#mMem').text(mem);
    $('#mTotal').text(parseFloat(total).toLocaleString('th-TH', {minimumFractionDigits:2}));

    $('#detailModal').removeClass('hidden');
    $('#mBody').html('<tr><td colspan="4" class="text-center p-4">Loading...</td></tr>');

    try {
      const res = await fetch(`api/basic_api.php?action=get_order_detail&doc_id=${id}`);
      const items = await res.json();
      let h = '';
      items.forEach(i => {
        h += `<tr><td class="p-3">${i.product_name}</td><td class="p-3 text-right">${parseFloat(i.price).toFixed(2)}</td><td class="p-3 text-center">${i.qty}</td><td class="p-3 text-right font-bold">${(i.price*i.qty).toFixed(2)}</td></tr>`;
      });
      $('#mBody').html(h);
    } catch(e) { console.error(e); }
  }
</script>
