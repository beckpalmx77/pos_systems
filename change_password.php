<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 flex justify-center items-start">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 w-full max-w-md overflow-hidden">
      <div class="p-5 border-b bg-gray-50">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-key text-yellow-500 mr-2"></i> เปลี่ยนรหัสผ่านใหม่
        </h2>
      </div>

      <div class="p-6 space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านปัจจุบัน</label>
          <input type="password" id="oldPassword"
                 class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                 placeholder="ระบุรหัสผ่านเดิม">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
          <input type="password" id="newPassword"
                 class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                 placeholder="อย่างน้อย 6 ตัวอักษร">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
          <input type="password" id="confirmPassword"
                 class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                 placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
        </div>
      </div>

      <div class="p-5 bg-gray-50 border-t flex flex-col gap-2">
        <button onclick="updatePassword()"
                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg shadow-sm transition transform active:scale-95">
          บันทึกการเปลี่ยนแปลง
        </button>
      </div>
    </div>
  </div>
</main>

<?php include 'layouts/footer.php'; ?>

<script>
  async function updatePassword() {
    const oldPass = $('#oldPassword').val().trim();
    const newPass = $('#newPassword').val().trim();
    const confirmPass = $('#confirmPassword').val().trim();

    if (!oldPass || !newPass || !confirmPass) {
      alertify.error('กรุณากรอกข้อมูลให้ครบทุกช่อง');
      return;
    }

    if (newPass.length < 6) {
      alertify.warning('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
      return;
    }

    if (newPass !== confirmPass) {
      alertify.error('รหัสผ่านใหม่ไม่ตรงกัน');
      return;
    }

    try {
      const res = await fetch('api/user_api.php?action=change_password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          old_password: oldPass,
          new_password: newPass
        })
      });

      const result = await res.json();

      if (result.success) {
        alertify.alert('สำเร็จ', 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว กรุณาเข้าสู่ระบบใหม่อีกครั้ง', function() {
          // เรียกฟังก์ชัน logout() ที่อยู่ใน footer.php
          logout();
        });
      } else {
        alertify.error(result.message);
      }
    } catch (error) {
      alertify.error('ไม่สามารถเชื่อมต่อ Server ได้');
    }
  }
</script>
