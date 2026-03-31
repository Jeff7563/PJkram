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
               rd.parts_delivery, rd.approver_id as repair_app_id, rd.approver_name as repair_app_name, rd.approver_signature as repair_app_sig,
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
    <style>
        .search-dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            z-index: 1000;
            max-height: 300px;
            overflow-y: auto;
            display: none;
        }
        .search-dropdown-list.show {
            display: block;
        }
        .dropdown-item:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }
        .cursor-pointer { cursor: pointer; }
        .btn-outline-orange {
            color: #ff6b00;
            border-color: #ff6b00;
        }
        .btn-outline-orange:hover {
            background-color: #ff6b00;
            color: white;
        }
        .border-dashed {
            border-style: dashed !important;
        }
    </style>
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
                                        <label class="col-sm-4 col-form-label fw-600">ประเภทงาน</label>
                                        <div class="col-sm-8">
                                            <select name="claimCategory" class="form-select border-2" required>
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
                                            <input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['recorder_id']) ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <label class="col-sm-4 col-form-label fw-600 color-555">วันที่บันทึก</label>
                                        <div class="col-sm-8">
                                            <input type="text" class="form-control bg-light border-0" value="<?= $claimDateFormatted ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ส่วนการเลือกประเภทการดำเนินการ -->
                        <div class="mt-4 p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold color-primary-orange">การดำเนินการ</label>
                                    <div class="d-flex flex-wrap gap-3 mt-1">
                                        <div class="form-check">
                                            <input class="form-check-input act-radio" type="radio" name="claimAction" value="RepairBranch" id="act1" <?= $cType === 'RepairBranch' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="act1">ซ่อมที่สาขา</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input act-radio" type="radio" name="claimAction" value="SendHQ" id="act2" <?= $cType === 'SendHQ' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="act2">ส่งซ่อมที่สนญ.</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input act-radio" type="radio" name="claimAction" value="ReplaceVehicle" id="act3" <?= $cType === 'ReplaceVehicle' ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="act3">เปลี่ยนคันใหม่</label>
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
                            <div id="repairApproverSection" class="mt-4 p-3 rounded-3 border bg-white shadow-sm <?= ($cType === 'RepairBranch' || $cType === 'SendHQ') ? '' : 'd-none' ?>">
                                <div class="fw-bold mb-3 text-primary-orange"><i class="fas fa-signature me-1"></i> ผู้อนุมัติการซ่อม :</div>
                                <div class="row g-3">
                                    <div class="col-md-12 position-relative">
                                        <label class="form-label fw-600">ค้นหาพนักงาน <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" id="search-approver-repair" class="form-control" placeholder="พิมพ์ชื่อพนักงาน..." value="<?= htmlspecialchars($claim['repair_app_name'] ?? '') ?>">
                                        </div>
                                        <div id="repair-dropdown-list" class="search-dropdown-list shadow-lg"></div>
                                        <input type="hidden" name="repair_id" id="repair_id" value="<?= htmlspecialchars($claim['repair_app_id'] ?? '') ?>">
                                        <input type="hidden" name="repair_name" id="repair_name" value="<?= htmlspecialchars($claim['repair_app_name'] ?? '') ?>">
                                        <input type="hidden" name="repair_signature" id="repair_signature" value="<?= htmlspecialchars($claim['repair_app_sig'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- ส่วนรายละเอียดขอเปลี่ยนคันใหม่ -->
                            <div id="replaceBlock" class="mt-4 p-3 rounded-3 border bg-white shadow-sm <?= ($cType === 'ReplaceVehicle') ? '' : 'd-none' ?>">
                                <div class="fw-bold mb-3 fs-5" style="color: #dc3545;"><i class="fas fa-exchange-alt me-1"></i> รายละเอียดการเปลี่ยนคันใหม่ :</div>
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">รถคันเก่า : คงเหลือเงินดาวน์</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="old_down_balance" class="form-control border-2" placeholder="0.00" value="<?= htmlspecialchars($claim['old_down_balance'] ?? '') ?>">
                                            <span class="input-group-text">บาท</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">รถคันใหม่ : คงเหลือเงินดาวน์</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" name="new_down_balance" class="form-control border-2" placeholder="0.00" value="<?= htmlspecialchars($claim['new_down_balance'] ?? '') ?>">
                                            <span class="input-group-text">บาท</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="fw-bold mb-2 text-secondary mt-3">รายละเอียดรถคันใหม่</div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-600">ประเภทรถ</label>
                                        <div class="d-flex gap-3 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repNew" value="new" <?= ($claim['rp_type'] ?? '') == 'new' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="repNew">รถใหม่</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repUsed" value="used" <?= ($claim['rp_type'] ?? '') == 'used' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="repUsed">รถมือสอง</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 rep-grade-field <?= ($claim['rp_type'] ?? '') == 'used' ? '' : 'd-none' ?>" id="repGradeField">
                                        <label class="form-label fw-600">เกรด</label>
                                        <select name="replaceUsedGrade" class="form-select border-2">
                                            <option value="">-- เลือกเกรด --</option>
                                            <option value="A_premium" <?= ($claim['rp_used_grade'] ?? '') == 'A_premium' ? 'selected' : '' ?>>A พรีเมี่ยม</option>
                                            <option value="A_w6" <?= ($claim['rp_used_grade'] ?? '') == 'A_w6' ? 'selected' : '' ?>>A (ประกัน 6 ด.)</option>
                                            <option value="C_w1" <?= ($claim['rp_used_grade'] ?? '') == 'C_w1' ? 'selected' : '' ?>>C (ประกัน 1 ด.)</option>
                                            <option value="C_as_is" <?= ($claim['rp_used_grade'] ?? '') == 'C_as_is' ? 'selected' : '' ?>>C (ตามสภาพ)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-600">รุ่น / สี</label>
                                        <div class="input-group">
                                            <input type="text" name="replace_model" class="form-control" placeholder="รุ่น" value="<?= htmlspecialchars($claim['rp_model'] ?? '') ?>">
                                            <input type="text" name="replace_color" class="form-control" placeholder="สี" value="<?= htmlspecialchars($claim['rp_color'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-600">เลขตัวถัง (คันใหม่)</label>
                                        <input type="text" name="replace_vin" class="form-control" placeholder="เลขตัวถัง / VIN" value="<?= htmlspecialchars($claim['replace_vin'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-600">วันที่รับรถ</label>
                                        <input type="date" name="replace_receive_date" class="form-control" value="<?= !empty($claim['rp_receive_date']) ? date('Y-m-d', strtotime($claim['rp_receive_date'])) : '' ?>">
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <label class="form-label fw-600">สาเหตุที่เปลี่ยนคัน</label>
                                    <textarea name="replace_reason" class="form-control" rows="2"><?= htmlspecialchars($claim['rp_reason'] ?? '') ?></textarea>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6 position-relative">
                                        <label class="form-label fw-600">ผู้อนุมัติ <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                                            <input type="text" id="search-approver-replace" class="form-control" placeholder="ค้นหาชื่อผู้อนุมัติ..." value="<?= htmlspecialchars($claim['rp_app_name'] ?? '') ?>">
                                        </div>
                                        <div id="replace-dropdown-list" class="search-dropdown-list shadow-lg"></div>
                                        <input type="hidden" name="replace_id" id="replace_id" value="<?= htmlspecialchars($claim['rp_app_id'] ?? '') ?>">
                                        <input type="hidden" name="replace_name" id="replace_name" value="<?= htmlspecialchars($claim['rp_app_name'] ?? '') ?>">
                                        <input type="hidden" name="replace_signature" id="replace_signature" value="<?= htmlspecialchars($claim['rp_app_sig'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-600">วันที่อนุมัติ</label>
                                        <input type="date" name="replace_approve_date" class="form-control" value="<?= !empty($claim['rp_app_date']) ? date('Y-m-d', strtotime($claim['rp_app_date'])) : '' ?>">
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
                                        <input type="text" id="vehicle_age" class="form-control bg-light border-0 fw-bold" readonly placeholder="คำนวณอัตโนมัติ">
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
                                $savedImgs = !empty($claim['claim_images']) ? json_decode($claim['claim_images'], true) : [];
                                if(is_array($savedImgs)):
                                    foreach($savedImgs as $idx => $imgPath): 
                                        $fileName = basename($imgPath);
                                ?>
                                    <div class="gallery-item existing-item">
                                        <input type="hidden" name="existing_images[]" value="<?= htmlspecialchars($imgPath) ?>">
                                        <img src="../<?= htmlspecialchars($imgPath) ?>" class="preview-img cursor-pointer" style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
                                        <div class="img-preview-footer mt-1 d-flex justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-danger btn-remove-existing" style="font-size:10px; padding:2px 8px;">❌</button>
                                        </div>
                                    </div>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- รายการอะไหล่ -->
                    <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4 overflow-hidden">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5 d-flex justify-content-between">
                            <span>รายการอะไหล่</span>
                            <div class="parts-image-upload">
                                <button type="button" id="btnUploadParts" class="btn btn-sm btn-success">📸 รูปภาพอะไหล่</button>
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
                        <div id="partsImgPreview" class="d-flex flex-wrap gap-2 mt-3"></div>
                    </div>

                    <!-- ค่าแรงและสรุปเงิน -->
                    <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4 bg-light">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ค่าแรงและสรุปยอดคงเหลือ</div>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="bg-white p-3 rounded-3 shadow-sm h-100">
                                    <div class="row align-items-center mb-3">
                                        <label class="col-5 fw-600">จำนวน FRT</label>
                                        <div class="col-7">
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" class="form-control" id="labor-frt" value="0.00">
                                                <span class="input-group-text">ชม.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <label class="col-5 fw-600">Rate/hr</label>
                                        <div class="col-7">
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" class="form-control" id="labor-rate" value="0.00">
                                                <span class="input-group-text">บาท</span>
                                            </div>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row align-items-center">
                                        <label class="col-5 fw-600">รวมค่าแรง</label>
                                        <div class="col-7 text-end fw-bold" id="labor-total-display">0.00</div>
                                        <input type="hidden" id="labor-total" value="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-3 rounded-3 shadow-sm h-100">
                                    <div class="row align-items-center mb-3">
                                        <label class="col-5 fw-600">รวมค่าอะไหล่</label>
                                        <div class="col-7 text-end fw-bold" id="parts-total-display"><?= number_format($totalMoney, 2) ?></div>
                                        <input type="hidden" id="labor-parts-total" value="<?= $totalMoney ?>">
                                    </div>
                                    <div class="row align-items-center mb-3">
                                        <label class="col-5 fw-600">ค่าจัดการ (%)</label>
                                        <div class="col-3">
                                            <input type="number" step="0.1" class="form-control form-control-sm" id="manage-pct" value="0.0">
                                        </div>
                                        <div class="col-4 text-end fw-bold" id="manage-fee-display">0.00</div>
                                        <input type="hidden" id="manage-fee" value="0.00">
                                    </div>
                                    <div class="row align-items-center mb-3 text-primary-orange fs-5 fw-bold">
                                        <label class="col-5">รวมเงินทั้งสิ้น</label>
                                        <div class="col-7 text-end" id="grand-total-display"><?= number_format($totalMoney, 2) ?></div>
                                        <input type="hidden" id="grand-total" value="<?= $totalMoney ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- สถานะและการลงชื่อ -->
                    <div class="edit-card border-0 shadow-sm rounded-4 p-4" id="verification-section">
                        <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5 text-primary-orange">สถานะและบันทึกการแก้ไข</div>
                        <div class="row g-4 border-top pt-4">
                            <div class="col-12 col-lg-6">
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 fw-600">สถานะเอกสาร :</label>
                                    <div class="col-sm-8">
                                        <select name="status" class="form-select fw-bold border-2">
                                            <option value="Pending" <?= $claim['status'] == 'Pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                                            <option value="Approved" <?= $claim['status'] == 'Approved' ? 'selected' : '' ?>>อนุมัติ</option>
                                            <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>ปฏิเสธ</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-3">
                                    <label class="col-sm-4 fw-600">ลงชื่อผู้แก้ไข <span class="text-danger">*</span></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="editor" class="form-control border-2" placeholder="ชื่อ-นามสกุล ผู้แก้ไขปัจจุบัน" required>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <label class="form-label fw-600">หมายเหตุการแก้ไข</label>
                                <textarea name="remarks" class="form-control border-2" rows="2" placeholder="ระบุรายละเอียดการเปลี่ยนแปลง (ถ้ามี)"></textarea>
                                <div class="text-end mt-4">
                                    <a href="history.php" class="btn btn-secondary px-4">ยกเลิก</a>
                                    <button type="submit" class="btn btn-primary-orange px-5 text-white fw-bold">บันทึกข้อมูล</button>
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
                            <img src="${evt.target.result}" class="preview-img cursor-pointer" style="width:100%; height:120px; object-fit:cover; border-radius:8px;">
                            <div class="img-preview-footer mt-1 d-flex justify-content-center">
                                <button type="button" class="btn btn-sm btn-danger btn-remove-new" data-idx="${fileIdx}" style="font-size:10px; padding:2px 8px;">❌</button>
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

            // --- จัดการการแสดงผลตาม Claim Type ---
            const repairSection = document.getElementById('repairApproverSection');
            const deliverySection = document.getElementById('repairDeliverySection');
            const replaceBlock = document.getElementById('replaceBlock');
            document.querySelectorAll('.act-radio').forEach(r => {
                r.addEventListener('change', function() {
                    const val = this.value;
                    if (val === 'ReplaceVehicle') {
                        replaceBlock.classList.remove('d-none');
                        repairSection.classList.add('d-none');
                        deliverySection.classList.add('d-none');
                    } else {
                        replaceBlock.classList.add('d-none');
                        repairSection.classList.remove('d-none');
                        deliverySection.classList.remove('d-none');
                    }
                });
            });

            document.querySelectorAll('.rep-car-type').forEach(r => {
                r.addEventListener('change', function() {
                    document.getElementById('repGradeField').style.display = (this.value === 'used') ? 'block' : 'none';
                });
            });

            // --- ระบบค้นหาพนักงาน ---
            let employees = [];
            fetch('../backend/api_users.php').then(res => res.json()).then(data => { employees = data; });

            function setupSearch(inputId, listId, targetIds) {
                const input = document.getElementById(inputId);
                const list = document.getElementById(listId);
                if (!input || !list) return;
                input.addEventListener('input', function() {
                    const val = this.value.trim().toLowerCase();
                    list.innerHTML = '';
                    if (!val) { list.classList.remove('show'); return; }
                    const filtered = employees.filter(e => e.name.toLowerCase().includes(val) || e.emp_id.toLowerCase().includes(val));
                    if (filtered.length > 0) {
                        filtered.forEach(emp => {
                            const item = document.createElement('div');
                            item.className = 'dropdown-item p-2';
                            item.innerHTML = `<strong>${emp.emp_id}</strong> - ${emp.name}`;
                            item.addEventListener('click', () => {
                                input.value = emp.name;
                                document.getElementById(targetIds.id).value = emp.emp_id;
                                document.getElementById(targetIds.name).value = emp.name;
                                document.getElementById(targetIds.sig).value = emp.signature_path || '';
                                list.classList.remove('show');
                            });
                            list.appendChild(item);
                        });
                        list.classList.add('show');
                    }
                });
                document.addEventListener('click', (e) => { if (!input.contains(e.target)) list.classList.remove('show'); });
            }
            setupSearch('search-approver-repair', 'repair-dropdown-list', {id: 'repair_id', name: 'repair_name', sig: 'repair_signature'});
            setupSearch('search-approver-replace', 'replace-dropdown-list', {id: 'replace_id', name: 'replace_name', sig: 'replace_signature'});

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
                
                // สรุปยอดด้านล่าง
                document.getElementById('parts-total-display').textContent = sPrice.toLocaleString(undefined, {minimumFractionDigits: 2});
                document.getElementById('labor-parts-total').value = sPrice.toFixed(2);

                const frt = parseFloat(document.getElementById('labor-frt').value) || 0;
                const rate = parseFloat(document.getElementById('labor-rate').value) || 0;
                const laborTotal = frt * rate;
                document.getElementById('labor-total-display').textContent = laborTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
                document.getElementById('labor-total').value = laborTotal.toFixed(2);

                const pct = parseFloat(document.getElementById('manage-pct').value) || 0;
                const manageFee = sPrice * (pct / 100);
                document.getElementById('manage-fee-display').textContent = manageFee.toLocaleString(undefined, {minimumFractionDigits: 2});
                document.getElementById('manage-fee').value = manageFee.toFixed(2);

                const grandTotal = laborTotal + sPrice + manageFee;
                document.getElementById('grand-total-display').textContent = grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
                document.getElementById('grand-total').value = grandTotal.toFixed(2);
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

            ['labor-frt', 'labor-rate', 'manage-pct'].forEach(id => {
                document.getElementById(id).addEventListener('input', calculateAll);
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
                        wrap.style.cssText = 'position:relative; width:80px; height:80px; border-radius:5px; overflow:hidden; border:1px solid #ddd;';
                        wrap.innerHTML = `<img src="${evt.target.result}" style="width:100%; height:100%; object-fit:cover;"><div style="position:absolute; top:0; right:0; background:red; color:white; padding:0 5px; cursor:pointer;" class="rm-p-img">×</div>`;
                        wrap.querySelector('.rm-p-img').onclick = () => { wrap.remove(); partsFiles[partsFiles.indexOf(f)] = null; };
                        document.getElementById('partsImgPreview').appendChild(wrap);
                    };
                    reader.readAsDataURL(f);
                }
            });
            document.getElementById('btnUploadParts').onclick = () => document.getElementById('imgPartsUpload').click();

            // --- ส่งฟอร์ม (AJAX) ---
            document.querySelector('form').addEventListener('submit', function(e) {
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