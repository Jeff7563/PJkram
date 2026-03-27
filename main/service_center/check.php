<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตรวจเช็ค - ระบบจัดการฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-check.css">
</head>
<body>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    
    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="filter-group">
        <div class="filter-group">
          <input type="text" placeholder="ค้นหาเอกสาร..." class="form-control min-w-250">
        </div>
        <select>
          <option value="">สาขา</option>
          <option value="sakon_nakhon">สกลนคร</option>
        </select>
        <select>
          <option value=""selected>สถานะ</option>
          <option value="approve">อนุมัติการเคลม</option>
          <option value="not_approve">ไม่อนุมัติ</option>
          <option value="return">ตีกลับไปแก้ไข</option>
        </select>
        <select>
          <option value=""selected>ประเภทการเคลม</option>
          <option value="claim">เคลมรถลูกค้า</option>
          <option value="pre_sale">	เคลมรถก่อนขาย</option>
          <option value="technical">	เคลมปัญหาทางเทคนิค</option>
        </select>
        <input type="date" class="date-input" value="2026-03-24">
      </div>
      <div class="filter-group justify-content-end">
        <button class="btn-search">ค้นหา</button>
        <button class="btn-reset">รีเซ็ต</button>
      </div>
    </div>

    <!-- Data Table Container for Check Page -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th class="export-col" style="display: none; width: 40px; text-align: center;">
              <input type="checkbox" id="selectAll" style="transform: scale(1.2); cursor: pointer;">
            </th>
            <th>เลขเอกสาร</th>
            <th>สาขา</th>
            <th>เลขตัวถัง</th>
            <th>ประเภทรถ</th>
            <th>ประเภทการเคลม</th>
            <th>การดำเนินการ</th>
            <th class="text-center">สถานะ</th> 
            <th class="text-center">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="export-col" style="display: none; text-align: center;">
              <input type="checkbox" class="row-checkbox" value="C001-270369" style="transform: scale(1.2); cursor: pointer;">
            </td>
            <td>C001-270369</td>
            <td>สกลนคร</td>
            <td>AGAS651ASG5</td>
            <td>รถมือสอง/Honda/A</td>
            <td>เคลมรถลูกค้า</td>
            <td>เปลี่ยนคัน</td>
            <td class="text-center">
              <span class="status-badge status-approve">อนุมัติเคลม</span>
            </td>
            <td class="text-center">
              <a href="verify_claim.php" class="btn-action px-3 py-1 fs-md">ตรวจสอบ</a>
            </td>
          </tr>
          <tr>
            <td class="export-col" style="display: none; text-align: center;">
              <input type="checkbox" class="row-checkbox" value="C001-270369" style="transform: scale(1.2); cursor: pointer;">
            </td>
            <td>C002-270369</td>
            <td>สกลนคร</td>
            <td>AHQHD52HA4A</td>
            <td>รถมือสอง/Honda/C</td>
            <td>เคลมรถก่อนขาย</td>
            <td>ส่งซ่อมที่สนญ.</td>
            <td class="text-center">
              <span class="status-badge status-pending">รอตรวจสอบ</span>
            </td>
            <td class="text-center">
              <a href="verify_claim.php" class="btn-action px-3 py-1 fs-md">ตรวจสอบ</a>
            </td>
          </tr>
          <tr>
            <td class="export-col" style="display: none; text-align: center;">
              <input type="checkbox" class="row-checkbox" value="C001-270369" style="transform: scale(1.2); cursor: pointer;">
            </td>
            <td>C003-270369</td>
            <td>สกลนคร</td>
            <td>GHAHRTYK588</td>
            <td>รถใหม่/Honda/A</td>
            <td>เคลมปัญหาทางเทคนิค</td>
            <td>ซ่อมที่สาขา</td>
            <td class="text-center">
              <span class="status-badge status-reject">ไม่อนุมัติ</span>
            </td>
            <td class="text-center">
              <a href="verify_claim.php" class="btn-action px-3 py-1 fs-md">ตรวจสอบ</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
  </div>

  <div class="floating-export">
    <button id="btnCancelExport" class="btn-floating-cancel">ยกเลิก</button>
    
    <button id="btnExportToggle" class="btn-floating-export">
      <svg id="exportIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="7 10 12 15 17 10"></polyline>
        <line x1="12" y1="15" x2="12" y2="3"></line>
      </svg>
      <span id="exportBtnText">Export</span>
    </button>
  </div>

   <div id="exportModal" class="export-modal-overlay">
    <div class="export-modal-box">
      <button class="export-modal-close" id="closeExportModal">&times;</button>
      <h3 class="export-modal-title">เลือกประเภทไฟล์</h3>
      <p class="export-modal-subtitle">ดาวน์โหลดเอกสาร: <span id="exportDocList"></span></p>
      
      <div class="export-options">
        <button class="export-opt-btn" id="exportPDFBtn">
          <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
          </svg>
          <span>PDF</span>
        </button>
        
        <button class="export-opt-btn" id="exportExcelBtn">
          <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="9" y1="15" x2="15" y2="9"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
          </svg>
          <span>Excel</span>
        </button>
      </div>
    </div>
  </div>

  <script>
    // ปุ่มรีเซ็ต
    document.querySelector('.btn-reset').addEventListener('click', function() {
      const filterBar = this.closest('.filter-bar');
      filterBar.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
      filterBar.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
      filterBar.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
    });

    // ระบบ Export Data
    const btnExportToggle = document.getElementById('btnExportToggle');
    const btnCancelExport = document.getElementById('btnCancelExport');
    const exportBtnText = document.getElementById('exportBtnText');
    const exportIcon = document.getElementById('exportIcon');
    const exportCols = document.querySelectorAll('.export-col');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');

    let isExportMode = false;

    // เมื่อกดปุ่ม Export หลัก
    btnExportToggle.addEventListener('click', function() {
      if (!isExportMode) {
        // --- กดครั้งแรก: เข้าสู่โหมดเลือกข้อมูล ---
        isExportMode = true;
        
        // โชว์คอลัมน์ Checkbox และปุ่มยกเลิก
        exportCols.forEach(col => col.style.display = 'table-cell');
        btnCancelExport.style.display = 'block';
        
        // เปลี่ยนหน้าตาปุ่มยืนยันให้เป็นสีส้ม เพื่อให้รู้ว่ากำลังจะทำ Action
        exportBtnText.textContent = 'ยืนยันดาวน์โหลด';
        this.style.background = 'linear-gradient(135deg, #f39c12, #e67e22)';
        this.style.boxShadow = '0 6px 15px rgba(230, 126, 34, 0.4)';
        exportIcon.style.display = 'none'; // ซ่อนไอคอนดาวน์โหลด
        
      } else {
        // --- กดครั้งที่สอง: ยืนยันการดาวน์โหลด ---
        const selectedDocs = Array.from(rowCheckboxes)
                                  .filter(cb => cb.checked)
                                  .map(cb => cb.value);

        if (selectedDocs.length === 0) {
          alert('กรุณาติ๊กเลือกข้อมูลอย่างน้อย 1 รายการครับ!');
          return;
        }

        // ==========================================
        // อัปเดตใหม่: โชว์ Modal แทนการ Alert
        // ==========================================
        // 1. นำเลขเอกสารไปแสดงใน Popup (ถ้าเลือกเยอะให้โชว์แค่ 2 อันแรก แล้วต่อด้วย ...)
        let docText = selectedDocs.join(', ');
        if (selectedDocs.length > 2) {
            docText = selectedDocs[0] + ', ' + selectedDocs[1] + ' และอีก ' + (selectedDocs.length - 2) + ' รายการ';
        }
        document.getElementById('exportDocList').textContent = docText;
        
        // 2. เปิด Popup
        document.getElementById('exportModal').classList.add('show');
      }
    });

    // ==========================================
    // ระบบจัดการใน Modal เลือกไฟล์
    // ==========================================
    const exportModal = document.getElementById('exportModal');
    
    // ฟังก์ชันปิด Modal
    function closeModal() {
      exportModal.classList.remove('show');
    }

    // กดปุ่มกากบาทปิด
    document.getElementById('closeExportModal').addEventListener('click', closeModal);

    // กดเลือก PDF
    document.getElementById('exportPDFBtn').addEventListener('click', function() {
      alert('กำลังดาวน์โหลดไฟล์ PDF...');
      closeModal();
      resetExportMode(); // รีเซ็ตหน้าตารางกลับเป็นปกติ
    });

    // กดเลือก Excel
    document.getElementById('exportExcelBtn').addEventListener('click', function() {
      alert('กำลังดาวน์โหลดไฟล์ Excel...');
      closeModal();
      resetExportMode(); // รีเซ็ตหน้าตารางกลับเป็นปกติ
    });

    // กดพื้นที่สีดำด้านนอกเพื่อปิด Popup
    exportModal.addEventListener('click', function(e) {
      if (e.target === this) closeModal();
    });

    // เมื่อกดปุ่มยกเลิก
    btnCancelExport.addEventListener('click', resetExportMode);

    // ฟังก์ชันรีเซ็ตกลับไปเป็นสถานะเริ่มต้น
    function resetExportMode() {
      isExportMode = false;
      
      // ซ่อนคอลัมน์ Checkbox และปุ่มยกเลิก
      exportCols.forEach(col => col.style.display = 'none');
      btnCancelExport.style.display = 'none';
      
      // คืนค่าหน้าตาปุ่ม Export กลับเป็นสีเขียว
      exportBtnText.textContent = 'Export';
      btnExportToggle.style.background = 'linear-gradient(135deg, #1D6F42, #27ae60)';
      btnExportToggle.style.boxShadow = '0 6px 15px rgba(39, 174, 96, 0.4)';
      exportIcon.style.display = 'block';

      // เอาเครื่องหมายติ๊กออกทั้งหมด
      selectAll.checked = false;
      rowCheckboxes.forEach(cb => cb.checked = false);
    }

    // ระบบติ๊กเลือกทั้งหมด (Select All)
    selectAll.addEventListener('change', function() {
      rowCheckboxes.forEach(cb => cb.checked = this.checked);
    });
  
  </script>
</body>
</html>
