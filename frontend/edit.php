<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("<div style='padding:20px; color:red; text-align:center;'>❌ ไม่พบรหัสการเคลม กรุณากลับไปเลือกจากหน้าประวัติ</div>");
}

try {
    $pdo = getServiceCenterPDO();
    
    // ดึงข้อมูลหลักและข้อมูลรายละเอียดการซ่อม/เปลี่ยนคัน
    $stmt = $pdo->prepare("
        SELECT c.*, 
               rd.job_number, rd.job_amount, rd.parts_delivery, rd.approver_id as repair_app_id, rd.approver_name as repair_app_name, rd.approver_signature as repair_app_sig,
               rp.old_down_balance, rp.new_down_balance, rp.replace_vin, rp.replace_brand as rp_brand, rp.replace_model as rp_model, rp.replace_color as rp_color, rp.replace_type as rp_type, rp.replace_used_grade as rp_used_grade, rp.replace_receive_date as rp_receive_date, rp.replace_reason as rp_reason, rp.approver_id as rp_app_id, rp.approver_name as rp_app_name, rp.approver_signature as rp_app_sig, rp.approve_date as rp_app_date
        FROM claims c
        LEFT JOIN claim_repair_details rd ON c.id = rd.claim_id
        LEFT JOIN claim_replacement_details rp ON c.id = rp.claim_id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $claim = $stmt->fetch();

    if (!$claim) {
        die("<div style='padding:20px; color:red; text-align:center;'>❌ ไม่พบข้อมูลการเคลมเลขที่ $id ในระบบ</div>");
    }

    // ดึงรายการอะไหล่
    $stmtItems = $pdo->prepare("SELECT * FROM claim_items WHERE claim_id = ?");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();

    // จัดรูปแบบ ID (C001-DDMMYY)
    $idPart = "C" . str_pad($claim['id'], 3, '0', STR_PAD_LEFT);
    $datePart = "000000";
    $claimDateFormatted = '-'; 

    if (!empty($claim['claim_date']) && $claim['claim_date'] !== '0000-00-00') {
        $timestamp = strtotime($claim['claim_date']);
        if ($timestamp !== false) {
            $buddhistYearShort = substr((date('Y', $timestamp) + 543), -2);
            $datePart = date('dm', $timestamp) . $buddhistYearShort;
            $claimDateFormatted = date('d/m/Y', $timestamp);
        }
    }
    $doc_id = $idPart . "-" . $datePart;

    $cType = $claim['claim_type'] ?? '';

    $val = !empty($claim['claim_images']) ? $claim['claim_images'] : '[]';
    $allImages = json_decode($val, true);
    if (!is_array($allImages)) $allImages = [];
    
    $problemImages = [];
    $partsImages = [];
    $vinLabel = !empty($claim['vin']) ? str_replace(['/', '\\', ' '], '_', $claim['vin']) : 'UnknownVIN';

    foreach ($allImages as $img) {
        if (!empty($img) && is_string($img)) {
            if (strpos($img, 'ภาพอะไหล่ที่เคลม') !== false) {
                $partsImages[] = $img;
            } else {
                $problemImages[] = $img;
            }
        }
    }

} catch (Exception $e) {
    die("เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../shared/assets/css/theme.css">
    <link rel="stylesheet" href="../shared/assets/css/styles-edit_claim.css">
</head>
<body>

    <?php 
        $current_page = 'history.php'; 
        include __DIR__ . '/../shared/assets/includes/sidebar.php'; 
    ?>

    <div class="main-content">
        <div class="container-fluid p-0">
        
            <div class="filter-bar mb-4">
                <div class="row w-100 align-items-center g-3">
                    <div class="col-12 col-md-6">
                        <div class="fs-xl fw-600">ประวัติเคลม <span class="color-999 fw-normal">/ <?= $doc_id ?> / แก้ไข</span></div>
                    </div>
                    <div class="col-12 col-md-6 text-md-end">
                        <div class="d-flex gap-2 justify-content-md-end">
                            <a href="#verification-section" class="btn-action bg-primary-orange text-decoration-none px-3 py-1 color-fff">ไปยังลงชื่อผู้แก้ไข</a>
                            <a href="history.php" class="btn-action bg-secondary text-decoration-none px-3 py-1 color-fff">ย้อนกลับ</a>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="../backend/edit_handler.php" enctype="multipart/form-data">
                <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
                <input type="hidden" name="claimDate" value="<?= $claim['claim_date'] ?>">
                <input type="hidden" name="carType" value="<?= $claim['car_type'] ?>">
                <input type="hidden" name="carBrand" value="<?= $claim['car_brand'] ?>">
                <input type="hidden" name="usedGrade" value="<?= $claim['used_grade'] ?>">

                <div class="edit-container mb-5">
                    
                    <!-- ส่วนข้อมูลหลัก -->
                    <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">แก้ไขข้อมูล</div>
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <div class="d-flex flex-column gap-3">
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600">สาขา</label>
                                        <div class="col-sm-8">
                                            <select name="branch" class="form-select border-2" required>
                                                <option value="สาขา สกลนคร" <?= $claim['branch'] == 'สาขา สกลนคร' ? 'selected' : '' ?>>สาขา สกลนคร</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600">ประเภทการเคลม</label>
                                        <div class="col-sm-8">
                                            <select name="claim_category" class="form-select border-2" required>
                                                <option value="เคลมรถก่อนขาย" <?= $claim['claim_category'] == 'pre-sale' || $claim['claim_category'] == 'เคลมรถก่อนขาย' ? 'selected' : '' ?>>เคลมรถก่อนขาย</option>
                                                <option value="เคลมปัญหาทางเทคนิค" <?= $claim['claim_category'] == 'technical' || $claim['claim_category'] == 'เคลมปัญหาทางเทคนิค' ? 'selected' : '' ?>>เคลมปัญหาทางเทคนิค</option>
                                                <option value="เคลมรถลูกค้า" <?= $claim['claim_category'] == 'customer' || $claim['claim_category'] == 'เคลมรถลูกค้า' ? 'selected' : '' ?>>เคลมรถลูกค้า</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600">เลขที่เอกสาร</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light border-0" value="<?= $doc_id ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-lg-6">
                                <div class="d-flex flex-column gap-3">
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600 color-555">ผู้บันทึก</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['recorder_id'] ?? '-') ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600 color-555">วันที่บันทึก</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light border-0" value="<?= $claimDateFormatted ?>" readonly>
                                        </div>
                                    </div>
                                    <?php if(!empty($claim['editor_id'])): ?>
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600 color-555 text-primary-orange">ผู้แก้ไขล่าสุด</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light border-0 text-primary-orange fw-bold" value="<?= htmlspecialchars($claim['editor_id']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600 color-555 text-primary-orange">วันที่แก้ไข</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light border-0 text-primary-orange fw-bold" value="<?= date('d/m/Y H:i', strtotime($claim['updated_at'])) ?>" readonly>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- ส่วนการเลือกประเภทการดำเนินการ -->
                        <div class="mt-4 p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold color-primary-orange">การดำเนินการ</label>
                                    <div class="mt-2 text-dark">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input act-radio" type="radio" name="claimAction" value="RepairBranch" id="actRepair" <?= $cType === 'RepairBranch' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="actRepair">ซ่อมที่สาขา</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input act-radio" type="radio" name="claimAction" value="SendHQ" id="actHQ" <?= $cType === 'SendHQ' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="actHQ">ส่งซ่อมที่สนญ.</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input act-radio" type="radio" name="claimAction" value="ReplaceVehicle" id="actReplace" <?= $cType === 'ReplaceVehicle' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="actReplace">เปลี่ยนคันใหม่</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold color-primary-orange">การส่งอะไหล่ / ดำเนินการ</label>
                                    <div id="repairDeliverySection" class="<?= ($cType === 'ReplaceVehicle') ? 'd-none' : '' ?>">
                                        <div class="d-flex flex-wrap gap-3 mt-1">
                                            <?php $pd = $claim['parts_delivery'] ?? ''; ?>
                                            <div class="form-check">
                                                <input class="form-check-input pd-radio" type="radio" name="partsDelivery" value="in_stock" id="pd1" <?= $pd == 'in_stock' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="pd1">มีอะไหล่ในสต็อก</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input pd-radio" type="radio" name="partsDelivery" value="wait_hq" id="pd2" <?= $pd == 'wait_hq' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="pd2">รอเบิกจาก สนญ.</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input pd-radio" type="radio" name="partsDelivery" value="buy_outside" id="pd3" <?= $pd == 'buy_outside' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="pd3">ซื้อร้านภายนอก</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ส่วนผู้อนุมัติการซ่อม -->
                            <div id="repairApproverSection" class="section-sub-card mt-4 <?= ($cType === 'RepairBranch' || $cType === 'SendHQ') ? '' : 'd-none' ?>" style="border-top: 4px solid var(--primary-orange);">
                                <div class="section-sub-title"><i class="fas fa-signature"></i> ผู้อนุมัติการซ่อม</div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="icon-label"><i class="fas fa-id-badge"></i> รหัสพนักงาน</label>
                                        <select name="repair_id" id="repair_id" class="form-select employee-select" data-target-name="repair_name" data-target-sig="repair_signature">
                                            <option value="<?= htmlspecialchars($claim['repair_app_id'] ?? '') ?>"><?= htmlspecialchars($claim['repair_app_id'] ?? '-- เลือกพนักงาน --') ?></option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="icon-label"><i class="fas fa-user-check"></i> ชื่อผู้อนุมัติ</label>
                                        <input type="text" name="repair_name" id="repair_name" class="form-control bg-light" readonly placeholder="ชื่อพนักงาน" value="<?= htmlspecialchars($claim['repair_app_name'] ?? '') ?>">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="icon-label"><i class="fas fa-pen-nib"></i> ลายเซ็นต์</label>
                                        <input type="text" name="repair_signature" id="repair_signature" class="form-control bg-light" readonly placeholder="ลายเซ็นต์" value="<?= htmlspecialchars($claim['repair_app_sig'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- ส่วนรายละเอียดขอเปลี่ยนคันใหม่ -->
                            <div id="replaceBlock" class="card mt-4 p-4 shadow-sm border-0 <?= ($cType === 'ReplaceVehicle') ? '' : 'd-none' ?>">
                                <h5 class="fw-bold mb-4"><i class="fas fa-sync-alt me-2"></i> รายละเอียดการเปลี่ยนคันใหม่</h5>
                                
                                <!-- Group 1: Financial Details -->
                                <div class="section-sub-card mb-4">
                                    <div class="section-sub-title"><i class="fas fa-wallet"></i> ยอดเงินคงเหลือ (เงินดาวน์)</div>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-6">
                                            <label class="icon-label"><i class="fas fa-car"></i> รถคันเก่า</label>
                                            <div class="input-group money-input-group">
                                                <input type="number" step="0.01" name="old_down_balance" class="form-control" placeholder="0.00" value="<?= htmlspecialchars($claim['old_down_balance'] ?? '') ?>">
                                                <span class="input-group-text">บาท</span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="icon-label"><i class="fas fa-star text-success"></i> รถคันใหม่</label>
                                            <div class="input-group money-input-group">
                                                <input type="number" step="0.01" name="new_down_balance" class="form-control" placeholder="0.00" value="<?= htmlspecialchars($claim['new_down_balance'] ?? '') ?>">
                                                <span class="input-group-text">บาท</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group 2: Vehicle Specs -->
                                <div class="section-sub-card mb-4">
                                    <div class="section-sub-title"><i class="fas fa-info-circle"></i> ข้อมูลรถคันใหม่</div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-tags"></i> ประเภทรถ</label>
                                            <div class="d-flex gap-3 align-items-center" style="height: 38px; background: #fff; border: 1px solid #eee; padding: 0 15px; border-radius: 8px;">
                                                <div class="form-check m-0">
                                                    <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repNew" value="รถใหม่" <?= (($claim['rp_type'] ?? '') == 'รถใหม่' || ($claim['rp_type'] ?? '') == 'new') ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="repNew">รถใหม่</label>
                                                </div>
                                                <div class="form-check m-0">
                                                    <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repUsed" value="รถมือสอง" <?= (($claim['rp_type'] ?? '') == 'รถมือสอง' || ($claim['rp_type'] ?? '') == 'used') ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="repUsed">รถมือสอง</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 rep-grade-field <?= ($claim['rp_type'] ?? '') == 'รถมือสอง' || ($claim['rp_type'] ?? '') == 'used' ? '' : 'd-none' ?>" id="repGradeField">
                                            <label class="icon-label text-primary-orange"><i class="fas fa-medal"></i> เกรดรถมือสอง <span class="text-danger">*</span></label>
                                            <select name="replaceUsedGrade" class="form-select border-primary-subtle">
                                                <option value="">-- เลือกเกรด --</option>
                                                <option value="A_premium" <?= ($claim['rp_used_grade'] ?? '') == 'A_premium' ? 'selected' : '' ?>>A พรีเมี่ยม</option>
                                                <option value="A_w6" <?= ($claim['rp_used_grade'] ?? '') == 'A_w6' ? 'selected' : '' ?>>A รับประกันเครื่องยนต์ 6 เดือน</option>
                                                <option value="C_w1" <?= ($claim['rp_used_grade'] ?? '') == 'C_w1' ? 'selected' : '' ?>>C รับประกันเครื่องยนต์ 1 เดือน</option>
                                                <option value="C_as_is" <?= ($claim['rp_used_grade'] ?? '') == 'C_as_is' ? 'selected' : '' ?>>C ตามสภาพไม่รับประกัน</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-calendar-alt"></i> วันที่รับรถ</label>
                                            <input type="date" name="replace_receive_date" class="form-control" value="<?= !empty($claim['rp_receive_date']) ? date('Y-m-d', strtotime($claim['rp_receive_date'])) : '' ?>">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-car-side"></i> รุ่น</label>
                                            <input type="text" name="replace_model" class="form-control" placeholder="ระบุรุ่น" value="<?= htmlspecialchars($claim['rp_model'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-palette"></i> สี</label>
                                            <input type="text" name="replace_color" class="form-control" placeholder="ระบุสี" value="<?= htmlspecialchars($claim['rp_color'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-fingerprint"></i> เลขตัวถัง (คันใหม่)</label>
                                            <input type="text" name="replace_vin" class="form-control" placeholder="เลขตัวถัง / VIN" value="<?= htmlspecialchars($claim['replace_vin'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="row g-3 mt-1">
                                        <div class="col-12">
                                            <label class="icon-label"><i class="fas fa-comment-dots"></i> สาเหตุที่เปลี่ยนคัน</label>
                                            <textarea name="replace_reason" class="form-control" rows="2" placeholder="ระบุรายละเอียดสาเหตุที่ต้องเปลี่ยนรถคันใหม่..."><?= htmlspecialchars($claim['rp_reason'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Group 3: Approval Details -->
                                <div class="section-sub-card">
                                    <div class="section-sub-title"><i class="fas fa-check-circle"></i> ผู้อนุมัติการเปลี่ยนรถ</div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-id-badge"></i> รหัสพนักงาน</label>
                                            <select name="replace_id" id="replace_id" class="form-select employee-select" data-target-name="replace_name" data-target-sig="replace_signature">
                                                <option value="<?= htmlspecialchars($claim['rp_app_id'] ?? '') ?>"><?= htmlspecialchars($claim['rp_app_id'] ?? '-- เลือกพนักงาน --') ?></option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-user-check"></i> ชื่อผู้อนุมัติ</label>
                                            <input type="text" name="replace_name" id="replace_name" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['rp_app_name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-pen-nib"></i> ลายเซ็นต์</label>
                                            <input type="text" name="replace_signature" id="replace_signature" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['rp_app_sig'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="row g-3 mt-1">
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-calendar-check"></i> วันที่อนุมัติ</label>
                                            <input type="date" name="replace_approve_date" class="form-control" value="<?= !empty($claim['rp_app_date']) ? date('Y-m-d', strtotime($claim['rp_app_date'])) : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลผู้ซื้อ / ผู้ใช้งาน -->
                    <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลผู้ใช้งาน</div>
                        <div class="row g-4">
                            <div class="col-12 col-lg-6">
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label fw-600 req">ชื่อ-นามสกุล</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="ownerName" class="form-control border-2" value="<?= htmlspecialchars($claim['owner_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="ownerPhone" class="form-control border-2" value="<?= htmlspecialchars($claim['owner_phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-600">ที่อยู่</label>
                                    <div class="col-sm-8">
                                        <textarea name="ownerAddress" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['owner_address'] ?? '') ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-12 col-lg-6">
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label fw-600 req">เลขตัวถัง (VIN)</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="vin" id="vin_number" class="form-control border-2" value="<?= htmlspecialchars($claim['vin'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label fw-600">เลขไมล์</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="number" name="mileage" class="form-control border-2" value="<?= htmlspecialchars($claim['mileage'] ?? 0) ?>">
                                            <span class="input-group-text">กม.</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label fw-600">วันที่ขาย</label>
                                    <div class="col-sm-8">
                                        <input type="date" name="sale_date" id="sale_date" class="form-control border-2" value="<?= htmlspecialchars($claim['sale_date'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row align-items-center">
                                    <label class="col-sm-4 col-form-label fw-600">อายุรถ</label>
                                    <div class="col-sm-8">
                                        <input type="text" id="vehicle_age" class="form-control bg-light border-0 fw-bold text-danger" readonly placeholder="คำนวณอัตโนมัติ">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- รายละเอียดปัญหา -->
                    <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ปัญหาที่พบ</div>
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-600">อาการปัญหาที่ลูกค้าแจ้ง</label>
                                <textarea name="problemDesc" class="form-control border-2" rows="3" required><?= htmlspecialchars($claim['problem_desc'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">วิธีการตรวจเช็ค</label>
                                <textarea name="inspectMethod" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['inspect_method'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">สาเหตุของปัญหา</label>
                                <textarea name="inspectCause" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['inspect_cause'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- รูปภาพปัญหา (Gallery) -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="fw-bold"><i class="fas fa-camera me-1 text-primary-orange"></i> รูปภาพปัญหา <span id="img-count-badge" class="badge rounded-pill bg-primary-orange ms-1" style="display:none;">0 รูป</span></div>
                                <label class="btn btn-sm btn-outline-orange">
                                    + เพิ่มรูปภาพ
                                    <input type="file" id="image-upload" multiple accept="image/*" class="d-none">
                                </label>
                            </div>
                            <div class="gallery-grid" id="gallery-grid">
                                <?php 
                                if(is_array($problemImages)):
                                    foreach($problemImages as $idx => $imgPath): 
                                        $ext = pathinfo($imgPath, PATHINFO_EXTENSION);
                                        $downloadName = "รูปภาพปัญหา_" . $vinLabel . "_" . ($idx + 1) . "." . $ext;
                                ?>
                                    <div class="gallery-item existing-item">
                                        <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($imgPath) ?>">
                                        <div class="img-wrap" style="position:relative;">
                                            <a href="../<?= htmlspecialchars($imgPath) ?>" target="_blank">
                                                <img src="../<?= htmlspecialchars($imgPath) ?>" class="preview-img cursor-pointer" style="width:100%; height:160px; object-fit:cover; border-radius:8px 8px 0 0;">
                                            </a>
                                        </div>
                                        <div class="img-preview-footer d-flex justify-content-center gap-2 p-2 bg-light border-top">
                                            <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= htmlspecialchars($downloadName) ?>" class="btn btn-info btn-sm px-2 text-white" style="font-size:11px; background-color: #0dcaf0 !important; border:none; min-width: 60px;">
                                                <i class="fas fa-download"></i> โหลด
                                            </a>
                                            <button type="button" class="btn btn-warning btn-sm btn-remove-existing text-white" style="font-size:11px; background-color: #ffc107 !important; border:none; min-width: 60px;">
                                                <i class="fas fa-times"></i> ลบออก
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- รายการอะไหล่ -->
                    <div id="partsTableSection" class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4 overflow-hidden <?= ($cType === 'ReplaceVehicle') ? 'd-none' : '' ?>">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5 d-flex justify-content-between">
                            <span>รายการอะไหล่</span>
                            <div class="parts-image-upload">
                                <button type="button" id="btnUploadParts" class="btn btn-sm btn-success">รูปภาพอะไหล่</button>
                                <input type="file" id="imgPartsUpload" name="imgParts[]" multiple accept="image/*" class="d-none">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">#</th>
                                        <th width="150">รหัสอะไหล่</th>
                                        <th>ชื่ออะไหล่</th>
                                        <th width="150">หมายเหตุ</th>
                                        <th width="120" class="text-end">ราคา/หน่วย</th>
                                        <th width="100" class="text-center">จำนวน</th>
                                        <th width="120" class="text-end">เป็นเงิน</th>
                                        <th width="50" class="text-center">ลบ</th>
                                    </tr>
                                </thead>
                                <tbody id="parts-main-tbody">
                                    <?php 
                                    $totalQty = 0; $totalMoney = 0;
                                    if (!empty($items)): 
                                        foreach($items as $index => $item):
                                            $totalQty += $item['quantity'];
                                            $totalMoney += ($item['quantity'] * $item['unit_price']);
                                    ?>
                                        <tr class="part-row">
                                            <td class="idx text-center"><?= $index + 1 ?></td>
                                            <td><input type="text" name="parts_code[]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['part_code'] ?? '') ?>"></td>
                                            <td><input type="text" name="parts_name[]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['part_name'] ?? '') ?>" required></td>
                                            <td><input type="text" name="parts_note[]" class="form-control form-control-sm" value="<?= htmlspecialchars($item['note'] ?? '') ?>"></td>
                                            <td><input type="number" step="0.01" name="parts_price[]" class="form-control form-control-sm part-price text-end" value="<?= $item['unit_price'] ?>"></td>
                                            <td><input type="number" step="0.1" name="parts_qty[]" class="form-control form-control-sm part-qty text-center" value="<?= $item['quantity'] ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm part-total text-end bg-light" value="<?= number_format($item['quantity'] * $item['unit_price'], 2) ?>" readonly></td>
                                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-part">❌</button></td>
                                        </tr>
                                    <?php endforeach; endif; ?>

                                    <tr id="add-main-row">
                                        <td colspan="8" class="p-3 text-center">
                                            <button type="button" class="btn btn-outline-orange btn-sm w-100 py-3 border-dashed" id="btn-add-main">+ เพิ่มรายการอะไหล่</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td colspan="5" class="text-end py-3">รวมสุทธิ</td>
                                        <td class="text-center text-primary-orange" id="sum-qty"><?= $totalQty ?></td>
                                        <td class="text-end text-primary-orange" id="sum-money"><?= number_format($totalMoney, 2) ?></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="partsImgPreview" class="gallery-grid mt-3">
                            <?php 
                            if(is_array($partsImages)):
                                foreach($partsImages as $idx => $imgPath): 
                                    $ext = pathinfo($imgPath, PATHINFO_EXTENSION);
                                    $downloadName = "รายการอะไหล่_" . $vinLabel . "_" . ($idx + 1) . "." . $ext;
                            ?>
                                <div class="gallery-item existing-item">
                                    <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($imgPath) ?>">
                                    <div class="img-wrap" style="position:relative;">
                                        <a href="../<?= htmlspecialchars($imgPath) ?>" target="_blank">
                                            <img src="../<?= htmlspecialchars($imgPath) ?>" class="preview-img cursor-pointer" style="width:100%; height:160px; object-fit:cover; border-radius:8px 8px 0 0;">
                                        </a>
                                    </div>
                                    <div class="img-preview-footer d-flex justify-content-center gap-2 p-2 bg-light border-top">
                                        <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= htmlspecialchars($downloadName) ?>" class="btn btn-info btn-sm px-2 text-white" style="font-size:11px; background-color: #0dcaf0 !important; border:none; min-width: 60px;">
                                            <i class="fas fa-download"></i> โหลด
                                        </a>
                                        <button type="button" class="btn btn-warning btn-sm btn-remove-existing text-white" style="font-size:11px; background-color: #ffc107 !important; border:none; min-width: 60px;">
                                            <i class="fas fa-times"></i> ลบออก
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>

                    <!-- ข้อมูลการซ่อม (Job) -->
                    <div class="job-info-card mb-4 border-0 shadow-sm rounded-4 p-4">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลการซ่อม (Job)</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary mb-2">เลขที่ Job</label>
                                <input type="text" name="job_number" class="form-control pill-input" placeholder="ระบุเลขที่ JOB" value="<?= htmlspecialchars($claim['job_number'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-secondary mb-2">จำนวนเงิน (บาท)</label>
                                <input type="number" step="0.01" name="job_amount" class="form-control pill-input" placeholder="ระบุจำนวนเงิน" value="<?= htmlspecialchars($claim['job_amount'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- สถานะและการลงชื่อ (Design Updated matching Mockup) -->
                    <div class="edit-log-card mb-5" id="verification-section">
                        <div class="edit-log-title">ลงชื่อผู้แก้ไขเอกสาร</div>
                        <hr style="opacity: 0.1; margin-bottom: 2rem;">
                        
                        <div class="row g-4">
                            <!-- Status Row -->
                            <div class="col-12 d-flex align-items-center gap-3 mb-2 flex-wrap">
                                <label class="form-label fw-bold text-secondary mb-0" style="min-width: 60px;">สถานะ :</label>
                                <select name="status" id="doc_status" class="form-select pill-select" style="max-width: 320px;">
                                    <option value="">--กรุณาเลือกผลการตรวจสอบ--</option>
                                <option value="Approved Claim" <?= $claim['status'] == 'Approved Claim' ? 'selected' : '' ?>>อนุมัติการเคลม</option>
                                <option value="Approved Replacement" <?= $claim['status'] == 'Approved Replacement' ? 'selected' : '' ?>>อนุมัติเปลี่ยนคัน</option>
                                <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                                <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>เปลี่ยน  </option>
                                <option value="Pending Fix" <?= $claim['status'] == 'Pending Fix' ? 'selected' : '' ?>>รอแก้ไข</option>
                                <option value="Pending" <?= $claim['status'] == 'Pending' ? 'selected' : '' ?>>ดำเนินการเสร็จสิ้น</option>
                                </select>
                            </div>

                            <!-- Notes Row -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary mb-2">หมายเหตุ / ความเห็นของผู้แก้ไข</label>
                                <textarea name="remarks" id="edit_remarks" class="form-control pill-textarea" placeholder="ระบุเหตุผลการแก้ไข"><?= htmlspecialchars($claim['verify_remarks'] ?? '') ?></textarea>
                            </div>

                            <!-- Inline Footer Fields & Buttons -->
                            <div class="col-12 d-flex justify-content-between align-items-end mt-2 flex-wrap gap-4">
                                <div class="d-flex flex-column gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <label class="form-label fw-bold text-secondary mb-0" style="min-width: 100px;">ลงชื่อผู้แก้ไข</label>
                                        <input type="text" name="editor" class="form-control pill-input-sm" placeholder="ชื่อ-นามสกุล ผู้ตรวจสอบ" required value="<?= htmlspecialchars($claim['editor_id'] ?? '') ?>">
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <label class="form-label fw-bold text-secondary mb-0" style="min-width: 100px;">วันที่แก้ไข</label>
                                        <input type="text" name="edit_date" class="form-control pill-input-sm" value="<?= date('d/m/Y') ?>" readonly placeholder="00/00/0000">
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-3 pb-1">
                                    <a href="history.php" class="btn btn-pill-cancel">ยกเลิก</a>
                                    <button type="submit" class="btn btn-pill-save px-4">บันทึกการแก้ไข</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal ขยายรูป -->
    <div class="modal-overlay" id="image-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
        <div style="position:absolute; top:20px; right:20px; color:white; font-size:40px; cursor:pointer;" id="modal-close">×</div>
        <img src="" id="modal-img" style="max-width:90%; max-height:90%; object-fit:contain;">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // --- รูปภาพเดิม / รูปภาพใหม่ ---
            const uploadedFiles = [];
            document.getElementById('image-upload')?.addEventListener('change', function(e) {
                const files = e.target.files;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    uploadedFiles.push(file);
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        const div = document.createElement('div');
                        div.className = 'gallery-item';
                        const fileIdx = uploadedFiles.length - 1;
                        div.innerHTML = `
                            <div class="img-wrap" style="position:relative;">
                                <img src="${evt.target.result}" class="preview-img cursor-pointer" style="width:100%; height:160px; object-fit:cover; border-radius:8px 8px 0 0;">
                            </div>
                            <div class="img-preview-footer d-flex justify-content-center gap-2 p-2 bg-light border-top">
                                <button type="button" class="btn btn-warning btn-sm btn-remove-new text-white" data-idx="${fileIdx}" style="font-size:11px; background-color: #ffc107 !important; border:none; min-width: 60px;">
                                    <i class="fas fa-times"></i> ลบออก
                                </button>
                            </div>
                        `;
                        div.querySelector('.btn-remove-new').addEventListener('click', function() {
                            const idx = this.getAttribute('data-idx');
                            uploadedFiles[idx] = null;
                            div.remove();
                            updateImgCountBadge();
                        });
                        document.getElementById('gallery-grid').appendChild(div);
                        updateImgCountBadge();
                    }
                    reader.readAsDataURL(file);
                }
                this.value = '';
            });

            document.querySelectorAll('.btn-remove-existing').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.gallery-item').remove();
                    updateImgCountBadge();
                });
            });

            function updateImgCountBadge() {
                const badge = document.getElementById('img-count-badge');
                const count = document.querySelectorAll('#gallery-grid .gallery-item').length;
                if (badge) {
                    badge.textContent = count + ' รูป';
                    badge.style.display = count > 0 ? 'inline-block' : 'none';
                }
            }
            updateImgCountBadge();

            // --- Lightbox ---
            const imageModal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-img');
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('preview-img')) {
                    e.preventDefault(); // ป้องกันการเปิดลิงก์ target_blank
                    modalImg.src = e.target.src;
                    imageModal.style.display = 'flex';
                }
            });
            document.getElementById('modal-close').addEventListener('click', () => imageModal.style.display = 'none');
            imageModal.addEventListener('click', (e) => { if (e.target === imageModal) imageModal.style.display = 'none'; });

            // --- คำนวณอายุรถ ---
            const saleDateInput = document.getElementById('sale_date');
            const ageDisplay = document.getElementById('vehicle_age');
            function calculateVehicleAge() {
                if (!saleDateInput.value) { ageDisplay.value = ''; return; }
                const start = new Date(saleDateInput.value);
                start.setHours(0,0,0,0);
                const end = new Date();
                
                let years = end.getFullYear() - start.getFullYear();
                let months = end.getMonth() - start.getMonth();
                let days = end.getDate() - start.getDate();
                let hours = end.getHours();
                
                if (days < 0) {
                    months--;
                    const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
                    days += prevMonth.getDate();
                }
                if (months < 0) {
                    years--;
                    months += 12;
                }
                
                let res = [];
                if (years > 0) res.push(years + " ปี");
                if (months > 0) res.push(months + " เดือน");
                if (days > 0) res.push(days + " วัน");
                res.push(hours + " ชั่วโมง");
                
                ageDisplay.value = res.join(" ");
            }
            if (saleDateInput) {
                saleDateInput.addEventListener('change', calculateVehicleAge);
                calculateVehicleAge();
            }

            // --- จัดการการแสดงผลตาม Claim Type (Action) ---
            const repairSection = document.getElementById('repairApproverSection');
            const deliverySection = document.getElementById('repairDeliverySection');
            const replaceBlock = document.getElementById('replaceBlock');
            const partsTableSection = document.getElementById('partsTableSection');

            function updateVisibility() {
                const checkedRadio = document.querySelector('.act-radio:checked');
                const val = checkedRadio ? checkedRadio.value : '';
                
                if (val === 'ReplaceVehicle') {
                    if (replaceBlock) { replaceBlock.classList.remove('d-none'); replaceBlock.classList.add('d-block'); }
                    if (repairSection) repairSection.classList.add('d-none');
                    if (deliverySection) deliverySection.classList.add('d-none');
                    if (partsTableSection) partsTableSection.classList.add('d-none');
                } else {
                    if (replaceBlock) { replaceBlock.classList.add('d-none'); replaceBlock.classList.remove('d-block'); }
                    if (repairSection) repairSection.classList.remove('d-none');
                    if (deliverySection) deliverySection.classList.remove('d-none');
                    if (partsTableSection) partsTableSection.classList.remove('d-none');
                }
            }

            document.querySelectorAll('.act-radio').forEach(r => {
                r.addEventListener('change', updateVisibility);
            });
            updateVisibility(); // Run on load

            function updateGradeVisibility() {
                const checkedRadio = document.querySelector('.rep-car-type:checked');
                const gradeField = document.getElementById('repGradeField');
                if (gradeField) {
                    const isUsed = checkedRadio && (checkedRadio.value === 'used' || checkedRadio.value === 'รถมือสอง');
                    if (isUsed) {
                        gradeField.classList.remove('d-none');
                    } else {
                        gradeField.classList.add('d-none');
                    }
                }
            }
            document.querySelectorAll('.rep-car-type').forEach(r => {
                r.addEventListener('change', updateGradeVisibility);
            });
            updateGradeVisibility(); // Run on load

            // --- ระบบจัดการผู้อนุมัติแบบ Dropdown ---
            let employeeData = [];
            
            // ดึงข้อมูลพนักงานทั้งหมด
            fetch('../backend/api_users.php')
                .then(res => res.json())
                .then(resp => {
                    const data = resp.data || resp; // Handle both {success:true, data:[]} and raw []
                    employeeData = Array.isArray(data) ? data : [];

                        // เติมข้อมูลลงใน Dropdown ทุกตัวที่มี class employee-select
                        document.querySelectorAll('.employee-select').forEach(select => {
                            const currentVal = select.getAttribute('data-current') || select.value;
                            select.innerHTML = '<option value="">-- เลือกพนักงาน --</option>';
                            employeeData.forEach(emp => {
                                const option = document.createElement('option');
                                option.value = emp.employee_id;
                                option.textContent = emp.employee_id + ' - ' + emp.name;
                                if (emp.employee_id === currentVal) option.selected = true;
                                select.appendChild(option);
                            });
                        });
                })
                .catch(err => console.error('Error fetching employees:', err));

            // จัดการ Event เมื่อมีการเลือกพนักงาน (Autofill)
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('employee-select')) {
                    const select = e.target;
                    const empId = select.value;
                    const targetNameId = select.getAttribute('data-target-name');
                    const targetSigId = select.getAttribute('data-target-sig');
                    
                    const emp = employeeData.find(u => u.employee_id === empId);
                    if (emp) {
                        if (targetNameId) document.getElementById(targetNameId).value = emp.name;
                        if (targetSigId) document.getElementById(targetSigId).value = emp.signature || '';
                    } else {
                        if (targetNameId) document.getElementById(targetNameId).value = '';
                        if (targetSigId) document.getElementById(targetSigId).value = '';
                    }
                }
            });



            // --- ตารางอะไหล่และการคำนวณ ---
            function calculateAll() {
                let sQty = 0; let sPrice = 0;
                document.querySelectorAll('.part-row').forEach((row, i) => {
                    row.querySelector('.idx').textContent = i + 1;
                    const p = parseFloat(row.querySelector('.part-price').value) || 0;
                    const q = parseFloat(row.querySelector('.part-qty').value) || 0;
                    const t = p * q;
                    row.querySelector('.part-total').value = t.toLocaleString(undefined, {minimumFractionDigits: 2});
                    sQty += q; sPrice += t;
                });
                document.getElementById('sum-qty').textContent = sQty;
                document.getElementById('sum-money').textContent = sPrice.toLocaleString(undefined, {minimumFractionDigits: 2});
            }

            function attachPartEvents(row) {
                row.querySelectorAll('.part-price, .part-qty').forEach(el => el.addEventListener('input', calculateAll));
                row.querySelector('.btn-remove-part').addEventListener('click', () => { row.remove(); calculateAll(); });
            }
            document.querySelectorAll('.part-row').forEach(attachPartEvents);

            document.getElementById('btn-add-main')?.addEventListener('click', function() {
                const tbody = document.getElementById('parts-main-tbody');
                const tr = document.createElement('tr');
                tr.className = 'part-row';
                tr.innerHTML = `
                    <td class="idx text-center"></td>
                    <td><input type="text" name="parts_code[]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="parts_name[]" class="form-control form-control-sm" required></td>
                    <td><input type="text" name="parts_note[]" class="form-control form-control-sm"></td>
                    <td><input type="number" step="0.01" name="parts_price[]" class="form-control form-control-sm part-price text-end" value="0.00"></td>
                    <td><input type="number" step="0.1" name="parts_qty[]" class="form-control form-control-sm part-qty text-center" value="1"></td>
                    <td><input type="text" class="form-control form-control-sm part-total text-end bg-light" value="0.00" readonly></td>
                    <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-part">❌</button></td>
                `;
                tbody.insertBefore(tr, document.getElementById('add-main-row'));
                attachPartEvents(tr);
                calculateAll();
            });



            // --- รูปภาพอะไหล่ ---
            const partsFiles = [];
            document.getElementById('imgPartsUpload')?.addEventListener('change', function(e) {
                const pFiles = e.target.files;
                for(let f of pFiles) {
                    partsFiles.push(f);
                    const reader = new FileReader();
                    reader.onload = (evt) => {
                        const wrap = document.createElement('div');
                        wrap.className = 'gallery-item';
                        wrap.innerHTML = `
                            <div class="img-wrap" style="position:relative;">
                                <img src="${evt.target.result}" class="preview-img cursor-pointer" style="width:100%; height:160px; object-fit:cover; border-radius:8px 8px 0 0;">
                            </div>
                            <div class="img-preview-footer d-flex justify-content-center gap-2 p-2 bg-light border-top">
                                <button type="button" class="btn btn-warning btn-sm rm-p-img text-white" style="font-size:11px; background-color: #ffc107 !important; border:none; min-width: 60px;">
                                    <i class="fas fa-times"></i> ลบออก
                                </button>
                            </div>
                        `;
                        wrap.querySelector('.rm-p-img').onclick = () => { wrap.remove(); partsFiles[partsFiles.indexOf(f)] = null; };
                        document.getElementById('partsImgPreview').appendChild(wrap);
                    };
                    reader.readAsDataURL(f);
                }
            });
            document.getElementById('btnUploadParts').onclick = () => document.getElementById('imgPartsUpload').click();

            // --- ส่งฟอร์ม (AJAX) ---
            document.querySelector('form').addEventListener('submit', function(e) {
                const remarks = document.getElementById('edit_remarks');
                const pFix = '<?= $claim['status'] ?>' === 'Pending Fix';
                const changingStatus = document.getElementById('doc_status').value === 'Pending';
                
                if(pFix && changingStatus && remarks.value.trim() === '') {
                    e.preventDefault();
                    remarks.classList.add('border-danger');
                    showToast('❌ กรุณาระบุเหตุผล/บันทึกการแก้ไข ก่อนส่งไปตรวจสอบใหม่', 'error');
                    return;
                }

                e.preventDefault();
                const btn = this.querySelector('button[type="submit"]');
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '⏳ กำลังบันทึก...';

                const fd = new FormData(this);
                uploadedFiles.forEach(f => { if(f) fd.append('claim_images[]', f); });
                partsFiles.forEach(f => { if(f) fd.append('imgParts[]', f); });

                fetch('../backend/edit_handler.php', { method: 'POST', body: fd })
                .then(res => res.text())
                .then(text => {
                    if (text.includes('✅')) {
                        showToast('✅ บันทึกข้อมูลสำเร็จ', 'success');
                        setTimeout(() => {
                             window.location.href = 'history.php';
                        }, 1500);
                    } else {
                        showToast('❌ ' + text.replace(/<[^>]*>/g, ''), 'error');
                        btn.disabled = false; btn.innerHTML = originalText;
                    }
                })
                .catch(() => {
                    showToast('❌ ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
                    btn.disabled = false; btn.innerHTML = originalText;
                });
            });

            calculateAll();
        });
    </script>
</body>
</html>