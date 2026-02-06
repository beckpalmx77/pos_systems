const API_URL = 'api/basic_api.php';
let currentUserData = null;
let cart = [];
let currentMember = null;

// --- LOGIN ---
async function login() {
  const u = document.getElementById('username').value;
  const p = document.getElementById('password').value;
  try {
    const res = await fetch(`${API_URL}?action=login`, {
      method: 'POST', body: JSON.stringify({ username: u, password: p })
    });
    const data = await res.json();
    if (data.success) {
      currentUserData = data.user;
      document.getElementById('loginModal').classList.add('hidden');
      document.getElementById('appWrapper').classList.remove('hidden');
      document.getElementById('currentUser').innerText = currentUserData.fullname;
      loadMenus(currentUserData.role);
      document.getElementById('barcodeInput').focus();
    } else { alert(data.message); }
  } catch (e) { console.error(e); alert('Connection Error: ' + API_URL); }
}

// --- MENUS ---
async function loadMenus(role) {
  const res = await fetch(`${API_URL}?action=get_menus&role=${role}`);
  const menus = await res.json();
  const list = document.getElementById('menuList');
  list.innerHTML = '';
  menus.forEach(m => {
    list.innerHTML += `<li><a href="${m.link}" class="flex items-center px-4 py-3 text-gray-400 hover:bg-slate-800 hover:text-white transition"><span class="w-8 text-center"><i class="fas ${m.icon}"></i></span><span>${m.name}</span></a></li>`;
  });
}

// --- MEMBER SYSTEM ---
async function findMember() {
  const input = document.getElementById('memberInput');
  const keyword = input.value.trim();
  if(!keyword) return;

  try {
    const res = await fetch(`${API_URL}?action=get_member&keyword=${keyword}`);
    const result = await res.json();
    if(result.found) {
      currentMember = result.data;
      const display = document.getElementById('memberDisplayName');
      display.innerHTML = `<i class="fas fa-check-circle"></i> ${currentMember.name} <span class="text-sm font-normal text-gray-600">(แต้ม: ${currentMember.points})</span>`;
      display.classList.add('text-green-600');
      input.value = '';
      document.getElementById('barcodeInput').focus(); // Jump to product
    } else {
      alert('ไม่พบข้อมูลสมาชิก');
      input.value = '';
      input.focus();
    }
  } catch(e) { console.error(e); }
}

function resetMember() {
  currentMember = null;
  const display = document.getElementById('memberDisplayName');
  display.innerText = "ลูกค้าทั่วไป (Guest)";
  display.classList.remove('text-green-600');
  document.getElementById('memberInput').value = '';
  document.getElementById('barcodeInput').focus();
}

document.getElementById('memberInput').addEventListener('keyup', (e) => {
  if(e.key === 'Enter') findMember();
});

// --- PRODUCT & CART ---
const input = document.getElementById('barcodeInput');
input.addEventListener('keyup', async (e) => {
  if (e.key === 'Enter' && input.value.trim() !== "") {
    const barcode = input.value.trim();
    const res = await fetch(`${API_URL}?action=get_product&barcode=${barcode}`);
    const result = await res.json();
    if (result.found) { addToCart(result.data); input.value = ''; }
    else { alert('ไม่พบสินค้า!'); input.value = ''; }
  }
});

function addToCart(product) {
  const existing = cart.find(i => i.id === product.id);
  if (existing) existing.qty++;
  else cart.push({ ...product, price: parseFloat(product.price), qty: 1 });
  renderCart();
}

function renderCart() {
  const tbody = document.getElementById('cartTable');
  const empty = document.getElementById('emptyCart');
  tbody.innerHTML = '';
  let total = 0, qty = 0;
  if (cart.length > 0) empty.classList.add('hidden'); else empty.classList.remove('hidden');

  cart.forEach((item, idx) => {
    const sum = item.price * item.qty;
    total += sum; qty += item.qty;
    tbody.innerHTML += `
        <tr class="hover:bg-blue-50 transition">
            <td class="py-3 px-4 border-b">${item.name}</td>
            <td class="py-3 px-4 border-b text-right">${item.price.toFixed(2)}</td>
            <td class="py-3 px-4 border-b text-center"><span class="bg-gray-200 px-2 py-1 rounded text-sm font-bold">${item.qty}</span></td>
            <td class="py-3 px-4 border-b text-right font-bold text-blue-600">${sum.toFixed(2)}</td>
            <td class="py-3 px-4 border-b text-center"><button onclick="remove(${idx})" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button></td>
        </tr>`;
  });
  document.getElementById('grandTotal').innerText = total.toLocaleString('th-TH', {minimumFractionDigits: 2});
  document.getElementById('totalItems').innerText = qty;
}

function remove(idx) { cart.splice(idx, 1); renderCart(); input.focus(); }

// --- SUBMIT ORDER ---
async function submitOrder() {
  if (cart.length === 0) return alert('ไม่มีสินค้า');
  if (!confirm('ยืนยันบันทึกยอดขาย?')) return;

  const payload = {
    cashier: currentUserData.fullname,
    total: parseFloat(document.getElementById('grandTotal').innerText.replace(/,/g,'')),
    items: cart,
    member_id: currentMember ? currentMember.id : null
  };

  try {
    const res = await fetch(`${API_URL}?action=save_order`, {
      method: 'POST', body: JSON.stringify(payload)
    });
    const result = await res.json();
    if (result.success) {
      alert(`✅ บันทึกสำเร็จ!\nเลขที่เอกสาร: ${result.docId}`);
      cart = []; resetMember(); renderCart();
    } else { alert('Error: ' + result.message); }
  } catch(e) { alert('Failed to save order'); }
}

// --- UTILS & HOTKEYS ---
setInterval(() => document.getElementById('systemTime').innerText = new Date().toLocaleString('th-TH'), 1000);

document.addEventListener('keydown', (e) => {
  if (e.key === 'F2') { e.preventDefault(); document.getElementById('memberInput').focus(); }
  if (e.key === 'F4' || e.key === 'Escape') { e.preventDefault(); document.getElementById('barcodeInput').focus(); }
});

document.addEventListener('click', (e) => {
  if (currentUserData && !e.target.closest('button') && e.target.tagName !== 'INPUT') {
    if(document.activeElement.id !== 'memberInput') input.focus();
  }
});
