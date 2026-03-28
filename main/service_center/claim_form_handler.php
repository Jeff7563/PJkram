<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. จัดการข้อมูลอะไหล่ที่ส่งมาเป็น Array ให้กลายเป็น JSON
    $parts = [];
    if (isset($_POST['parts_name']) && is_array($_POST['parts_name'])) {
        for ($i = 0; $i < count($_POST['parts_name']); $i++) {
            if (!empty($_POST['parts_name'][$i])) {
                $parts[] = [
                    'code' => $_POST['parts_code'][$i] ?? '',
                    'name' => $_POST['parts_name'][$i],
                    'qty' => $_POST['parts_qty'][$i] ?? 1,
                    'price' => $_POST['parts_price'][$i] ?? '',
                    'note' => $_POST['parts_note'][$i] ?? ''
                ];
            }
        }
    }

    // 2. ดึงข้อมูลจากฟอร์มมาใส่ตัวแปร $entry
    $entry = [
        'branch' => $_POST['branch'] ?? '',
        'claimDate' => $_POST['claimDate'] ?? '',
        'carType' => $_POST['carType'] ?? '',
        'carBrand' => $_POST['carBrand'] ?? '',
        'vin' => $_POST['vin'] ?? '',
        'ownerName' => $_POST['ownerName'] ?? '',
        'ownerPhone' => $_POST['ownerPhone'] ?? '',
        'problemDesc' => $_POST['problemDesc'] ?? '',
        'inspectMethod' => $_POST['inspectMethod'] ?? '',
        'inspectCause' => $_POST['inspectCause'] ?? '',
        'claimCategory' => $_POST['claimCategory'] ?? '',
        'repairBranch' => (isset($_POST['claimAction']) && $_POST['claimAction'] === 'repairBranch') ? 1 : 0,
        'sendHQ' => (isset($_POST['claimAction']) && $_POST['claimAction'] === 'sendHQ') ? 1 : 0,
        'parts' => $parts,
        'partsDelivery' => $_POST['partsDelivery'] ?? '',
        'recorder' => $_POST['recorder'] ?? '',
        'files' => [] // ไว้ทำระบบอัปโหลดรูปภาพทีหลัง
    ];

    require_once __DIR__ . '/conn/db_connect.php';

    if (!empty($dbConfig['enabled'])) {
        try {
            $pdo = getServiceCenterPDO();
            $table = getServiceCenterTable();

            $stmt = $pdo->prepare("INSERT INTO `{$table}` (created_at, branch, claimDate, carType, carBrand, vin, ownerName, ownerPhone, problemDesc, inspectMethod, inspectCause, claimCategory, repairBranch, sendHQ, parts, partsDelivery, recorder, files) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            
            $stmt->execute([
                date('Y-m-d H:i:s'),
                $entry['branch'],
                $entry['claimDate'] ? date('Y-m-d', strtotime($entry['claimDate'])) : null,
                $entry['carType'],
                $entry['carBrand'],
                $entry['vin'],
                $entry['ownerName'],
                $entry['ownerPhone'],
                $entry['problemDesc'],
                $entry['inspectMethod'],
                $entry['inspectCause'],
                $entry['claimCategory'],
                $entry['repairBranch'],
                $entry['sendHQ'],
                json_encode($entry['parts'], JSON_UNESCAPED_UNICODE),
                $entry['partsDelivery'],
                $entry['recorder'],
                json_encode($entry['files'], JSON_UNESCAPED_UNICODE)
            ]);
            
            // ส่งข้อความความสำเร็จกลับไปยัง Javascript
            echo '<div style="color: #06b957; font-weight: bold;">✅ บันทึกข้อมูลการเคลมเรียบร้อยแล้ว!</div>';
        } catch (Exception $e) {
            echo '<div style="color: #dc3545; font-weight: bold;">❌ เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage() . '</div>';
        }
    }
    // ใช้ exit เพื่อไม่ให้โค้ดรันไปหน้าอื่น ระบบจะได้โชว์ข้อความเฉยๆ
    exit;
}
?>