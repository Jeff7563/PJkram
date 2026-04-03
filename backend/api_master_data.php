<?php
/**
 * api_master_data.php — Public API สำหรับดึงข้อมูลมาสเตอร์ (Read-Only)
 * ใช้ในฟอร์ม index.php, edit.php, verify.php เพื่อโหลด dropdown
 */
require_once __DIR__ . '/../shared/config/db_connect.php';
require_once __DIR__ . '/auth.php';

requireLogin(); // ต้อง login แต่ไม่ต้องเป็น admin

header('Content-Type: application/json; charset=utf-8');

$type = $_GET['type'] ?? '';

$tableMap = [
    'brands'           => 'master_brands',
    'grades'           => 'master_grades',
    'claim_categories' => 'master_claim_categories',
    'statuses'         => 'master_statuses',
];

if (!isset($tableMap[$type])) {
    echo json_encode(['success' => false, 'message' => 'ประเภทข้อมูลไม่ถูกต้อง'], JSON_UNESCAPED_UNICODE);
    exit;
}

$table = $tableMap[$type];

try {
    $pdo = getServiceCenterPDO();
    $stmt = $pdo->query("SELECT * FROM `$table` WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $data = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
