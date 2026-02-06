<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

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
            <th class="text-right" width="15%">ราคา</th>
            <th class="text-center" width="15%">จัดการ</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="productModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-700">เพิ่มสินค้า</h3>
        <button id="btnCloseModal" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 space-y-4">
        <input type="hidden" id="prodId">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสบาร์โค้ด (Barcode)</label>
          <input type="text" id="inpBarcode" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="สแกนหรือพิมพ์รหัส">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อสินค้า</label>
          <input type="text" id="inpName" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ระบุชื่อสินค้า">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ราคาขาย</label>
          <input type="number" step="0.01" id="inpPrice" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-right" placeholder="0.00">
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
  // ชี้ไปที่ API ตัวใหม่
  const API_URL = 'api/products_api.php';

  $(document).ready(function() {

    // 1. Init DataTables
    table = $('#productsTable').DataTable({
      "ajax": {
        "url": `${API_URL}?action=get_products`,
        "dataSrc": ""
      },
      "columns": [
        {
          "data": "barcode",
          "className": "font-mono font-bold text-blue-600"
        },
        { "data": "name" },
        {
          "data": "price",
          "className": "text-right font-bold",
          "render": $.fn.dataTable.render.number(',', '.', 2, '')
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

    // 2. Event: กดปุ่มแก้ไข
    $('#productsTable tbody').on('click', '.btn-edit', function () {
      let tr = $(this).closest('tr');
      let row = table.row(tr);
      let data = row.data();

      // เปิด Modal พร้อมข้อมูลเก่า
      $('#modalTitle').text('แก้ไขสินค้า');
      $('#prodId').val(data.id);
      $('#inpBarcode').val(data.barcode);
      $('#inpName').val(data.name);
      $('#inpPrice').val(data.price);
      $('#productModal').removeClass('hidden');
    });

    // 3. Event: กดปุ่มลบ
    $('#productsTable tbody').on('click', '.btn-delete', function () {
      let tr = $(this).closest('tr');
      let row = table.row(tr);
      let data = row.data();

      if(confirm(`ยืนยันลบสินค้า "${data.name}" ?`)) {
        deleteProduct(data.id);
      }
    });

    // 4. ปุ่มปิด Modal
    $('#btnCloseModal, #btnCancel').click(function() {
      $('#productModal').addClass('hidden');
    });

    // คลิกพื้นหลังปิด
    $('#productModal').on('click', function(e) {
      if (e.target === this) $('#productModal').addClass('hidden');
    });
  });

  // ฟังก์ชันเปิด Modal เพิ่มใหม่
  function openModal() {
    $('#modalTitle').text('เพิ่มสินค้าใหม่');
    $('#prodId').val('');     // เคลียร์ ID
    $('#inpBarcode').val(''); // เคลียร์ช่องกรอก
    $('#inpName').val('');
    $('#inpPrice').val('');
    $('#productModal').removeClass('hidden');

    // Auto Focus ช่องบาร์โค้ด
    setTimeout(() => $('#inpBarcode').focus(), 100);
  }

  // ฟังก์ชันบันทึก
  async function saveProduct() {
    const payload = {
      id: $('#prodId').val(),
      barcode: $('#inpBarcode').val().trim(),
      name: $('#inpName').val().trim(),
      price: $('#inpPrice').val().trim()
    };

    if(!payload.barcode || !payload.name || !payload.price) {
      return alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
    }

    try {
      const res = await fetch(`${API_URL}?action=save_product`, {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        alert('✅ ' + result.message);
        $('#productModal').addClass('hidden');
        table.ajax.reload(); // รีโหลดตาราง
      } else {
        alert('❌ Error: ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  }

  // ฟังก์ชันลบ
  async function deleteProduct(id) {
    try {
      const res = await fetch(`${API_URL}?action=delete_product`, {
        method: 'POST',
        body: JSON.stringify({id})
      });
      const result = await res.json();

      if(result.success) {
        table.ajax.reload();
      } else {
        alert('ลบไม่สำเร็จ');
      }
    } catch(e) {
      alert('Error deleting product');
    }
  }
</script>
