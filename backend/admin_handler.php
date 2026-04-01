<?php
/**
 * admin_handler.php — CRUD ผู้ใช้ (เฉพาะ Admin)
 */
require_once __DIR__ . '/../shared/config/db_connect.php';
require_once __DIR__ . '/auth.php';

requireAdmin();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $pdo = getServiceCenterPDO();

    // ============================================================
    // LIST — ดึงรายชื่อ User ทั้งหมด
    // ============================================================
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT id, employee_id, name, signature, role, branch, tags, is_active, created_at, updated_at FROM users ORDER BY id ASC");
        $users = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $users], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // CREATE — เพิ่ม User ใหม่
    // ============================================================
    if ($action === 'create') {
        $employee_id = trim($_POST['employee_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $signature = trim($_POST['signature'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $branch = trim($_POST['branch'] ?? '');
        $tags = $_POST['tags'] ?? '[]';

        if (empty($employee_id) || empty($name) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ (รหัสพนักงาน, ชื่อ, รหัสผ่าน)'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // ตรวจสอบรหัสพนักงานซ้ำ
        $check = $pdo->prepare("SELECT id FROM users WHERE employee_id = ?");
        $check->execute([$employee_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'รหัสพนักงานนี้มีอยู่แล้วในระบบ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO users (employee_id, name, signature, password, role, branch, tags) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$employee_id, $name, $signature, $password, $role, $branch, $tags]);

        echo json_encode(['success' => true, 'message' => 'เพิ่มผู้ใช้เรียบร้อยแล้ว', 'id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // UPDATE — แก้ไข User
    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        $employee_id = trim($_POST['employee_id'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $signature = trim($_POST['signature'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $branch = trim($_POST['branch'] ?? '');
        $tags = $_POST['tags'] ?? '[]';
        $is_active = intval($_POST['is_active'] ?? 1);

        if (!$id || empty($employee_id) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // ตรวจสอบรหัสพนักงานซ้ำ (ยกเว้นตัวเอง)
        $check = $pdo->prepare("SELECT id FROM users WHERE employee_id = ? AND id != ?");
        $check->execute([$employee_id, $id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'รหัสพนักงานนี้มีผู้ใช้คนอื่นใช้อยู่แล้ว'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!empty($password)) {
            $stmt = $pdo->prepare("UPDATE users SET employee_id=?, name=?, signature=?, password=?, role=?, branch=?, tags=?, is_active=? WHERE id=?");
            $stmt->execute([$employee_id, $name, $signature, $password, $role, $branch, $tags, $is_active, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET employee_id=?, name=?, signature=?, role=?, branch=?, tags=?, is_active=? WHERE id=?");
            $stmt->execute([$employee_id, $name, $signature, $role, $branch, $tags, $is_active, $id]);
        }

        echo json_encode(['success' => true, 'message' => 'แก้ไขผู้ใช้เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // DELETE — ลบ User (soft delete = is_active = 0)
    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบ ID'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // ป้องกันไม่ให้ลบตัวเองและไม่ให้ลบ Admin คนสุดท้าย
        if ($id == ($_SESSION['user_id'] ?? 0)) {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบตัวเองได้'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'ปิดการใช้งานผู้ใช้เรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // GET — ดึงข้อมูล User เดียว
    if ($action === 'get') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบ ID'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $stmt = $pdo->prepare("SELECT id, employee_id, name, signature, role, branch, tags, is_active FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user) {
            echo json_encode(['success' => true, 'data' => $user], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่พบผู้ใช้'], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'ไม่พบ action ที่ต้องการ'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
