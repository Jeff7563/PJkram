<?php
require_once __DIR__ . '/../vendor/autoload.php'; // โหลด Library mPDF
require_once __DIR__ . '/../shared/config/db_connect.php'; // เชื่อมต่อฐานข้อมูล

$claim_id = $_GET['id']; // รับ ID จากหน้า check.php

// 1. ดึงข้อมูลจากฐานข้อมูล (ตัวอย่าง query)
$sql = "SELECT * FROM claims WHERE claim_id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$claim_id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

// 2. สร้าง HTML เนื้อหา (ใช้ CSS Inline เพื่อความเป๊ะบน PDF)
$html = '
<style>
    body { font-family: "sarabun"; font-size: 14pt; }
    .table-parts { border-collapse: collapse; width: 100%; }
    .table-parts th, .table-parts td { border: 1px solid black; padding: 5px; text-align: center; }
    .dotted { border-bottom: 1px dotted black; }
    .header-table { width: 100%; border: none; }
    .title-box { border: 1px solid black; text-align: center; padding: 5px; font-weight: bold; }
</style>

<div style="width: 100%;">
    <table class="header-table">
        <tr>
            <td width="30%"><strong style="font-size: 18pt;">อึ้งกุ่ยเฮง</strong><br><small>UNGKUIHENG</small></td>
            <td width="70%" align="right" style="font-size: 10pt;">
                <strong>บริษัท อึ้งกุ่ยเฮงสกลนคร จำกัด</strong> 1353/15-20 ถ.สุขเกษม<br>
                ต.ธาตุเชิงชุม อ.เมืองสกลนคร จ.สกลนคร 47000
            </td>
        </tr>
    </table>

    <div class="title-box">การแจ้งเคลมทางเทคนิค/ รถแลกเปลี่ยน <span style="float:right; font-weight:normal; font-size: 10pt;">ประเภท [ ] รถใหม่ [ ] รถมือสอง</span></div>

    <table width="100%" style="margin-top: 10px;">
        <tr>
            <td width="33%">วันที่แจ้ง: <span class="dotted">'.($data['date_created'] ?? '................').'</span></td>
            <td width="33%">สาขา: <span class="dotted">'.($data['branch'] ?? '................').'</span></td>
            <td width="34%">ชื่อลูกค้า: <span class="dotted">'.($data['customer_name'] ?? '................').'</span></td>
        </tr>
    </table>
    
    <p><strong>ส่วนที่ 1 เฉพาะสาขา</strong> [ ] เคลมลูกค้า [ ] เคลมก่อนขาย [ ] เปลี่ยนคัน</p>
    <div style="border: 1px solid black; height: 80px; padding: 10px;">
        '.($data['problem_description'] ?? '').'
    </div>

    <p><strong>ส่วนที่ 3 รายการอะไหล่</strong></p>
    <table class="table-parts">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>รหัสอะไหล่</th>
                <th>ชื่ออะไหล่</th>
                <th>จำนวน</th>
                <th>ราคา</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>1</td><td></td><td></td><td></td><td></td></tr>
            <tr><td>2</td><td></td><td></td><td></td><td></td></tr>
            <tr><td colspan="2">รวม</td><td></td><td></td><td></td></tr>
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 10pt;">
        ***หมายเหตุ: 1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้ง...
    </div>
</div>
';

// 3. เริ่มการสร้าง PDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'default_font' => 'sarabun' // ต้องติดตั้งฟอนต์ TH Sarabun ใน mPDF ด้วย
]);

$mpdf->WriteHTML($html);
$mpdf->Output("Claim_Form_" . $claim_id . ".pdf", "I"); // "I" คือแสดงใน Browser, "D" คือดาวน์โหลด