<?php
require_once __DIR__ . '/../shared/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    if (!$id) die('❌ ไม่พบ ID ที่ต้องการอัปเดต');

    try {
        $pdo = getServiceCenterPDO();
        $table = getServiceCenterTable();

        $pdo->beginTransaction();

        // 1. ดึงข้อมูลเดิมจาก Database มาก่อน (ป้องกันข้อมูลเก่าหาย)
        $stmt_old = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch(PDO::FETCH_ASSOC);

        if (!$old_data) die('❌ ไม่พบข้อมูลเดิมในระบบ');
        $claim_type = $_POST['claimAction'] ?? $old_data['claim_type'];

        // 2. จัดการรูปภาพ (สร้าง Folder อัตโนมัติเหมือน index_handler.php)
        $finalImages = $_POST['existing_images'] ?? [];
        
        // กำหนดโฟลเดอร์สำหรับเคสนี้ (YYYY-MM-DD/CXXX) โดยใช้วันที่ที่สร้างรายการครั้งแรก
        $claimDateForFolder = !empty($old_data['claim_date']) && $old_data['claim_date'] !== '0000-00-00' ? date('Y-m-d', strtotime($old_data['claim_date'])) : date('Y-m-d');
        $docIdFormat = "C" . str_pad($id, 3, '0', STR_PAD_LEFT);
        $uploadFolder = "{$claimDateForFolder}/{$docIdFormat}";
        $uploadDir = __DIR__ . '/../uploads/claims/' . $uploadFolder . '/';
        
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

        $vinInput = $_POST['vin'] ?? $old_data['vin'];
        $safeVin = preg_replace('/[^a-zA-Z0-9_-]/', '_', $vinInput);

        if (isset($_FILES['claim_images']) && !empty($_FILES['claim_images']['name'][0])) {
            $fileCount = count($_FILES['claim_images']['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['claim_images']['error'][$i] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['claim_images']['name'][$i], PATHINFO_EXTENSION);
                    $newFileName = 'ภาพเพิ่มเติม_' . $safeVin . '_' . date('His') . '_' . rand(100,999) . '.' . $ext;
                    if (move_uploaded_file($_FILES['claim_images']['tmp_name'][$i], $uploadDir . $newFileName)) {
                        $finalImages[] = 'uploads/claims/' . $uploadFolder . '/' . $newFileName; 
                    }
                }
            }
        }
        $claim_images_json = json_encode($finalImages, JSON_UNESCAPED_UNICODE);

        // 3. เตรียมข้อมูลหลักที่จะเซฟ
        $branch = $_POST['branch'] ?? $old_data['branch'];
        $claimDate = !empty($_POST['claimDate']) ? $_POST['claimDate'] : $old_data['claim_date'];
        $claimCategory = $_POST['claimCategory'] ?? $old_data['claim_category'];
        $carType = $_POST['carType'] ?? $old_data['car_type'];
        $carBrand = $_POST['carBrand'] ?? $old_data['car_brand'];
        $usedGrade = $_POST['usedGrade'] ?? $old_data['used_grade'];
        
        $ownerName = $_POST['ownerName'] ?? $old_data['owner_name'];
        $ownerPhone = $_POST['ownerPhone'] ?? $old_data['owner_phone'];
        
        $problemDesc = $_POST['problemDesc'] ?? $old_data['problem_desc'];
        $inspectMethod = $_POST['inspectMethod'] ?? $old_data['inspect_method'];
        $inspectCause = $_POST['inspectCause'] ?? $old_data['inspect_cause'];
        
        $status = $_POST['status'] ?? $old_data['status'];
        $editor = $_POST['editor'] ?? '';
        $edit_date = !empty($_POST['edit_date']) ? $_POST['edit_date'] . ' ' . date('H:i:s') : date('Y-m-d H:i:s');

        // 4. บันทึกข้อมูลหลักลงตาราง claims
        $sql = "UPDATE `$table` SET 
                branch=?, claim_type=?, claim_date=?, claim_category=?, car_type=?, car_brand=?, used_grade=?, vin=?, 
                owner_name=?, owner_phone=?, problem_desc=?, inspect_method=?, inspect_cause=?, 
                status=?, editor_id=?, updated_at=?, claim_images=?
                WHERE id=?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $branch, $claim_type, $claimDate, $claimCategory, $carType, $carBrand, $usedGrade, $vinInput,
            $ownerName, $ownerPhone, $problemDesc, $inspectMethod, $inspectCause, 
            $status, $editor, $edit_date, $claim_images_json, $id
        ]);

        // 5. บันทึกตาราง claim_items (ทับของเดิม)
        $pdo->prepare("DELETE FROM `claim_items` WHERE claim_id = ?")->execute([$id]);
        if (isset($_POST['parts_name']) && is_array($_POST['parts_name'])) {
            $sql_item = "INSERT INTO `claim_items` (claim_id, part_name, part_code, quantity, unit_price) VALUES (?, ?, ?, ?, ?)";
            $stmt_item = $pdo->prepare($sql_item);
            for ($i = 0; $i < count($_POST['parts_name']); $i++) {
                if (!empty($_POST['parts_name'][$i])) {
                    $stmt_item->execute([
                        $id,
                        $_POST['parts_name'][$i],
                        $_POST['parts_code'][$i] ?? '',
                        $_POST['parts_qty'][$i] ?? 1,
                        $_POST['parts_price'][$i] ?? 0
                    ]);
                }
            }
        }
        
        // 6. บันทึกตาราง Details หรือ Replacement
        if ($claim_type === 'RepairBranch' || $claim_type === 'SendHQ' || $claim_type === 'Other' || $claim_type === 'ReplaceVehicle') {
            $partsDelivery = $_POST['partsDelivery'] ?? null;
            if ($partsDelivery === 'other') $partsDelivery = $_POST['partsDeliveryOtherText'] ?? 'อื่นๆ';
            
            $check = $pdo->prepare("SELECT id FROM claim_repair_details WHERE claim_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                $sql_rep = "UPDATE claim_repair_details SET parts_delivery=?, approver_id=?, approver_name=?, approver_signature=? WHERE claim_id=?";
                $pdo->prepare($sql_rep)->execute([$partsDelivery, $_POST['repair_id'] ?? null, $_POST['repair_name'] ?? null, $_POST['repair_signature'] ?? null, $id]);
            } else {
                $sql_rep = "INSERT INTO claim_repair_details (claim_id, parts_delivery, approver_id, approver_name, approver_signature) VALUES (?, ?, ?, ?, ?)";
                $pdo->prepare($sql_rep)->execute([$id, $partsDelivery, $_POST['repair_id'] ?? null, $_POST['repair_name'] ?? null, $_POST['repair_signature'] ?? null]);
            }
        } 
        
        if ($claim_type === 'ReplaceVehicle') {
            $old_down = !empty($_POST['old_down_balance']) ? $_POST['old_down_balance'] : null;
            $new_down = !empty($_POST['new_down_balance']) ? $_POST['new_down_balance'] : null;
            
            $check = $pdo->prepare("SELECT id FROM claim_replacement_details WHERE claim_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                $sql_repl = "UPDATE claim_replacement_details SET 
                    old_down_balance=?, new_down_balance=?, replace_type=?, replace_used_grade=?, replace_brand=?, 
                    replace_model=?, replace_color=?, replace_vin=?, replace_receive_date=?, replace_reason=?,
                    approver_id=?, approver_name=?, approver_signature=?, approve_date=?
                    WHERE claim_id=?";
                $pdo->prepare($sql_repl)->execute([
                    $old_down, $new_down, $_POST['replaceType'] ?? null, $_POST['replaceUsedGrade'] ?? null, 
                    $_POST['replace_brand'] ?? null, $_POST['replace_model'] ?? null, $_POST['replace_color'] ?? null, 
                    $_POST['replace_vin'] ?? null, $_POST['replace_receive_date'] ?? null, $_POST['replace_reason'] ?? null,
                    $_POST['replace_id'] ?? null, $_POST['replace_name'] ?? null, $_POST['replace_signature'] ?? null, 
                    $_POST['replace_approve_date'] ?? null, $id
                ]);
            } else {
                $sql_repl = "INSERT INTO claim_replacement_details (
                    claim_id, old_down_balance, new_down_balance, replace_type, replace_used_grade, replace_brand, 
                    replace_model, replace_color, replace_vin, replace_receive_date, replace_reason,
                    approver_id, approver_name, approver_signature, approve_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql_repl)->execute([
                    $id, $old_down, $new_down, $_POST['replaceType'] ?? null, $_POST['replaceUsedGrade'] ?? null, 
                    $_POST['replace_brand'] ?? null, $_POST['replace_model'] ?? null, $_POST['replace_color'] ?? null, 
                    $_POST['replace_vin'] ?? null, $_POST['replace_receive_date'] ?? null, $_POST['replace_reason'] ?? null,
                    $_POST['replace_id'] ?? null, $_POST['replace_name'] ?? null, $_POST['replace_signature'] ?? null, 
                    $_POST['replace_approve_date'] ?? null
                ]);
            }
        }

        $pdo->commit();
        echo "✅ บันทึกข้อมูลเรียบร้อยแล้ว!";
              
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    exit;
}
?>