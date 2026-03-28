<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตรวจสอบข้อมูลเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-edit_claim.css">
</head>
<body>

  <?php 
    $current_page = 'claim_form_check.php';
    include 'includes/sidebar.php'; 
    $doc_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'TS01-001';
  ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid p-0">
    
      <div class="filter-bar mb-4">
        <div class="row w-100 align-items-center g-3">
          <div class="col-12 col-md-6">
            <div class="fs-xl fw-600">ประวัติเคลม <span class="color-999 fw-normal">/ <?= $doc_id ?> / ตรวจสอบ</span></div>
          </div>
          <div class="col-12 col-md-6 text-md-end">
            <div class="d-flex gap-2 justify-content-md-end">
              <a href="#verification-section" class="btn-action bg-primary-orange text-decoration-none px-3 py-1 color-fff rounded-3 shadow-sm">ไปยังส่วนอนุมัติ</a>
              <a href="claim_form_check.php" class="btn-action bg-secondary text-decoration-none px-3 py-1 color-fff rounded-3 shadow-sm">ย้อนกลับ</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Form blocks -->
      <div class="edit-container mb-5">
        
        <!-- Card 1: ข้อมูลเอกสาร (Read Only) -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลเอกสาร</div>
          <div class="row g-4">
            <!-- Left Column -->
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">สาขา</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control bg-light border-0" value="สำนักงานใหญ่" readonly>
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">ประเภทการเคลม</label>
                  <div class="col-sm-8">
                    <div class="row g-2">
                       <div class="col-6"><input type="text" class="form-control bg-light border-0" value="รถลูกค้า" readonly></div>
                       <div class="col-6"><input type="text" class="form-control bg-light border-0" value="เคลมปกติ" readonly></div>
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
                    <input type="text" class="form-control bg-light border-0" value="25/03/2569" readonly>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600 color-555">ผู้บันทึกส่งเคลม</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control bg-light border-0" value="Mr. TEST" readonly>
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600 color-555">ผู้แก้ไขครั้งล่าสุด</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control bg-light border-0" value="Mr. TEST" readonly>
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600 color-555">วันที่แก้ไข</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control bg-light border-0" value="24/03/2569" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Card 2: ข้อมูลผู้ใช้ & หมายเลขตัวถัง (Read Only) -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลผู้ใช้</div>
          <div class="row g-4">
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">ชื่อ-นามสกุล</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control bg-light border-0" value="สมชาย เข็มกลัด" readonly>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-4 col-form-label fw-600">ที่อยู่</label>
                <div class="col-sm-8">
                  <div class="d-flex flex-column gap-2">
                    <input type="text" class="form-control bg-light border-0" value="123/45 หมู่ 6 ต.ห้วยขวาง" readonly>
                    <input type="text" class="form-control bg-light border-0" value="จ.สกลนคร 47000" readonly>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control bg-light border-0" value="081-234-5678" readonly>
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label fw-600">หมายเลขตัวถัง</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control bg-light border-0 fw-bold text-primary" value="MLH1234567890" id="vin_number" readonly>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Card 4: ปัญหา (Read Only) -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ปัญหา</div>
          <div class="mb-4">
            <label class="form-label fw-600 mb-2">รายละเอียดปัญหาที่ลูกค้าแจ้ง</label>
            <textarea class="form-control bg-light border-0" rows="4" readonly>มีเสียงดังผิดปกติที่บริเวณเครื่องยนต์ขณะเร่งเครื่อง</textarea>
          </div>
          <div>
            <label class="form-label fw-600 mb-2">ผลการตรวจเช็คปัญหา วิธีการตรวจเช็ค และสาเหตุของปัญหา</label>
            <textarea class="form-control bg-light border-0" rows="4" readonly>ตรวจเช็คพบลูกปืนข้อเหวี่ยงมีอาการหลวม สาเหตุเกิดจากการใช้งานหนัก</textarea>
          </div>
        </div>

        <!-- Card 6: รูปภาพปัญหา -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom fw-bold fs-5">
            รูปภาพปัญหา
            <span id="img-count-badge" class="badge rounded-pill bg-primary-orange px-3">2 รูป</span>
          </div>
          <div class="gallery-grid" id="gallery-grid">
             <div class="gallery-item">
               <img src="https://via.placeholder.com/300x200?text=Problem+1" class="preview-img cursor-pointer" title="คลิกเพื่อขยาย">
               <div class="img-preview-footer">
                 <span class="img-preview-title" title="รูปภาพปัญหา_MLH1234567890_1.jpg">รูปภาพปัญหา_MLH1234567890_1.jpg</span>
               </div>
             </div>
             <div class="gallery-item">
               <img src="https://via.placeholder.com/300x200?text=Problem+2" class="preview-img cursor-pointer" title="คลิกเพื่อขยาย">
               <div class="img-preview-footer">
                 <span class="img-preview-title" title="รูปภาพปัญหา_MLH1234567890_2.jpg">รูปภาพปัญหา_MLH1234567890_2.jpg</span>
               </div>
             </div>
          </div>
        </div>
        
        <!-- Card 5: อะไหล่ (Read Only Table) -->
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
                      <th width="100">Lot No.</th>
                      <th width="120" class="text-center">ราคา/หน่วย</th>
                      <th width="90" class="text-center">จำนวน</th>
                      <th width="120" class="text-center">เป็นเงิน</th>
                      <th width="70" class="text-center">ส่งDCS</th>
                      <th width="70" class="text-center">พิเศษ</th>
                    </tr>
                  </thead>
                  <tbody id="parts-tbody">
                    <tr class="group-header bg-light">
                      <td colspan="9" class="text-danger fw-bold py-3 ps-3">อะไหล่หลัก</td>
                    </tr>
                    <tr class="part-row">
                      <td>1</td>
                      <td>P-001</td>
                      <td>ลูกปืนข้อเหวี่ยง L/R</td>
                      <td>2024-03</td>
                      <td class="text-center">450.00</td>
                      <td class="text-center">2</td>
                      <td class="text-center fw-600">900.00</td>
                      <td class="text-center"><input type="checkbox" checked disabled></td>
                      <td class="text-center"><input type="checkbox" disabled></td>
                    </tr>
                    
                    <tr class="group-header bg-light">
                      <td colspan="9" class="text-danger fw-bold py-3 ps-3">อะไหล่ที่เคลมร่วมกัน</td>
                    </tr>
                    <tr class="part-row">
                      <td>1</td>
                      <td>G-002</td>
                      <td>ชุดปะเก็นเครื่องยนต์</td>
                      <td>2024-03</td>
                      <td class="text-center">250.00</td>
                      <td class="text-center">1</td>
                      <td class="text-center fw-600">250.00</td>
                      <td class="text-center"><input type="checkbox" checked disabled></td>
                      <td class="text-center"><input type="checkbox" disabled></td>
                    </tr>

                    <tr class="summary-row fw-bold bg-light">
                      <td colspan="5" class="py-3 ps-4">ยอดรวม</td>
                      <td class="text-center text-primary-orange" id="sum-qty">3</td>
                      <td class="text-center text-primary-orange" id="sum-money">1,150.00</td>
                      <td colspan="2"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
        </div>

        <!-- Card 6: ค่าแรง & สรุปเงิน (Read Only) -->
        <div class="edit-card p-4 border-0 shadow-sm rounded-4 mb-4" style="background-color: #fafafa;">
            <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ค่าแรงและสรุปเงินประจำเคส</div>
            <div class="row g-4">
              <!-- Left side labor -->
              <div class="col-12 col-lg-6">
                <div class="bg-white p-4 rounded-4 shadow h-100">
                  <div class="d-flex flex-column gap-3">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">จำนวน FRT</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="1.50" readonly>
                          <span class="input-group-text border-0 bg-light">ชม.</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">FRT. Rate/hr</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="300.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">รวมค่าแรง</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="450.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">รวมค่าอะไหล่</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="1,150.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Right side calculation -->
              <div class="col-12 col-lg-6">
                <div class="bg-white p-4 rounded-4 shadow h-100">
                  <div class="d-flex flex-column gap-3">
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">อัตราค่าการจัดการ</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="10.0" readonly>
                          <span class="input-group-text border-0 bg-light">%</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">ค่าการจัดการ</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="115.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-600">ค่าใช้จ่ายอื่นๆ</label>
                      <div class="col-sm-7">
                        <div class="input-group">
                          <input type="text" class="form-control border-0 bg-light fw-bold" value="0.00" readonly>
                          <span class="input-group-text border-0 bg-light">บาท</span>
                        </div>
                      </div>
                    </div>
                    <div class="row align-items-center">
                      <label class="col-sm-5 col-form-label fw-700 text-primary-orange fs-5">รวมเงินเคลมสุทธิ</label>
                      <div class="col-sm-7">
                        <div class="input-group shadow-sm">
                          <input type="text" class="form-control fw-bold border-2 border-primary-orange text-primary-orange fs-4 py-2" value="1,715.00" readonly>
                          <span class="input-group-text border-2 border-primary-orange bg-primary-orange color-fff fw-bold">บาท</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        <!-- Card 7: การตรวจสอบผลและอนุมัติ (Editable) -->
        <div class="edit-card border-0 shadow-sm rounded-4 p-4" id="verification-section" style="border: 2px solid var(--primary-orange) !important;">
          <div class="section-title verification-title d-flex align-items-center mb-4 pb-2 border-bottom fw-bold fs-5 color-primary-orange">
            <svg class="me-2" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            ผลการตรวจสอบและอนุมัติ
          </div>
          
          <div class="row g-4 mb-4">
              <div class="col-12 col-lg-6">
                <div class="p-4 bg-light rounded-4 shadow-sm h-100 border border-secondary border-opacity-10">
                  <label class="form-label fw-bold mb-3 border-bottom pb-2 w-100 fs-6">รายการตรวจสอบ (Checklist)</label>
                  <div class="d-flex flex-column gap-3">
                    <div class="form-check custom-checkbox-lg">
                      <input class="form-check-input border-2" type="checkbox" id="check1" style="width: 22px; height: 22px;">
                      <label class="form-check-label fs-md ms-2 pt-1 cursor-pointer" for="check1">ตรวจสอบความถูกต้องของข้อมูลลูกค้าและหมายเลขตัวถัง</label>
                    </div>
                    <div class="form-check custom-checkbox-lg">
                      <input class="form-check-input border-2" type="checkbox" id="check2" style="width: 22px; height: 22px;">
                      <label class="form-check-label fs-md ms-2 pt-1 cursor-pointer" for="check2">ตรวจสอบรายละเอียดและการแนบรูปภาพประกอบปัญหา</label>
                    </div>
                    <div class="form-check custom-checkbox-lg">
                      <input class="form-check-input border-2" type="checkbox" id="check3" style="width: 22px; height: 22px;">
                      <label class="form-check-label fs-md ms-2 pt-1 cursor-pointer" for="check3">ตรวจสอบรายการอะไหล่ ค่าแรง และยอดเคลมสุทธิว่าถูกต้องเหมาะสม</label>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-12 col-lg-6">
                <div class="bg-white p-4 rounded-4 shadow-sm h-100 border border-secondary border-opacity-10">
                  <div class="row align-items-center mb-3">
                    <label class="col-sm-4 col-form-label fw-bold text-dark">ผลพิจารณา</label>
                    <div class="col-sm-8">
                      <select class="form-select border-primary-orange border-2 fw-bold text-primary-orange">
                        <option value="">-- กรุณาเลือกผลการตรวจสอบ --</option>
                        <option value="approve" class="text-success">อนุมัติการเคลม</option>
                        <option value="reject" class="text-danger">ไม่อนุมัติ</option>
                        <option value="return" class="text-warning">ตีกลับไปแก้ไข</option>
                      </select>
                    </div>
                  </div>
                  <div class="mb-0">
                    <label class="form-label fw-bold mb-2">หมายเหตุ / ความเห็นผู้ตรวจสอบ</label>
                    <textarea class="form-control border-2" rows="4" placeholder="ระบุเหตุผล หากไม่อนุมัติหรือตีกลับ..."></textarea>
                  </div>
                </div>
              </div>
          </div>
          
          <div class="row g-4 border-top pt-4 mt-2">
             <div class="col-12 col-lg-6">
               <div class="row align-items-center mb-3">
                  <label class="col-sm-4 col-form-label fw-600">ผู้ตรวจสอบ</label>
                  <div class="col-sm-8">
                    <input type="text" class="form-control border-2" placeholder="ชื่อ-นามสกุล ผู้ตรวจสอบ" value="Super Admin">
                  </div>
               </div>
               <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">วันที่ตรวจสอบ</label>
                  <div class="col-sm-8">
                    <input type="date" class="form-control border-2" value="<?= date('Y-m-d') ?>">
                  </div>
               </div>
             </div>
             
             <div class="col-12 col-lg-6 d-flex flex-column flex-sm-row justify-content-end align-items-stretch align-items-sm-end gap-3 mt-4 mt-lg-0">
                <a href="claim_form_check.php" class="btn btn-secondary px-5 py-2 rounded-3 shadow-sm text-decoration-none text-center color-fff">ยกเลิก</a>
                <button type="button" class="btn-action bg-primary-orange color-fff border-0 px-5 py-2 rounded-3 shadow-sm fw-bold">บันทึกผลการตรวจสอบ</button>
             </div>
          </div>
        </div>
        
      </div> <!-- /edit-container -->
    </div>
  </div>

  <!-- Image Modal Viewer -->
  <div class="modal-overlay" id="image-modal">
    <div class="modal-close" id="modal-close">×</div>
    <img src="" id="modal-img" class="modal-content" alt="Enlarged view">
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const imageModal = document.getElementById('image-modal');
      const modalImg = document.getElementById('modal-img');
      const modalClose = document.getElementById('modal-close');

      // Modal logic for image preview
      document.querySelectorAll('.preview-img').forEach(img => {
        img.addEventListener('click', function() {
          modalImg.src = this.src;
          imageModal.style.display = 'flex';
        });
      });

      if(modalClose) {
        modalClose.addEventListener('click', () => {
          imageModal.style.display = 'none';
        });
      }

      if(imageModal) {
        imageModal.addEventListener('click', function(e) {
          if(e.target === imageModal) imageModal.style.display = 'none';
        });
      }
    });
  </script>
</body>
</html>