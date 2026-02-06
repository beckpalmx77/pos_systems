<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
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

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;

  $(document).ready(function() {
    // ตอนนี้ jQuery ($) โหลดมาแล้ว จะทำงานได้ปกติ
    table = $('#salesTable').DataTable({
      "ajax": {
        "url": "api/basic_api.php?action=get_orders", // ตรวจสอบ path ให้ถูกต้อง
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
  });

  // ฟังก์ชันดูรายละเอียด
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
