<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    
    if (!$id) {
        die('❌ ไม่พบ ID ที่ต้องการตรวจสอบ');
    }

    $status = (isset($_POST['status']) && trim($_POST['status']) !== '') ? trim($_POST['status']) : 'Pending';
    $verify_remarks = (isset($_POST['verify_remarks']) && trim($_POST['verify_remarks']) !== '') ? trim($_POST['verify_remarks']) : null;
    
    // รับข้อมูลผู้อนุมัติจากฟอร์ม verify.php
    $verifier_emp_id = (isset($_POST['approver_emp_id']) && trim($_POST['approver_emp_id']) !== '') ? trim($_POST['approver_emp_id']) : null;
    $verifier_name = (isset($_POST['approver_name']) && trim($_POST['approver_name']) !== '') ? trim($_POST['approver_name']) : null;
    $verifier_signature = (isset($_POST['approver_signature']) && trim($_POST['approver_signature']) !== '') ? trim($_POST['approver_signature']) : null;
    
    // แปลงวันที่ตรวจสอบ d/m/Y -> Y-m-d
    $verify_date_raw = $_POST['verify_date'] ?? '';
    $verify_date = null;
    if (!empty($verify_date_raw)) {
        $dateObj = DateTime::createFromFormat('d/m/Y', $verify_date_raw);
        if ($dateObj) {
            $verify_date = $dateObj->format('Y-m-d');
        }
    }
    
    require_once __DIR__ . '/../shared/config/db_connect.php';
    require_once __DIR__ . '/auth.php';

    $updated_at = date('Y-m-d H:i:s'); 

    try {
        $pdo = getServiceCenterPDO();

        // อัปเดตข้อมูลการตรวจสอบหลัก (รวม verifier_emp_id ใหม่)
        $sql = "UPDATE `claims` SET 
                status=?, verify_remarks=?, verifier_emp_id=?, verifier_name=?, verifier_signature=?, verify_date=?, updated_at=? 
                WHERE id=?";
                 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $status, $verify_remarks, $verifier_emp_id, $verifier_name, $verifier_signature, $verify_date, $updated_at, $id
        ]);
        
        // อัปเดตข้อมูลการซ่อม (Job Number, Job Amount)
        $job_num = (isset($_POST['job_number']) && trim($_POST['job_number']) !== '') ? trim($_POST['job_number']) : null;
        $job_amt = (isset($_POST['job_amount']) && trim($_POST['job_amount']) !== '') ? trim($_POST['job_amount']) : null;

        // ตรวจสอบว่ามีข้อมูลใน claim_repair_details หรือยัง
        $checkRd = $pdo->prepare("SELECT COUNT(*) FROM claim_repair_details WHERE claim_id = ?");
        $checkRd->execute([$id]);
        if ($checkRd->fetchColumn() == 0) {
            $pdo->prepare("INSERT INTO claim_repair_details (claim_id, job_number, job_amount) VALUES (?, ?, ?)")
                ->execute([$id, $job_num, $job_amt]);
        } else {
            $pdo->prepare("UPDATE claim_repair_details SET job_number = ?, job_amount = ? WHERE claim_id = ?")
                ->execute([$job_num, $job_amt, $id]);
        }
        
        echo '✅'; 

    } catch (Exception $e) {
        echo '❌ ' . $e->getMessage();
    }
    exit;
}
?>