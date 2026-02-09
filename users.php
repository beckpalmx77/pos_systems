<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">
      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-users-cog text-blue-500 mr-2"></i> จัดการผู้ใช้งาน
        </h2>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition">
          <i class="fas fa-plus"></i> เพิ่มผู้ใช้งาน
        </button>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="usersTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th width="10%">ID</th>
            <th>Username</th>
            <th>ชื่อ-นามสกุล</th>
            <th>สิทธิ์ (Role)</th>
            <th class="text-center" width="15%">จัดการ</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="userModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-700">เพิ่มผู้ใช้งาน</h3>
        <button onclick="closeModal()" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 space-y-4">
        <input type="hidden" id="userId">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
          <input type="text" id="inpUsername" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" id="inpPassword" placeholder="(เว้นว่างไว้ถ้าไม่เปลี่ยน)" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ-นามสกุล</label>
          <input type="text" id="inpFullname" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">สิทธิ์การใช้งาน</label>
          <select id="inpRole" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="staff">Staff (พนักงานขาย)</option>
            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
          </select>
        </div>
      </div>

      <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
        <button onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded">ยกเลิก</button>
        <button onclick="saveUser()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">บันทึก</button>
      </div>
    </div>
  </div>

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;
  const API_URL = 'api/user_api.php';

  // ตั้งค่า Alertify defaults (Optional)
  alertify.defaults.transition = "zoom";
  alertify.defaults.theme.ok = "ui positive button";
  alertify.defaults.theme.cancel = "ui black button";

  $(document).ready(function() {
    // 1. แก้ Alert ตรวจสิทธิ์
    if(currentUserData && currentUserData.role !== 'admin'){
      alertify.alert('แจ้งเตือน', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้', function(){
        window.location.href = 'index.php';
      });
      return;
    }

    table = $('#usersTable').DataTable({
      "ajax": { "url": `${API_URL}?action=get_users`, "dataSrc": "" },
      "columns": [
        { "data": "id" },
        { "data": "username", "className": "font-bold text-blue-600" },
        { "data": "fullname" },
        {
          "data": "role",
          "render": function(data) {
            return data === 'admin'
              ? '<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-bold">Admin</span>'
              : '<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Staff</span>';
          }
        },
        {
          "data": null,
          "className": "text-center",
          "render": function(data, type, row) {
            let jsonRow = JSON.stringify(row).replace(/"/g, '&quot;');
            return `
                <button onclick="editUser(${jsonRow})" class="text-yellow-500 hover:text-yellow-600 mx-1" title="แก้ไข"><i class="fas fa-edit"></i></button>
                <button onclick="deleteUser(${row.id})" class="text-red-500 hover:text-red-600 mx-1" title="ลบ"><i class="fas fa-trash"></i></button>
            `;
          }
        }
      ]
    });
  });

  function openModal() {
    $('#modalTitle').text('เพิ่มผู้ใช้งาน');
    $('#userId').val('');
    $('#inpUsername').val('');
    $('#inpPassword').val('');
    $('#inpFullname').val('');
    $('#inpRole').val('staff');
    $('#userModal').removeClass('hidden');
  }

  function editUser(user) {
    $('#modalTitle').text('แก้ไขข้อมูล');
    $('#userId').val(user.id);
    $('#inpUsername').val(user.username);
    $('#inpPassword').val('');
    $('#inpFullname').val(user.fullname);
    $('#inpRole').val(user.role);
    $('#userModal').removeClass('hidden');
  }

  function closeModal() {
    $('#userModal').addClass('hidden');
  }

  async function saveUser() {
    const payload = {
      id: $('#userId').val(),
      username: $('#inpUsername').val().trim(),
      password: $('#inpPassword').val().trim(),
      fullname: $('#inpFullname').val().trim(),
      role: $('#inpRole').val()
    };

    // 2. แก้ Validation เป็น Alertify Error/Warning (ไม่ใช้ return alert)
    if(!payload.username || !payload.fullname) {
      alertify.error('กรุณากรอกข้อมูล Username และ ชื่อ-นามสกุล ให้ครบ');
      return;
    }
    if(!payload.id && !payload.password) {
      alertify.warning('กรุณาตั้งรหัสผ่านสำหรับผู้ใช้ใหม่');
      return;
    }

    try {
      const res = await fetch(`${API_URL}?action=save_user`, { method: 'POST', body: JSON.stringify(payload) });
      const result = await res.json();

      if(result.success) {
        // 3. แสดงผลสำเร็จ
        alertify.success('✅ บันทึกข้อมูลเรียบร้อยแล้ว');
        closeModal();
        table.ajax.reload();
      } else {
        alertify.error('เกิดข้อผิดพลาด: ' + result.message);
      }
    } catch(e) {
      console.error(e);
      alertify.error('ไม่สามารถเชื่อมต่อ Server ได้');
    }
  }

  function deleteUser(id) {
    // 4. แก้ Confirm เป็น Alertify Confirm
    // Alertify เป็น Asynchronous ต้องใช้ callback function แทนการใช้ if(!confirm) return;

    alertify.confirm('ยืนยันการลบ', 'คุณต้องการลบผู้ใช้งานนี้ใช่หรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้',
      async function() { // เมื่อกด OK
        try {
          const res = await fetch(`${API_URL}?action=delete_user`, { method: 'POST', body: JSON.stringify({id}) });
          const result = await res.json();

          if(result.success) {
            alertify.success('ลบข้อมูลสำเร็จ');
            table.ajax.reload();
          } else {
            alertify.error('ลบไม่สำเร็จ: ' + result.message);
          }
        } catch(e) {
          alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }
      },
      function() { // เมื่อกด Cancel
        alertify.message('ยกเลิกการลบ');
      }
    ).set('labels', {ok:'ลบข้อมูล', cancel:'ยกเลิก'}); // เปลี่ยนข้อความปุ่ม
  }
</script>
