<?php
// เปิดโหมดโชว์ Error เพื่อป้องกันปัญหาหน้าขาว
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../shared/config/db_connect.php';

// เช็คว่ามีการส่งข้อมูล ID มาหรือไม่
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['export_ids'])) {
    die("❌ ไม่มีข้อมูลสำหรับส่งออก กรุณากลับไปเลือกรายการที่หน้าตรวจเช็ค");
}

$ids = explode(',', $_POST['export_ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    $pdo = getServiceCenterPDO();

    // 1. ดึงข้อมูลหลักและรายละเอียด (V3)
    $stmt = $pdo->prepare("
        SELECT c.*, 
               rd.job_number, rd.job_amount, rd.parts_delivery, rd.approver_name as repair_app_name,
               rp.replace_vin, rp.approver_name as rp_app_name
        FROM `claims` c
        LEFT JOIN claim_repair_details rd ON c.id = rd.claim_id
        LEFT JOIN claim_replacement_details rp ON c.id = rp.claim_id
        WHERE c.id IN ($placeholders) 
        ORDER BY c.id DESC
    ");
    $stmt->execute($ids);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. ดึงข้อมูลอะไหล่ทั้งหมดที่เกี่ยวข้อง (Batch Fetch)
    $stmtItems = $pdo->prepare("SELECT * FROM `claim_items` WHERE claim_id IN ($placeholders)");
    $stmtItems->execute($ids);
    $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // จัดกลุ่มอะไหล่ตาม claim_id เพื่อให้เรียกใช้งานง่าย
    $itemsByClaim = [];
    foreach ($allItems as $item) {
        $itemsByClaim[$item['claim_id']][] = $item;
    }

    // เคลียร์สิ่งตกค้าง (Buffer) ป้องกันไฟล์พัง
    if (ob_get_length()) ob_clean(); 
    
    // ตั้งค่า Header บังคับให้ดาวน์โหลดเป็นไฟล์ .csv
    $filename = "Export_Claims_V3_" . date('Ymd_His') . ".csv";
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
        'วันที่ขายรถ',
        'อายุการใช้งาน',
        'สาขา', 
        'ชื่อลูกค้า',
        'เบอร์โทรศัพท์',
        'ประเภทรถ', 
        'ยี่ห้อรถ (เกรด)', 
        'หมายเลขตัวถัง', 
        'เลขไมล์ (กม.)',
        'ปัญหาที่ลูกค้าแจ้ง',
        'เลขที่ Job',
        'ยอดเงิน Job',
        'ยอดรวมค่าอะไหล่ (บาท)',
        'รายการอะไหล่',
        'ผู้อนุมัติ',
        'สถานะการพิจารณา',
        'หมายเหตุ (ผู้ตรวจสอบ)',
        'ผู้ตรวจสอบ',
        'ลายเซ็นต์ตรวจสอบ',
        'วันที่ทำรายการล่าสุด'
    ]);

    $count = 1;
    foreach ($claims as $row) {
        $claim_id = $row['id'];
        $claimDateFormatted = !empty($row['claim_date']) ? date('d/m/Y', strtotime($row['claim_date'])) : '-';
        $saleDateFormatted = (!empty($row['sale_date']) && $row['sale_date'] !== '0000-00-00') ? date('d/m/Y', strtotime($row['sale_date'])) : '-';
        $updatedAtFormatted = !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-';
        
        // คำนวณอายุการใช้งานเมื่อมี sale_date
        $ageDisplay = '-';
        if (!empty($row['sale_date']) && $row['sale_date'] !== '0000-00-00') {
            $sale_date = new DateTime($row['sale_date']);
            $now = new DateTime();
            if ($now >= $sale_date) {
                $diff = $now->diff($sale_date);
                $ageDisplay = "{$diff->y} ปี {$diff->m} เดือน {$diff->d} วัน";
            } else {
                $ageDisplay = '(!)';
            }
        }

        // จัดรูปแบบเลขเอกสาร
        $idPart = "C" . str_pad($claim_id, 3, '0', STR_PAD_LEFT);
        $datePart = "000000";
        if (!empty($row['claim_date']) && $row['claim_date'] !== '0000-00-00') {
            $timestamp = strtotime($row['claim_date']);
            if ($timestamp !== false) {
                $datePart = date('dm', $timestamp) . substr((date('Y', $timestamp) + 543), -2);
            }
        }
        $docId = $idPart . "-" . $datePart;

        // จัดรูปแบบสถานะ (เพิ่ม 5 สถานะใหม่)
        $statusText = 'รอดำเนินการ';
        if ($row['status'] == 'Pending Fix') $statusText = 'รอแก้ไข';
        if ($row['status'] == 'Completed') $statusText = 'ดำเนินการเสร็จสิ้น';
        if ($row['status'] == 'Replaced') $statusText = 'เปลี่ยนคัน';
        if ($row['status'] == 'Approved Claim' || $row['status'] == 'Approved') $statusText = 'อนุมัติเคลม';
        if ($row['status'] == 'Approved Replacement') $statusText = 'อนุมัติเปลี่ยนคัน';
        if ($row['status'] == 'Rejected') $statusText = 'ปฏิเสธ';

        // รูปแบบประเภทรถ
        $carType = $row['car_type'] == 'new' ? 'รถใหม่' : ($row['car_type'] == 'used' ? 'รถมือสอง' : $row['car_type']);
        $brandText = $row['car_brand'] . (!empty($row['used_grade']) ? " ({$row['used_grade']})" : "");

        // ดึงรายการอะไหล่
        $parts = $itemsByClaim[$claim_id] ?? [];
        $sumMoney = 0;
        $partsDesc = [];
        foreach($parts as $part) {
            $qty = floatval($part['quantity'] ?? 0);
            $price = floatval($part['unit_price'] ?? 0);
            $sumMoney += ($qty * $price);
            $partsDesc[] = trim(($part['part_code'] ?? '-') . ':' . ($part['part_name'] ?? '-') . ' x' . $qty);
        }

        // ลบ \n ป้องกัน CSV พัง
        $cleanProblemDesc = str_replace(array("\r", "\n"), " ", $row['problem_desc'] ?? '');
        $cleanRemarks = str_replace(array("\r", "\n"), " ", $row['verify_remarks'] ?? '');
        
        // ผู้อนุมัติ ตามประเภท
        $approver_name = '-';
        if ($row['claim_type'] == 'ReplaceVehicle') {
             $approver_name = $row['rp_app_name'] ?? '-';
        } else {
             $approver_name = $row['repair_app_name'] ?? '-';
        }

        fputcsv($output, [
            $count++,
            $docId,
            $claimDateFormatted,
            $saleDateFormatted,
            $ageDisplay,
            $row['branch'],
            $row['owner_name'],
            $row['owner_phone'],
            $carType,
            $brandText,
            $row['vin'],
            $row['mileage'],
            $cleanProblemDesc,
            $row['job_number'] ?? '-',
            $row['job_amount'] ?? '-',
            number_format($sumMoney, 2, '.', ''),
            $partsDesc ? implode(' / ', $partsDesc) : '-',
            $approver_name,
            $statusText,
            $cleanRemarks,
            $row['verifier_name'] ?? $row['editor_id'] ?? '-',
            $row['verifier_signature'] ?? '-',
            $updatedAtFormatted
        ]);
    }
    
    fclose($output);
    exit;

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการส่งออก Excel: " . $e->getMessage());
}
?>