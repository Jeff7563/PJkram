<?php
require_once __DIR__ . '/../shared/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getServiceCenterPDO();

    try {
        $pdo->beginTransaction();

        // 1. จัดการข้อมูลรูปภาพ (Smart Naming)
        $uploadDir = __DIR__ . '/../uploads/claims/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

        $vin = $_POST['vin'] ?? 'UnknownVIN';
        $safeVin = preg_replace('/[^a-zA-Z0-9_-]/', '_', $vin);

        $fieldLabels = [
            'imgFullCar'  => 'ภาพรถทั้งคัน',
            'imgSpot'     => 'ภาพจุดปัญหา',
            'imgPart'     => 'ภาพชิ้นส่วนที่เสียหาย',
            'imgWarranty' => 'ภาพสมุดรับประกัน',
            'imgOdometer' => 'ภาพเลขไมล์',
            'imgEstimate' => 'ภาพใบประเมินอะไหล่',
            'imgParts'    => 'ภาพอะไหล่ที่เคลม'
        ];

        $savedImages = [];
        foreach ($_FILES as $key => $fileData) {
            $prefix = $fieldLabels[$key] ?? 'รูปภาพทั่วไป';
            if (is_array($fileData['name'])) {
                for ($i = 0; $i < count($fileData['name']); $i++) {
                    if ($fileData['error'][$i] === UPLOAD_ERR_OK && !empty($fileData['name'][$i])) {
                        $ext = pathinfo($fileData['name'][$i], PATHINFO_EXTENSION);
                        $uniqueId = date('Ymd_His') . '_' . ($i + 1);
                        $newFileName = $prefix . '_' . $safeVin . '_' . $uniqueId . '.' . $ext;
                        if (move_uploaded_file($fileData['tmp_name'][$i], $uploadDir . $newFileName)) {
                            $savedImages[] = 'uploads/claims/' . $newFileName; 
                        }
                    }
                }
            } else {
                if ($fileData['error'] === UPLOAD_ERR_OK && !empty($fileData['name'])) {
                    $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $uniqueId = date('Ymd_His') . '_0';
                    $newFileName = $prefix . '_' . $safeVin . '_' . $uniqueId . '.' . $ext;
                    if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $newFileName)) {
                        $savedImages[] = 'uploads/claims/' . $newFileName; 
                    }
                }
            }
        }
        $claim_images_json = json_encode($savedImages, JSON_UNESCAPED_UNICODE);

        // 2. เตรียมข้อมูลหลัก (Claims Table)
        $claimAction = $_POST['claimAction'] ?? 'Other';
        $claimTypeMap = [
            'repairBranch'   => 'RepairBranch',
            'sendHQ'         => 'SendHQ',
            'replaceVehicle' => 'ReplaceVehicle',
            'other'          => 'Other'
        ];
        $claim_type = $claimTypeMap[$claimAction] ?? 'Other';

        $branch      = $_POST['branch'] ?? '';
        $claimDate   = !empty($_POST['claimDate']) ? $_POST['claimDate'] : date('Y-m-d');
        $saleDate    = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;
        $carType     = $_POST['carType'] ?? '';
        $carBrand    = $_POST['carBrand'] ?? '';
        $usedGrade   = $_POST['usedGrade'] ?? '';
        $ownerName   = $_POST['ownerName'] ?? '';
        $ownerPhone  = $_POST['ownerPhone'] ?? '';
        $mileage     = $_POST['mileage'] ?? '';
        $problemDesc = $_POST['problemDesc'] ?? '';
        $inspectMethod = $_POST['inspectMethod'] ?? '';
        $inspectCause = $_POST['inspectCause'] ?? '';
        $claimCategory = $_POST['claimCategory'] ?? '';
        $recorder    = $_POST['recorder'] ?? '';

        $sql_claim = "INSERT INTO `claims` (
            claim_type, claim_date, sale_date, vin, mileage, car_type, car_brand, used_grade, 
            owner_name, owner_phone, problem_desc, inspect_method, inspect_cause, 
            claim_category, branch, recorder_id, claim_images
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_claim = $pdo->prepare($sql_claim);
        $stmt_claim->execute([
            $claim_type, $claimDate, $saleDate, $vin, $mileage, $carType, $carBrand, $usedGrade,
            $ownerName, $ownerPhone, $problemDesc, $inspectMethod, $inspectCause,
            $claimCategory, $branch, $recorder, $claim_images_json
        ]);

        $claim_id = $pdo->lastInsertId();

        // 3. บันทึกรายการอะไหล่ (Claim Items Table)
        if (isset($_POST['parts_name']) && is_array($_POST['parts_name'])) {
            $sql_item = "INSERT INTO `claim_items` (claim_id, part_name, part_code, quantity, unit_price) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $pdo->prepare($sql_item);
            for ($i = 0; $i < count($_POST['parts_name']); $i++) {
                if (!empty($_POST['parts_name'][$i])) {
                    $stmt_item->execute([
                        $claim_id,
                        $_POST['parts_name'][$i],
                        $_POST['parts_code'][$i] ?? '',
                        $_POST['parts_qty'][$i] ?? 1,
                        $_POST['parts_price'][$i] ?? 0
                    ]);
                }
            }
        }

        // 4. บันทึกรายละเอียดเฉพาะประเภท
        if ($claim_type === 'RepairBranch' || $claim_type === 'SendHQ') {
            $partsDelivery = $_POST['partsDelivery'] ?? '';
            if ($partsDelivery === 'other') $partsDelivery = $_POST['partsDeliveryOtherText'] ?? 'อื่นๆ';

            $sql_repair = "INSERT INTO `claim_repair_details` (
                claim_id, parts_delivery, approver_id, approver_name, approver_signature
            ) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql_repair)->execute([
                $claim_id, $partsDelivery, 
                $_POST['approver_id'] ?? null,
                $_POST['approver_name'] ?? null,
                $_POST['approver_signature'] ?? null
            ]);
        } 
        else if ($claim_type === 'ReplaceVehicle') {
            $sql_replace = "INSERT INTO `claim_replacement_details` (
                claim_id, old_down_balance, new_down_balance, replace_vin, replace_brand, 
                replace_model, replace_color, replace_type, replace_receive_date, 
                replace_reason, approver_id, approver_name, approver_signature, approve_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $pdo->prepare($sql_replace)->execute([
                $claim_id,
                !empty($_POST['old_down_balance']) ? $_POST['old_down_balance'] : null,
                !empty($_POST['new_down_balance']) ? $_POST['new_down_balance'] : null,
                $_POST['replace_vin'] ?? null,
                $_POST['replace_brand'] ?? null,
                $_POST['replace_model'] ?? null,
                $_POST['replace_color'] ?? null,
                $_POST['replaceType'] ?? null,
                !empty($_POST['replace_receive_date']) ? $_POST['replace_receive_date'] : null,
                $_POST['replace_reason'] ?? null,
                $_POST['replace_id'] ?? null,
                $_POST['replace_name'] ?? null,
                $_POST['replace_signature'] ?? null,
                !empty($_POST['replace_approve_date']) ? $_POST['replace_approve_date'] : date('Y-m-d')
            ]);
        }

        $pdo->commit();
        echo '<div style="color: #06b957; font-weight: bold; padding: 10px; border-radius: 8px; background: #e8f5e9;">✅ บันทึกข้อมูลการเคลม (Normalized V3) เรียบร้อยแล้ว!</div>';
        echo '<script>setTimeout(function(){ window.location.href = "check.php"; }, 1500);</script>';

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo '<div style="color: #dc3545; font-weight: bold; padding: 10px; border-radius: 8px; background: #fdedea;">❌ เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
    }
    exit;
}
?>