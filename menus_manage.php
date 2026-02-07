<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-list text-blue-500 mr-2"></i> จัดการเมนู (Menu Setting)
        </h2>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
          <i class="fas fa-plus"></i> เพิ่มเมนูใหม่
        </button>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="menuTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th width="5%">ID</th>
            <th width="8%" class="text-center">ลำดับ</th> <th width="10%" class="text-center">ไอคอน</th>
            <th>ชื่อเมนู</th>
            <th>ลิงก์ไฟล์ (Link)</th>
            <th>สิทธิ์เข้าถึง (Roles)</th>
            <th class="text-center" width="15%">จัดการ</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="menuModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-700">เพิ่มเมนู</h3>
        <button id="btnCloseModal" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 space-y-4">
        <input type="hidden" id="menuId">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับการแสดงผล (Sort Order)</label>
          <input type="number" id="inpMenuId" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ตัวเลข เช่น 1, 2, 3">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อเมนู (Display Name)</label>
          <input type="text" id="inpName" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น สต็อกสินค้า">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อไฟล์ (Link URL)</label>
          <input type="text" id="inpLink" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น products.php">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ไอคอน (FontAwesome Class)</label>
          <div class="flex gap-2">
            <input type="text" id="inpIcon" class="flex-1 border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น fa-box">
            <div id="iconPreview" class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center text-gray-600 border"><i class="fas fa-question"></i></div>
          </div>
          <p class="text-xs text-gray-400 mt-1">ใช้ชื่อ Class จาก FontAwesome 5 (ไม่ต้องใส่ fa-)</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">สิทธิ์การมองเห็น (Allowed Roles)</label>
          <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" class="role-check w-4 h-4" value="admin">
              <span class="text-sm">Admin</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" class="role-check w-4 h-4" value="staff">
              <span class="text-sm">Staff</span>
            </label>
          </div>
        </div>
      </div>

      <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
        <button id="btnCancel" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded">ยกเลิก</button>
        <button onclick="saveMenu()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">บันทึก</button>
      </div>
    </div>
  </div>

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;
  const API_URL = 'api/menu_api.php';

  $(document).ready(function() {

    // Init DataTables
    table = $('#menuTable').DataTable({
      "ajax": {
        "url": `${API_URL}?action=get_menus_list`,
        "dataSrc": ""
      },
      "columns": [
        { "data": "id" },
        {
          "data": "menu_id",
          "className": "text-center font-bold text-blue-600"
        }, // เพิ่ม column menu_id
        {
          "data": "icon",
          "className": "text-center text-blue-500 text-lg",
          "render": function(data) {
            return `<i class="fas ${data}"></i>`;
          }
        },
        { "data": "name", "className": "font-bold" },
        { "data": "link", "className": "text-gray-500 font-mono text-xs" },
        {
          "data": "allowed_roles",
          "render": function(data) {
            if(!data) return '-';
            return data.split(',').map(r => {
              if(r.trim() === 'admin') return '<span class="bg-red-100 text-red-600 px-2 py-0.5 rounded text-xs mr-1">Admin</span>';
              if(r.trim() === 'staff') return '<span class="bg-green-100 text-green-600 px-2 py-0.5 rounded text-xs mr-1">Staff</span>';
              return `<span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs mr-1">${r}</span>`;
            }).join('');
          }
        },
        {
          "data": null,
          "className": "text-center",
          "render": function(data, type, row) {
            let jsonRow = JSON.stringify(row).replace(/"/g, '&quot;');
            return `
                            <button onclick="editMenu(${jsonRow})" class="text-yellow-500 hover:text-yellow-600 mx-1 p-1" title="แก้ไข"><i class="fas fa-edit"></i></button>
                            <button onclick="deleteMenu(${row.id})" class="text-red-500 hover:text-red-600 mx-1 p-1" title="ลบ"><i class="fas fa-trash"></i></button>
                        `;
          }
        }
      ],
      "order": [[ 1, "asc" ]] // ตั้งค่าให้เรียงตาม Column ที่ 1 (menu_id) เป็นค่าเริ่มต้น
    });

    // Icon Preview Realtime
    $('#inpIcon').on('input', function() {
      let val = $(this).val();
      $('#iconPreview').html(`<i class="fas ${val}"></i>`);
    });

    // Close Modal Events
    $('#btnCloseModal, #btnCancel').click(function() {
      $('#menuModal').addClass('hidden');
    });
    $('#menuModal').on('click', function(e) {
      if (e.target === this) $('#menuModal').addClass('hidden');
    });
  });

  function openModal() {
    $('#modalTitle').text('เพิ่มเมนูใหม่');
    $('#menuId').val('');
    $('#inpMenuId').val(''); // เคลียร์ค่าลำดับ
    $('#inpName').val('');
    $('#inpLink').val('');
    $('#inpIcon').val('fa-circle');
    $('#iconPreview').html('<i class="fas fa-circle"></i>');

    $('.role-check').prop('checked', true);
    $('#menuModal').removeClass('hidden');
  }

  function editMenu(menu) {
    $('#modalTitle').text('แก้ไขเมนู');
    $('#menuId').val(menu.id);
    $('#inpMenuId').val(menu.menu_id); // ใส่ค่าลำดับเดิม
    $('#inpName').val(menu.name);
    $('#inpLink').val(menu.link);
    $('#inpIcon').val(menu.icon);
    $('#iconPreview').html(`<i class="fas ${menu.icon}"></i>`);

    $('.role-check').prop('checked', false);
    let roles = menu.allowed_roles.split(',').map(r => r.trim());
    roles.forEach(r => {
      $(`.role-check[value="${r}"]`).prop('checked', true);
    });

    $('#menuModal').removeClass('hidden');
  }

  async function saveMenu() {
    let selectedRoles = [];
    $('.role-check:checked').each(function() {
      selectedRoles.push($(this).val());
    });

    const payload = {
      id: $('#menuId').val(),
      menu_id: $('#inpMenuId').val(), // ส่งค่า menu_id ไปด้วย
      name: $('#inpName').val().trim(),
      link: $('#inpLink').val().trim(),
      icon: $('#inpIcon').val().trim(),
      allowed_roles: selectedRoles
    };

    if(!payload.name || !payload.link || !payload.icon) {
      return alert('กรุณากรอกข้อมูลให้ครบ');
    }

    if(payload.allowed_roles.length === 0) {
      return alert('กรุณาเลือกสิทธิ์อย่างน้อย 1 รายการ');
    }

    try {
      const res = await fetch(`${API_URL}?action=save_menu`, {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        alert('✅ ' + result.message);
        $('#menuModal').addClass('hidden');
        table.ajax.reload();

        // รีโหลด Sidebar ทันที (ถ้าฟังก์ชันมีอยู่จริง)
        if(typeof loadMenus === 'function' && typeof currentUserData !== 'undefined') {
          loadMenus(currentUserData.role);
        }
      } else {
        alert('❌ Error: ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  }

  async function deleteMenu(id) {
    if(!confirm('ยืนยันลบเมนูนี้?')) return;
    try {
      const res = await fetch(`${API_URL}?action=delete_menu`, {
        method: 'POST',
        body: JSON.stringify({id})
      });
      const result = await res.json();

      if(result.success) {
        table.ajax.reload();
        if(typeof loadMenus === 'function' && typeof currentUserData !== 'undefined') loadMenus(currentUserData.role);
      } else {
        alert('ลบไม่สำเร็จ: ' + (result.message || ''));
      }
    } catch(e) { alert('Error'); }
  }
</script>
