<?php
require_once __DIR__ . '/auth.php';
requireAdmin();
require_once __DIR__ . '/../shared/config/db_connect.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    $pdo = getServiceCenterPDO();

    if ($action === 'list') {
        $stmt = $pdo->query("SELECT id, branch_code, branch_name FROM branches ORDER BY id ASC");
        $branches = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $branches]);
        exit;
    }

    if ($action === 'create') {
        $code = trim($_POST['branch_code'] ?? '');
        $name = trim($_POST['branch_name'] ?? '');
        
        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'กรุณาระบุชื่อสาขา']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT IGNORE INTO branches (branch_code, branch_name) VALUES (?, ?)");
        $stmt->execute([$code, $name]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'เพิ่มสาขาเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ชื่อสาขานี้มีอยู่แล้ว']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ไม่พบ ID สาขาที่จะลบ']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM branches WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'ลบสาขาเรียบร้อยแล้ว']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบสาขาได้']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action ไม่ถูกต้อง']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
