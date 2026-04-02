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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-dxz0OFf2LjA5efXKwBlenuMxS9IIrLs+1E1iY1p6RhJHciAPxsBHo/djC6AmlL0I" crossorigin="anonymous">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles.css"> 
  <link rel="stylesheet" href="../shared/assets/css/styles-claim_form.css"> 
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/styles-index_claim.css"> 
</head>
<body>
  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>
  <div class="main-content">
  <main class="container">
    <?php if (!empty($message)): ?>
      <div class="card" style="margin-bottom:12px;padding:10px;background:linear-gradient(90deg,#ff6a00,#ff8f3d);color:#fff;border-radius:8px"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <svg style="display:none" aria-hidden="true">
      <symbol id="icon-claim" viewBox="0 0 24 24"><path d="M6 2h9a2 2 0 0 1 2 2v3h-2V4H6v16h6v2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/></symbol>
      <symbol id="icon-image" viewBox="0 0 24 24">
        <rect x="2" y="5" width="20" height="14" rx="3"/>
        <path d="M8 13.5l3-3 4 5H6l2-2.5z" fill="#fff" opacity="0.08"/>
        <circle cx="12" cy="12" r="3.2"/>
      </symbol>
      <symbol id="icon-modern" viewBox="0 0 24 24">
        <path d="M4 7a2 2 0 0 1 2-2h2.2l1-1h5.6l1 1H18a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z"/>
        <circle cx="12" cy="13" r="3.2"/>
        <rect x="7.5" y="8.5" width="2.5" height="1.8" rx="0.4"/>
      </symbol>
      <symbol id="icon-upload" viewBox="0 0 24 24"><path d="M12 3l4 4h-3v6h-2V7H8l4-4zM5 19h14v2H5v-2z"/></symbol>
      <symbol id="icon-add" viewBox="0 0 24 24"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2z"/></symbol>
      <symbol id="icon-delete" viewBox="0 0 24 24"><path d="M6 7h12v13a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2V7zm3-4h6v2H9V3z"/></symbol>
      <symbol id="icon-close" viewBox="0 0 24 24"><path d="M18.3 5.7L12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3 10.6 10.6 16.9 4.3z"/></symbol>
    </svg>

    <form id="claimForm" class="card p-4" method="post" action="../backend/index_handler.php" enctype="multipart/form-data" novalidate>
      <div class="container-fluid">
        <div class="tab-content" id="claimFormTabContent">
          <div class="tab-pane fade show active" id="tab-basic" role="tabpanel" aria-labelledby="tab-basic-tab">
            <div class="claim-form-grid">
              <!-- Row 1: Branch & Claim Date -->
              <div class="form-row-item">
                <label for="branch" class="form-label">สาขา</label>
                <select id="branch" name="branch" class="form-select" required>
                  <option value="">-- เลือกสาขา --</option>
                  <option>สาขา สกลนคร</option>
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

              <!-- Row 2: Sale Date & Vehicle Age -->
              <div class="form-row-item">
                <label class="form-label">อายุการใช้งาน</label>
                <input type="text" id="vehicle_age_display" class="form-control" placeholder="-- ปี -- เดือน -- วัน 0 ชั่วโมง" readonly style="background-color: #ffffff; color: #fd0000; font-weight: 600;">
              </div>
            </div>

            <!-- Row 3: Car Details (Pills/Radios + Selects) - Uses 3 columns grid -->
            <div class="claim-form-grid-3">
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
                <label for="car_brand" class="form-label">ยี่ห้อ</label>
                <select id="car_brand" name="car_brand" class="form-select" required>
                  <option value="">-- เลือกยี่ห้อ --</option>
                  <option>Honda</option>
                  <option>Yamaha</option>
                  <option>Vespa</option>
                </select>
              </div>

              <div id="used_grade_block" class="form-row-item d-none" style="grid-template-columns: 60px 1fr;">
                <label for="used_grade" class="form-label">เกรด</label>
                <select id="used_grade" name="used_grade" class="form-select">
                  <option value="">-- เลือกเกรด --</option>
                  <option value="A_premium">A พรีเมี่ยม</option>
                  <option value="A_w6">A (6ด.)</option>
                  <option value="C_w1">C (1ด.)</option>
                  <option value="C_as_is">C (ตามสภาพ)</option>
                </select>
              </div>
            </div>

            <div class="claim-form-grid">
              <!-- Primary Identification -->
              <div class="form-row-item">
                <label for="vin" class="form-label">เลขตัวถัง <span class="text-danger">*</span></label>
                <input type="text" id="vin" name="vin" class="form-control" placeholder="VIN Number" required>
              </div>
              <div class="form-row-item">
                <label for="mileage" class="form-label">เลขไมล์รถ</label>
                <input type="number" id="mileage" name="mileage" class="form-control" placeholder="0" min="0">
              </div>

              <!-- Customer Info -->
              <div class="form-row-item">
                <label for="owner_name" class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                <input type="text" id="owner_name" name="owner_name" class="form-control" placeholder="ชื่อ นามสกุล" required>
              </div>

              <div class="form-row-item">
                <label for="owner_phone" class="form-label">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                <input type="text" id="owner_phone" name="owner_phone" class="form-control" placeholder="เบอร์โทรศัพท์" required>
              </div>
            </div>

            <div class="row g-3 mt-2">
              <div class="col-12">
                <h5 class="fw-bold mb-3" style="color: #222; font-size: 1rem;">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</h5>
                <div class="mb-3">
                  <textarea id="problem_desc" name="problem_desc" rows="4" class="form-control" placeholder="อธิบายปัญหาที่ลูกค้าแจ้ง" required></textarea>
                </div>
              </div>
            </div>

            <div class="mt-4">
              <h5 class="fw-bold mb-3" style="color: #222; font-size: 1rem;">ผลการตรวจเช็คปัญหา :</h5>
              <div class="mb-3">
                <label for="inspect_method" class="form-label fw-bold mb-2">วิธีตรวจเช็ค</label>
                <textarea id="inspect_method" name="inspect_method" rows="3" class="form-control" placeholder="วิธีการตรวจเช็คปัญหา" required></textarea>
              </div>
              <div class="mb-3">
                <label for="inspect_cause" class="form-label fw-bold mb-2">สาเหตุของปัญหา</label>
                <textarea id="inspect_cause" name="inspect_cause" rows="3" class="form-control" placeholder="สาเหตุของปัญหา" required></textarea>
              </div>
            </div>

            <div class="mt-4">
              <div class="alert alert-info mb-0" style="background-color: #fff6f6; border-left: 4px solid var(--primary-orange); font-size: 0.95rem;">
                <strong>***หมายเหตุ :</strong>
                <ol style="margin: 6px 0 0 18px; padding: 0;">
                  <li>รถมือสองมีปัญหาปรึกษาช่างมือสอง เบอร์โทรศัพท์ พี่สีเมือง 061-0190011 พี่บัว 093-3197117 หรือ 042-71135 ต่อ 201</li>
                  <li>รถใหม่มีปัญหาปรึกษาศูนย์บริการ Honda 086-4594656 Yamaha 086-4550614 Vespa 099-1285556</li>
                </ol>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-images" role="tabpanel" aria-labelledby="tab-images-tab">
            <fieldset class="full images">
              <legend>แนบรูปภาพปัญหา :</legend>
              <div class="image-uploader">
                <div class="image-gallery" id="imageGallery">
                  <div class="upload-card" data-field="imgFullCar">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพรถทั้งคัน</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgFullCar" name="imgFullCar[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgFullCar"></div>
                  </div>

                  <div class="upload-card" data-field="imgSpot">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพจุดที่เกิดปัญหา</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgSpot" name="imgSpot[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgSpot"></div>
                  </div>

                  <div class="upload-card" data-field="imgPart">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพชิ้นส่วนที่เกิดความเสียหาย</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgPart" name="imgPart[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgPart"></div>
                  </div>

                  <div class="upload-card" data-field="imgWarranty">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพสมุดรับประกันที่มีประวัติ</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgWarranty" name="imgWarranty[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgWarranty"></div>
                  </div>

                  <div class="upload-card" data-field="imgOdometer">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพเลขไมล์</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgOdometer" name="imgOdometer[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgOdometer"></div>
                  </div>

                  <div class="upload-card" data-field="imgEstimate">
                    <div class="drop-area">
                      <div class="upload-placeholder"><svg class="icon" aria-hidden="true"><use xlink:href="#icon-modern"></use></svg>ภาพใบประเมินรายการอะไหล่</div>
                      <div class="upload-hint">รองรับหลายรูป</div>
                      <span class="attach-count" aria-hidden="true">0</span>
                    </div>
                    <input type="file" id="imgEstimate" name="imgEstimate[]" accept="image/*" multiple>
                    <div class="preview" data-target="imgEstimate"></div>
                  </div>
                </div>
              </div>
            </fieldset>
          </div>

          <div class="tab-pane fade" id="tab-claim" role="tabpanel" aria-labelledby="tab-claim-tab">
            <div class="row g-3 mt-2">
              <div class="col-12">
                <h5 class="fw-bold mb-3" style="color: #222; font-size: 1rem;">ประเภทการเคลม :</h5>
              </div>
              <div class="col-12 col-md-6">
                <div class="mb-3">
                  <label for="claim_category" class="form-label fw-semibold">เลือกประเภท</label>
                  <select id="claim_category" name="claim_category" class="form-select">
                    <option value="">-- เลือกประเภทการเคลม --</option>
                    <option value="เคลมรถก่อนขาย">เคลมรถก่อนขาย</option>
                    <option value="เคลมปัญหาทางเทคนิค">เคลมปัญหาทางเทคนิค</option>
                    <option value="เคลมรถลูกค้า">เคลมรถลูกค้า</option>
                  </select>
                </div>
              </div>
              
              <div class="col-12">
                <div class="mb-3">
                  <label class="form-label fw-semibold">การดำเนินการ</label>
                  <div class="d-flex flex-wrap gap-2">
                    <?php if (hasTag('repairBranch')): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claim_action" id="claim_repair" value="repairBranch">
                      <label class="form-check-label" for="claim_repair">ซ่อมที่สาขา</label>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (hasTag('sendHQ')): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claim_action" id="claim_send" value="sendHQ">
                      <label class="form-check-label" for="claim_send">ส่งซ่อมที่สนญ.</label>
                    </div>
                    <?php endif; ?>

                    <?php if (hasTag('replaceVehicle')): ?>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claim_action" id="claim_replace" value="replaceVehicle">
                      <label class="form-check-label" for="claim_replace">เปลี่ยนคัน</label>
                    </div>
                    <?php endif; ?>

                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="claim_action" id="claim_other" value="other">
                      <label class="form-check-label" for="claim_other">อื่นๆ</label>
                    </div>
                  </div>
                  <input type="text" id="claim_other_text" name="claim_other_text" class="form-control mt-2 d-none" placeholder="ระบุอื่นๆ">
                </div>
              </div>
            </div>

            <section id="partsSection" class="d-none mt-4">
              <div class="row g-3">
                <div class="col-12">
                  <h5 class="fw-bold" style="color: #222; font-size: 1rem;">ระบุรายการอะไหล่ ที่ต้องการเคลม/จำนวน</h5>
                </div>
                <div class="col-12">
                  <div class="table-responsive">
                    <table id="partsTable" class="table table-hover">
                      <thead class="table-light">
                        <tr>
                          <th style="width:48px">ลำดับ</th>
                          <th style="width:140px">รหัสอะไหล่</th>
                          <th>ชื่ออะไหล่</th>
                          <th style="width:96px">จำนวน</th>
                          <th style="width:120px">ราคา</th>
                          <th style="width:160px">หมายเหตุ</th>
                          <th style="width:72px">จัดการ</th>
                        </tr>
                      </thead>
                      <tbody></tbody>
                    </table>
                  </div>
                  <div class="3" style="width: 100%;">
                    <div></div>
                    <button type="button" id="addPart" class="btn-parts-add px-4" style="border-radius:20px; font-weight: 600;">
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                      เพิ่มรายการ
                    </button>
                    <button type="button" id="btnUploadParts" class="btn-parts-upload px-3" style="border-radius:20px;">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                      อัปโหลดรูปภาพ
                    </button>
                    <input type="file" id="imgPartsUpload" name="imgParts[]" accept="image/*" multiple style="position:absolute;width:1px;height:1px;opacity:0;pointer-events:none;">
                  </div>
                  <div id="partsImgPreview" class="parts-img-preview" style="display: flex; flex-wrap: wrap; gap: 12px; margin-top: 15px;"></div>
                </div>

                <div id="approverSection" class="section-sub-card d-none mt-4" style="border-top: 4px solid var(--primary-orange);">
                  <div class="section-sub-title"><i class="fas fa-signature"></i> ผู้อนุมัติการดำเนินการ</div>
                  <div class="claim-form-grid-3">
                    <div class="form-row-item" style="grid-template-columns: 100px 1fr;">
                      <label class="icon-label"><i class="fas fa-id-badge"></i> รหัสพนักงาน</label>
                      <select name="approver_id" class="form-select employee-select border-primary-subtle" data-target-name="approver_name" data-target-sig="approver_signature">
                        <option value="">-- เลือกพนักงาน --</option>
                      </select>
                    </div>
                    <div class="form-row-item" style="grid-template-columns: 80px 1fr;">
                      <label class="icon-label"><i class="fas fa-user-check"></i> ชื่อผู้อนุมัติ</label>
                      <input type="text" name="approver_name" class="form-control bg-light" readonly placeholder="ชื่อพนักงาน">
                    </div>
                    <div class="form-row-item" style="grid-template-columns: 80px 1fr;">
                      <label class="icon-label"><i class="fas fa-pen-nib"></i> ลายเซ็นต์</label>
                      <input type="text" name="approver_signature" class="form-control bg-light" readonly placeholder="ลายเซ็นต์">
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <div id="partsDeliverySection" class="d-none mt-4">
              <div class="row g-3">
                <div class="col-12">
                  <h5 class="fw-bold" style="color: #222; font-size: 1rem;">ประเภทการส่ง อะไหล่</h5>
                </div>
                <div class="col-12">
                  <div class="d-flex flex-wrap gap-3">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="parts_delivery" id="partsDelivery_stock" value="in_stock" checked>
                      <label class="form-check-label" for="partsDelivery_stock">ซ่อมที่สาขา</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="parts_delivery" id="partsDelivery_hq" value="wait_hq">
                      <label class="form-check-label" for="partsDelivery_hq">รอส่งอะไหล่ จากสนญ.</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="parts_delivery" id="partsDelivery_buy" value="buy_outside">
                      <label class="form-check-label" for="partsDelivery_buy">ซื้ออะไหล่ร้านนอก</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="parts_delivery" id="partsDeliveryOtherRadio" value="other">
                      <label class="form-check-label" for="partsDeliveryOtherRadio">อื่นๆ</label>
                    </div>
                  </div>
                  <input type="text" id="parts_delivery_other_text" name="parts_delivery_other_text" class="form-control mt-2 d-none" placeholder="ระบุอื่นๆ">
                </div>
              </div>
            </div>
          </div>

          <div class="tab-pane fade" id="tab-replace" role="tabpanel" aria-labelledby="tab-replace-tab">
            <div id="replaceBlock" class="card d-none mt-4 p-4 shadow-sm border-0">
              <h5 class="fw-bold mb-4" style="color: var(--primary-orange);"><i class="fas fa-sync-alt me-2"></i> รายละเอียดการเปลี่ยนคันใหม่</h5>
              
              <!-- Group 1: Financial Details -->
              <div class="section-sub-card mb-4">
                <div class="section-sub-title"><i class="fas fa-wallet"></i> ยอดเงินคงเหลือ (เงินดาวน์)</div>
                <div class="claim-form-grid">
                  <div class="form-row-item">
                    <label class="icon-label"><i class="fas fa-car"></i> รถคันเก่า</label>
                    <div class="input-group money-input-group">
                      <input type="number" class="form-control" name="old_down_balance" placeholder="0.00" step="0.01" min="0">
                      <span class="input-group-text">บาท</span>
                    </div>
                  </div>
                  <div class="form-row-item">
                    <label class="icon-label"><i class="fas fa-star text-success"></i> รถคันใหม่</label>
                    <div class="input-group money-input-group">
                      <input type="number" class="form-control" name="new_down_balance" placeholder="0.00" step="0.01" min="0">
                      <span class="input-group-text">บาท</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Group 2: Vehicle Specs -->
              <div class="section-sub-card mb-4">
                <div class="section-sub-title"><i class="fas fa-info-circle"></i> ข้อมูลรถคันใหม่</div>
                
                <div class="claim-form-grid mb-3">
                  <div class="form-row-item" style="grid-template-columns: 100px 1fr;">
                    <label class="icon-label"><i class="fas fa-tags"></i> ประเภทรถ</label>
                    <div class="d-flex gap-3 align-items-center bg-white border rounded px-3 h-100">
                      <div class="form-check m-0">
                        <input class="form-check-input replace-car-type" type="radio" name="replace_type" id="replaceType_new" value="รถใหม่" checked>
                        <label class="form-check-label" for="replaceType_new">รถใหม่</label>
                      </div>
                      <div class="form-check m-0">
                        <input class="form-check-input replace-car-type" type="radio" name="replace_type" id="replaceType_used" value="รถมือสอง">
                        <label class="form-check-label" for="replaceType_used">รถมือสอง</label>
                      </div>
                    </div>
                  </div>

                  <div id="replaceGradeSection" class="form-row-item d-none" style="grid-template-columns: 100px 1fr;">
                    <label class="icon-label text-primary-orange"><i class="fas fa-medal"></i> เกรดรถ <span class="text-danger">*</span></label>
                    <select name="replace_used_grade" class="form-select border-primary-subtle">
                      <option value="">-- เลือกเกรด --</option>
                      <option value="A_premium">A พรีเมี่ยม</option>
                      <option value="A_w6">A รับประกันเครื่องยนต์ 6 เดือน</option>
                      <option value="C_w1">C รับประกันเครื่องยนต์ 1 เดือน</option>
                      <option value="C_as_is">C ตามสภาพไม่รับประกัน</option>
                    </select>
                  </div>
                </div>

                <div class="claim-form-grid-3 mb-3">
                  <div class="form-row-item" style="grid-template-columns: 60px 1fr;">
                    <label for="replaceModel" class="icon-label"><i class="fas fa-car-side"></i> รุ่น</label>
                    <input type="text" id="replaceModel" name="replace_model" class="form-control" placeholder="รุ่น">
                  </div>
                  <div class="form-row-item" style="grid-template-columns: 60px 1fr;">
                    <label for="replaceColor" class="icon-label"><i class="fas fa-palette"></i> สี</label>
                    <input type="text" id="replaceColor" name="replace_color" class="form-control" placeholder="ระบุสี">
                  </div>
                  <div class="form-row-item" style="grid-template-columns: 100px 1fr;">
                    <label for="replaceVin" class="icon-label"><i class="fas fa-fingerprint"></i> เลขตัวถัง</label>
                    <input type="text" id="replaceVin" name="replace_vin" class="form-control" placeholder="เลขตัวถัง / VIN">
                  </div>
                </div>

                <div class="mt-3">
                  <label for="replaceReason" class="icon-label mb-2"><i class="fas fa-comment-dots"></i> สาเหตุที่เปลี่ยนคัน</label>
                  <textarea id="replaceReason" name="replace_reason" class="form-control" placeholder="ระบุรายละเอียดสาเหตุที่ต้องเปลี่ยนรถคันใหม่..." rows="2"></textarea>
                </div>
              </div>

              <!-- Group 3: Approval Details -->
              <div class="section-sub-card">
                <div class="section-sub-title"><i class="fas fa-check-circle"></i> ผู้อนุมัติการเปลี่ยนรถ</div>
                <div class="claim-form-grid-3 mb-2">
                  <div class="form-row-item" style="grid-template-columns: 100px 1fr;">
                    <label class="icon-label"><i class="fas fa-id-badge"></i> รหัสพนักงาน</label>
                    <select id="replace_id" name="replace_id" class="form-select employee-select border-primary-subtle" data-target-name="replace_name" data-target-sig="replace_signature">
                      <option value="">-- เลือก --</option>
                    </select>
                  </div>
                  <div class="form-row-item" style="grid-template-columns: 80px 1fr;">
                    <label class="icon-label"><i class="fas fa-user-check"></i> ชื่อผู้อนุมัติ</label>
                    <input type="text" id="replace_name" name="replace_name" class="form-control bg-light" readonly placeholder="ชื่อพนักงาน">
                  </div>
                  <div class="form-row-item" style="grid-template-columns: 80px 1fr;">
                    <label class="icon-label"><i class="fas fa-pen-nib"></i> ลายเซ็นต์</label>
                    <input type="text" id="replace_signature" name="replace_signature" class="form-control bg-light" readonly placeholder="ลายเซ็นต์">
                  </div>
                </div>
                <div class="claim-form-grid-3">
                  <div class="form-row-item" style="grid-template-columns: 100px 1fr;">
                    <label class="icon-label"><i class="fas fa-calendar-check"></i> วันที่อนุมัติ</label>
                    <input type="date" id="replace_approve_date" name="replace_approve_date" class="form-control" value="<?= date('Y-m-d') ?>">
                  </div>
                  <div></div>
                  <div></div>
                </div>
              </div>

              <div class="row mt-3 mb-0">
                <div class="col-12">
                  <p class="text-danger fw-bold" style="font-size:0.95rem; margin-bottom:0;">***หมายเหตุ :</p>
                  <p class="text-danger" style="font-size:0.95rem; margin-bottom:0.25rem;">1. ลูกค้าแจ้งเปลี่ยนคัน ส่งให้สินเชื่อพร้อมใบอนุมัติทุกครั้งที่มีการเปลี่ยน/ตัวจริงแนบมากับสัญญาส่งให้บัญชี</p>
                  <p class="text-danger" style="font-size:0.95rem;">2. สินเชื่อเช็คประกันรถหาย / ทะเบียนแก้ไข พ.ร.บ.-ทะเบียน / บริหารสต็อก ตัดแลกเปลี่ยน / ธุรการสินเชื่อ ตรวจรอบการเปิดขาย กลับมาให้หน่อย</p>
                </div>
              </div>
            </div>
          </div>
        </div> 
      </div> 
      
      <hr class="my-4" style="border-color: #ececec;">
      
      <div class="claim-form-grid mt-4" style="padding: 0 12px;">
        <div class="form-row-item">
          <label for="recorder" class="form-label fw-semibold">ผู้บันทึกส่งเคลม <span class="text-danger">*</span></label>
          <input type="text" id="recorder" name="recorder" class="form-control" placeholder="ชื่อผู้บันทึก" required>
        </div>
        <div></div> <!-- Spacer for 2-column grid -->
      </div>

      <div class="row mt-2 mb-3" style="padding: 0 12px;">
        <div class="col-12 d-flex gap-2 justify-content-end">
          <button type="submit" class="btn btn-primary">บันทึกการส่งเคลม</button>
          <button type="reset" class="btn btn-reset">รีเซ็ต</button>
        </div>
      </div>

      <div id="result" class="result mt-3" role="status" aria-live="polite" style="display: none; padding: 0 12px;"></div>
    </form>
  </main>
</div> <!-- End of main-content -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-cndY3KSa6nw2pNpGFAvZrKpT8829k3KgAC45Eynl0qsnI9qZC6Qys9VbDomvY1vG" crossorigin="anonymous"></script>
  
  <script>
    (function(){
      // set claim_date value from placeholder if empty so user can edit it
      const _claimDate = document.getElementById('claim_date');
      if(_claimDate && !_claimDate.value){ _claimDate.value = _claimDate.placeholder || '<?php echo date('Y-m-d'); ?>'; }

      // Real-time Age of Vehicle Calculation
      function updateVehicleAge() {
        const saleDateInput = document.getElementById('sale_date');
        const ageDisplay = document.getElementById('vehicle_age_display');
        
        if (!saleDateInput || !ageDisplay) return;
        
        if (!saleDateInput.value) {
          ageDisplay.value = '-- ปี -- เดือน -- วัน 0 ชั่วโมง';
          ageDisplay.style.color = '#666';
          ageDisplay.style.fontWeight = '600';
          return;
        }

        const saleDate = new Date(saleDateInput.value);
        const now = new Date();

        if (now < saleDate) {
          ageDisplay.value = 'วันที่ขายต้องไม่เกินวันปัจจุบัน';
          ageDisplay.style.color = '#e74c3c';
          return;
        }

        let years = now.getFullYear() - saleDate.getFullYear();
        let months = now.getMonth() - saleDate.getMonth();
        let days = now.getDate() - saleDate.getDate();
        let hours = now.getHours() - saleDate.getHours();
        let minutes = now.getMinutes() - saleDate.getMinutes();

        if (minutes < 0) {
            minutes += 60;
            hours--;
        }
        if (hours < 0) {
            hours += 24;
            days--;
        }
        if (days < 0) {
            const previousMonth = new Date(now.getFullYear(), now.getMonth(), 0);
            days += previousMonth.getDate();
            months--;
        }
        if (months < 0) {
            months += 12;
            years--;
        }

        ageDisplay.value = `${years} ปี ${months} เดือน ${days} วัน ${hours} ชั่วโมง ${minutes} นาที`;
        ageDisplay.style.color = '#e74c3c'; // สีแดงเด่นชัด (Text สีแดง แบบ Real Time)
        ageDisplay.style.fontWeight = 'bold';
      }

      setInterval(updateVehicleAge, 60000); // 1 minute
      const saleDateInputEvent = document.getElementById('sale_date');
      if(saleDateInputEvent) saleDateInputEvent.addEventListener('change', updateVehicleAge);

      const gallery = document.getElementById('imageGallery');
      const form = document.getElementById('claimForm');
      const resultBox = document.getElementById('result');
      if(!gallery || !form) return;

      // Car Age Calculation
      const saleDateInput = document.getElementById('sale_date');
      const ageDisplay = document.getElementById('vehicle_age_display');
      
      function calculateVehicleAge() {
        if (!saleDateInput.value) {
          ageDisplay.value = '';
          return;
        }
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
      saleDateInput.addEventListener('change', calculateVehicleAge);

      // Employee Dropdowns Logic
      let employeesData = [];
      async function loadEmployees() {
        try {
          const res = await fetch('../backend/api_users.php');
          const json = await res.json();
          if (json.success) {
            employeesData = json.data;
            populateEmployeeDropdowns();
          }
        } catch (e) { console.error('Failed to load employees', e); }
      }

      function populateEmployeeDropdowns() {
        const selects = document.querySelectorAll('.employee-select');
        selects.forEach(sel => {
          employeesData.forEach(emp => {
            const opt = document.createElement('option');
            opt.value = emp.employee_id;
            opt.textContent = `${emp.employee_id} - ${emp.name}`;
            sel.appendChild(opt);
          });

          sel.addEventListener('change', function() {
            const targetName = document.getElementsByName(this.dataset.targetName)[0] || document.getElementById(this.dataset.targetName);
            const targetSig = document.getElementsByName(this.dataset.targetSig)[0] || document.getElementById(this.dataset.targetSig);
            const emp = employeesData.find(e => e.employee_id === this.value);
            
            if (targetName) targetName.value = emp ? emp.name : '';
            if (targetSig) targetSig.value = emp ? (emp.signature || 'No Signature') : '';
          });
        });
      }
      loadEmployees();

      // show grade select only for used cars and keep it inline with brand
      const gradeFields = document.querySelectorAll('.grade-field');
      const usedGradeCol = document.getElementById('used_grade_block');
      function updateGradeVisibility(){
        const checkedType = document.querySelector('input[name="car_type"]:checked');
        const used = checkedType && checkedType.value === 'used';
        if(usedGradeCol) usedGradeCol.classList.toggle('d-none', !used);
      }
      // listen for car_type changes
      document.querySelectorAll('input[name="car_type"]').forEach(r => r.addEventListener('change', updateGradeVisibility));
      updateGradeVisibility();

      // Claim action: single-select radios
      const claimActionRadios = document.querySelectorAll('input[name="claim_action"]');
      const replaceBlock = document.getElementById('replaceBlock');
      const approverSection = document.getElementById('approverSection');
      const partsSection = document.getElementById('partsSection');
      const claimOtherTextInit = document.getElementById('claim_other_text');

      function updatePartsVisibility(){
        if(!partsSection) return;
        const sel = document.querySelector('input[name="claim_action"]:checked');
        const val = sel ? sel.value : '';
        // ซ่อนตารางอะไหล่ถ้าเป็นการเปลี่ยนคัน
        const showParts = val === 'repairBranch' || val === 'sendHQ';
        partsSection.classList.toggle('d-none', !showParts);
        
        const partsDeliverySection = document.getElementById('partsDeliverySection');
        if(partsDeliverySection) partsDeliverySection.classList.toggle('d-none', !showParts);
        
        if(approverSection) approverSection.classList.toggle('d-none', !showParts);
      }

      function updateClaimActionVisibility(){
        const sel = document.querySelector('input[name="claim_action"]:checked');
        const val = sel ? sel.value : '';
        if(replaceBlock) replaceBlock.classList.toggle('d-none', val !== 'replaceVehicle');
        if(claimOtherTextInit) { 
          claimOtherTextInit.classList.toggle('d-none', val !== 'other'); 
          if(val !== 'other') claimOtherTextInit.value = ''; 
        }
      }

      claimActionRadios.forEach(r=> r.addEventListener('change', ()=>{ updatePartsVisibility(); updateClaimActionVisibility(); updateCheckedClasses(); }));
      updatePartsVisibility(); updateClaimActionVisibility();

      // show an 'other' input when parts_delivery = other is selected
      const partsDeliveryOtherText = document.getElementById('parts_delivery_other_text');
      const partsDeliveryRadios = document.querySelectorAll('input[name="parts_delivery"]');
      if(partsDeliveryRadios && partsDeliveryOtherText){
        function updatePartsDeliveryOther(){
          const sel = document.querySelector('input[name="parts_delivery"]:checked');
          const other = sel && sel.value === 'other';
          partsDeliveryOtherText.classList.toggle('d-none', !other);
          if(!other) partsDeliveryOtherText.value = '';
        }
        partsDeliveryRadios.forEach(r=> r.addEventListener('change', updatePartsDeliveryOther));
        updatePartsDeliveryOther();
      }

      function updateCheckedClasses(){
        Array.from(form.querySelectorAll('input[type="checkbox"], input[type="radio"]')).forEach(inp => {
          const lab = inp.closest('label');
          if(!lab) return;
          if(inp.checked) lab.classList.add('checked'); else lab.classList.remove('checked');
        });
      }
      Array.from(form.querySelectorAll('input[type="checkbox"], input[type="radio"]')).forEach(inp => inp.addEventListener('change', updateCheckedClasses));
      updateCheckedClasses();

      // Parts table dynamic rows
      const partsTableBody = document.querySelector('#partsTable tbody');
      const addPartBtn = document.getElementById('addPart');
      function reindexParts(){
        cleanPartsRows();
        Array.from(partsTableBody.querySelectorAll('tr')).forEach((r,i)=>{
          const idx = r.querySelector('.idx'); if(idx) idx.textContent = i+1;
        });
      }

      function createPartRow(data){
        const tr = document.createElement('tr');
        const tdIdx = document.createElement('td'); tdIdx.className = 'idx'; tdIdx.style.textAlign = 'center'; tr.appendChild(tdIdx);

        // code
        const tdCode = document.createElement('td');
        const inCode = document.createElement('input'); inCode.type='text'; inCode.name='parts_code[]'; inCode.value = data && data.code ? data.code : ''; inCode.placeholder='รหัสอะไหล่';
        const wrapCode = document.createElement('div'); wrapCode.className = 'field';
        const lblCode = document.createElement('span'); lblCode.className = 'label-text'; lblCode.textContent = 'รหัสอะไหล่';
        const inType = document.createElement('input'); inType.type = 'hidden'; inType.name = 'parts_type[]'; inType.value = data && data.type ? data.type : 'main';
        wrapCode.appendChild(lblCode); wrapCode.appendChild(inCode); wrapCode.appendChild(inType); tdCode.appendChild(wrapCode); tr.appendChild(tdCode);

        // name
        const tdName = document.createElement('td');
        const inName = document.createElement('input'); inName.type='text'; inName.name='parts_name[]'; inName.value = data && data.name ? data.name : ''; inName.placeholder='ชื่ออะไหล่';
        const wrapName = document.createElement('div'); wrapName.className = 'field';
        const lblName = document.createElement('span'); lblName.className = 'label-text'; lblName.textContent = 'ชื่ออะไหล่';
        wrapName.appendChild(lblName); wrapName.appendChild(inName); tdName.appendChild(wrapName); tr.appendChild(tdName);

        // qty
        const tdQty = document.createElement('td');
        const inQty = document.createElement('input'); inQty.type='number'; inQty.name='parts_qty[]'; inQty.min='0'; inQty.value = data && data.qty ? data.qty : 1;
        const wrapQty = document.createElement('div'); wrapQty.className = 'field';
        const lblQty = document.createElement('span'); lblQty.className = 'label-text'; lblQty.textContent = 'จำนวน';
        wrapQty.appendChild(lblQty); wrapQty.appendChild(inQty); tdQty.appendChild(wrapQty); tr.appendChild(tdQty);

        // price
        const tdPrice = document.createElement('td');
        const inPrice = document.createElement('input'); inPrice.type='number'; inPrice.name='parts_price[]'; inPrice.step='0.01'; inPrice.min='0'; inPrice.value = data && data.price ? data.price : '';
        const wrapPrice = document.createElement('div'); wrapPrice.className = 'field';
        const lblPrice = document.createElement('span'); lblPrice.className = 'label-text'; lblPrice.textContent = 'ราคา';
        wrapPrice.appendChild(lblPrice); wrapPrice.appendChild(inPrice); tdPrice.appendChild(wrapPrice); tr.appendChild(tdPrice);

        // note
        const tdNote = document.createElement('td');
        const inNote = document.createElement('input'); inNote.type='text'; inNote.name='parts_note[]'; inNote.value = data && data.note ? data.note : ''; inNote.placeholder='หมายเหตุ';
        const wrapNote = document.createElement('div'); wrapNote.className = 'field';
        const lblNote = document.createElement('span'); lblNote.className = 'label-text'; lblNote.textContent = 'หมายเหตุ';
        wrapNote.appendChild(lblNote); wrapNote.appendChild(inNote); tdNote.appendChild(wrapNote); tr.appendChild(tdNote);

        const tdAct = document.createElement('td'); tdAct.style.textAlign='center';
        const del = document.createElement('button'); del.type='button'; del.className='btn small removePart'; del.innerHTML = '<svg class="icon small" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>';
        del.addEventListener('click', ()=>{ tr.remove(); reindexParts(); }); tdAct.appendChild(del); tr.appendChild(tdAct);

        partsTableBody.appendChild(tr); reindexParts();
        return tr;
      }

      if(addPartBtn){ addPartBtn.addEventListener('click', ()=>{ createPartRow({}); cleanPartsRows(); }); }

      if(partsTableBody){
        partsTableBody.innerHTML = '';
        for(let i=0;i<3;i++) createPartRow({});
        cleanPartsRows();
      }

      function cleanPartsRows(){
        if(!partsTableBody) return;
        Array.from(partsTableBody.querySelectorAll('tr')).forEach(tr=>{
          if(tr.querySelectorAll('td').length < 7) tr.remove();
        });
      }

      const filesMap = {};
      const fieldLabel = {
        imgFullCar:   'รถทั้งคัน', imgSpot:       'จุดปัญหา', imgPart:       'ชิ้นส่วน',
        imgWarranty:   'สมุดรับประกัน', imgOdometer:   'เลขไมล์', imgEstimate:   'ใบประเมิน'
      };

      function getVin() {
        const v = (document.getElementById('vin') || {}).value || '';
        return v.trim().replace(/\s+/g, '_') || 'XXXXXXX';
      }

      function renderPreview(fieldId){
        const card = gallery.querySelector(`.upload-card[data-field="${fieldId}"]`);
        if(!card) return;
        const preview = card.querySelector('.preview');
        preview.innerHTML = '';
        const list = filesMap[fieldId] || [];
        if(list.length > 1) card.classList.add('multi'); else card.classList.remove('multi');
        if(list.length > 0) card.classList.add('has-preview'); else card.classList.remove('has-preview');
        
        const countEl = card.querySelector('.attach-count');
        if(countEl){
          if(list.length > 0){
            countEl.textContent = '(จำนวน ' + list.length + ' รูป)';
            countEl.setAttribute('data-show','true');
          } else {
            countEl.textContent = '';
            countEl.setAttribute('data-show','false');
          }
        }
        
        list.forEach((file, idx) => {
          if(!file.type.startsWith('image/')) return;
          const ext = file.name.split('.').pop() || 'jpg';
          const prefix = fieldLabel[fieldId] || fieldId;
          const vin = getVin();
          const fixedName = `${prefix}_${vin}_${idx + 1}.${ext}`;

          const wrap = document.createElement('div'); wrap.className = 'thumb';
          const img = document.createElement('img'); img.className = 'thumb-img';
          img.alt = fixedName;
          const reader = new FileReader();
          reader.onload = e => {
            img.src = e.target.result;
            const dlLink = wrap.querySelector('.dl-link');
            if (dlLink) dlLink.href = e.target.result;
          };
          reader.readAsDataURL(file);
          wrap.appendChild(img);

          const dl = document.createElement('a');
          dl.className = 'dl-link remove-btn';
          dl.title = `ดาวน์โหลด: ${fixedName}`;
          dl.download = fixedName;
          dl.href = '#';
          dl.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>';
          dl.style.cssText = 'position:absolute;top:8px;left:8px;width:28px;height:28px;background:rgba(0,0,0,0.5);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;';
          wrap.appendChild(dl);

          const del = document.createElement('button'); del.className = 'remove-btn'; del.title = 'ลบรูป';
          del.innerHTML = '<svg class="icon" aria-hidden="true"><use xlink:href="#icon-delete"></use></svg>';
          del.addEventListener('click', ev=>{ ev.stopPropagation(); removeFile(fieldId, idx); });
          wrap.appendChild(del);

          img.addEventListener('click', (ev)=>{ ev.stopPropagation(); openLightbox(fieldId, idx); });
          preview.appendChild(wrap);
        });
        if(countEl){ countEl.onclick = e=>{ e.stopPropagation(); if((filesMap[fieldId]||[]).length) openLightbox(fieldId, 0); }; }
      }

      function removeFile(fieldId, index){
        if(!filesMap[fieldId]) return;
        filesMap[fieldId].splice(index,1);
        renderPreview(fieldId);
      }

      gallery.querySelectorAll('.upload-card').forEach(card => {
        const field = card.dataset.field;
        const input = card.querySelector('input[type=file]');
        const multiple = input.hasAttribute('multiple');
        filesMap[field] = filesMap[field] || [];

        card.addEventListener('click', e => { if(e.target.tagName === 'INPUT' || e.target.classList.contains('remove-btn')) return; if(e.target.closest('.preview')) return; input.click(); });

        input.addEventListener('change', ()=>{
          const chosen = Array.from(input.files || []);
          if(multiple) filesMap[field] = filesMap[field].concat(chosen);
          else filesMap[field] = chosen.slice(0,1);
          renderPreview(field);
          input.value = ''; // เคลียร์เพื่อเลือกไฟล์เดิมซ้ำได้
        });

        ['dragenter','dragover'].forEach(ev=>{ card.addEventListener(ev, e=>{ e.preventDefault(); card.classList.add('dragover'); }); });
        ['dragleave','drop'].forEach(ev=>{ card.addEventListener(ev, e=>{ e.preventDefault(); card.classList.remove('dragover'); }); });
        card.addEventListener('drop', e=>{
          const dt = e.dataTransfer; if(!dt) return;
          const dropped = Array.from(dt.files || []);
          if(dropped.length){
            if(multiple) filesMap[field] = filesMap[field].concat(dropped);
            else filesMap[field] = [dropped[0]];
            renderPreview(field);
          }
        });
      });

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

      // ดักการกด Submit 
      form.addEventListener('submit', function(e){
        e.preventDefault();
        
        resultBox.style.display = 'block';
        resultBox.innerHTML = '<div style="padding: 10px; background: #fff3cd; color: #856404; border-radius: 8px; font-weight: bold; margin-bottom: 10px;">⏳ กำลังบันทึกข้อมูลเคลม... กรุณารอสักครู่</div>';

        if (!this.checkValidity()) {
          this.reportValidity();
          return;
        }
        
        // ใช้ FormData กวาดข้อมูลจากหน้าเว็บ "ทุกช่อง" โดยอัตโนมัติ
        const fd = new FormData(this);

        // วนลูปเอารูปภาพจาก 6 กล่องด้านบน ยัดใส่ลงไปใน FormData
        gallery.querySelectorAll('.upload-card').forEach(card => {
            const fieldId = card.dataset.field;
            const inputEl = card.querySelector('input[type="file"]');
            if(inputEl && filesMap[fieldId]) {
                const inputName = inputEl.getAttribute('name');
                filesMap[fieldId].forEach(file => {
                    fd.append(inputName, file);
                });
            }
        });

        // เอารูปภาพจากตารางอะไหล่ ยัดใส่ลงไปใน FormData ด้วย (ถ้ามี)
        const partsFieldId = 'imgParts[]';
        if(filesMap[partsFieldId]) {
            filesMap[partsFieldId].forEach(file => {
                fd.append(partsFieldId, file);
            });
        }

        // ยิงข้อมูลไปที่ index_handler.php
        fetch(form.action, {
          method: 'POST', 
          body: fd
        })
        .then(r => r.text())
        .then(txt => {
          resultBox.innerHTML = txt; // โชว์ข้อความที่ส่งกลับมาจาก Handler
          
          if(txt.includes('✅')) {
             // ถ้าสำเร็จ ให้เคลียร์รูปภาพในหน้าเว็บเตรียมรอเคสต่อไป
             document.querySelectorAll('.preview').forEach(p => p.innerHTML='');
             const partsPreview = document.getElementById('partsImgPreview');
             if(partsPreview) partsPreview.innerHTML = '';
             
             Object.keys(filesMap).forEach(k => filesMap[k] = []);
             
             const btnUploadParts = document.getElementById('btnUploadParts');
             if(btnUploadParts) btnUploadParts.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg> อัปโหลดรูปภาพ`;

             window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
          }
        })
        .catch(err => { 
          resultBox.innerHTML = '<div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 8px; font-weight: bold; margin-bottom: 10px;">❌ เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: '+ err.message + '</div>'; 
        });
      });

      // --- จัดการรการเปลี่ยนคันใหม่ : เกรด รถมือสอง ---
      document.querySelectorAll('.replace-car-type').forEach(radio => {
        radio.addEventListener('change', function() {
          const section = document.getElementById('replaceGradeSection');
          if (this.value === 'รถมือสอง') {
            section.classList.remove('d-none');
          } else {
            section.classList.add('d-none');
            // reset grade value
            section.querySelector('select').value = '';
          }
        });
      });
    })();
  </script>
</body>
</html>