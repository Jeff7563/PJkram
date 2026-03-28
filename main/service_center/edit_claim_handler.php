<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    
    if (!$id) {
        die('❌ ไม่พบ ID ที่ต้องการอัปเดต');
    }

    // 1. จัดการข้อมูลอะไหล่
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

    // 2. รับค่าทั้งหมดจากฟอร์ม
    $branch = $_POST['branch'] ?? '';
    $claimDate = !empty($_POST['claimDate']) ? date('Y-m-d', strtotime($_POST['claimDate'])) : null;
    $carType = $_POST['carType'] ?? '';
    $carBrand = $_POST['carBrand'] ?? '';
    $vin = $_POST['vin'] ?? '';
    $ownerName = $_POST['ownerName'] ?? '';
    $ownerPhone = $_POST['ownerPhone'] ?? '';
    $problemDesc = $_POST['problemDesc'] ?? '';
    $inspectMethod = $_POST['inspectMethod'] ?? '';
    $inspectCause = $_POST['inspectCause'] ?? '';
    $claimCategory = $_POST['claimCategory'] ?? '';
    
    $repairBranch = $_POST['repairBranch'] ?? 0;
    $sendHQ = $_POST['sendHQ'] ?? 0;
    
    $partsJson = json_encode($parts, JSON_UNESCAPED_UNICODE);
    $status = $_POST['status'] ?? 'Pending';
    
    // ** จุดที่เปลี่ยน: เปลี่ยนมารับค่า editor แทน recorder **
    $editor = $_POST['editor'] ?? '';
    $updated_at = date('Y-m-d H:i:s'); // เก็บเวลา ณ ตอนที่กดแก้ไข

    require_once __DIR__ . '/conn/db_connect.php';

    try {
        $pdo = getServiceCenterPDO();
        $table = getServiceCenterTable();

        $sql = "UPDATE `$table` SET 
                branch=?, claimDate=?, carType=?, carBrand=?, vin=?, 
                ownerName=?, ownerPhone=?, problemDesc=?, inspectMethod=?, inspectCause=?, 
                claimCategory=?, repairBranch=?, sendHQ=?, parts=?, status=?, 
                editor=?, updated_at=? 
                WHERE id=?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $branch, $claimDate, $carType, $carBrand, $vin, 
            $ownerName, $ownerPhone, $problemDesc, $inspectMethod, $inspectCause, 
            $claimCategory, $repairBranch, $sendHQ, $partsJson, $status,
            $editor, $updated_at,
            $id
        ]);
        
        echo '✅'; 

    } catch (Exception $e) {
        echo '❌ ' . $e->getMessage();
    }
    exit;
}
?>