</div> <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
  // [สำคัญ] แยก API URL ให้ชัดเจน
  const USER_API = 'api/user_api.php';

  let currentUserData = null;

  $(document).ready(function() {
    checkLoginState();
    setInterval(() => $('#systemTime').text(new Date().toLocaleString('th-TH')), 1000);
  });

  function checkLoginState() {
    const stored = localStorage.getItem('pos_user');
    if (stored) {
      currentUserData = JSON.parse(stored);
      $('#loginModal').addClass('hidden');
      $('#appWrapper').removeClass('hidden');
      $('#currentUser').text(currentUserData.fullname);
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
      // เรียกใช้ USER_API
      const res = await fetch(`${USER_API}?action=login`, { method: 'POST', body: JSON.stringify({ username: u, password: p }) });
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
    // เรียกใช้ USER_API
    const res = await fetch(`${USER_API}?action=get_menus&role=${role}`);
    const menus = await res.json();
    const list = document.getElementById('menuList');
    if(list) {
      list.innerHTML = '';
      const currentPage = window.location.pathname.split("/").pop();
      menus.forEach(m => {
        const isActive = (m.link === currentPage) ? 'bg-slate-800 text-white border-l-4 border-blue-400' : 'text-gray-400 hover:bg-slate-800 hover:text-white';
        list.innerHTML += `<li><a href="${m.link}" class="flex items-center px-4 py-3 transition ${isActive}"><span class="w-8 text-center"><i class="fas ${m.icon}"></i></span><span>${m.name}</span></a></li>`;
      });
    }
  }
</script>
</body>
</html>
