<?php
  if (session_status() === PHP_SESSION_NONE) session_start();
  
  // Include DB to query alert counts
  require_once __DIR__ . '/../../config/db_connect.php';
  
  $current_page = basename($_SERVER['PHP_SELF']);
  $_sidebar_is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
  $_sidebar_user_name = $_SESSION['user_name'] ?? '';
  $_sidebar_user_role = $_SESSION['user_role'] ?? '';
  $_sidebar_logged_in = !empty($_SESSION['logged_in']);
  
  // โลจิกดึงจำนวนเคสที่ "รอดำเนินการ" และ "รอแก้ไข"
  $pending_claims_count = 0;
  try {
      if (function_exists('getServiceCenterPDO')) {
          $pdo = getServiceCenterPDO();
          // ถ้าเป็น Admin เห็นทั้งหมด, ถ้าเป็น User เห็นแค่สาขาตัวเอง
          if ($_sidebar_is_admin) {
              $stmtAlert = $pdo->prepare("SELECT COUNT(id) FROM claims WHERE status IN ('Pending', 'Pending Fix')");
              $stmtAlert->execute();
          } else {
              $userBranch = $_SESSION['branch'] ?? '';
              $stmtAlert = $pdo->prepare("SELECT COUNT(id) FROM claims WHERE status IN ('Pending', 'Pending Fix') AND branch = ?");
              $stmtAlert->execute([$userBranch]);
          }
          $pending_claims_count = $stmtAlert->fetchColumn() ?: 0;
      }
  } catch(Exception $e) { }
?>
<button id="sidebarToggle" class="hamburger-btn" aria-label="Toggle menu">☰</button>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<div class="sidebar" id="sidebar">
  <div class="logo-container">
    <img src="https://i.ibb.co/svxDp4Y7/image.png" alt="อึ้งกุ่ยเฮง Logo">
  </div>
  <div class="nav-menu">
    <a href="<?= BASE_URL_FRONTEND ?>/dashboard.php" class="nav-btn <?= ($current_page == 'dashboard.php') ? 'active' : '';?>">แดชบอร์ด</a>
    <a href="<?= BASE_URL_FRONTEND ?>/index.php" class="nav-btn <?= ($current_page == 'index.php') ? 'active' : '';?>">ฟอร์มส่งเคลม</a>
    
    <a href="<?= BASE_URL_FRONTEND ?>/history.php" class="nav-btn <?= ($current_page == 'history.php') ? 'active' : '';?> d-flex justify-content-between align-items-center">
        <span>ประวัติเคลม</span>
        <?php if($pending_claims_count > 0 && !$_sidebar_is_admin): ?>
            <span class="badge bg-danger rounded-pill shadow-sm" style="font-size:0.75rem; padding: 4px 8px;"><?= $pending_claims_count ?> รายการ</span>
        <?php endif; ?>
    </a>
    
    <a href="<?= BASE_URL_FRONTEND ?>/check.php" class="nav-btn <?= ($current_page == 'check.php') ? 'active' : '';?> d-flex justify-content-between align-items-center">
        <span>ตรวจเช็ค</span>
        <?php if($pending_claims_count > 0 && $_sidebar_is_admin): ?>
            <span class="badge bg-danger rounded-pill shadow-sm animate-pulse" style="font-size:0.75rem; padding: 4px 8px;"><?= $pending_claims_count ?> ใหม่</span>
        <?php endif; ?>
    </a>
    
    <?php if ($_sidebar_is_admin): ?>
    <a href="<?= BASE_URL_FRONTEND ?>/admin.php" class="nav-btn <?= ($current_page == 'admin.php') ? 'active' : '';?>" style="margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 16px;">จัดการผู้ใช้/สิทธิ์</a>
    <?php endif; ?>
  </div>

<style>
  .sidebar-user-info {
    margin-top: auto; 
    padding: 25px 20px; 
    border-top: 1px solid #eee; 
    width: 100%;
  }
  .btn-logout {
    background-color: #f8f9fa !important;
    color: #666 !important;
    font-size: 0.9rem !important;
    padding: 10px !important;
    border-radius: 12px !important;
    margin-top: 8px !important;
    border: 1px solid #eee !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease !important;
    text-decoration: none;
    font-weight: 500;
  }
  .btn-logout:hover {
    background-color: #fff1f0 !important;
    color: #e74c3c !important;
    border-color: #ffa39e !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(231, 76, 60, 0.1);
  }
</style>

  <?php if ($_sidebar_logged_in): ?>
  <div class="sidebar-user-info">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
        <div style="width: 44px; height: 44px; background: linear-gradient(135deg, #f2722b, #ff9b50); color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(242, 114, 43, 0.2); border: 2px solid #fff;">
            <?= mb_substr($_sidebar_user_name, 0, 1, 'UTF-8') ?>
        </div>
        <div style="overflow: hidden; flex: 1;">
            <div style="font-weight: 700; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #333; margin-bottom: 2px;">
                <?= htmlspecialchars($_sidebar_user_name) ?>
            </div>
            <div style="font-size: 0.75rem; color: rgba(0,0,0,0.6); display: flex; align-items: center; gap: 4px;">
                <span style="font-size: 0.9rem;"><?= $_sidebar_is_admin ? '🛡️' : '👤' ?></span>
                <span style="text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;"><?= $_sidebar_is_admin ? 'Admin' : 'User' ?></span>
            </div>
        </div>
    </div>
    <a href="<?= BASE_URL_FRONTEND ?>/logout.php" class="btn-logout">
        <span>🚪 ออกจากระบบ</span>
    </a>
</div>
  <?php endif; ?>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  const overlay = document.getElementById('sidebarOverlay');

  function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('visible');
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('visible');
  }

  toggle.addEventListener('click', () => {
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
  });

  overlay.addEventListener('click', closeSidebar);

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) closeSidebar();
  });

  // Toast Helper
  window.showToast = function(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastMsg = document.getElementById('toastMessage');
    if (!toastEl || !toastMsg) return;

    toastMsg.textContent = message;
    toastEl.classList.remove('bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-dark');
    
    if (type === 'success') {
        toastEl.classList.add('bg-success');
    } else if (type === 'error' || type === 'danger') {
        toastEl.classList.add('bg-danger');
    } else if (type === 'warning') {
        toastEl.classList.add('bg-warning', 'text-dark');
    } else {
        toastEl.classList.add('bg-primary');
    }

    const toast = new bootstrap.Toast(toastEl);
    toast.show();
  };
</script>

<!-- Toast Container -->
<!-- <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 10000;">
  <div id="liveToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px; min-width: 250px;">
    <div class="d-flex p-2">
      <div class="toast-body fs-6 fw-medium" id="toastMessage">
        ข้อความแจ้งเตือน
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div> -->

