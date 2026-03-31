<?php
/**
 * api_users.php — API ดึงรายชื่อพนักงานสำหรับ Dropdown ผู้อนุมัติ
 */
require_once __DIR__ . '/../shared/config/db_connect.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = getServiceCenterPDO();
    
    // ดึงเฉพาะ user ที่ active
    $stmt = $pdo->prepare("SELECT id, employee_id, name, signature, role, branch, tags FROM users WHERE is_active = 1 ORDER BY name ASC");
    $stmt->execute();
    $users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => $users
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>
