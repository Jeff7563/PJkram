<?php
require_once __DIR__ . '/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    if (!$id) die('❌ ไม่พบ ID ที่ต้องการอัปเดต');

    try {
        $pdo = getServiceCenterPDO();
        $pdo->beginTransaction();

        // 1. จัดเตรียมข้อมูลพื้นฐาน
        $branch = $_POST['branch'] ?? '';
        $claim_date = !empty($_POST['claimDate']) ? $_POST['claimDate'] : date('Y-m-d');
        $sale_date = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;
        $car_type = $_POST['carType'] ?? '';
        $car_brand = $_POST['carBrand'] ?? '';
        $used_grade = $_POST['usedGrade'] ?? '';
        $vin = $_POST['vin'] ?? '';
        $mileage = !empty($_POST['mileage']) ? floatval($_POST['mileage']) : 0;
        $owner_name = $_POST['ownerName'] ?? '';
        $owner_address = $_POST['ownerAddress'] ?? '';
        $owner_phone = $_POST['ownerPhone'] ?? '';
        $problem_desc = $_POST['problemDesc'] ?? '';
        $inspect_method = $_POST['inspectMethod'] ?? '';
        $inspect_cause = $_POST['inspectCause'] ?? '';
        $claim_category = $_POST['claimCategory'] ?? '';
        $claim_type = $_POST['claimAction'] ?? 'Other'; // RepairBranch, SendHQ, ReplaceVehicle, Other

        $status = $_POST['status'] ?? 'Pending';
        $editor_id = $_POST['editor'] ?? 'Unknown';
        $updated_at = date('Y-m-d H:i:s');

        // จัดการรูปภาพ (คงเดิมตาม logic เดิมของ user แต่ปรับชื่อคอลัมน์)
        $finalImages = $_POST['existing_images'] ?? [];
        $uploadDir = __DIR__ . '/../uploads/claims/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $safeVin = preg_replace('/[^a-zA-Z0-9_-]/', '_', $vin);

        if (!empty($_FILES['claim_images']['name'][0])) {
            foreach ($_FILES['claim_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['claim_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = pathinfo($_FILES['claim_images']['name'][$key], PATHINFO_EXTENSION);
                    $newFileName = 'Edit_' . $safeVin . '_' . date('Ymd_His') . '_' . $key . '.' . $ext;
                    if (move_uploaded_file($tmp_name, $uploadDir . $newFileName)) {
                        $finalImages[] = 'uploads/claims/' . $newFileName;
                    }
                }
            }
        }
        $claim_images_json = json_encode($finalImages, JSON_UNESCAPED_UNICODE);

        // 2. อัปเดตตาราง claims
        $sqlClaim = "UPDATE claims SET 
                     branch = ?, claim_date = ?, sale_date = ?, car_type = ?, car_brand = ?, used_grade = ?, 
                     vin = ?, mileage = ?, owner_name = ?, owner_address = ?, owner_phone = ?, 
                     problem_desc = ?, inspect_method = ?, inspect_cause = ?, claim_category = ?, 
                     claim_type = ?, claim_images = ?, status = ?, editor_id = ?, updated_at = ?
                     WHERE id = ?";
        $stmtClaim = $pdo->prepare($sqlClaim);
        $stmtClaim->execute([
            $branch, $claim_date, $sale_date, $car_type, $car_brand, $used_grade,
            $vin, $mileage, $owner_name, $owner_address, $owner_phone,
            $problem_desc, $inspect_method, $inspect_cause, $claim_category,
            $claim_type, $claim_images_json, $status, $editor_id, $updated_at, $id
        ]);

        // 3. จัดการรายการอะไหล่ (ลบของเก่าทิ้งแล้วลงใหม่)
        $stmtDelItems = $pdo->prepare("DELETE FROM claim_items WHERE claim_id = ?");
        $stmtDelItems->execute([$id]);

        if (isset($_POST['parts_name']) && is_array($_POST['parts_name'])) {
            $sqlItem = "INSERT INTO claim_items (claim_id, part_code, part_name, quantity, unit_price, note) VALUES (?, ?, ?, ?, ?, ?)";
            $stmtItem = $pdo->prepare($sqlItem);
            for ($i = 0; $i < count($_POST['parts_name']); $i++) {
                if (!empty($_POST['parts_name'][$i])) {
                    $stmtItem->execute([
                        $id,
                        $_POST['parts_code'][$i] ?? '',
                        $_POST['parts_name'][$i],
                        $_POST['parts_qty'][$i] ?? 1,
                        $_POST['parts_price'][$i] ?? 0,
                        $_POST['parts_note'][$i] ?? ''
                    ]);
                }
            }
        }

        // 4. จัดการข้อมูลเฉพาะประเภท (Repair / Replacement)
        // ลบข้อมูลเดิมในตารางรายละเอียด (เพื่อป้องกันข้อมูลค้างกรณีเปลี่ยนประเภท)
        $pdo->prepare("DELETE FROM claim_repair_details WHERE claim_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM claim_replacement_details WHERE claim_id = ?")->execute([$id]);

        if ($claim_type === 'RepairBranch' || $claim_type === 'SendHQ') {
            $pd = $_POST['partsDelivery'] ?? '';
            if ($pd === 'other') $pd = $_POST['partsDeliveryOtherText'] ?? 'อื่นๆ';

            $sqlRepair = "INSERT INTO claim_repair_details (claim_id, parts_delivery, approver_id, approver_name, approver_signature) VALUES (?, ?, ?, ?, ?)";
            $stmtRepair = $pdo->prepare($sqlRepair);
            $stmtRepair->execute([
                $id, $pd,
                $_POST['approver_repair_id'] ?? null,
                $_POST['approver_repair_name'] ?? null,
                $_POST['approver_repair_signature'] ?? null
            ]);
        } 
        elseif ($claim_type === 'ReplaceVehicle') {
            $sqlReplace = "INSERT INTO claim_replacement_details (
                claim_id, old_down_balance, new_down_balance, replace_vin, replace_brand, replace_model, 
                replace_color, replace_type, replace_used_grade, replace_receive_date, replace_reason, 
                approver_id, approver_name, approver_signature, approve_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtReplace = $pdo->prepare($sqlReplace);
            $stmtReplace->execute([
                $id,
                !empty($_POST['old_down_balance']) ? $_POST['old_down_balance'] : 0,
                !empty($_POST['new_down_balance']) ? $_POST['new_down_balance'] : 0,
                $_POST['replace_vin'] ?? '',
                $_POST['replace_brand'] ?? '',
                $_POST['replace_model'] ?? '',
                $_POST['replace_color'] ?? '',
                $_POST['replaceType'] ?? '',
                $_POST['replaceUsedGrade'] ?? null,
                !empty($_POST['replace_receive_date']) ? $_POST['replace_receive_date'] : null,
                $_POST['replace_reason'] ?? '',
                $_POST['replace_id'] ?? null,
                $_POST['replace_name'] ?? null,
                $_POST['replace_signature'] ?? null,
                !empty($_POST['replace_approve_date']) ? $_POST['replace_approve_date'] : null
            ]);
        }

        $pdo->commit();
        echo '✅';
    } catch (Exception $e) {
        if (isset($pdo)) $pdo->rollBack();
        echo '❌ ' . $e->getMessage();
    }
    exit;
}
?>