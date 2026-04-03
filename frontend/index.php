<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../backend/index_handler.php';
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>ฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles.css"> 
  <link rel="stylesheet" href="../shared/assets/css/styles-claim_form.css"> 
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/styles-index_claim.css"> 
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <script src="../shared/assets/js/utils.js"></script>
  <style>
    :root {
      --primary-orange: #ff8533;
      --success-green: #00b050;
    }
    .section-card {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 2px 15px rgba(0,0,0,0.08); /* Slightly deeper shadow like mockup */
      border: 1px solid #eee;
    }
    .section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: #000;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-title i {
      display: none; /* Hide icon in title if mockup doesn't show it */
    }
    /* Note Box Styles */
    .note-box {
      background-color: #fff9f2;
      border: 1px solid #ffe8d1;
      border-radius: 10px;
      padding: 15px 20px;
      margin-top: 20px;
      font-size: 0.9rem;
      color: #333;
    }
    .note-box .note-header {
      font-weight: 800;
      margin-bottom: 8px;
    }
    .note-box ol {
      margin: 0;
      padding-left: 20px;
    }
    .note-box li {
      margin-bottom: 4px;
    }
    /* Button Styles */
    .btn-orange {
      background-color: var(--primary-orange);
      color: #fff;
      border: none;
      transition: all 0.2s;
    }
    .btn-orange:hover {
      background-color: #e6762d;
      color: #fff;
    }
    .btn-green {
      background-color: var(--success-green);
      color: #fff;
      border: none;
      transition: all 0.2s;
    }
    .btn-green:hover {
      background-color: #008f41;
      color: #fff;
    }
    /* Radio Pill Styling */
    .radio-pill-group {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }
    .radio-pill-group .form-check {
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 50px;
      padding: 8px 20px 8px 40px;
      min-width: 120px;
      cursor: pointer;
      position: relative;
    }
    .radio-pill-group .form-check:hover {
      border-color: var(--primary-orange);
    }
    .radio-pill-group .form-check-input {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      margin: 0;
    }
    .radio-pill-group .form-check:has(.form-check-input:checked) {
      border-color: var(--primary-orange);
      background-color: #fffaf7;
    }
    .image-uploader .upload-card {
      border: 1px solid #ddd;
      border-radius: 12px;
      padding: 20px;
      background: #fff;
    }
    .image-uploader .upload-placeholder {
      color: #000;
      font-weight: 600;
      font-size: 1.1rem;
    }
    .image-uploader .upload-placeholder svg, .image-uploader .upload-placeholder i {
      color: var(--primary-orange);
      margin-right: 15px;
      font-size: 2rem;
    }
    /* Parts Table Adjustments */
    #partsTable thead th {
      background-color: #f8f9fa;
      color: #000;
      font-weight: 700;
      border: none;
    }
    .btn-remove-part-orange:hover {
      background: #e6762d;
    }
    .attach-count {
      color: var(--primary-orange);
      font-weight: 700;
      font-size: 0.95rem;
      display: block;
      margin-top: 5px;
    }
  </style>
</head>
<body>
  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>
  <div class="main-content">
  <main class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold m-0" style="color: #000;"><i class="fas fa-file-invoice text-primary me-2"></i> บันทึกส่งเคลม</h4>
    </div>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <svg style="display:none" aria-hidden="true">
      <symbol id="icon-modern" viewBox="0 0 24 24"><path d="M4 7a2 2 0 0 1 2-2h2.2l1-1h5.6l1 1H18a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"/><circle cx="12" cy="13" r="3.2"/><rect x="7.5" y="8.5" width="2.5" height="1.8" rx="0.4"/></symbol>
      <symbol id="icon-delete" viewBox="0 0 24 24"><path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6v2H9V3z"/></symbol>
    </svg>

    <form id="claimForm" method="post" action="../backend/index_handler.php" enctype="multipart/form-data" novalidate>
      
      <!-- Section 1: ข้อมูลเบื้องต้น -->
      <div class="section-card">
        <div class="section-title"><i class="fas fa-info-circle"></i> ข้อมูลเบื้องต้น</div>
        <div class="claim-form-grid">
          <div class="form-row-item">
            <label for="branch" class="form-label">สาขา <span class="text-danger">*</span></label>
            <select id="branch" name="branch" class="form-select" required>
              <option value="">-- เลือกสาขา --</option>
            </select>
          </div>
          <div class="form-row-item">
            <label for="sale_date" class="form-label">วันที่ขายรถ</label>
            <input type="date" id="sale_date" name="sale_date" class="form-control">
          </div>
          <div class="form-row-item">
            <label for="claim_date" class="form-label">วันที่ส่งเคลม</label>
            <input type="date" id="claim_date" name="claim_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" readonly required>
          </div>
          <div class="form-row-item">
            <label class="form-label">อายุการใช้งาน</label>
            <input type="text" id="vehicle_age_display" class="form-control" placeholder="-- ปี -- เดือน -- วัน 0 ชั่วโมง" readonly style="background-color: #fff; color: #fd0000; font-weight: 600;">
          </div>
        </div>

        <div class="claim-form-grid-3 mt-3">
          <div class="form-row-item" style="grid-template-columns: 80px 1fr;">
            <label class="form-label">ประเภทรถ</label>
            <div class="d-flex gap-4 align-items-center h-100">
              <div class="form-check m-0">
                <input class="form-check-input" type="radio" name="car_type" id="car_type_new" value="new" checked required>
                <label class="form-check-label ms-1" for="car_type_new">รถใหม่</label>
              </div>
              <div class="form-check m-0">
                <input class="form-check-input" type="radio" name="car_type" id="car_type_used" value="used" required>
                <label class="form-check-label ms-1" for="car_type_used">มือสอง</label>
              </div>
            </div>
          </div>
          <div class="form-row-item" style="grid-template-columns: 60px 1fr;">
            <label for="car_brand" class="form-label">ยี่ห้อ <span class="text-danger">*</span></label>
            <select id="car_brand" name="car_brand" class="form-select" required>
              <option value="">-- เลือกยี่ห้อ --</option>
              <option>Honda</option>
              <option>Yamaha</option>
              <option>Vespa</option>
            </select>
          </div>
          <div id="used_grade_block" class="form-row-item d-none" style="grid-template-columns: 60px 1fr;">
            <label for="used_grade" class="form-label">เกรด</label>
            <select id="used_grade" name="used_grade" class="form-select bg-light">
              <option value="">-- เลือกเกรด --</option>
              <option value="A_premium">A พรีเมี่ยม</option>
              <option value="A_w6">A (6ด.)</option>
              <option value="C_w1">C (1ด.)</option>
              <option value="C_as_is">C (ตามสภาพ)</option>
            </select>
          </div>
        </div>

        <div class="claim-form-grid mt-3">
          <div class="form-row-item">
            <label for="vin" class="form-label">เลขตัวถัง <span class="text-danger">*</span></label>
            <input type="text" id="vin" name="vin" class="form-control" placeholder="VIN Number" required>
          </div>
          <div class="form-row-item">
            <label for="mileage" class="form-label">เลขไมล์รถ</label>
            <input type="number" id="mileage" name="mileage" class="form-control" placeholder="0" min="0">
          </div>
          <div class="form-row-item">
            <label for="owner_name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
            <input type="text" id="owner_name" name="owner_name" class="form-control" placeholder="ชื่อ นามสกุล" required>
          </div>
          <div class="form-row-item">
            <label for="owner_phone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
            <input type="text" id="owner_phone" name="owner_phone" class="form-control" placeholder="เบอร์โทรศัพท์" required>
          </div>
        </div>
      </div>

      <!-- Section 2: รายละเอียดปัญหา -->
      <div class="section-card">
        <div class="section-title"><i class="fas fa-exclamation-triangle"></i> รายละเอียดปัญหาและการตรวจเช็ค</div>
        <div class="mb-4">
          <label for="problem_desc" class="form-label fw-bold">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
          <textarea id="problem_desc" name="problem_desc" rows="3" class="form-control" placeholder="กรอกรายละเอียด..." required></textarea>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="inspect_method" class="form-label fw-bold text-center d-block">วิธีตรวจเช็ค :</label>
            <textarea id="inspect_method" name="inspect_method" rows="3" class="form-control" placeholder="วิธีตรวจเช็ค..." required></textarea>
          </div>
          <div class="col-md-6 mb-3">
            <label for="inspect_cause" class="form-label fw-bold text-center d-block">สาเหตุของปัญหา :</label>
            <textarea id="inspect_cause" name="inspect_cause" rows="3" class="form-control" placeholder="สาเหตุของปัญหา..." required></textarea>
          </div>
        </div>

        <!-- Section: หมายเหตุ (Mockup 1) -->
        <div class="note-box">
          <div class="note-header">***หมายเหตุ :</div>
          <ol>
            <li>รถมือสองมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117 หรือ 042-71135 ต่อ 201</li>
            <li>รถใหม่มีปัญหาปรึกษาศูนย์บริการ Honda 086-4594656 Yamaha 086-4550614 Vespa 099-1285556</li>
          </ol>
        </div>
      </div>

      <!-- Section 3: รูปภาพประกอบ -->
      <div class="section-card">
        <div class="section-title"><i class="fas fa-camera"></i> แนบรูปภาพปัญหา</div>
        <div class="image-uploader">
          <div class="image-gallery" id="imageGallery">
            <?php 
            $fields = [
              ['id' => 'imgFullCar', 'label' => 'ภาพรถทั้งคัน'],
              ['id' => 'imgSpot', 'label' => 'ภาพจุดปัญหา'],
              ['id' => 'imgPart', 'label' => 'ภาพชิ้นส่วน'],
              ['id' => 'imgWarranty', 'label' => 'ภาพสมุดรับประกัน'],
              ['id' => 'imgOdometer', 'label' => 'ภาพเลขไมล์'],
              ['id' => 'imgEstimate', 'label' => 'ภาพใบประเมิน']
            ];
            foreach($fields as $f): ?>
            <div class="upload-card" data-field="<?= $f['id'] ?>">
              <div class="drop-area">
                <div class="upload-placeholder"><i class="fa-solid fa-square"></i> <?= $f['label'] ?></div>
                <div class="small text-muted mt-1">คลิกหรือวางรูปที่นี่</div>
                <span class="attach-count"></span>
              </div>
              <input type="file" name="<?= $f['id'] ?>[]" accept="image/*" multiple style="display:none">
              <div class="preview"></div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Section 4: การดำเนินการ -->
      <div class="section-card">
        <div class="section-title"><i class="fas fa-tools"></i> ประเภทการเคลมและการดำเนินการ</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label for="claim_category" class="form-label fw-bold">ประเภทการเคลม <span class="text-danger">*</span></label>
            <select id="claim_category" name="claim_category" class="form-select" required>
              <option value="">-- เลือกประเภทการเคลม --</option>
              <option value="เคลมรถก่อนขาย">เคลมรถก่อนขาย</option>
              <option value="เคลมปัญหาทางเทคนิค">เคลมปัญหาทางเทคนิค</option>
              <option value="เคลมรถลูกค้า">เคลมรถลูกค้า</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">การดำเนินการ</label>
            <div class="radio-pill-group mt-1">
              <div class="form-check"><input class="form-check-input" type="radio" name="claim_action" id="claim_repair" value="repairBranch" required><label class="form-check-label ms-1" for="claim_repair">ซ่อมที่สาขา</label></div>
              <div class="form-check"><input class="form-check-input" type="radio" name="claim_action" id="claim_send" value="sendHQ"><label class="form-check-label ms-1" for="claim_send">ส่งซ่อมที่สนญ.</label></div>
              <div class="form-check"><input class="form-check-input" type="radio" name="claim_action" id="claim_replace" value="replaceVehicle"><label class="form-check-label ms-1" for="claim_replace">เปลี่ยนคัน</label></div>
              <div class="form-check"><input class="form-check-input" type="radio" name="claim_action" id="claim_other" value="other"><label class="form-check-label ms-1" for="claim_other">อื่นๆ</label></div>
            </div>
            <input type="text" id="claim_other_text" name="claim_other_text" class="form-control mt-2 d-none" placeholder="ระบุอื่นๆ">
          </div>
        </div>

        <div id="partsSectionTitle" class="d-none mt-4">
          <h5 class="fw-bold" style="color: #000; font-size: 1.1rem;">ระบุรายการอะไหล่ ที่ต้องการเคลม/จำนวน</h5>
        </div>

        <!-- รายการอะไหล่ (สำหรับซ่อม) -->
        <div id="partsSection" class="d-none mt-4 border-top pt-4">
          <div class="d-flex justify-content-center align-items-center mb-3">
            <div class="gap-3 d-flex">
              <button type="button" id="addPart" class="btn btn-orange rounded-pill px-4">+ เพิ่มรายการ</button>
              <button type="button" id="btnUploadParts" class="btn btn-green rounded-pill px-4">+ อัปโหลดรูปภาพ</button>
            </div>
          </div>
          <div class="table-responsive">
            <table id="partsTable" class="table table-bordered align-middle">
              <thead class="table-light"><tr><th width="60">ลำดับ</th><th>รหัสอะไหล่</th><th>ชื่ออะไหล่</th><th width="100">จำนวน</th><th width="150">ราคา/หน่วย</th><th>หมายเหตุ</th><th width="50"></th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
          <input type="file" id="imgPartsUpload" name="imgParts[]" accept="image/*" multiple style="display:none">
          <div id="partsImgPreview" class="d-flex flex-wrap gap-2 mt-3"></div>

          <div id="partsDeliverySection" class="mt-4 p-3 border rounded-3 bg-white">
            <label class="fw-bold text-dark mb-2">ประเภทการส่ง อะไหล่</label>
            <div class="radio-pill-group mt-1">
              <div class="form-check"><input class="form-check-input" type="radio" name="parts_delivery" value="in_stock" checked id="pd_stock"><label class="form-check-label ms-1" for="pd_stock">ใช้อะไหล่ ที่มีในสต็อกสาขา</label></div>
              <div class="form-check"><input class="form-check-input" type="radio" name="parts_delivery" value="wait_hq" id="pd_hq"><label class="form-check-label ms-1" for="pd_hq">รอส่งอะไหล่ จากสนญ.</label></div>
              <div class="form-check"><input class="form-check-input" type="radio" name="parts_delivery" value="buy_outside" id="pd_outside"><label class="form-check-label ms-1" for="pd_outside">ซื้ออะไหล่ร้านนอก</label></div>
              <div class="form-check"><input class="form-check-input" type="radio" name="parts_delivery" value="other" id="pd_other"><label class="form-check-label ms-1" for="pd_other">อื่นๆ</label></div>
            </div>
            <input type="text" id="parts_delivery_other_text" name="parts_delivery_other_text" class="form-control mt-2 d-none" placeholder="ระบุการจัดซื้อภายนอกหรืออื่นๆ">
          </div>

          <div id="approverSection" class="mt-4 p-3 border-start border-primary border-4 bg-primary-subtle rounded">
            <h6 class="fw-bold"><i class="fas fa-user-check me-2"></i> ผู้อนุมัติการดำเนินการ (ภายในสาขา)</h6>
            <div class="row g-3 mt-1">
              <div class="col-md-4"><label class="form-label small">รหัสพนักงาน</label><select name="approver_id" class="form-select employee-select" data-target-name="approver_name" data-target-sig="approver_signature"><option value="">-- เลือก --</option></select></div>
              <div class="col-md-4"><label class="form-label small">ชื่อผู้อนุมัติ</label><input type="text" name="approver_name" class="form-control bg-white" readonly></div>
              <div class="col-md-4"><label class="form-label small">ลายเซ็นต์</label><input type="text" name="approver_signature" class="form-control bg-white" readonly></div>
            </div>
          </div>
        </div>

        <!-- รายละเอียดเปลี่ยนคันใหม่ (สำหรับเปลี่ยนคัน) -->
        <div id="replaceBlock" class="d-none mt-4 border-top pt-4">
          <h6 class="fw-bold mb-4 text-primary"><i class="fas fa-sync-alt me-2"></i> ข้อมูลรถคันใหม่ที่ส่งมอบทดแทน</h6>
          <div class="row g-3 mb-4">
            <div class="col-md-6"><label class="form-label">ยอดดาวน์รถคันเก่า</label><input type="number" name="old_down_balance" class="form-control" placeholder="0.00"></div>
            <div class="col-md-6"><label class="form-label">ยอดดาวน์รถคันใหม่</label><input type="number" name="new_down_balance" class="form-control" placeholder="0.00"></div>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-4">
              <label class="form-label">ประเภทรถใหม่</label>
              <div class="d-flex gap-3 mt-1">
                <div class="form-check"><input class="form-check-input replace-car-type" type="radio" name="replace_type" value="รถใหม่" checked><label class="form-check-label">รถใหม่</label></div>
                <div class="form-check"><input class="form-check-input replace-car-type" type="radio" name="replace_type" value="รถมือสอง"><label class="form-check-label">รถมือสอง</label></div>
              </div>
            </div>
            <div id="replaceGradeSection" class="col-md-8 d-none">
              <label class="form-label">เกรดรถมือสอง</label>
              <select name="replace_used_grade" class="form-select">
                <option value="">-- เลือกเกรด --</option>
                <option value="A_premium">A พรีเมี่ยม</option>
                <option value="A_w6">A (6ด.)</option>
                <option value="C_w1">C (1ด.)</option>
                <option value="C_as_is">C (ตามสภาพ)</option>
              </select>
            </div>
          </div>
          <div class="row g-3 mb-4">
            <div class="col-md-4"><label class="form-label">รุ่น</label><input type="text" name="replace_model" class="form-control" placeholder="ระบุรุ่น"></div>
            <div class="col-md-4"><label class="form-label">สี</label><input type="text" name="replace_color" class="form-control" placeholder="ระบุสี"></div>
            <div class="col-md-4"><label class="form-label">เลขตัวถัง</label><input type="text" name="replace_vin" class="form-control" placeholder="VIN ของคันใหม่"></div>
          </div>
          <div class="mb-4"><label class="form-label fw-bold">สาเหตุที่เปลี่ยนคัน <span class="text-danger">*</span></label><textarea name="replace_reason" class="form-control" rows="2" placeholder="เหตุผลและความจำเป็นในการเปลี่ยนรถคันใหม่"></textarea></div>
          <div class="row g-3 p-3 bg-primary-subtle rounded border-start border-primary border-4 mt-2">
            <h6 class="fw-bold col-12 m-0">ผู้อนุมัติการเปลี่ยนคัน</h6>
            <div class="col-md-4"><label class="form-label small">รหัสพนักงาน</label><select name="replace_id" class="form-select employee-select" data-target-name="replace_name" data-target-sig="replace_signature"><option value="">-- เลือก --</option></select></div>
            <div class="col-md-4"><label class="form-label small">ชื่อผู้อนุมัติ</label><input type="text" name="replace_name" class="form-control" readonly></div>
            <div class="col-md-4"><label class="form-label small">วันที่อนุมัติ</label><input type="date" name="replace_approve_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
          </div>
        </div>
      </div>

      <!-- Footer & Submit -->
      <div class="section-card">
        <div class="row align-items-center">
          <div class="col-md-6 d-flex align-items-center gap-3">
            <label class="form-label fw-bold m-0 text-nowrap">ผู้บันทึกส่งเคลม</label>
            <input type="text" name="recorder" class="form-control bg-light" required placeholder="ชื่อ-นามสกุล ผูบันทึก" style="max-width: 300px;">
          </div>
          <div class="col-md-6 d-flex align-items-end justify-content-end gap-2 pt-3 pt-md-0">
            <button type="submit" class="btn btn-orange px-5 py-2 fw-bold shadow-sm rounded-3">บันทึกการส่งเคลม</button>
            <button type="reset" class="btn btn-outline-secondary px-4 py-2 rounded-3 bg-white text-dark">รีเซ็ต</button>
          </div>
        </div>
      </div>
      <div id="result" class="mt-4" style="display:none"></div>
    </form>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  // Initialize dynamic data
  if(window.PJUtils){ 
    PJUtils.loadBranches('branch'); 
    PJUtils.loadEmployees(); 
  }

  // Auto-calculate age
  const saleDateIn = document.getElementById('sale_date');
  const ageOut = document.getElementById('vehicle_age_display');
  const updateAge = () => { 
    if(saleDateIn && ageOut && window.PJUtils) {
      ageOut.value = PJUtils.calculateAge(saleDateIn.value) || '-- ปี -- เดือน -- วัน 0 ชั่วโมง';
    }
  };
  if(saleDateIn) saleDateIn.onchange = updateAge;
  updateAge();
  setInterval(updateAge, 60000);

  const form = document.getElementById('claimForm');
  const gallery = document.getElementById('imageGallery');
  const result = document.getElementById('result');
  const filesMap = {};

  // Form interactivity
  document.querySelectorAll('input[name="car_type"]').forEach(r => r.onchange = () => document.getElementById('used_grade_block').classList.toggle('d-none', r.value !== 'used'));
  
  document.querySelectorAll('input[name="claim_action"]').forEach(r => r.onchange = () => {
    const val = r.value;
    const isRepair = ['repairBranch','sendHQ'].includes(val);
    document.getElementById('partsSection').classList.toggle('d-none', !isRepair);
    if(document.getElementById('partsSectionTitle')) {
      document.getElementById('partsSectionTitle').classList.toggle('d-none', !isRepair);
    }
    document.getElementById('replaceBlock').classList.toggle('d-none', val !== 'replaceVehicle');
    document.getElementById('claim_other_text').classList.toggle('d-none', val !== 'other');
  });

  document.querySelectorAll('input[name="parts_delivery"]').forEach(r => r.onchange = () => {
    document.getElementById('parts_delivery_other_text').classList.toggle('d-none', r.value !== 'other');
  });

  document.querySelectorAll('.replace-car-type').forEach(r => r.onchange = () => {
    document.getElementById('replaceGradeSection').classList.toggle('d-none', r.value !== 'รถมือสอง');
  });

  // Parts Table Logic
  const partsTbody = document.querySelector('#partsTable tbody');
  const addRow = (d={}) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="idx text-center fw-bold"></td>
      <td><input type="text" name="parts_code[]" class="form-control bg-light" value="${d.code||''}" placeholder="รหัสอะไหล่"></td>
      <td><input type="text" name="parts_name[]" class="form-control bg-light" value="${d.name||''}" placeholder="ชื่ออะไหล่"></td>
      <td><input type="number" name="parts_qty[]" class="form-control bg-light text-center" value="${d.qty||1}"></td>
      <td><input type="number" name="parts_price[]" class="form-control bg-light text-center" value="${d.price||''}" placeholder="0.00" step="0.01"></td>
      <td><input type="text" name="parts_note[]" class="form-control bg-light" value="${d.note||''}" placeholder="หมายเหตุ"></td>
      <td class="text-center"><button type="button" class="btn btn-remove-part-orange remove"><i class="fas fa-trash-alt"></i></button></td>
    `;
    tr.querySelector('.remove').onclick = () => { tr.remove(); reindex(); };
    partsTbody.appendChild(tr); reindex();
  };
  const reindex = () => Array.from(partsTbody.rows).forEach((r,i)=>r.cells[0].innerText = i+1);
  document.getElementById('addPart').onclick = () => addRow();
  // Initial rows
  if(partsTbody.rows.length === 0) for(let i=0;i<3;i++) addRow();

  // Images Logic
  gallery.querySelectorAll('.upload-card').forEach(card => {
    const field = card.dataset.field;
    const inp = card.querySelector('input[type=file]');
    filesMap[field] = [];
    // Initial count display
    const countSpan = card.querySelector('.attach-count');
    if(countSpan) countSpan.innerText = '( 0 รูป )';
    
    card.onclick = (e) => { if(e.target.tagName !== 'INPUT' && !e.target.closest('.preview')) inp.click(); };
    inp.onchange = () => { 
      filesMap[field] = inp.multiple ? [...filesMap[field], ...inp.files] : [...inp.files]; 
      render(field); 
      inp.value=''; 
    };
  });

  const render = (f) => {
    const card = gallery.querySelector(`[data-field="${f}"]`);
    const pre = card.querySelector('.preview');
    pre.innerHTML = '';
    filesMap[f].forEach((file, i) => {
      const div = document.createElement('div');
      div.className = 'thumb';
      div.style.cssText = 'position:relative;width:60px;height:60px;margin:2px;border-radius:4px;overflow:hidden;border:1px solid #ddd;';
      const img = document.createElement('img');
      img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
      const reader = new FileReader(); reader.onload = e => img.src = e.target.result; reader.readAsDataURL(file);
      const btn = document.createElement('button'); 
      btn.innerHTML = '&times;'; 
      btn.style.cssText = 'position:absolute;top:0;right:0;background:rgba(255,0,0,0.7);color:white;border:none;width:18px;height:18px;font-size:12px;line-height:1;';
      btn.onclick = (e) => { e.stopPropagation(); filesMap[f].splice(i,1); render(f); };
      div.append(img, btn); pre.append(div);
    });
    const countSpan = card.querySelector('.attach-count');
    if(countSpan) {
      countSpan.innerText = `( ${filesMap[f].length} รูป )`;
    }
  };

  const partInp = document.getElementById('imgPartsUpload');
  filesMap['imgParts[]'] = [];
  document.getElementById('btnUploadParts').onclick = () => partInp.click();
  partInp.onchange = () => { filesMap['imgParts[]'] = [...filesMap['imgParts[]'], ...partInp.files]; renderParts(); partInp.value=''; };
  const renderParts = () => {
    const pre = document.getElementById('partsImgPreview');
    pre.innerHTML = '';
    filesMap['imgParts[]'].forEach((file, i) => {
      const div = document.createElement('div');
      div.style.cssText = 'position:relative;width:80px;height:80px;border-radius:8px;overflow:hidden;border:1px solid #ddd;';
      const img = document.createElement('img'); img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
      const reader = new FileReader(); reader.onload = e => img.src = e.target.result; reader.readAsDataURL(file);
      const btn = document.createElement('button'); 
      btn.innerHTML = '&times;'; 
      btn.style.cssText = 'position:absolute;top:2px;right:2px;background:red;color:white;border:none;border-radius:50%;width:20px;height:20px;';
      btn.onclick = () => { filesMap['imgParts[]'].splice(i,1); renderParts(); };
      div.append(img, btn); pre.append(div);
    });
    const btn = document.getElementById('btnUploadParts');
    btn.innerHTML = filesMap['imgParts[]'].length ? `<i class="fas fa-check me-1"></i> อัปโหลดแล้ว ${filesMap['imgParts[]'].length} รูป` : `<i class="fas fa-upload me-1"></i> อัปโหลดรูปอะไหล่`;
  };

  // Form Submit Logic
  form.onsubmit = function(e){
    e.preventDefault();
    if(!this.checkValidity()){ this.reportValidity(); return; }
    
    result.style.display = 'block'; 
    result.innerHTML = '<div class="alert alert-warning"><i class="fas fa-spinner fa-spin me-2"></i> กำลังประมวลผลข้อมูลและอัปโหลดไฟล์... กรุณารอสักครู่</div>';
    
    const fd = new FormData(this);
    // Add custom images from filesMap
    Object.keys(filesMap).forEach(k => filesMap[k].forEach(f => fd.append(k, f)));

    fetch(this.action, { method:'POST', body:fd })
    .then(r => r.text())
    .then(t => {
      result.innerHTML = t;
      if(t.includes('✅')){
        // Reset form and UI
        this.reset(); 
        Object.keys(filesMap).forEach(k => filesMap[k] = []);
        document.querySelectorAll('.preview').forEach(p => p.innerHTML='');
        document.getElementById('partsImgPreview').innerHTML = '';
        updateAge();
        window.scrollTo({ top: 0, behavior: 'smooth' });
      } else {
        window.scrollTo({ top: result.offsetTop - 100, behavior: 'smooth' });
      }
    })
    .catch(err => {
      result.innerHTML = `<div class="alert alert-danger">❌ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ${err.message}</div>`;
    });
  };
})();
</script>
</body>
</html>