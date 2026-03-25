<?php
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
  <div class="logo-container">
    <img src="https://i.ibb.co/svxDp4Y7/image.png" alt="อึ้งกุ่ยเฮง Logo">
  </div>
  <div class="nav-menu">
    <a href="claim_form.php" class="nav-btn <?php echo ($current_page == 'claim_form.php') ? 'active' : ''; ?>">ฟอร์มส่งเคลม</a>
    <a href="history.php" class="nav-btn <?php echo ($current_page == 'history.php') ? 'active' : ''; ?>">ประวัติเคลม</a>
    <a href="check.php" class="nav-btn <?php echo ($current_page == 'check.php') ? 'active' : ''; ?>">ตรวจเช็ค</a>
    <a href="report.php" class="nav-btn <?php echo ($current_page == 'report.php') ? 'active' : ''; ?>">รายงาน</a>
  </div>
</div>
