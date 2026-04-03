<?php
/**
 * auth.php — ระบบ Session Login / Logout / ตรวจสอบสิทธิ์
 * ใช้ง่ายๆ: รหัสพนักงาน + รหัสผ่าน (plain text ตาม V2)
 */

require_once __DIR__ . '/../shared/config/db_connect.php';

// เริ่ม Session (ถ้ายังไม่ได้เริ่ม)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Login — ตรวจสอบรหัสพนักงาน + รหัสผ่าน
 * @return array|false ข้อมูล user ถ้าถูกต้อง, false ถ้าผิด
 */
function doLogin($employeeId, $password) {
    try {
        $pdo = getServiceCenterPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE employee_id = ? AND is_active = 1");
        $stmt->execute([$employeeId]);
        $user = $stmt->fetch();

        if ($user && $user['password'] === $password) {
            // บันทึกข้อมูลลง Session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_branch'] = $user['branch'];
            $_SESSION['user_tags'] = json_decode($user['tags'] ?: '[]', true);
            $_SESSION['user_signature'] = $user['signature'];
            $_SESSION['logged_in'] = true;
            return $user;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Logout — ลบ Session ทั้งหมด
 */
function doLogout() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * ตรวจสอบว่า Login อยู่หรือไม่ — ถ้าไม่ ก็ redirect ไปหน้า Login
 */
function requireLogin() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['logged_in'])) {
        // ตรวจสอบว่าอยู่ในโฟลเดอร์ backend หรือไม่
        $currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
        $redirectUrl = ($currentDir === 'backend') ? '../frontend/login.php' : 'login.php';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * ตรวจสอบว่า Login อยู่หรือไม่ — ถ้าไม่ ก็ redirect (สำหรับ backend)
 */
function requireLoginBackend() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['logged_in'])) {
        header('Location: ../frontend/login.php');
        exit;
    }
}

/**
 * ตรวจสอบว่าเป็น Admin หรือไม่
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * ตรวจสอบว่าเป็น Admin — ถ้าไม่ ก็ redirect กลับ
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $currentDir = basename(dirname($_SERVER['SCRIPT_NAME']));
        $redirectUrl = ($currentDir === 'backend') ? '../frontend/index.php' : 'index.php';
        header('Location: ' . $redirectUrl);
        exit;
    }
}

/**
 * ดึง Tags ของ User ปัจจุบัน
 * @return array เช่น ["repairBranch", "sendHQ"]
 */
function getUserTags() {
    return $_SESSION['user_tags'] ?? [];
}

/**
 * ตรวจสอบว่า User มี Tag นี้หรือไม่
 */
function hasTag($tag) {
    if (isAdmin()) return true; // Admin มีทุก Tag
    $tags = getUserTags();
    return in_array($tag, $tags);
}

/**
 * ดึงข้อมูล User ปัจจุบัน
 */
function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'employee_id' => $_SESSION['employee_id'] ?? '',
        'name' => $_SESSION['user_name'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'user',
        'branch' => $_SESSION['user_branch'] ?? '',
        'tags' => $_SESSION['user_tags'] ?? [],
        'signature' => $_SESSION['user_signature'] ?? '',
    ];
}
?>
