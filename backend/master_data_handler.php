<?php
/**
 * master_data_handler.php — CRUD ข้อมูลมาสเตอร์ (เฉพาะ Admin)
 * รองรับ: master_brands, master_grades, master_claim_categories, master_statuses
 */
require_once __DIR__ . '/../shared/config/db_connect.php';
require_once __DIR__ . '/auth.php';

requireAdmin();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$type = $_POST['type'] ?? $_GET['type'] ?? '';

// Map type → table name (ป้องกัน SQL injection)
$tableMap = [
    'brands'           => 'master_brands',
    'grades'           => 'master_grades',
    'claim_categories' => 'master_claim_categories',
    'statuses'         => 'master_statuses',
];

if (!isset($tableMap[$type])) {
    echo json_encode(['success' => false, 'message' => 'ประเภทข้อมูลไม่ถูกต้อง (brands, grades, claim_categories, statuses)'], JSON_UNESCAPED_UNICODE);
    exit;
}

$table = $tableMap[$type];

try {
    $pdo = getServiceCenterPDO();

    // ============================================================
    // LIST — ดึงรายการทั้งหมด
    // ============================================================
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT * FROM `$table` ORDER BY sort_order ASC, id ASC");
        $data = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // CREATE — เพิ่มรายการใหม่
    // ============================================================
    if ($action === 'create') {
        if ($type === 'brands') {
            $name = trim($_POST['brand_name'] ?? '');
            if (empty($name)) { echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อยี่ห้อ'], JSON_UNESCAPED_UNICODE); exit; }
            $sort = intval($_POST['sort_order'] ?? 0);
            $stmt = $pdo->prepare("INSERT INTO `$table` (brand_name, sort_order) VALUES (?, ?)");
            $stmt->execute([$name, $sort]);
        }
        elseif ($type === 'grades') {
            $code = trim($_POST['grade_code'] ?? '');
            $name = trim($_POST['grade_name'] ?? '');
            if (empty($code) || empty($name)) { echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสเกรดและชื่อเกรด'], JSON_UNESCAPED_UNICODE); exit; }
            $sort = intval($_POST['sort_order'] ?? 0);
            $stmt = $pdo->prepare("INSERT INTO `$table` (grade_code, grade_name, sort_order) VALUES (?, ?, ?)");
            $stmt->execute([$code, $name, $sort]);
        }
        elseif ($type === 'claim_categories') {
            $name = trim($_POST['category_name'] ?? '');
            if (empty($name)) { echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อประเภทเคลม'], JSON_UNESCAPED_UNICODE); exit; }
            $sort = intval($_POST['sort_order'] ?? 0);
            $stmt = $pdo->prepare("INSERT INTO `$table` (category_name, sort_order) VALUES (?, ?)");
            $stmt->execute([$name, $sort]);
        }
        elseif ($type === 'statuses') {
            $code = trim($_POST['status_code'] ?? '');
            $name = trim($_POST['status_name'] ?? '');
            $badge = trim($_POST['badge_class'] ?? 'status-pending');
            if (empty($code) || empty($name)) { echo json_encode(['success' => false, 'message' => 'กรุณากรอกรหัสสถานะและชื่อสถานะ'], JSON_UNESCAPED_UNICODE); exit; }
            $sort = intval($_POST['sort_order'] ?? 0);
            $stmt = $pdo->prepare("INSERT INTO `$table` (status_code, status_name, badge_class, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->execute([$code, $name, $badge, $sort]);
        }

        echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลเรียบร้อยแล้ว', 'id' => $pdo->lastInsertId()], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // UPDATE — แก้ไขรายการ
    // ============================================================
    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ไม่พบ ID'], JSON_UNESCAPED_UNICODE); exit; }

        if ($type === 'brands') {
            $name = trim($_POST['brand_name'] ?? '');
            $sort = intval($_POST['sort_order'] ?? 0);
            $active = intval($_POST['is_active'] ?? 1);
            $stmt = $pdo->prepare("UPDATE `$table` SET brand_name=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $sort, $active, $id]);
        }
        elseif ($type === 'grades') {
            $code = trim($_POST['grade_code'] ?? '');
            $name = trim($_POST['grade_name'] ?? '');
            $sort = intval($_POST['sort_order'] ?? 0);
            $active = intval($_POST['is_active'] ?? 1);
            $stmt = $pdo->prepare("UPDATE `$table` SET grade_code=?, grade_name=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$code, $name, $sort, $active, $id]);
        }
        elseif ($type === 'claim_categories') {
            $name = trim($_POST['category_name'] ?? '');
            $sort = intval($_POST['sort_order'] ?? 0);
            $active = intval($_POST['is_active'] ?? 1);
            $stmt = $pdo->prepare("UPDATE `$table` SET category_name=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$name, $sort, $active, $id]);
        }
        elseif ($type === 'statuses') {
            $code = trim($_POST['status_code'] ?? '');
            $name = trim($_POST['status_name'] ?? '');
            $badge = trim($_POST['badge_class'] ?? 'status-pending');
            $sort = intval($_POST['sort_order'] ?? 0);
            $active = intval($_POST['is_active'] ?? 1);
            $stmt = $pdo->prepare("UPDATE `$table` SET status_code=?, status_name=?, badge_class=?, sort_order=?, is_active=? WHERE id=?");
            $stmt->execute([$code, $name, $badge, $sort, $active, $id]);
        }

        echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ============================================================
    // DELETE — ลบรายการ
    // ============================================================
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'ไม่พบ ID'], JSON_UNESCAPED_UNICODE); exit; }

        $stmt = $pdo->prepare("DELETE FROM `$table` WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'ไม่พบ action ที่ต้องการ'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
