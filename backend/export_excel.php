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

    // 1. ดึงข้อมูลหลักจากตาราง claims (V3)
    $stmt = $pdo->prepare("SELECT * FROM `claims` WHERE id IN ($placeholders) ORDER BY id DESC");
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
        'สาขา', 
        'ชื่อลูกค้า',
        'เบอร์โทรศัพท์',
        'ประเภทรถ', 
        'ยี่ห้อรถ (เกรด)', 
        'หมายเลขตัวถัง', 
        'ปัญหาที่ลูกค้าแจ้ง',
        'ยอดรวมค่าอะไหล่ (บาท)',
        'รายการอะไหล่',
        'สถานะการพิจารณา',
        'หมายเหตุ (ผู้ตรวจสอบ)',
        'ผู้ตรวจสอบ', 
        'วันที่ทำรายการล่าสุด'
    ]);

    $count = 1;
    foreach ($claims as $row) {
        // จัดรูปแบบวันที่
        $claim_id = $row['id'];
        $claimDateFormatted = !empty($row['claim_date']) ? date('d/m/Y', strtotime($row['claim_date'])) : '-';
        $updatedAtFormatted = !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-';
        
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

        // จัดรูปแบบสถานะ
        $statusText = 'รอดำเนินการ';
        if ($row['status'] == 'Approved') $statusText = 'อนุมัติการเคลม';
        if ($row['status'] == 'Rejected') $statusText = 'ไม่อนุมัติการเคลม';

        // จัดรูปแบบประเภทและเกรดรถ
        $carType = $row['car_type'] == 'new' ? 'รถใหม่' : ($row['car_type'] == 'used' ? 'รถมือสอง' : $row['car_type']);
        $brandText = $row['car_brand'] . (!empty($row['used_grade']) ? " ({$row['used_grade']})" : "");

        // ดึงรายการอะไหล่จากที่จัดกลุ่มไว้
        $parts = $itemsByClaim[$claim_id] ?? [];
        $sumMoney = 0;
        $partsDesc = [];
        
        foreach($parts as $part) {
            $qty = floatval($part['quantity'] ?? 0);
            $price = floatval($part['unit_price'] ?? 0);
            $total = $qty * $price;
            $sumMoney += $total;
            $partsDesc[] = trim(($part['part_code'] ?? '-') . ' : ' . ($part['part_name'] ?? '-') . ' x' . $qty);
        }

        // จัดการลบการขึ้นบรรทัดใหม่ในช่องปัญหา (ป้องกัน Excel แถวพัง)
        $cleanProblemDesc = str_replace(array("\r", "\n"), " ", $row['problem_desc'] ?? '');
        $cleanRemarks = str_replace(array("\r", "\n"), " ", $row['verify_remarks'] ?? '');

        // วางข้อมูลลงไปทีละแถว
        fputcsv($output, [
            $count++,
            $docId,
            $claimDateFormatted,
            $row['branch'],
            $row['owner_name'],
            $row['owner_phone'],
            $carType,
            $brandText,
            $row['vin'],
            $cleanProblemDesc,
            number_format($sumMoney, 2, '.', ''),
            $partsDesc ? implode(' / ', $partsDesc) : '-',
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
            } else {
                $mainSum += $total;
                $mainPartsDesc[] = trim(($part['code'] ?? '-') . ' : ' . ($part['name'] ?? '-') . ' x' . $qty);
            }
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
            number_format($mainSum, 2, '.', ''),
            number_format($assocSum, 2, '.', ''),
            number_format($sumMoney, 2, '.', ''), // ใส่ยอดรวมอะไหล่
            $mainPartsDesc ? implode(' | ', $mainPartsDesc) : '-',
            $assocPartsDesc ? implode(' | ', $assocPartsDesc) : '-',
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