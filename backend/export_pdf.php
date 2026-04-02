<?php
/**
 * export_pdf.php - High-fidelity "Print to PDF" V2
 * Matches PDF.jpg 100% using HTML/CSS layout.
 */
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Error: ไม่พบรหัสการเคลม");
}

try {
    $pdo = getServiceCenterPDO();
    
    // 1. ดึงข้อมูลหลักและรายละเอียด
    $stmt = $pdo->prepare("
        SELECT c.*, 
               rd.job_number, rd.job_amount, rd.parts_delivery, rd.approver_name as repair_app_name, rd.approver_signature as repair_app_sig,
               rp.old_down_balance, rp.new_down_balance, rp.replace_vin, rp.replace_brand as rp_brand, rp.replace_model as rp_model, 
               rp.replace_color as rp_color, rp.replace_type as rp_type, rp.replace_used_grade as rp_used_grade, 
               rp.replace_receive_date as rp_receive_date, rp.replace_reason as rp_reason, 
               rp.approver_name as rp_app_name, rp.approver_signature as rp_app_sig, rp.approve_date as rp_app_date
        FROM claims c
        LEFT JOIN claim_repair_details rd ON c.id = rd.claim_id
        LEFT JOIN claim_replacement_details rp ON c.id = rp.claim_id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $claim = $stmt->fetch();

    if (!$claim) {
        die("Error: ไม่พบข้อมูลการเคลมเลขที่ $id");
    }

    // 2. ดึงรายการอะไหล่
    $stmtItems = $pdo->prepare("SELECT * FROM claim_items WHERE claim_id = ?");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();

    // 3. จัดข้อมูลเบื้องต้น
    $idPart = "C" . str_pad($claim['id'], 3, '0', STR_PAD_LEFT);
    $claimDateRaw = $claim['claim_date'] ?? '';
    if ($claimDateRaw && $claimDateRaw !== '0000-00-00') {
        $ts = strtotime($claimDateRaw);
        $datePart = date('dm', $ts) . substr((date('Y', $ts) + 543), -2);
        $claimDateForm = date('d/m/Y', $ts);
    } else {
        $datePart = "000000";
        $claimDateForm = "";
    }
    $docId = $idPart . "-" . $datePart;

    $saleDateForm = (!empty($claim['sale_date']) && $claim['sale_date'] !== '0000-00-00') ? date('d/m/Y', strtotime($claim['sale_date'])) : '';
    $receiveDateForm = (!empty($claim['rp_receive_date']) && $claim['rp_receive_date'] !== '0000-00-00') ? date('d/m/Y', strtotime($claim['rp_receive_date'])) : '';
    $approveDateForm = (!empty($claim['rp_app_date']) && $claim['rp_app_date'] !== '0000-00-00') ? date('d/m/Y', strtotime($claim['rp_app_date'])) : '';

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Print Claim Form - <?= $docId ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-black: #000;
            --border-color: #333;
        }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
        body { 
            font-family: 'Sarabun', sans-serif; 
            font-size: 13px; 
            line-height: 1.4; 
            margin: 0; 
            padding: 0; 
            background: #f0f0f0; 
            color: #000;
        }

        .a4-page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm 15mm;
            margin: 10mm auto;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }

        @media print {
            body { background: #fff; }
            .a4-page { margin: 0; box-shadow: none; width: 100%; min-height: 100%; border: none; }
            .no-print { display: none; }
        }

        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 30px;
            background: #ff6f00;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(255,111,0,0.3);
            z-index: 9999;
            transition: 0.3s;
        }
        .btn-print:hover { transform: scale(1.05); background: #e65100; }

        /* Header Style */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2px; }
        .logo-wrap { display: flex; align-items: center; gap: 10px; }
        .logo-text h1 { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -1px; }
        .logo-text small { font-size: 10px; letter-spacing: 2px; font-weight: 600; display: block; margin-top: -5px; }

        .company-info { text-align: right; font-size: 11px; color: #333; line-height: 1.2; }
        .company-info strong { font-size: 13px; display: block; margin-bottom: 2px; }

        .doc-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
            position: relative;
        }
        .doc-title-box {
            border: 1.5px solid #000;
            padding: 4px 50px;
            display: inline-block;
        }
        .type-select { 
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px; 
            font-weight: normal; 
        }

        /* Content Rows */
        .dotted-line { border-bottom: 1px dotted #000; display: inline-block; padding: 0 5px; }
        .info-row { margin-bottom: 3px; display: flex; align-items: baseline; gap: 5px; }
        
        .section-title { font-weight: bold; text-decoration: underline; margin-top: 8px; margin-bottom: 4px; font-size: 14px; }
        
        /* Checkboxes */
        .checkbox-group { display: inline-flex; align-items: center; gap: 4px; margin-right: 12px; }
        .box { width: 13px; height: 13px; border: 1px solid #000; display: inline-block; position: relative; flex-shrink: 0; }
        .box.checked::after { content: '\2713'; position: absolute; top: -5px; left: 0; font-size: 13px; font-weight: bold; }

        /* Section Layouts */
        .box-container { border: 1px solid #000; padding: 8px; margin-bottom: 5px; min-height: 90px; position: relative; }
        .sig-container { 
            position: absolute; 
            bottom: 8px; 
            right: 8px; 
            border: 1px solid #000; 
            padding: 5px 15px; 
            width: 250px; 
            text-align: center;
            background: #fff;
        }
        .sig-label { font-size: 10px; position: absolute; top: -8px; left: 10px; background: #fff; padding: 0 5px; }

        .fine-print { font-size: 10px; color: #444; margin: 3px 0; font-style: italic; }

        /* Tables */
        .parts-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .parts-table th, .parts-table td { border: 1px solid #000; padding: 3px 5px; text-align: center; }
        .parts-table th { background: #f5f5f5; font-weight: bold; font-size: 12px; }

        .dual-sig-row { display: flex; justify-content: space-between; margin-top: 5px; }
        .sig-box-wide { border: 1.5px solid #000; padding: 10px 15px; width: 48%; min-height: 70px; }

        /* Replacement Section */
        .replace-box { border: 1.5px solid #000; padding: 8px; margin-top: 5px; }
        .replace-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }

        .footer { position: absolute; bottom: 10mm; left: 15mm; right: 15mm; font-size: 10px; border-top: 1px solid #eee; padding-top: 5px; }
        
        .empty-val { color: #ccc; }
        .filled-val { color: #000; font-weight: 500; border-bottom: 1px dotted #555; display: inline-block; min-width: 30px; padding: 0 4px; }
        
        /* Helpers */
        .under-dotted { border-bottom: 1px dotted #000; flex: 1; padding: 0 5px; min-height: 18px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">กดค้าง "Save as PDF"</button>

    <div class="a4-page">
        <!-- HEADER -->
        <div class="header">
            <div class="logo-wrap">
                <div class="logo-text">
                    <h1>อึ้งกุ่ยเฮง</h1>
                    <small>UNGKUIHENG</small>
                </div>
            </div>
            <div class="company-info">
                <strong>บริษัท อึ้งกุ่ยเฮงสกลนคร จำกัด</strong> 1353/15-20 ถ.สุขเกษม <br>
                ต.ธาตุเชิงชุม อ.เมืองสกลนคร จ.สกลนคร 47000 เบอร์โทรศัพท์ 042-711135
            </div>
        </div>

        <div class="doc-title">
            <div class="doc-title-box">การแจ้งเคลมทางเทคนิค/ รถแลกเปลี่ยน</div>
            <div class="type-select">
                ประเภท 
                <span class="checkbox-group"><span class="box <?= ($claim['car_type'] == 'new') ? 'checked' : '' ?>"></span> รถใหม่</span>
                <span class="checkbox-group" style="margin-left:5px;"><span class="box <?= ($claim['car_type'] == 'used') ? 'checked' : '' ?>"></span> รถมือสอง</span>
            </div>
        </div>

        <!-- TOP INFO SECTION -->
        <div class="info-row">
            <div style="flex: 1;">วันที่แจ้ง <span class="dotted-line" style="min-width: 150px;"><?= $claimDateForm ?></span></div>
            <div style="flex: 1;">สาขา <span class="dotted-line" style="min-width: 150px;"><?= htmlspecialchars($claim['branch'] ?? '') ?></span></div>
            <div style="flex: 1.5;">ชื่อ-สกุลลูกค้า <span class="dotted-line" style="min-width: 200px;"><?= htmlspecialchars($claim['owner_name'] ?? '') ?></span></div>
        </div>

        <div class="info-row">
            <div style="flex: 1;">วันที่ซื้อรถ <span class="dotted-line" style="min-width: 100px;"><?= $saleDateForm ?></span></div>
            <div style="flex: 1.5;">เลขถัง <span class="dotted-line" style="min-width: 180px;"><?= htmlspecialchars($claim['vin'] ?? '') ?></span></div>
            <div style="flex: 1;">รุ่นรถ <span class="dotted-line" style="min-width: 120px;"><?= htmlspecialchars($claim['car_brand'] ?? '') ?></span></div>
            <div style="flex: 0.8;">สี <span class="dotted-line" style="min-width: 80px;"></span></div>
        </div>

        <div class="info-row">
            <div style="flex: 1;">เลขเครื่อง <span class="dotted-line" style="min-width: 180px;"></span></div>
            <div style="flex: 1;">เลขไมล์ <span class="dotted-line" style="min-width: 120px;"><?= number_format(floatval($claim['mileage'] ?? 0)) ?> กม.</span></div>
            <div style="flex: 1;">ดาวน์ <span class="dotted-line" style="min-width: 100px;"></span></div>
        </div>

        <div class="info-row" style="margin-top: 2px;">
            <div style="margin-right: 5px;">รถเกรด</div>
            <div class="checkbox-group"><span class="box <?= ($claim['used_grade'] == 'A_premium') ? 'checked' : '' ?>"></span> A พรีเมี่ยม</div>
            <div class="checkbox-group"><span class="box <?= ($claim['used_grade'] == 'A_w6') ? 'checked' : '' ?>"></span> A รับประกันเครื่องยนต์ 6 เดือน</div>
            <div class="checkbox-group"><span class="box <?= ($claim['used_grade'] == 'C_w1') ? 'checked' : '' ?>"></span> C รับประกันเครื่องยนต์ 1 เดือน</div>
            <div class="checkbox-group"><span class="box <?= ($claim['used_grade'] == 'C_as_is') ? 'checked' : '' ?>"></span> C ตามสภาพไม่รับประกัน</div>
        </div>

        <!-- SECTION 1 -->
        <div class="section-title">ส่วนที่ 1 เฉพาะสาขา</div>
        <div class="info-row">
            <div style="margin-right: 15px;">ประเภท</div>
            <div class="checkbox-group"><span class="box <?= ($claim['claim_category'] == 'customer') ? 'checked' : '' ?>"></span> เคลมรถลูกค้า</div>
            <div class="checkbox-group"><span class="box <?= ($claim['claim_category'] == 'pre-sale') ? 'checked' : '' ?>"></span> เคลมรถก่อนขาย</div>
            <div class="checkbox-group"><span class="box <?= ($claim['claim_type'] == 'ReplaceVehicle') ? 'checked' : '' ?>"></span> ขอเปลี่ยนคัน</div>
            <div class="checkbox-group"><span class="box"></span> อื่นๆ..............................</div>
        </div>

        <div class="box-container">
            <div style="font-weight: 500; font-size:11px;">ปัญหาที่ลูกค้าแจ้งและศูนย์บริการตรวจเช็คอาการของรถลูกค้า</div>
            <div style="margin-top: 5px; white-space: pre-wrap; font-size: 13px; line-height: 1.5;"><?= htmlspecialchars($claim['problem_desc'] ?? '') ?></div>
            
            <div class="sig-container">
                <div class="sig-label">สาขา</div>
                <div style="margin-top: 5px; font-size: 12px;">ลงชื่อ..........................................................</div>
                <div style="font-size: 12px; margin-top: 4px;">( <?= htmlspecialchars($claim['recorder_id'] ?? '..................................') ?> )</div>
            </div>
        </div>

        <!-- SECTION 2 -->
        <div class="section-title">ส่วนที่ 2 เจ้าหน้าที่ <span style="font-weight: normal; margin-left:15px; font-size: 12px;">
            <div class="checkbox-group"><span class="box <?= ($claim['claim_type'] == 'SendHQ') ? 'checked' : '' ?>"></span> ส่งซ่อมสนญ.</div>
            <div class="checkbox-group"><span class="box <?= ($claim['claim_type'] == 'RepairBranch') ? 'checked' : '' ?>"></span> ซ่อมสาขา</div>
            <div class="checkbox-group"><span class="box"></span> อื่นๆ............................................................</div>
        </span></div>

        <div class="box-container">
            <div style="font-weight: 500; font-size:11px;">สาเหตุและแนวทางการแก้ไข</div>
            <div style="margin-top: 5px; font-size: 13px; line-height: 1.5;">
                <?= htmlspecialchars($claim['inspect_method'] ?? '') ?> <?= htmlspecialchars($claim['inspect_cause'] ?? '') ?>
            </div>

            <div class="sig-container">
                <div class="sig-label">ผู้อนุมัติ</div>
                <div style="margin-top: 5px; font-size: 12px;">ลงชื่อ..........................................................</div>
                <div style="font-size: 12px; margin-top: 4px;">( <?= htmlspecialchars($claim['repair_app_name'] ?? '..................................') ?> )</div>
            </div>
        </div>

        <div class="fine-print">
            ***หมายเหตุ 1. รถมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117 หรือ 042-711135 ต่อ 201 <br>
            2. รถใหม่มีปัญหาปรึกษาศูนย์บริการ honda 086-459-4656 yamaha 086-455-0614 vespa 099-128-5556
        </div>

        <!-- SECTION 3 -->
        <div class="section-title">ส่วนที่ 3 รายการอะไหล่ <span style="font-weight: normal; margin-left:15px; font-size: 11px;">
            <div class="checkbox-group"><span class="box <?= ($claim['parts_delivery'] == 'in_stock') ? 'checked' : '' ?>"></span> อะไหล่ที่สาขา</div>
            <div class="checkbox-group"><span class="box <?= ($claim['parts_delivery'] == 'buy_outside') ? 'checked' : '' ?>"></span> ซื้ออะไหล่ด้านนอก</div>
            <div class="checkbox-group"><span class="box <?= ($claim['parts_delivery'] == 'wait_hq') ? 'checked' : '' ?>"></span> ส่งอะไหล่ให้สาขา</div>
            <div class="checkbox-group"><span class="box"></span> อื่นๆ............................................................</div>
        </span></div>

        <table class="parts-table">
            <thead>
                <tr>
                    <th width="35">ลำดับ</th>
                    <th width="120">รหัสอะไหล่</th>
                    <th>ชื่ออะไหล่</th>
                    <th width="60">จำนวน</th>
                    <th width="90">ราคา</th>
                    <th width="120">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totalSum = 0;
                $displayItems = count($items);
                // PDF.jpg shows at least 3 rows
                for($i=0; $i < max(3, $displayItems); $i++):
                    $item = $items[$i] ?? null;
                    $rowTotal = ($item ? $item['quantity'] * $item['unit_price'] : 0);
                    $totalSum += $rowTotal;
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= htmlspecialchars($item['part_code'] ?? '') ?></td>
                    <td style="text-align: left;"><?= htmlspecialchars($item['part_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['quantity'] ?? '') ?></td>
                    <td><?= ($item ? number_format($item['unit_price'], 2) : '') ?></td>
                    <td><?= htmlspecialchars($item['note'] ?? '') ?></td>
                </tr>
                <?php endfor; ?>
                <tr>
                    <td colspan="4" class="text-center fw-bold">รวม</td>
                    <td class="fw-bold"><?= number_format($totalSum, 2) ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="dual-sig-row">
            <div class="sig-box-wide" style="position: relative;">
                <div style="text-align: center; font-weight: 500;">ห้องอะไหล่</div>
                <div style="margin-top: 15px; font-size: 11px;">ลงชื่อ....................................................................................</div>
                <div style="font-size: 11px; text-align: center; margin-top: 5px;">(........................../........................../..........................)</div>
            </div>
            <div class="sig-box-wide" style="position: relative;">
                <div style="text-align: center; font-weight: 500;">จัดส่ง</div>
                <div style="margin-top: 15px; font-size: 11px;">ลงชื่อ....................................................................................</div>
                <div style="font-size: 11px; text-align: center; margin-top: 5px;">(........................../........................../..........................)</div>
            </div>
        </div>

        <!-- SECTION 4 -->
        <div class="section-title">ส่วนที่ 4 รถแลกเปลี่ยน</div>
        <div class="replace-box">
            <div class="info-row">
                <div style="flex: 1.5;">รายละเอียดขอเปลี่ยนคันใหม่</div>
                <div style="flex: 1;">แจ้งเปลี่ยนคัน 
                    <span class="checkbox-group" style="margin-left: 10px;"><span class="box <?= (($claim['rp_type'] ?? '') == 'รถใหม่') ? 'checked' : '' ?>"></span> รถใหม่</span>
                    <span class="checkbox-group" style="margin-left: 10px;"><span class="box <?= (($claim['rp_type'] ?? '') == 'รถมือสอง') ? 'checked' : '' ?>"></span> รถมือสอง</span>
                </div>
            </div>
            <div class="info-row" style="margin-top: 3px;">
                <div style="flex: 1;">รถคันเก่า คงเหลือเงินดาวน์ <span class="dotted-line" style="min-width: 150px;"><?= ($claim['old_down_balance'] > 0) ? number_format($claim['old_down_balance'], 2) : '' ?></span></div>
                <div style="flex: 1;">รถคันใหม่ คงเหลือเงินดาวน์ <span class="dotted-line" style="min-width: 150px;"><?= ($claim['new_down_balance'] > 0) ? number_format($claim['new_down_balance'], 2) : '' ?></span></div>
            </div>
            <div class="info-row" style="margin-top: 3px;">
                <div style="flex: 1;">ยี่ห้อ <span class="dotted-line" style="min-width: 80px;"><?= htmlspecialchars($claim['rp_brand'] ?? '') ?></span></div>
                <div style="flex: 1;">รุ่น <span class="dotted-line" style="min-width: 80px;"><?= htmlspecialchars($claim['rp_model'] ?? '') ?></span></div>
                <div style="flex: 0.8;">สี <span class="dotted-line" style="min-width: 60px;"><?= htmlspecialchars($claim['rp_color'] ?? '') ?></span></div>
                <div style="flex: 1.5;">เลขถัง <span class="dotted-line" style="min-width: 150px;"><?= htmlspecialchars($claim['replace_vin'] ?? '') ?></span></div>
                <div style="flex: 1.2;">วันที่รับรถ <span class="dotted-line" style="min-width: 80px;"><?= $receiveDateForm ?></span></div>
            </div>
            <div class="info-row" style="margin-top: 3px;">
                <div style="flex: 1;">สาเหตุที่เปลี่ยน <span class="dotted-line" style="width: 100%;"><?= htmlspecialchars($claim['replace_reason'] ?? '') ?></span></div>
            </div>
            <div class="info-row" style="margin-top: 5px; justify-content: space-between;">
                <div style="flex: 1;">ผู้อนุมัติ <span class="dotted-line" style="min-width: 200px;"><?= htmlspecialchars($claim['rp_app_name'] ?? '') ?></span></div>
                <div style="flex: 1; text-align: right;">วันที่อนุมัติ <span class="dotted-line" style="min-width: 150px;"><?= $approveDateForm ?></span></div>
            </div>
        </div>

        <div class="fine-print" style="margin-top: 10px;">
            ***หมายเหตุ 1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้งที่มีการเปลี่ยน / ตัวจริงแนบมากับสัญญาจัดส่งให้ห้องบัญชี <br>
            2. สินเชื่อเช็คประกันรถหาย / ทะเบียนแก้ไข พรบ. + ทะเบียน / บริหารสต็อก ตัดแลกเปลี่ยน / ธุรการสินเชื่อ ตรวจสอบการเปิดขาย
        </div>
    </div>

</body>
</html>