<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-tags text-purple-500 mr-2"></i> จัดการหมวดหมู่ (Categories)
        </h2>
        <button onclick="openModal()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
          <i class="fas fa-plus"></i> เพิ่มหมวดหมู่
        </button>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="categoriesTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th width="15%" class="text-center">รหัส (Code)</th>
            <th>ชื่อหมวดหมู่ (Name)</th>
            <th width="15%" class="text-center">จัดการ</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-700">เพิ่มหมวดหมู่</h3>
        <button onclick="closeModal()" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 space-y-4">
        <input type="hidden" id="catId">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสหมวดหมู่ <span class="text-gray-400">(Auto)</span></label>
          <input type="text" id="inpCode" class="w-full border border-gray-300 bg-gray-100 text-gray-500 px-3 py-2 rounded focus:outline-none cursor-not-allowed font-mono" placeholder="สร้างอัตโนมัติ (C-XXXX)" readonly>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อหมวดหมู่ <span class="text-red-500">*</span></label>
          <input type="text" id="inpName" maxlength="100" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="เช่น เครื่องดื่ม, ขนมขบเคี้ยว">
        </div>
      </div>

      <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
        <button onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded">ยกเลิก</button>
        <button onclick="saveCategory()" class="px-4 py-2 bg-purple-600 text-white hover:bg-purple-700 rounded shadow">บันทึก</button>
      </div>
    </div>
  </div>

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;
  const API_URL = 'api/categories_api.php';

  $(document).ready(function() {
    // 1. Init DataTables
    table = $('#categoriesTable').DataTable({
      "ajax": {
        "url": `${API_URL}?action=get_categories`,
        "dataSrc": ""
      },
      "columns": [
        {
          "data": "categories", // แสดงรหัส C-XXXX
          "className": "text-center font-mono font-bold text-purple-600"
        },
        {
          "data": "detail",     // แสดงชื่อหมวดหมู่
          "className": "font-bold text-gray-700"
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

    // 2. Edit Button
    $('#categoriesTable tbody').on('click', '.btn-edit', function () {
      let tr = $(this).closest('tr');
      let row = table.row(tr);
      let data = row.data();

      $('#modalTitle').text('แก้ไขหมวดหมู่');
      $('#catId').val(data.id);
      $('#inpCode').val(data.categories); // แสดงรหัส
      $('#inpName').val(data.detail);     // ชื่อหมวดหมู่

      $('#categoryModal').removeClass('hidden');
    });

    // 3. Delete Button
    $('#categoriesTable tbody').on('click', '.btn-delete', function () {
      let tr = $(this).closest('tr');
      let row = table.row(tr);
      let data = row.data();

      if(confirm(`ต้องการลบหมวดหมู่ "${data.detail}" (${data.categories}) หรือไม่?`)) {
        deleteCategory(data.id);
      }
    });

    // Close Modal on Click Outside
    $('#categoryModal').on('click', function(e) {
      if (e.target === this) closeModal();
    });
  });

  function openModal() {
    $('#modalTitle').text('เพิ่มหมวดหมู่');
    $('#catId').val('');
    $('#inpCode').val('สร้างอัตโนมัติ (C-XXXX)');
    $('#inpName').val('');
    $('#categoryModal').removeClass('hidden');
    setTimeout(() => $('#inpName').focus(), 100);
  }

  function closeModal() {
    $('#categoryModal').addClass('hidden');
  }

  async function saveCategory() {
    const payload = {
      id: $('#catId').val(),
      name: $('#inpName').val().trim() // ส่งชื่อไป (backend จะจัดการรหัสเอง)
    };

    if(!payload.name) return alert('กรุณาระบุชื่อหมวดหมู่');

    try {
      const res = await fetch(`${API_URL}?action=save_category`, {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        if(typeof alertify !== 'undefined') alertify.success(result.message);
        else alert(result.message);

        closeModal();
        table.ajax.reload();
      } else {
        alert('❌ ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alert('Connection Error');
    }
  }

  async function deleteCategory(id) {
    try {
      const res = await fetch(`${API_URL}?action=delete_category`, {
        method: 'POST',
        body: JSON.stringify({id})
      });
      const result = await res.json();

      if(result.success) {
        table.ajax.reload();
      } else {
        alert('ลบไม่สำเร็จ: ' + result.message);
      }
    } catch(e) {
      alert('Error deleting category');
    }
  }
</script>
