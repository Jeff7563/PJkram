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
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --orange: #e65100; --orange-light: #ff7a1a; }
    body { font-family: 'Kanit', sans-serif; }

    .admin-header {
      background: linear-gradient(135deg, var(--orange), var(--orange-light));
      color: #fff;
      padding: 24px 32px;
      border-radius: 20px;
      margin-bottom: 24px;
      box-shadow: 0 16px 48px rgba(230, 81, 0, 0.15);
    }
    .admin-header h2 { margin: 0; font-weight: 800; font-size: 1.4rem; }
    .admin-header p { margin: 4px 0 0; opacity: 0.85; font-size: 0.9rem; }

    .user-card {
      background: #fff;
      border-radius: 16px;
      border: 1px solid #f0f0f0;
      padding: 20px;
      margin-bottom: 16px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.04);
      transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .user-card:hover {
      transform: translateY(-3px);
      box-shadow: 0 14px 40px rgba(0,0,0,0.08);
    }

    .badge-role {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.78rem;
      font-weight: 700;
    }
    .badge-admin { background: linear-gradient(135deg, var(--orange), var(--orange-light)); color: #fff; }
    .badge-user { background: #e3f2fd; color: #1565c0; }
    .badge-inactive { background: #f5f5f5; color: #999; }

    .tag-pill {
      display: inline-block;
      padding: 3px 10px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      margin: 2px;
    }
    .tag-repair { background: #e8f5e9; color: #2e7d32; }
    .tag-sendhq { background: #e3f2fd; color: #1565c0; }
    .tag-replace { background: #fce4ec; color: #c62828; }

    .btn-add-user {
      background: linear-gradient(135deg, var(--orange), var(--orange-light));
      color: #fff;
      border: none;
      padding: 10px 20px;
      border-radius: 12px;
      font-weight: 700;
      font-family: 'Kanit', sans-serif;
      cursor: pointer;
      box-shadow: 0 6px 20px rgba(230, 81, 0, 0.15);
      transition: all 0.15s ease;
    }
    .btn-add-user:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(230, 81, 0, 0.2); color: #fff; }

    .modal-content { border-radius: 20px; border: none; }
    .modal-header { border-bottom: 2px solid #f5f5f5; }
    .modal-footer { border-top: 2px solid #f5f5f5; }

    .form-check-tag { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 12px; border: 2px solid #eee; margin: 4px; cursor: pointer; transition: all 0.15s ease; }
    .form-check-tag:has(input:checked) { border-color: var(--orange); background: rgba(230, 81, 0, 0.05); }
    .form-check-tag input { accent-color: var(--orange); }

    .search-bar { position: relative; }
    .search-bar input {
      padding: 12px 18px 12px 44px;
      border-radius: 14px;
      border: 2px solid #eee;
      font-size: 0.95rem;
      width: 100%;
      font-family: 'Kanit', sans-serif;
      transition: border-color 0.2s;
    }
    .search-bar input:focus { border-color: var(--orange); outline: none; box-shadow: 0 0 0 4px rgba(230, 81, 0, 0.06); }
    .search-bar svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #aaa; }
  </style>
</head>
<body>

  <?php $current_page = 'admin.php'; include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="container-fluid p-0">

      <div class="admin-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h2>🛡️ จัดการผู้ใช้และสิทธิ์</h2>
          <p>เพิ่ม แก้ไข และกำหนดสิทธิ์การใช้งานระบบเคลม</p>
        </div>
        <button class="btn-add-user" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreateModal()">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-2px; margin-right:6px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          เพิ่มผู้ใช้ใหม่
        </button>
      </div>

      <!-- Filters -->
      <div class="d-flex flex-wrap gap-3 mb-4">
        <div class="search-bar flex-grow-1" style="max-width:400px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="searchUsers" placeholder="ค้นหาชื่อ, รหัสพนักงาน...">
        </div>
        <div>
          <select id="filterBranch" class="form-select border-2 py-2 px-3 fw-bold" style="border-radius: 14px; color: var(--orange);">
            <option value="">ทุกสาขาทั้งหมด</option>
            <option value="สาขา สกลนคร">สาขา สกลนคร</option>
            <!-- ใส่ตรรกะดึงสาขาอื่นๆถ้ามี -->
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
                <option value="สาขา สกลนคร">สาขา สกลนคร</option>
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
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 mb-2">
                  <span class="fw-bold fs-6">${escHtml(u.name)}</span>
                  ${roleBadge}${activeBadge}
                </div>
                <div class="text-muted small mb-1">
                  <strong>รหัส:</strong> ${escHtml(u.employee_id)} &nbsp;|&nbsp;
                  <strong>สาขา:</strong> ${escHtml(u.branch || '-')} &nbsp;|&nbsp;
                  <strong>ลายเซ็น:</strong> ${escHtml(u.signature || '-')}
                </div>
                <div class="mt-2">${tagHtml || '<span class="text-muted small border p-1 rounded">ไม่มี Tag กรองโซนซ่อม</span>'}</div>
              </div>
              <div class="d-flex gap-1 flex-shrink-0 ms-3">
                <button class="btn btn-sm btn-outline-secondary" onclick="openEditModal(${u.id})" title="แก้ไข">✏️</button>
                ${u.id != <?= $_SESSION['user_id'] ?? 0 ?> ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${u.id}, '${escHtml(u.name)}')" title="ปิดการใช้งาน">🗑️</button>` : ''}
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
      if (!confirm('ต้องการปิดการใช้งาน "' + name + '" จริงหรือไม่?')) return;

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

    // โหลดตอนเปิดหน้า
    loadUsers();
  </script>
</body>
</html>
