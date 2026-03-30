<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    if (!$id) die('❌ ไม่พบ ID ที่ต้องการอัปเดต');

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

    // จัดการรูปภาพใหม่ที่ถูกอัปโหลดเพิ่มเข้ามา
    $finalImages = $_POST['existing_images'] ?? []; 
    $uploadDir = __DIR__ . '/../uploads/claims/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }

    $vin = $_POST['vin'] ?? 'UnknownVIN';
    $safeVin = preg_replace('/[^a-zA-Z0-9_-]/', '_', $vin);

    $fieldLabels = [
        'claim_images' => 'ภาพแก้ไขเพิ่มเติม'
    ];

    foreach ($_FILES as $key => $fileData) {
        $prefix = $fieldLabels[$key] ?? 'รูปภาพปัญหา';
        if (is_array($fileData['name'])) {
            for ($i = 0; $i < count($fileData['name']); $i++) {
                if ($fileData['error'][$i] === UPLOAD_ERR_OK && !empty($fileData['name'][$i])) {
                    $ext = pathinfo($fileData['name'][$i], PATHINFO_EXTENSION);
                    $newFileName = $prefix . '_' . $safeVin . '_' . date('His') . '_' . rand(1000,9999) . '.' . $ext;
                    if (move_uploaded_file($fileData['tmp_name'][$i], $uploadDir . $newFileName)) {
                        $finalImages[] = 'uploads/claims/' . $newFileName; 
                    }
                }
            }
        } else {
            if ($fileData['error'] === UPLOAD_ERR_OK && !empty($fileData['name'])) {
                $ext = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                $newFileName = $prefix . '_' . $safeVin . '_' . date('His') . '_' . rand(1000,9999) . '.' . $ext;
                if (move_uploaded_file($fileData['tmp_name'], $uploadDir . $newFileName)) {
                    $finalImages[] = 'uploads/claims/' . $newFileName; 
                }
            }
        }
    }
    $claim_images_json = json_encode($finalImages, JSON_UNESCAPED_UNICODE);

    $partsDelivery = $_POST['partsDelivery'] ?? ''; 
    if ($partsDelivery === 'other') {
        $partsDelivery = $_POST['partsDeliveryOtherText'] ?? 'อื่นๆ';
    }

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
    $otherAction = ($claimAction === 'replaceVehicle') ? 1 : 0;
    $otherActionText = ($claimAction === 'other') ? ($_POST['claimOtherText'] ?? '') : null;
    
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
    
    $status = $_POST['status'] ?? 'Pending';
    $editor = $_POST['editor'] ?? '';
    $updated_at = date('Y-m-d H:i:s'); 

    require_once __DIR__ . '/../shared/config/db_connect.php';

    try {
        $pdo = getServiceCenterPDO();
        $table = getServiceCenterTable();

        $sql = "UPDATE `$table` SET 
                branch=?, claimDate=?, carType=?, carBrand=?, usedGrade=?, vin=?, 
                ownerName=?, ownerAddress=?, ownerPhone=?, problemDesc=?, inspectMethod=?, inspectCause=?, 
                claimCategory=?, repairBranch=?, sendHQ=?, otherAction=?, otherActionText=?, parts=?, partsDelivery=?, status=?, 
                editor=?, updated_at=?, claim_images=?,
                old_down_balance=?, new_down_balance=?, replaceType=?, replaceUsedGrade=?, replace_brand=?, replace_model=?, replace_color=?, replace_vin=?, 
                replace_receive_date=?, replace_reason=?, replace_id=?, replace_name=?, replace_signature=?, replace_approve_date=?
                WHERE id=?";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $branch, $claimDate, $carType, $carBrand, $usedGrade, $vin,
            $ownerName, $ownerAddress, $ownerPhone, $problemDesc, $inspectMethod, $inspectCause, 
            $claimCategory, $repairBranch, $sendHQ, $otherAction, $otherActionText, $partsJson, $partsDelivery, $status,
            $editor, $updated_at, $claim_images_json,
            $old_down_balance, $new_down_balance, $replaceType, $replaceUsedGrade, $replace_brand, $replace_model, $replace_color, $replace_vin,
            $replace_receive_date, $replace_reason, $replace_id, $replace__name, $replace_signature, $replace_approve_date,
            $id
        ]);
        
        echo '✅'; 
    } catch (Exception $e) {
        echo '❌ ' . $e->getMessage();
    }
    exit;
}
?>