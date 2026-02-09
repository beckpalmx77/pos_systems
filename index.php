<?php include 'layouts/header.php'; ?>

  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <style>
    .alertify-notifier .ajs-message.ajs-success { background-color: #10b981; color: white; }
    .alertify-notifier .ajs-message.ajs-error { background-color: #ef4444; color: white; }
    .alertify-notifier .ajs-message.ajs-warning { background-color: #f59e0b; color: white; }
    /* ปรับปุ่มใน Modal Alertify */
    .ajs-button.ajs-ok { background-color: #10b981 !important; color: white; font-weight: bold; }
    .ajs-button.ajs-cancel { background-color: #6b7280 !important; color: white; }
  </style>

<?php include 'layouts/sidebar.php'; ?>

  <main class="flex-1 flex flex-col min-w-0 bg-gray-100">
    <?php include 'layouts/topbar.php'; ?>

    <div class="flex-1 p-4 overflow-hidden flex flex-col">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

        <div class="p-4 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
          <div class="flex items-center gap-3 flex-1">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-500 shadow-sm"><i class="fas fa-users"></i></div>
            <div>
              <p class="text-xs text-gray-500">ลูกค้าสมาชิก (F2)</p>
              <h3 id="memberDisplayName" class="text-lg font-bold text-gray-800">ลูกค้าทั่วไป (Guest)</h3>
            </div>
          </div>
          <div class="flex gap-2">
            <input type="text" id="memberInput" class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-48" placeholder="เบอร์โทร / รหัส">
            <button onclick="findMember()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm shadow transition"><i class="fas fa-check"></i> ตกลง</button>
            <button onclick="openMemberSearchModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded-lg text-sm shadow transition" title="รายชื่อสมาชิก"><i class="fas fa-list"></i></button>
            <button onclick="resetMember()" class="bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm shadow hover:bg-gray-400 transition"><i class="fas fa-times"></i></button>
          </div>
        </div>

        <div class="p-4 bg-gray-50 border-b border-gray-100">
          <div class="flex gap-2">
            <div class="relative flex-1">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-barcode text-gray-400 text-xl"></i></div>
              <input type="text" id="barcodeInput" class="w-full pl-12 pr-4 py-4 border-2 border-blue-500 rounded-lg text-xl focus:outline-none focus:ring-2 focus:ring-blue-300 transition" placeholder="ยิงบาร์โค้ด หรือ พิมพ์รหัส (F4)" autofocus>
            </div>
            <button onclick="triggerSearchProduct()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-lg font-bold shadow transition"><i class="fas fa-check"></i> ตกลง</button>
            <button onclick="openProductSearchModal()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2 rounded-lg text-xl font-bold shadow transition flex items-center gap-2" title="ค้นหาสินค้า (F3)"><i class="fas fa-search"></i> ค้นหา</button>
          </div>
        </div>

        <div class="flex-1 overflow-auto relative">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100 sticky top-0 shadow-sm z-10">
            <tr>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600">สินค้า</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-right">ราคา</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">จำนวน</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-right">รวม</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">ลบ</th>
            </tr>
            </thead>
            <tbody id="cartTable" class="divide-y divide-gray-100"></tbody>
          </table>
          <div id="emptyCart" class="flex flex-col items-center justify-center h-40 text-gray-400 mt-10">
            <i class="fas fa-box-open text-5xl mb-3 opacity-50"></i>
            <p class="text-lg">รายการว่าง</p>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-gray-200">
          <div class="grid grid-cols-2 gap-3 mb-4">
            <button onclick="holdBill()" class="bg-orange-500 hover:bg-orange-600 text-white py-2 rounded-lg font-bold shadow transition flex justify-center items-center gap-2">
              <i class="fas fa-pause-circle"></i> พักบิลชั่วคราว
            </button>
            <button onclick="showHeldBills()" class="bg-purple-500 hover:bg-purple-600 text-white py-2 rounded-lg font-bold shadow transition flex justify-center items-center gap-2">
              <i class="fas fa-hand-holding"></i> เรียกบิลคืน <span id="heldBillCount" class="bg-white text-purple-600 text-xs px-2 py-0.5 rounded-full hidden">0</span>
            </button>
          </div>

          <div class="flex justify-between items-end mb-4 border-t pt-4 border-gray-200">
            <div class="text-gray-500">จำนวน: <span id="totalItems" class="font-bold text-gray-800">0</span> ชิ้น</div>
            <div class="text-right">
              <p class="text-xs text-gray-500">ยอดสุทธิ</p>
              <h2 class="text-4xl font-bold text-blue-600">฿<span id="grandTotal">0.00</span></h2>
            </div>
          </div>

          <button onclick="submitOrder()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-2xl font-bold py-4 rounded-xl shadow-lg transition transform active:scale-[0.98] flex justify-center items-center gap-2">
            <i class="fas fa-print"></i> ยืนยันการขาย (Print)
          </button>
        </div>
      </div>
    </div>
  </main>

  <div id="productSearchModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex justify-center items-center z-50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden flex flex-col h-[80vh]">
      <div class="p-4 bg-yellow-500 text-white flex justify-between items-center">
        <h3 class="text-xl font-bold flex items-center gap-2"><i class="fas fa-search"></i> ค้นหาสินค้า</h3>
        <button onclick="$('#productSearchModal').addClass('hidden')" class="text-white hover:text-gray-200 text-3xl font-bold">&times;</button>
      </div>
      <div class="p-4 flex-1 overflow-auto bg-gray-50">
        <table id="searchProductTable" class="w-full text-left border-collapse bg-white rounded-lg shadow-sm overflow-hidden" style="width:100%">
          <thead>
          <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3">Barcode</th>
            <th class="p-3">ชื่อสินค้า</th>
            <th class="p-3 text-right">ราคา</th>
            <th class="p-3 text-center">คงเหลือ</th>
            <th class="p-3 text-center">เลือก</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="p-4 bg-white border-t flex justify-end">
        <button onclick="$('#productSearchModal').addClass('hidden')" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-bold">ปิดหน้าต่าง</button>
      </div>
    </div>
  </div>

  <div id="memberSearchModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex justify-center items-center z-50 backdrop-blur-sm">
    <div class="bg-white w-full max-w-4xl rounded-xl shadow-2xl overflow-hidden flex flex-col h-[80vh]">
      <div class="p-4 bg-blue-600 text-white flex justify-between items-center">
        <h3 class="text-xl font-bold flex items-center gap-2"><i class="fas fa-users"></i> รายชื่อสมาชิก</h3>
        <button onclick="$('#memberSearchModal').addClass('hidden')" class="text-white hover:text-gray-200 text-3xl font-bold">&times;</button>
      </div>
      <div class="p-4 flex-1 overflow-auto bg-gray-50">
        <table id="searchMemberTable" class="w-full text-left border-collapse bg-white rounded-lg shadow-sm overflow-hidden" style="width:100%">
          <thead>
          <tr class="bg-gray-100 text-gray-600 text-sm">
            <th class="p-3">รหัส / เบอร์โทร</th>
            <th class="p-3">ชื่อลูกค้า</th>
            <th class="p-3 text-center">คะแนนสะสม</th>
            <th class="p-3 text-center">เลือก</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
      <div class="p-4 bg-white border-t flex justify-end">
        <button onclick="$('#memberSearchModal').addClass('hidden')" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-bold">ปิดหน้าต่าง</button>
      </div>
    </div>
  </div>

  <div id="heldBillsModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex justify-center items-center z-50 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white w-full max-w-3xl rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] transform transition-all scale-100">
      <div class="p-4 bg-purple-600 text-white flex justify-between items-center">
        <h3 class="text-xl font-bold flex items-center gap-2"><i class="fas fa-list-alt"></i> รายการบิลที่พักไว้</h3>
        <button onclick="$('#heldBillsModal').addClass('hidden')" class="text-white hover:text-gray-200 text-3xl font-bold">&times;</button>
      </div>
      <div class="flex-1 overflow-auto p-0">
        <table class="w-full text-left border-collapse">
          <thead class="bg-purple-50 text-purple-900 sticky top-0">
          <tr>
            <th class="p-4 font-semibold">เวลาพักบิล</th>
            <th class="p-4 font-semibold">หมายเหตุ / ลูกค้า</th>
            <th class="p-4 font-semibold text-right">ยอดรวม</th>
            <th class="p-4 font-semibold text-center">จัดการ</th>
          </tr>
          </thead>
          <tbody id="heldBillsList" class="divide-y divide-purple-100"></tbody>
        </table>
      </div>
      <div class="p-4 bg-gray-50 border-t flex justify-end">
        <button onclick="$('#heldBillsModal').addClass('hidden')" class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-2 rounded-lg font-bold transition">ปิดหน้าต่าง</button>
      </div>
    </div>
  </div>

  <script>
    const API_URL = 'api/basic_api.php';
    const SEARCH_PRODUCT_API = 'api/search_product_api.php';
    const SEARCH_MEMBER_API = 'api/search_member_api.php';

    let cart = [];
    let currentMember = null;
    let productSearchTable = null;
    let memberSearchTable = null;

    $(document).ready(function() {
      if(localStorage.getItem('pos_user')) $('#barcodeInput').focus();

      // 1. ตั้งค่า Alertify Defaults
      if(typeof alertify !== 'undefined') {
        alertify.defaults.glossary.title = 'แจ้งเตือน';
        alertify.defaults.glossary.ok = 'ตกลง';
        alertify.defaults.glossary.cancel = 'ยกเลิก';
        alertify.defaults.transition = "zoom";
        alertify.defaults.theme.ok = "ui positive button";
        alertify.defaults.theme.cancel = "ui black button";
      }
      updateHeldBillCount();
    });

    // --- MEMBER SEARCH ---
    function openMemberSearchModal() {
      $('#memberSearchModal').removeClass('hidden');
      if (!$.fn.DataTable.isDataTable('#searchMemberTable')) {
        memberSearchTable = $('#searchMemberTable').DataTable({
          "ajax": { "url": `${SEARCH_MEMBER_API}?action=get_all_members`, "dataSrc": "" },
          "columns": [
            { "data": "code", "className": "font-mono text-blue-600 font-bold" },
            { "data": "name" },
            { "data": "points", "className": "text-center text-green-600 font-bold", "render": $.fn.dataTable.render.number(',', '.', 0, '') },
            { "data": null, "className": "text-center", "render": function(data, type, row) {
                const rowData = encodeURIComponent(JSON.stringify(row));
                return `<button onclick="selectMemberFromSearch('${rowData}')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-sm"><i class="fas fa-check"></i> เลือก</button>`;
              }}
          ],
          "pageLength": 10, "lengthChange": false
        });
      } else { memberSearchTable.ajax.reload(); }
    }

    function selectMemberFromSearch(encodedData) {
      try {
        const member = JSON.parse(decodeURIComponent(encodedData));
        currentMember = member;
        $('#memberDisplayName').html(`<i class="fas fa-check-circle"></i> ${currentMember.name} <span class="text-sm font-normal text-gray-600">(${currentMember.points} แต้ม)</span>`).removeClass('text-gray-800').addClass('text-green-600');
        $('#memberInput').val(''); alertify.success(`สมาชิก: ${currentMember.name}`);
        $('#memberSearchModal').addClass('hidden'); $('#barcodeInput').focus();
      } catch(e) { console.error(e); }
    }

    $('#memberInput').on('keydown', function(e) { if (e.which === 13) { e.preventDefault(); findMember(); }});
    async function findMember() {
      const keyword = $('#memberInput').val().trim(); if(!keyword) return;
      try {
        const res = await fetch(`${API_URL}?action=get_member&keyword=${keyword}`); const result = await res.json();
        if(result.found) {
          currentMember = result.data;
          $('#memberDisplayName').html(`<i class="fas fa-check-circle"></i> ${currentMember.name} <span class="text-sm font-normal text-gray-600">(${currentMember.points} แต้ม)</span>`).removeClass('text-gray-800').addClass('text-green-600');
          $('#memberInput').val(''); alertify.success(`สมาชิก: ${currentMember.name}`); $('#barcodeInput').focus();
        } else { alertify.error('ไม่พบข้อมูลสมาชิก'); $('#memberInput').select(); }
      } catch(e) {}
    }
    function resetMember() { currentMember = null; $('#memberDisplayName').text("ลูกค้าทั่วไป (Guest)").removeClass('text-green-600').addClass('text-gray-800'); $('#memberInput').val(''); $('#barcodeInput').focus(); }

    // --- PRODUCT SEARCH ---
    function openProductSearchModal() {
      $('#productSearchModal').removeClass('hidden');
      if (!$.fn.DataTable.isDataTable('#searchProductTable')) {
        productSearchTable = $('#searchProductTable').DataTable({
          "ajax": { "url": `${SEARCH_PRODUCT_API}?action=get_all_products`, "dataSrc": "" },
          "columns": [
            { "data": "barcode", "className": "font-mono text-blue-600 font-bold" },
            { "data": "name" },
            { "data": "price", "className": "text-right font-bold text-green-600", "render": $.fn.dataTable.render.number(',', '.', 2, '') },
            { "data": "quantity", "className": "text-center", "render": function(data) { return data > 0 ? `<span class="text-gray-700">${data}</span>` : `<span class="text-red-500 font-bold">หมด</span>`; }},
            { "data": null, "className": "text-center", "render": function(data, type, row) {
                const rowData = encodeURIComponent(JSON.stringify(row));
                return `<button onclick="selectProductFromSearch('${rowData}')" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded shadow text-sm"><i class="fas fa-plus"></i> เลือก</button>`;
              }}
          ],
          "pageLength": 10, "lengthChange": false
        });
      } else { productSearchTable.ajax.reload(); }
    }

    function selectProductFromSearch(encodedData) {
      try { const product = JSON.parse(decodeURIComponent(encodedData)); addToCart(product); $('#productSearchModal').addClass('hidden'); $('#barcodeInput').focus(); } catch(e) {}
    }

    $('#barcodeInput').on('keydown', function(e) { if (e.which === 13) { e.preventDefault(); triggerSearchProduct(); } if (e.key === 'F3') { e.preventDefault(); openProductSearchModal(); }});
    async function triggerSearchProduct() {
      const barcode = $('#barcodeInput').val().trim(); if (barcode === "") { $('#barcodeInput').focus(); return; }
      try {
        const res = await fetch(`${API_URL}?action=get_product&barcode=${barcode}`); const result = await res.json();
        if (result.found) { addToCart(result.data); $('#barcodeInput').val(''); }
        else { alertify.warning(`ไม่พบรหัส: ${barcode}`); $('#barcodeInput').val(''); }
      } catch(e) {} $('#barcodeInput').focus();
    }

    // --- CART LOGIC ---
    function addToCart(p) { const price = parseFloat(p.price); const exist = cart.find(i => i.id === p.id); if(exist) exist.qty++; else cart.push({...p, price: price, qty: 1}); renderCart(); alertify.success(`+ ${p.name}`); }

    function renderCart() {
      const tbody = $('#cartTable'); tbody.empty(); let total = 0, qty = 0;
      if(cart.length > 0) $('#emptyCart').addClass('hidden'); else $('#emptyCart').removeClass('hidden');
      cart.forEach((item, idx) => {
        const sum = item.price * item.qty; total += sum; qty += item.qty;
        tbody.append(`
            <tr class="hover:bg-blue-50 transition border-b group">
                <td class="py-3 px-4"><div class="font-bold text-gray-700">${item.name}</div><div class="text-xs text-gray-400 font-mono">${item.barcode || ''}</div></td>
                <td class="py-3 px-4 text-right">${item.price.toFixed(2)}</td>
                <td class="py-3 px-4 text-center">
                    <div class="inline-flex items-center border rounded-lg bg-white shadow-sm">
                        <button onclick="changeQty(${idx}, -1)" class="px-2 py-1 text-gray-500 hover:text-red-500 hover:bg-gray-100 rounded-l">-</button>
                        <span class="px-3 py-1 font-bold text-gray-700 min-w-[30px]">${item.qty}</span>
                        <button onclick="changeQty(${idx}, 1)" class="px-2 py-1 text-gray-500 hover:text-green-500 hover:bg-gray-100 rounded-r">+</button>
                    </div>
                </td>
                <td class="py-3 px-4 text-right font-bold text-blue-600">${sum.toFixed(2)}</td>
                <td class="py-3 px-4 text-center"><button onclick="remove(${idx})" class="text-red-300 hover:text-red-600 bg-red-50 hover:bg-red-100 p-2 rounded-full opacity-0 group-hover:opacity-100"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`);
      });
      $('#grandTotal').text(total.toLocaleString('th-TH', {minimumFractionDigits: 2})); $('#totalItems').text(qty);
    }

    function changeQty(idx, amount) {
      if(cart[idx].qty + amount > 0) { cart[idx].qty += amount; renderCart(); $('#barcodeInput').focus(); }
      else {
        alertify.confirm("ลบรายการ", "ยืนยันลบรายการนี้?", function(){ cart.splice(idx, 1); renderCart(); $('#barcodeInput').focus(); }, function(){}).set('labels', {ok:'ลบสินค้า', cancel:'ยกเลิก'});
      }
    }
    function remove(idx) { cart.splice(idx, 1); renderCart(); $('#barcodeInput').focus(); }

    // --- ORDER LOGIC ---
    function submitOrder() {
      if(cart.length === 0) return alertify.alert('แจ้งเตือน', 'ไม่มีสินค้าในตะกร้า');
      alertify.confirm('ยืนยันการขาย', 'ต้องการบันทึกยอดขายหรือไม่?', async function(){ await processOrder(); }, function(){} ).set('labels', {ok:'บันทึกขาย', cancel:'ยกเลิก'});
    }

    async function processOrder() {
      const payload = { cashier: currentUserData ? currentUserData.fullname : 'Unknown', total: parseFloat($('#grandTotal').text().replace(/,/g,'')), items: cart, member_id: currentMember ? currentMember.id : null };
      try {
        const res = await fetch(`${API_URL}?action=save_order`, {method:'POST', body:JSON.stringify(payload)});
        const result = await res.json();
        if(result.success) {
          alertify.confirm('บันทึกสำเร็จ', `เลขที่เอกสาร: <b>${result.docId}</b><br>ต้องการพิมพ์ใบเสร็จหรือไม่?`,
            function() { window.open(`receipt.php?doc_id=${result.docId}`, 'Receipt', 'width=350,height=600,scrollbars=yes'); clearScreen(); },
            function() { clearScreen(); }
          ).set('labels', {ok:'พิมพ์ใบเสร็จ', cancel:'ไม่พิมพ์'});
        } else { alertify.alert('ข้อผิดพลาด', result.message); }
      } catch(e) { alertify.error('Error'); }
    }

    // --- HELD BILL LOGIC ---
    function holdBill() {
      if(cart.length === 0) return alertify.alert('แจ้งเตือน', 'ไม่มีรายการสินค้าให้พัก');
      alertify.prompt( 'พักบิลชั่วคราว', 'ระบุชื่อลูกค้า หรือ หมายเหตุ:', 'ลูกค้าทั่วไป',
        async function(evt, value) {
          try {
            const payload = { note: value, items: cart, total: parseFloat($('#grandTotal').text().replace(/,/g,'')) };
            const res = await fetch(`${API_URL}?action=hold_bill`, { method: 'POST', body: JSON.stringify(payload) });
            const result = await res.json();
            if(result.success) { alertify.success('พักบิลเรียบร้อย'); clearScreen(); updateHeldBillCount(); }
            else { alertify.error('เกิดข้อผิดพลาดในการพักบิล'); }
          } catch(e) { console.error(e); }
        }, function() { }
      ).set('labels', {ok:'ยืนยัน', cancel:'ยกเลิก'});
    }

    async function showHeldBills() {
      try {
        const res = await fetch(`${API_URL}?action=get_held_bills`);
        const bills = await res.json();
        const tbody = $('#heldBillsList');
        tbody.empty();
        if(bills.length === 0) { tbody.html('<tr><td colspan="4" class="text-center p-8 text-gray-400">ไม่มีบิลที่พักไว้</td></tr>'); }
        else {
          bills.forEach(b => {
            const time = new Date(b.created_at).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
            tbody.append(`
              <tr class="hover:bg-purple-50 transition border-b">
                  <td class="p-4 text-gray-600 font-mono"><span class="bg-gray-100 px-2 py-1 rounded border">${time}</span></td>
                  <td class="p-4 font-bold text-gray-800">${b.reference_note}</td>
                  <td class="p-4 text-right font-bold text-purple-600">${parseFloat(b.total_amount).toLocaleString('th-TH', {minimumFractionDigits:2})}</td>
                  <td class="p-4 text-center space-x-2">
                      <button onclick='restoreBill(${JSON.stringify(b)})' class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg shadow font-bold text-sm transition"><i class="fas fa-play mr-1"></i> เรียกคืน</button>
                      <button onclick="deleteHeldBill(${b.id})" class="bg-red-100 hover:bg-red-200 text-red-600 px-3 py-2 rounded-lg shadow font-bold text-sm transition"><i class="fas fa-trash-alt"></i></button>
                  </td>
              </tr>`);
          });
        }
        $('#heldBillsModal').removeClass('hidden');
      } catch(e) {}
    }

    // 2. ปรับ RestoreBill ให้รองรับ Alertify
    async function restoreBill(bill) {
      const processRestore = async () => {
        let items = (typeof bill.items === 'string') ? JSON.parse(bill.items) : bill.items;
        cart = items; renderCart();
        await deleteHeldBill(bill.id, false); // ลบแบบไม่ต้องยืนยันซ้ำ
        $('#heldBillsModal').addClass('hidden');
        alertify.success(`เรียกคืนบิล: ${bill.reference_note || ''}`);
        updateHeldBillCount();
      };

      if(cart.length > 0) {
        alertify.confirm('แจ้งเตือน', 'มีรายการสินค้าค้างอยู่ในตะกร้า ต้องการเคลียร์ทิ้งและเรียกบิลนี้มาแทนหรือไม่?',
          function() { processRestore(); }, function() {}
        ).set('labels', {ok:'เรียกคืน', cancel:'ยกเลิก'});
      } else {
        await processRestore();
      }
    }

    // 3. ปรับ DeleteHeldBill ให้รองรับ Alertify
    async function deleteHeldBill(id, refresh = true) {
      const processDelete = async () => {
        try {
          await fetch(`${API_URL}?action=delete_held_bill`, { method: 'POST', body: JSON.stringify({id: id}) });
          if(refresh) { showHeldBills(); alertify.success('ลบบิลเรียบร้อยแล้ว'); }
          updateHeldBillCount();
        } catch(e) { console.error(e); }
      };

      if(refresh) {
        alertify.confirm('ยืนยันการลบ', 'ต้องการลบบิลนี้ทิ้งใช่หรือไม่?',
          function() { processDelete(); }, function() {}
        ).set('labels', {ok:'ลบข้อมูล', cancel:'ยกเลิก'});
      } else {
        await processDelete();
      }
    }

    async function updateHeldBillCount() {
      try { const res = await fetch(`${API_URL}?action=get_held_bills`); const bills = await res.json(); const count = bills.length; if(count > 0) { $('#heldBillCount').text(count).removeClass('hidden'); } else { $('#heldBillCount').addClass('hidden'); } } catch(e) {}
    }

    function clearScreen() { cart = []; resetMember(); renderCart(); $('#barcodeInput').focus(); alertify.success('พร้อมขายรายการต่อไป'); }

    $(document).keydown(function(e) { if(e.key === 'F2') { e.preventDefault(); $('#memberInput').focus(); } if(e.key === 'F4') { e.preventDefault(); $('#barcodeInput').focus(); }});
  </script>

<?php include 'layouts/footer.php'; ?>
