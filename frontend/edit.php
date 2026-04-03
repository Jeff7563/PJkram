<?php
require_once __DIR__ . '/../backend/auth.php';
requireAdmin();
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
    
    $vinLabel = !empty($claim['vin']) ? str_replace(['/', '\\', ' '], '_', $claim['vin']) : 'UnknownVIN';

    // จัดกลุ่มรูปตามหมวด
    $imgCategories = [
      'ภาพรถทั้งคัน' => ['icon' => 'fa-car', 'color' => '#ff8533', 'field' => 'imgFullCar'],
      'ภาพจุดปัญหา' => ['icon' => 'fa-search-plus', 'color' => '#e74c3c', 'field' => 'imgSpot'],
      'ภาพชิ้นส่วน' => ['icon' => 'fa-cogs', 'color' => '#3498db', 'field' => 'imgPart'],
      'ภาพสมุดรับประกัน' => ['icon' => 'fa-book', 'color' => '#2ecc71', 'field' => 'imgWarranty'],
      'ภาพเลขไมล์' => ['icon' => 'fa-tachometer-alt', 'color' => '#9b59b6', 'field' => 'imgOdometer'],
      'ภาพใบประเมิน' => ['icon' => 'fa-file-alt', 'color' => '#f39c12', 'field' => 'imgEstimate'],
      'ภาพอะไหล่ที่เคลม' => ['icon' => 'fa-wrench', 'color' => '#1abc9c', 'field' => 'imgParts'],
    ];
    $groupedImages = [];
    $uncategorizedImages = [];
    foreach ($allImages as $img) {
      if (!empty($img) && is_string($img)) {
        $fname = basename($img);
        $matched = false;
        foreach ($imgCategories as $prefix => $info) {
          if (mb_strpos($fname, $prefix) === 0) {
            $groupedImages[$prefix][] = $img;
            $matched = true;
            break;
          }
        }
        if (!$matched) $uncategorizedImages[] = $img;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../shared/assets/js/utils.js"></script>
    <style>
        :root {
            --primary-orange: #ff8533;
            --success-green: #00b050;
        }
        body { background-color: #f8f9fa; font-family: 'Kanit', sans-serif; }
        .edit-card { 
            background: white; border-radius: 12px; padding: 25px; 
            box-shadow: 0 2px 15px rgba(0,0,0,0.08); border: 1px solid #eee;
            margin-bottom: 24px;
        }
        .section-title {
            font-size: 1.1rem; font-weight: 700; color: #000; 
            border-bottom: 2px solid #f2f2f2; padding-bottom: 12px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        /* Note Box Styles */
        .note-box {
          background-color: #fff9f2;
          border: 1px solid #ffe8d1;
          border-radius: 10px;
          padding: 15px 20px;
          margin-top: 20px;
          font-size: 0.95rem;
          color: #333;
        }
        .note-box .note-header { font-weight: 800; margin-bottom: 8px; }
        /* V3 Buttons */
        .btn-orange { background-color: var(--primary-orange); color: #fff; border: none; }
        .btn-orange:hover { background-color: #e6762d; color: #fff; }
        .btn-green { background-color: var(--success-green); color: #fff; border: none; }
        .btn-green:hover { background-color: #008f41; color: #fff; }
        .btn-remove-part-orange {
          background: var(--primary-orange);
          color: #fff; border: none; border-radius: 6px;
          width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        }
        /* Radio Pill Styling */
        .radio-pill-group { display: flex; flex-wrap: wrap; gap: 10px; }
        .radio-pill-group .form-check {
          background: #fff; border: 1px solid #ddd; border-radius: 50px;
          padding: 8px 18px 8px 38px; min-width: 120px; cursor: pointer; position: relative;
        }
        .radio-pill-group .form-check:hover { border-color: var(--primary-orange); }
        .radio-pill-group .form-check-input {
          position: absolute; left: 15px; top: 50%; transform: translateY(-50%); margin: 0;
        }
        .radio-pill-group .form-check:has(.form-check-input:checked) {
          border-color: var(--primary-orange); background-color: #fffaf7;
        }
        .attach-count { color: var(--primary-orange); font-weight: 700; }
        .gallery-item:hover .img-controls { opacity: 1 !important; transform: translateY(0); }
        .img-controls { transform: translateY(-5px); transition: all 0.2s ease-in-out; }
        .btn-xs { padding: 0.1rem 0.25rem; font-size: 0.75rem; line-height: 1; }
        .category-card:hover { border-color: #ff8533 !important; background-color: #fffaf7 !important; }
        .gallery-item img { transition: transform 0.3s; }
        .gallery-item:hover img { transform: scale(1.05); }
    </style>
</head>
<body>

    <?php 
        $current_page = 'history.php'; 
        include __DIR__ . '/../shared/assets/includes/sidebar.php'; 
        include __DIR__ . '/../shared/includes/components/toast.php';
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

            <form id="editClaimForm" method="POST" action="../backend/edit_handler.php" enctype="multipart/form-data">
                <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
                <input type="hidden" name="claim_date" value="<?= $claim['claim_date'] ?>">
                <input type="hidden" name="car_type" value="<?= $claim['car_type'] ?>">
                <input type="hidden" name="car_brand" value="<?= $claim['car_brand'] ?>">
                <input type="hidden" name="used_grade" value="<?= $claim['used_grade'] ?>">

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
                                            <select id="branch" name="branch" class="form-select border-2" required>
                                                <option value="">-- เลือกสาขา --</option>
                                                <?php 
                                                $stmtB = $pdo->query("SELECT branch_name FROM branches ORDER BY branch_name ASC");
                                                while ($bRow = $stmtB->fetch()) {
                                                    $sel = (trim($claim['branch']) == trim($bRow['branch_name'])) ? 'selected' : '';
                                                    echo '<option value="'.htmlspecialchars($bRow['branch_name']).'" '.$sel.'>'.htmlspecialchars($bRow['branch_name']).'</option>';
                                                }
                                                ?>
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
                                    <label class="form-label fw-bold" style="color: #000;">การดำเนินการ</label>
                                    <div class="radio-pill-group mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input act-radio" type="radio" name="claim_action" value="RepairBranch" id="actRepair" <?= $cType === 'RepairBranch' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="actRepair">ซ่อมที่สาขา</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input act-radio" type="radio" name="claim_action" value="SendHQ" id="actHQ" <?= $cType === 'SendHQ' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="actHQ">ส่งซ่อมที่สนญ.</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input act-radio" type="radio" name="claim_action" value="ReplaceVehicle" id="actReplace" <?= $cType === 'ReplaceVehicle' ? 'checked' : '' ?>>
                                            <label class="form-check-label fw-bold" for="actReplace">เปลี่ยนคันใหม่</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold" style="color: #000;">ประเภทการส่ง อะไหล่</label>
                                    <div id="repairDeliverySection" class="<?= ($cType === 'ReplaceVehicle') ? 'd-none' : '' ?>">
                                        <div class="radio-pill-group mt-2">
                                            <?php $pd = $claim['parts_delivery'] ?? ''; ?>
                                            <div class="form-check">
                                                <input class="form-check-input pd-radio" type="radio" name="parts_delivery" value="in_stock" id="pd1" <?= $pd == 'in_stock' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="pd1">ใช้อะไหล่ที่มีในสต็อกสาขา</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input pd-radio" type="radio" name="parts_delivery" value="wait_hq" id="pd2" <?= $pd == 'wait_hq' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="pd2">รอส่งอะไหล่จากสนญ.</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input pd-radio" type="radio" name="parts_delivery" value="buy_outside" id="pd3" <?= $pd == 'buy_outside' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="pd3">ซื้ออะไหล่ร้านนอก</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ส่วนผู้อนุมัติการดำเนินการ (ข้อมูลจาก verify.php) -->
                            <div id="repairApproverSection" class="p-3 border-start border-warning border-4 bg-warning-subtle rounded mt-4 <?= ($cType === 'RepairBranch' || $cType === 'SendHQ') ? '' : 'd-none' ?>">
                                <h6 class="fw-bold"><i class="fas fa-user-check me-2"></i> ผู้อนุมัติการดำเนินการ</h6>
                                <div class="alert alert-info py-2 px-3 mt-2 mb-3" style="font-size: 0.85rem; border-radius: 10px;">
                                    <i class="fas fa-info-circle me-1"></i> ข้อมูลนี้มาจากการอนุมัติที่หน้า <strong>ตรวจเช็ค</strong>
                                    <?php if(empty($claim['verifier_emp_id']) && empty($claim['verifier_name'])): ?>
                                    — <span class="text-danger fw-bold">ยังไม่มีการอนุมัติ</span>
                                    <?php endif; ?>
                                </div>
                                <div class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label class="icon-label"><i class="fas fa-id-badge"></i> รหัสพนักงาน</label>
                                        <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['verifier_emp_id'] ?? '') ?>" placeholder="ยังไม่มีข้อมูล">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="icon-label"><i class="fas fa-user-check"></i> ชื่อผู้อนุมัติ</label>
                                        <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['verifier_name'] ?? '') ?>" placeholder="ยังไม่มีข้อมูล">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label class="icon-label"><i class="fas fa-pen-nib"></i> ลายเซ็นต์</label>
                                        <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['verifier_signature'] ?? '') ?>" placeholder="ยังไม่มีข้อมูล">
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
                                                    <input class="form-check-input rep-car-type" type="radio" name="replace_type" id="repNew" value="รถใหม่" <?= (($claim['rp_type'] ?? '') == 'รถใหม่' || ($claim['rp_type'] ?? '') == 'new') ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="repNew">รถใหม่</label>
                                                </div>
                                                <div class="form-check m-0">
                                                    <input class="form-check-input rep-car-type" type="radio" name="replace_type" id="repUsed" value="รถมือสอง" <?= (($claim['rp_type'] ?? '') == 'รถมือสอง' || ($claim['rp_type'] ?? '') == 'used') ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="repUsed">รถมือสอง</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 rep-grade-field <?= ($claim['rp_type'] ?? '') == 'รถมือสอง' || ($claim['rp_type'] ?? '') == 'used' ? '' : 'd-none' ?>" id="repGradeField">
                                            <label class="icon-label text-primary-orange"><i class="fas fa-medal"></i> เกรดรถมือสอง <span class="text-danger">*</span></label>
                                            <select name="replace_used_grade" class="form-select border-primary-subtle">
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

                                <!-- Group 3: Approval Details (ข้อมูลจาก verify.php) -->
                                <div class="p-3 border-start border-warning border-4 bg-warning-subtle rounded">
                                    <h6 class="fw-bold"><i class="fas fa-check-circle me-2"></i> ผู้อนุมัติการเปลี่ยนรถ</h6>
                                    <div class="alert alert-info py-2 px-3 mt-2 mb-3" style="font-size: 0.85rem; border-radius: 10px;">
                                        <i class="fas fa-info-circle me-1"></i> ข้อมูลนี้มาจากการอนุมัติที่หน้า <strong>ตรวจเช็ค</strong>
                                        <?php if(empty($claim['verifier_emp_id']) && empty($claim['verifier_name'])): ?>
                                        — <span class="text-danger fw-bold">ยังไม่มีการอนุมัติ</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-id-badge"></i> รหัสพนักงาน</label>
                                            <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['verifier_emp_id'] ?? '') ?>" placeholder="ยังไม่มีข้อมูล">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-user-check"></i> ชื่อผู้อนุมัติ</label>
                                            <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['verifier_name'] ?? '') ?>" placeholder="ยังไม่มีข้อมูล">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="icon-label"><i class="fas fa-pen-nib"></i> ลายเซ็นต์</label>
                                            <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($claim['verifier_signature'] ?? '') ?>" placeholder="ยังไม่มีข้อมูล">
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
                                        <input type="text" name="owner_name" class="form-control border-2" value="<?= htmlspecialchars($claim['owner_name'] ?? '') ?>" required>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="owner_phone" class="form-control border-2" value="<?= htmlspecialchars($claim['owner_phone'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <label class="col-sm-4 col-form-label fw-600">ที่อยู่</label>
                                    <div class="col-sm-8">
                                        <textarea name="owner_address" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['owner_address'] ?? '') ?></textarea>
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
                                <label class="form-label fw-600">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
                                <textarea name="problem_desc" class="form-control border-2" rows="3" required><?= htmlspecialchars($claim['problem_desc'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-center d-block">วิธีตรวจเช็ค :</label>
                                <textarea name="inspect_method" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['inspect_method'] ?? '') ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 text-center d-block">สาเหตุของปัญหา :</label>
                                <textarea name="inspect_cause" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['inspect_cause'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <!-- Section: หมายเหตุ (V3 Style) -->
                        <div class="note-box mt-3">
                          <div class="note-header">***หมายเหตุ :</div>
                          <ol class="m-0 ps-3">
                            <li>รถมือสองมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117 หรือ 042-71135 ต่อ 201</li>
                            <li>รถใหม่มีปัญหาปรึกษาศูนย์บริการ Honda 086-4594656 Yamaha 086-4550614 Vespa 099-1285556</li>
                          </ol>
                        </div>

                        <!-- รูปภาพปัญหา (Categorized Gallery) -->
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="fw-bold"><i class="fas fa-camera me-1 text-primary-orange"></i> แกลเลอรีรูปภาพแยกหมวดหมู่</div>
                                <div class="badge rounded-pill bg-light text-dark border px-3 py-2" id="total-img-badge">รวมทั้งหมด 0 รูป</div>
                            </div>

                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3" id="main-image-gallery">
                                <?php foreach($imgCategories as $catName => $catInfo): 
                                    $existingImgs = $groupedImages[$catName] ?? [];
                                    $fieldId = $catInfo['field'];
                                ?>
                                <div class="col">
                                    <div class="category-card p-3 rounded-4 h-100 shadow-sm border border-2 border-dashed bg-white" style="transition: all 0.2s;">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background: <?= $catInfo['color'] ?>15;">
                                                <i class="fas <?= $catInfo['icon'] ?>" style="color: <?= $catInfo['color'] ?>; font-size: 14px;"></i>
                                            </div>
                                            <span class="fw-bold" style="font-size: 0.9rem;"><?= $catName ?></span>
                                            
                                            <label class="ms-auto mb-0 cursor-pointer text-primary" title="เพิ่มรูปภาพ">
                                                <i class="fas fa-plus-circle fa-lg"></i>
                                                <input type="file" name="<?= $fieldId ?>[]" class="d-none category-upload" multiple accept="image/*" data-category="<?= $catName ?>" data-field="<?= $fieldId ?>">
                                            </label>
                                        </div>

                                        <!-- Existing Images List -->
                                        <div class="existing-preview-grid d-flex flex-wrap gap-2 mb-2">
                                            <?php foreach($existingImgs as $idx => $imgPath): 
                                                $ext = pathinfo($imgPath, PATHINFO_EXTENSION);
                                                $dlName = $catName . '_' . $vinLabel . '_' . ($idx + 1) . '.' . $ext;
                                            ?>
                                            <div class="gallery-item existing-item shadow-sm border" style="position: relative; width: 72px; height: 72px; border-radius: 10px; overflow: hidden; border: 2px solid #fff;">
                                                <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($imgPath) ?>">
                                                <img src="../<?= htmlspecialchars($imgPath) ?>" class="preview-img cursor-pointer" style="width: 100%; height: 100%; object-fit: cover;">
                                                
                                                <!-- Download (Bottom-Right like verify.php) -->
                                                <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= $dlName ?>" class="position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center bg-dark text-white opacity-75" onclick="event.stopPropagation();" title="ดาวน์โหลด" style="width: 22px; height: 22px; font-size: 10px; border-radius: 8px 0 0 0; text-decoration: none;">
                                                    <i class="fas fa-download"></i>
                                                </a>

                                                <!-- Delete (Top-Right) -->
                                                <button type="button" class="btn-remove-existing position-absolute top-0 end-0 d-flex align-items-center justify-content-center bg-danger text-white border-0 opacity-75" onclick="event.stopPropagation();" title="ลบออก" style="width: 22px; height: 22px; font-size: 10px; border-radius: 0 0 0 8px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- New Upload Previews -->
                                        <div class="new-preview-grid d-flex flex-wrap gap-2" id="preview-<?= $fieldId ?>">
                                            <!-- New items injected here via JS -->
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <!-- Uncategorized / Others -->
                                <?php if(true): // ให้แสดงหมวด 'อื่นๆ' เสมอเพื่อให้เพิ่มรูปจิปาถะได้
                                    $fieldId = 'claim_images';
                                ?>
                                <div class="col">
                                    <div class="category-card p-3 rounded-4 h-100 shadow-sm border border-2 border-dashed bg-white" style="transition: all 0.2s;">
                                        <div class="d-flex align-items-center gap-2 mb-3">
                                            <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background: #6c757d15;">
                                                <i class="fas fa-images" style="color: #6c757d; font-size: 14px;"></i>
                                            </div>
                                            <span class="fw-bold" style="font-size: 0.9rem;">รูปอื่นๆ / เดิม</span>
                                            
                                            <label class="ms-auto mb-0 cursor-pointer text-primary" title="เพิ่มรูปภาพ">
                                                <i class="fas fa-plus-circle fa-lg"></i>
                                                <input type="file" name="<?= $fieldId ?>[]" class="d-none category-upload" multiple accept="image/*" data-category="รูปอื่นๆ" data-field="<?= $fieldId ?>">
                                            </label>
                                        </div>

                                        <!-- Existing Images List -->
                                        <div class="existing-preview-grid d-flex flex-wrap gap-2 mb-2">
                                            <?php foreach(($uncategorizedImages ?? []) as $idx => $imgPath): 
                                                $ext = pathinfo($imgPath, PATHINFO_EXTENSION);
                                                $dlName = 'Other_' . $vinLabel . '_' . ($idx + 1) . '.' . $ext;
                                            ?>
                                            <div class="gallery-item existing-item shadow-sm border" style="position: relative; width: 72px; height: 72px; border-radius: 10px; overflow: hidden; border: 2px solid #fff;">
                                                <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($imgPath) ?>">
                                                <img src="../<?= htmlspecialchars($imgPath) ?>" class="preview-img cursor-pointer" style="width: 100%; height: 100%; object-fit: cover;">
                                                
                                                <!-- Download (Bottom-Right like verify.php) -->
                                                <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= $dlName ?>" class="position-absolute bottom-0 end-0 d-flex align-items-center justify-content-center bg-dark text-white opacity-75" onclick="event.stopPropagation();" title="ดาวน์โหลด" style="width: 22px; height: 22px; font-size: 10px; border-radius: 8px 0 0 0; text-decoration: none;">
                                                    <i class="fas fa-download"></i>
                                                </a>

                                                <!-- Delete (Top-Right) -->
                                                <button type="button" class="btn-remove-existing position-absolute top-0 end-0 d-flex align-items-center justify-content-center bg-danger text-white border-0 opacity-75" onclick="event.stopPropagation();" title="ลบออก" style="width: 22px; height: 22px; font-size: 10px; border-radius: 0 0 0 8px;">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- New Upload Previews -->
                                        <div class="new-preview-grid d-flex flex-wrap gap-2" id="preview-<?= $fieldId ?>">
                                            <!-- New items injected here via JS -->
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- รายการอะไหล่ -->
                    <div id="partsTableSection" class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4 overflow-hidden <?= ($cType === 'ReplaceVehicle') ? 'd-none' : '' ?>">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">
                            <span>ระบุรายการอะไหล่ ที่ต้องการเคลม/จำนวน</span>
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
                                            <td class="idx text-center fw-bold"><?= $index + 1 ?></td>
                                            <td><input type="text" name="parts_code[]" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($item['part_code'] ?? '') ?>" placeholder="รหัสอะไหล่"></td>
                                            <td><input type="text" name="parts_name[]" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($item['part_name'] ?? '') ?>" required placeholder="ชื่ออะไหล่"></td>
                                            <td><input type="text" name="parts_note[]" class="form-control form-control-sm bg-light" value="<?= htmlspecialchars($item['note'] ?? '') ?>" placeholder="หมายเหตุ"></td>
                                            <td><input type="number" step="0.01" name="parts_price[]" class="form-control form-control-sm part-price text-center bg-light" value="<?= $item['unit_price'] ?>" placeholder="0.00"></td>
                                            <td><input type="number" step="0.1" name="parts_qty[]" class="form-control form-control-sm part-qty text-center bg-light" value="<?= $item['quantity'] ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm part-total text-end bg-light fw-bold" value="<?= number_format($item['quantity'] * $item['unit_price'], 2) ?>" readonly></td>
                                            <td class="text-center"><button type="button" class="btn btn-remove-part-orange remove"><i class="fas fa-trash-alt"></i></button></td>
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

                    <!-- สถานะและการลงชื่อ -->
                    <div class="edit-card mb-5 border-0 shadow-sm rounded-4 overflow-hidden" id="verification-section">
                        <div class="px-4 py-3" style="background: linear-gradient(135deg, #ff8533, #ffad73);">
                            <h5 class="fw-bold text-white m-0"><i class="fas fa-pen-fancy me-2"></i> ลงชื่อผู้แก้ไขเอกสาร</h5>
                        </div>
                        <div class="p-4">
                            <!-- สถานะ -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-secondary mb-2"><i class="fas fa-clipboard-check me-1"></i> สถานะ</label>
                                    <select name="status" id="doc_status" class="form-select" style="border-radius: 12px; padding: 12px 16px; border: 2px solid #e9ecef; font-size: 0.95rem;">
                                        <option value="">--กรุณาเลือกผลการตรวจสอบ--</option>
                                        <option value="Approved Claim" <?= $claim['status'] == 'Approved Claim' ? 'selected' : '' ?>>อนุมัติการเคลม</option>
                                        <option value="Approved Replacement" <?= $claim['status'] == 'Approved Replacement' ? 'selected' : '' ?>>อนุมัติเปลี่ยนคัน</option>
                                        <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                                        <option value="Replaced" <?= $claim['status'] == 'Replaced' ? 'selected' : '' ?>>เปลี่ยนคัน</option>
                                        <option value="Pending Fix" <?= $claim['status'] == 'Pending Fix' ? 'selected' : '' ?>>รอแก้ไข</option>
                                        <option value="Completed" <?= $claim['status'] == 'Completed' ? 'selected' : '' ?>>ดำเนินการเสร็จสิ้น</option>
                                    </select>
                                </div>
                            </div>

                            <!-- หมายเหตุ -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-secondary mb-2"><i class="fas fa-comment-dots me-1"></i> หมายเหตุ / ความเห็นของผู้แก้ไข</label>
                                <textarea name="remarks" id="edit_remarks" class="form-control" rows="3" style="border-radius: 12px; border: 2px solid #e9ecef; padding: 12px 16px;" placeholder="ระบุเหตุผลการแก้ไข..."><?= htmlspecialchars($claim['verify_remarks'] ?? '') ?></textarea>
                            </div>

                            <!-- ลงชื่อ + วันที่ -->
                            <div class="p-3 rounded-4 mb-4" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-secondary mb-2"><i class="fas fa-user-edit me-1"></i> ลงชื่อผู้แก้ไข</label>
                                        <input type="text" name="editor" class="form-control" style="border-radius: 12px; padding: 12px 16px; border: 2px solid #e9ecef;" placeholder="ชื่อ-นามสกุล ผู้แก้ไข" required value="<?= htmlspecialchars($claim['editor_id'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-secondary mb-2"><i class="fas fa-calendar-alt me-1"></i> วันที่แก้ไข</label>
                                        <input type="text" name="edit_date" class="form-control bg-white" style="border-radius: 12px; padding: 12px 16px; border: 2px solid #e9ecef;" value="<?= date('d/m/Y') ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <!-- ปุ่ม -->
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="history.php" class="btn px-4 py-2 fw-bold" style="border-radius: 12px; border: 2px solid #dee2e6; color: #6c757d; background: white; transition: all 0.2s;">
                                    <i class="fas fa-times me-1"></i> รีเซ็ต / ยกเลิก
                                </a>
                                <button type="submit" class="btn text-white px-5 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #ff8533, #ffad73); border: none; box-shadow: 0 4px 15px rgba(255,133,51,0.3); transition: all 0.2s;">
                                    <i class="fas fa-save me-1"></i> บันทึกการแก้ไข
                                </button>
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
            // --- Toggle Section Logic (V3) ---
            const actionRadios = document.querySelectorAll('.act-radio');
            const repairDeliverySection = document.getElementById('repairDeliverySection');
            const repairApproverSection = document.getElementById('repairApproverSection');
            const partsTableSection = document.getElementById('partsTableSection');
            const replaceBlock = document.getElementById('replaceBlock');

            actionRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    const val = this.value;
                    const isRepair = (val === 'RepairBranch' || val === 'SendHQ');
                    const isReplace = (val === 'ReplaceVehicle');

                    if(repairDeliverySection) repairDeliverySection.parentElement.classList.toggle('d-none', !isRepair);
                    if(repairApproverSection) repairApproverSection.classList.toggle('d-none', !isRepair);
                    if(partsTableSection) partsTableSection.classList.toggle('d-none', !isRepair);
                    if(replaceBlock) replaceBlock.classList.toggle('d-none', !isReplace);
                });
            });

            // Toggle Grade field in Replace Block
            const repCarTypeRadios = document.querySelectorAll('.rep-car-type');
            const repGradeField = document.getElementById('repGradeField');
            repCarTypeRadios.forEach(r => {
                r.addEventListener('change', function() {
                    if(repGradeField) {
                        repGradeField.classList.toggle('d-none', this.value !== 'รถมือสอง');
                    }
                });
            });

            // --- ระบบจัดการรูปภาพแบบแยกหมวดหมู่ (V3) ---
            const categoryUploads = {}; // { 'imgFullCar': [File, File], ... }

            // จัดการการอัปโหลดใหม่
            document.querySelectorAll('.category-upload').forEach(input => {
                input.addEventListener('change', function(e) {
                    const fieldId = this.dataset.field;
                    const catName = this.dataset.category;
                    const files = Array.from(e.target.files);
                    
                    if (!categoryUploads[fieldId]) categoryUploads[fieldId] = [];
                    
                    files.forEach(file => {
                        const fileId = Math.random().toString(36).substr(2, 9);
                        categoryUploads[fieldId].push({ id: fileId, file: file });
                        
                        const reader = new FileReader();
                        reader.onload = (evt) => {
                            const previewGrid = document.getElementById(`preview-${fieldId}`);
                            const div = document.createElement('div');
                            div.className = 'gallery-item new-item';
                            div.style.cssText = 'position: relative; width: 70px; height: 70px;';
                            div.innerHTML = `
                                <img src="${evt.target.result}" class="preview-img rounded-3 shadow-sm cursor-pointer" style="width: 100%; height: 100%; object-fit: cover;">
                                <button type="button" class="btn btn-danger btn-xs p-1 rounded-pill btn-remove-new position-absolute top-0 end-0 m-1" style="line-height:1; width:20px; height:20px;">
                                    <i class="fas fa-times" style="font-size:10px;"></i>
                                </button>
                                <span class="badge bg-success position-absolute bottom-0 start-0 m-1" style="font-size:8px;">ใหม่</span>
                            `;
                            div.querySelector('.btn-remove-new').onclick = () => {
                                categoryUploads[fieldId] = categoryUploads[fieldId].filter(f => f.id !== fileId);
                                div.remove();
                                updateImgCountBadge();
                            };
                            previewGrid.appendChild(div);
                            updateImgCountBadge();
                        };
                        reader.readAsDataURL(file);
                    });
                    this.value = ''; // Reset input
                });
            });

            // จัดการรูปภาพเดิม (ลบออก)
            document.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-existing')) {
                    const btn = e.target.closest('.btn-remove-existing');
                    const item = btn.closest('.gallery-item');
                    if (confirm('ยืนยันการลบรูปภาพนี้ออกจากรายการ?')) {
                        item.remove();
                        updateImgCountBadge();
                    }
                }
            });

            function updateImgCountBadge() {
                const totalText = document.getElementById('total-img-badge');
                const count = document.querySelectorAll('.gallery-item').length;
                if (totalText) {
                    totalText.textContent = `รวมทั้งหมด ${count} รูป`;
                }
            }
            updateImgCountBadge();

            // --- Lightbox ---
            const imageModal = document.getElementById('image-modal');
            const modalImg = document.getElementById('modal-img');
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('preview-img')) {
                    modalImg.src = e.target.src;
                    imageModal.style.display = 'flex';
                }
            });
            document.getElementById('modal-close').addEventListener('click', () => imageModal.style.display = 'none');
            imageModal.addEventListener('click', (e) => { if (e.target === imageModal) imageModal.style.display = 'none'; });

            // --- คำนวณอายุรถ ---
            const saleDateInput = document.getElementById('sale_date');
            const ageDisplay = document.getElementById('vehicle_age');
            function updateVehicleAge() {
                if (saleDateInput && ageDisplay) {
                    ageDisplay.value = PJUtils.calculateAge(saleDateInput.value);
                }
            }
            if (saleDateInput) {
                saleDateInput.addEventListener('change', updateVehicleAge);
                updateVehicleAge();
            }

            PJUtils.loadEmployees();

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
                const sumQtyEl = document.getElementById('sum-qty');
                const sumMoneyEl = document.getElementById('sum-money');
                if(sumQtyEl) sumQtyEl.textContent = sQty;
                if(sumMoneyEl) sumMoneyEl.textContent = sPrice.toLocaleString(undefined, {minimumFractionDigits: 2});
            }

            function attachPartEvents(row) {
                row.querySelectorAll('.part-price, .part-qty').forEach(el => el.addEventListener('input', calculateAll));
                const rmBtn = row.querySelector('.btn-remove-part-orange') || row.querySelector('.remove');
                if (rmBtn) rmBtn.addEventListener('click', () => { row.remove(); calculateAll(); });
            }
            document.querySelectorAll('.part-row').forEach(attachPartEvents);

            document.getElementById('btn-add-main')?.addEventListener('click', function() {
                const tbody = document.getElementById('parts-main-tbody');
                const tr = document.createElement('tr');
                tr.className = 'part-row';
                tr.innerHTML = `
                    <td class="idx text-center fw-bold"></td>
                    <td><input type="text" name="parts_code[]" class="form-control form-control-sm bg-light" placeholder="รหัสอะไหล่"></td>
                    <td><input type="text" name="parts_name[]" class="form-control form-control-sm bg-light" required placeholder="ชื่ออะไหล่"></td>
                    <td><input type="text" name="parts_note[]" class="form-control form-control-sm bg-light" placeholder="หมายเหตุ"></td>
                    <td><input type="number" step="0.01" name="parts_price[]" class="form-control form-control-sm part-price text-center bg-light" value="0.00"></td>
                    <td><input type="number" step="0.1" name="parts_qty[]" class="form-control form-control-sm part-qty text-center bg-light" value="1"></td>
                    <td><input type="text" class="form-control form-control-sm part-total text-end bg-light fw-bold" value="0.00" readonly></td>
                    <td class="text-center"><button type="button" class="btn btn-remove-part-orange remove"><i class="fas fa-trash-alt"></i></button></td>
                `;
                tbody.insertBefore(tr, document.getElementById('add-main-row'));
                attachPartEvents(tr);
                calculateAll();
            });

            // --- ส่งฟอร์ม (AJAX V3) ---
            const editForm = document.getElementById('editClaimForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const remarks = document.getElementById('edit_remarks');
                    const pFix = '<?= $claim['status'] ?>' === 'Pending Fix';
                    const changingStatus = document.getElementById('doc_status').value === 'Pending';
                    
                    if(pFix && changingStatus && (!remarks || remarks.value.trim() === '')) {
                        window.showToast('❌ กรุณาระบุเหตุผล/บันทึกการแก้ไข ก่อนส่งไปตรวจสอบใหม่', 'error');
                        if(remarks) remarks.classList.add('border-danger');
                        return;
                    }

                    const btn = this.querySelector('button[type="submit"]');
                    const originalText = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '⏳ กำลังบันทึก...';

                    const fd = new FormData(this);
                    
                    // เพิ่มไฟล์แยกตามหมวดหมู่
                    for (const fieldId in categoryUploads) {
                        categoryUploads[fieldId].forEach(item => {
                            if (item && item.file) {
                                fd.append(`${fieldId}[]`, item.file);
                            }
                        });
                    }

                    fetch('../backend/edit_handler.php', { method: 'POST', body: fd })
                    .then(res => res.text())
                    .then(text => {
                        if (text.includes('✅')) {
                            window.showToast('✅ บันทึกข้อมูลสำเร็จ', 'success');
                            setTimeout(() => { window.location.href = 'history.php'; }, 1500);
                        } else {
                            window.showToast('❌ ' + text.replace(/<[^>]*>/g, ''), 'error');
                            btn.disabled = false; btn.innerHTML = originalText;
                        }
                    })
                    .catch(() => {
                        window.showToast('❌ ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้', 'error');
                        btn.disabled = false; btn.innerHTML = originalText;
                    });
                });
            }

            calculateAll();
        });
    </script>
</body>
</html>