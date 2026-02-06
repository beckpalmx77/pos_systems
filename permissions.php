<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden max-w-4xl mx-auto w-full">

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
          </select>
          <span class="text-sm text-gray-500">* เลือกตำแหน่งเพื่อดูหรือแก้ไขสิทธิ์</span>
        </div>

        <div class="border rounded-lg overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100 text-gray-600 border-b">
            <tr>
              <th class="p-4 w-16 text-center">อนุญาต</th>
              <th class="p-4">ชื่อเมนู</th>
              <th class="p-4 text-gray-400">ปัจจุบันอนุญาตให้</th>
            </tr>
            </thead>
            <tbody id="permissionTableBody" class="divide-y">
            <tr><td colspan="3" class="p-4 text-center">Loading...</td></tr>
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
  const API_URL = 'api/user_api.php';
  let allMenus = []; // เก็บข้อมูลเมนูไว้ที่ตัวแปรกลาง

  $(document).ready(function() {
    // เช็คสิทธิ์ Admin
    if(currentUserData && currentUserData.role !== 'admin'){
      alert('สงวนสิทธิ์เฉพาะ Admin เท่านั้น');
      window.location.href = 'index.php';
      return;
    }

    // โหลดข้อมูลครั้งแรก
    fetchMenusAndRender();

    // เมื่อเปลี่ยน Role ให้ Render ตารางใหม่
    $('#selectRole').change(function() {
      renderTable();
    });
  });

  // 1. ดึงข้อมูลเมนูทั้งหมดจาก API
  async function fetchMenusAndRender() {
    try {
      const res = await fetch(`${API_URL}?action=get_all_menus_list`);
      allMenus = await res.json();
      renderTable(); // วาดตาราง
    } catch(e) { console.error(e); alert('โหลดข้อมูลไม่สำเร็จ'); }
  }

  // 2. วาดตารางตาม Role ที่เลือก
  function renderTable() {
    const targetRole = $('#selectRole').val(); // role ที่กำลังเลือกอยู่ (เช่น staff)
    const tbody = $('#permissionTableBody');
    tbody.empty();

    allMenus.forEach(menu => {
      // เช็คว่า menu นี้อนุญาต role นี้หรือไม่?
      // แปลง string "admin,staff" -> array ["admin", "staff"]
      const allowedList = menu.allowed_roles.split(',').map(r => r.trim());
      const isChecked = allowedList.includes(targetRole) ? 'checked' : '';

      // สร้าง role badges สวยๆ
      let roleBadges = allowedList.map(r => {
        if(r === 'admin') return '<span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded mx-1">Admin</span>';
        if(r === 'staff') return '<span class="text-xs bg-green-100 text-green-600 px-2 py-1 rounded mx-1">Staff</span>';
        return `<span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded mx-1">${r}</span>`;
      }).join('');

      // ป้องกันไม่ให้ Admin ติ๊กออกเมนู "กำหนดสิทธิ์" ของตัวเอง (เดี๋ยวเข้าไม่ได้)
      let disabled = '';
      if(targetRole === 'admin' && menu.name.includes('กำหนดสิทธิ์')) {
        disabled = 'disabled';
        // ถ้าเป็น admin เมนูนี้ต้อง checked เสมอ
        // isChecked = 'checked';
      }

      tbody.append(`
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-4 text-center bg-gray-50">
                        <input type="checkbox" class="menu-checkbox w-5 h-5 text-blue-600 rounded focus:ring-blue-500 cursor-pointer"
                               value="${menu.id}" ${isChecked} ${disabled}>
                    </td>
                    <td class="p-4 font-medium text-gray-700">${menu.name}</td>
                    <td class="p-4">${roleBadges}</td>
                </tr>
            `);
    });
  }

  // 3. บันทึกข้อมูล
  async function savePermissions() {
    const targetRole = $('#selectRole').val();

    // หา ID ของเมนูที่ถูกติ๊ก Checkbox
    let selectedIds = [];
    $('.menu-checkbox:checked').each(function() {
      selectedIds.push($(this).val());
    });

    // กรณี Admin: เมนู "กำหนดสิทธิ์" อาจถูก disable ไว้ ทำให้ jQuery selector จับไม่ได้
    // เราต้องแอบยัด ID ของเมนูนั้นกลับเข้าไปด้วย เพื่อกันพลาด
    if(targetRole === 'admin') {
      // ค้นหาเมนูชื่อ "กำหนดสิทธิ์"
      const adminLockMenu = allMenus.find(m => m.name.includes('กำหนดสิทธิ์'));
      if(adminLockMenu && !selectedIds.includes(adminLockMenu.id.toString())) {
        selectedIds.push(adminLockMenu.id);
      }
    }

    if(!confirm(`ยืนยันบันทึกสิทธิ์สำหรับ "${targetRole}" ?`)) return;

    const payload = {
      role: targetRole,
      menu_ids: selectedIds
    };

    try {
      const res = await fetch(`${API_URL}?action=save_permission`, {
        method: 'POST', body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        alert('✅ ' + result.message);
        // รีโหลดข้อมูลใหม่เพื่อให้หน้าจออัปเดต
        fetchMenusAndRender();
      } else {
        alert('Error: ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alert('Failed to save');
    }
  }
</script>
