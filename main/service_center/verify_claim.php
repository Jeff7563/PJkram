<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตรวจสอบข้อมูลเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-edit_claim.css">
</head>
<body>

  <?php 
    $current_page = 'check.php';
    include 'includes/sidebar.php'; 
    $doc_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'TS01-001';
  ?>

   <!-- Main Content -->
  <div class="main-content">
    
      <div class="filter-bar">
        <div class="fs-xl fw-600">ประวัติเคลม <span class="color-999 fw-normal">/ <?= $doc_id ?> / แก้ไข</span></div>
        <div class="filter-group filter-group justify-content-end">
          <a href="#verification-section" class="btn-action bg-primary-orange text-decoration-none">ไปยังลงชื่อผู้แก้ไข</a>
          <a href="history.php" class="btn-action bg-secondary text-decoration-none">ย้อนกลับ</a>
        </div>
      </div>

    <!-- Edit Form blocks -->
    <div class="edit-container mb-5">
      
      <!-- Card 1: Main Info -->
      <div class="edit-card">
        <div class="section-title">
          แก้ไขข้อมูล
        </div>
          <div class="grid-2">
            <!-- Left Column -->
            <div class="d-flex flex-column gap-20">
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600">สาขา</label>
                <select class="form-control" required>
                  <option>สำนักงานใหญ่</option>
                </select>
              </div>
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600">ประเภทการเคลม</label>
                <div class="d-flex gap-10 w-100">
                  <select class="form-control min-w-120"><option>รถลูกค้า</option></select>
                  <select class="form-control"><option>เคลมปกติ</option></select>
                </div>
              </div>
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600">เลขที่เอกสาร</label>
                <input type="text" class="form-control" value="<?= $doc_id ?>" readonly class="bg-disabled input-readonly">
              </div>
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600">วันที่เอกสาร</label>
                <input type="text" class="form-control" value="25/03/2569" readonly class="bg-light-gray input-readonly">
              </div>
            </div>
            
            <!-- Right Column -->
            <div class="d-flex flex-column gap-20">
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600 color-555">ผู้บันทึกส่งเคลม</label>
                <input type="text" class="form-control" value="Mr. TEST" readonly class="bg-readonly input-readonly">
              </div>
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600 color-555">ผู้แก้ไขครั้งล่าสุด</label>
                <input type="text" id="top-editor-name" class="form-control" value="Mr. TEST" readonly class="bg-readonly input-readonly">
              </div>
              <div class="form-group row-group">
                <label class="form-label label-140 fw-600 color-555">วันที่แก้ไข</label>
                <input type="text" id="top-edit-date" class="form-control" value="00/00/0000" readonly class="bg-light-gray input-readonly">
              </div>
            </div>
          </div>
        </div>
      
      <!-- Card 2: ข้อมูลผู้ใช้ & หมายเลขตัวถัง -->
      <div class="edit-card">
        <div class="section-title">
          ข้อมูลผู้ใช้
        </div>
        <div class="grid-2">
          

          <div class="form-group row-group">
            <label class="form-label req">ชื่อ-นามสกุล</label>
             <input type="text" class="form-control" placeholder="ชื่อ นามสกุล" required>
          </div>
          <div></div>
          
          <div class="form-group row-group form-group row-group full-width">
            <label class="form-label">ที่อยู่</label>
            <div class="d-flex flex-column gap-10 w-100">
              <input type="text" class="form-control" placeholder="ที่อยู่ 1">
              <input type="text" class="form-control" placeholder="ที่อยู่ 2">
            </div>
          </div>
          
          <div class="form-group row-group">
             <label class="form-label">รหัสไปรษณีย์</label>
             <input type="text" class="form-control" placeholder="รหัสไปรษณีย์">
          </div>

          <div class="form-group row-group">
             <label class="form-label">จังหวัด</label>
             <input type="text" class="form-control" placeholder="จังหวัด">
            </div>

          <div class="form-group row-group">
             <label class="form-label">เบอร์โทรศัพท์</label>
             <input type="text" class="form-control" placeholder="เบอร์โทรศัพท์">
          </div>

          <div class="form-group row-group">
            <label class="form-label req">หมายเลขตัวถัง</label>
             <input type="text" class="form-control" placeholder="หมายเลขตัวถัง" id="vin_number">
          </div>
          <div></div>

        </div>
      </div>
      
      <!-- Card 3 (รถ) REMOVED based on User request -->
      
      <!-- Card 4: ปัญหา -->
      <div class="edit-card">
        <div class="section-title">
          ปัญหา
        </div>
        <div class="form-group mb-4">
          <label class="form-label">รายละเอียดปัญหาที่ลูกค้าแจ้ง</label>
          <textarea class="form-control" rows="4" placeholder="โปรดใส่รายละเอียด"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">ผลการตรวจเช็คปัญหา วิธีการตรวจเช็ค และสาเหตุของปัญหา</label>
          <textarea class="form-control" rows="4" placeholder="โปรดใส่รายละเอียด"></textarea>
        </div>
      </div>

        <!-- Card 6: รูปภาพปัญหา -->
        <div class="edit-card">
          <div class="section-title section-title d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-8">
              รูปภาพปัญหา
              <span id="img-count-badge" style="display:none;background:var(--primary-orange);color:#fff;border-radius:20px;padding:2px 12px;font-size:0.8rem;font-weight:600;margin-left:8px;">0 รูป</span>
            </div>
            <label class="btn-action cursor-pointer m-0 px-3 py-1 fs-md">
              + อัปโหลดรูปภาพ
              <input type="file" id="image-upload" multiple accept="image/*" class="d-none">
            </label>
          </div>
          
          <div class="gallery-grid" id="gallery-grid">
        </div>
      </div>
      
      <!-- Card 5: อะไหล่ และ ค่าแรง -->
      <div class="edit-card edit-card p-0 overflow-hidden">
        <!-- Parts Table Area -->
          <div class="p-4 pb-2">
            <div class="section-title section-title d-flex justify-content-between border-bottom-none">
              <div class="d-flex align-items-center gap-8">
                รายการอะไหล่
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="edit-table">
                <thead>
                  <tr>
                    <th width="40">#</th>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th width="100">Lot No.</th>
                    <th width="120" class="center">ราคา/หน่วย</th>
                    <th width="90" class="center">จำนวน</th>
                    <th width="120" class="center">เป็นเงิน</th>
                    <th width="70" class="center">ส่งDCS</th>
                    <th width="70" class="center">พิเศษ</th>
                    <th width="50" class="center">ลบ</th>
                  </tr>
                </thead>
                <tbody id="parts-tbody">
                  <!-- กลุ่มที่ 1: อะไหล่หลัก -->
                  <tr class="group-header">
                    <td colspan="7" class="text-danger">อะไหล่หลัก</td>
                    <td colspan="3" class="center fw-normal fs-sm pt-4 text-right pr-3">
                      <label class="cursor-pointer"><input type="checkbox" class="custom-check">ไม่ระบุจำนวน อะไหล่หลัก</label>
                    </td>
                  </tr>
                  
                  <tr id="add-main-row" class="bg-white">
                    <td colspan="10" class="p-2 text-center border-bottom">
                      <button type="button" class="btn-action" id="btn-add-main" class="btn-action bg-white text-primary-orange border-dashed-orange px-3 py-1 fs-sm mx-auto d-inline-block">+ เพิ่มอะไหล่หลัก</button>
                    </td>
                  </tr>

                  <!-- กลุ่มที่ 2: อะไหล่ที่เคลมร่วมกัน -->
                  <tr class="group-header">
                    <td colspan="10" class="text-danger">อะไหล่ที่เคลมร่วมกัน</td>
                  </tr>
                  
                  <tr id="add-assoc-row" class="bg-white">
                    <td colspan="10" class="p-2 text-center border-bottom">
                      <button type="button" class="btn-action" id="btn-add-assoc" class="btn-action bg-white text-primary-orange border-dashed-orange px-3 py-1 fs-sm mx-auto d-inline-block">+ เพิ่มอะไหล่เคลมร่วม</button>
                    </td>
                  </tr>

                  <tr class="summary-row">
                    <td colspan="5" class="pt-4 pb-4">ยอดรวม</td>
                    <td class="center" id="sum-qty">3</td>
                    <td class="center" id="sum-money" class="text-right pr-3">126.75</td>
                    <td colspan="3"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

      </div> <!-- ปิด Card ส่วนของอะไหล่ -->

      <!-- Card 6: ค่าแรง -->
      <div class="edit-card edit-card p-4 bg-light-alt">
        <div class="section-title">
          ค่าแรง
        </div>
        <div class="grid-2">
          <!-- Left side labor -->
          <div class="d-flex flex-column gap-15">
            <div class="form-group row-group">
              <label class="form-label req">จำนวน FRT</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="number" step="0.01" class="form-control num" id="labor-frt" value="0.00"> <span class="w-35 d-inline-block flex-shrink-0">ชม.</span>
              </div>
            </div>
            <div class="form-group row-group">
              <label class="form-label">FRT. Rate/hr</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="number" step="0.01" class="form-control num" id="labor-rate" value="0.00"> <span class="w-35 d-inline-block flex-shrink-0">บาท</span>
              </div>
            </div>
            <div class="form-group row-group">
              <label class="form-label">รวมค่าแรง</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="text" class="form-control num" id="labor-total" value="0.00" readonly> <span class="w-35 d-inline-block flex-shrink-0">บาท</span>
              </div>
            </div>
            <div class="form-group row-group">
              <label class="form-label">รวมค่าอะไหล่</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="text" class="form-control num" id="labor-parts-total" value="0.00" readonly> <span class="w-35 d-inline-block flex-shrink-0">บาท</span>
              </div>
            </div>
          </div>
          <!-- Right side calculation -->
          <div class="d-flex flex-column gap-15">
              <div class="form-group row-group">
              <label class="form-label">อัตราค่าการจัดการ</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="number" step="0.1" class="form-control num" id="manage-pct" value="0.00"> <span class="w-35 d-inline-block flex-shrink-0">%</span>
              </div>
            </div>
              <div class="form-group row-group">
              <label class="form-label">ค่าการจัดการ</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="text" class="form-control num" id="manage-fee" value="0.00" readonly> <span class="w-35 d-inline-block flex-shrink-0">บาท</span>
              </div>
            </div>
              <div class="form-group row-group">
              <label class="form-label">ค่าใช้จ่ายอื่นๆ</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="number" step="0.01" class="form-control num" id="other-fee" value="0.00"> <span class="w-35 d-inline-block flex-shrink-0">บาท</span>
              </div>
            </div>
              <div class="form-group row-group">
              <label class="form-label fw-600 text-primary-orange">รวมเงินเคลมสุทธิ</label>
              <div class="d-flex gap-10 align-items-center w-100">
                <input type="text" class="form-control num" id="grand-total" value="173.09" class="verification-total" readonly> <span class="w-35 d-inline-block flex-shrink-0">บาท</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      
      <div class="edit-card" id="verification-section" class="verification-box">
        <div class="section-title section-title verification-title">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          ผลการตรวจสอบและอนุมัติ
        </div>
        
        <div class="grid-2">
          <div class="checklist-container">
            <label class="form-label mb-1">รายการตรวจสอบ (Checklist)</label>
            <label class="checklist-item">
              <input type="checkbox" class="custom-check custom-check checklist-checkbox"> 
              <span class="checklist-text">ตรวจสอบความถูกต้องของข้อมูลลูกค้าและหมายเลขตัวถัง</span>
            </label>
            <label class="checklist-item">
              <input type="checkbox" class="custom-check custom-check checklist-checkbox"> 
              <span class="checklist-text">ตรวจสอบรายละเอียดและการแนบรูปภาพประกอบปัญหา</span>
            </label>
            <label class="checklist-item">
              <input type="checkbox" class="custom-check custom-check checklist-checkbox"> 
              <span class="checklist-text">ตรวจสอบรายการอะไหล่ ค่าแรง และยอดเคลมสุทธิว่าถูกต้องเหมาะสม</span>
            </label>
          </div>
          
          <div class="d-flex flex-column gap-15">
            <div class="form-group row-group">
              <label class="form-label req label-120">ผลพิจารณา</label>
              <select class="form-control verification-select">
                <option value="">-- กรุณาเลือกผลการตรวจสอบ --</option>
                <option value="approve" class="text-success">อนุมัติการเคลม</option>
                <option value="reject" class="text-danger">ไม่อนุมัติ</option>
                <option value="return" class="text-warning">ตีกลับไปแก้ไข</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">หมายเหตุ / ความเห็นผู้ตรวจสอบ</label>
              <textarea class="form-control" rows="3" placeholder="ระบุเหตุผล หากไม่อนุมัติหรือตีกลับ..."></textarea>
            </div>
          </div>
          
          <div class="form-group row-group form-group row-group signature-row">
             <label class="form-label req label-120">ผู้ตรวจสอบ</label>
             <input type="text" class="form-control" placeholder="ชื่อ-นามสกุล ผู้ตรวจสอบ">
          </div>
          <div class="form-group row-group form-group row-group signature-row">
             <label class="form-label req label-120">วันที่ตรวจสอบ</label>
             <input type="date" class="form-control" value="2026-03-24">
          </div>
        </div>
        
        <div class="signature-actions">
          <a href="check.php" class="btn-action btn-cancel">ยกเลิก</a>
          <button type="button" class="btn-action btn-save">บันทึกผลการตรวจสอบ</button>
        </div>
      </div>

    </div> 
    
  </div>

  <!-- ดูรูป -->
  <div class="modal-overlay" id="image-modal">
    <div class="modal-close" id="modal-close">×</div>
    <img src="" id="modal-img" class="modal-content" alt="Enlarged view">
  </div>

  <!-- คิดเลข -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const partsTbody = document.getElementById('parts-tbody');
      
      function calculateParts() {
        let sumQty = 0;
        let sumTotal = 0;
        
        const rows = document.querySelectorAll('.part-row');
        // Update row numbers cleanly
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
        document.getElementById('sum-money').textContent = sumTotal.toFixed(2);
        
        // Update labor parts total
        document.getElementById('labor-parts-total').value = sumTotal.toFixed(2);
        calculateLaborAndGrandTotal();
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
      
      // Init existing rows
      document.querySelectorAll('.part-row').forEach(attachRowEvents);
      
      // Helper function to create a new row
      function createNewPartRow() {
        const tr = document.createElement('tr');
        tr.className = 'part-row';
        tr.innerHTML = `
           <td></td>
           <td><input type="text" class="form-control sm" placeholder="รหัสสินค้า"></td>
           <td><input type="text" class="form-control sm" placeholder="ชื่อสินค้า"></td>
           <td><input type="text" class="form-control sm"></td>
           <td class="center"><input type="number" step="0.01" class="form-control sm num part-price" value="0.00"></td>
           <td class="center"><input type="number" class="form-control sm num part-qty" value="1"></td>
           <td class="center"><input type="text" class="form-control sm num part-total" value="0.00" readonly></td>
           <td class="center"><input type="checkbox" class="custom-check"></td>
           <td class="center"><input type="checkbox" class="custom-check"></td>
           <td class="center"><button type="button" class="btn-remove-part btn-remove-part-icon">×</button></td>
        `;
        attachRowEvents(tr);
        return tr;
      }

      // Add อะไหล่หลัก button
      document.getElementById('btn-add-main').addEventListener('click', function() {
        const tr = createNewPartRow();
        const addRow = document.getElementById('add-main-row');
        partsTbody.insertBefore(tr, addRow);
        calculateParts();
      });

      // Add อะไหล่เคลมร่วม button
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
      
      
      // --- อัพรูป LOGIC ---
      const imageUpload = document.getElementById('image-upload');
      const galleryGrid = document.getElementById('gallery-grid');
      const imageModal = document.getElementById('image-modal');
      const modalImg = document.getElementById('modal-img');
      let imageIndex = 2; // starting counter for dummy names
      
      imageUpload.addEventListener('change', function(e) {
        const files = e.target.files;
        const vinNumber = document.getElementById('vin_number').value || 'UnknownVIN';
        
        for (let i = 0; i < files.length; i++) {
           const file = files[i];
           const reader = new FileReader();
           
           reader.onload = function(evt) {
              const ext = file.name.split('.').pop() || 'jpg';
              // Naming convention as requested: หัวข้อ(รูปภาพปัญหา) + หมายเลขตัวถัง 
              const newFileName = `รูปภาพปัญหา_${vinNumber}_${imageIndex}.${ext}`;
              createImageItem(evt.target.result, newFileName);
              imageIndex++;
           }
           reader.readAsDataURL(file);
        }
        // Reset input so saem file can be uploaded again if needed
        this.value = '';
      });
      
      function createImageItem(src, title) {
        const div = document.createElement('div');
        div.className = 'gallery-item';
        div.innerHTML = `
          <img src="${src}" alt="uploaded image" class="preview-img cursor-pointer"title="คลิกเพื่อขยาย">
          <div class="img-preview-footer">
            <span class="img-preview-title" title="${title}">${title}</span>
            <div class="img-preview-actions">
              <a href="${src}" download="${title}" class="img-download-link" title="ดาวน์โหลดพร้อมชื่อใหม่">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              </a>
              <button type="button" class="btn-remove-img btn-remove-img" title="ลบรูป">×</button>
            </div>
          </div>
        `;
        
        div.querySelector('.btn-remove-img').addEventListener('click', function() { div.remove(); });
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
      
      // Wire up initial gallery item
      document.querySelectorAll('.btn-remove-img').forEach(btn => btn.addEventListener('click', function() {
        this.closest('.gallery-item').remove();
        updateImgCountBadge();
      }));
      document.querySelectorAll('.preview-img').forEach(img => img.addEventListener('click', function() {
         modalImg.src = this.src;
         imageModal.style.display = 'flex';
      }));
      
      // Modal close logic
      document.getElementById('modal-close').addEventListener('click', () => imageModal.style.display = 'none');
      imageModal.addEventListener('click', function(e) {
        if(e.target === imageModal) imageModal.style.display = 'none';
      });
      
      // Initial calculation
      calculateParts();
    });
  </script>
</body>
</html>
