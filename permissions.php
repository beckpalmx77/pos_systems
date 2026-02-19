<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-y-auto flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col max-w-4xl mx-auto w-full">

      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-user-lock text-blue-500 mr-2"></i> กำหนดสิทธิ์การใช้งาน (Permissions)
        </h2>
      </div>

      <div class="p-6">
        <div class="mb-6 flex items-center gap-4 bg-blue-50 p-4 rounded-lg border border-blue-100">
          <label class="font-bold text-gray-700">เลือกตำแหน่ง (Role):</label>
          <select id="selectRole" class="border border-gray-300 rounded px-3 py-2 w-48 focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
            <option value="staff">Staff (พนักงานขาย)</option>
            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
            <option value="manager">Manager (ผู้จัดการ)</option>
            <option value="customer">Customer (ลูกค้า)</option>
          </select>
          <span class="text-sm text-gray-500">* เลือกตำแหน่งเพื่อดูหรือแก้ไขสิทธิ์</span>
        </div>

        <div class="border rounded-lg overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100 text-gray-600 border-b">
            <tr>
              <th class="p-4 w-16 text-center">อนุญาต</th>
              <th class="p-4">ชื่อเมนู</th>
              <th class="p-4 text-gray-400">สิทธิ์ปัจจุบัน</th>
            </tr>
            </thead>
            <tbody id="permissionTableBody" class="divide-y">
            <tr><td colspan="3" class="p-4 text-center">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
          </table>
        </div>

        <div class="mt-6 flex justify-end">
          <button onclick="savePermissions()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow font-bold flex items-center gap-2 transition transform active:scale-95">
            <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<?php include 'layouts/footer.php'; ?>

<script>
  // [เปลี่ยน] ไปใช้ API ตัวใหม่ที่แยกออกมา
  const PERMISSION_API = 'api/permission_api.php';
  let allMenus = [];

  $(document).ready(function() {
    // เช็คสิทธิ์ Admin (ถ้าไม่ใช่ Admin ดีดออก)
    if(currentUserData && currentUserData.role !== 'admin'){
      alertify.alert('แจ้งเตือน', 'หน้านี้สำหรับ Admin เท่านั้น', function(){
        window.location.href = 'index.php';
      });
      return;
    }

    // เริ่มต้นโหลดข้อมูล
    fetchMenusAndRender();

    // เมื่อเปลี่ยน Role ใน Select ให้วาดตารางใหม่
    $('#selectRole').change(function() {
      renderTable();
    });
  });

  // 1. ดึงข้อมูลเมนูทั้งหมด
  async function fetchMenusAndRender() {
    try {
      const res = await fetch(`${PERMISSION_API}?action=get_permissions`);
      allMenus = await res.json();
      renderTable();
    } catch(e) {
      console.error(e);
      alertify.error('ไม่สามารถโหลดข้อมูลสิทธิ์ได้');
    }
  }

  // 2. วาดตารางตาม Role ที่เลือก
  function renderTable() {
    const targetRole = $('#selectRole').val(); // role ที่เลือกอยู่ (staff/admin)
    const tbody = $('#permissionTableBody');
    tbody.empty();

    if(allMenus.length === 0) {
      tbody.html('<tr><td colspan="3" class="p-4 text-center text-gray-400">ไม่มีข้อมูลเมนู</td></tr>');
      return;
    }

    allMenus.forEach(menu => {
      // แปลง "admin,staff" เป็น Array
      const allowedList = menu.allowed_roles ? menu.allowed_roles.split(',').map(r => r.trim()) : [];

      // เช็คว่า Role ที่เลือก มีสิทธิ์ในเมนูนี้ไหม
      const isChecked = allowedList.includes(targetRole) ? 'checked' : '';

      // สร้าง Badges แสดงสิทธิ์ปัจจุบัน
      let roleBadges = allowedList.map(r => {
        if(r === 'admin') return '<span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded mx-1 font-bold">Admin</span>';
        if(r === 'staff') return '<span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded mx-1 font-bold">Staff</span>';
        return `<span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded mx-1">${r}</span>`;
      }).join('');

      // [Lock] ป้องกัน Admin เผลอติ๊กเมนู "กำหนดสิทธิ์" ของตัวเองออก
      let disabled = '';
      if(targetRole === 'admin' && menu.name.includes('กำหนดสิทธิ์')) {
        disabled = 'disabled';
        // isChecked = 'checked'; // ถ้าอยากบังคับให้ติ๊กเสมอ
      }

      tbody.append(`
            <tr class="hover:bg-blue-50 transition group">
                <td class="p-4 text-center bg-gray-50 group-hover:bg-blue-100">
                    <input type="checkbox" class="menu-checkbox w-5 h-5 text-blue-600 rounded focus:ring-blue-500 cursor-pointer"
                           value="${menu.id}" ${isChecked} ${disabled}>
                </td>
                <td class="p-4 font-medium text-gray-700 flex items-center gap-2">
                    <i class="fas ${menu.icon} text-gray-400 w-6 text-center"></i>
                    ${menu.name}
                </td>
                <td class="p-4">${roleBadges}</td>
            </tr>
        `);
    });
  }

  // 3. บันทึกข้อมูล
  async function savePermissions() {
    const targetRole = $('#selectRole').val();

    // หา ID เมนูที่ติ๊กถูก
    let selectedIds = [];
    $('.menu-checkbox:checked').each(function() {
      selectedIds.push($(this).val());
    });

    // [Safety] กรณี Admin: ต้องแน่ใจว่าเมนู "กำหนดสิทธิ์" ถูกรวมไปด้วย (กันพลาดเพราะ disabled ไว้)
    if(targetRole === 'admin') {
      const adminLockMenu = allMenus.find(m => m.name.includes('กำหนดสิทธิ์'));
      if(adminLockMenu && !selectedIds.includes(adminLockMenu.id.toString())) {
        selectedIds.push(adminLockMenu.id);
      }
    }

    alertify.confirm(`บันทึกสิทธิ์ (${targetRole})`, `ยืนยันการแก้ไขสิทธิ์สำหรับตำแหน่ง <b>${targetRole}</b> ?`,
      async function() {
        try {
          const payload = {
            role: targetRole,
            menu_ids: selectedIds
          };

          const res = await fetch(`${PERMISSION_API}?action=save_permission`, {
            method: 'POST',
            body: JSON.stringify(payload)
          });
          const result = await res.json();

          if(result.success) {
            alertify.success(result.message);
            fetchMenusAndRender(); // โหลดข้อมูลใหม่เพื่ออัปเดต Badges
          } else {
            alertify.alert('ข้อผิดพลาด', result.message);
          }
        } catch(e) {
          console.error(e);
          alertify.error('เกิดข้อผิดพลาดในการบันทึก');
        }
      },
      function() { /* Cancel */ }
    ).set('labels', {ok:'บันทึก', cancel:'ยกเลิก'});
  }
</script>
