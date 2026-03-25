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
          <option value="approve">อนุมัติการเคลม</option>
          <option value="not_approve">ไม่อนุมัติ</option>
          <option value="return">ตีกลับไปแก้ไข</option>
        </select>
        <input type="date" class="date-input" value="2026-03-24">
      </div>
      <div class="filter-group" style="justify-content: flex-end;">
        <button class="btn-search">ค้นหา</button>
        <button class="btn-reset">รีเซ็ต</button>
      </div>
    </div>

    <!-- Data Table Container for Check Page -->
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
            <th style="text-align: center;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>24/03/2569</td>
            <td>TS01-001</td>
            <td>TEST</td>
            <td>TEST</td>
            <td>TEST</td>
            <td><span class="badge-status" style="padding: 6px 14px; font-size: 0.9rem; background-color: #27ae60;">อนุมัติการเคลม</span></td>
            <td style="text-align: center;">
              <a href="verify_claim.php" class="btn-action" style="padding: 6px 15px; font-size: 0.9rem;">ตรวจสอบ</a>
            </td>
          </tr>
          <tr>
            <td>24/03/2569</td>
            <td>TS01-002</td>
            <td>TEST</td>
            <td>TEST</td>
            <td>TEST</td>
            <td><span class="badge-status" style="padding: 6px 14px; font-size: 0.9rem; background-color: #e74c3c;">ไม่อนุมัติ</span></td>
            <td style="text-align: center;">
              <a href="verify_claim.php" class="btn-action" style="padding: 6px 15px; font-size: 0.9rem;">ตรวจสอบ</a>
            </td>
          </tr>
          <tr>
            <td>24/03/2569</td>
            <td>TS01-003</td>
            <td>TEST</td>
            <td>TEST</td>
            <td>TEST</td>
            <td><span class="badge-status" style="padding: 6px 14px; font-size: 0.9rem; background-color: #f39c12;">ตีกลับไปแก้ไข</span></td>
            <td style="text-align: center;">
              <a href="verify_claim.php" class="btn-action" style="padding: 6px 15px; font-size: 0.9rem;">ตรวจสอบ</a>
            </td>
          </tr>
        </tbody>
      </table>
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
