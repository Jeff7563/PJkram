<?php
require_once '../shared/config/db_connect.php';

// 1. รับ ID จาก URL (ที่ส่งมาจากปุ่มในจุดที่ 1)
$id = $_GET['id'] ?? ''; 

if (empty($id)) {
    die("Error: ไม่พบเลขที่เอกสารสำหรับการปริ้น");
}

// 2. ใช้ ID นี้ไปค้นหาในฐานข้อมูล (ต้องใช้ WHERE เพื่อระบุเคส)
$sql = "SELECT * FROM claims WHERE claim_id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute(['id' => $id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die("Error: ไม่พบข้อมูลของเอกสารเลขที่: " . htmlspecialchars($id));
}

// หลังจากนี้ค่อยเป็นส่วนของ HTML/CSS ที่ดึงค่าจาก $row มาโชว์
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์ใบแจ้งเคลม - <?php echo $row['claim_id']; ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap');
        
        body { font-family: 'Sarabun', sans-serif; font-size: 14px; margin: 0; padding: 0; background-color: #f0f0f0; }
        .a4-page { width: 210mm; min-height: 297mm; padding: 15mm; margin: 10mm auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); box-sizing: border-box; position: relative; }
        
        /* สไตล์ตารางและเส้น */
        .dotted { border-bottom: 1px dotted #000; display: inline-block; min-width: 100px; padding: 0 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 5px; text-align: center; }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; }
        .title-box { text-align: center; border: 1px solid #000; padding: 5px; margin: 10px 0; font-weight: bold; }
        
        /* ซ่อนปุ่มเมื่อสั่งพิมพ์ */
        @media print {
            .no-print { display: none; }
            body { background: none; }
            .a4-page { margin: 0; box-shadow: none; width: 100%; height: 100%; }
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; padding: 20px;">
    <button onclick="window.print()" style="padding: 10px 20px; background: #d9534f; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
        คันนี้ข้อมูลครบแล้ว กด "บันทึกเป็น PDF" ได้เลย
    </button>
</div>

<div class="a4-page">
    <div class="header">
        <div><strong style="font-size: 20px;">อึ้งกุ่ยเฮง</strong><br><small>UNGKUIHENG</small></div>
        <div style="text-align: right; font-size: 12px;"><strong>บริษัท อึ้งกุ่ยเฮงสกลนคร จำกัด</strong><br>1353/15-20 ถ.สุขเกษม ต.ธาตุเชิงชุม<br>อ.เมืองสกลนคร จ.สกลนคร 47000</div>
    </div>

    <div class="title-box">การแจ้งเคลมทางเทคนิค/ รถแลกเปลี่ยน</div>

    <div style="margin-top: 10px;">
        วันที่แจ้ง: <span class="dotted"><?php echo $row['date_created']; ?></span> 
        สาขา: <span class="dotted"><?php echo $row['branch'] ?? 'สำนักงานใหญ่'; ?></span> 
        ชื่อลูกค้า: <span class="dotted"><?php echo $row['customer_name']; ?></span>
    </div>

    <div style="margin-top: 10px;">
        เลขถัง: <span class="dotted"><?php echo $row['vin_number'] ?? '-'; ?></span>
        รุ่นรถ: <span class="dotted"><?php echo $row['car_model']; ?></span>
        สี: <span class="dotted"><?php echo $row['color'] ?? '-'; ?></span>
    </div>

    <p><strong>ส่วนที่ 1 รายละเอียดปัญหาที่แจ้ง:</strong></p>
    <div style="border: 1px solid #000; min-height: 100px; padding: 10px;">
        <?php echo nl2br($row['problem_description']); ?>
    </div>

    <p><strong>ส่วนที่ 3 รายการอะไหล่ที่เกี่ยวข้อง:</strong></p>
    <table>
        <tr>
            <th width="10%">ลำดับ</th>
            <th width="60%">ชื่ออะไหล่ / รายการเคลม</th>
            <th width="15%">จำนวน</th>
            <th width="15%">หมายเหตุ</th>
        </tr>
        <tr>
            <td>1</td>
            <td>ตรวจสอบและเคลมตามอาการที่แจ้ง</td>
            <td>1</td>
            <td>-</td>
        </tr>
        <tr>
            <td colspan="2"><strong>รวม</strong></td>
            <td>1</td>
            <td></td>
        </tr>
    </table>

    <div style="margin-top: 50px; display: flex; justify-content: space-between;">
        <div style="text-align: center;">
            ลงชื่อ................................................<br>(พนักงานผู้รับแจ้ง)<br>วันที่......./......./.......
        </div>
        <div style="text-align: center;">
            ลงชื่อ................................................<br>(ผู้อนุมัติการเคลม)<br>วันที่......./......./.......
        </div>
    </div>
</div>

</body>
</html>