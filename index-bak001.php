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
          <div class="flex gap-2">
            <div class="relative flex-1">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none"><i class="fas fa-barcode text-gray-400 text-xl"></i></div>
              <input type="text" id="barcodeInput" class="w-full pl-12 pr-4 py-4 border-2 border-blue-500 rounded-lg text-xl focus:outline-none focus:ring-2 focus:ring-blue-300" placeholder="ยิงบาร์โค้ด หรือ พิมพ์รหัส (F4)" autofocus>
            </div>
            <button onclick="triggerSearchProduct()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-lg font-bold shadow transition">
              <i class="fas fa-check"></i> ตกลง
            </button>
          </div>
        </div>

        <div class="flex-1 overflow-auto">
          <table class="w-full text-left border-collapse">
            <thead class="bg-gray-100 sticky top-0 shadow-sm z-10">
            <tr>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600">สินค้า</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-right">ราคา</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">จำนวน</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-right">รวม</th>
              <th class="py-3 px-4 text-sm font-semibold text-gray-600 text-center">ลบ</th>
            </tr>
            </thead>
            <tbody id="cartTable" class="divide-y divide-gray-100"></tbody>
          </table>
          <div id="emptyCart" class="flex flex-col items-center justify-center h-40 text-gray-400">
            <i class="fas fa-box-open text-4xl mb-2 opacity-50"></i>
            <p>รายการว่าง</p>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-gray-200">
          <div class="flex justify-between items-end mb-4">
            <div class="text-gray-500">จำนวน: <span id="totalItems" class="font-bold text-gray-800">0</span> ชิ้น</div>
            <div class="text-right">
              <p class="text-xs text-gray-500">ยอดสุทธิ</p>
              <h2 class="text-4xl font-bold text-blue-600">฿<span id="grandTotal">0.00</span></h2>
            </div>
          </div>
          <button onclick="submitOrder()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white text-xl font-bold py-4 rounded-xl shadow-lg transition transform active:scale-[0.98]">
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

    $(document).ready(function() {
      if(localStorage.getItem('pos_user')) $('#barcodeInput').focus();
      if(typeof alertify !== 'undefined') {
        alertify.defaults.glossary.title = 'แจ้งเตือน';
        alertify.defaults.glossary.ok = 'ตกลง';
        alertify.defaults.glossary.cancel = 'ยกเลิก';
      }
    });

    // --- 1. MEMBER LOGIC ---
    $('#memberInput').on('keydown', function(e) {
      if (e.which === 13 || e.key === 'Enter') {
        e.preventDefault();
        findMember();
      }
    });

    async function findMember() {
      const keyword = $('#memberInput').val().trim();
      if(!keyword) return;

      try {
        const res = await fetch(`${API_URL}?action=get_member&keyword=${keyword}`);
        const result = await res.json();

        if(result.found) {
          currentMember = result.data;
          $('#memberDisplayName')
            .html(`<i class="fas fa-check-circle"></i> ${currentMember.name} <span class="text-sm font-normal text-gray-600">(${currentMember.points} แต้ม)</span>`)
            .removeClass('text-gray-800').addClass('text-green-600');

          $('#memberInput').val('');
          alertify.success(`สมาชิก: ${currentMember.name}`);
          $('#barcodeInput').focus();
        } else {
          alertify.error('ไม่พบข้อมูลสมาชิก');
          $('#memberInput').select();
        }
      } catch(e) { console.error(e); }
    }

    function resetMember() {
      currentMember = null;
      $('#memberDisplayName').text("ลูกค้าทั่วไป (Guest)").removeClass('text-green-600').addClass('text-gray-800');
      $('#memberInput').val('');
      $('#barcodeInput').focus();
    }


    // --- 2. BARCODE LOGIC (Enter + Click) ---
    $('#barcodeInput').on('keydown', function(e) {
      if (e.which === 13 || e.key === 'Enter') {
        e.preventDefault();
        triggerSearchProduct();
      }
    });

    async function triggerSearchProduct() {
      const barcode = $('#barcodeInput').val().trim();
      if (barcode === "") { $('#barcodeInput').focus(); return; }

      try {
        const res = await fetch(`${API_URL}?action=get_product&barcode=${barcode}`);
        if (!res.ok) {
          alertify.error(`Server Error: ${res.status}`);
          return;
        }
        const result = await res.json();

        if (result.found) {
          addToCart(result.data);
          $('#barcodeInput').val('');
        } else {
          alertify.warning(`ไม่พบรหัส: ${barcode}`);
          $('#barcodeInput').val('');
        }
      } catch(e) {
        console.error(e);
        alertify.error('Connection Error');
      }
      $('#barcodeInput').focus();
    }

    // --- 3. CART FUNCTIONS ---
    function addToCart(p) {
      const price = parseFloat(p.price);
      const exist = cart.find(i => i.id === p.id);
      if(exist) exist.qty++; else cart.push({...p, price: price, qty: 1});
      renderCart();
      alertify.success(`+ ${p.name}`);
    }

    function renderCart() {
      const tbody = $('#cartTable');
      tbody.empty();
      let total = 0, qty = 0;

      if(cart.length > 0) $('#emptyCart').addClass('hidden');
      else $('#emptyCart').removeClass('hidden');

      cart.forEach((item, idx) => {
        const sum = item.price * item.qty;
        total += sum; qty += item.qty;

        tbody.append(`
        <tr class="hover:bg-blue-50 transition border-b">
            <td class="py-3 px-4">
                <div class="font-bold text-gray-700">${item.name}</div>
                <div class="text-xs text-gray-400 font-mono">${item.barcode || ''}</div>
            </td>
            <td class="py-3 px-4 text-right">${item.price.toFixed(2)}</td>
            <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center border rounded-lg bg-white shadow-sm">
                    <button onclick="changeQty(${idx}, -1)" class="px-2 py-1 text-gray-500 hover:text-red-500 hover:bg-gray-100 rounded-l transition">-</button>
                    <span class="px-3 py-1 font-bold text-gray-700 min-w-[30px] select-none">${item.qty}</span>
                    <button onclick="changeQty(${idx}, 1)" class="px-2 py-1 text-gray-500 hover:text-green-500 hover:bg-gray-100 rounded-r transition">+</button>
                </div>
            </td>
            <td class="py-3 px-4 text-right font-bold text-blue-600">${sum.toFixed(2)}</td>
            <td class="py-3 px-4 text-center">
                <button onclick="remove(${idx})" class="text-red-400 hover:text-red-600 bg-red-50 hover:bg-red-100 p-2 rounded-full transition shadow-sm">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        </tr>`);
      });
      $('#grandTotal').text(total.toLocaleString('th-TH', {minimumFractionDigits: 2}));
      $('#totalItems').text(qty);
    }

    function changeQty(idx, amount) {
      if(cart[idx].qty + amount > 0) {
        cart[idx].qty += amount;
      } else {
        if(confirm('ลบรายการนี้?')) cart.splice(idx, 1);
      }
      renderCart();
      $('#barcodeInput').focus();
    }

    function remove(idx) {
      cart.splice(idx, 1);
      renderCart();
      $('#barcodeInput').focus();
    }

    // --- 4. SUBMIT & API RESPONSE ---
    function submitOrder() {
      if(cart.length === 0) return alertify.alert('แจ้งเตือน', 'ไม่มีสินค้าในตะกร้า');

      alertify.confirm('ยืนยันการขาย', 'ต้องการบันทึกยอดขายหรือไม่?',
        async function(){ await processOrder(); },
        function(){}
      ).set('labels', {ok:'บันทึกขาย', cancel:'ยกเลิก'});
    }

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

          // ตรวจสอบ Response จาก API Gateway (ถ้ามี)
          let extraMsg = "";
          if(result.gateway_result && result.gateway_result.payment_url) {
            window.open(result.gateway_result.payment_url, '_blank');
            extraMsg = "<br><span class='text-red-500 text-sm'>*กำลังเปิดหน้าชำระเงิน...</span>";
          }

          alertify.confirm('บันทึกสำเร็จ', `เลขที่เอกสาร: <b>${result.docId}</b>${extraMsg}<br>ต้องการพิมพ์ใบเสร็จหรือไม่?`,
            function() {
              window.open(`receipt.php?doc_id=${result.docId}`, 'Receipt', 'width=350,height=600,scrollbars=yes');
              clearScreen();
            },
            function() { clearScreen(); }
          ).set('labels', {ok:'พิมพ์ใบเสร็จ', cancel:'ไม่พิมพ์'});

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

    $(document).keydown(function(e) {
      if(e.key === 'F2') { e.preventDefault(); $('#memberInput').focus(); }
      if(e.key === 'F4') { e.preventDefault(); $('#barcodeInput').focus(); }
    });
  </script>

<?php include 'layouts/footer.php'; ?>
