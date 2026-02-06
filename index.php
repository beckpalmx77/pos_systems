<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

  <main class="flex-1 flex flex-col min-w-0 bg-gray-100">
    <?php include 'layouts/topbar.php'; ?>

    <div class="flex-1 p-4 overflow-hidden flex flex-col">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

        <div class="p-4 bg-blue-50 border-b border-blue-100 flex items-center justify-between">
          <div class="flex items-center gap-3 flex-1">
            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-500 shadow-sm"><i class="fas fa-users"></i></div>
            <div>
              <p class="text-xs text-gray-500">ลูกค้าสมาชิก (F2)</p>
              <h3 id="memberDisplayName" class="text-lg font-bold text-gray-800">ลูกค้าทั่วไป (Guest)</h3>
            </div>
          </div>
          <div class="flex gap-2">
            <input type="text" id="memberInput" class="px-3 py-2 border border-gray-300 rounded-lg text-sm w-48" placeholder="เบอร์โทร / รหัส">
            <button onclick="findMember()" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm shadow"><i class="fas fa-search"></i></button>
            <button onclick="resetMember()" class="bg-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm shadow"><i class="fas fa-times"></i></button>
          </div>
        </div>

        <div class="p-4 bg-gray-50 border-b border-gray-100">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-barcode text-gray-400 text-xl"></i></div>
            <input type="text" id="barcodeInput" class="w-full pl-12 pr-4 py-4 border-2 border-blue-500 rounded-lg text-xl" placeholder="ยิงบาร์โค้ด (F4)" autofocus>
          </div>
        </div>

        <div class="flex-1 overflow-auto">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100 sticky top-0">
            <tr>
              <th class="py-3 px-4 text-sm">สินค้า</th>
              <th class="py-3 px-4 text-sm text-right">ราคา</th>
              <th class="py-3 px-4 text-sm text-center">จำนวน</th>
              <th class="py-3 px-4 text-sm text-right">รวม</th>
              <th class="py-3 px-4 text-sm text-center">ลบ</th>
            </tr>
            </thead>
            <tbody id="cartTable" class="divide-y divide-gray-100"></tbody>
          </table>
          <div id="emptyCart" class="flex flex-col items-center justify-center h-40 text-gray-400"><i class="fas fa-box-open text-4xl mb-2"></i><p>รายการว่าง</p></div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-gray-200">
          <div class="flex justify-between items-end mb-4">
            <div class="text-gray-500">จำนวน: <span id="totalItems" class="font-bold">0</span></div>
            <div class="text-right">
              <p class="text-xs text-gray-500">สุทธิ</p>
              <h2 class="text-4xl font-bold text-blue-600">฿<span id="grandTotal">0.00</span></h2>
            </div>
          </div>
          <button onclick="submitOrder()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-xl font-bold py-4 rounded-xl shadow-lg transition transform active:scale-95">
            <i class="fas fa-print mr-2"></i> ยืนยันการขาย (Print)
          </button>
        </div>
      </div>
    </div>
  </main>

  <script>
    const API_URL = 'api/basic_api.php';

    let cart = [];
    let currentMember = null;

    // --- Startup ---
    $(document).ready(function() {
      if(localStorage.getItem('pos_user')) $('#barcodeInput').focus();

      // ตั้งค่าปุ่ม Alertify ภาษาไทย
      if(typeof alertify !== 'undefined') {
        alertify.defaults.glossary.title = 'แจ้งเตือน';
        alertify.defaults.glossary.ok = 'ตกลง';
        alertify.defaults.glossary.cancel = 'ยกเลิก';
      }
    });

    // --- Member Logic ---
    $('#memberInput').on('keyup', function(e) { if(e.key === 'Enter') findMember(); });

    async function findMember() {
      const keyword = $('#memberInput').val().trim();
      if(!keyword) return;
      try {
        const res = await fetch(`${API_URL}?action=get_member&keyword=${keyword}`);
        const result = await res.json();
        if(result.found) {
          currentMember = result.data;
          $('#memberDisplayName').html(`<i class="fas fa-check-circle"></i> ${currentMember.name} <span class="text-sm font-normal text-gray-600">(${currentMember.points} แต้ม)</span>`).addClass('text-green-600');
          $('#memberInput').val('');
          $('#barcodeInput').focus();
          alertify.success(`สมาชิก: ${currentMember.name}`); // ใช้ Alertify Success
        } else {
          alertify.error('ไม่พบข้อมูลสมาชิก'); // ใช้ Alertify Error
          $('#memberInput').val('').focus();
        }
      } catch(e) { console.error(e); }
    }

    function resetMember() {
      currentMember = null;
      $('#memberDisplayName').text("ลูกค้าทั่วไป (Guest)").removeClass('text-green-600');
      $('#memberInput').val('');
      $('#barcodeInput').focus();
    }

    // --- Barcode & Cart Logic ---
    $('#barcodeInput').on('keyup', async function(e) {
      if (e.key === 'Enter' && this.value.trim() !== "") {
        const barcode = this.value.trim();
        try {
          const res = await fetch(`${API_URL}?action=get_product&barcode=${barcode}`);
          const result = await res.json();
          if (result.found) {
            addToCart(result.data);
            this.value = '';
          } else {
            alertify.warning('ไม่พบสินค้า!'); // ใช้ Alertify Warning
            this.value = '';
          }
        } catch(e) { console.error(e); }
      }
    });

    function addToCart(p) {
      const exist = cart.find(i => i.id === p.id);
      if(exist) exist.qty++; else cart.push({...p, price: parseFloat(p.price), qty: 1});
      renderCart();
    }

    function renderCart() {
      const tbody = $('#cartTable');
      tbody.empty();
      let total = 0, qty = 0;
      if(cart.length > 0) $('#emptyCart').addClass('hidden'); else $('#emptyCart').removeClass('hidden');

      cart.forEach((item, idx) => {
        const sum = item.price * item.qty;
        total += sum; qty += item.qty;
        tbody.append(`
        <tr class="hover:bg-blue-50 transition">
            <td class="py-3 px-4">${item.name}</td>
            <td class="py-3 px-4 text-right">${item.price.toFixed(2)}</td>
            <td class="py-3 px-4 text-center"><span class="bg-gray-200 px-2 py-1 rounded text-sm font-bold">${item.qty}</span></td>
            <td class="py-3 px-4 text-right font-bold text-blue-600">${sum.toFixed(2)}</td>
            <td class="py-3 px-4 text-center"><button onclick="remove(${idx})" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button></td>
        </tr>
      `);
      });
      $('#grandTotal').text(total.toLocaleString('th-TH', {minimumFractionDigits: 2}));
      $('#totalItems').text(qty);
    }

    function remove(idx) { cart.splice(idx, 1); renderCart(); $('#barcodeInput').focus(); }

    // --- Submit Order (Alertify Confirm) ---
    function submitOrder() {
      if(cart.length === 0) return alertify.alert('แจ้งเตือน', 'ไม่มีสินค้าในตะกร้า');

      // [แก้ไข] ใช้ alertify.confirm แทน confirm() ธรรมดา
      alertify.confirm('ยืนยันการขาย', 'ต้องการบันทึกยอดขายหรือไม่?',
        async function(){
          // --- เมื่อกด OK (ตกลง) ---
          await processOrder();
        },
        function(){
          // --- เมื่อกด Cancel (ยกเลิก) ---
          // alertify.error('ยกเลิกรายการ');
        }
      );
    }

    // แยกฟังก์ชันออกมาเพื่อให้เรียกใช้ใน Callback ง่ายขึ้น
    async function processOrder() {
      const payload = {
        cashier: currentUserData ? currentUserData.fullname : 'Unknown',
        total: parseFloat($('#grandTotal').text().replace(/,/g,'')),
        items: cart,
        member_id: currentMember ? currentMember.id : null
      };

      try {
        const res = await fetch(`${API_URL}?action=save_order`, {
          method: 'POST',
          body: JSON.stringify(payload)
        });
        const result = await res.json();

        if(result.success) {
          // ถามเพื่อ Print ใบเสร็จ (Alertify Confirm อีกรอบ)
          alertify.confirm('บันทึกสำเร็จ', `เลขที่เอกสาร: <b>${result.docId}</b><br>ต้องการพิมพ์ใบเสร็จหรือไม่?`,
            function() {
              // กดตกลง -> พิมพ์
              window.open(`receipt.php?doc_id=${result.docId}`, 'Receipt', 'width=350,height=600,scrollbars=yes');
              clearScreen();
            },
            function() {
              // กดไม่พิมพ์ -> แค่เคลียร์หน้าจอ
              clearScreen();
            }
          ).set('labels', {ok:'พิมพ์ใบเสร็จ', cancel:'ไม่พิมพ์'}); // เปลี่ยนชื่อปุ่มเฉพาะตรงนี้

        } else {
          alertify.alert('ข้อผิดพลาด', result.message);
        }
      } catch(e) {
        console.error(e);
        alertify.error('Failed to save order');
      }
    }

    function clearScreen() {
      cart = [];
      resetMember();
      renderCart();
      $('#barcodeInput').focus();
      alertify.success('พร้อมขายรายการต่อไป');
    }

    // --- Hotkeys ---
    $(document).keydown(function(e) {
      if(e.key === 'F2') { e.preventDefault(); $('#memberInput').focus(); }
      if(e.key === 'F4') { e.preventDefault(); $('#barcodeInput').focus(); }
    });
  </script>

<?php include 'layouts/footer.php'; ?>
