<?php
/**
 * export_pdf.php - High-fidelity "Print to PDF" V2
 * Supports multiple IDs and Batch Printing
 */
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

// Support both GET (single) and POST (batch)
$ids = [];
if (isset($_GET['id'])) {
    $ids[] = (int)$_GET['id'];
} elseif (isset($_POST['export_ids'])) {
    $rawIds = explode(',', $_POST['export_ids']);
    foreach ($rawIds as $rid) {
        $cleanId = (int)trim($rid);
        if ($cleanId > 0) $ids[] = $cleanId;
    }
}

if (empty($ids)) {
    die("Error: ไม่พบรหัสการเคลมที่ต้องการพิมพ์");
}

try {
    $pdo = getServiceCenterPDO();
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    // 1. Fetch all claims in the requested batch
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
        WHERE c.id IN ($placeholders)
        ORDER BY FIELD(c.id, $placeholders)
    ");
    
    // Execute with IDs twice: once for IN and once for FIELD order
    $stmt->execute(array_merge($ids, $ids));
    $claims = $stmt->fetchAll();

    if (empty($claims)) {
        die("Error: ไม่พบข้อมูลสำหรับการพิมพ์");
    }

} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Print Claim Forms (<?= count($claims) ?> items)</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-black: #000;
            --border-color: #000;
        }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact; }
        body { 
            font-family: 'Sarabun', sans-serif; 
            font-size: 14px; 
            line-height: 1.3; 
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
            page-break-after: always;
        }
        .a4-page:last-child { page-break-after: auto; }

        @media print {
            body { background: #fff; }
            .a4-page { margin: 0; box-shadow: none; width: 100%; min-height: 100%; border: none; padding: 5mm 12mm; }
            .no-print { display: none !important; }
            @page { size: A4; margin: 0; }
        }

        .btn-print {
            position: fixed; top: 20px; right: 20px; padding: 12px 30px;
            background: #ff6f00; color: white; border: none; border-radius: 50px;
            cursor: pointer; font-size: 16px; font-weight: bold;
            box-shadow: 0 4px 15px rgba(255,111,0,0.3); z-index: 9999;
            transition: 0.3s;
        }
        .btn-print:hover { transform: scale(1.05); background: #e65100; }

        /* Header Style */
        .header-container { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2px; }
        .logo-box { width: 180px; position: relative; }
        .logo-img { width: 100%; height: auto; display: block; }
        .logo-text-fallback { font-size: 24px; font-weight: 800; color: #000; letter-spacing: -1px; line-height: 1; }
        
        .company-address { text-align: right; font-size: 11.5px; color: #000; line-height: 1.25; }
        .company-address strong { font-size: 14px; display: block; margin-bottom: 2px; }

        .doc-title-area {
            text-align: center;
            margin: 5px 0;
            position: relative;
            width: 100%;
        }
        .title-frame {
            padding: 8px 40px;
            display: inline-block;
            font-size: 18px;
            font-weight: bold;
            background: transparent;
        }
        .type-selector { 
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px; 
            font-weight: normal; 
        }

        /* Content Rows */
        .info-line { margin-bottom: 2px; display: flex; align-items: baseline; gap: 12px; width: 100%; }
        .info-item { display: flex; align-items: baseline; overflow: hidden; }
        .label-text { font-weight: normal; flex-shrink: 0; white-space: nowrap; margin-right: 5px; }
        .dotted-fill { 
            border-bottom: 1px dotted #000; 
            flex: 1; 
            padding: 0 2px; 
            min-height: 18px; 
            color: #333; 
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .section-tag { font-weight: bold; text-decoration: underline; margin-top: 8px; margin-bottom: 4px; font-size: 14.5px; }
        .chk-item { display: inline-flex; align-items: center; gap: 4px; margin-right: 12px; white-space: nowrap; }
        .chk-square { width: 14px; height: 14px; border: 1.2px solid #000; display: inline-block; position: relative; flex-shrink: 0; }
        .chk-square.is-checked::after { content: '\2713'; position: absolute; top: -6px; left: 0; font-size: 16px; font-weight: bold; }

        .data-box { border: 1.2px solid #000; padding: 8px; margin-bottom: 6px; min-height: 105px; position: relative; }
        .inner-sig { position: absolute; bottom: 8px; right: 8px; border: 1px solid #000; padding: 6px 15px; width: 260px; text-align: center; background: #fff; }
        .inner-sig-tag { font-size: 10px; font-weight: bold; position: absolute; top: -8px; left: 10px; background: #fff; padding: 0 5px; }
        .sig-image { height: 40px; max-width: 200px; object-fit: contain; margin-bottom: -15px; }
        .notice-text { font-size: 11px; color: #333; margin: 5px 0; font-style: italic; line-height: 1.3; }

        .items-grid { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 14px; }
        .items-grid th, .items-grid td { border: 1.2px solid #000; padding: 4px 8px; text-align: center; }
        .items-grid th { background: #f0f0f0; font-weight: bold; }

        .sig-row-bottom { display: flex; justify-content: space-between; margin-top: 8px; }
        .sig-frame-wide { border: 1.5px solid #000; padding: 12px; width: 48.5%; min-height: 90px; text-align: center; }
        .replacement-panel { border: 1.5px solid #000; padding: 10px; margin-top: 8px; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>

    <button class="no-print btn-print" onclick="window.print()">กดค้าง "Save as PDF"</button>

    <?php foreach ($claims as $claim): 
        $stmtItems = $pdo->prepare("SELECT * FROM claim_items WHERE claim_id = ?");
        $stmtItems->execute([$claim['id']]);
        $items = $stmtItems->fetchAll();

        // Prepare display data
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
    ?>
    <div class="a4-page">
        <!-- HEADER -->
        <div class="header-container">
            <div class="logo-box">
                <?php 
                $logoPath = "../shared/assets/images/logo_ungkuiheng.png";
                if (file_exists($logoPath)): ?>
                    <img src="<?= $logoPath ?>" class="logo-img" alt="Logo">
                <?php else: ?>
                    <div class="logo-text-fallback">อึ้งกุ่ยเฮง</div>
                    <div style="font-size:10px; letter-spacing:2px; font-weight:bold; margin-top:-5px;">UNGKUIHENG</div>
                <?php endif; ?>
            </div>
            <div class="company-address">
                <strong>บริษัท อึ้งกุ่ยเฮงสกลนคร จำกัด</strong>
                1353/15-20 ถ.สุขเกษม ต.ธาตุเชิงชุม <br>
                อ.เมืองสกลนคร จ.สกลนคร 47000 เบอร์โทรศัพท์ 042-711135
            </div>
        </div>

        <div class="doc-title-area">
            <div class="title-frame">การแจ้งเคลมทางเทคนิค/ รถแลกเปลี่ยน</div>
            <div class="type-selector">
                ประเภท 
                <span class="chk-item"><span class="chk-square <?= ($claim['car_type'] == 'new') ? 'checked is-checked' : '' ?>"></span> รถใหม่</span>
                <span class="chk-item"><span class="chk-square <?= ($claim['car_type'] == 'used') ? 'checked is-checked' : '' ?>"></span> รถมือสอง</span>
            </div>
        </div>

        <!-- TOP INFO SECTION -->
        <div class="info-line">
            <div class="info-item" style="width: 22%;"><span class="label-text">วันที่แจ้ง</span><span class="dotted-fill"><?= $claimDateForm ?></span></div>
            <div class="info-item" style="width: 30%;"><span class="label-text">สาขา</span><span class="dotted-fill"><?= htmlspecialchars($claim['branch'] ?? '') ?></span></div>
            <div class="info-item" style="width: 48%;"><span class="label-text">ชื่อ-สกุลลูกค้า</span><span class="dotted-fill"><?= htmlspecialchars($claim['owner_name'] ?? '') ?></span></div>
        </div>
        <div class="info-line">
            <div class="info-item" style="width: 22%;"><span class="label-text">วันที่ซื้อรถ</span><span class="dotted-fill"><?= $saleDateForm ?></span></div>
            <div class="info-item" style="width: 33%;"><span class="label-text">เลขถัง</span><span class="dotted-fill"><?= htmlspecialchars($claim['vin'] ?? '') ?></span></div>
            <div class="info-item" style="width: 25%;"><span class="label-text">รุ่นรถ</span><span class="dotted-fill"><?= htmlspecialchars($claim['car_brand'] ?? '') ?></span></div>
            <div class="info-item" style="width: 20%;"><span class="label-text">สี</span><span class="dotted-fill"></span></div>
        </div>
        <div class="info-line">
            <div class="info-item" style="width: 35%;"><span class="label-text">เลขเครื่อง</span><span class="dotted-fill"></span></div>
            <div class="info-item" style="width: 35%;"><span class="label-text">เลขไมล์</span><span class="dotted-fill"><?= number_format(floatval($claim['mileage'] ?? 0)) ?> กม.</span></div>
            <div class="info-item" style="width: 30%;"><span class="label-text">ดาวน์</span><span class="dotted-fill"></span></div>
        </div>
        <div class="info-line" style="margin-top: 3px; flex-wrap: wrap;">
            <span class="label-text" style="margin-right: 12px;">รถเกรด</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['used_grade'] == 'A_premium' || $claim['used_grade'] == 'A พรีเมี่ยม') ? 'checked is-checked' : '' ?>"></span> A พรีเมี่ยม</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['used_grade'] == 'A_w6' || $claim['used_grade'] == 'A รับประกันเครื่องยนต์ 6 เดือน') ? 'checked is-checked' : '' ?>"></span> A รับประกันเครื่องยนต์ 6 เดือน</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['used_grade'] == 'C_w1' || $claim['used_grade'] == 'C รับประกันเครื่องยนต์ 1 เดือน') ? 'checked is-checked' : '' ?>"></span> C รับประกันเครื่องยนต์ 1 เดือน</span>
            <div class="chk-item" style="white-space: normal;"><span class="chk-square <?= ($claim['used_grade'] == 'C_as_is' || $claim['used_grade'] == 'C ตามสภาพไม่รับประกัน') ? 'checked is-checked' : '' ?>"></span> C ตามสภาพไม่รับประกัน</div>
        </div>

        <div class="section-tag">ส่วนที่ 1 เฉพาะสาขา</div>
        <div class="info-line">
            <span class="label-text" style="margin-right: 15px;">ประเภท</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['claim_category'] == 'เคลมรถลูกค้า' || $claim['claim_category'] == 'customer' || $claim['claim_category'] == 'customer-sale') ? 'checked is-checked' : '' ?>"></span> เคลมรถลูกค้า</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['claim_category'] == 'เคลมรถก่อนขาย' || $claim['claim_category'] == 'pre-sale') ? 'checked is-checked' : '' ?>"></span> เคลมรถก่อนขาย</span>
            <span class="chk-item"><span class="chk-square <?= (stripos($claim['claim_type'], 'ReplaceVehicle') !== false) ? 'checked is-checked' : '' ?>"></span> ขอเปลี่ยนคัน</span>
            <span class="chk-item"><span class="chk-square"></span> อื่นๆ..............................</span>
        </div>
        <div class="data-box">
            <div style="font-weight: 500; font-size:12px; margin-bottom: 5px;">ปัญหาที่ลูกค้าแจ้งและศูนย์บริการตรวจเช็คอาการของรถลูกค้า</div>
            <div style="white-space: pre-wrap; line-height: 1.4;"><?= htmlspecialchars($claim['problem_desc'] ?? '') ?></div>
            <div class="inner-sig">
                <div class="inner-sig-tag">สาขา</div>
                <div style="font-size: 13px; margin-top: 10px;">ลงชื่อ..........................................................</div>
                <div style="font-size: 13px; margin-top: 4px;">( <?= htmlspecialchars($claim['recorder_id'] ?? '..................................') ?> )</div>
            </div>
        </div>

        <div class="section-tag">ส่วนที่ 2 เจ้าหน้าที่ <span style="font-weight: normal; margin-left:20px; font-size: 13px;">
            <span class="chk-item"><span class="chk-square <?= (stripos($claim['claim_type'], 'SendHQ') !== false) ? 'checked is-checked' : '' ?>"></span> ส่งซ่อมสนญ.</span>
            <span class="chk-item"><span class="chk-square <?= (stripos($claim['claim_type'], 'RepairBranch') !== false) ? 'checked is-checked' : '' ?>"></span> ซ่อมสาขา</span>
            <span class="chk-item"><span class="chk-square"></span> อื่นๆ............................................................</span>
        </span></div>
        <div class="data-box" style="min-height: 120px;">
            <div style="font-weight: 500; font-size:12px; margin-bottom: 5px;">สาเหตุและแนวทางการแก้ไข</div>
            <div style="line-height: 1.4; min-height: 40px;"><?= htmlspecialchars($claim['inspect_method'] ?? '') ?> <?= htmlspecialchars($claim['inspect_cause'] ?? '') ?></div>
            <div class="inner-sig">
                <div class="inner-sig-tag">ผู้อนุมัติ</div>
                <?php 
                $sigData = $claim['repair_app_sig'] ?? '';
                if (!empty($sigData) && (strpos($sigData, 'data:image') === 0 || filter_var($sigData, FILTER_VALIDATE_URL) || file_exists($sigData))): ?>
                    <img src="<?= $sigData ?>" class="sig-image" alt="Signature">
                <?php endif; ?>
                <div style="font-size: 13px; margin-top: 10px;">ลงชื่อ..........................................................</div>
                <div style="font-size: 13px; margin-top: 4px;">( <?= htmlspecialchars($claim['repair_app_name'] ?? '..................................') ?> )</div>
            </div>
        </div>
        <div class="notice-text">***หมายเหตุ 1. รถมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117 หรือ 042-711135 ต่อ 201 | 2. รถใหม่มีปัญหาปรึกษาศูนย์บริการ honda 086-459-4656 yamaha 086-455-0614 vespa 099-128-5556</div>

        <div class="section-tag">ส่วนที่ 3 รายการอะไหล่ <span style="font-weight: normal; margin-left:20px; font-size: 12px;">
            <span class="chk-item"><span class="chk-square <?= ($claim['parts_delivery'] == 'in_stock') ? 'checked is-checked' : '' ?>"></span> อะไหล่ที่สาขา</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['parts_delivery'] == 'buy_outside') ? 'checked is-checked' : '' ?>"></span> ซื้ออะไหล่ด้านนอก</span>
            <span class="chk-item"><span class="chk-square <?= ($claim['parts_delivery'] == 'wait_hq') ? 'checked is-checked' : '' ?>"></span> ส่งอะไหล่ให้สาขา</span>
            <span class="chk-item"><span class="chk-square"></span> อื่นๆ............................................................</span>
        </span></div>
        <table class="items-grid">
            <thead><tr><th width="45">ลำดับ</th><th width="140">รหัสอะไหล่</th><th>ชื่ออะไหล่</th><th width="70">จำนวน</th><th width="100">ราคา</th><th width="140">หมายเหตุ</th></tr></thead>
            <tbody>
                <?php $totalSum = 0; $rowCount = count($items); for($i=0; $i < max(3, $rowCount); $i++): $item = $items[$i] ?? null; if ($item) $totalSum += ($item['quantity'] * $item['unit_price']); ?>
                <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($item['part_code'] ?? '') ?></td><td style="text-align: left;"><?= htmlspecialchars($item['part_name'] ?? '') ?></td><td><?= htmlspecialchars($item['quantity'] ?? '') ?></td><td><?= ($item ? number_format($item['unit_price'], 2) : '') ?></td><td><?= htmlspecialchars($item['note'] ?? '') ?></td></tr>
                <?php endfor; ?>
                <tr><td colspan="4" style="text-align: center; font-weight: bold;">รวม</td><td style="font-weight: bold;"><?= number_format($totalSum, 2) ?></td><td></td></tr>
            </tbody>
        </table>
        <div class="sig-row-bottom">
            <div class="sig-frame-wide">
                <div style="font-weight: bold; margin-bottom: 35px;">ห้องอะไหล่</div>
                <div style="font-size: 12px;">ลงชื่อ....................................................................................</div>
                <div style="font-size: 12px; margin-top: 5px;">(........................../........................../..........................)</div>
            </div>
            <div class="sig-frame-wide">
                <div style="font-weight: bold; margin-bottom: 35px;">จัดส่ง</div>
                <div style="font-size: 12px;">ลงชื่อ....................................................................................</div>
                <div style="font-size: 12px; margin-top: 5px;">(........................../........................../..........................)</div>
            </div>
        </div>

        <div class="section-tag">ส่วนที่ 4 รถแลกเปลี่ยน</div>
        <div class="replacement-panel">
            <div class="info-line">
                <div style="width: 40%;">รายละเอียดขอเปลี่ยนคันใหม่</div>
                <div style="width: 60%;">แจ้งเปลี่ยนคัน 
                    <span class="chk-item" style="margin-left: 15px;"><span class="chk-square <?= (($claim['rp_type'] ?? '') == 'รถใหม่') ? 'checked is-checked' : '' ?>"></span> รถใหม่</span>
                    <span class="chk-item"><span class="chk-square <?= (($claim['rp_type'] ?? '') == 'รถมือสอง') ? 'checked is-checked' : '' ?>"></span> รถมือสอง</span>
                </div>
            </div>
            <div class="info-line" style="margin-top: 5px;">
                <div style="width: 50%; display: flex;"><span class="label-text">รถคันเก่า คงเหลือเงินดาวน์</span><span class="dotted-fill"><?= ($claim['old_down_balance'] > 0) ? number_format($claim['old_down_balance'], 2) : '' ?></span></div>
                <div style="width: 50%; display: flex;"><span class="label-text">รถคันใหม่ คงเหลือเงินดาวน์</span><span class="dotted-fill"><?= ($claim['new_down_balance'] > 0) ? number_format($claim['new_down_balance'], 2) : '' ?></span></div>
            </div>
            <div class="info-line" style="margin-top: 5px;">
                <div style="width: 25%; display: flex;"><span class="label-text">ยี่ห้อ</span><span class="dotted-fill"><?= htmlspecialchars($claim['rp_brand'] ?? '') ?></span></div>
                <div style="width: 25%; display: flex;"><span class="label-text">รุ่น</span><span class="dotted-fill"><?= htmlspecialchars($claim['rp_model'] ?? '') ?></span></div>
                <div style="width: 50%; display: flex;"><span class="label-text">เลขถัง</span><span class="dotted-fill"><?= htmlspecialchars($claim['replace_vin'] ?? '') ?></span></div>
            </div>
            <div class="info-line" style="margin-top: 5px;">
                <span class="label-text">สาเหตุที่เปลี่ยน</span><span class="dotted-fill"><?= htmlspecialchars($claim['replace_reason'] ?? '') ?></span>
            </div>
        </div>
        <div class="notice-text">***หมายเหตุ 1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้งที่มีการเปลี่ยน | 2. บริหารสต็อก ตัดแลกเปลี่ยน / ธุรการสินเชื่อ ตรวจสอบการเปิดขาย</div>
    </div>
    <?php endforeach; ?>

    <script>
        // Auto-print when page loads, then go back (optional)
        window.onload = function() {
            window.print();
            // ถ้าอยากให้โหลดเสร็จแล้วหน้าจอไม่ค้างอยู่ตรงนี้ 
            // สามารถใช้ history.back() หรือคำสั่งอื่นๆ เพิ่มเติมได้ครับ
        };
    </script>
</body>
</html>
