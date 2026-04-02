<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    
    if (!$id) {
        die('❌ ไม่พบ ID ที่ต้องการตรวจสอบ');
    }

    $status = $_POST['status'] ?? 'Pending';
    $verify_remarks = $_POST['verify_remarks'] ?? '';
    $verifier_name = $_POST['verifier'] ?? '';
    
    // แปลงวันที่ตรวจสอบ d/m/Y -> Y-m-d
    $verify_date_raw = $_POST['verify_date'] ?? '';
    $verify_date = null;
    if (!empty($verify_date_raw)) {
        $dateObj = DateTime::createFromFormat('d/m/Y', $verify_date_raw);
        if ($dateObj) {
            $verify_date = $dateObj->format('Y-m-d');
        }
    }
    
    $updated_at = date('Y-m-d H:i:s'); 

    require_once __DIR__ . '/../shared/config/db_connect.php';

    try {
        $pdo = getServiceCenterPDO();

        // ตรวจสอบและเพิ่มคอลัมน์ที่จำเป็นหากยังไม่มี
        try { $pdo->query("ALTER TABLE `claims` ADD COLUMN `verifier_name` VARCHAR(255) DEFAULT NULL;"); } catch(Exception $e){}
        try { $pdo->query("ALTER TABLE `claims` ADD COLUMN `verify_date` DATE DEFAULT NULL;"); } catch(Exception $e){}

        // อัปเดตข้อมูลการตรวจสอบหลัก
        $sql = "UPDATE `claims` SET 
                status=?, verify_remarks=?, verifier_name=?, verify_date=?, updated_at=? 
                WHERE id=?";
                 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $status, $verify_remarks, $verifier_name, $verify_date, $updated_at, $id
        ]);
        
        // อัปเดตข้อมูลการซ่อม (Job Number, Job Amount)
        $job_num = $_POST['job_number'] ?? null;
        $job_amt = $_POST['job_amount'] ?? null;
        if ($job_amt === '') $job_amt = null;

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