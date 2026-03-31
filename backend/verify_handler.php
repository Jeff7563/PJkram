<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['claim_id'] ?? null;
    
    if (!$id) {
        die('❌ ไม่พบ ID ที่ต้องการตรวจสอบ');
    }

    $status = $_POST['status'] ?? 'Pending';
    $verify_remarks = $_POST['verify_remarks'] ?? '';
    $verifier = $_POST['verifier'] ?? '';
    
    // อัปเดตเวลาเพื่อให้รู้ว่ามีการอัปเดตสถานะล่าสุดเมื่อไหร่
    $updated_at = date('Y-m-d H:i:s'); 

    require_once __DIR__ . '/../shared/config/db_connect.php';

    try {
        $pdo = getServiceCenterPDO();

        $sql = "UPDATE `claims` SET 
                status=?, verify_remarks=?, verifier=?, updated_at=? 
                WHERE id=?";
                 
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $status, $verify_remarks, $verifier, $updated_at,
            $id
        ]);
        
        echo '✅'; 

    } catch (Exception $e) {
        echo '❌ ' . $e->getMessage();
    }
    exit;
}
?>