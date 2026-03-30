<?php
require_once __DIR__ . '/../shared/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // 2. จัดการอัปโหลดรูปภาพ และตั้งชื่อไฟล์แบบฉลาด (Smart Naming)
    $uploadDir = __DIR__ . '/../uploads/claims/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

    // ดึงเลขตัวถังมาทำเป็นชื่อไฟล์ (ตัดอักขระแปลกๆ ออกป้องกันไฟล์พัง)
    $vin = $_POST['vin'] ?? 'UnknownVIN';
    $safeVin = preg_replace('/[^a-zA-Z0-9_-]/', '_', $vin);

    // กำหนดชื่อหัวข้อ ตามชื่อของกล่องอัปโหลด
    $fieldLabels = [
        'imgFullCar'  => 'ภาพรถทั้งคัน',
        'imgSpot'     => 'ภาพจุดปัญหา',
        'imgPart'     => 'ภาพชิ้นส่วนที่เสียหาย',
        'imgWarranty' => 'ภาพสมุดรับประกัน',
        'imgOdometer' => 'ภาพเลขไมล์',
        'imgEstimate' => 'ภาพใบประเมินอะไหล่',
        'imgParts'    => 'ภาพอะไหล่ที่เคลม',
        'claim_images'=> 'ภาพเพิ่มเติม'
    ];

    $savedImages = [];
    foreach ($_FILES as $key => $fileData) {
        // หาชื่อหัวข้อภาษาไทย ถ้าไม่ตรงกับกล่องไหนเลยให้ใช้คำว่า "รูปภาพทั่วไป"
        $prefix = $fieldLabels[$key] ?? 'รูปภาพทั่วไป';

        if (is_array($fileData['name'])) {
            for ($i = 0; $i < count($fileData['name']); $i++) {
                if ($fileData['error'][$i] === UPLOAD_ERR_OK && !empty($fileData['name'][$i])) {
                    $ext = pathinfo($fileData['name'][$i], PATHINFO_EXTENSION);
                    // สร้างชื่อไฟล์ใหม่: ภาพรถทั้งคัน_VIN12345_1425_4821.jpg
                    $newFileName = $prefix . '_' . $safeVin . '_' . date('His') . '_' . rand(1000,9999) . '.' . $ext;
                    if (move_uploaded_file($fileData['tmp_name'][$i], $uploadDir . $newFileName)) {
                        $savedImages[] = 'uploads/claims/' . $newFileName; 
                    }
                }
            }
        } else {
            if ($fileData['error'] === UPLOAD_ERR_OK && !empty($fileData['name'])) {
                $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                // สร้างชื่อไฟล์ใหม่: ภาพรถทั้งคัน_VIN12345_1425_4821.jpg
                $newFileName = $prefix . '_' . $safeVin . '_' . date('His') . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $newFileName)) {
                    $savedImages[] = 'uploads/claims/' . $newFileName; 
                }
            }
        }
    }
    $claim_images_json = json_encode($savedImages, JSON_UNESCAPED_UNICODE);

    // 3. ประเภทการส่งอะไหล่
    $partsDelivery = $_POST['partsDelivery'] ?? '';
    if ($partsDelivery === 'other') {
        $partsDelivery = $_POST['partsDeliveryOtherText'] ?? 'อื่นๆ';
    }

    // 4. รับค่าฟอร์มหลัก
    $branch = $_POST['branch'] ?? '';
    $claimDate = !empty($_POST['claimDate']) ? date('Y-m-d', strtotime($_POST['claimDate'])) : null;
    $carType = $_POST['carType'] ?? '';
    $carBrand = $_POST['carBrand'] ?? '';
    $usedGrade = $_POST['usedGrade'] ?? '';
    $ownerName = $_POST['ownerName'] ?? '';
    $ownerAddress = $_POST['ownerAddress'] ?? ''; 
    $ownerPhone = $_POST['ownerPhone'] ?? '';
    $problemDesc = $_POST['problemDesc'] ?? '';
    $inspectMethod = $_POST['inspectMethod'] ?? '';
    $inspectCause = $_POST['inspectCause'] ?? '';
    $claimCategory = $_POST['claimCategory'] ?? '';
    
    $claimAction = $_POST['claimAction'] ?? '';
    $repairBranch = ($claimAction === 'repairBranch') ? 1 : 0;
    $sendHQ = ($claimAction === 'sendHQ') ? 1 : 0;
    $otherAction = ($claimAction === 'replaceVehicle' || $claimAction === 'other') ? 1 : 0;
    $otherActionText = ($claimAction === 'other') ? ($_POST['claimOtherText'] ?? '') : null;
    
    // 5. ฟอร์มเปลี่ยนคัน
    $old_down_balance = !empty($_POST['old_down_balance']) ? $_POST['old_down_balance'] : null;
    $new_down_balance = !empty($_POST['new_down_balance']) ? $_POST['new_down_balance'] : null;
    $replaceType = $_POST['replaceType'] ?? null;
    $replaceUsedGrade = $_POST['replaceUsedGrade'] ?? null;
    $replace_brand = $_POST['replace_brand'] ?? null;
    $replace_model = $_POST['replace_model'] ?? null;
    $replace_color = $_POST['replace_color'] ?? null;
    $replace_vin = $_POST['replace_vin'] ?? null;
    $replace_receive_date = !empty($_POST['replace_receive_date']) ? date('Y-m-d', strtotime($_POST['replace_receive_date'])) : null;
    $replace_reason = $_POST['replace_reason'] ?? null;
    $replace_emp_id = $_POST['replace_id'] ?? null;
    $replace_emp_name = $_POST['replace_name'] ?? null;
    $replace_signature = $_POST['replace_signature'] ?? null;
    $replace_approve_date = !empty($_POST['replace_approve_date']) ? date('Y-m-d', strtotime($_POST['replace_approve_date'])) : null;
    
    $partsJson = json_encode($parts, JSON_UNESCAPED_UNICODE);
    $recorder = $_POST['recorder'] ?? '';
    $filesJson = json_encode([], JSON_UNESCAPED_UNICODE);
    
    $status = 'Pending'; 
    $created_at = date('Y-m-d H:i:s');


    try {
        $pdo = getServiceCenterPDO();
        $table = getServiceCenterTable();

        $sql = "INSERT INTO `{$table}` (
            created_at, branch, claimDate, carType, carBrand, usedGrade, vin, 
            ownerName, ownerAddress, ownerPhone, problemDesc, inspectMethod, inspectCause, 
            claimCategory, repairBranch, sendHQ, otherAction, otherActionText, parts, partsDelivery, recorder, 
            files, claim_images, status,
            old_down_balance, new_down_balance, replaceType, replaceUsedGrade, replace_brand, replace_model, replace_color, replace_vin, 
            replace_receive_date, replace_reason, replace_id, replace_name, replace_signature, replace_approve_date
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $created_at, $branch, $claimDate, $carType, $carBrand, $usedGrade, $vin,
            $ownerName, $ownerAddress, $ownerPhone, $problemDesc, $inspectMethod, $inspectCause,
            $claimCategory, $repairBranch, $sendHQ, $otherAction, $otherActionText, $partsJson, $partsDelivery, $recorder,
            $filesJson, $claim_images_json, $status,
            $old_down_balance, $new_down_balance, $replaceType, $replaceUsedGrade, $replace_brand, $replace_model, $replace_color, $replace_vin,
            $replace_receive_date, $replace_reason, $replace_emp_id, $replace_emp_name, $replace_signature, $replace_approve_date
        ]);
        
        echo '<div style="color: #06b957; font-weight: bold; padding: 10px; border-radius: 8px; background: #e8f5e9;">✅ บันทึกข้อมูลการเคลมเรียบร้อยแล้ว!</div>';
        echo '<script>setTimeout(function(){ window.location.href = "' . BASE_URL_BACKEND . '/check.php"; }, 1500);</script>';
    } catch (Exception $e) {
        echo '<div style="color: #dc3545; font-weight: bold; padding: 10px; border-radius: 8px; background: #fdedea;">❌ เกิดข้อผิดพลาดในฐานข้อมูล: ' . $e->getMessage() . '</div>';
    }
    exit;
}
?>