<?php
/**
 * toast.php - ระบบแจ้งเตือน (Toast)
 * ใช้เรียกผ่าน window.showToast(message, type)
 */
?>
<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 10000;">
  <div id="liveToast" class="toast align-items-center text-white border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 12px; min-width: 250px;">
    <div class="d-flex p-2">
      <div class="toast-body fs-6 fw-medium" id="toastMessage">
        ข้อความแจ้งเตือน
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<script>
  /**
   * แสดงข้อความแจ้งเตือน (Toast)
   * @param {string} message 
   * @param {string} type 'success', 'error', 'warning', 'info'
   */
  window.showToast = function(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastMsg = document.getElementById('toastMessage');
    if (!toastEl || !toastMsg) return;

    toastMsg.textContent = message;
    toastEl.classList.remove('bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'text-dark');
    
    if (type === 'success') {
        toastEl.classList.add('bg-success');
    } else if (type === 'error' || type === 'danger') {
        toastEl.classList.add('bg-danger');
    } else if (type === 'warning') {
        toastEl.classList.add('bg-warning', 'text-dark');
    } else {
        toastEl.classList.add('bg-primary');
    }

    if (typeof bootstrap !== 'undefined') {
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    } else {
        console.warn('Bootstrap is not loaded. Toast cannot be displayed.');
    }
  };
</script>
