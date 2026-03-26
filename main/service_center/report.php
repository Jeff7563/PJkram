<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>รายงาน - ระบบจัดการฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/theme.css">
  <link rel="stylesheet" href="css/styles-report.css">
</head>
<body>

  <!-- Sidebar -->
  <?php include 'includes/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content">
    
    <!-- Header & Actions -->
    <div class="filter-bar">
      <div class="filter-group">
        <div class="filter-group">
          <input type="text" placeholder="ค้นหาเอกสาร..." class="form-control min-w-250">
        </div>
        <select>
          <option value="">สาขา</option>
          <option value="bangkok">กรุงเทพฯ</option>
          <option value="chiangmai">เชียงใหม่</option>
          <option value="khonkaen">ขอนแก่น</option>
        </select>
        <select>
          <option value=""selected>สถานะ</option>
          <option value="approve">ตรสจสอบแล้ว</option>
        </select>
        <input type="date" class="date-input" value="2026-03-24">
      </div>
      <div class="filter-group filter-group justify-content-end">
        <button class="btn-search">ค้นหา</button>
        <button class="btn-reset">รีเซ็ต</button>
      </div>
    </div>

    <!-- Data Table -->
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>วันที่</th>
            <th>เลขที่เอกสาร</th>
            <th>สาขา</th>
            <th>ชื่อผู้ใช้งาน</th>
            <th>ประเภทรถ</th>
            <th>สถานะ</th>
            <th class="text-center">Export</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>24/03/2569</td>
            <td>TS01-001</td>
            <td>TEST</td>
            <td>TEST</td>
            <td>TEST</td>
            <td><span class="badge-status bg-success px-3 fs-md text-white">ตรวจสอบแล้ว</span></td>
            <td class="text-center">
              <button class="btn-action btn-export-row" data-doc="TS01-001">Export</button>
            </td>
          </tr>
          <tr>
            <td>23/03/2569</td>
            <td>TS01-002</td>
            <td>TEST</td>
            <td>TEST</td>
            <td>TEST</td>
            <td><span class="badge-status bg-success px-3 fs-md text-white">ตรวจสอบแล้ว</span></td>
            <td class="text-center">
              <button class="btn-action btn-export-row" data-doc="TS01-002">Export</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  
  <!-- Export Modal -->
  <div class="modal-overlay" id="export-modal">
    <div class="modal-content" style="max-width: 400px; text-align: center; position: relative;">
      <div class="modal-close" id="close-export-modal">&times;</div>
      <h3 class="export-modal-title">เลือกประเภทไฟล์</h3>
      <p class="export-modal-desc">ดาวน์โหลดเอกสาร: <span id="export-doc-number"></span></p>
      
      <div class="export-btn-container">
        <button class="btn-export-pdf">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#e74c3c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
          <span style="font-weight:600; color:#444;">PDF</span>
        </button>
        <button class="btn-export-excel">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 0 0 0 2 2h12a2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="17"></line><line x1="16" y1="13" x2="8" y2="17"></line></svg>
          <span style="font-weight:600; color:#444;">Excel</span>
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

    // แสดง Export Modal ทันทีที่กดปุ่ม Export ในตาราง
    const exportModal = document.getElementById('export-modal');
    const closeExportBtn = document.getElementById('close-export-modal');
    const exportDocNumber = document.getElementById('export-doc-number');

    document.querySelectorAll('.btn-export-row').forEach(btn => {
      btn.addEventListener('click', function() {
        const docNo = this.getAttribute('data-doc');
        exportDocNumber.textContent = docNo;
        exportModal.style.display = 'flex';
      });
    });

    // ปิด Modal เมื่อกดกากบาท
    closeExportBtn.addEventListener('click', () => {
      exportModal.style.display = 'none';
    });

    // ปิด Modal เมื่อคลิกพื้นที่ว่างข้างนอก
    exportModal.addEventListener('click', function(e) {
      if(e.target === exportModal) {
        exportModal.style.display = 'none';
      }
    });

    // แจ้งเตือนเมื่อกดเลือกประเภทไฟล์ (จำลองการทำงานจริง)
    document.querySelector('.btn-export-pdf').addEventListener('click', () => {
      alert("กำลังดาวน์โหลดไฟล์ PDF ของเอกสารเลขที่: " + exportDocNumber.textContent);
      exportModal.style.display = 'none';
    });
    document.querySelector('.btn-export-excel').addEventListener('click', () => {
      alert("กำลังดาวน์โหลดไฟล์ Excel ของเอกสารเลขที่: " + exportDocNumber.textContent);
      exportModal.style.display = 'none';
    });
  </script>

</body>
</html>
