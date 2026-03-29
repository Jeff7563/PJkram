<?php
// เปิดโหมดโชว์ Error เพื่อป้องกันปัญหาหน้าขาว
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/conn/db_connect.php';

// เช็คว่ามีการส่งข้อมูล ID มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['export_ids'])) {
    die("❌ ไม่มีข้อมูลสำหรับส่งออก กรุณากลับไปเลือกรายการที่หน้าตรวจเช็ค");
}

$ids = explode(',', $_POST['export_ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    $pdo = getServiceCenterPDO();
    $table = getServiceCenterTable();

    // ดึงข้อมูลตาม ID ที่ส่งมา
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id IN ($placeholders) ORDER BY id DESC");
    $stmt->execute($ids);
    $claims = $stmt->fetchAll();

    // เคลียร์สิ่งตกค้าง (Buffer) ป้องกันไฟล์พัง
    if (ob_get_length()) ob_clean(); 
    
    // ตั้งค่า Header บังคับให้ดาวน์โหลดเป็นไฟล์ .csv
    $filename = "Export_Claims_" . date('Ymd_His') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    
    // ใส่ BOM (Byte Order Mark) เพื่อให้โปรแกรม Excel อ่านภาษาไทยได้เป๊ะ
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // ใส่แถวแรก (หัวตาราง Excel)
    fputcsv($output, [
        'ลำดับ', 
        'เลขที่เอกสาร', 
        'วันที่ส่งเคลม', 
        'สาขา', 
        'ชื่อลูกค้า',
        'เบอร์โทรศัพท์',
        'ประเภทรถ', 
        'ยี่ห้อรถ (เกรด)', 
        'หมายเลขตัวถัง', 
        'ปัญหาที่ลูกค้าแจ้ง',
        'ยอดรวมค่าอะไหล่ (บาท)',
        'สถานะการพิจารณา',
        'หมายเหตุ (ผู้ตรวจสอบ)',
        'ผู้ตรวจสอบ', 
        'วันที่ทำรายการล่าสุด'
    ]);

    $count = 1;
    foreach ($claims as $row) {
        // จัดรูปแบบวันที่
        $claimDateFormatted = $row['claimDate'] ? date('d/m/Y', strtotime($row['claimDate'])) : '-';
        $updatedAtFormatted = !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-';
        
        // จัดรูปแบบเลขเอกสาร
        $idPart = "C" . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
        $datePart = "000000";
        if (!empty($row['claimDate']) && $row['claimDate'] !== '0000-00-00') {
            $timestamp = strtotime($row['claimDate']);
            if ($timestamp !== false) {
                $datePart = date('dm', $timestamp) . substr((date('Y', $timestamp) + 543), -2);
            }
        }
        $docId = $idPart . "-" . $datePart;

        // จัดรูปแบบสถานะ
        $statusText = 'รอดำเนินการ';
        if ($row['status'] == 'Approved') $statusText = 'อนุมัติการเคลม';
        if ($row['status'] == 'Rejected') $statusText = 'ไม่อนุมัติการเคลม';

        // จัดรูปแบบประเภทและเกรดรถ
        $carType = $row['carType'] == 'new' ? 'รถใหม่' : ($row['carType'] == 'used' ? 'รถมือสอง' : $row['carType']);
        $brandText = $row['carBrand'] . (!empty($row['usedGrade']) ? " ({$row['usedGrade']})" : "");

        // คำนวณยอดรวมอะไหล่เฉพาะเคสนั้นๆ
        $partsArray = json_decode($row['parts'], true) ?: [];
        $sumMoney = 0;
        foreach($partsArray as $part) {
            $qty = floatval($part['qty'] ?? 0);
            $price = floatval($part['price'] ?? 0);
            $sumMoney += ($qty * $price);
        }

        // จัดการลบการขึ้นบรรทัดใหม่ในช่องปัญหา (ป้องกัน Excel แถวพัง)
        $cleanProblemDesc = str_replace(array("\r", "\n"), " ", $row['problemDesc']);
        $cleanRemarks = str_replace(array("\r", "\n"), " ", $row['verify_remarks'] ?? '');

        // วางข้อมูลลงไปทีละแถว
        fputcsv($output, [
            $count++,
            $docId,
            $claimDateFormatted,
            $row['branch'],
            $row['ownerName'],
            $row['ownerPhone'],
            $carType,
            $brandText,
            $row['vin'],
            $cleanProblemDesc,
            number_format($sumMoney, 2, '.', ''), // ใส่ยอดรวมอะไหล่
            $statusText,
            $cleanRemarks,
            $row['verifier'] ?? '-',
            $updatedAtFormatted
        ]);
    }
    
    // ปิดไฟล์
    fclose($output);
    exit;

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการส่งออก Excel: " . $e->getMessage());
}
?>