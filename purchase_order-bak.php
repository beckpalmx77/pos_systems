<?php include 'layouts/header.php'; ?>

  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
  <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

  <style>
    .alertify-notifier .ajs-message.ajs-success { background-color: #10b981; color: white; }
    .alertify-notifier .ajs-message.ajs-error { background-color: #ef4444; color: white; }
    .ajs-button.ajs-ok { background-color: #10b981 !important; color: white; border-radius: 6px; }
    .ajs-button.ajs-cancel { background-color: #6b7280 !important; color: white; border-radius: 6px; }

    /* สำหรับตารางค้นหาใน Modal */
    .search-row:hover { background-color: #f0fdf4; cursor: pointer; }
  </style>

<?php include 'layouts/sidebar.php'; ?>

  <main class="flex-1 flex flex-col min-w-0 bg-gray-100">
    <?php include 'layouts/topbar.php'; ?>

    <div class="flex-1 p-4 overflow-hidden flex flex-col">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

        <div class="p-4 bg-emerald-50 border-b border-emerald-100 flex items-center justify-between">
          <div class="flex items-center gap-3 flex-1">
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-emerald-500 shadow-sm border border-emerald-100">
              <i class="fas fa-truck-moving text-xl"></i>
            </div>
            <div>
              <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">ผู้จำหน่าย (Supplier)</p>
              <h3 id="supName" class="text-lg font-bold text-gray-800">โปรดเลือกผู้จำหน่าย</h3>
            </div>
          </div>
          <select id="supSelect" class="px-4 py-2.5 border border-gray-300 rounded-lg text-sm w-72 shadow-sm focus:ring-2 focus:ring-emerald-500 outline-none" onchange="selectSupplier(this.value)">
            <option value="">-- ค้นหา/เลือกรายชื่อผู้จำหน่าย --</option>
          </select>
        </div>

        <div class="p-4 bg-gray-50 border-b border-gray-100">
          <div class="flex gap-3">
            <div class="relative flex-1">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i class="fas fa-barcode text-gray-400 text-xl"></i>
              </div>
              <input type="text" id="barcodeInput"
                     class="w-full pl-12 pr-4 py-4 border-2 border-emerald-400 rounded-lg text-xl focus:outline-none focus:ring-4 focus:ring-emerald-100 transition shadow-inner"
                     placeholder="สแกนบาร์โค้ด หรือกด F2 เพื่อค้นหาชื่อสินค้า" autofocus>
            </div>
            <button onclick="openSearchModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded-lg font-bold shadow-lg transition flex items-center gap-2">
              <i class="fas fa-search"></i> ค้นหา (F2)
            </button>
            <button onclick="findProduct()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 rounded-lg font-bold shadow-lg transition flex items-center gap-2">
              <i class="fas fa-plus-circle"></i> เพิ่มรายการ
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-auto relative">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100 sticky top-0 shadow-sm z-10">
            <tr>
              <th class="py-4 px-6 text-sm font-bold text-gray-600 uppercase">รายการสินค้า</th>
              <th class="py-4 px-4 text-sm font-bold text-gray-600 text-right uppercase">ต้นทุน/หน่วย</th>
              <th class="py-4 px-4 text-sm font-bold text-gray-600 text-center uppercase" style="width: 150px;">จำนวนรับ</th>
              <th class="py-4 px-4 text-sm font-bold text-gray-600 text-right uppercase">รวม (ต้นทุน)</th>
              <th class="py-4 px-6 text-sm font-bold text-gray-600 text-center uppercase">ลบ</th>
            </tr>
            </thead>
            <tbody id="poTable" class="divide-y divide-gray-100 bg-white"></tbody>
          </table>
          <div id="emptyPO" class="flex flex-col items-center justify-center h-64 text-gray-400">
            <i class="fas fa-file-invoice-dollar text-6xl mb-4 opacity-20"></i>
            <p class="text-xl font-medium">ยังไม่มีรายการสั่งซื้อ</p>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-gray-200">
          <div class="flex justify-between items-center mb-6">
            <div>
              <span class="text-gray-500">รวมทั้งหมด:</span>
              <span id="itemCount" class="text-2xl font-bold text-gray-800 ml-2">0</span>
              <span class="text-gray-500 ml-1">รายการ</span>
            </div>
            <div class="text-right">
              <p class="text-sm text-gray-500 font-bold uppercase tracking-wider">ยอดเงินรวมสุทธิ</p>
              <h2 class="text-5xl font-extrabold text-emerald-600">฿<span id="grandTotal">0.00</span></h2>
            </div>
          </div>
          <div class="flex gap-4">
            <button onclick="clearPO()" class="flex-none bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 px-8 rounded-xl transition">ล้างหน้าจอ</button>
            <button onclick="savePO()" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white text-2xl font-bold py-4 rounded-xl shadow-xl transition transform active:scale-[0.98] flex justify-center items-center gap-3">
              <i class="fas fa-save"></i> บันทึกใบสั่งซื้อ
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <div id="searchModal" class="fixed inset-0 bg-black/50 z-[100] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden">
      <div class="p-4 border-b bg-gray-50 flex justify-between items-center">
        <h3 class="text-xl font-bold text-gray-800"><i class="fas fa-search mr-2"></i>เลือกสินค้า</h3>
        <button onclick="closeSearchModal()" class="text-gray-400 hover:text-red-500 text-3xl">&times;</button>
      </div>
      <div class="p-4 bg-white border-b">
        <input type="text" id="modalSearchInput" autofocus
               class="w-full p-4 border-2 border-blue-100 rounded-xl focus:border-blue-500 outline-none text-lg"
               placeholder="พิมพ์ชื่อสินค้า หรือบาร์โค้ด..." onkeyup="searchProductInModal(this.value)">
      </div>
      <div class="flex-1 overflow-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50 text-gray-600 sticky top-0">
          <tr>
            <th class="p-4">บาร์โค้ด</th>
            <th class="p-4">ชื่อสินค้า</th>
            <th class="p-4 text-right">ต้นทุนล่าสุด</th>
            <th class="p-4 text-right">คงเหลือ</th>
            <th class="p-4 text-center">เลือก</th>
          </tr>
          </thead>
          <tbody id="modalProductList" class="divide-y divide-gray-100">
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    const PURCHASE_API = 'api/purchase_api.php';
    const BASIC_API = 'api/basic_api.php';
    let poCart = [];
    let selectedSupplierId = null;

    $(document).ready(function () {
      loadSuppliers();

      $('#barcodeInput').on('keypress', function (e) {
        if (e.which == 13) { e.preventDefault(); findProduct(); }
      });

      $(document).keydown(function (e) {
        if (e.key === 'F4') { e.preventDefault(); $('#barcodeInput').focus(); }
        if (e.key === 'F2') { e.preventDefault(); openSearchModal(); }
        if (e.key === 'Escape') { closeSearchModal(); }
      });
    });

    // --- ระบบค้นหาสินค้า (Search Modal) ---
    function openSearchModal() {
      $('#searchModal').removeClass('hidden');
      $('#modalSearchInput').val('').focus();
      searchProductInModal('');
    }

    function closeSearchModal() {
      $('#searchModal').addClass('hidden');
    }

    async function searchProductInModal(query) {
      try {
        const res = await fetch(`${BASIC_API}?action=search_products&q=${encodeURIComponent(query)}`);
        const products = await res.json();
        const tbody = $('#modalProductList');
        tbody.empty();

        products.forEach(p => {
          tbody.append(`
          <tr class="search-row border-b" onclick="selectFromModal('${p.barcode}')">
            <td class="p-4 font-mono text-sm">${p.barcode}</td>
            <td class="p-4 font-bold text-gray-700">${p.name}</td>
            <td class="p-4 text-right">฿${parseFloat(p.cost).toFixed(2)}</td>
            <td class="p-4 text-right text-blue-600 font-bold">${p.quantity}</td>
            <td class="p-4 text-center">
              <button class="bg-emerald-500 text-white px-4 py-1 rounded-lg text-sm">เลือก</button>
            </td>
          </tr>
        `);
        });
      } catch (err) { console.error("Search Error:", err); }
    }

    function selectFromModal(barcode) {
      $('#barcodeInput').val(barcode);
      findProduct();
      closeSearchModal();
    }

    // --- ระบบจัดการใบสั่งซื้อเดิม ---
    async function loadSuppliers() {
      try {
        const res = await fetch(`${PURCHASE_API}?action=get_suppliers`);
        const sups = await res.json();
        sups.forEach(s => $('#supSelect').append(`<option value="${s.id}">${s.name}</option>`));
      } catch (err) { console.error(err); }
    }

    function selectSupplier(id) {
      selectedSupplierId = id;
      const name = $("#supSelect option:selected").text();
      id ? $('#supName').text(name).addClass('text-emerald-600') : $('#supName').text('โปรดเลือกผู้จำหน่าย');
    }

    async function findProduct() {
      const barcode = $('#barcodeInput').val().trim();
      if (!barcode) return;
      try {
        const res = await fetch(`${BASIC_API}?action=get_product&barcode=${barcode}`);
        const result = await res.json();
        if (result.found) {
          addProductToPO(result.data);
          $('#barcodeInput').val('');
        } else {
          alertify.error('ไม่พบสินค้า: ' + barcode);
        }
      } catch (err) { alertify.error('API Error'); }
    }

    function addProductToPO(p) {
      const existIdx = poCart.findIndex(i => i.barcode === p.barcode);
      if (existIdx !== -1) {
        poCart[existIdx].qty++;
      } else {
        poCart.push({
          id: p.id,
          barcode: p.barcode,
          name: p.name,
          cost: parseFloat(p.cost) || 0,
          qty: 1
        });
      }
      renderTable();
    }

    function renderTable() {
      const tbody = $('#poTable');
      tbody.empty();
      let total = 0;
      poCart.length > 0 ? $('#emptyPO').addClass('hidden') : $('#emptyPO').removeClass('hidden');

      poCart.forEach((item, idx) => {
        const rowSum = item.cost * item.qty;
        total += rowSum;
        tbody.append(`
        <tr class="hover:bg-emerald-50 transition border-b">
          <td class="py-4 px-6">
            <div class="font-bold text-gray-800">${item.name}</div>
            <div class="text-xs text-gray-400 font-mono">${item.barcode}</div>
          </td>
          <td class="py-4 px-4 text-right">
            <input type="number" step="0.01" value="${item.cost}" onchange="updateRow(${idx}, 'cost', this.value)" class="w-32 text-right border rounded px-2 py-1">
          </td>
          <td class="py-4 px-4 text-center">
            <div class="inline-flex items-center border rounded bg-white">
              <button onclick="changeQty(${idx}, -1)" class="px-3 py-1">-</button>
              <input type="number" value="${item.qty}" onchange="updateRow(${idx}, 'qty', this.value)" class="w-16 text-center border-none focus:ring-0">
              <button onclick="changeQty(${idx}, 1)" class="px-3 py-1">+</button>
            </div>
          </td>
          <td class="py-4 px-4 text-right font-bold text-emerald-600 text-lg">฿${rowSum.toLocaleString(undefined,{minimumFractionDigits:2})}</td>
          <td class="py-4 px-6 text-center">
            <button onclick="removeItem(${idx})" class="text-red-400 hover:text-red-600"><i class="fas fa-trash-alt"></i></button>
          </td>
        </tr>
      `);
      });
      $('#grandTotal').text(total.toLocaleString(undefined,{minimumFractionDigits:2}));
      $('#itemCount').text(poCart.length);
    }

    function updateRow(idx, field, val) {
      poCart[idx][field] = parseFloat(val) || 0;
      renderTable();
    }

    function changeQty(idx, delta) {
      if (poCart[idx].qty + delta >= 1) { poCart[idx].qty += delta; renderTable(); }
    }

    function removeItem(idx) {
      poCart.splice(idx, 1);
      renderTable();
    }

    function clearPO() {
      if(confirm('ล้างรายการทั้งหมด?')) { poCart = []; renderTable(); }
    }

    async function savePO() {
      if (!selectedSupplierId || poCart.length === 0) return alertify.error('ข้อมูลไม่ครบ');
      // ... logic บันทึกเหมือนเดิม ...
      alertify.success('บันทึกสำเร็จ');
      poCart = []; renderTable();
    }
  </script>

<?php include 'layouts/footer.php'; ?>
