<?php
require_once __DIR__ . '/../shared/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = getServiceCenterPDO();

    try {
        $pdo->beginTransaction();



        // 2. เตรียมข้อมูลหลัก (Claims Table)
        $claim_action = $_POST['claim_action'] ?? 'Other';
        $claimTypeMap = [
            'repairBranch'   => 'RepairBranch',
            'sendHQ'         => 'SendHQ',
            'replaceVehicle' => 'ReplaceVehicle',
            'other'          => 'Other'
        ];
        $claim_type = $claimTypeMap[$claim_action] ?? 'Other';

        $branch        = $_POST['branch'] ?? '';
        $claim_date    = !empty($_POST['claim_date']) ? $_POST['claim_date'] : date('Y-m-d');
        $sale_date     = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;
        $car_type      = $_POST['car_type'] ?? '';
        $car_brand     = $_POST['car_brand'] ?? '';
        $used_grade    = $_POST['used_grade'] ?? '';
        $owner_name    = $_POST['owner_name'] ?? '';
        $owner_phone   = $_POST['owner_phone'] ?? '';
        $mileage       = $_POST['mileage'] ?? '';
        $problem_desc  = $_POST['problem_desc'] ?? '';
        $inspect_method = $_POST['inspect_method'] ?? '';
        $inspect_cause = $_POST['inspect_cause'] ?? '';
        $claim_category = $_POST['claim_category'] ?? '';
        if ($claim_category === 'อื่นๆ') {
            $claim_category = $_POST['claim_other_text'] ?? 'อื่นๆ';
        }
        $vin         = $_POST['vin'] ?? '';
        $recorder    = $_POST['recorder'] ?? '';

        $sql_claim = "INSERT INTO `claims` (
            claim_type, claim_date, sale_date, vin, mileage, car_type, car_brand, used_grade, 
            owner_name, owner_phone, problem_desc, inspect_method, inspect_cause, 
            claim_category, branch, recorder_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_claim = $pdo->prepare($sql_claim);
        $stmt_claim->execute([
            $claim_type, $claim_date, $sale_date, $vin, $mileage, $car_type, $car_brand, $used_grade,
            $owner_name, $owner_phone, $problem_desc, $inspect_method, $inspect_cause,
            $claim_category, $branch, $recorder
        ]);

        $claim_id = $pdo->lastInsertId();

        // 1.5 จัดการข้อมูลรูปภาพ (สร้าง Folder ตามวันที่/รหัสเคส)
        $docIdFormat = "C" . str_pad($claim_id, 3, '0', STR_PAD_LEFT);
        $uploadBaseDate = date('Y-m-d');
        $uploadFolder = "{$uploadBaseDate}/{$docIdFormat}";
        $uploadDir = __DIR__ . '/../uploads/claims/' . $uploadFolder . '/';
        
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

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
                        $uniqueId = ($i + 1);
                        $newFileName = $prefix . '_' . $safeVin . '_' . $uniqueId . '.' . $ext;
                        if (move_uploaded_file($fileData['tmp_name'][$i], $uploadDir . $newFileName)) {
                            $savedImages[] = 'uploads/claims/' . $uploadFolder . '/' . $newFileName; 
                        }
                    }
                }
            } else {
                if ($fileData['error'] === UPLOAD_ERR_OK && !empty($fileData['name'])) {
                    $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $uniqueId = '1';
                    $newFileName = $prefix . '_' . $safeVin . '_' . $uniqueId . '.' . $ext;
                    if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $newFileName)) {
                        $savedImages[] = 'uploads/claims/' . $uploadFolder . '/' . $newFileName; 
                    }
                }
            }
        }
        $claim_images_json = json_encode($savedImages, JSON_UNESCAPED_UNICODE);
        
        // Update images back to the record
        $pdo->prepare("UPDATE `claims` SET claim_images = ? WHERE id = ?")->execute([$claim_images_json, $claim_id]);

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
            $parts_delivery = $_POST['parts_delivery'] ?? '';
            if ($parts_delivery === 'other') $parts_delivery = $_POST['parts_delivery_other_text'] ?? 'อื่นๆ';

            $sql_repair = "INSERT INTO `claim_repair_details` (
                claim_id, parts_delivery, job_number, job_amount, approver_id, approver_name, approver_signature
            ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $pdo->prepare($sql_repair)->execute([
                $claim_id, $parts_delivery, 
                null, null, // job_number, job_amount เริ่มต้นเป็น null
                $_POST['approver_id'] ?? null,
                $_POST['approver_name'] ?? null,
                $_POST['approver_signature'] ?? null
            ]);
        } 
        else if ($claim_type === 'ReplaceVehicle') {
            $sql_replace = "INSERT INTO `claim_replacement_details` (
                claim_id, old_down_balance, new_down_balance, replace_vin, replace_brand, 
                replace_model, replace_color, replace_type, replace_used_grade, replace_receive_date, 
                replace_reason, approver_id, approver_name, approver_signature, approve_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $pdo->prepare($sql_replace)->execute([
                $claim_id,
                (isset($_POST['old_down_balance']) && trim($_POST['old_down_balance']) !== '') ? trim($_POST['old_down_balance']) : null,
                (isset($_POST['new_down_balance']) && trim($_POST['new_down_balance']) !== '') ? trim($_POST['new_down_balance']) : null,
                (isset($_POST['replace_vin']) && trim($_POST['replace_vin']) !== '') ? trim($_POST['replace_vin']) : null,
                (isset($_POST['replace_brand']) && trim($_POST['replace_brand']) !== '') ? trim($_POST['replace_brand']) : null,
                (isset($_POST['replace_model']) && trim($_POST['replace_model']) !== '') ? trim($_POST['replace_model']) : null,
                (isset($_POST['replace_color']) && trim($_POST['replace_color']) !== '') ? trim($_POST['replace_color']) : null,
                (isset($_POST['replace_type']) && trim($_POST['replace_type']) !== '') ? trim($_POST['replace_type']) : null,
                (isset($_POST['replace_used_grade']) && trim($_POST['replace_used_grade']) !== '') ? trim($_POST['replace_used_grade']) : null,
                (isset($_POST['replace_receive_date']) && trim($_POST['replace_receive_date']) !== '') ? trim($_POST['replace_receive_date']) : null,
                (isset($_POST['replace_reason']) && trim($_POST['replace_reason']) !== '') ? trim($_POST['replace_reason']) : null,
                (isset($_POST['replace_id']) && trim($_POST['replace_id']) !== '') ? trim($_POST['replace_id']) : null,
                (isset($_POST['replace_name']) && trim($_POST['replace_name']) !== '') ? trim($_POST['replace_name']) : null,
                (isset($_POST['replace_signature']) && trim($_POST['replace_signature']) !== '') ? trim($_POST['replace_signature']) : null,
                (isset($_POST['replace_approve_date']) && trim($_POST['replace_approve_date']) !== '') ? trim($_POST['replace_approve_date']) : date('Y-m-d')
            ]);
        }

        $pdo->commit();
        $_SESSION['flash_success'] = "บันทึกข้อมูลเคลม " . $docIdFormat . " เรียบร้อยแล้ว!";
        echo '<div style="color: #06b957; font-weight: bold; padding: 10px; border-radius: 8px; background: #e8f5e9;">✅ บันทึกข้อมูลการเคลม เรียบร้อยแล้ว!</div>';
        echo '<script>setTimeout(function(){ window.location.href = "check.php"; }, 1500);</script>';

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $_SESSION['flash_error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        echo '<div style="color: #dc3545; font-weight: bold; padding: 10px; border-radius: 8px; background: #fdedea;">❌ เกิดข้อผิดพลาด: ' . $e->getMessage() . '</div>';
    }
    exit;
}
?>