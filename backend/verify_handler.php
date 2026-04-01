<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    
    if (!$id) {
        die('❌ ไม่พบ ID ที่ต้องการตรวจสอบ');
    }

    $status = $_POST['status'] ?? 'Pending';
    $verify_remarks = $_POST['verify_remarks'] ?? '';
    $verifier_id = $_POST['verifier_id'] ?? '';
    $verifier_name = $_POST['verifier_name'] ?? $_POST['verifier'] ?? '';
    $verifier_sig = $_POST['verifier_signature'] ?? '';
    
    // อัปเดตเวลาเพื่อให้รู้ว่ามีการอัปเดตสถานะล่าสุดเมื่อไหร่
    $updated_at = date('Y-m-d H:i:s'); 

    require_once __DIR__ . '/../shared/config/db_connect.php';

    try {
        $pdo = getServiceCenterPDO();

        try { $pdo->query("ALTER TABLE `claims` ADD COLUMN `verifier_name` VARCHAR(255) DEFAULT NULL;"); } catch(Exception $e){}
        try { $pdo->query("ALTER TABLE `claims` ADD COLUMN `verifier_signature` VARCHAR(255) DEFAULT NULL;"); } catch(Exception $e){}

        $sql = "UPDATE `claims` SET 
                status=?, verify_remarks=?, editor_id=?, verifier_name=?, verifier_signature=?, updated_at=? 
                WHERE id=?";
                 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $status, $verify_remarks, $verifier_name, $verifier_name, $verifier_sig, $updated_at,
            $id
        ]);
        
        // รับค่า Summary สำหรับ Admin (Job Number, Job Amount, และ Repair Approver)
        if (isset($_POST['job_number']) || isset($_POST['job_amount']) || isset($_POST['repair_name'])) {
            $job_num = $_POST['job_number'] ?? null;
            $job_amt = $_POST['job_amount'] ?? null;
            if ($job_amt === '') $job_amt = null;
            
            $sqlRd = "UPDATE claim_repair_details SET ";
            $paramsRd = [];
            if(isset($_POST['job_number']) || isset($_POST['job_amount'])) {
                $sqlRd .= "job_number = ?, job_amount = ?";
                $paramsRd[] = $job_num;
                $paramsRd[] = $job_amt;
            }
            if(isset($_POST['repair_name'])) {
                if(count($paramsRd) > 0) $sqlRd .= ", ";
                $sqlRd .= "approver_id = ?, approver_name = ?, approver_signature = ?";
                $paramsRd[] = $_POST['repair_id'] ?? null;
                $paramsRd[] = $_POST['repair_name'] ?? null;
                $paramsRd[] = $_POST['repair_signature'] ?? null;
            }
            $sqlRd .= " WHERE claim_id = ?";
            $paramsRd[] = $id;

            $stmtRd = $pdo->prepare($sqlRd);
            $stmtRd->execute($paramsRd);
        }
        
        echo '✅'; 

    } catch (Exception $e) {
        echo '❌ ' . $e->getMessage();
    }
    exit;
}
?>