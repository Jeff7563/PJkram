<?php
/**
 * sidebar.php - ส่วนเมนูข้างแบบ Component
 * @param string $currentPage ชื่อไฟล์ปัจจุบันเพื่อไฮไลท์เมนู (เช่น 'dashboard.php')
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db_connect.php';

$currentPage = $currentPage ?? basename($_SERVER['PHP_SELF']);
$isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
$userName = $_SESSION['user_name'] ?? 'ผู้ใช้งาน';
$userRole = $_SESSION['user_role'] ?? 'user';
$isLoggedIn = !empty($_SESSION['logged_in']);

// ดึงจำนวนเคสที่ต้องจัดการ (Pending)
$alertCount = 0;
try {
    $pdo = getServiceCenterPDO();
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM claims WHERE status IN ('Pending', 'Pending Fix')");
        $stmt->execute();
    } else {
        $userBranch = $_SESSION['user_branch'] ?? '';
        $stmt = $pdo->prepare("SELECT COUNT(id) FROM claims WHERE status IN ('Pending', 'Pending Fix') AND branch = ?");
        $stmt->execute([$userBranch]);
    }
    $alertCount = $stmt->fetchColumn() ?: 0;
} catch(Exception $e) { }
?>

<!-- Mobile Toggle Button -->
<button id="sidebarToggle" class="hamburger-btn">
    <i class="fas fa-bars"></i>
</button>

<!-- Navigation Sidebar -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="logo-container">
        <img src="https://i.ibb.co/svxDp4Y7/image.png" alt="Logo">
    </div>

    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-btn <?= ($currentPage == 'dashboard.php') ? 'active' : '' ?>">แดชบอร์ด</a>
        <a href="index.php" class="nav-btn <?= ($currentPage == 'index.php') ? 'active' : '' ?>">ฟอร์มส่งเคลม</a>
        
        <a href="history.php" class="nav-btn <?= ($currentPage == 'history.php') ? 'active' : '' ?> d-flex justify-content-between align-items-center">
            <span>ประวัติเคลม</span>
            <?php if($alertCount > 0 && !$isAdmin): ?>
                <span class="badge bg-danger rounded-pill"><?= $alertCount ?></span>
            <?php endif; ?>
        </a>
        
        <a href="check.php" class="nav-btn <?= ($currentPage == 'check.php') ? 'active' : '' ?> d-flex justify-content-between align-items-center">
            <span>ตรวจเช็ค</span>
            <?php if($alertCount > 0 && $isAdmin): ?>
                <span class="badge bg-danger rounded-pill"><?= $alertCount ?> ใหม่</span>
            <?php endif; ?>
        </a>
        
        <?php if ($isAdmin): ?>
            <a href="admin.php" class="nav-btn <?= ($currentPage == 'admin.php') ? 'active' : '' ?>" style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 20px;">จัดการผู้ใช้</a>
        <?php endif; ?>
    </nav>

    <!-- User Profile & Logout -->
    <?php if ($isLoggedIn): ?>
    <div class="sidebar-user-info">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div style="width: 44px; height: 44px; background: var(--primary-orange); color: #fff; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                <?= mb_substr($userName, 0, 1, 'UTF-8') ?>
            </div>
            <div style="overflow: hidden; flex: 1;">
                <div style="font-weight: 700; font-size: 0.9rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    <?= htmlspecialchars($userName) ?>
                </div>
                <div style="font-size: 0.7rem; color: #777; text-transform: uppercase;">
                    <?= $isAdmin ? '🛡️ Admin' : '👤 User' ?>
                </div>
            </div>
        </div>
        <a href="logout.php" class="btn-pill btn-red w-100 py-2">
            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
        </a>
    </div>
    <?php endif; ?>
</aside>

<!-- Sidebar JS Helper -->
<script src="../shared/assets/js/sidebar.js"></script>
<?php include __DIR__ . '/../components/toast.php'; ?>
