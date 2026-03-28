<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>แก้ไขข้อมูลเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-edit_claim.css">
</head>
<body>

  <?php 
    $current_page = 'history.php';
    include 'includes/sidebar.php'; 
    $doc_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'TS01-001';
  ?>

  <!-- Main Content -->
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

      <!-- Edit Form blocks -->
      <div class="edit-container mb-5">
        
        <!-- Card 1: Main Info -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">แก้ไขข้อมูล</div>
          <div class="row g-4">
            <!-- Left Column -->
            <div class="col-12 col-lg-6">
              <div class="d-flex flex-column gap-3">
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">สาขา</label>
                  <div class="col-sm-8">
                    <select class="form-select border-2" required>
                      <option>สำนักงานใหญ่</option>
                    </select>
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">ประเภทการเคลม</label>
                  <div class="col-sm-8">
                    <div class="row g-2">
                       <div class="col-6"><select class="form-select border-2"><option>รถลูกค้า</option></select></div>
                       <div class="col-6"><select class="form-select border-2"><option>เคลมปกติ</option></select></div>
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
                    <input type="text" id="top-editor-name" class="form-control bg-light border-0" value="Mr. TEST" readonly>
                  </div>
                </div>
                <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600 color-555">วันที่แก้ไข</label>
                  <div class="col-sm-8">
                    <input type="text" id="top-edit-date" class="form-control bg-light border-0" value="00/00/0000" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Card 2: ข้อมูลผู้ใช้ & หมายเลขตัวถัง -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ข้อมูลผู้ใช้</div>
          <div class="row g-4">
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600 req">ชื่อ-นามสกุล</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control border-2" placeholder="ชื่อ นามสกุล" required>
                </div>
              </div>
              <div class="row mb-3">
                <label class="col-sm-4 col-form-label fw-600">ที่อยู่</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control border-2 mb-2" placeholder="ที่อยู่ 1">
                  <input type="text" class="form-control border-2" placeholder="ที่อยู่ 2">
                </div>
              </div>
            </div>
            
            <div class="col-12 col-lg-6">
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">จังหวัด</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control border-2" placeholder="จังหวัด">
                </div>
              </div>
              <div class="row align-items-center mb-3">
                <label class="col-sm-4 col-form-label fw-600">เบอร์โทรศัพท์</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control border-2" placeholder="เบอร์โทรศัพท์">
                </div>
              </div>
              <div class="row align-items-center">
                <label class="col-sm-4 col-form-label fw-600 req">หมายเลขตัวถัง</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control border-2" placeholder="หมายเลขตัวถัง" id="vin_number">
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Card 4: ปัญหา -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ปัญหา</div>
          <div class="mb-4">
            <label class="form-label fw-600 mb-2">รายละเอียดปัญหาที่ลูกค้าแจ้ง</label>
            <textarea class="form-control border-2" rows="4" placeholder="โปรดใส่รายละเอียด"></textarea>
          </div>
          <div>
            <label class="form-label fw-600 mb-2">ผลการตรวจเช็คปัญหา วิธีการตรวจเช็ค และสาเหตุของปัญหา</label>
            <textarea class="form-control border-2" rows="4" placeholder="โปรดใส่รายละเอียด"></textarea>
          </div>
        </div>

        <!-- Card 6: รูปภาพปัญหา -->
        <div class="edit-card mb-4 border-0 shadow-sm rounded-4 p-4">
          <div class="section-title d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 pb-2 border-bottom gap-3">
            <div class="d-flex align-items-center fw-bold fs-5">
              รูปภาพปัญหา
              <span id="img-count-badge" class="badge rounded-pill bg-primary-orange ms-2 px-3" style="display:none;">0 รูป</span>
            </div>
            <label class="btn-action bg-primary-orange color-fff cursor-pointer m-0 px-3 py-2 fs-md rounded-3 shadow-sm text-center">
              + อัปโหลดรูปภาพ
              <input type="file" id="image-upload" multiple accept="image/*" class="d-none">
            </label>
          </div>
          <div class="gallery-grid" id="gallery-grid"></div>
        </div>
        
        <!-- Card 5: อะไหล่ -->
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
                      <th width="50" class="text-center">ลบ</th>
                    </tr>
                  </thead>
                  <tbody id="parts-tbody">
                    <!-- กลุ่มที่ 1: อะไหล่หลัก -->
                    <tr class="group-header bg-light">
                      <td colspan="7" class="text-danger fw-bold py-3 ps-3">อะไหล่หลัก</td>
                      <td colspan="3" class="text-end pe-3">
                        <div class="form-check d-inline-block">
                          <input class="form-check-input" type="checkbox" id="no-qty-main">
                          <label class="form-check-label fs-xs" for="no-qty-main">ไม่ระบุจำนวน อะไหล่หลัก</label>
                        </div>
                      </td>
                    </tr>
                    
                    <tr id="add-main-row">
                      <td colspan="10" class="p-3 text-center">
                        <button type="button" class="btn btn-outline-orange btn-sm text-primary-orange w-100 py-3 border-dashed" id="btn-add-main" style="border-style: dashed !important; border-width: 2px;">+ เพิ่มอะไหล่หลัก</button>
                      </td>
                    </tr>

                    <!-- กลุ่มที่ 2: อะไหล่ที่เคลมร่วมกัน -->
                    <tr class="group-header bg-light">
                      <td colspan="10" class="text-danger fw-bold">อะไหล่ที่เคลมร่วมกัน</td>
                    </tr>
                    
                    <tr id="add-assoc-row">
                      <td colspan="10" class="p-3 text-center">
                        <button type="button" class="btn btn-outline-orange btn-sm text-primary-orange w-100 py-3 border-dashed" id="btn-add-assoc" style="border-style: dashed !important; border-width: 2px;">+ เพิ่มอะไหล่เคลมร่วม</button>
                      </td>
                    </tr>

                    <tr class="summary-row fw-bold bg-light">
                      <td colspan="5" class="py-3 ps-4">ยอดรวม</td>
                      <td class="text-center text-primary-orange fw-bold" id="sum-qty">0</td>
                      <td class="text-center text-primary-orange fw-bold" id="sum-money">0.00</td>
                      <td colspan="3"></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
        </div>

        <!-- Card 6: ค่าแรง & สรุปเงิน -->
        <div class="edit-card p-4 border-0 shadow-sm rounded-4 mb-4" style="background-color: #fbfbfb;">
            <div class="section-title mb-4 pb-2 border-bottom fw-bold fs-5">ค่าแรงและสรุปเงินประจำเคส</div>
            <div class="row g-4">
              <!-- Left side labor -->
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
              <!-- Right side calculation -->
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

        <!-- Card 7: การตรวจสอบผลและอนุมัติ -->
        <div class="edit-card border-0 shadow-sm rounded-4 p-4" id="verification-section">
          <div class="section-title verification-title mb-4 pb-2 border-bottom fw-bold fs-5 color-primary-orange">ลงชื่อผู้แก้ไขเอกสาร</div>
          
          <div class="row g-4 mb-4">
              <div class="col-12 col-lg-6">
                <div class="row align-items-center">
                  <label class="col-sm-3 col-form-label fw-600">สถานะ :</label>
                  <div class="col-sm-9">
                    <select class="form-select verification-select fw-bold border-2">
                      <option value="">-- กรุณาเลือกสถานะของเคส --</option>
                      <option value="wait" class="text-warning">รอตรวจสอบ</option>
                      <option value="approve" class="text-success">อนุมัติการเคลม</option>
                      <option value="reject" class="text-danger">ไม่นุมัติ</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <div class="">
                  <label class="form-label fw-600 mb-2">หมายเหตุ / ความเห็นของผู้แก้ไข</label>
                  <textarea class="form-control border-2" rows="3" placeholder="ระบุเหตุผลการแก้ไข..."></textarea>
                </div>
              </div>
          </div>
          
          <div class="row g-4 border-top pt-4">
             <div class="col-12 col-lg-6">
               <div class="row align-items-center mb-3">
                  <label class="col-sm-4 col-form-label fw-600">ลงชื่อผู้แก้ไข</label>
                  <div class="col-sm-8">
                    <input type="text" id="bottom-editor-name" class="form-control border-2" placeholder="ชื่อ-นามสกุล ผู้แก้ไข">
                  </div>
               </div>
               <div class="row align-items-center">
                  <label class="col-sm-4 col-form-label fw-600">วันที่แก้ไข</label>
                  <div class="col-sm-8">
                    <input type="date" id="bottom-edit-date" class="form-control border-2">
                  </div>
               </div>
             </div>
             
             <div class="col-12 col-lg-6 d-flex flex-column flex-sm-row justify-content-end align-items-stretch align-items-sm-end gap-3 mt-4 mt-lg-0">
                <a href="history.php" class="btn btn-secondary px-5 py-2 rounded-3 shadow-sm text-decoration-none text-center color-fff">ยกเลิก</a>
                <button type="submit" class="btn-action bg-primary-orange color-fff border-0 px-5 py-2 rounded-3 shadow-sm fw-bold">บันทึกการแก้ไข</button>
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
      const partsTbody = document.getElementById('parts-tbody');
      
      // Calculate parts table
      function calculateParts() {
        let sumQty = 0;
        let sumTotal = 0;
        
        const rows = document.querySelectorAll('.part-row');
        rows.forEach((row, index) => {
           row.cells[0].textContent = index + 1; 
           const price = parseFloat(row.querySelector('.part-price').value) || 0;
           const qty = parseFloat(row.querySelector('.part-qty').value) || 0;
           const total = price * qty;
           
           row.querySelector('.part-total').value = total.toFixed(2);
           sumQty += qty;
           sumTotal += total;
        });
        
        document.getElementById('sum-qty').textContent = sumQty;
        document.getElementById('sum-money').textContent = sumTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        document.getElementById('labor-parts-total').value = sumTotal.toFixed(2);
        calculateLaborAndGrandTotal();
      }

      // Sync signature inputs
      const bottomEditorName = document.getElementById('bottom-editor-name');
      const bottomEditDate = document.getElementById('bottom-edit-date');
      const topEditorName = document.getElementById('top-editor-name');
      const topEditDate = document.getElementById('top-edit-date');

      if(bottomEditorName && topEditorName) {
        bottomEditorName.addEventListener('input', function() {
          topEditorName.value = this.value || 'Mr. TEST';
        });
      }
      if(bottomEditDate && topEditDate) {
        bottomEditDate.addEventListener('input', function() {
          if (this.value) {
            const parts = this.value.split('-');
            const thYear = parseInt(parts[0]) + 543;
            topEditDate.value = `${parts[2]}/${parts[1]}/${thYear}`;
          } else {
            topEditDate.value = '00/00/0000';
          }
        });
      }

      // Calculate labor & grand total
      function calculateLaborAndGrandTotal() {
        const frt = parseFloat(document.getElementById('labor-frt').value) || 0;
        const rate = parseFloat(document.getElementById('labor-rate').value) || 0;
        const laborTotal = frt * rate;
        document.getElementById('labor-total').value = laborTotal.toFixed(2);
        
        const partsTotal = parseFloat(document.getElementById('labor-parts-total').value) || 0;
        const managePct = parseFloat(document.getElementById('manage-pct').value) || 0;
        
        const manageFee = partsTotal * (managePct / 100);
        document.getElementById('manage-fee').value = manageFee.toFixed(2);
        
        const otherFee = parseFloat(document.getElementById('other-fee').value) || 0;
        
        const grandTotal = laborTotal + partsTotal + manageFee + otherFee;
        document.getElementById('grand-total').value = grandTotal.toFixed(2);
      }
      
      // Attach events to existing rows
      function attachRowEvents(row) {
         row.querySelector('.part-price').addEventListener('input', calculateParts);
         row.querySelector('.part-qty').addEventListener('input', calculateParts);
         row.querySelector('.btn-remove-part').addEventListener('click', function() {
            row.remove();
            calculateParts();
         });
      }
      
      // Helper function to create a new row
      function createNewPartRow() {
        const tr = document.createElement('tr');
        tr.className = 'part-row';
        tr.innerHTML = `
           <td></td>
           <td><input type="text" class="form-control form-control-sm" placeholder="รหัสสินค้า"></td>
           <td><input type="text" class="form-control form-control-sm" placeholder="ชื่อสินค้า"></td>
           <td><input type="text" class="form-control form-control-sm"></td>
           <td class="text-center"><input type="number" step="0.01" class="form-control form-control-sm text-center part-price" value="0.00"></td>
           <td class="text-center"><input type="number" class="form-control form-control-sm text-center part-qty" value="1"></td>
           <td class="text-center"><input type="text" class="form-control form-control-sm text-center bg-light part-total" value="0.00" readonly></td>
           <td class="text-center"><input type="checkbox" class="form-check-input"></td>
           <td class="text-center"><input type="checkbox" class="form-check-input"></td>
           <td class="text-center"><button type="button" class="btn btn-link text-danger btn-remove-part p-0" title="ลบรายการ">×</button></td>
        `;
        attachRowEvents(tr);
        return tr;
      }

      // Add main part button
      document.getElementById('btn-add-main').addEventListener('click', function() {
        const tr = createNewPartRow();
        const addRow = document.getElementById('add-main-row');
        partsTbody.insertBefore(tr, addRow);
        calculateParts();
      });

      // Add assoc part button
      document.getElementById('btn-add-assoc').addEventListener('click', function() {
        const tr = createNewPartRow();
        const addRow = document.getElementById('add-assoc-row');
        partsTbody.insertBefore(tr, addRow);
        calculateParts();
      });
      
      // Attach events to labor inputs
      document.getElementById('labor-frt').addEventListener('input', calculateLaborAndGrandTotal);
      document.getElementById('labor-rate').addEventListener('input', calculateLaborAndGrandTotal);
      document.getElementById('manage-pct').addEventListener('input', calculateLaborAndGrandTotal);
      document.getElementById('other-fee').addEventListener('input', calculateLaborAndGrandTotal);
      
      // IMAGE UPLOAD LOGIC
      const imageUpload = document.getElementById('image-upload');
      const galleryGrid = document.getElementById('gallery-grid');
      const imageModal = document.getElementById('image-modal');
      const modalImg = document.getElementById('modal-img');
      let imageIndex = 2; 
      
      imageUpload.addEventListener('change', function(e) {
        const files = e.target.files;
        const vinNumber = document.getElementById('vin_number').value || 'UnknownVIN';
        
        for (let i = 0; i < files.length; i++) {
           const file = files[i];
           const reader = new FileReader();
           
           reader.onload = function(evt) {
              const ext = file.name.split('.').pop() || 'jpg';
              const newFileName = `รูปภาพปัญหา_${vinNumber}_${imageIndex}.${ext}`;
              createImageItem(evt.target.result, newFileName);
              imageIndex++;
           }
           reader.readAsDataURL(file);
        }
        this.value = '';
      });
      
      function createImageItem(src, title) {
        const div = document.createElement('div');
        div.className = 'gallery-item';
        div.innerHTML = `
          <img src="${src}" alt="uploaded image" class="preview-img cursor-pointer" title="คลิกเพื่อขยาย">
          <div class="img-preview-footer">
            <span class="img-preview-title" title="${title}">${title}</span>
            <div class="img-preview-actions">
              <button type="button" class="btn-remove-img" title="ลบรูป">×</button>
            </div>
          </div>
        `;
        
        div.querySelector('.btn-remove-img').addEventListener('click', function() {
          div.remove();
          updateImgCountBadge();
        });
        div.querySelector('.preview-img').addEventListener('click', function() {
           modalImg.src = this.src;
           imageModal.style.display = 'flex';
        });
        
        galleryGrid.appendChild(div);
        updateImgCountBadge();
      }

      function updateImgCountBadge() {
        const badge = document.getElementById('img-count-badge');
        if (!badge) return;
        const count = document.querySelectorAll('#gallery-grid .gallery-item').length;
        if (count > 0) {
          badge.textContent = count + ' รูป';
          badge.style.display = 'inline-block';
        } else {
          badge.style.display = 'none';
        }
      }
      
      document.querySelectorAll('.btn-remove-img').forEach(btn => btn.addEventListener('click', function() {
        this.closest('.gallery-item').remove();
        updateImgCountBadge();
      }));
      document.querySelectorAll('.preview-img').forEach(img => img.addEventListener('click', function() {
         modalImg.src = this.src;
         imageModal.style.display = 'flex';
      }));
      
      document.getElementById('modal-close').addEventListener('click', () => imageModal.style.display = 'none');
      imageModal.addEventListener('click', function(e) {
        if(e.target === imageModal) imageModal.style.display = 'none';
      });
      
      calculateParts();
    });
  </script>
</body>
</html>
