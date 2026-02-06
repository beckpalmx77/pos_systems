<?php include 'layouts/header.php'; ?>
<?php include 'layouts/sidebar.php'; ?>

<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/default.min.css"/>

<main class="flex-1 flex flex-col min-w-0 bg-gray-100">
  <?php include 'layouts/topbar.php'; ?>

  <div class="flex-1 p-6 overflow-hidden flex flex-col">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex-1 flex flex-col overflow-hidden">

      <div class="p-5 border-b bg-gray-50 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-700">
          <i class="fas fa-address-card text-blue-500 mr-2"></i> จัดการสมาชิก (CRM)
        </h2>
        <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm shadow-sm transition flex items-center gap-2">
          <i class="fas fa-user-plus"></i> เพิ่มสมาชิก
        </button>
      </div>

      <div class="flex-1 overflow-auto p-5">
        <table id="membersTable" class="display w-full text-left text-sm" style="width:100%">
          <thead>
          <tr>
            <th width="15%">รหัส/เบอร์โทร</th>
            <th>ชื่อ-นามสกุล</th>
            <th class="text-right" width="15%">คะแนนสะสม (Point)</th>
            <th class="text-center" width="15%">วันที่สมัคร</th>
            <th class="text-center" width="15%">จัดการ</th>
          </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

  <div id="memberModal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex justify-center items-center backdrop-blur-sm">
    <div class="bg-white rounded-lg shadow-xl w-96 overflow-hidden">
      <div class="p-4 bg-gray-100 border-b flex justify-between items-center">
        <h3 id="modalTitle" class="text-lg font-bold text-gray-700">เพิ่มสมาชิก</h3>
        <button id="btnCloseModal" class="text-gray-500 hover:text-red-500 text-2xl font-bold px-2">&times;</button>
      </div>

      <div class="p-6 space-y-4">
        <input type="hidden" id="memId">

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">รหัสสมาชิก / เบอร์โทร</label>
          <input type="text" id="inpCode" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="เช่น 0812345678">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อ-นามสกุล</label>
          <input type="text" id="inpName" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="ระบุชื่อลูกค้า">
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">คะแนนสะสม</label>
          <input type="number" id="inpPoints" class="w-full border border-gray-300 px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-500 text-right" placeholder="0" value="0">
          <p class="text-xs text-gray-400 mt-1">* สามารถแก้ไขคะแนนด้วยมือได้</p>
        </div>
      </div>

      <div class="p-4 bg-gray-50 border-t flex justify-end gap-2">
        <button id="btnCancel" class="px-4 py-2 text-gray-600 hover:bg-gray-200 rounded">ยกเลิก</button>
        <button onclick="saveMember()" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded shadow">บันทึก</button>
      </div>
    </div>
  </div>

</main>

<?php include 'layouts/footer.php'; ?>

<script>
  let table;
  const MEMBER_API = 'api/members_api.php';

  $(document).ready(function() {

    // Config Alertify
    if(typeof alertify !== 'undefined') {
      alertify.defaults.glossary.title = 'แจ้งเตือน';
      alertify.defaults.glossary.ok = 'ตกลง';
      alertify.defaults.glossary.cancel = 'ยกเลิก';
    }

    // 1. Init DataTables
    table = $('#membersTable').DataTable({
      "ajax": {
        "url": `${MEMBER_API}?action=get_members`,
        "dataSrc": ""
      },
      "columns": [
        {
          "data": "code",
          "className": "font-bold text-blue-600",
          "render": function(data) {
            return `<i class="fas fa-id-card text-gray-400 mr-2"></i>${data}`;
          }
        },
        { "data": "name" },
        {
          "data": "points",
          "className": "text-right font-bold text-green-600",
          "render": $.fn.dataTable.render.number(',', '.', 0, '')
        },
        {
          "data": "created_at",
          "className": "text-center text-gray-500 text-xs",
          "render": function(data) {
            if(!data) return '-';
            // แปลงวันที่ให้สวยงาม (ถ้าต้องการ)
            return data.split(' ')[0];
          }
        },
        {
          "data": null,
          "className": "text-center",
          "render": function(data, type, row) {
            // Escape quote
            let jsonRow = JSON.stringify(row).replace(/"/g, '&quot;');
            return `
                            <button onclick="editMember(${jsonRow})" class="text-yellow-500 hover:text-yellow-600 mx-1 p-1" title="แก้ไข">
                                <i class="fas fa-edit fa-lg"></i>
                            </button>
                            <button onclick="deleteMember(${row.id}, '${row.name}')" class="text-red-500 hover:text-red-600 mx-1 p-1" title="ลบ">
                                <i class="fas fa-trash fa-lg"></i>
                            </button>
                        `;
          }
        }
      ],
      "order": [[ 0, "asc" ]]
    });

    // Event ปิด Modal
    $('#btnCloseModal, #btnCancel').click(function() {
      $('#memberModal').addClass('hidden');
    });
    $('#memberModal').on('click', function(e) {
      if (e.target === this) $('#memberModal').addClass('hidden');
    });
  });

  // เปิด Modal เพิ่มใหม่
  function openModal() {
    $('#modalTitle').text('เพิ่มสมาชิกใหม่');
    $('#memId').val('');
    $('#inpCode').val('');
    $('#inpName').val('');
    $('#inpPoints').val('0');
    $('#memberModal').removeClass('hidden');
    setTimeout(() => $('#inpCode').focus(), 100);
  }

  // เปิด Modal แก้ไข
  function editMember(mem) {
    $('#modalTitle').text('แก้ไขข้อมูลสมาชิก');
    $('#memId').val(mem.id);
    $('#inpCode').val(mem.code);
    $('#inpName').val(mem.name);
    $('#inpPoints').val(mem.points);
    $('#memberModal').removeClass('hidden');
  }

  // บันทึกข้อมูล
  async function saveMember() {
    const payload = {
      id: $('#memId').val(),
      code: $('#inpCode').val().trim(),
      name: $('#inpName').val().trim(),
      points: $('#inpPoints').val()
    };

    if(!payload.code || !payload.name) {
      return alertify.alert('ข้อมูลไม่ครบ', 'กรุณากรอกรหัสและชื่อสมาชิก');
    }

    try {
      const res = await fetch(`${MEMBER_API}?action=save_member`, {
        method: 'POST',
        body: JSON.stringify(payload)
      });
      const result = await res.json();

      if(result.success) {
        alertify.success(result.message);
        $('#memberModal').addClass('hidden');
        table.ajax.reload();
      } else {
        alertify.alert('บันทึกไม่สำเร็จ', result.message);
      }
    } catch(e) {
      console.error(e);
      alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อ');
    }
  }

  // ลบสมาชิก
  function deleteMember(id, name) {
    alertify.confirm('ยืนยันการลบ', `ต้องการลบสมาชิก <b>${name}</b> หรือไม่?`,
      async function() {
        try {
          const res = await fetch(`${MEMBER_API}?action=delete_member`, {
            method: 'POST',
            body: JSON.stringify({id})
          });
          const result = await res.json();

          if(result.success) {
            alertify.success('ลบเรียบร้อย');
            table.ajax.reload();
          } else {
            alertify.alert('ลบไม่สำเร็จ', result.message);
          }
        } catch(e) { alertify.error('Error deleting member'); }
      },
      function() { } // Cancel callback
    ).set('labels', {ok:'ลบ', cancel:'ยกเลิก'});
  }
</script>
