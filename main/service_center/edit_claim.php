<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>แก้ไขข้อมูลเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-edit_claim.css">
</head>
<body>

  <?php 
    $current_page = 'history.php';
    include 'includes/sidebar.php'; 
  ?>

  <!-- Main Content -->
  <div class="main-content">
    
      <div class="filter-bar">
        <div style="font-size: 1.25rem; font-weight: 600;">ประวัติเคลม <span style="color: #999; font-weight: 400;">/ แก้ไขข้อมูล</span></div>
        <div class="filter-group" style="justify-content: flex-end;">
          <a href="history.php" class="btn-action" style="background-color: var(--primary-orange);">
              <svg style="vertical-align: middle; margin-right: 5px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
              บันทึก
          </a>
          <a href="history.php" class="btn-action" style="background-color: #6c757d;">
              <svg style="vertical-align: middle; margin-right: 5px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg>
              ปิด
          </a>
        </div>
      </div>

    <!-- Edit Form blocks -->
    <div class="edit-container" style="margin-bottom: 40px;">
      
      <!-- Card 1: Main Info -->
      <div class="edit-card">
        <div class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
          แก้ไขข้อมูล
        </div>
          <div class="grid-2">
            <div class="form-group row-group">
              <label class="form-label">สาขา</label>
              <select class="form-control">
                <option>สำนักงานใหญ่</option>
              </select>
            </div>
            
            <div class="form-group row-group" style="align-items: center;">
              <label class="form-label">ผู้ดูแลประจำร้าน</label>
              <span class="text-danger" style="margin-top: 0;">MR TEST</span>
            </div>
            
            <div class="form-group row-group">
              <label class="form-label req">ประเภทการเคลม</label>
              <div style="display:flex; gap:10px; width:100%;">
                <select class="form-control"><option>รถลูกค้า</option></select>
                <select class="form-control"><option>เคลมปกติ</option></select>
              </div>
            </div>
            <div></div>
            
            <div class="form-group row-group">
              <label class="form-label">เลขที่เอกสาร</label>
              <div style="display:flex; gap:15px; align-items:center; width:100%; flex-wrap:wrap;">
                <input type="text" class="form-control" value="TS01-001" readonly style="flex:1; min-width:150px;">
              </div>
            </div>
            
            <div class="form-group row-group">
              <label class="form-label">วันที่เอกสาร</label>
              <input type="date" class="form-control" value="2026-03-23">
            </div>
          </div>
        </div>
      
      <!-- Card 2: ข้อมูลผู้ใช้ & หมายเลขตัวถัง -->
      <div class="edit-card">
        <div class="section-title">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          ข้อมูลผู้ใช้
        </div>
        <div class="grid-2">
          

          <div class="form-group row-group">
            <label class="form-label req">ชื่อ-นามสกุล</label>
             <input type="text" class="form-control" placeholder="ชื่อ นามสกุล">
          </div>
          <div></div>
          
          <div class="form-group row-group" style="grid-column: 1 / -1;">
            <label class="form-label">ที่อยู่</label>
            <div style="display:flex; flex-direction:column; gap:10px; width:100%;">
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
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
          ปัญหา
        </div>
        <div class="form-group" style="margin-bottom: 25px;">
          <label class="form-label">รายละเอียดปัญหาที่ลูกค้าแจ้ง</label>
          <textarea class="form-control" rows="4" placeholder="โปรดใส่รายละเอียด"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">ผลการตรวจเช็คปัญหา วิธีการตรวจเช็ค และสาเหตุของปัญหา</label>
          <textarea class="form-control" rows="4" placeholder="โปรดใส่รายละเอียด"></textarea>
        </div>
      </div>
      
      <!-- Card 5: อะไหล่ และ ค่าแรง -->
      <div class="edit-card" style="padding:0; overflow:hidden;">
        <!-- Parts Table Area -->
          <div style="padding: 25px 25px 10px 25px;">
            <div class="section-title" style="display:flex; justify-content:space-between; border-bottom:none;">
              <div style="display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
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
                    <td colspan="7" style="color:#e74c3c;">อะไหล่หลัก</td>
                    <td colspan="3" class="center" style="font-weight:normal; font-size: 0.85rem; padding-top:20px; text-align:right; padding-right:10px;">
                      <label style="cursor:pointer;"><input type="checkbox" class="custom-check">ไม่ระบุจำนวน อะไหล่หลัก</label>
                    </td>
                  </tr>
                  
                  <tr id="add-main-row" style="background:#fff;">
                    <td colspan="10" style="padding: 10px; text-align:center; border-bottom:1px solid #eaeaea;">
                      <button type="button" class="btn-action" id="btn-add-main" style="background:#fff; color:var(--primary-orange); border: 1px dashed var(--primary-orange); padding: 5px 15px; font-size:0.85rem; margin:0 auto; display:inline-block;">+ เพิ่มอะไหล่หลัก</button>
                    </td>
                  </tr>

                  <!-- กลุ่มที่ 2: อะไหล่ที่เคลมร่วมกัน -->
                  <tr class="group-header">
                    <td colspan="10" style="color:#e74c3c;">อะไหล่ที่เคลมร่วมกัน</td>
                  </tr>
                  
                  <tr id="add-assoc-row" style="background:#fff;">
                    <td colspan="10" style="padding: 10px; text-align:center; border-bottom:1px solid #eaeaea;">
                      <button type="button" class="btn-action" id="btn-add-assoc" style="background:#fff; color:var(--primary-orange); border: 1px dashed var(--primary-orange); padding: 5px 15px; font-size:0.85rem; margin:0 auto; display:inline-block;">+ เพิ่มอะไหล่เคลมร่วม</button>
                    </td>
                  </tr>

                  <tr class="summary-row">
                    <td colspan="5" style="padding-top:20px; padding-bottom:20px;">ยอดรวม</td>
                    <td class="center" id="sum-qty">3</td>
                    <td class="center" id="sum-money" style="text-align: right; padding-right:15px;">126.75</td>
                    <td colspan="3"></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

      </div> <!-- ปิด Card ส่วนของอะไหล่ -->

      <!-- Card 6: ค่าแรง -->
      <div class="edit-card" style="padding: 25px; background: #fafafa;">
          <div class="section-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            ค่าแรง
          </div>
          <div class="grid-2">
            <!-- Left side labor -->
            <div style="display:flex; flex-direction:column; gap:15px;">
              <div class="form-group row-group">
                <label class="form-label req">จำนวน FRT</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="number" step="0.01" class="form-control num" id="labor-frt" value="0.00"> <span style="width:35px; display:inline-block; flex-shrink:0;">ชม.</span>
                </div>
              </div>
              <div class="form-group row-group">
                <label class="form-label">FRT. Rate/hr</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="number" step="0.01" class="form-control num" id="labor-rate" value="0.00"> <span style="width:35px; display:inline-block; flex-shrink:0;">บาท</span>
                </div>
              </div>
              <div class="form-group row-group">
                <label class="form-label">รวมค่าแรง</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="text" class="form-control num" id="labor-total" value="0.00" readonly> <span style="width:35px; display:inline-block; flex-shrink:0;">บาท</span>
                </div>
              </div>
              <div class="form-group row-group">
                <label class="form-label">รวมค่าอะไหล่</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="text" class="form-control num" id="labor-parts-total" value="0.00" readonly> <span style="width:35px; display:inline-block; flex-shrink:0;">บาท</span>
                </div>
              </div>
            </div>
            <!-- Right side calculation -->
            <div style="display:flex; flex-direction:column; gap:15px;">
               <div class="form-group row-group">
                <label class="form-label">อัตราค่าการจัดการ</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="number" step="0.1" class="form-control num" id="manage-pct" value="0.00"> <span style="width:35px; display:inline-block; flex-shrink:0;">%</span>
                </div>
              </div>
               <div class="form-group row-group">
                <label class="form-label">ค่าการจัดการ</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="text" class="form-control num" id="manage-fee" value="0.00" readonly> <span style="width:35px; display:inline-block; flex-shrink:0;">บาท</span>
                </div>
              </div>
               <div class="form-group row-group">
                <label class="form-label">ค่าใช้จ่ายอื่นๆ</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="number" step="0.01" class="form-control num" id="other-fee" value="0.00"> <span style="width:35px; display:inline-block; flex-shrink:0;">บาท</span>
                </div>
              </div>
               <div class="form-group row-group">
                <label class="form-label" style="font-weight:600; color:var(--primary-orange);">รวมเงินเคลมสุทธิ</label>
                <div style="display:flex; gap:10px; align-items:center; width:100%;">
                  <input type="text" class="form-control num" id="grand-total" value="173.09" style="background:#fff5f0; border-color:var(--primary-orange); color:var(--primary-orange); font-weight:bold; font-size:1.1rem;" readonly> <span style="width:35px; display:inline-block; flex-shrink:0;">บาท</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      
      <!-- Card 6: รูปภาพปัญหา -->
      <div class="edit-card">
        <div class="section-title" style="display:flex; justify-content:space-between; align-items:center;">
          <div style="display:flex; align-items:center; gap:8px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
            รูปภาพปัญหา
          </div>
          <label class="btn-action" style="cursor:pointer; margin:0; padding:6px 15px; font-size:0.9rem;">
            + อัปโหลดรูปภาพ
            <input type="file" id="image-upload" multiple accept="image/*" style="display:none;">
          </label>
        </div>
        
        <div class="gallery-grid" id="gallery-grid">
      </div>
      
    </div> <!-- /edit-container -->
    
  </div>

  <!-- Image Modal Viewer -->
  <div class="modal-overlay" id="image-modal">
    <div class="modal-close" id="modal-close">×</div>
    <img src="" id="modal-img" class="modal-content" alt="Enlarged view">
  </div>

  <!-- JavaScript interactivty calculations -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const partsTbody = document.getElementById('parts-tbody');
      
      // Calculate parts table
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
           <td class="center"><button type="button" class="btn-remove-part" style="color:#e74c3c; background:none; border:none; cursor:pointer; font-weight:bold; font-size:1.1rem;">×</button></td>
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
      
      
      // --- IMAGE UPLOAD LOGIC ---
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
          <img src="${src}" alt="uploaded image" class="preview-img" style="cursor:pointer;" title="คลิกเพื่อขยาย">
          <div style="padding: 10px; display:flex; justify-content:space-between; align-items:center; background:#fff; border-top:1px solid #eee;">
            <span style="font-size:0.85rem; color:#555; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:90px;" title="${title}">${title}</span>
            <div style="display:flex; gap:12px; align-items:center;">
              <a href="${src}" download="${title}" style="text-decoration:none; color:#777; display:flex;" title="ดาวน์โหลดพร้อมชื่อใหม่" onmouseover="this.style.color='var(--primary-orange)'" onmouseout="this.style.color='#777'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              </a>
              <button type="button" class="btn-remove-img" style="color:#e74c3c; background:none; border:none; cursor:pointer; font-weight:bold; font-size:1.3rem; padding:0; display:flex;" title="ลบรูป">×</button>
            </div>
          </div>
        `;
        
        div.querySelector('.btn-remove-img').addEventListener('click', function() { div.remove(); });
        div.querySelector('.preview-img').addEventListener('click', function() {
           modalImg.src = this.src;
           imageModal.style.display = 'flex';
        });
        
        galleryGrid.appendChild(div);
      }
      
      // Wire up initial gallery item
      document.querySelectorAll('.btn-remove-img').forEach(btn => btn.addEventListener('click', function() { this.closest('.gallery-item').remove(); }));
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
