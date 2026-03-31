<?php
// เปิดโหมดโชว์ Error
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../shared/config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['export_ids'])) {
    die("<h2 style='color:red; text-align:center; padding: 50px;'>❌ ไม่มีข้อมูลสำหรับส่งออก กรุณาปิดหน้านี้แล้วลองใหม่</h2>");
}

$ids = explode(',', $_POST['export_ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));

try {
    $pdo = getServiceCenterPDO();
    
    // 1. ดึงข้อมูลหลักจากตาราง claims (V3)
    $stmt = $pdo->prepare("SELECT * FROM `claims` WHERE id IN ($placeholders) ORDER BY id DESC");
    $stmt->execute($ids);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. ดึงข้อมูลอะไหล่ลูกทั้งหมด (Batch Fetch)
    $stmtItems = $pdo->prepare("SELECT * FROM `claim_items` WHERE claim_id IN ($placeholders)");
    $stmtItems->execute($ids);
    $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    // จัดกลุ่มลำดับอะไหล่ตาม claim_id
    $itemsByClaim = [];
    foreach ($allItems as $item) {
        $itemsByClaim[$item['claim_id']][] = $item;
    }

} catch (Exception $e) {
    die("<h2 style='color:red; text-align:center;'>❌ เกิดข้อผิดพลาดในฐานข้อมูล: " . $e->getMessage() . "</h2>");
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>กำลังสร้างไฟล์ PDF...</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body { font-family: 'Sarabun', sans-serif; margin: 0; padding: 0; background: #f4f6f9; }
        * { box-sizing: border-box; }
        
        #loading-screen {
            position: fixed; top: 0; left: 0; width: 100%; height: 100vh;
            background: #ffffff; display: flex; flex-direction: column;
            align-items: center; justify-content: center; z-index: 9999;
        }
        .spinner {
            width: 50px; height: 50px; border: 5px solid #f3f3f3;
            border-top: 5px solid #e65100; border-radius: 50%;
            animation: spin 1s linear infinite; margin-bottom: 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        #pdf-wrapper { position: absolute; left: -9999px; top: 0; }
        #pdf-content { width: 210mm; background: #fff; color: #000; font-size: 15px; }
        .claim-page { padding: 15mm 20mm; }

        .header-title { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-title h2 { margin: 0; font-size: 22px; font-weight: bold; }
        .section-title { font-weight: bold; font-size: 16px; background-color: #f0f0f0; padding: 5px 10px; border-left: 4px solid #e65100; margin-top: 15px; margin-bottom: 10px; }
        .problem-box { border: 1px solid #000; padding: 10px; border-radius: 4px; min-height: 50px; margin-bottom: 10px; }
        table.parts-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        .parts-table th, .parts-table td { border: 1px solid #000; padding: 6px; text-align: left; }
        .parts-table th { background-color: #e6e6e6; text-align: center; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .html2pdf__page-break { display: block; page-break-after: always; page-break-before: always; }
        .parts-table { page-break-inside: auto; }
        .parts-table tr { page-break-inside: avoid; }
        .section-title, .problem-box { page-break-inside: avoid; }
    </style>
</head>
<body>
    
    <div id="loading-screen">
        <div class="spinner"></div>
        <h2 style="color: #333; margin-bottom: 5px;">⏳ กำลังประมวลผลไฟล์ PDF (V3 Normalized)...</h2>
        <p style="color: #666; font-size: 16px;">กรุณารอสักครู่ ระบบจะปิดหน้านี้อัตโนมัติเมื่อดาวน์โหลดเสร็จสิ้น</p>
    </div>

    <div id="pdf-wrapper">
        <div id="pdf-content">
            <?php $i = 0; $total = count($claims); foreach ($claims as $claim): $i++;
                $claim_id = $claim['id'];
                $idPart = "C" . str_pad($claim_id, 3, '0', STR_PAD_LEFT);
                $datePart = "000000";
                if (!empty($claim['claim_date']) && $claim['claim_date'] !== '0000-00-00') {
                    $timestamp = strtotime($claim['claim_date']);
                    if ($timestamp !== false) {
                        $datePart = date('dm', $timestamp) . substr((date('Y', $timestamp) + 543), -2);
                    }
                }
                $doc_id = $idPart . "-" . $datePart;
                $claimDateFormatted = !empty($claim['claim_date']) ? date('d/m/Y', strtotime($claim['claim_date'])) : '-';
                
                $carTypeDisplay = $claim['car_type'] === 'new' ? 'รถใหม่' : ($claim['car_type'] === 'used' ? 'รถมือสอง' : $claim['car_type']);
                $brandText = $claim['car_brand'] . (!empty($claim['used_grade']) ? " (เกรด: {$claim['used_grade']})" : "");
                
                $statusText = 'รอดำเนินการ';
                if ($claim['status'] == 'Approved') $statusText = 'อนุมัติการเคลม';
                if ($claim['status'] == 'Rejected') $statusText = 'ไม่นุมัติการเคลม';
            ?>
            <div class="claim-page">
                <div class="header-title">
                    <h2>ใบรายละเอียดการตรวจสอบและส่งเคลม (V3)</h2>
                    <div style="font-size: 16px; margin-top: 5px;">เลขที่อ้างอิง: <?= $doc_id ?></div>
                </div>

                <div class="section-title">1. ข้อมูลเอกสารและผู้ใช้งาน</div>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td style="width: 15%; font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">วันที่ส่งเคลม:</td>
                        <td style="width: 35%; padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= $claimDateFormatted ?></td>
                        <td style="width: 15%; font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">สาขา:</td>
                        <td style="width: 35%; padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= htmlspecialchars($claim['branch'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">ชื่อ-นามสกุล:</td>
                        <td style="padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= htmlspecialchars($claim['owner_name'] ?? '-') ?></td>
                        <td style="font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">เบอร์โทรศัพท์:</td>
                        <td style="padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= htmlspecialchars($claim['owner_phone'] ?? '-') ?></td>
                    </tr>
                </table>

                <div class="section-title">2. ข้อมูลรถจักรยานยนต์</div>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td style="width: 15%; font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">ประเภทรถ:</td>
                        <td style="width: 35%; padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= $carTypeDisplay ?></td>
                        <td style="width: 15%; font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">ยี่ห้อรถ/เกรด:</td>
                        <td style="width: 35%; padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= $brandText ?></td>
                    </tr>
                    <tr>
                        <td style="font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">หมายเลขตัวถัง:</td>
                        <td style="padding: 4px 0; border-bottom: 1px dotted #ccc;"><b><?= htmlspecialchars($claim['vin'] ?? '-') ?></b></td>
                        <td style="font-weight: bold; padding: 4px 0; border-bottom: 1px dotted #ccc;">เลขไมล์:</td>
                        <td style="padding: 4px 0; border-bottom: 1px dotted #ccc;"><?= htmlspecialchars($claim['mileage'] ?? '-') ?> กม.</td>
                    </tr>
                </table>

                <div class="section-title">3. รายละเอียดปัญหา</div>
                <div>
                    <div style="font-weight:bold; margin-bottom: 5px;">ปัญหาที่ลูกค้าแจ้ง:</div>
                    <div class="problem-box"><?= nl2br(htmlspecialchars($claim['problem_desc'] ?? '')) ?></div>
                </div>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                    <tr>
                        <td style="width: 50%; padding-right: 5px; vertical-align: top;">
                            <div style="font-weight:bold; margin-bottom: 5px;">วิธีการตรวจเช็ค:</div>
                            <div class="problem-box"><?= nl2br(htmlspecialchars($claim['inspect_method'] ?? '')) ?></div>
                        </td>
                        <td style="width: 50%; padding-left: 5px; vertical-align: top;">
                            <div style="font-weight:bold; margin-bottom: 5px;">สาเหตุของปัญหา:</div>
                            <div class="problem-box"><?= nl2br(htmlspecialchars($claim['inspect_cause'] ?? '')) ?></div>
                        </td>
                    </tr>
                </table>

                <div class="section-title">4. รายการอะไหล่ที่เคลม</div>
                <?php
                    $parts = $itemsByClaim[$claim_id] ?? [];
                    $sumQty = 0;
                    $sumMoney = 0;
                ?>
                <table class="parts-table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">รหัสสินค้า</th>
                            <th width="35%">ชื่อสินค้า</th>
                            <th width="15%">ราคา/หน่วย</th>
                            <th width="10%">จำนวน</th>
                            <th width="20%">เป็นเงินสุทธิ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($parts) > 0): ?>
                            <?php foreach ($parts as $idx => $part):
                                $qty = floatval($part['quantity'] ?? 0);
                                $price = floatval($part['unit_price'] ?? 0);
                                $total = $qty * $price;
                                $sumQty += $qty;
                                $sumMoney += $total;
                            ?>
                            <tr>
                                <td class="text-center"><?= $idx + 1 ?></td>
                                <td><?= htmlspecialchars($part['part_code'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($part['part_name'] ?? '-') ?></td>
                                <td class="text-right"><?= number_format($price, 2) ?></td>
                                <td class="text-center"><?= $qty ?></td>
                                <td class="text-right" style="font-weight: bold;"><?= number_format($total, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">ไม่มีรายการอะไหล่ในฐานข้อมูล</td></tr>
                        <?php endif; ?>

                        <tr>
                            <td colspan="4" class="text-right" style="font-weight: bold; padding-right: 15px;">รวมยอดอะไหล่สุทธิ</td>
                            <td class="text-center" style="font-weight: bold;"><?= $sumQty ?></td>
                            <td class="text-right" style="font-weight: bold; color: #e65100;"><?= number_format($sumMoney, 2) ?> บาท</td>
                        </tr>
                    </tbody>
                </table>

                <div class="section-title">5. สรุปผลการพิจารณาอนุมัติ</div>
                <div class="problem-box" style="background-color: #f9f9f9; padding: 15px;">
                    <div style="font-size: 16px; margin-bottom: 10px;">
                        <b>สถานะการพิจารณา:</b> 
                        <span style="font-size: 18px; font-weight: bold; color: <?= $claim['status'] == 'Approved' ? '#06b957' : ($claim['status'] == 'Rejected' ? '#dc3545' : '#f39c12') ?>;">
                            [ <?= $statusText ?> ]
                        </span>
                    </div>
                    <div style="margin-bottom: 10px;"><b>หมายเหตุผู้ตรวจสอบ:</b> <?= htmlspecialchars($claim['verify_remarks'] ?? '-') ?></div>
                    <div><b>ลงชื่อผู้ตรวจสอบ:</b> <?= htmlspecialchars($claim['verifier'] ?? '.......................................................') ?></div>
                    <div style="margin-top: 10px;"><b>วันที่ทำรายการล่าสุด:</b> <?= !empty($claim['updated_at']) ? date('d/m/Y H:i', strtotime($claim['updated_at'])) : '-' ?></div>
                </div>
            </div>
            
            <?php if ($i < $total): ?>
            <div class="html2pdf__page-break"></div>
            <?php endif; ?>

            <?php endforeach; ?>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                var element = document.getElementById('pdf-content');
                var opt = {
                    margin:       [10, 10, 10, 10],
                    filename:     'Export_Claims_V3_<?= date("Ymd_His") ?>.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, scrollY: 0 },
                    jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak:    { mode: ['css', 'legacy'] }
                };
                
                html2pdf().set(opt).from(element).save().then(function() {
                    window.close();
                });
            }, 1000);
        };
    </script>
</body>
</html>