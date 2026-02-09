<?php include 'layouts/header.php'; ?>

<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<style>
  .alertify-notifier .ajs-message.ajs-success { background-color: #10b981; color: white; }
  .alertify-notifier .ajs-message.ajs-error { background-color: #ef4444; color: white; }
  .alertify-notifier .ajs-message.ajs-warning { background-color: #f59e0b; color: white; }
</style>

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
            <th width="8%" class="text-center">ลำดับ</th>
            <th width="10%" class="text-center">ไอคอน</th>
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
            <input type="text" id="inpIcon" class="flex-1 border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="fa-box">
            <div id="iconPreview" class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center text-gray-600 border"><i class="fas fa-question"></i></div>
          </div>
          <p class="text-xs text-gray-400 mt-1">ไม่ต้องใส่ fa- นำหน้า (เช่น box, user, home)</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">สิทธิ์การมองเห็น (Allowed Roles)</label>
          <div id="roleContainer" class="flex gap-4 flex-wrap bg-gray-50 p-3 rounded border border-gray-200">
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

  // ==========================================
  // 1. CONFIGURATION: กำหนด Role ทั้งหมดที่นี่
  // ==========================================
  const ROLE_CONFIG = {
    'admin': {
      label: 'Admin',
      class: 'bg-red-100 text-red-700 border border-red-200'
    },
    'staff': {
      label: 'Staff',
      class: 'bg-green-100 text-green-700 border border-green-200'
    },
    // ตัวอย่าง: ถ้าอนาคตมี Role ผู้จัดการเพิ่มมา ก็แค่ uncomment บรรทัดนี้
    'manager': { label: 'Manager', class: 'bg-purple-100 text-purple-700 border border-purple-200' },
  };

  // ค่า Default กรณี Role ในฐานข้อมูลไม่ตรงกับ Config
  const UNKNOWN_ROLE_STYLE = 'bg-gray-100 text-gray-600 border border-gray-200';

  // ตั้งค่า Alertify Defaults
  alertify.defaults.transition = "zoom";
  alertify.defaults.theme.ok = "ui positive button";
  alertify.defaults.theme.cancel = "ui black button";

  $(document).ready(function() {

    // สร้าง Checkbox ใน Modal ตาม Config
    renderRoleCheckboxes();

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
        },
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

            // Dynamic Rendering based on ROLE_CONFIG
            return data.split(',').map(r => {
              const roleKey = r.trim();
              const config = ROLE_CONFIG[roleKey]; // ค้นหาใน Config

              if (config) {
                // เจอใน Config ใช้สีที่ตั้งไว้
                return `<span class="${config.class} px-2 py-0.5 rounded text-xs mr-1 font-bold shadow-sm">${config.label}</span>`;
              } else {
                // ไม่เจอ ใช้สี Default
                return `<span class="${UNKNOWN_ROLE_STYLE} px-2 py-0.5 rounded text-xs mr-1">${roleKey}</span>`;
              }
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
      "order": [[ 1, "asc" ]]
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

  // ฟังก์ชันสร้าง Checkbox อัตโนมัติใน Modal
  function renderRoleCheckboxes() {
    const container = $('#roleContainer');
    container.empty();

    for (const [key, value] of Object.entries(ROLE_CONFIG)) {
      const html = `
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input type="checkbox" class="role-check w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500" value="${key}">
              <span class="text-sm text-gray-700">${value.label}</span>
            </label>
          `;
      container.append(html);
    }
  }

  function openModal() {
    $('#modalTitle').text('เพิ่มเมนูใหม่');
    $('#menuId').val('');
    $('#inpMenuId').val('');
    $('#inpName').val('');
    $('#inpLink').val('');
    $('#inpIcon').val('');
    $('#iconPreview').html('<i class="fas fa-circle"></i>');

    // Default checked all roles
    $('.role-check').prop('checked', true);

    $('#menuModal').removeClass('hidden');
  }

  function editMenu(menu) {
    $('#modalTitle').text('แก้ไขเมนู');
    $('#menuId').val(menu.id);
    $('#inpMenuId').val(menu.menu_id);
    $('#inpName').val(menu.name);
    $('#inpLink').val(menu.link);
    $('#inpIcon').val(menu.icon);
    $('#iconPreview').html(`<i class="fas ${menu.icon}"></i>`);

    // Reset checkboxes
    $('.role-check').prop('checked', false);

    // Check boxes based on data
    if(menu.allowed_roles) {
      let roles = menu.allowed_roles.split(',').map(r => r.trim());
      roles.forEach(r => {
        $(`.role-check[value="${r}"]`).prop('checked', true);
      });
    }

    $('#menuModal').removeClass('hidden');
  }

  async function saveMenu() {
    // Collect roles
    let selectedRoles = [];
    $('.role-check:checked').each(function() {
      selectedRoles.push($(this).val());
    });

    const payload = {
      id: $('#menuId').val(),
      menu_id: $('#inpMenuId').val(),
      name: $('#inpName').val().trim(),
      link: $('#inpLink').val().trim(),
      icon: $('#inpIcon').val().trim(),
      allowed_roles: selectedRoles
    };

    // Validation
    if(!payload.name || !payload.link || !payload.icon) {
      alertify.error('กรุณากรอกข้อมูล ชื่อเมนู, ลิงก์ และ ไอคอน ให้ครบ');
      return;
    }

    if(payload.allowed_roles.length === 0) {
      alertify.warning('กรุณาเลือกสิทธิ์การใช้งานอย่างน้อย 1 รายการ');
      return;
    }

    try {
      const res = await fetch(`${API_URL}?action=save_menu`, {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        //alertify.success('✅ ' + result.message);
        alertify.success(result.message);
        $('#menuModal').addClass('hidden');
        table.ajax.reload();

        // Refresh Sidebar (if function exists)
        if(typeof loadMenus === 'function' && typeof currentUserData !== 'undefined') {
          loadMenus(currentUserData.role);
        }
      } else {
        alertify.error('❌ Error: ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อ Server');
    }
  }

  function deleteMenu(id) {
    // Alertify Confirm
    alertify.confirm('ยืนยันการลบ', 'คุณต้องการลบเมนูนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้',
      async function() { // OK Callback
        try {
          const res = await fetch(`${API_URL}?action=delete_menu`, {
            method: 'POST',
            body: JSON.stringify({id})
          });
          const result = await res.json();

          if(result.success) {
            alertify.success('ลบข้อมูลเรียบร้อยแล้ว');
            table.ajax.reload();
            if(typeof loadMenus === 'function' && typeof currentUserData !== 'undefined') loadMenus(currentUserData.role);
          } else {
            alertify.error('ลบไม่สำเร็จ: ' + (result.message || ''));
          }
        } catch(e) {
          alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
      },
      function() { // Cancel Callback
        alertify.message('ยกเลิกการลบ');
      }
    ).set('labels', {ok:'ลบข้อมูล', cancel:'ยกเลิก'});
  }
</script>
