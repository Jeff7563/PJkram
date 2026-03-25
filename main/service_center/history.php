<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ประวัติเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link rel="stylesheet" href="css/theme.css">
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
          <input type="text" placeholder="ค้นหาเอกสาร..." style="min-width: 250px;">
        </div>
        <select>
          <option value="">สาขา</option>
          <option value="bangkok">กรุงเทพฯ</option>
          <option value="chiangmai">เชียงใหม่</option>
          <option value="khonkaen">ขอนแก่น</option>
        </select>
        <select>
          <option value=""selected>สถานะ</option>
          <option value="***">***</option>
        </select>
        <input type="date" class="date-input" value="2026-03-24">
      </div>
      <div class="filter-group" style="justify-content: flex-end;">
        <button class="btn-search">ค้นหา</button>
        <button class="btn-reset">รีเซ็ต</button>
      </div>
    </div>

    <!-- Data Card Template -->
    <div class="data-card">
      <div class="card-header">
        <div class="card-header-left">
          <div class="date-box">24/03/2569</div>
          <div class="doc-box">เลขที่เอกสาร : TS01-001</div>
        </div>
        <div class="card-header-right">
          <div class="badge-status">สถานะ : <span>****</span></div>
          <a href="edit_claim.php" class="btn-action">แก้ไข</a>
        </div>
      </div>

      <div class="card-body-grid">
        <div class="info-row">
          <div class="info-label">สาขา :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">ประเภทการเคลม :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">ชื่อผู้ใช้งาน :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">ประเภทรถ :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">หมายเลขตัวถัง :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">เบอร์โทรศัพท์ :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">วันที่ส่งเคลม :</div>
          <div class="info-value"></div>
        </div>
        <div class="info-row">
          <div class="info-label">วันที่ส่งคืนอะไหล่ :</div>
          <div class="info-value"></div>
        </div>
      </div>

      <div class="problem-section">
        <h4>รายละเอียดปัญหาที่ลูกค้าแจ้ง :</h4>
        <div class="problem-box">
        </div>
      </div>

      <div class="fix-grid">
        <div class="fix-box">
          <h4>วิธีการแก้ไข :</h4>
          <div class="fix-value"></div>
        </div>
        <div class="fix-box">
          <h4>สาเหตุของปัญหา :</h4>
          <div class="fix-value"></div>
        </div>
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
  </script>

</body>
</html>
