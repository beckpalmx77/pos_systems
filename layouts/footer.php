</div> <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
  // กำหนด URL API
  const USER_API = 'api/user_api.php';
  const POS_API  = 'api/basic_api.php'; // เผื่อไว้ใช้

  let currentUserData = null;

  $(document).ready(function() {
    // 1. เช็ค Login
    checkLoginState();

    // 2. เริ่มต้นนาฬิกา
    updateThaiTime();
    setInterval(updateThaiTime, 1000);
  });

  // --- ฟังก์ชันนาฬิกาไทย ---
  function updateThaiTime() {
    const now = new Date();

    const days = ['วันอาทิตย์', 'วันจันทร์', 'วันอังคาร', 'วันพุธ', 'วันพฤหัสบดี', 'วันศุกร์', 'วันเสาร์'];
    const months = [
      'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
      'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
    ];

    // ดึงค่าต่างๆ
    const dayName = days[now.getDay()];
    const date = now.getDate();
    const monthName = months[now.getMonth()];
    const year = now.getFullYear() + 543; // แปลงเป็น พ.ศ.

    // จัดรูปแบบเวลา hh:mm:ss
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');

    // ประกอบข้อความ: "วันศุกร์ , 6 กุมภาพันธ์ 2569 เวลา 15:00:00"
    const finalString = `${dayName} , ${date} ${monthName} ${year} เวลา ${h}:${m}:${s}`;

    // แสดงผล (เช็คว่ามี element นี้อยู่ไหม เพื่อป้องกัน error ในหน้า Login)
    const timeDisplay = document.getElementById('systemTime');
    if(timeDisplay) {
      timeDisplay.innerText = finalString;
    }
  }

  // --- ฟังก์ชันจัดการ User & System ---
  function checkLoginState() {
    const stored = localStorage.getItem('pos_user');
    if (stored) {
      currentUserData = JSON.parse(stored);
      $('#loginModal').addClass('hidden');
      $('#appWrapper').removeClass('hidden');

      // อัปเดตชื่อผู้ใช้บน Topbar
      const userDisplay = document.getElementById('currentUser');
      if(userDisplay) userDisplay.innerText = currentUserData.fullname;

      loadMenus(currentUserData.role);
    } else {
      $('#loginModal').removeClass('hidden');
      $('#appWrapper').addClass('hidden');
    }
  }

  async function login() {
    const u = $('#l_username').val();
    const p = $('#l_password').val();
    try {
      const res = await fetch(`${USER_API}?action=login`, {
        method: 'POST',
        body: JSON.stringify({ username: u, password: p })
      });
      const data = await res.json();
      if (data.success) {
        localStorage.setItem('pos_user', JSON.stringify(data.user));
        checkLoginState();
      } else { alert(data.message); }
    } catch (e) { alert('Connection Error'); }
  }

  function logout() {
    if(confirm('ยืนยันออกจากระบบ?')) {
      localStorage.removeItem('pos_user');
      window.location.href = 'index.php';
    }
  }

  async function loadMenus(role) {
    try {
      const res = await fetch(`${USER_API}?action=get_menus&role=${role}`);
      const menus = await res.json();
      const list = document.getElementById('menuList');

      if(list) {
        list.innerHTML = '';
        // หาชื่อไฟล์ปัจจุบัน (เช่น index.php)
        const currentPage = window.location.pathname.split("/").pop();

        menus.forEach(m => {
          // Highlight เมนูที่กำลังใช้งานอยู่
          const isActive = (m.link === currentPage)
            ? 'bg-slate-800 text-white border-l-4 border-blue-400'
            : 'text-gray-400 hover:bg-slate-800 hover:text-white';

          list.innerHTML += `
                    <li>
                        <a href="${m.link}" class="flex items-center px-4 py-3 transition ${isActive}">
                            <span class="w-8 text-center"><i class="fas ${m.icon}"></i></span>
                            <span>${m.name}</span>
                        </a>
                    </li>`;
        });
      }
    } catch(e) { console.error("Menu Load Error:", e); }
  }
</script>
</body>
</html>
