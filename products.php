<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-box text-blue-500 mr-2"></i> จัดการสินค้า (Stock)
        </h2>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
          <i class="fas fa-plus"></i> เพิ่มสินค้าใหม่
        </button>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="productsTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th width="15%">Barcode</th>
            <th>ชื่อสินค้า</th>
            <th width="15%">หมวดหมู่</th> <th class="text-right" width="10%">ทุน</th>
            <th class="text-right" width="10%">ราคาขาย</th>
            <th class="text-center" width="10%">คงเหลือ</th>
            <th class="text-center" width="10%">Min/Max</th>
            <th class="text-center" width="10%">จัดการ</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="productModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-[550px] overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-700">เพิ่มสินค้า</h3>
        <button id="btnCloseModal" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
        <input type="hidden" id="prodId">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสบาร์โค้ด (Barcode)</label>
          <div class="flex gap-2">
            <input type="text" id="inpBarcode" class="flex-1 border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="สแกนหรือพิมพ์รหัส">
            <button onclick="genRandomBarcode()" class="bg-gray-200 hover:bg-gray-300 text-gray-600 px-3 rounded text-sm transition" title="สุ่มรหัสบาร์โค้ด"><i class="fas fa-random"></i></button>
          </div>
        </div>

        <div class="flex justify-center bg-gray-50 border border-gray-200 rounded p-2 h-20 items-center overflow-hidden">
          <svg id="barcodePreview" class="hidden"></svg>
          <span id="barcodePlaceholder" class="text-gray-400 text-xs">ภาพบาร์โค้ดจะปรากฏที่นี่</span>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อสินค้า</label>
          <input type="text" id="inpName" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ระบุชื่อสินค้า">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">หมวดหมู่สินค้า</label>
          <select id="inpCategory" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="">-- ไม่ระบุ --</option>
          </select>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ราคาทุน (Cost)</label>
            <input type="number" step="0.01" id="inpCost" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-right" placeholder="0.00">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ราคาขาย (Price)</label>
            <input type="number" step="0.01" id="inpPrice" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-right" placeholder="0.00">
          </div>
        </div>

        <div class="border-t border-gray-100 pt-2 mt-2">
          <label class="block text-sm font-bold text-gray-700 mb-2">การจัดการสต็อก</label>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="block text-xs text-gray-500 mb-1">คงเหลือ (Qty)</label>
              <input type="number" id="inpQuantity" class="w-full border border-blue-300 bg-blue-50 px-3 py-2 rounded text-right font-bold text-blue-700" placeholder="0">
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">ขั้นต่ำ (Min)</label>
              <input type="number" id="inpMin" class="w-full border border-gray-300 px-3 py-2 rounded text-right" placeholder="0">
            </div>
            <div>
              <label class="block text-xs text-gray-500 mb-1">สูงสุด (Max)</label>
              <input type="number" id="inpMax" class="w-full border border-gray-300 px-3 py-2 rounded text-right" placeholder="0">
            </div>
          </div>
        </div>

      </div>

      <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
        <button id="btnCancel" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded">ยกเลิก</button>
        <button onclick="saveProduct()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">บันทึก</button>
      </div>
    </div>
  </div>

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;
  const API_URL = 'api/products_api.php';
  const CAT_API_URL = 'api/categories_api.php'; // API หมวดหมู่

  $(document).ready(function() {

    // 1. โหลดรายชื่อหมวดหมู่ใส่ Dropdown
    loadCategories();

    // 2. Init DataTables
    table = $('#productsTable').DataTable({
      "ajax": {
        "url": `${API_URL}?action=get_products`,
        "dataSrc": ""
      },
      "columns": [
        {
          "data": "barcode",
          "defaultContent": "-",
          "className": "font-mono font-bold text-blue-600",
          "render": function(data) {
            return `<i class="fas fa-barcode text-gray-400 mr-1"></i> ${data}`;
          }
        },
        {
          "data": "name",
          "defaultContent": "ไม่ระบุชื่อ"
        },
        {
          "data": "category_name", // แสดงชื่อหมวดหมู่จากที่ JOIN มา
          "defaultContent": "-",
          "className": "text-gray-600",
          "render": function(data) {
            // ถ้ามีหมวดหมู่ ใส่ Badge สีม่วง
            return data ? `<span class="bg-purple-100 text-purple-700 px-2 py-1 rounded text-xs">${data}</span>` : '<span class="text-gray-400">-</span>';
          }
        },
        {
          "data": "cost",
          "defaultContent": "0",
          "className": "text-right text-gray-500",
          "render": function(data) { return $.fn.dataTable.render.number(',', '.', 2, '').display(data || 0); }
        },
        {
          "data": "price",
          "defaultContent": "0",
          "className": "text-right font-bold text-green-600",
          "render": function(data) { return $.fn.dataTable.render.number(',', '.', 2, '').display(data || 0); }
        },
        {
          "data": "quantity",
          "defaultContent": "0",
          "className": "text-center font-bold",
          "render": function(data, type, row) {
            let qty = parseFloat(data || 0);
            let min = parseFloat(row.min || 0);
            // เช็คเงื่อนไขแสดงสี
            if(qty <= min && min > 0) return `<span class="text-red-600 bg-red-100 px-2 py-1 rounded text-xs animate-pulse">${qty}</span>`;
            if(qty <= 0) return `<span class="text-gray-400 bg-gray-100 px-2 py-1 rounded text-xs">หมด</span>`;
            return `<span class="text-gray-700 bg-gray-100 px-2 py-1 rounded text-xs">${qty}</span>`;
          }
        },
        {
          "data": null,
          "className": "text-center text-xs text-gray-400",
          "render": function(data, type, row) { return `${row.min || '-'} / ${row.max || '-'}`; }
        },
        {
          "data": null,
          "className": "text-center",
          "render": function(data, type, row) {
            return `
                <button class="btn-edit text-yellow-500 hover:text-yellow-600 mx-1 p-1" title="แก้ไข">
                    <i class="fas fa-edit fa-lg"></i>
                </button>
                <button class="btn-delete text-red-500 hover:text-red-600 mx-1 p-1" title="ลบ">
                    <i class="fas fa-trash fa-lg"></i>
                </button>
             `;
          }
        }
      ],
      "order": [[ 0, "desc" ]]
    });

    // 3. ปุ่มแก้ไข (Load ข้อมูลเข้า Modal)
    $('#productsTable tbody').on('click', '.btn-edit', function () {
      let tr = $(this).closest('tr');
      let row = table.row(tr);
      let data = row.data();

      $('#modalTitle').text('แก้ไขสินค้า');
      $('#prodId').val(data.id);
      $('#inpBarcode').val(data.barcode);
      $('#inpName').val(data.name);

      // เลือกหมวดหมู่ให้ตรงกับข้อมูลเดิม
      $('#inpCategory').val(data.category_id);

      $('#inpPrice').val(data.price || 0);
      $('#inpCost').val(data.cost || 0);
      $('#inpQuantity').val(parseFloat(data.quantity || 0));
      $('#inpMin').val(parseFloat(data.min || 0));
      $('#inpMax').val(parseFloat(data.max || 0));

      genBar(data.barcode);
      $('#productModal').removeClass('hidden');
    });

    // 4. ปุ่มลบ
    $('#productsTable tbody').on('click', '.btn-delete', function () {
      let tr = $(this).closest('tr');
      let row = table.row(tr);
      let data = row.data();
      if(confirm(`ยืนยันลบสินค้า "${data.name}" ?`)) { deleteProduct(data.id); }
    });

    // Event Handlers อื่นๆ
    $('#btnCloseModal, #btnCancel').click(function() { $('#productModal').addClass('hidden'); });
    $('#productModal').on('click', function(e) { if (e.target === this) $('#productModal').addClass('hidden'); });
    $('#inpBarcode').on('input', function() { genBar($(this).val()); });
  });

  // --- Functions ---

  // ฟังก์ชันโหลดหมวดหมู่
  async function loadCategories() {
    try {
      const res = await fetch(`${CAT_API_URL}?action=get_categories`);
      const data = await res.json();

      let options = '<option value="">-- ไม่ระบุ --</option>';
      data.forEach(cat => {
        // แสดงชื่อหมวดหมู่ (detail) และรหัส (categories) ในวงเล็บ
        options += `<option value="${cat.id}">${cat.detail} (${cat.categories})</option>`;
      });
      $('#inpCategory').html(options);
    } catch(e) { console.error('Load Categories Error:', e); }
  }

  // ฟังก์ชันสุ่มบาร์โค้ด
  function genRandomBarcode() {
    // สุ่มเลข 8 หลัก นำหน้าด้วย 885
    const random = Math.floor(Math.random() * 90000000) + 10000000;
    $('#inpBarcode').val('885' + random);
    genBar($('#inpBarcode').val());
  }

  // สร้าง Barcode SVG
  function genBar(code) {
    if(code && code.trim() !== "") {
      $('#barcodePlaceholder').addClass('hidden');
      $('#barcodePreview').removeClass('hidden');
      try {
        JsBarcode("#barcodePreview", code, { format: "CODE128", lineColor: "#000", width: 2, height: 40, displayValue: true, fontSize: 14, margin: 0 });
      } catch(e) {
        $('#barcodePreview').addClass('hidden');
        $('#barcodePlaceholder').removeClass('hidden').text('รหัสไม่ถูกต้อง');
      }
    } else {
      $('#barcodePreview').addClass('hidden');
      $('#barcodePlaceholder').removeClass('hidden').text('ภาพบาร์โค้ดจะปรากฏที่นี่');
    }
  }

  function openModal() {
    $('#modalTitle').text('เพิ่มสินค้าใหม่');
    $('#prodId').val('');
    $('#inpBarcode').val('');
    $('#inpName').val('');
    $('#inpCategory').val(''); // Reset หมวดหมู่
    $('#inpPrice').val('');
    $('#inpCost').val('');
    $('#inpQuantity').val('');
    $('#inpMin').val('');
    $('#inpMax').val('');

    $('#barcodePreview').addClass('hidden');
    $('#barcodePlaceholder').removeClass('hidden').text('ภาพบาร์โค้ดจะปรากฏที่นี่');

    $('#productModal').removeClass('hidden');
    setTimeout(() => $('#inpBarcode').focus(), 100);
  }

  async function saveProduct() {
    const payload = {
      id: $('#prodId').val(),
      barcode: $('#inpBarcode').val().trim(),
      name: $('#inpName').val().trim(),
      category_id: $('#inpCategory').val(), // ส่งค่า Category ID ไปด้วย
      price: $('#inpPrice').val().trim(),
      cost: $('#inpCost').val().trim(),
      quantity: $('#inpQuantity').val().trim(),
      min: $('#inpMin').val().trim(),
      max: $('#inpMax').val().trim()
    };

    if(!payload.barcode || !payload.name) return alert('กรุณากรอกชื่อและบาร์โค้ด');

    try {
      const res = await fetch(`${API_URL}?action=save_product`, {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        if(typeof alertify !== 'undefined') alertify.success(result.message);
        else alert('✅ ' + result.message);

        $('#productModal').addClass('hidden');
        table.ajax.reload();
      } else {
        alert('❌ ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alert('Connection Error');
    }
  }

  async function deleteProduct(id) {
    try {
      const res = await fetch(`${API_URL}?action=delete_product`, {
        method: 'POST',
        body: JSON.stringify({id})
      });
      const result = await res.json();
      if(result.success) table.ajax.reload();
      else alert('ลบไม่สำเร็จ');
    } catch(e) { alert('Error deleting product'); }
  }
</script>
