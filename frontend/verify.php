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

    $stmtItems = $pdo->prepare("SELECT * FROM claim_items WHERE claim_id = ?");
    $stmtItems->execute([$id]);
    $items = $stmtItems->fetchAll();

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

    $updatedAtFormatted = '-';
    if (!empty($claim['updated_at'])) {
        $uTimestamp = strtotime($claim['updated_at']);
        if ($uTimestamp !== false) {
            $updatedAtFormatted = date('d/m/Y H:i', $uTimestamp);
        }
    }

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
        
        $carAgeDisplay = !empty($parts) ? implode(" ", $parts) : "0 วัน";
    }
    
    // แปลงประเภทรถ และการดำเนินการ
    // แปลงประเภทรถ
    $carTypeDisplay = $claim['car_type'] === 'new' ? 'รถใหม่' : ($claim['car_type'] === 'used' ? 'รถมือสอง' : $claim['car_type']);
    
    // แสดงประเภทการเคลม (ดึงจากฐานข้อมูลตรงๆ เพราะเราบันทึกเป็นภาษาไทยอยู่แล้ว)
    $claimCategoryDisplay = $claim['claim_category'] ?: '-';
    
    // แปลงชื่อประเภทการเคลมจริงๆ
    $claimTypeMap = [
        'RepairBranch' => 'ซ่อมที่สาขา',
        'SendHQ' => 'ส่งซ่อมสนญ.',
        'ReplaceVehicle' => 'เปลี่ยนคัน',
        'Other' => 'อื่นๆ'
    ];
    $cType = $claim['claim_type'] ?? '';
    $claimTypeDisplay = $claimTypeMap[$cType] ?? ($cType ?: '-');

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
              <a href="#verification-section" class="btn-action <?= isAdmin() ? 'bg-primary-orange' : 'bg-info' ?> text-decoration-none px-3 py-2 color-fff rounded-3 shadow-sm">
                  <i class="fas fa-check-circle me-1"></i> <?= isAdmin() ? 'ไปยังส่วนอนุมัติ' : 'ดูผลการอนุมัติ' ?>
              </a>
            </div>
          </div>
        </div>
      </div>

      <div class="edit-container mb-5 mt-4">
        
        <!-- ข้อมูลเอกสาร -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลเอกสาร</div>
          <div class="row g-4">
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">สาขา</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= htmlspecialchars($claim['branch']) ?>" readonly></div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">ประเภทการเคลม</label>
                  <div class="col-sm-8">
                    <div class="row g-2">
                       <div class="col-6"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= $claimCategoryDisplay ?>" readonly></div>
                       <div class="col-6"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= $carTypeDisplay ?>" readonly></div>
                    </div>
                  </div>
                </div>
                <div class="row align-items-center mb-2">
                  <label class="col-sm-4 col-form-label fw-600">การดำเนินการ</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input text-primary-orange fw-bold" value="<?= $claimTypeDisplay ?>" readonly></div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">เลขที่เอกสาร</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= $doc_id ?>" readonly></div>
                </div>
              </div>
            </div>
            
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">วันที่เอกสาร</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= $claimDateFormatted ?>" readonly></div>
                </div>
                <div class="row align-items-center text-secondary">
                  <label class="col-sm-4 col-form-label fw-600">ผู้บันทึกส่งเคลม</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= htmlspecialchars($claim['recorder_id']) ?>" readonly></div>
                </div>
                <div class="row align-items-center text-secondary">
                  <label class="col-sm-4 col-form-label fw-600">วันที่แก้ไขล่าสุด</label>
                  <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input" value="<?= $updatedAtFormatted ?>" readonly></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ข้อมูลผู้ใช้งาน -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลผู้ใช้งาน และรถ</div>
          <div class="row g-4">
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">ชื่อ-นามสกุล</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input text-danger fw-bold" value="<?= htmlspecialchars($claim['owner_name']) ?>" readonly></div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input text-danger fw-bold" value="<?= htmlspecialchars($claim['owner_phone'] ?? '-') ?>" readonly></div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label fw-600">เลขตัวถัง (VIN)</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input text-danger fw-bold" value="<?= htmlspecialchars($claim['vin']) ?>" readonly></div>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">เลขไมล์</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input text-danger fw-bold" value="<?= htmlspecialchars($claim['mileage'] . ' กม.') ?>" readonly></div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">วันที่ขาย</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input text-danger fw-bold" value="<?= !empty($claim['sale_date']) ? date('d/m/Y', strtotime($claim['sale_date'])) : '-' ?>" readonly></div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label fw-600 border-0">อายุรถ</label>
                <div class="col-sm-8"><input type="text" class="form-control bg-light border-0 pill-input fw-bold text-danger" value="<?= $carAgeDisplay ?>" readonly></div>
              </div>
            </div>
          </div>
        </div>

        <!-- รายละเอียดปัญหา -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">รายละเอียดปัญหา</div>
          <div class="mb-4">
            <label class="form-label fw-600 mb-2">อาการปัญหาที่ลูกค้าแจ้ง</label>
            <textarea class="form-control bg-light border-0 pill-textarea" rows="3" readonly><?= htmlspecialchars($claim['problem_desc']) ?></textarea>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-600 mb-2">วิธีการตรวจเช็ค</label>
              <textarea class="form-control bg-light border-0 pill-textarea" rows="3" readonly><?= htmlspecialchars($claim['inspect_method']) ?></textarea>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-600 mb-2">สาเหตุของปัญหา</label>
              <textarea class="form-control bg-light border-0 pill-textarea" rows="3" readonly><?= htmlspecialchars($claim['inspect_cause']) ?></textarea>
            </div>
          </div>
        </div>

        <!-- รูปภาพปัญหา -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">รูปภาพปัญหาที่พบ</div>
          <div class="gallery-grid">
            <?php 
            $savedImgs = !empty($claim['claim_images']) ? json_decode($claim['claim_images'], true) : [];
            if(is_array($savedImgs) && count($savedImgs) > 0):
                foreach($savedImgs as $imgPath):
                    $fileName = basename($imgPath);
            ?>
                <div class="gallery-item">
                   <div class="img-preview-container cursor-pointer" onclick="openImageModal('../<?= htmlspecialchars($imgPath) ?>')">
                       <img src="../<?= htmlspecialchars($imgPath) ?>" alt="รูปภาพเคลม">
                   </div>
                   <div class="img-preview-footer text-center">
                       <span class="img-preview-title" title="<?= htmlspecialchars($fileName) ?>"><?= htmlspecialchars($fileName) ?></span>
                       <?php 
                           $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                           $cleanDownloadName = str_replace(['/', '\\'], '_', htmlspecialchars($fileName)); // Default
                           // Create clean name: Prefix_VIN
                           $prefixName = explode('_', $fileName)[0] ?? 'รูปภาพ';
                           $cleanDownloadName = $prefixName . '_' . htmlspecialchars($claim['vin']) . '.' . $ext;
                       ?>
                       <a href="../<?= htmlspecialchars($imgPath) ?>" download="<?= $cleanDownloadName ?>" class="img-download-link"><i class="fas fa-download"></i></a>
                   </div>
                </div>
            <?php endforeach; else: ?>
                <div class="col-12 text-center py-4 bg-light rounded-3 border border-dashed">
                  <p class="text-muted mb-0">ไม่ได้แนบรูปภาพปัญหามาในเอกสารนี้</p>
                </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- รายการอะไหล่ -->
        <div class="edit-card p-0 overflow-hidden mb-4 border-0 shadow-sm rounded-4">
            <div class="p-4">
              <div class="section-title mb-3 pb-2 border-bottom fw-bold fs-5">รายการอะไหล่และค่าใช้จ่าย</div>
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
                  <?php $sumQty = 0; $sumMoney = 0; ?>
                  <tbody>
                    <?php if (count($items) > 0): ?>
                        <?php foreach ($items as $idx => $part):
                            $qty = floatval($part['quantity'] ?? 0);
                            $price = floatval($part['unit_price'] ?? 0);
                            $total = $qty * $price;
                            $sumQty += $qty; $sumMoney += $total;
                        ?>
                        <tr>
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
                    <tr class="summary-row">
                      <td colspan="4" class="py-3 ps-4 text-end">รวมยอดอะไหล่สุทธิ</td>
                      <td class="text-center text-primary-orange"><?= $sumQty ?></td>
                      <td class="text-center text-primary-orange fw-bold"><?= number_format($sumMoney, 2) ?> บาท</td>
                      <td></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
        </div>

        <form id="verifyForm" method="POST" action="../backend/verify_handler.php">
            <input type="hidden" name="claim_id" value="<?= $claim['id'] ?>">

            <!-- ข้อมูลการซ่อม (Job Card) -->
            <div class="job-info-card mb-4 border-0 shadow-sm rounded-4 p-4">
                <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลการซ่อม (Job)</div>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary mb-2">เลขที่ Job</label>
                        <input type="text" name="job_number" class="form-control pill-input" placeholder="ระบุเลขที่ JOB" value="<?= htmlspecialchars($claim['job_number'] ?? '') ?>" <?= !isAdmin() ? 'readonly' : '' ?>>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-secondary mb-2">จำนวนเงิน (บาท)</label>
                        <input type="number" step="0.01" name="job_amount" class="form-control pill-input" placeholder="ระบุจำนวนเงิน" value="<?= htmlspecialchars($claim['job_amount'] ?? '') ?>" <?= !isAdmin() ? 'readonly' : '' ?>>
                    </div>
                </div>
            </div>

            <!-- ผลการตรวจสอบและอนุมัติ -->
            <div class="edit-log-card border-0 shadow-sm rounded-4 p-4 mb-5" id="verification-section">
                <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5" style="color: #ff7a32;">
                    ผลการตรวจสอบและอนุมัติ
                </div>
                
                <div class="approval-grid">
                    <!-- ฝั่งซ้าย: Checklist และ ลงนาม -->
                    <div class="checklist-column">
                        <label class="form-label fw-bold text-secondary mb-3">รายการตรวจสอบ (Checklist)</label>
                        <div class="checklist-container">
                            <label class="checklist-item">
                                <input type="checkbox" class="checklist-checkbox" id="check1" <?= !isAdmin() ? 'disabled' : '' ?>>
                                <span class="checklist-text">ตรวจสอบความถูกต้องของข้อมูลลูกค้าและหมายเลขตัวถัง</span>
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox" class="checklist-checkbox" id="check2" <?= !isAdmin() ? 'disabled' : '' ?>>
                                <span class="checklist-text">ตรวจสอบรายละเอียดและการแนบรูปภาพประกอบของปัญหา</span>
                            </label>
                            <label class="checklist-item">
                                <input type="checkbox" class="checklist-checkbox" id="check3" <?= !isAdmin() ? 'disabled' : '' ?>>
                                <span class="checklist-text">ตรวจสอบรายการอะไหล่ ค่าแรง และยอดเคลมสุทธิว่าถูกต้องเหมาะสม</span>
                            </label>
                        </div>

                        <div class="mt-5">
                            <div class="row align-items-center mb-3">
                                <label class="col-sm-4 col-form-label fw-bold text-secondary">ลงชื่อผู้ตรวจสอบ</label>
                                <div class="col-sm-8">
                                    <input type="text" name="verifier" class="form-control pill-input bg-light" placeholder="ชื่อ-นามสกุล ผู้ตรวจสอบ" value="<?= htmlspecialchars($claim['verifier_name'] ?? $_SESSION['user_name'] ?? '') ?>" <?= isAdmin() ? 'required' : 'readonly' ?>>
                                </div>
                            </div>
                            <div class="row align-items-center">
                                <label class="col-sm-4 col-form-label fw-bold text-secondary">วันที่ตรวจสอบ</label>
                                <div class="col-sm-8">
                                    <input type="text" name="verify_date" class="form-control pill-input bg-light" placeholder="วว/ดด/ปปปป" value="<?= date('d/m/Y') ?>" <?= isAdmin() ? '' : 'readonly' ?>>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ฝั่งขวา: ผลการพิจารณาและหมายเหตุ -->
                    <div class="action-column d-flex flex-column">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <label class="form-label fw-bold text-secondary mb-0" style="min-width: 100px;">ผลพิจารณา</label>
                            <select name="status" class="form-select pill-select-orange w-100" required <?= !isAdmin() ? 'disabled' : '' ?>>
                                <option value="">--กรุณาเลือกผลการตรวจสอบ--</option>
                                <option value="Approved Claim" <?= $claim['status'] == 'Approved Claim' ? 'selected' : '' ?>>อนุมัติการเคลม</option>
                                <option value="Approved Replacement" <?= $claim['status'] == 'Approved Replacement' ? 'selected' : '' ?>>อนุมัติเปลี่ยนคัน</option>
                                <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>ไม่อนุมัติ</option>
                                <option value="Rejected" <?= $claim['status'] == 'Rejected' ? 'selected' : '' ?>>เปลี่ยน  </option>
                                <option value="Pending Fix" <?= $claim['status'] == 'Pending Fix' ? 'selected' : '' ?>>รอแก้ไข</option>
                                <option value="Pending" <?= $claim['status'] == 'Pending' ? 'selected' : '' ?>>ดำเนินการเสร็จสิ้น</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary mb-2">หมายเหตุ / ความเห็นผู้ตรวจสอบ</label>
                            <textarea name="verify_remarks" class="form-control pill-textarea" rows="5" placeholder="ระบุเหตุผล หากไม่อนุมัติหรือตีกลับ..." <?= !isAdmin() ? 'readonly' : '' ?>><?= htmlspecialchars($claim['verify_remarks'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-auto">
                            <a href="<?= isAdmin() ? 'check.php' : 'history.php' ?>" class="btn btn-pill-cancel px-4">ยกเลิก</a>
                            <?php if(isAdmin()): ?>
                            <button type="submit" id="btnSubmitVerify" class="btn btn-pill-save px-4">บันทึกผลการตรวจ</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
      </div> 
    </div>
  </div>

    <!-- Modal ขยายรูป -->
    <div class="modal-overlay" id="image-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); z-index:9999; justify-content:center; align-items:center; transition: all 0.3s ease;">
        <div style="position:absolute; top:20px; right:30px; color:white; font-size:45px; cursor:pointer;" onclick="closeImageModal()">×</div>
        <img src="" id="modal-img" style="max-width:90%; max-height:90%; object-fit:contain; border-radius: 12px; box-shadow: 0 5px 30px rgba(0,0,0,0.5);">
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // --- ฟังก์ชันขยายรูปภาพ ---
    function openImageModal(src) {
        const modal = document.getElementById('image-modal');
        const modalImg = document.getElementById('modal-img');
        if (modal && modalImg) {
            modalImg.src = src;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // ป้องกันการ Scroll หลังพื้นหลัง
        }
    }

    function closeImageModal() {
        const modal = document.getElementById('image-modal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // คืนค่าการ Scroll
        }
    }

    // ปิด Modal เมื่อคลิกพื้นหลัง
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('image-modal');
        if (e.target === modal) closeImageModal();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const verifyForm = document.getElementById('verifyForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const statusVal = document.querySelector('select[name="status"]').value;
                if(statusVal === '') {
                    if(typeof showToast === 'function') showToast('❌ กรุณาเลือกผลการตรวจสอบ', 'error');
                    else alert('กรุณาเลือกผลการตรวจสอบ');
                    return;
                }
                
                const submitBtn = document.getElementById('btnSubmitVerify');
                const originalText = submitBtn ? submitBtn.innerHTML : 'บันทึกผลการตรวจ';
                if (submitBtn) { submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> กำลังบันทึก...'; submitBtn.disabled = true; }

                fetch('../backend/verify_handler.php', { method: 'POST', body: new FormData(this) })
                .then(res => res.text())
                .then(text => {
                    if (text.includes('✅')) {
                        if(typeof showToast === 'function') showToast('✅ บันทึกผลการตรวจสอบเรียบร้อยแล้ว!', 'success');
                        else alert('บันทึกผลการตรวจสอบเรียบร้อยแล้ว!');
                        setTimeout(() => {
                            window.location.href = 'check.php'; 
                        }, 1500);
                    } else {
                        const cleanMsg = text.replace(/(<([^>]+)>)/gi, "");
                        if(typeof showToast === 'function') showToast('❌ ' + cleanMsg, 'error');
                        else alert('เกิดข้อผิดพลาด: ' + cleanMsg);
                        if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
                    }
                })
                .catch(err => {
                    if(typeof showToast === 'function') showToast('❌ เกิดข้อผิดพลาดในการเชื่อมต่อ!', 'error');
                    else alert('เกิดข้อผิดพลาดในการเชื่อมต่อ!');
                    if (submitBtn) { submitBtn.innerHTML = originalText; submitBtn.disabled = false; }
                });
            });
        }
    });
  </script>
</body>
</html>