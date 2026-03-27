<?php
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<button id="sidebarToggle" class="hamburger-btn" aria-label="Toggle menu">☰</button>

<div id="sidebarOverlay" class="sidebar-overlay"></div>

<div class="sidebar" id="sidebar">
  <div class="logo-container">
    <img src="https://i.ibb.co/svxDp4Y7/image.png" alt="อึ้งกุ่ยเฮง Logo">
  </div>
  <div class="nav-menu">
    <a href="claim_form.php" class="nav-btn <?= ($current_page == 'claim_form.php') ? 'active' : '';?>">ฟอร์มส่งเคลม</a>
    <a href="history.php" class="nav-btn <?= ($current_page == 'history.php') ? 'active' : '';?>">ประวัติเคลม</a>
    <a href="check.php" class="nav-btn <?= ($current_page == 'check.php') ? 'active' : '';?>">ตรวจเช็ค</a>
  </div>
</div>

<script>
  const sidebar = document.getElementById('sidebar');
  const toggle = document.getElementById('sidebarToggle');
  const overlay = document.getElementById('sidebarOverlay');

  function openSidebar() {
    sidebar.classList.add('open');
    overlay.classList.add('visible');
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    overlay.classList.remove('visible');
  }

  toggle.addEventListener('click', () => {
    sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
  });

  overlay.addEventListener('click', closeSidebar);

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1024) closeSidebar();
  });
</script>
