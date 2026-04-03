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
        $claim_type = $_POST['claim_action'] ?? $old_data['claim_type'];

        // 2. จัดการรูปภาพ (สร้าง Folder อัตโนมัติเหมือน index_handler.php)
        $finalImages = $_POST['existing_images'] ?? [];

        // --- 1. ลบไฟล์ภาพออกจากเครื่องจริง (Disk Delete) ---
        $oldImagesRaw = !empty($old_data['claim_images']) ? $old_data['claim_images'] : '[]';
        $oldImagesArr = json_decode($oldImagesRaw, true);
        if (is_array($oldImagesArr)) {
            foreach ($oldImagesArr as $oldImgPath) {
                if (!in_array($oldImgPath, $finalImages)) {
                    $fullPath = __DIR__ . '/../' . $oldImgPath;
                    if (is_file($fullPath)) { @unlink($fullPath); }
                }
            }
        }

        // --- 2. จัดการรูปภาพใหม่ที่อัปโหลดเพิ่ม ---
        $claimDateForFolder = !empty($old_data['claim_date']) && $old_data['claim_date'] !== '0000-00-00' ? date('Y-m-d', strtotime($old_data['claim_date'])) : date('Y-m-d');
        $docIdFormat = "C" . str_pad($id, 3, '0', STR_PAD_LEFT);
        $uploadFolder = "{$claimDateForFolder}/{$docIdFormat}";
        $uploadDir = __DIR__ . '/../uploads/claims/' . $uploadFolder . '/';
        
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

        $vinInput = $_POST['vin'] ?? $old_data['vin'];
        $safeVin = preg_replace('/[^a-zA-Z0-9_-]/', '_', $vinInput);

        $fieldLabels = [
            'imgFullCar'  => 'ภาพรถทั้งคัน', 'imgSpot' => 'ภาพจุดปัญหา', 'imgPart' => 'ภาพชิ้นส่วน',
            'imgWarranty' => 'ภาพสมุดรับประกัน', 'imgOdometer' => 'ภาพเลขไมล์', 'imgEstimate' => 'ภาพใบประเมิน',
            'imgParts' => 'ภาพอะไหล่ที่เคลม', 'claim_images' => 'ภาพเพิ่มเติม'
        ];

        foreach ($_FILES as $key => $fileData) {
            $prefix = $fieldLabels[$key] ?? 'รูปภาพทั่วไป';
            if (is_array($fileData['name'])) {
                for ($i = 0; $i < count($fileData['name']); $i++) {
                    if ($fileData['error'][$i] === UPLOAD_ERR_OK && !empty($fileData['name'][$i])) {
                        $ext = pathinfo($fileData['name'][$i], PATHINFO_EXTENSION);
                        $newFileName = $prefix . '_' . $safeVin . '_' . ($i + 1) . '_' . rand(10,99) . '.' . $ext;
                        if (move_uploaded_file($fileData['tmp_name'][$i], $uploadDir . $newFileName)) {
                            $finalImages[] = 'uploads/claims/' . $uploadFolder . '/' . $newFileName; 
                        }
                    }
                }
            } else {
                if ($fileData['error'] === UPLOAD_ERR_OK && !empty($fileData['name'])) {
                    $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $newFileName = $prefix . '_' . $safeVin . '_1_' . rand(10,99) . '.' . $ext;
                    if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $newFileName)) {
                        $finalImages[] = 'uploads/claims/' . $uploadFolder . '/' . $newFileName; 
                    }
                }
            }
        }
        $claim_images_json = json_encode($finalImages, JSON_UNESCAPED_UNICODE);

        // 3. เตรียมข้อมูลหลักที่จะเซฟ
        $branch = $_POST['branch'] ?? $old_data['branch'];
        $claim_date = !empty($_POST['claim_date']) ? $_POST['claim_date'] : $old_data['claim_date'];
        $claim_category = $_POST['claim_category'] ?? $old_data['claim_category'];
        $car_type = $_POST['car_type'] ?? $old_data['car_type'];
        $car_brand = $_POST['car_brand'] ?? $old_data['car_brand'];
        $used_grade = $_POST['used_grade'] ?? $old_data['used_grade'];
        
        $owner_name = $_POST['owner_name'] ?? $old_data['owner_name'];
        $owner_phone = $_POST['owner_phone'] ?? $old_data['owner_phone'];
        $owner_address = $_POST['owner_address'] ?? $old_data['owner_address'];
        
        $problem_desc = $_POST['problem_desc'] ?? $old_data['problem_desc'];
        $inspect_method = $_POST['inspect_method'] ?? $old_data['inspect_method'];
        $inspect_cause = $_POST['inspect_cause'] ?? $old_data['inspect_cause'];
        
        $status = $_POST['status'] ?? $old_data['status'];
        $editor = $_POST['editor'] ?? '';
        
        // แปลงวันที่จาก d/m/Y เป็น Y-m-d ก่อนบันทึก
        $raw_edit_date = $_POST['edit_date'] ?? '';
        if (strpos($raw_edit_date, '/') !== false) {
            list($d, $m, $y) = explode('/', $raw_edit_date);
            $edit_date = "$y-$m-$d " . date('H:i:s');
        } else {
            $edit_date = !empty($raw_edit_date) ? $raw_edit_date . ' ' . date('H:i:s') : date('Y-m-d H:i:s');
        }

        // 4. บันทึกข้อมูลหลักลงตาราง claims
        $sql = "UPDATE `$table` SET 
                branch=?, claim_type=?, claim_date=?, claim_category=?, car_type=?, car_brand=?, used_grade=?, vin=?, 
                owner_name=?, owner_phone=?, owner_address=?, problem_desc=?, inspect_method=?, inspect_cause=?, 
                status=?, editor_id=?, updated_at=?, claim_images=?
                WHERE id=?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $branch, $claim_type, $claim_date, $claim_category, $car_type, $car_brand, $used_grade, $vinInput,
            $owner_name, $owner_phone, $owner_address, $problem_desc, $inspect_method, $inspect_cause, 
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
            $parts_delivery = $_POST['parts_delivery'] ?? null;
            if ($parts_delivery === 'other') $parts_delivery = $_POST['parts_delivery_other_text'] ?? 'อื่นๆ';
            
            $jobNum = $_POST['job_number'] ?? null;
            $jobAmt = !empty($_POST['job_amount']) ? $_POST['job_amount'] : null;

            $check = $pdo->prepare("SELECT id FROM claim_repair_details WHERE claim_id = ?");
            $check->execute([$id]);
            if ($check->fetch()) {
                $sql_rep = "UPDATE claim_repair_details SET parts_delivery=?, job_number=?, job_amount=?, approver_id=?, approver_name=?, approver_signature=? WHERE claim_id=?";
                $pdo->prepare($sql_rep)->execute([$parts_delivery, $jobNum, $jobAmt, $_POST['repair_id'] ?? null, $_POST['repair_name'] ?? null, $_POST['repair_signature'] ?? null, $id]);
            } else {
                $sql_rep = "INSERT INTO claim_repair_details (claim_id, parts_delivery, job_number, job_amount, approver_id, approver_name, approver_signature) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql_rep)->execute([$id, $parts_delivery, $jobNum, $jobAmt, $_POST['repair_id'] ?? null, $_POST['repair_name'] ?? null, $_POST['repair_signature'] ?? null]);
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
                    (isset($_POST['old_down_balance']) && trim($_POST['old_down_balance']) !== '') ? trim($_POST['old_down_balance']) : null, 
                    (isset($_POST['new_down_balance']) && trim($_POST['new_down_balance']) !== '') ? trim($_POST['new_down_balance']) : null, 
                    (isset($_POST['replace_type']) && trim($_POST['replace_type']) !== '') ? trim($_POST['replace_type']) : null, 
                    (isset($_POST['replace_used_grade']) && trim($_POST['replace_used_grade']) !== '') ? trim($_POST['replace_used_grade']) : null, 
                    (isset($_POST['replace_brand']) && trim($_POST['replace_brand']) !== '') ? trim($_POST['replace_brand']) : null,
                    (isset($_POST['replace_model']) && trim($_POST['replace_model']) !== '') ? trim($_POST['replace_model']) : null,
                    (isset($_POST['replace_color']) && trim($_POST['replace_color']) !== '') ? trim($_POST['replace_color']) : null,
                    (isset($_POST['replace_vin']) && trim($_POST['replace_vin']) !== '') ? trim($_POST['replace_vin']) : null,
                    (isset($_POST['replace_receive_date']) && trim($_POST['replace_receive_date']) !== '') ? trim($_POST['replace_receive_date']) : null,
                    (isset($_POST['replace_reason']) && trim($_POST['replace_reason']) !== '') ? trim($_POST['replace_reason']) : null,
                    (isset($_POST['replace_id']) && trim($_POST['replace_id']) !== '') ? trim($_POST['replace_id']) : null, 
                    (isset($_POST['replace_name']) && trim($_POST['replace_name']) !== '') ? trim($_POST['replace_name']) : null, 
                    (isset($_POST['replace_signature']) && trim($_POST['replace_signature']) !== '') ? trim($_POST['replace_signature']) : null, 
                    (isset($_POST['replace_approve_date']) && trim($_POST['replace_approve_date']) !== '') ? trim($_POST['replace_approve_date']) : null, 
                    $id
                ]);
            } else {
                $sql_repl = "INSERT INTO claim_replacement_details (
                    claim_id, old_down_balance, new_down_balance, replace_type, replace_used_grade, replace_brand, 
                    replace_model, replace_color, replace_vin, replace_receive_date, replace_reason,
                    approver_id, approver_name, approver_signature, approve_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $pdo->prepare($sql_repl)->execute([
                    $id, 
                    (isset($_POST['old_down_balance']) && trim($_POST['old_down_balance']) !== '') ? trim($_POST['old_down_balance']) : null, 
                    (isset($_POST['new_down_balance']) && trim($_POST['new_down_balance']) !== '') ? trim($_POST['new_down_balance']) : null, 
                    (isset($_POST['replace_type']) && trim($_POST['replace_type']) !== '') ? trim($_POST['replace_type']) : null, 
                    (isset($_POST['replace_used_grade']) && trim($_POST['replace_used_grade']) !== '') ? trim($_POST['replace_used_grade']) : null, 
                    (isset($_POST['replace_brand']) && trim($_POST['replace_brand']) !== '') ? trim($_POST['replace_brand']) : null, 
                    (isset($_POST['replace_model']) && trim($_POST['replace_model']) !== '') ? trim($_POST['replace_model']) : null, 
                    (isset($_POST['replace_color']) && trim($_POST['replace_color']) !== '') ? trim($_POST['replace_color']) : null, 
                    (isset($_POST['replace_vin']) && trim($_POST['replace_vin']) !== '') ? trim($_POST['replace_vin']) : null, 
                    (isset($_POST['replace_receive_date']) && trim($_POST['replace_receive_date']) !== '') ? trim($_POST['replace_receive_date']) : null, 
                    (isset($_POST['replace_reason']) && trim($_POST['replace_reason']) !== '') ? trim($_POST['replace_reason']) : null,
                    (isset($_POST['replace_id']) && trim($_POST['replace_id']) !== '') ? trim($_POST['replace_id']) : null, 
                    (isset($_POST['replace_name']) && trim($_POST['replace_name']) !== '') ? trim($_POST['replace_name']) : null, 
                    (isset($_POST['replace_signature']) && trim($_POST['replace_signature']) !== '') ? trim($_POST['replace_signature']) : null, 
                    (isset($_POST['replace_approve_date']) && trim($_POST['replace_approve_date']) !== '') ? trim($_POST['replace_approve_date']) : null
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