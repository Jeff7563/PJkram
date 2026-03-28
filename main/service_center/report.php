<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>รายงาน - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-report.css">
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

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
              <option value="approve">ตรวจสอบแล้ว</option>
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

      <div class="table-responsive bg-white rounded shadow-sm">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-3">วันที่</th>
              <th>เลขที่เอกสาร</th>
              <th>สาขา</th>
              <th>ชื่อผู้ใช้งาน</th>
              <th>ประเภทรถ</th>
              <th>สถานะ</th>
              <th class="text-center pe-3">Export</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="ps-3">24/03/2569</td>
              <td>TS01-001</td>
              <td>TEST</td>
              <td>TEST</td>
              <td>TEST</td>
              <td><span class="badge bg-success px-3 py-2 fw-normal fs-xs">ตรวจสอบแล้ว</span></td>
              <td class="text-center pe-3">
                <button class="btn btn-sm btn-outline-primary btn-export-row" data-doc="TS01-001">Export</button>
              </td>
            </tr>
            <tr>
              <td class="ps-3">23/03/2569</td>
              <td>TS01-002</td>
              <td>TEST</td>
              <td>TEST</td>
              <td>TEST</td>
              <td><span class="badge bg-success px-3 py-2 fw-normal fs-xs">ตรวจสอบแล้ว</span></td>
              <td class="text-center pe-3">
                <button class="btn btn-sm btn-outline-primary btn-export-row" data-doc="TS01-002">Export</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <div class="modal-overlay" id="export-modal">
    <div class="modal-content" style="max-width: 400px; text-align: center; position: relative;">
      <div class="modal-close" id="close-export-modal">&times;</div>
      <h3 class="export-modal-title">เลือกประเภทไฟล์</h3>
      <p class="export-modal-desc">ดาวน์โหลดเอกสาร: <span id="export-doc-number" class="fw-bold text-primary-orange"></span></p>
      
      <div class="row g-3 mt-4">
        <div class="col-6">
          <button class="btn btn-export-pdf w-100 p-3 border d-flex flex-column align-items-center gap-2">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 0 0 0 2 2h12a2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span class="fw-bold small text-dark">PDF</span>
          </button>
        </div>
        <div class="col-6">
          <button class="btn btn-export-excel w-100 p-3 border d-flex flex-column align-items-center gap-2">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 0 0 0 2 2h12a2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="17"></line><line x1="16" y1="13" x2="8" y2="17"></line></svg>
            <span class="fw-bold small text-dark">Excel</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // ปุ่มรีเซ็ต
      const btnReset = document.querySelector('.btn-reset');
      if (btnReset) {
        btnReset.addEventListener('click', function() {
          const filterBar = this.closest('.filter-bar');
          filterBar.querySelectorAll('input[type="text"]').forEach(input => input.value = '');
          filterBar.querySelectorAll('select').forEach(select => select.selectedIndex = 0);
          filterBar.querySelectorAll('input[type="date"]').forEach(input => input.value = '');
        });
      }

      // แสดง Export Modal ทันทีที่กดปุ่ม Export ในตาราง
      const exportModal = document.getElementById('export-modal');
      const closeExportBtn = document.getElementById('close-export-modal');
      const exportDocNumber = document.getElementById('export-doc-number');

      document.querySelectorAll('.btn-export-row').forEach(btn => {
        btn.addEventListener('click', function() {
          const docNo = this.getAttribute('data-doc');
          if (exportDocNumber) exportDocNumber.textContent = docNo;
          if (exportModal) exportModal.style.display = 'flex';
        });
      });

      // ปิด Modal เมื่อกดกากบาท
      if (closeExportBtn) {
        closeExportBtn.addEventListener('click', () => {
          if (exportModal) exportModal.style.display = 'none';
        });
      }

      // ปิด Modal เมื่อคลิกพื้นที่ว่างข้างนอก
      if (exportModal) {
        exportModal.addEventListener('click', function(e) {
          if(e.target === exportModal) {
            exportModal.style.display = 'none';
          }
        });
      }

      // แจ้งเตือนเมื่อกดเลือกประเภทไฟล์
      const btnPdf = document.querySelector('.btn-export-pdf');
      if (btnPdf) {
        btnPdf.addEventListener('click', () => {
          alert("กำลังดาวน์โหลดไฟล์ PDF ของเอกสารเลขที่: " + (exportDocNumber ? exportDocNumber.textContent : ""));
          if (exportModal) exportModal.style.display = 'none';
        });
      }

      const btnExcel = document.querySelector('.btn-export-excel');
      if (btnExcel) {
        btnExcel.addEventListener('click', () => {
          alert("กำลังดาวน์โหลดไฟล์ Excel ของเอกสารเลขที่: " + (exportDocNumber ? exportDocNumber.textContent : ""));
          if (exportModal) exportModal.style.display = 'none';
        });
      }
    });
  </script>

</body>
</html>
