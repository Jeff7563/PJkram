<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตรวจเช็ค - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-check.css">
</head>
<body>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container-fluid p-0">
    
      <div class="filter-bar mb-4">
        <div class="row w-100 align-items-center g-3">
          <div class="col-12 col-md-4">
            <input type="text" placeholder="ค้นหาเอกสาร..." class="form-control">
          </div>
          <div class="col-12 col-md-2">
            <select class="form-select">
              <option value="">สาขา</option>
              <option value="bangkok">กรุงเทพฯ</option>
              <option value="chiangmai">เชียงใหม่</option>
              <option value="khonkaen">ขอนแก่น</option>
            </select>
          </div>
          <div class="col-12 col-md-2">
            <select class="form-select">
              <option value="" selected>สถานะ</option>
              <option value="pending">รอการตรวจสอบ</option>
              <option value="approve">อนุมัติแล้ว</option>
              <option value="reject">ไม่อนุมัติ</option>
            </select>
          </div>
          <div class="col-12 col-md-2">
            <input type="date" class="form-control" value="2026-03-24">
          </div>
          <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-search flex-grow-1">ค้นหา</button>
            <button class="btn btn-reset btn-light border flex-grow-1">รีเซ็ต</button>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="export-col text-center" style="display: none; width: 40px;">
                  <input type="checkbox" id="selectAll" class="form-check-input">
                </th>
                <th class="ps-3" width="140">เลขที่เอกสาร</th>
                <th>สาขา</th>
                <th>หมายเลขตัวถัง</th>
                <th>ประเภทรถ</th>
                <th>อาการเสีย</th>
                <th>สาเหตุ</th>
                <th class="text-center" width="150">สถานะ</th>
                <th class="text-center pe-3" width="120">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="export-col text-center" style="display: none;">
                  <input type="checkbox" class="row-checkbox form-check-input" value="C001-270369">
                </td>
                <td class="ps-3 fw-bold">C001-270369</td>
                <td>สำนักงานใหญ่</td>
                <td>AGAS651ASG5</td>
                <td>รถลูกค้า/Honda/A</td>
                <td>เครื่องยนต์สตาร์ทติดยาก</td>
                <td>ตรวจสอบระบบน้ำมัน</td>
                <td class="text-center">
                  <span class="badge bg-success px-3 fs-xs fw-normal">ตรวจสอบแล้ว</span>
                </td>
                <td class="text-center pe-3">
                  <a href="verify_claim.php" class="btn btn-sm btn-outline-primary px-3">ตรวจสอบ</a>
                </td>
              </tr>
              <tr>
                <td class="export-col text-center" style="display: none;">
                  <input type="checkbox" class="row-checkbox form-check-input" value="C002-270369">
                </td>
                <td class="ps-3 fw-bold">C002-270369</td>
                <td>สำนักงานใหญ่</td>
                <td>AHQHD52HA4A</td>
                <td>รถลูกค้า/Honda/C</td>
                <td>มีเสียงดังผิดปกติบริเวณห้องเครื่อง</td>
                <td>เฟืองแคมชาร์ฟเสื่อมสภาพ</td>
                <td class="text-center">
                  <span class="badge bg-warning text-dark px-3 fs-xs fw-normal">รอการตรวจสอบ</span>
                </td>
                <td class="text-center pe-3">
                  <a href="verify_claim.php" class="btn btn-sm btn-outline-primary px-3">ตรวจสอบ</a>
                </td>
              </tr>
              <tr>
                <td class="export-col text-center" style="display: none;">
                  <input type="checkbox" class="row-checkbox form-check-input" value="C003-270369">
                </td>
                <td class="ps-3 fw-bold">C003-270369</td>
                <td>สำนักงานใหญ่</td>
                <td>GHAHRTYK588</td>
                <td>รถสาธิต/Honda/A</td>
                <td>ปุ่มควบคุมระดับน้ำมันทำงานผิดปกติ</td>
                <td>แผงสวิตช์ควบคุมชำรุด</td>
                <td class="text-center">
                  <span class="badge bg-danger px-3 fs-xs fw-normal">ไม่อนุมัติ</span>
                </td>
                <td class="text-center pe-3">
                  <a href="verify_claim.php" class="btn btn-sm btn-outline-primary px-3">ตรวจสอบ</a>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Floating Export -->
  <div class="floating-export">
    <button id="btnCancelExport" class="btn-floating-cancel" style="display: none;">ยกเลิก</button>
    <button id="btnExportToggle" class="btn-floating-export d-flex align-items-center gap-2">
      <svg id="exportIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="7 10 12 15 17 10"></polyline>
        <line x1="12" y1="15" x2="12" y2="3"></line>
      </svg>
      <span id="exportBtnText">Export</span>
    </button>
  </div>

  <!-- Export Modal -->
  <div id="exportModal" class="export-modal-overlay">
    <div class="export-modal-box">
      <button class="export-modal-close" id="closeExportModal">&times;</button>
      <h3 class="export-modal-title">เลือกรูปแบบการส่งออก</h3>
      <p class="export-modal-subtitle text-muted">รายการเอกสารที่เลือก: <span id="exportDocList" class="fw-bold text-primary-orange"></span></p>
      
      <div class="row g-3 mt-4">
        <div class="col-6">
          <button class="btn btn-outline-danger w-100 p-3 d-flex flex-column align-items-center gap-2" id="exportPDFBtn">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 0 0 0 2 2h12a2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
              <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            <span class="fw-bold">PDF</span>
          </button>
        </div>
        <div class="col-6">
          <button class="btn btn-outline-success w-100 p-3 d-flex flex-column align-items-center gap-2" id="exportExcelBtn">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 0 0 0 2 2h12a2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="9" y1="15" x2="15" y2="9"></line>
              <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span class="fw-bold">Excel</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Reset functionality
      const btnReset = document.querySelector('.btn-reset');
      if (btnReset) {
        btnReset.addEventListener('click', function() {
          const filterBar = this.closest('.filter-bar');
          filterBar.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
          filterBar.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
          filterBar.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
        });
      }

      // Export System
      const btnExportToggle = document.getElementById('btnExportToggle');
      const btnCancelExport = document.getElementById('btnCancelExport');
      const exportBtnText = document.getElementById('exportBtnText');
      const exportIcon = document.getElementById('exportIcon');
      const exportCols = document.querySelectorAll('.export-col');
      const rowCheckboxes = document.querySelectorAll('.row-checkbox');
      const selectAll = document.getElementById('selectAll');
      const exportModal = document.getElementById('exportModal');

      let isExportMode = false;

      if (btnExportToggle) {
        btnExportToggle.addEventListener('click', function() {
          if (!isExportMode) {
            isExportMode = true;
            exportCols.forEach(col => col.style.display = 'table-cell');
            btnCancelExport.style.display = 'block';
            exportBtnText.textContent = 'ยืนยันการส่งออก';
            this.style.background = 'linear-gradient(135deg, #f39c12, #e67e22)';
            exportIcon.style.display = 'none';
          } else {
            const selectedDocs = Array.from(rowCheckboxes)
                                      .filter(cb => cb.checked)
                                      .map(cb => cb.value);

            if (selectedDocs.length === 0) {
              alert('กรุณาเลือกเอกสารที่ต้องการส่งออกอย่างน้อย 1 รายการ!');
              return;
            }

            let docText = selectedDocs.join(', ');
            if (selectedDocs.length > 2) {
                docText = selectedDocs[0] + ', ' + selectedDocs[1] + ' และอีก ' + (selectedDocs.length - 2) + ' รายการ';
            }
            const exportDocList = document.getElementById('exportDocList');
            if (exportDocList) exportDocList.textContent = docText;
            exportModal.classList.add('show');
          }
        });
      }

      if (btnCancelExport) {
        btnCancelExport.addEventListener('click', function() {
          isExportMode = false;
          exportCols.forEach(col => col.style.display = 'none');
          this.style.display = 'none';
          exportBtnText.textContent = 'Export';
          btnExportToggle.style.background = 'linear-gradient(135deg, #1D6F42, #27ae60)';
          exportIcon.style.display = 'block';
          if (selectAll) selectAll.checked = false;
          rowCheckboxes.forEach(cb => cb.checked = false);
        });
      }

      const closeExportModal = document.getElementById('closeExportModal');
      if (closeExportModal) {
        closeExportModal.addEventListener('click', () => exportModal.classList.remove('show'));
      }
      
      const exportPDFBtn = document.getElementById('exportPDFBtn');
      if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', function() {
          alert('กำลังเตรียมไฟล์ PDF...');
          exportModal.classList.remove('show');
          btnCancelExport.click();
        });
      }

      const exportExcelBtn = document.getElementById('exportExcelBtn');
      if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function() {
          alert('กำลังเตรียมไฟล์ Excel...');
          exportModal.classList.remove('show');
          btnCancelExport.click();
        });
      }

      if (exportModal) {
        exportModal.addEventListener('click', function(e) {
          if (e.target === this) this.classList.remove('show');
        });
      }

      if (selectAll) {
        selectAll.addEventListener('change', function() {
          rowCheckboxes.forEach(cb => cb.checked = this.checked);
        });
      }
    });
  </script>
</body>
</html>
