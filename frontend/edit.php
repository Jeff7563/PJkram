<?php
require_once __DIR__ . '/../shared/config/db_connect.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("<div style='padding:20px; color:red; text-align:center;'>❌ ไม่พบรหัสการเคลม กรุณากลับไปเลือกจากหน้าประวัติ</div>");
}

try {
    $pdo = getServiceCenterPDO();
    $table = getServiceCenterTable();
    
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE id = ?");
    $stmt->execute([$id]);
    $claim = $stmt->fetch();

    if (!$claim) {
        die("<div style='padding:20px; color:red; text-align:center;'>❌ ไม่พบข้อมูลการเคลมเลขที่ $id ในระบบ</div>");
    }

    $idPart = "C" . str_pad($claim['id'], 3, '0', STR_PAD_LEFT);
    $datePart = "000000";
    $claimDateFormatted = '-'; 

    if (!empty($claim['claimDate']) && $claim['claimDate'] !== '0000-00-00') {
        $timestamp = strtotime($claim['claimDate']);
        if ($timestamp !== false) {
            $buddhistYearShort = substr((date('Y', $timestamp) + 543), -2);
            $datePart = date('dm', $timestamp) . $buddhistYearShort;
            $claimDateFormatted = date('d/m/Y', $timestamp);
        }
    }
    $doc_id = $idPart . "-" . $datePart;
    $partsArray = json_decode($claim['parts'], true) ?: [];

    $carTypeDisplay = $claim['carType'] === 'new' ? 'รถใหม่' : ($claim['carType'] === 'used' ? 'รถมือสอง' : $claim['carType']);
    $claimCategoryDisplay = $claim['claimCategory'] === 'pre-sale' ? 'เคลมรถก่อนขาย' : ($claim['claimCategory'] === 'technical' ? 'เคลมปัญหาทางเทคนิค' : 'เคลมรถลูกค้า');

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
        <input type="hidden" name="claimDate" value="<?= $claim['claimDate'] ?>">
        <input type="hidden" name="carType" value="<?= $claim['carType'] ?>">
        <input type="hidden" name="carBrand" value="<?= $claim['carBrand'] ?>">

        <div class="edit-container mb-5">
          
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
                    <div class="row g-2">
                      <select id="claimCategory" name="claimCategory" class="col-6 form-select" required>
                        <option value="เคลมรถก่อนขาย" <?= $claim['claimCategory'] == 'เคลมรถก่อนขาย' ? 'selected' : '' ?>>เคลมรถก่อนขาย</option>
                        <option value="เคลมปัญหาทางเทคนิค" <?= $claim['claimCategory'] == 'เคลมปัญหาทางเทคนิค' ? 'selected' : '' ?>>เคลมปัญหาทางเทคนิค</option>
                        <option value="เคลมรถลูกค้า" <?= $claim['claimCategory'] == 'เคลมรถลูกค้า' ? 'selected' : '' ?>>เคลมรถลูกค้า</option>
                      </select>
                    </div>
                  </div>
                </div>
                  <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-600">เลขที่เอกสาร</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control bg-light border-0" value="<?= $doc_id ?>" readonly>
                    </div>
                  </div>
                  <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-600">วันที่เอกสาร</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control bg-light border-0" value="<?= $claimDateFormatted ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-12 col-lg-6">
                <div class="d-flex flex-column gap-3">
                  <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-600 color-555">ผู้บันทึกส่งเคลม</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control bg-light border-0 text-primary-orange fw-bold" value="<?= htmlspecialchars($claim['recorder']) ?>" readonly>
                    </div>
                  </div>
                  <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-600 color-555">ผู้แก้ไขครั้งล่าสุด</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control bg-light border-0" value="<?= htmlspecialchars($claim['editor'] ?? 'ยังไม่มีการแก้ไข') ?>" readonly>
                    </div>
                  </div>
                  <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-600 color-555">วันที่แก้ไขล่าสุด</label>
                    <div class="col-sm-8">
                      <input type="text" class="form-control bg-light border-0" value="<?= !empty($claim['updated_at']) ? date('d/m/Y H:i', strtotime($claim['updated_at'])) : '-' ?>" readonly>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-12 mt-3">
                <div class="p-3 rounded-3" style="background-color: #f8f9fa; border: 1px solid #e9ecef;">
                  <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold color-primary-orange">การดำเนินการ</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <div class="form-check">
                                <input class="form-check-input act-radio" type="radio" name="claimAction" value="repairBranch" id="act1" <?= ($claim['repairBranch'] ?? 0) == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="act1">ซ่อมที่สาขา</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input act-radio" type="radio" name="claimAction" value="sendHQ" id="act2" <?= ($claim['sendHQ'] ?? 0) == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="act2">ส่งซ่อมที่สนญ.</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input act-radio" type="radio" name="claimAction" value="replaceVehicle" id="act3" <?= ($claim['otherAction'] ?? 0) == 1 || !empty($claim['replaceType']) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="act3">เปลี่ยนคัน/อื่นๆ</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold color-primary-orange">ประเภทการส่งอะไหล่</label>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            <?php 
                                $pd = $claim['partsDelivery'] ?? '';
                                $isOtherPD = !in_array($pd, ['', 'in_stock', 'wait_hq', 'buy_outside']);
                            ?>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_stock" value="in_stock" <?= $pd == 'in_stock' ? 'checked' : '' ?>>
                              <label class="form-check-label" for="pd_stock">ซ่อมที่สาขา</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_hq" value="wait_hq" <?= $pd == 'wait_hq' ? 'checked' : '' ?>>
                              <label class="form-check-label" for="pd_hq">รอส่งอะไหล่ จากสนญ.</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_buy" value="buy_outside" <?= $pd == 'buy_outside' ? 'checked' : '' ?>>
                              <label class="form-check-label" for="pd_buy">ซื้ออะไหล่ร้านนอก</label>
                            </div>
                            <div class="form-check">
                              <input class="form-check-input pd-radio" type="radio" name="partsDelivery" id="pd_other" value="other" <?= ($isOtherPD && $pd != '') ? 'checked' : '' ?>>
                              <label class="form-check-label" for="pd_other">อื่นๆ</label>
                            </div>
                        </div>
                        <input type="text" id="partsDeliveryOtherTextEdit" name="partsDeliveryOtherText" class="form-control mt-2 <?= ($isOtherPD && $pd != '') ? '' : 'd-none' ?>" value="<?= $isOtherPD ? htmlspecialchars($pd) : '' ?>" placeholder="ระบุการส่งอะไหล่แบบอื่นๆ">
                    </div>
                  </div>

                  <div class="replace-block mt-4 p-4 bg-white rounded-3 border shadow-sm <?= (!empty($claim['replaceType']) || ($claim['otherAction'] ?? 0) == 1) ? 'd-block' : 'd-none' ?>" id="replaceBlock">
                      <div class="fw-bold mb-3 fs-5" style="color: #dc3545;">รายละเอียดการเปลี่ยนคันใหม่ :</div>
                      
                      <div class="row g-3 mb-3">
                          <div class="col-md-6">
                              <label class="form-label fw-600">รถคันเก่า : คงเหลือเงินดาวน์</label>
                              <div class="input-group">
                                  <input type="number" step="0.01" name="old_down_balance" class="form-control border-2" placeholder="0.00" value="<?= htmlspecialchars($claim['old_down_balance'] ?? '') ?>">
                                  <span class="input-group-text border-2">บาท</span>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <label class="form-label fw-600">รถคันใหม่ : คงเหลือเงินดาวน์</label>
                              <div class="input-group">
                                  <input type="number" step="0.01" name="new_down_balance" class="form-control border-2" placeholder="0.00" value="<?= htmlspecialchars($claim['new_down_balance'] ?? '') ?>">
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
                                      <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repNew" value="new" <?= ($claim['replaceType'] ?? '') == 'new' ? 'checked' : '' ?>>
                                      <label class="form-check-label" for="repNew">รถใหม่</label>
                                  </div>
                                  <div class="form-check">
                                      <input class="form-check-input rep-car-type" type="radio" name="replaceType" id="repUsed" value="used" <?= ($claim['replaceType'] ?? '') == 'used' ? 'checked' : '' ?>>
                                      <label class="form-check-label" for="repUsed">รถมือสอง</label>
                                  </div>
                              </div>
                          </div>
                          <div class="col-md-4 rep-grade-field <?= ($claim['replaceType'] ?? '') == 'used' ? '' : 'd-none' ?>" id="repGradeField">
                              <label class="form-label fw-600">เกรด</label>
                              <select name="replaceUsedGrade" class="form-select border-2">
                                  <option value="">-- เลือกเกรด --</option>
                                  <option value="A_premium" <?= ($claim['replaceUsedGrade'] ?? '') == 'A_premium' ? 'selected' : '' ?>>A พรีเมี่ยม</option>
                                  <option value="A_w6" <?= ($claim['replaceUsedGrade'] ?? '') == 'A_w6' ? 'selected' : '' ?>>A (ประกัน 6 ด.)</option>
                                  <option value="C_w1" <?= ($claim['replaceUsedGrade'] ?? '') == 'C_w1' ? 'selected' : '' ?>>C (ประกัน 1 ด.)</option>
                                  <option value="C_as_is" <?= ($claim['replaceUsedGrade'] ?? '') == 'C_as_is' ? 'selected' : '' ?>>C (ตามสภาพ)</option>
                              </select>
                          </div>
                          
                          <div class="col-md-4">
                              <label class="form-label fw-600">รุ่น</label>
                              <input type="text" name="replace_model" class="form-control border-2" placeholder="รุ่น" value="<?= htmlspecialchars($claim['replace_model'] ?? '') ?>">
                          </div>
                          <div class="col-md-4">
                              <label class="form-label fw-600">สี</label>
                              <input type="text" name="replace_color" class="form-control border-2" placeholder="สี" value="<?= htmlspecialchars($claim['replace_color'] ?? '') ?>">
                          </div>

                          <div class="col-md-4">
                              <label class="form-label fw-600">เลขตัวถัง (คันใหม่)</label>
                              <input type="text" name="replace_vin" class="form-control border-2" placeholder="เลขตัวถัง / VIN" value="<?= htmlspecialchars($claim['replace_vin'] ?? '') ?>">
                          </div>
                          <div class="col-md-6">
                              <label class="form-label fw-600">วันที่รับรถ</label>
                              <input type="date" name="replace_receive_date" class="form-control border-2" value="<?= !empty($claim['replace_receive_date']) ? date('Y-m-d', strtotime($claim['replace_receive_date'])) : '' ?>">
                          </div>
                      </div>

                      <div class="mb-3 mt-4">
                          <label class="form-label fw-600">สาเหตุที่เปลี่ยนคัน</label>
                          <textarea name="replace_reason" class="form-control border-2" rows="2" placeholder="ระบุสาเหตุการเปลี่ยนคัน"><?= htmlspecialchars($claim['replace_reason'] ?? '') ?></textarea>
                      </div>

                      <div class="row g-3">
                          <div class="col-md-6">
                              <label class="form-label fw-600">ผู้อนุมัติ</label>
                              <input type="text" name="replace_approver" class="form-control border-2" placeholder="ชื่อผู้อนุมัติ" value="<?= htmlspecialchars($claim['replace_signature'] ?? '') ?>">
                          </div>
                          <div class="col-md-6">
                              <label class="form-label fw-600">วันที่อนุมัติ</label>
                              <input type="date" name="replace_approve_date" class="form-control border-2" value="<?= !empty($claim['replace_approve_date']) ? date('Y-m-d', strtotime($claim['replace_approve_date'])) : '' ?>">
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
            <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลผู้ใช้</div>
            <div class="row g-4">
              <div class="col-12 col-lg-6">
                <div class="row align-items-center mb-3">
                  <label class="col-sm-4 col-form-label fw-600 req">ชื่อ-นามสกุล</label>
                  <div class="col-sm-8">
                    <input type="text" name="ownerName" class="form-control border-2" value="<?= htmlspecialchars($claim['ownerName']) ?>" required>
                  </div>
                </div>
                <div class="row mb-3">
                  <label class="col-sm-4 col-form-label fw-600">ที่อยู่ผู้ซื้อ</label>
                  <div class="col-sm-8">
                    <textarea name="ownerAddress" class="form-control border-2" rows="3" placeholder="ระบุที่อยู่ปัจจุบัน"><?= htmlspecialchars($claim['ownerAddress'] ?? '') ?></textarea>
                  </div>
                </div>
              </div>
              
              <div class="col-12 col-lg-6">
                <div class="row align-items-center mb-3">
                  <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                  <div class="col-sm-8">
                    <input type="text" name="ownerPhone" class="form-control border-2" value="<?= htmlspecialchars($claim['ownerPhone'] ?? '') ?>">
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600 req">หมายเลขตัวถัง</label>
                  <div class="col-sm-8">
                    <input type="text" name="vin" class="form-control border-2" id="vin_number" value="<?= htmlspecialchars($claim['vin']) ?>" required>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
            <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ปัญหา</div>
            <div class="mb-4">
              <label class="form-label fw-600 mb-2">รายละเอียดปัญหาที่ลูกค้าแจ้ง</label>
              <textarea name="problemDesc" class="form-control border-2" rows="4"><?= htmlspecialchars($claim['problemDesc']) ?></textarea>
            </div>
            
            <div class="row">
              <div class="col-12 col-md-6 mb-3">
                <label class="form-label fw-600 mb-2">วิธีการตรวจเช็ค</label>
                <textarea name="inspectMethod" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['inspectMethod']) ?></textarea>
              </div>
              <div class="col-12 col-md-6 mb-3">
                <label class="form-label fw-600 mb-2">สาเหตุของปัญหา</label>
                <textarea name="inspectCause" class="form-control border-2" rows="3"><?= htmlspecialchars($claim['inspectCause']) ?></textarea>
              </div>
            </div>
          </div>

          <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
            <div class="section-title d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom gap-3">
              <div class="d-flex align-items-center fw-bold fs-5">
                รูปภาพปัญหา
                <span id="img-count-badge" class="badge rounded-pill bg-primary-orange ms-2 px-3" style="display:none;">0 รูป</span>
              </div>
              <label class="btn-action bg-primary-orange color-fff cursor-pointer m-0 px-3 py-2 fs-md rounded-3 shadow-sm text-center">
                + อัปโหลดรูปภาพใหม่
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
                    
                    <img src="../<?= htmlspecialchars($imgPath) ?>" class="preview-img cursor-pointer" style="width:100%; height:120px; object-fit:cover; border-radius:8px;" title="คลิกเพื่อขยาย">
                    <div class="img-preview-footer" style="padding:5px; text-align:center;">
                      <span class="img-preview-title" title="<?= htmlspecialchars($fileName) ?>" style="font-size:12px; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($fileName) ?></span>
                      <div class="img-preview-actions mt-1 d-flex justify-content-center gap-1">
                        <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= htmlspecialchars($fileName) ?>" class="btn btn-sm btn-success" style="font-size:12px; padding:2px 8px; text-decoration:none;" title="ดาวน์โหลดรูปภาพ">⬇️</a>
                        <button type="button" class="btn btn-sm btn-danger btn-remove-existing" style="font-size:12px; padding:2px 8px;" title="ลบรูป">❌</button>
                      </div>
                    </div>
                  </div>
              <?php 
                  endforeach; 
              endif; 
              ?>
            </div>
          </div>
          
          <div class="edit-card p-0 overflow-hidden mb-4 border-0 shadow-sm rounded-4">
              <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                  <div class="section-title fw-bold fs-5 mb-0">รายการอะไหล่</div>
                    <div class="parts-actions">
                      <button type="button" id="btnUploadParts" class="btn-action btn-green ...">อัปโหลดรูปภาพ</button>
                      <input type="file" id="imgPartsUpload" name="imgParts[]" accept="image/*" multiple class="d-none">
                    </div>
                </div>
                <div class="table-responsive">
                  <table class="edit-table table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>รหัสสินค้า</th>
                        <th>ชื่อสินค้า</th>
                        <th>Lot No.</th>
                        <th>ราคา/หน่วย</th>
                        <th>จำนวน</th>
                        <th>เป็นเงิน</th>
                        <th>ส่งDCS</th>
                        <th>พิเศษ</th>
                        <th>ลบ</th>
                      </tr>
                    </thead>
                    <tbody id="parts-main-tbody">
                      <tr class="group-header bg-light">
                        <td colspan="10" class="text-danger fw-bold py-3 ps-3">อะไหล่หลัก</td>
                      </tr>
                      
                      <tr id="add-main-row">
                        <td colspan="10" class="p-3 text-center">
                          <button type="button" class="btn btn-outline-orange btn-sm text-primary-orange w-100 py-3 border-dashed" id="btn-add-main" style="border-style: dashed !important; border-width: 2px;">+ เพิ่มอะไหล่หลัก</button>
                        </td>
                      </tr>
                    </tbody>

                    <tbody id="parts-assoc-tbody">
                      <tr class="group-header bg-light">
                        <td colspan="10" class="text-danger fw-bold">อะไหล่ที่เคลมร่วมกัน</td>
                      </tr>
                      
                      <tr id="add-assoc-row">
                        <td colspan="10" class="p-3 text-center">
                          <button type="button" class="btn btn-btn-outline-orange btn-sm text-primary-orange w-100 py-3 border-dashed" id="btn-add-assoc" style="border-style: dashed !important; border-width: 2px;">+ เพิ่มอะไหล่เคลมร่วม</button>
                        </td>
                      </tr>
                    </tbody>

                    <tbody id="parts-summary-tbody">
                      <tr class="summary-row fw-bold bg-light">
                        <td colspan="5" class="py-3 ps-4">ยอดรวม</td>
                        <td class="text-center text-primary-orange fw-bold" id="sum-qty">0</td>
                        <td class="text-center text-primary-orange fw-bold" id="sum-money">0.00</td>
                      </tr>
                      <tr>
                          <div id="partsImgPreview" class="d-flex flex-wrap gap-3 mt-3"></div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
          </div>

          <div class="edit-card p-4 border-0 shadow-sm rounded-4 mb-4" style="background-color: #fbfbfb;">
              <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ค่าแรงและสรุปเงินประจำเคส</div>
              <div class="row g-4">
                <div class="col-12 col-lg-6">
                  <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                    <div class="d-flex flex-column gap-3">
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-600 req">จำนวน FRT</label>
                        <div class="col-sm-7">
                          <div class="input-group">
                            <input type="number" step="0.01" class="form-control border-2" id="labor-frt" value="0.00">
                            <span class="input-group-text border-2">ชม.</span>
                          </div>
                        </div>
                      </div>
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-600">FRT. Rate/hr</label>
                        <div class="col-sm-7">
                          <div class="input-group">
                            <input type="number" step="0.01" class="form-control border-2" id="labor-rate" value="0.00">
                            <span class="input-group-text border-2">บาท</span>
                          </div>
                        </div>
                      </div>
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-600">รวมค่าแรง</label>
                        <div class="col-sm-7">
                          <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 fw-bold" id="labor-total" value="0.00" readonly>
                            <span class="input-group-text border-0 bg-light">บาท</span>
                          </div>
                        </div>
                      </div>
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-600">รวมค่าอะไหล่</label>
                        <div class="col-sm-7">
                          <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 fw-bold" id="labor-parts-total" value="0.00" readonly>
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
                            <input type="number" step="0.1" class="form-control border-2" id="manage-pct" value="0.00">
                            <span class="input-group-text border-2">%</span>
                          </div>
                        </div>
                      </div>
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-600">ค่าการจัดการ</label>
                        <div class="col-sm-7">
                          <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 fw-bold" id="manage-fee" value="0.00" readonly>
                            <span class="input-group-text border-0 bg-light">บาท</span>
                          </div>
                        </div>
                      </div>
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-600">ค่าใช้จ่ายอื่นๆ</label>
                        <div class="col-sm-7">
                          <div class="input-group">
                            <input type="number" step="0.01" class="form-control border-2" id="other-fee" value="0.00">
                            <span class="input-group-text border-2">บาท</span>
                          </div>
                        </div>
                      </div>
                      <div class="row align-items-center">
                        <label class="col-sm-5 col-form-label fw-700 text-primary-orange fs-5">รวมเงินเคลมสุทธิ</label>
                        <div class="col-sm-7">
                          <div class="input-group shadow-sm">
                            <input type="text" class="form-control hc-total-input fw-bold border-2 border-primary-orange text-primary-orange fs-5 py-2" id="grand-total" value="0.00" readonly>
                            <span class="input-group-text border-2 border-primary-orange bg-primary-orange color-fff fw-bold">บาท</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          <div class="edit-card border-0 shadow-sm rounded-4 p-4" id="verification-section">
            <div class="section-title verification-title mb-4 pb-2 border-bottom fw-bold fs-5 color-primary-orange">ลงชื่อผู้แก้ไขเอกสาร</div>
            
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                  <div class="row align-items-center">
                    <label class="col-sm-3 col-form-label fw-600">สถานะ :</label>
                    <div class="col-sm-9">
                      <select name="status" class="form-select verification-select fw-bold border-2">
                        <option value="Pending" <?= $claim['status'] == 'Pending' ? 'selected' : '' ?> class="text-warning">รอดำเนินการ</option>
                        <option value="Approved" <?= $claim['status'] == 'Approved' ? 'selected' : '' ?> class="text-success">อนุมัติ</option>
                        <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?> class="text-danger">ปฏิเสธ</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-lg-6">
                  <div class="">
                    <label class="form-label fw-600 mb-2">หมายเหตุ / ความเห็นของผู้แก้ไข</label>
                    <textarea name="remarks" class="form-control border-2" rows="3" placeholder="ระบุเหตุผลการแก้ไข..."></textarea>
                  </div>
                </div>
            </div>
            
            <div class="row g-4 border-top pt-4">
               <div class="col-12 col-lg-6">
                 <div class="row align-items-center mb-3">
                    <label class="col-sm-4 col-form-label fw-600">ลงชื่อผู้แก้ไข <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                      <input type="text" name="editor" id="bottom-editor-name" class="form-control border-2" placeholder="พิมพ์ชื่อ-นามสกุล ผู้แก้ไขปัจจุบัน" required>
                    </div>
                 </div>
                 <div class="row align-items-center">
                    <label class="col-sm-4 col-form-label fw-600">วันที่ทำรายการ</label>
                    <div class="col-sm-8">
                      <input type="date" class="form-control border-2 bg-light" value="<?= date('Y-m-d') ?>" readonly>
                    </div>
                 </div>
               </div>
               
               <div class="col-12 col-lg-6 d-flex flex-column flex-sm-row justify-content-end align-items-stretch align-items-sm-end gap-3 mt-4 mt-lg-0">
                  <a href="history.php" class="btn btn-secondary px-5 py-2 rounded-3 shadow-sm text-decoration-none text-center color-fff">ยกเลิก</a>
                  <button type="submit" class="btn-action bg-primary-orange color-fff border-0 px-5 py-2 rounded-3 shadow-sm fw-bold">บันทึกการแก้ไข</button>
               </div>
            </div>
          </div>
          
        </div> 
      </form>
    </div>
  </div>

  <div class="modal-overlay" id="image-modal">
    <div class="modal-close" id="modal-close">×</div>
    <img src="" id="modal-img" class="modal-content" alt="Enlarged view">
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {

      // ดักการโชว์กล่อง "เปลี่ยนคัน"
      const actRadios = document.querySelectorAll('.act-radio');
      const replaceBlock = document.getElementById('replaceBlock');
      if (actRadios && replaceBlock) {
          actRadios.forEach(r => r.addEventListener('change', function() {
              if (this.value === 'replaceVehicle') {
                  replaceBlock.classList.remove('d-none');
                  replaceBlock.classList.add('d-block');
              } else {
                  replaceBlock.classList.remove('d-block');
                  replaceBlock.classList.add('d-none');
              }
          }));
      }

      // ดักการโชว์กล่อง "เกรด" เวลากดรถมือสอง (ในกล่องเปลี่ยนคัน)
      const repCarTypes = document.querySelectorAll('.rep-car-type');
      const repGradeField = document.getElementById('repGradeField');
      if (repCarTypes && repGradeField) {
          repCarTypes.forEach(r => r.addEventListener('change', function() {
              if (this.value === 'used') {
                  repGradeField.classList.remove('d-none');
              } else {
                  repGradeField.classList.add('d-none');
                  repGradeField.querySelector('select').value = '';
              }
          }));
      }

      // ดักการโชว์กล่องพิมพ์ข้อความ เมื่อเลือกส่งอะไหล่ "อื่นๆ"
      const pdRadios = document.querySelectorAll('.pd-radio');
      const pdOtherText = document.getElementById('partsDeliveryOtherTextEdit');
      if(pdRadios && pdOtherText) {
          pdRadios.forEach(r => r.addEventListener('change', function() {
              if(this.value === 'other') {
                  pdOtherText.classList.remove('d-none');
              } else {
                  pdOtherText.classList.add('d-none');
                  pdOtherText.value = '';
              }
          }));
      }

      // ดักการลบรูปภาพเก่า
      document.addEventListener('click', function(e) {
          if (e.target && e.target.classList.contains('btn-remove-existing')) {
              e.target.closest('.existing-item').remove();
              updateImgCountBadge();
          }
      });

      function updateImgCountBadge() {
          const badge = document.getElementById('img-count-badge');
          if (!badge) return;
          const count = document.querySelectorAll('#gallery-grid .gallery-item').length;
          badge.textContent = count + ' รูป';
          badge.style.display = count > 0 ? 'inline-block' : 'none';
      }

      function createImageItem(src, title, fileIndex) {
          const galleryGrid = document.getElementById('gallery-grid');
          if (!galleryGrid) return;

          const div = document.createElement('div');
          div.className = 'gallery-item';
          div.dataset.fileIndex = fileIndex;
          div.innerHTML = `
            <img src="${src}" class="preview-img cursor-pointer" style="width:100%; height:120px; object-fit:cover; border-radius:8px;" title="${title}">
            <div class="img-preview-footer" style="padding:5px; text-align:center;">
              <span class="img-preview-title" title="${title}" style="font-size:12px; display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${title}</span>
              <div class="img-preview-actions mt-1 d-flex justify-content-center gap-1">
                <button type="button" class="btn btn-sm btn-danger btn-remove-new" style="font-size:12px; padding:2px 8px;">❌</button>
              </div>
            </div>
          `;

          const imgEl = div.querySelector('.preview-img');
          const removeBtn = div.querySelector('.btn-remove-new');

          if (removeBtn) {
              removeBtn.addEventListener('click', function(e) {
                  e.stopPropagation();
                  uploadedFiles[fileIndex] = null;
                  div.remove();
                  updateImgCountBadge();
              });
          }

          if (imgEl) {
              imgEl.addEventListener('click', function() {
                  if (modalImg && imageModal) {
                      modalImg.src = src;
                      imageModal.style.display = 'flex';
                  }
              });
          }

          galleryGrid.appendChild(div);
          updateImgCountBadge();
      }

      updateImgCountBadge();

      // ตารางคำนวณอะไหล่และเงิน
      const mainPartsTbody = document.getElementById('parts-main-tbody');
      const assocPartsTbody = document.getElementById('parts-assoc-tbody');
      
      function calculateParts() {
        let sumQty = 0; let sumTotal = 0;
        document.querySelectorAll('.part-row').forEach((row, index) => {
           row.cells[0].textContent = index + 1; 
           const price = parseFloat(row.querySelector('.part-price').value) || 0;
           const qty = parseFloat(row.querySelector('.part-qty').value) || 0;
           const total = price * qty;
           row.querySelector('.part-total').value = total.toFixed(2);
           sumQty += qty; sumTotal += total;
        });
        if(document.getElementById('sum-qty')) document.getElementById('sum-qty').textContent = sumQty;
        if(document.getElementById('sum-money')) document.getElementById('sum-money').textContent = sumTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
        if(document.getElementById('labor-parts-total')) {
            document.getElementById('labor-parts-total').value = sumTotal.toFixed(2);
            calculateLaborAndGrandTotal();
        }
      }

      const bottomEditorName = document.getElementById('bottom-editor-name');
      const topEditorName = document.getElementById('top-editor-name');
      if(bottomEditorName && topEditorName) {
        topEditorName.value = bottomEditorName.value; 
        bottomEditorName.addEventListener('input', function() { topEditorName.value = this.value || 'ไม่ได้ระบุ'; });
      }

      function calculateLaborAndGrandTotal() {
        const frt = parseFloat(document.getElementById('labor-frt')?.value) || 0;
        const rate = parseFloat(document.getElementById('labor-rate')?.value) || 0;
        const laborTotal = frt * rate;
        if(document.getElementById('labor-total')) document.getElementById('labor-total').value = laborTotal.toFixed(2);
        
        const partsTotal = parseFloat(document.getElementById('labor-parts-total')?.value) || 0;
        const managePct = parseFloat(document.getElementById('manage-pct')?.value) || 0;
        const manageFee = partsTotal * (managePct / 100);
        if(document.getElementById('manage-fee')) document.getElementById('manage-fee').value = manageFee.toFixed(2);
        
        const otherFee = parseFloat(document.getElementById('other-fee')?.value) || 0;
        const grandTotal = laborTotal + partsTotal + manageFee + otherFee;
        if(document.getElementById('grand-total')) document.getElementById('grand-total').value = grandTotal.toFixed(2);
      }
      
      function attachRowEvents(row) {
         const priceInp = row.querySelector('.part-price');
         if(priceInp) priceInp.addEventListener('input', calculateParts);
         const qtyInp = row.querySelector('.part-qty');
         if(qtyInp) qtyInp.addEventListener('input', calculateParts);
         const rmBtn = row.querySelector('.btn-remove-part');
         if(rmBtn) rmBtn.addEventListener('click', () => { row.remove(); calculateParts(); });
      }
      
      function createNewPartRow(data = {}) {
        const tr = document.createElement('tr');
        tr.className = 'part-row';

        const tdIdx = document.createElement('td');
        tdIdx.className = 'idx';
        tdIdx.style.textAlign = 'center';
        tr.appendChild(tdIdx);

        const tdCode = document.createElement('td');
        const inCode = document.createElement('input');
        inCode.type = 'text';
        inCode.name = 'parts_code[]';
        inCode.className = 'form-control form-control-sm';
        inCode.value = data.code || '';
        const wrapCode = document.createElement('div');
        wrapCode.className = 'field';
        const lblCode = document.createElement('span');
        lblCode.className = 'label-text';
        wrapCode.appendChild(lblCode);
        wrapCode.appendChild(inCode);
        tdCode.appendChild(wrapCode);
        tr.appendChild(tdCode);

        const tdName = document.createElement('td');
        const inName = document.createElement('input');
        inName.type = 'text';
        inName.name = 'parts_name[]';
        inName.className = 'form-control form-control-sm';
        inName.value = data.name || '';
        const wrapName = document.createElement('div');
        wrapName.className = 'field';
        const lblName = document.createElement('span');
        lblName.className = 'label-text';
        wrapName.appendChild(lblName);
        wrapName.appendChild(inName);
        tdName.appendChild(wrapName);
        tr.appendChild(tdName);

        const tdNote = document.createElement('td');
        const inNote = document.createElement('input');
        inNote.type = 'text';
        inNote.name = 'parts_note[]';
        inNote.className = 'form-control form-control-sm';
        inNote.value = data.note || '';
        const wrapNote = document.createElement('div');
        wrapNote.className = 'field';
        const lblNote = document.createElement('span');
        lblNote.className = 'label-text';
        wrapNote.appendChild(lblNote);
        wrapNote.appendChild(inNote);

        const inType = document.createElement('input');
        inType.type = 'hidden';
        inType.name = 'parts_type[]';
        inType.value = data.type ? data.type : 'main';
        wrapNote.appendChild(inType);
        tdNote.appendChild(wrapNote);
        tr.appendChild(tdNote);

        const tdPrice = document.createElement('td');
        tdPrice.className = 'text-center';
        const inPrice = document.createElement('input');
        inPrice.type = 'number';
        inPrice.name = 'parts_price[]';
        inPrice.step = '0.01';
        inPrice.min = '0';
        inPrice.className = 'form-control form-control-sm text-center part-price';
        inPrice.value = data.price || '0.00';
        tdPrice.appendChild(inPrice);
        tr.appendChild(tdPrice);

        const tdQty = document.createElement('td');
        tdQty.className = 'text-center';
        const inQty = document.createElement('input');
        inQty.type = 'number';
        inQty.name = 'parts_qty[]';
        inQty.min = '0';
        inQty.className = 'form-control form-control-sm text-center part-qty';
        inQty.value = data.qty || '1';
        tdQty.appendChild(inQty);
        tr.appendChild(tdQty);

        const tdTotal = document.createElement('td');
        tdTotal.className = 'text-center';
        const inTotal = document.createElement('input');
        inTotal.type = 'text';
        inTotal.className = 'form-control form-control-sm text-center bg-light part-total';
        inTotal.value = '0.00';
        inTotal.readOnly = true;
        tdTotal.appendChild(inTotal);
        tr.appendChild(tdTotal);

        const tdSendDcs = document.createElement('td');
        tdSendDcs.className = 'text-center';
        const inSendDcs = document.createElement('input');
        inSendDcs.type = 'checkbox';
        inSendDcs.className = 'form-check-input';
        tdSendDcs.appendChild(inSendDcs);
        tr.appendChild(tdSendDcs);

        const tdSpecial = document.createElement('td');
        tdSpecial.className = 'text-center';
        const inSpecial = document.createElement('input');
        inSpecial.type = 'checkbox';
        inSpecial.className = 'form-check-input';
        tdSpecial.appendChild(inSpecial);
        tr.appendChild(tdSpecial);

        const tdAct = document.createElement('td');
        tdAct.style.textAlign = 'center';
        const del = document.createElement('button');
        del.type = 'button';
        del.className = 'btn btn-link text-danger btn-remove-part p-0';
        del.innerHTML = '❌';
        del.addEventListener('click', () => { tr.remove(); calculateParts(); });
        tdAct.appendChild(del);
        tr.appendChild(tdAct);

        attachRowEvents(tr);
        return tr;
      }

      function movePartRowByType(row, type) {
        if (type === 'assoc' && assocPartsTbody && addAssocRow) {
          assocPartsTbody.insertBefore(row, addAssocRow);
        } else if (mainPartsTbody && addMainRow) {
          mainPartsTbody.insertBefore(row, addMainRow);
        }
        calculateParts();
      }

      const existingParts = <?= !empty($partsArray) ? json_encode($partsArray) : '[]' ?>;
      const addMainRow = document.getElementById('add-main-row');
      const addAssocRow = document.getElementById('add-assoc-row');
      if (existingParts && existingParts.length > 0) {
          existingParts.forEach(part => {
              const partData = Object.assign({ type: part.type || 'main' }, part);
              if (partData.type === 'assoc' && assocPartsTbody && addAssocRow) {
                  assocPartsTbody.insertBefore(createNewPartRow(partData), addAssocRow);
              } else if (mainPartsTbody && addMainRow) {
                  mainPartsTbody.insertBefore(createNewPartRow(partData), addMainRow);
              }
          });
      }
      document.getElementById('btn-add-main')?.addEventListener('click', () => { if (mainPartsTbody && addMainRow) { mainPartsTbody.insertBefore(createNewPartRow({ type: 'main' }), addMainRow); calculateParts(); }});
      document.getElementById('btn-add-assoc')?.addEventListener('click', () => { if (assocPartsTbody && addAssocRow) { assocPartsTbody.insertBefore(createNewPartRow({ type: 'assoc' }), addAssocRow); calculateParts(); }});
      
      ['labor-frt', 'labor-rate', 'manage-pct', 'other-fee'].forEach(id => {
          document.getElementById(id)?.addEventListener('input', calculateLaborAndGrandTotal);
      });
      calculateParts();

      // ระบบจัดการรูปภาพใหม่
      const imageUpload = document.getElementById('image-upload');
      const galleryGrid = document.getElementById('gallery-grid');
      const imageModal = document.getElementById('image-modal');
      const modalImg = document.getElementById('modal-img');
      const modalClose = document.getElementById('modal-close');
      let imageIndex = 1; let uploadedFiles = []; 
      
      if(imageUpload && galleryGrid) {
          imageUpload.addEventListener('change', function(e) {
            const files = e.target.files;
            const vinNumber = document.getElementById('vin_number') ? document.getElementById('vin_number').value || 'UnknownVIN' : 'UnknownVIN';
            for (let i = 0; i < files.length; i++) {
               const file = files[i];
               uploadedFiles.push(file); 
               const fileIndex = uploadedFiles.length - 1; 
               const reader = new FileReader();
               reader.onload = function(evt) {
                  const ext = file.name.split('.').pop() || 'jpg';
                  createImageItem(evt.target.result, `รูปภาพปัญหา_${vinNumber}_${imageIndex}.${ext}`, fileIndex);
                  imageIndex++;
               }
               reader.readAsDataURL(file);
            }
            this.value = ''; 
          });
      }

      if (galleryGrid && imageModal && modalImg) {
          galleryGrid.addEventListener('click', function(e) {
              const target = e.target;
              if (target && target.classList.contains('preview-img')) {
                  modalImg.src = target.src;
                  imageModal.style.display = 'flex';
              }
          });
      }

      if (modalClose && imageModal) {
          modalClose.addEventListener('click', function() {
              imageModal.style.display = 'none';
          });
      }

      if (imageModal) {
          imageModal.addEventListener('click', function(e) {
              if (e.target === this) {
                  imageModal.style.display = 'none';
              }
          });
      }
      
      const filesMap = {};


      // ระบบอัปโหลดรูปภาพอะไหล่
      const btnUploadParts = document.getElementById('btnUploadParts');
      const imgPartsUpload = document.getElementById('imgPartsUpload');
      const partsPreview = document.getElementById('partsImgPreview');
      const partsFieldId = 'imgParts[]';
      filesMap[partsFieldId] = [];

      if (btnUploadParts && imgPartsUpload) {
        btnUploadParts.addEventListener('click', () => imgPartsUpload.click());
        imgPartsUpload.addEventListener('change', function() {
          const chosenFiles = Array.from(this.files || []);
          if (chosenFiles.length > 0) {
            filesMap[partsFieldId] = filesMap[partsFieldId].concat(chosenFiles);
            renderPartsPreview();
          }
          this.value = ''; 
        });
      }

      function renderPartsPreview() {
        partsPreview.innerHTML = '';
        const list = filesMap[partsFieldId];
        const uploadSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>`;
        
        if (list.length === 0) {
          btnUploadParts.innerHTML = uploadSvg + ' อัปโหลดรูปภาพ';
        } else {
          btnUploadParts.innerHTML = uploadSvg + ` อัปโหลดแล้ว ${list.length} รูป`;
        }

        list.forEach((file, idx) => {
          const wrap = document.createElement('div');
          wrap.style.cssText = 'position: relative; width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.05);';
          
          const img = document.createElement('img');
          img.style.cssText = 'width: 100%; height: 100%; object-fit: cover; display: block; cursor: pointer;';
          const reader = new FileReader();
          reader.onload = e => { img.src = e.target.result; };
          reader.readAsDataURL(file);
          
          const del = document.createElement('button');
          del.type = 'button';
          del.innerHTML = '×';
          del.style.cssText = 'position: absolute; top: 6px; right: 6px; background: rgba(255, 30, 30, 0.85); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; padding: 0;';
          del.addEventListener('click', (ev) => {
            ev.stopPropagation();
            filesMap[partsFieldId].splice(idx, 1);
            renderPartsPreview();
          });

          img.addEventListener('click', (ev) => { ev.stopPropagation(); openLightbox(partsFieldId, idx); });

          wrap.appendChild(img);
          wrap.appendChild(del);
          partsPreview.appendChild(wrap);
        });
      }

      // ระบบ Lightbox
      document.body.insertAdjacentHTML('beforeend', '\n        <div id="lightbox" class="lightbox" aria-hidden="true">\n          <div class="imgwrap">\n            <button class="close" aria-label="ปิด">✕</button>\n            <button class="nav prev" aria-label="ก่อนหน้า">‹</button>\n            <div class="imgframe"><img src="" alt="preview"><div class="counter" aria-hidden="true"></div></div>\n            <button class="nav next" aria-label="ถัดไป">›</button>\n          </div>\n        </div>\n      ');
      const lb = document.getElementById('lightbox');
      let lbState = { fieldId: null, index: 0 };
      
      function openLightbox(fieldId, index){
        const list = filesMap[fieldId] || [];
        if(!list || !list.length) return;
        lbState.fieldId = fieldId; lbState.index = index || 0;
        const file = list[lbState.index];
        const img = lb.querySelector('.imgframe img');
        const counter = lb.querySelector('.counter');
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; counter.textContent = (lbState.index+1) + ' / ' + list.length; };
        reader.readAsDataURL(file);
        lb.classList.add('open'); lb.setAttribute('aria-hidden','false');
      }
      
      function closeLightbox(){ lb.classList.remove('open'); lb.setAttribute('aria-hidden','true'); lbState = { fieldId:null, index:0 }; }
      function lbNext(){ if(!lbState.fieldId) return; const list = filesMap[lbState.fieldId]||[]; lbState.index = (lbState.index+1)%list.length; openLightbox(lbState.fieldId, lbState.index); }
      function lbPrev(){ if(!lbState.fieldId) return; const list = filesMap[lbState.fieldId]||[]; lbState.index = (lbState.index-1+list.length)%list.length; openLightbox(lbState.fieldId, lbState.index); }
      
      lb.addEventListener('click', e=>{ if(e.target.id==='lightbox' || e.target.classList.contains('close')) closeLightbox(); });
      lb.querySelector('.nav.next').addEventListener('click', e=>{ e.stopPropagation(); lbNext(); });
      lb.querySelector('.nav.prev').addEventListener('click', e=>{ e.stopPropagation(); lbPrev(); });
      document.addEventListener('keydown', e=>{ if(!lb.classList.contains('open')) return; if(e.key==='Escape') closeLightbox(); if(e.key==='ArrowRight') lbNext(); if(e.key==='ArrowLeft') lbPrev(); });

      // ส่งข้อมูลเข้าเซิร์ฟเวอร์
      const editForm = document.querySelector('form');
      if(editForm) {
          editForm.addEventListener('submit', function(e) {
            e.preventDefault(); 
            const submitBtn = document.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.innerHTML : 'บันทึกข้อมูล';
            if(submitBtn) { submitBtn.innerHTML = '⏳ กำลังบันทึกข้อมูล...'; submitBtn.disabled = true; }

            if (!this.checkValidity()) {
              this.reportValidity();
              return;
            }

            const fd = new FormData(this);
            uploadedFiles.forEach(file => { if (file !== null) fd.append('claim_images[]', file); });
            const partsFieldId = 'imgParts[]';
            if (filesMap[partsFieldId]) {
                filesMap[partsFieldId].forEach(file => { if (file !== null) fd.append(partsFieldId, file); });
            }

            fetch('../backend/edit_handler.php', { method: 'POST', body: fd })
            .then(res => res.text())
            .then(text => {
                if (text.includes('✅')) {
                    alert('✅ บันทึกการแก้ไขข้อมูลเรียบร้อยแล้ว!');
                    window.location.href = 'history.php'; 
                } else {
                    alert('❌ เกิดข้อผิดพลาดจากฐานข้อมูล:\n' + text.replace(/(<([^>]+)>)/gi, "")); 
                    if(submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
                }
            })
            .catch(err => {
                alert('❌ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์!');
                if(submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
            });
          });
      }
    });
  </script>
</body>
</html>