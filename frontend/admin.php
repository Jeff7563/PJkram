<?php
require_once __DIR__ . '/../backend/auth.php';
requireAdmin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>จัดการผู้ใช้และสิทธิ์ — ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles-admin.css">
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

  <?php $current_page = 'admin.php'; include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="container-fluid p-0">

      <div class="admin-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h2>จัดการผู้ใช้และสิทธิ์</h2>
          <p>เพิ่ม แก้ไข และกำหนดสิทธิ์การใช้งานระบบเคลม</p>
        </div>
        <div class="d-flex gap-2">
          <button class="btn btn-light fw-bold px-3 py-2" style="border-radius: 12px; font-size: 0.9rem; color: var(--orange); border:none; box-shadow: 0 4px 12px rgba(0,0,0,0.1);" 
                  data-bs-toggle="modal" data-bs-target="#branchModal">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="vertical-align:-2px; margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            เพิ่มสาขา
          </button>
          <button class="btn-add-user" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreateModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-2px; margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            เพิ่มผู้ใช้ใหม่
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="search-bar flex-grow-1" style="max-width:400px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchUsers" placeholder="ค้นหาชื่อ, รหัสพนักงาน...">
        </div>
        <div class="flex-grow-1" style="max-width: 250px;">
          <select id="filterBranch" class="form-select border-2 py-2 px-3 fw-bold" style="border-radius: 14px; color: var(--orange);">
            <option value="">ทุกสาขาทั้งหมด</option>
            <!-- ทยอยใส่จาก JS -->
          </select>
        </div>
      </div>

      <!-- Admin Zone -->
      <div class="mb-5">
          <div class="d-flex align-items-center mb-3">
              <span class="fs-5 fw-bold" style="color: #e65100;">ผู้ดูแลระบบ (Admin)</span>
              <span class="badge bg-danger ms-2" id="adminCount">0</span>
              <div class="flex-grow-1 border-bottom ms-3" style="border-color: #ffd1b3 !important;"></div>
          </div>
          <div id="adminList" class="row g-3">
              <div class="col-12 text-center py-4 text-muted"><div class="spinner-border text-secondary spinner-border-sm" role="status"></div> กำลังโหลดข้อมูล...</div>
          </div>
      </div>

      <!-- User Zone -->
      <div class="mb-5">
          <div class="d-flex align-items-center mb-3">
              <span class="fs-5 fw-bold text-dark">ผู้ใช้งานระบบ (User / พนักงานสาขา)</span>
              <span class="badge bg-secondary ms-2" id="userCount">0</span>
              <div class="flex-grow-1 border-bottom ms-3"></div>
          </div>
          <div id="userList" class="row g-3">
              <div class="col-12 text-center py-4 text-muted"><div class="spinner-border text-secondary spinner-border-sm" role="status"></div> กำลังโหลดข้อมูล...</div>
          </div>
      </div>

    </div>
  </div>

  <!-- Modal: Create/Edit User -->
  <div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content shadow-lg">
        <div class="modal-header px-4 py-3">
          <h5 class="modal-title fw-bold" id="modalTitle">เพิ่มผู้ใช้ใหม่</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-4 py-4">
          <input type="hidden" id="editUserId" value="">
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">รหัสพนักงาน <span class="text-danger">*</span></label>
              <input type="text" id="formEmpId" class="form-control" placeholder="เช่น EMP001" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
              <input type="text" id="formName" class="form-control" placeholder="ชื่อ นามสกุล" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">ลายเซ็นต์</label>
              <input type="text" id="formSignature" class="form-control" placeholder="ลายเซ็นต์ (ข้อความ)">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">รหัสผ่าน <span class="text-danger" id="passRequired">*</span></label>
              <input type="text" id="formPassword" class="form-control" placeholder="รหัสผ่าน">
              <small class="text-muted" id="passHint" style="display:none;">เว้นว่างถ้าไม่ต้องการเปลี่ยน</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">สาขา</label>
              <select id="formBranch" class="form-select">
                <option value="">-- เลือกสาขา --</option>
                <!-- ทยอยใส่จาก JS -->
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">สิทธิ์ (Role)</label>
              <select id="formRole" class="form-select">
                <option value="user">User — ผู้ใช้ทั่วไป</option>
                <option value="admin">Admin — ผู้ดูแลระบบ</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-bold">Tag ประเภทงาน</label>
              <p class="text-muted small mb-2">เลือก Tag ที่ User คนนี้รับผิดชอบ (Admin มีสิทธิ์ทุก Tag อัตโนมัติ)</p>
              <div class="d-flex flex-wrap">
                <label class="form-check-tag">
                  <input type="checkbox" class="form-check-input tag-check" value="repairBranch"> ซ่อมที่สาขา
                </label>
                <label class="form-check-tag">
                  <input type="checkbox" class="form-check-input tag-check" value="sendHQ"> ส่งซ่อมที่ สนญ.
                </label>
                <label class="form-check-tag">
                  <input type="checkbox" class="form-check-input tag-check" value="replaceVehicle"> เปลี่ยนคัน
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer px-4 py-3">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
          <button type="button" class="btn text-white px-4 fw-bold" id="btnSaveUser" 
                  style="background: linear-gradient(135deg, var(--orange), var(--orange-light));" onclick="saveUser()">
            บันทึก
          </button>
        </div>
      </div>
    </div>
  </div>

  </div>

  <!-- Modal: Add/Manage Branches -->
  <div class="modal fade" id="branchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
        <div class="modal-header px-4 py-3 border-0" style="background: linear-gradient(135deg, #ff7a32, #ff9e68); color: white;">
          <h5 class="modal-title fw-bold">จัดการสาขาในระบบ</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 py-4">
          <div class="row g-3 mb-4 p-3 bg-light rounded-4">
            <div class="col-md-4">
               <label class="form-label fw-bold text-secondary mb-2">รหัสสาขา</label>
               <input type="text" id="newBranchCode" class="form-control" style="border-radius:12px; padding: 12px; border: 2px solid #ddd;" placeholder="เช่น BR001">
            </div>
            <div class="col-md-5">
               <label class="form-label fw-bold text-secondary mb-2">ชื่อสาขา <span class="text-danger">*</span></label>
               <input type="text" id="newBranchName" class="form-control" style="border-radius:12px; padding: 12px; border: 2px solid #ddd;" placeholder="เช่น สาขา อุดรธานี">
            </div>
            <div class="col-md-3 d-flex align-items-end">
               <button class="btn text-white fw-bold w-100" style="background: var(--orange); border-radius:12px; height: 50px;" onclick="saveBranch()">
                 บันทึก
               </button>
            </div>
          </div>

          <div class="border-top pt-4">
            <label class="form-label fw-bold text-secondary mb-3">รายการสาขาปัจจุบัน</label>
            <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
              <table class="table table-hover align-middle">
                <thead class="table-light sticky-top">
                  <tr>
                    <th>รหัส</th>
                    <th>ชื่อสาขา</th>
                    <th width="80" class="text-center">จัดการ</th>
                  </tr>
                </thead>
                <tbody id="branchListBody">
                  <!-- JS ทยอยใส่ให้ -->
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const TAG_LABELS = {
      repairBranch: { label: 'ซ่อมที่สาขา', cls: 'tag-repair' },
      sendHQ: { label: 'ส่งซ่อม สนญ.', cls: 'tag-sendhq' },
      replaceVehicle: { label: 'เปลี่ยนคัน', cls: 'tag-replace' }
    };

    let allUsers = [];

    // โหลดรายชื่อผู้ใช้
    async function loadUsers() {
      try {
        const res = await fetch('../backend/admin_handler.php?action=list');
        const json = await res.json();
        if (json.success) {
          allUsers = json.data;
          renderUsers(allUsers);
        }
      } catch (e) {
        document.getElementById('userList').innerHTML = '<div class="col-12 text-center text-danger py-5">❌ ไม่สามารถโหลดข้อมูลได้</div>';
      }
    }

    function renderUsers(users) {
      const adminContainer = document.getElementById('adminList');
      const userContainer = document.getElementById('userList');
      const adminCountBadge = document.getElementById('adminCount');
      const userCountBadge = document.getElementById('userCount');

      if (users.length === 0) {
        adminContainer.innerHTML = '<div class="col-12 text-center text-muted py-4">ไม่พบผู้ดูแลระบบตามเงื่อนไขที่ค้นหา</div>';
        userContainer.innerHTML = '<div class="col-12 text-center text-muted py-4">ไม่พบผู้ใช้ตามเงื่อนไขที่ค้นหา</div>';
        adminCountBadge.textContent = '0';
        userCountBadge.textContent = '0';
        return;
      }

      let adminHtml = '';
      let userHtml = '';
      let aCount = 0;
      let uCount = 0;

      users.forEach(u => {
        const tags = JSON.parse(u.tags || '[]');
        const tagHtml = tags.map(t => {
          const info = TAG_LABELS[t] || { label: t, cls: '' };
          return `<span class="tag-pill ${info.cls}">${info.label}</span>`;
        }).join('');

        const roleBadge = u.role === 'admin'
          ? '<span class="badge-role badge-admin">Admin</span>'
          : '<span class="badge-role badge-user">User</span>';

        const activeBadge = u.is_active == 0
          ? '<span class="badge-role badge-inactive ms-1">ปิดใช้งาน</span>'
          : '';

        const opacity = u.is_active == 0 ? 'opacity: 0.5;' : '';
        const cardStyle = u.role === 'admin' ? 'border-left: 4px solid var(--orange);' : 'border-left: 4px solid #1565c0;';

        const uHtml = `
          <div class="col-12 col-lg-6" data-search="${(u.employee_id + ' ' + u.name + ' ' + u.branch).toLowerCase()}" data-branch="${u.branch || ''}">
            <div class="user-card d-flex justify-content-between align-items-start" style="${opacity} ${cardStyle}">
              <div class="flex-grow-1" style="min-width: 0;">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                  <span class="fw-bold fs-6 text-dark" style="letter-spacing: -0.2px;">${escHtml(u.name)}</span>
                  ${roleBadge}${activeBadge}
                </div>
                <div class="text-muted small mb-1 d-flex flex-wrap gap-x-3 gap-y-1">
                  <span><strong>รหัส:</strong> ${escHtml(u.employee_id)}</span>
                  <span><strong>สาขา:</strong> ${escHtml(u.branch || '-')}</span>
                  <span><strong>ลายเซ็น:</strong> ${escHtml(u.signature || '-')}</span>
                </div>
                <div class="mt-3 d-flex flex-wrap gap-1">${tagHtml || '<span class="text-muted small border p-1 px-2 rounded-pill" style="font-size:0.7rem; background:#fcfcfc;">ไม่มี Tag กรองเขตงาน</span>'}</div>
              </div>
              <div class="d-flex gap-2 flex-shrink-0 ms-3">
                <button class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" 
                        style="width:32px; height:32px; border-radius:10px; border-color:#e2e8f0;"
                        onclick="openEditModal(${u.id})" title="แก้ไข">
                   <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                ${u.id != <?= $_SESSION['user_id'] ?? 0 ?> ? `
                <button class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center" 
                        style="width:32px; height:32px; border-radius:10px; border-color:#fee2e2;"
                        onclick="deleteUser(${u.id}, '${escHtml(u.name)}')" title="ลบผู้ใช้">
                   <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>` : ''}
              </div>
            </div>
          </div>
        `;

        if (u.role === 'admin') { adminHtml += uHtml; aCount++; }
        else { userHtml += uHtml; uCount++; }
      });

      adminContainer.innerHTML = adminHtml || '<div class="col-12 text-center text-muted py-4">ไม่มีผู้ดูแลระบบในระบบ</div>';
      userContainer.innerHTML = userHtml || '<div class="col-12 text-center text-muted py-4">ไม่มีผู้ใช้งานในระบบ</div>';
      adminCountBadge.textContent = aCount;
      userCountBadge.textContent = uCount;
    }

    function escHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }

    // Search / Filter ผู้ใช้
    function applyFilters() {
      const q = document.getElementById('searchUsers').value.toLowerCase().trim();
      const bOption = document.getElementById('filterBranch').value;
      const filtered = allUsers.filter(u => {
          const matchQ = (u.employee_id + ' ' + u.name + ' ' + (u.branch || '')).toLowerCase().includes(q);
          const matchB = bOption === '' || u.branch === bOption;
          return matchQ && matchB;
      });
      renderUsers(filtered);
    }

    document.getElementById('searchUsers').addEventListener('input', applyFilters);
    document.getElementById('filterBranch').addEventListener('change', applyFilters);

    // Modal: เปิดสร้างใหม่
    function openCreateModal() {
      document.getElementById('modalTitle').textContent = 'เพิ่มผู้ใช้ใหม่';
      document.getElementById('editUserId').value = '';
      document.getElementById('formEmpId').value = '';
      document.getElementById('formName').value = '';
      document.getElementById('formSignature').value = '';
      document.getElementById('formPassword').value = '';
      document.getElementById('formBranch').value = '';
      document.getElementById('formRole').value = 'user';
      document.querySelectorAll('.tag-check').forEach(c => c.checked = false);
      document.getElementById('passRequired').style.display = '';
      document.getElementById('passHint').style.display = 'none';
    }

    // Modal: เปิดแก้ไข
    async function openEditModal(id) {
      const res = await fetch('../backend/admin_handler.php?action=get&id=' + id);
      const json = await res.json();
      if (!json.success) { alert('ไม่พบข้อมูลผู้ใช้'); return; }

      const u = json.data;
      document.getElementById('modalTitle').textContent = 'แก้ไขผู้ใช้: ' + u.name;
      document.getElementById('editUserId').value = u.id;
      document.getElementById('formEmpId').value = u.employee_id;
      document.getElementById('formName').value = u.name;
      document.getElementById('formSignature').value = u.signature || '';
      document.getElementById('formPassword').value = '';
      document.getElementById('formBranch').value = u.branch || '';
      document.getElementById('formRole').value = u.role;
      document.getElementById('passRequired').style.display = 'none';
      document.getElementById('passHint').style.display = '';

      const tags = JSON.parse(u.tags || '[]');
      document.querySelectorAll('.tag-check').forEach(c => {
        c.checked = tags.includes(c.value);
      });

      const modal = new bootstrap.Modal(document.getElementById('userModal'));
      modal.show();
    }

    // บันทึก User
    async function saveUser() {
      const id = document.getElementById('editUserId').value;
      const empId = document.getElementById('formEmpId').value.trim();
      const name = document.getElementById('formName').value.trim();
      const signature = document.getElementById('formSignature').value.trim();
      const password = document.getElementById('formPassword').value.trim();
      const branch = document.getElementById('formBranch').value;
      const role = document.getElementById('formRole').value;

      if (!empId || !name) {
        alert('กรุณากรอกรหัสพนักงานและชื่อ-นามสกุล');
        return;
      }
      if (!id && !password) {
        alert('กรุณากรอกรหัสผ่าน');
        return;
      }

      const tags = [];
      document.querySelectorAll('.tag-check:checked').forEach(c => tags.push(c.value));

      const fd = new FormData();
      fd.append('action', id ? 'update' : 'create');
      if (id) fd.append('id', id);
      fd.append('employee_id', empId);
      fd.append('name', name);
      fd.append('signature', signature);
      fd.append('password', password);
      fd.append('role', role);
      fd.append('branch', branch);
      fd.append('tags', JSON.stringify(tags));
      fd.append('is_active', '1');

      try {
        const res = await fetch('../backend/admin_handler.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
          bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
          loadUsers();
        } else {
          alert('❌ ' + json.message);
        }
      } catch (e) {
        alert('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ');
      }
    }

    // ลบ User
    async function deleteUser(id, name) {
      if (!confirm('ต้องการลบผู้ใช้ "' + name + '" จริงหรือไม่? (ข้อมูลจะถูกลบออกจากระบบอย่างถาวร)')) return;

      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('id', id);

      try {
        const res = await fetch('../backend/admin_handler.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
          loadUsers();
        } else {
          alert('❌ ' + json.message);
        }
      } catch (e) {
        alert('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ');
      }
    }

    // ระบบจัดการสาขาแบบไดนามิก
    async function loadBranches() {
      try {
        const res = await fetch('../backend/branch_handler.php?action=list');
        const json = await res.json();
        if (json.success) {
          const filterSel = document.getElementById('filterBranch');
          const formSel = document.getElementById('formBranch');
          const listBody = document.getElementById('branchListBody');
          
          let optionsHtml = '<option value="">ทุกสาขาทั้งหมด</option>';
          let modalOptionsHtml = '<option value="">-- เลือกสาขา --</option>';
          let listHtml = '';

          json.data.forEach(b => {
            const bTxt = b.branch_code ? `[${b.branch_code}] ${b.branch_name}` : b.branch_name;
            optionsHtml += `<option value="${escHtml(b.branch_name)}">${escHtml(b.branch_name)}</option>`;
            modalOptionsHtml += `<option value="${escHtml(b.branch_name)}">${escHtml(b.branch_name)}</option>`;
            
            listHtml += `
              <tr>
                <td class="fw-bold">${escHtml(b.branch_code || '-')}</td>
                <td>${escHtml(b.branch_name)}</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteBranch(${b.id}, '${escHtml(b.branch_name)}')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </td>
              </tr>
            `;
          });

          if (filterSel) filterSel.innerHTML = optionsHtml;
          if (formSel) formSel.innerHTML = modalOptionsHtml;
          if (listBody) listBody.innerHTML = listHtml || '<tr><td colspan="3" class="text-center text-muted">ไม่พบข้อมูลสาขา</td></tr>';
        }
      } catch (e) {
        console.error("Error loading branches:", e);
      }
    }

    async function saveBranch() {
      const code = document.getElementById('newBranchCode').value.trim();
      const name = document.getElementById('newBranchName').value.trim();
      if (!name) {
        alert('กรุณาระบุชื่อสาขา');
        return;
      }

      const fd = new FormData();
      fd.append('action', 'create');
      fd.append('branch_code', code);
      fd.append('branch_name', name);

      try {
        const res = await fetch('../backend/branch_handler.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
          document.getElementById('newBranchCode').value = '';
          document.getElementById('newBranchName').value = '';
          if(typeof showToast === 'function') showToast('✅ เพิ่มสาขาเรียบร้อยแล้ว', 'success');
          else alert('เพิ่มสาขาเรียบร้อยแล้ว');
          loadBranches();
        } else {
          alert('❌ ' + json.message);
        }
      } catch (e) {
        alert('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ');
      }
    }

    async function deleteBranch(id, name) {
      if (!confirm(`ต้องการลบ "${name}" จริงหรือไม่?`)) return;
      
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('id', id);

      try {
        const res = await fetch('../backend/branch_handler.php', { method: 'POST', body: fd });
        const json = await res.json();
        if (json.success) {
          if(typeof showToast === 'function') showToast('✅ ลบสาขาเรียบร้อยแล้ว', 'success');
          else alert('ลบสาขาเรียบร้อยแล้ว');
          loadBranches();
        } else {
          alert('❌ ' + json.message);
        }
      } catch (e) {
        alert('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ');
      }
    }

    // โหลดตอนเปิดหน้า
    loadUsers();
    loadBranches();
  </script>
</body>
</html>
