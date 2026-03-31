<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("<div style='padding:20px; color:red; text-align:center;'>❌ ไม่พบรหัสการเคลม กรุณากลับไปเลือกจากหน้าตรวจสอบ</div>");
}

try {
    $pdo = getServiceCenterPDO();
    $table = getServiceCenterTable();
    
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

    // ดึงรายการอะไหล่จากตารางแยก
    $stmtItems = $pdo->prepare("SELECT * FROM claim_items WHERE claim_id = ?");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();

    // จัดรูปแบบเลขเอกสาร (C001-280369)
    $idPart = "C" . str_pad($claim['id'], 3, '0', STR_PAD_LEFT);
    $datePart = "000000";
    if (!empty($claim['claim_date']) && $claim['claim_date'] !== '0000-00-00') {
        $timestamp = strtotime($claim['claim_date']);
        if ($timestamp !== false) {
            $buddhistYearShort = substr((date('Y', $timestamp) + 543), -2);
            $datePart = date('dm', $timestamp) . $buddhistYearShort;
        }
    }
    $doc_id = $idPart . "-" . $datePart;
    $claimDateFormatted = $claim['claim_date'] ? date('d/m/Y', strtotime($claim['claim_date'])) : '-';
    $updatedAtFormatted = !empty($claim['updated_at']) ? date('d/m/Y H:i', strtotime($claim['updated_at'])) : '-';

    // คำนวณอายุรถ
    $carAgeDisplay = '-';
    if (!empty($claim['sale_date'])) {
        $saleDate = new DateTime($claim['sale_date']);
        $now = new DateTime();
        $interval = $saleDate->diff($now);
        
        $parts = [];
        if ($interval->y > 0) $parts[] = $interval->y . " ปี";
        if ($interval->m > 0) $parts[] = $interval->m . " เดือน";
        if ($interval->d > 0) $parts[] = $interval->d . " วัน";
        $parts[] = $interval->h . " ชั่วโมง";
        
        $carAgeDisplay = implode(" ", $parts);
    }
    
    // แปลงประเภทรถ และการดำเนินการ
    $carTypeDisplay = $claim['car_type'] === 'new' ? 'รถใหม่' : ($claim['car_type'] === 'used' ? 'รถมือสอง' : $claim['car_type']);
    $claimCategoryDisplay = $claim['claim_category'] === 'pre-sale' ? 'เคลมรถก่อนขาย' : ($claim['claim_category'] === 'technical' ? 'เคลมปัญหาทางเทคนิค' : 'เคลมรถลูกค้า');
    
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
  <title>ตรวจสอบข้อมูลเคลม - <?= $doc_id ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles-edit_claim.css">
</head>
<body>

  <?php 
    $current_page = 'check.php';
    include __DIR__ . '/../shared/assets/includes/sidebar.php'; 
  ?>

  <div class="main-content">
    <div class="container-fluid p-0">
    
      <div class="filter-bar mb-4">
        <div class="row w-100 align-items-center g-3">
          <div class="col-12 col-md-6">
            <div class="fs-xl fw-600">ตรวจสอบและอนุมัติเคลม <span class="color-999 fw-normal">/ <?= $doc_id ?></span></div>
          </div>
          <div class="col-12 col-md-6 text-md-end">
            <div class="d-flex gap-2 justify-content-md-end">
              <a href="#verification-section" class="btn-action bg-primary-orange text-decoration-none px-3 py-1 color-fff rounded-3 shadow-sm">ไปยังส่วนอนุมัติ</a>
              <a href="check.php" class="btn-action bg-secondary text-decoration-none px-3 py-1 color-fff rounded-3 shadow-sm">ย้อนกลับ</a>
            </div>
          </div>
        </div>
      </div>

      <div class="edit-container mb-5">
        
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลเอกสาร</div>
          <div class="row g-4">
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">สาขา</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['branch']) ?>" readonly></div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">ประเภทการเคลม</label>
                  <div class="col-sm-8">
                    <div class="row g-2">
                       <div class="col-6"><input type="text" class="form-control bg-light border-0" value="<?= $claimCategoryDisplay ?>" readonly></div>
                       <div class="col-6"><input type="text" class="form-control bg-light border-0" value="<?= $carTypeDisplay ?>" readonly></div>
                    </div>
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">เลขที่เอกสาร</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= $doc_id ?>" readonly></div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">วันที่เอกสาร</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= $claimDateFormatted ?>" readonly></div>
                </div>
              </div>
            </div>
            
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center mb-3">
                  <label class="col-sm-4 col-form-label fw-600 color-555">ผู้บันทึกส่งเคลม</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['recorder_id']) ?>" readonly></div>
                </div>
                <div class="row align-items-center mb-3">
                  <label class="col-sm-4 col-form-label fw-600 color-555">ผู้แก้ไขครั้งล่าสุด</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['editor_id'] ?? 'ยังไม่มีการแก้ไข') ?>" readonly></div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600 color-555">วันที่แก้ไข</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= $updatedAtFormatted ?>" readonly></div>
                </div>
              </div>
            </div>
            <div class="col-12 mt-3">
                <div class="p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                  <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold color-primary-orange" >การดำเนินการ</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input act-radio" type="radio" name="claimAction" value="RepairBranch" id="act1" <?= $cType === 'RepairBranch' ? 'checked' : '' ?> disabled>
                                <label class="form-check-label" for="act1">ซ่อมที่สาขา</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input act-radio" type="radio" name="claimAction" value="SendHQ" id="act2" <?= $cType === 'SendHQ' ? 'checked' : '' ?> disabled>
                                <label class="form-check-label" for="act2">ส่งซ่อมที่สนญ.</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input act-radio" type="radio" name="claimAction" value="ReplaceVehicle" id="act3" <?= $cType === 'ReplaceVehicle' ? 'checked' : '' ?> disabled>
                                <label class="form-check-label" for="act3">เปลี่ยนคัน/อื่นๆ</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold color-primary-orange">ประเภทการส่งอะไหล่</label>
                        <div class="d-flex flex-wrap gap-3 mt-1" >
                            <?php 
                                $pd = $claim['parts_delivery'] ?? '';
                                $isOtherPD = !in_array($pd, ['', 'in_stock', 'wait_hq', 'buy_outside']);
                            ?>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_stock" value="in_stock" <?= $pd == 'in_stock' ? 'checked' : '' ?> disabled>
                              <label class="form-check-label" for="pd_stock">ซ่อมที่สาขา</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_hq" value="wait_hq" <?= $pd == 'wait_hq' ? 'checked' : '' ?> disabled>
                              <label class="form-check-label" for="pd_hq">รอส่งอะไหล่ จากสนญ.</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_buy" value="buy_outside" <?= $pd == 'buy_outside' ? 'checked' : '' ?> disabled>
                              <label class="form-check-label" for="pd_buy">ซื้ออะไหล่ร้านนอก</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_other" value="other" <?= ($isOtherPD && $pd != '') ? 'checked' : '' ?> disabled>
                              <label class="form-check-label" for="pd_other">อื่นๆ</label>
                            </div>
                        </div>
                        <input type="text" id="partsDeliveryOtherTextEdit" name="partsDeliveryOtherText" class="form-control mt-2 <?= ($isOtherPD && $pd != '') ? '' : 'd-none' ?>" value="<?= $isOtherPD ? htmlspecialchars($pd) : '' ?>" placeholder="ระบุการส่งอะไหล่แบบอื่นๆ" readonly>
                        
                        <?php if(!empty($claim['repair_app_name'])): ?>
                        <div class="mt-3 p-2 rounded" style="background:#fffcf0; border: 1px solid #ffeeba;">
                             <small class="fw-bold text-muted d-block mb-1">ผู้อนุมัติจากฟอร์มหลัก:</small>
                             <div class="d-flex gap-3">
                                 <code class="text-dark"><?= htmlspecialchars($claim['repair_app_id']) ?></code>
                                 <span class="fw-bold"><?= htmlspecialchars($claim['repair_app_name']) ?></span>
                                 <span class="text-muted fs-xs">Sig: <?= htmlspecialchars($claim['repair_app_sig']) ?></span>
                             </div>
                        </div>
                        <?php endif; ?>
                    </div>
                  </div>

                  <div class="replace-block mt-4 p-4 bg-white rounded-3 border shadow-sm <?= ($cType === 'ReplaceVehicle') ? 'd-block' : 'd-none' ?>" id="replaceBlock">
                      <div class="fw-bold mb-3 fs-5" style="color: #dc3545;">รายละเอียดการเปลี่ยนคันใหม่ :</div>
                      
                      <div class="row g-3 mb-3">
                          <div class="col-md-6">
                              <label class="form-label fw-600">รถคันเก่า : คงเหลือเงินดาวน์</label>
                              <div class="input-group">
                                  <input type="number" step="0.01" name="old_down_balance" class="form-control border-2" placeholder="0.00" value="<?= htmlspecialchars($claim['old_down_balance'] ?? '') ?>" readonly>
                                  <span class="input-group-text border-2">บาท</span>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label fw-600">รถคันใหม่ : คงเหลือเงินดาวน์</label>
                              <div class="input-group">
                                  <input type="number" step="0.01" name="new_down_balance" class="form-control border-2" placeholder="0.00" value="<?= htmlspecialchars($claim['new_down_balance'] ?? '') ?>" readonly>
                                  <span class="input-group-text border-2">บาท</span>
                              </div>
                          </div>
                      </div>

                      <div class="fw-bold mb-2 text-secondary mt-4">รายละเอียดรถคันใหม่</div>
                      <div class="row g-3 mb-3">
                          <div class="col-md-4">
                              <label class="form-label fw-600">ประเภทรถ</label>
                              <div class="d-flex gap-3 mt-2">
                                  <div class="form-check">
                                      <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repNew" value="new" <?= ($claim['rp_type'] ?? '') == 'new' ? 'checked' : '' ?> disabled>
                                      <label class="form-check-label" for="repNew">รถใหม่</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repUsed" value="used" <?= ($claim['rp_type'] ?? '') == 'used' ? 'checked' : '' ?> disabled>
                                      <label class="form-check-label" for="repUsed">รถมือสอง</label>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4 rep-grade-field <?= ($claim['rp_type'] ?? '') == 'used' ? '' : 'd-none' ?>" id="repGradeField">
                              <label class="form-label fw-600">เกรด</label>
                              <select name="replaceUsedGrade" class="form-select border-2" disabled>
                                  <option value="">-- เลือกเกรด --</option>
                                  <option value="A_premium" <?= ($claim['rp_used_grade'] ?? '') == 'A_premium' ? 'selected' : '' ?>>A พรีเมี่ยม</option>
                                  <option value="A_w6" <?= ($claim['rp_used_grade'] ?? '') == 'A_w6' ? 'selected' : '' ?>>A (ประกัน 6 ด.)</option>
                                  <option value="C_w1" <?= ($claim['rp_used_grade'] ?? '') == 'C_w1' ? 'selected' : '' ?>>C (ประกัน 1 ด.)</option>
                                  <option value="C_as_is" <?= ($claim['rp_used_grade'] ?? '') == 'C_as_is' ? 'selected' : '' ?>>C (ตามสภาพ)</option>
                              </select>
                          </div>
                          
                          <div class="col-md-4">
                              <label class="form-label fw-600">รุ่น</label>
                              <input type="text" name="replace_model" class="form-control border-2" placeholder="รุ่น" value="<?= htmlspecialchars($claim['rp_model'] ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-4">
                              <label class="form-label fw-600">สี</label>
                              <input type="text" name="replace_color" class="form-control border-2" placeholder="สี" value="<?= htmlspecialchars($claim['rp_color'] ?? '') ?>" readonly>
                          </div>

                          <div class="col-md-4">
                              <label class="form-label fw-600">เลขตัวถัง (คันใหม่)</label>
                              <input type="text" name="replace_vin" class="form-control border-2" placeholder="เลขตัวถัง / VIN" value="<?= htmlspecialchars($claim['replace_vin'] ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label fw-600">วันที่รับรถ</label>
                              <input type="date" name="replace_receive_date" class="form-control border-2" value="<?= !empty($claim['rp_receive_date']) ? date('Y-m-d', strtotime($claim['rp_receive_date'])) : '' ?>" readonly>
                          </div>
                      </div>

                      <div class="mb-3 mt-4">
                          <label class="form-label fw-600">สาเหตุที่เปลี่ยนคัน</label>
                          <textarea name="replace_reason" class="form-control border-2" rows="2" placeholder="ระบุสาเหตุการเปลี่ยนคัน" readonly><?= htmlspecialchars($claim['rp_reason'] ?? '') ?></textarea>
                      </div>

                      <div class="row g-3">
                          <div class="col-md-6">
                              <label class="form-label fw-600">ผู้อนุมัติ</label>
                              <input type="text" name="replace_approver" class="form-control border-2" placeholder="ชื่อผู้อนุมัติ" value="<?= htmlspecialchars($claim['rp_app_name'] ?? '') ?>" readonly>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label fw-600">วันที่อนุมัติ</label>
                              <input type="date" name="replace_approve_date" class="form-control border-2" value="<?= !empty($claim['rp_app_date']) ? date('Y-m-d', strtotime($claim['rp_app_date'])) : '' ?>" readonly>
                          </div>
                      </div>
                      
                      <div class="mt-4 p-3 bg-light rounded text-danger" style="font-size: 0.9rem;">
                          <strong>***หมายเหตุ : </strong><br>
                          1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้งที่มีการเปลี่ยน/ตัวจริงแนบมากับสัญญาส่งให้บัญชี<br>
                          2. สินเชื่อเช็คประกันรถหาย / ทะเบียนแก้ไข พ.ร.บ.-ทะเบียน / บริหารสต็อก ตัดแลกเปลี่ยน / ธุรการสินเชื่อ ตรวจรอบการเปิดขาย
                      </div>
                  </div>
                  </div>
              </div>
              
            </div>
          </div>
        
        
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลผู้ใช้ และข้อมูลรถ</div>
          <div class="row g-4">
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">ชื่อ-นามสกุล</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['ownerName']) ?>" readonly></div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">ยี่ห้อรถ</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['car_brand'] . (!empty($claim['used_grade']) ? ' / เกรด: ' . $claim['used_grade'] : '')) ?>" readonly>
                </div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">เลขไมล์รถ</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['mileage'] . ' กม.') ?>" readonly></div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">อายุการใช้งาน</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 fw-bold" value="<?= $carAgeDisplay ?>" readonly></div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['owner_phone'] ?? '-') ?>" readonly></div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">หมายเลขตัวถัง</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 fw-bold text-primary-orange" value="<?= htmlspecialchars($claim['vin']) ?>" readonly></div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">วันที่ขายรถ</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0" value="<?= !empty($claim['sale_date']) ? date('d/m/Y', strtotime($claim['sale_date'])) : '-' ?>" readonly></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ปัญหา</div>
          <div class="mb-4">
            <label class="form-label fw-600 mb-2">รายละเอียดปัญหาที่ลูกค้าแจ้ง</label>
            <textarea class="form-control bg-light border-0" rows="3" readonly><?= htmlspecialchars($claim['problem_desc']) ?></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-600 mb-2">วิธีการตรวจเช็ค</label>
              <textarea class="form-control bg-light border-0" rows="3" readonly><?= htmlspecialchars($claim['inspect_method']) ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-600 mb-2">สาเหตุของปัญหา</label>
              <textarea class="form-control bg-light border-0" rows="3" readonly><?= htmlspecialchars($claim['inspect_cause']) ?></textarea>
            </div>
          </div>
        </div>

        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">รูปภาพปัญหา</div>
          <div class="row g-3">
            <?php 
            $savedImgs = !empty($claim['claim_images']) ? json_decode($claim['claim_images'], true) : [];
            if(is_array($savedImgs) && count($savedImgs) > 0):
                foreach($savedImgs as $imgPath):
                    $fileName = basename($imgPath);
            ?>
                <div class="col-6 col-md-4 col-lg-3">
                   <div class="border rounded-3 p-2 text-center h-100 d-flex flex-column bg-white shadow-sm hover-overlay">
                       <a href="../<?= htmlspecialchars($imgPath) ?>" class="problem-image-link" data-bs-toggle="modal" data-bs-target="#problemImageModal">
                           <img src="../<?= htmlspecialchars($imgPath) ?>" alt="รูปภาพเคลม" class="img-fluid rounded-2 mb-2" style="height: 140px; width: 100%; object-fit: cover;">
                       </a>
                       <div class="mt-auto d-flex justify-content-between align-items-center">
                           <span class="text-truncate small text-muted d-inline-block" style="max-width: 100px;" title="<?= htmlspecialchars($fileName) ?>"><?= htmlspecialchars($fileName) ?></span>
                           <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= htmlspecialchars($fileName) ?>" class="btn btn-sm btn-success py-0 px-2" style="font-size: 12px;">โหลด ⬇️</a>
                       </div>
                   </div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <div class="col-12 text-center py-4 bg-light rounded-3 border border-dashed">
                  <p class="text-muted mb-0">ไม่ได้แนบรูปภาพปัญหามาในเอกสารนี้</p>
                </div>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="edit-card p-0 overflow-hidden mb-4 border-0 shadow-sm rounded-4">
            <div class="p-4">
              <div class="section-title mb-3 pb-2 border-bottom fw-bold fs-5">รายการอะไหล่</div>
              <div class="table-responsive">
                <table class="edit-table table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th width="40">#</th>
                      <th>รหัสสินค้า</th>
                      <th>ชื่อสินค้า</th>
                      <th width="120" class="text-center">ราคา/หน่วย</th>
                      <th width="90" class="text-center">จำนวน</th>
                      <th width="120" class="text-center">เป็นเงิน</th>
                      <th>หมายเหตุ</th>
                    </tr>
                  </thead>
                  <?php
                    $sumQty = 0;
                    $sumMoney = 0;
                  ?>
                  <tbody>
                    <tr class="group-header bg-light">
                      <td colspan="7" class="text-danger fw-bold py-3 ps-3">รายการอะไหล่ทั้งหมด</td>
                    </tr>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $idx => $part):
                            $qty = floatval($part['quantity'] ?? 0);
                            $price = floatval($part['unit_price'] ?? 0);
                            $total = $qty * $price;
                            $sumQty += $qty;
                            $sumMoney += $total;
                        ?>
                        <tr class="part-row">
                          <td><?= $idx + 1 ?></td>
                          <td><?= htmlspecialchars($part['part_code'] ?? '') ?></td>
                          <td><?= htmlspecialchars($part['part_name'] ?? '') ?></td>
                          <td class="text-center"><?= number_format($price, 2) ?></td>
                          <td class="text-center"><?= $qty ?></td>
                          <td class="text-center fw-600"><?= number_format($total, 2) ?></td>
                          <td><?= htmlspecialchars($part['note'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted">ไม่มีรายการอะไหล่</td></tr>
                    <?php endif; ?>
                  </tbody>
                  <tbody>
                    <tr class="summary-row fw-bold bg-light">
                      <td colspan="4" class="py-3 ps-4 text-end">รวมยอดอะไหล่สุทธิ</td>
                      <td class="text-center text-primary-orange"><?= $sumQty ?></td>
                      <td class="text-center text-primary-orange"><span id="total-parts-cost"><?= number_format($sumMoney, 2, '.', '') ?></span> บาท</td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
        </div>

        <div class="edit-card p-4 border-0 shadow-sm rounded-4 mb-4" style="background-color: #fbfbfb;">
            <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">สรุปค่าแรงและจำนวนเงิน (ตรวจสอบ)</div>
            <div class="row g-4">
              <div class="col-12 col-lg-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                  <div class="d-flex flex-column gap-3">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">จำนวน FRT</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="number" class="form-control bg-light border-0" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">ชม.</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">FRT. Rate/hr</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="number" class="form-control bg-light border-0" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">รวมค่าแรง</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control bg-light border-0 fw-bold" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">รวมค่าอะไหล่</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control bg-light border-0 fw-bold" value="<?= number_format($sumMoney, 2) ?>" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                  <div class="d-flex flex-column gap-3">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">อัตราค่าการจัดการ</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="number" class="form-control bg-light border-0" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">%</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">ค่าการจัดการ</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control bg-light border-0 fw-bold" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">ค่าใช้จ่ายอื่นๆ</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="number" class="form-control bg-light border-0" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-700 text-primary-orange fs-5">รวมเงินเคลมสุทธิ</label>
                      <div class="col-sm-7">
                        <div class="input-group shadow-sm">
                          <input type="text" class="form-control bg-light border-2 border-primary-orange text-primary-orange fw-bold fs-5 py-2" value="<?= number_format($sumMoney, 2) ?>" readonly>
                          <span class="input-group-text border-2 border-primary-orange bg-primary-orange color-fff fw-bold">บาท</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>

        <form id="verifyForm" method="POST" action="../backend/verify_handler.php">
            <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">
            
            <div class="edit-card border-0 shadow-sm rounded-4 p-4" id="verification-section" style="border: 2px solid var(--primary-orange) !important;">
            <div class="section-title verification-title d-flex align-items-center mb-4 pb-2 border-bottom fw-bold fs-5 color-primary-orange">
                <svg class="me-2" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                บันทึกผลการตรวจสอบและอนุมัติ
            </div>
            
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="p-4 bg-light rounded-4 shadow-sm h-100 border border-secondary border-opacity-10">
                    <label class="form-label fw-bold mb-3 border-bottom pb-2 w-100 fs-6">รายการตรวจสอบเบื้องต้น</label>
                    <div class="d-flex flex-column gap-3">
                        <div class="form-check custom-checkbox-lg">
                        <input class="form-check-input border-2" type="checkbox" id="check1" style="width: 22px; height: 22px;">
                        <label class="form-check-label fs-md ms-2 pt-1 cursor-pointer" for="check1">ข้อมูลลูกค้าและรถถูกต้อง</label>
                        </div>
                        <div class="form-check custom-checkbox-lg">
                        <input class="form-check-input border-2" type="checkbox" id="check2" style="width: 22px; height: 22px;">
                        <label class="form-check-label fs-md ms-2 pt-1 cursor-pointer" for="check2">เหตุผลการเคลมชัดเจน สมเหตุสมผล</label>
                        </div>
                        <div class="form-check custom-checkbox-lg">
                        <input class="form-check-input border-2" type="checkbox" id="check3" style="width: 22px; height: 22px;">
                        <label class="form-check-label fs-md ms-2 pt-1 cursor-pointer" for="check3">รายการอะไหล่ถูกต้อง</label>
                        </div>
                    </div>
                    </div>
                </div>
                
                <div class="col-12 col-lg-6">
                    <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-secondary border-opacity-10">
                    <div class="row align-items-center mb-3">
                        <label class="col-sm-4 col-form-label fw-bold text-dark">ผลพิจารณา <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                        <select name="status" class="form-select border-primary-orange border-2 fw-bold text-primary-orange" required>
                            <option value="">-- กรุณาเลือกผลการตรวจสอบ --</option>
                            <option value="Approved" <?= $claim['status'] == 'Approved' ? 'selected' : '' ?> class="text-success">อนุมัติการเคลม</option>
                            <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?> class="text-danger">ไม่อนุมัติ</option>
                            <option value="Pending" <?= $claim['status'] == 'Pending' ? 'selected' : '' ?> class="text-warning">ตีกลับไปแก้ไข / รอตรวจสอบ</option>
                        </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold mb-2">หมายเหตุ / ความเห็นผู้ตรวจสอบ</label>
                        <textarea name="verify_remarks" class="form-control border-2" rows="3" placeholder="ระบุเหตุผล หรือข้อเสนอแนะ..."><?= htmlspecialchars($claim['verify_remarks'] ?? '') ?></textarea>
                    </div>
                    </div>
                </div>
            </div>
            
            <div class="row g-4 border-top pt-4 mt-2">
                <div class="col-12 col-lg-6">
                <div class="row align-items-center mb-3">
                    <label class="col-sm-4 col-form-label fw-600">ผู้ตรวจสอบ <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" name="verifier" class="form-control border-2" placeholder="ลงชื่อผู้ตรวจสอบ (เช่น Admin)" value="<?= htmlspecialchars($claim['verifier'] ?? '') ?>" required>
                    </div>
                </div>
                </div>
                
                <div class="col-12 col-lg-6 d-flex flex-column flex-sm-row justify-content-end align-items-stretch align-items-sm-end gap-3 mt-4 mt-lg-0">
                    <a href="check.php" class="btn btn-secondary px-5 py-2 rounded-3 shadow-sm text-decoration-none text-center color-fff">ยกเลิก / กลับ</a>
                    <button type="submit" id="btnSubmitVerify" class="btn-action bg-primary-orange color-fff border-0 px-5 py-2 rounded-3 shadow-sm fw-bold">บันทึกผลการตรวจสอบ</button>
                </div>
            </div>
            </div>
        </form>
        
      </div> 
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <div id="lightbox" class="lightbox" aria-hidden="true">
    <div class="imgwrap">
      <button class="close" aria-label="ปิด">✕</button>
      <button class="nav prev" aria-label="ก่อนหน้า">‹</button>
      <div class="imgframe"><img src="" alt="preview"><div class="counter" aria-hidden="true"></div></div>
      <button class="nav next" aria-label="ถัดไป">›</button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageLinks = Array.from(document.querySelectorAll('.problem-image-link'));
        const lightboxHtml = document.getElementById('lightbox');
        const lightboxImage = lightboxHtml?.querySelector('.imgframe img');
        const lightboxCounter = lightboxHtml?.querySelector('.counter');
        let lightboxState = { index: 0, images: imageLinks.map(link => link.getAttribute('href')) };

        imageLinks.forEach((link, idx) => {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                lightboxState.index = idx;
                if (lightboxImage) lightboxImage.src = this.getAttribute('href');
                if (lightboxCounter) lightboxCounter.textContent = (idx + 1) + ' / ' + lightboxState.images.length;
                if (lightboxHtml) {
                    lightboxHtml.classList.add('open');
                    lightboxHtml.setAttribute('aria-hidden', 'false');
                }
            });
        });

        function closeLightbox() {
            if (!lightboxHtml) return;
            lightboxHtml.classList.remove('open');
            lightboxHtml.setAttribute('aria-hidden', 'true');
        }

        function showLightboxIndex(index) {
            if (!lightboxImage || !lightboxCounter || !lightboxHtml) return;
            lightboxState.index = (index + lightboxState.images.length) % lightboxState.images.length;
            lightboxImage.src = lightboxState.images[lightboxState.index];
            lightboxCounter.textContent = (lightboxState.index + 1) + ' / ' + lightboxState.images.length;
            lightboxHtml.classList.add('open');
            lightboxHtml.setAttribute('aria-hidden', 'false');
        }

        lightboxHtml?.addEventListener('click', function(e) {
            if (e.target.id === 'lightbox' || e.target.classList.contains('close')) {
                closeLightbox();
            }
        });
        lightboxHtml?.querySelector('.nav.next')?.addEventListener('click', function(e) {
            e.stopPropagation();
            showLightboxIndex(lightboxState.index + 1);
        });
        lightboxHtml?.querySelector('.nav.prev')?.addEventListener('click', function(e) {
            e.stopPropagation();
            showLightboxIndex(lightboxState.index - 1);
        });
        document.addEventListener('keydown', function(e) {
            if (!lightboxHtml?.classList.contains('open')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') showLightboxIndex(lightboxState.index + 1);
            if (e.key === 'ArrowLeft') showLightboxIndex(lightboxState.index - 1);
        });

        const verifyForm = document.getElementById('verifyForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = document.getElementById('btnSubmitVerify');
                const originalText = submitBtn ? submitBtn.innerHTML : 'บันทึกผลการตรวจสอบ';
                if (submitBtn) { submitBtn.innerHTML = '⏳ กำลังบันทึก...'; submitBtn.disabled = true; }

                fetch('../backend/verify_handler.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.text())
                .then(text => {
                    if (text.includes('✅')) {
                        alert('✅ บันทึกผลการตรวจสอบเรียบร้อยแล้ว!');
                        window.location.href = 'check.php'; 
                    } else {
                        alert('❌ เกิดข้อผิดพลาด:\n' + text.replace(/(<([^>]+)>)/gi, "")); 
                        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
                    }
                })
                .catch(err => {
                    alert('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ!');
                    if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
                });
            });
        }
    });
  </script>
</body>
</html>