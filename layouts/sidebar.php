<aside class="w-64 bg-slate-900 text-white flex flex-col shadow-2xl z-20 font-sans">

  <div class="h-20 flex items-center justify-center border-b border-slate-700 bg-slate-900 shadow-sm">
    <a href="../pos_systems/index" class="hover:opacity-80 transition cursor-pointer">
      <h1 class="text-2xl font-bold tracking-widest text-blue-500 drop-shadow-md">
        POS<span class="text-white"> SYSTEM</span>
      </h1>
    </a>
  </div>

  <ul id="menuList" class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
  </ul>

  <div class="p-4 border-t border-slate-700 bg-slate-900/50">

    <button onclick="dashboard()" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-blue-900/30 group">
      <i class="fas fa-tachometer group-hover:rotate-180 transition-transform duration-300"></i>
      <span class="font-medium">Dash Board</span>
    </button>
  </div>

  <div class="p-4 border-t border-slate-700 bg-slate-900/50">

    <button onclick="logout()" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg transition-all duration-200 flex items-center justify-center gap-2 shadow-lg hover:shadow-red-900/30 group">
      <i class="fas fa-sign-out-alt group-hover:rotate-180 transition-transform duration-300"></i>
      <span class="font-medium">ออกจากระบบ</span>
    </button>

    <div class="mt-6 text-center">
      <p class="text-xs text-slate-400">
        System By Admin <span class="text-slate-300 font-bold">@<span id="sidebarYear"></span></span>
      </p>
      <p class="text-[10px] text-slate-600 mt-1 tracking-wider">
        POS System v1.0
      </p>
    </div>

  </div>
</aside>

<script>
  // ทำงานทันทีเมื่อโหลด Sidebar เสร็จ
  document.addEventListener("DOMContentLoaded", function() {
    const yearSpan = document.getElementById('sidebarYear');
    if(yearSpan) {
      yearSpan.innerText = new Date().getFullYear();
    }
  });
</script>
