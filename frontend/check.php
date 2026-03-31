<?php
// 1. เชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

try {
    $pdo = getServiceCenterPDO();

    // 2. รับค่าจากฟอร์มค้นหา (Filter)
    $search = $_GET['search'] ?? '';
    $branch = $_GET['branch'] ?? '';
    $status = $_GET['status'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';

    // 3. สร้างเงื่อนไข SQL (V3 Snake Case)
    $whereConditions = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $whereConditions[] = "(owner_name LIKE ? OR vin LIKE ? OR id LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    if (!empty($branch)) {
        $whereConditions[] = "branch = ?";
        $params[] = $branch;
    }
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    if (!empty($date_from)) {
        $whereConditions[] = "claim_date >= ?";
        $params[] = $date_from;
    }
    if (!empty($date_to)) {
        $whereConditions[] = "claim_date <= ?";
        $params[] = $date_to;
    }

    $whereSql = implode(' AND ', $whereConditions);
    
    // ดึงข้อมูลเรียงจากใหม่ไปเก่า
    $stmt = $pdo->prepare("SELECT * FROM `claims` WHERE $whereSql ORDER BY id DESC");
    $stmt->execute($params);
    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ดึงรายชื่อสาขาที่มีอยู่จริงในฐานข้อมูลมาทำ Dropdown
    $branchesStmt = $pdo->query("SELECT DISTINCT branch FROM claims WHERE branch IS NOT NULL AND branch != ''");
    $allBranches = $branchesStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ตรวจเช็คและรายงาน - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles-check.css">
  <style>
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    .filter-label { font-size: 0.85rem; font-weight: 600; color: #666; margin-bottom: 5px; }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="container-fluid">
      
      <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold m-0" style="color: #2c3e50;">🔎 ตรวจเช็คและรายงาน (V3)</h2>
      </div>

      <div class="filter-card">
        <form method="GET" action="check.php">
            <div class="row g-3">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="filter-label">ค้นหาข้อมูล</div>
                    <input type="text" name="search" placeholder="ชื่อ, ตัวถัง, เลขเอกสาร..." class="form-control" value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <div class="filter-label">สาขา</div>
                    <select name="branch" class="form-select">
                        <option value="">ทุกสาขา</option>
                        <?php foreach($allBranches as $b): ?>
                            <option value="<?= htmlspecialchars($b) ?>" <?= $branch == $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <div class="filter-label">สถานะ</div>
                    <select name="status" class="form-select">
                        <option value="">ทุกสถานะ</option>
                        <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>รอดำเนินการ</option>
                        <option value="Approved" <?= $status == 'Approved' ? 'selected' : '' ?>>อนุมัติ</option>
                        <option value="Rejected" <?= $status == 'Rejected' ? 'selected' : '' ?>>ปฏิเสธ</option>
                    </select>
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="filter-label">จากวันที่</div>
                    <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($date_from) ?>">
                </div>
                <div class="col-12 col-md-6 col-lg-2">
                    <div class="filter-label">ถึงวันที่</div>
                    <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($date_to) ?>">
                </div>
                <div class="col-12 col-lg-1 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100 py-2" style="background: var(--primary-orange); border: none; border-radius: 10px;">ค้นหา</button>
                    <a href="check.php" class="btn btn-light py-2" style="border-radius: 10px;">&times;</a>
                </div>
            </div>
        </form>
      </div>

      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-hover align-middle bg-white" style="border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
            <thead style="background: #f8f9fa;">
              <tr>
                <th class="export-col text-center" style="display: none; width: 50px;">
                  <input type="checkbox" id="selectAll" class="form-check-input shadow-none">
                </th>
                <th class="ps-4">เลขที่เอกสาร</th>
                <th>สาขา</th>
                <th>ข้อมูลรถ</th>
                <th>หมายเลขตัวถัง</th>
                <th>ประเภทการเคลม</th>
                <th>การดำเนินการ</th> 
                <th class="text-center">สถานะ</th>
                <th class="text-center pe-4">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($claims) > 0): ?>
                  <?php foreach ($claims as $row): 
                      $claim_id = $row['id'];
                      $claimDateFormatted = !empty($row['claim_date']) ? date('d/m/Y', strtotime($row['claim_date'])) : '-';
                      
                      // เลขเอกสาร C001-280369
                      $idPart = "C" . str_pad($claim_id, 3, '0', STR_PAD_LEFT);
                      $datePart = "000000";
                      if (!empty($row['claim_date']) && $row['claim_date'] !== '0000-00-00') {
                          $timestamp = strtotime($row['claim_date']);
                          if ($timestamp !== false) {
                              $datePart = date('dm', $timestamp) . substr((date('Y', $timestamp) + 543), -2);
                          }
                      }
                      $docId = $idPart . "-" . $datePart;

                      $dbStatus = $row['status'] ?? 'Pending';
                      $badgeClass = 'status-badge '; 
                      $statusDisplay = 'รอดำเนินการ';

                      if ($dbStatus === 'Approved') {
                          $badgeClass .= 'status-approve';
                          $statusDisplay = 'อนุมัติ';
                      } elseif ($dbStatus === 'Rejected') {
                          $badgeClass .= 'status-reject';
                          $statusDisplay = 'ปฏิเสธ';
                      } else {
                          $badgeClass .= 'status-pending'; 
                          $statusDisplay = 'รอดำเนินการ';
                      }

                      // Action Display
                      $actionDisplay = '-';
                      $cType = $row['claim_type'] ?? '';
                      if ($cType === 'RepairBranch') $actionDisplay = 'ซ่อมที่สาขา';
                      elseif ($cType === 'SendHQ') $actionDisplay = 'ส่งซ่อมที่สนญ.';
                      elseif ($cType === 'ReplaceVehicle') $actionDisplay = 'เปลี่ยนคัน';
                      elseif ($cType === 'Other') $actionDisplay = 'อื่นๆ';

                      // Car Type
                      $carTypeDisplay = ($row['car_type'] === 'new') ? 'รถใหม่' : (($row['car_type'] === 'used') ? 'รถมือสอง' : htmlspecialchars($row['car_type'] ?? '-'));
                      
                      $brandDisplay = htmlspecialchars($row['car_brand'] ?? '-');
                      if ($row['car_type'] === 'used' && !empty($row['used_grade'])) {
                          $brandDisplay .= ' / ' . htmlspecialchars($row['used_grade']);
                      }

                      // Claim Category
                      $claimCatDisplay = htmlspecialchars($row['claim_category'] ?? '-');
                      if ($claimCatDisplay === 'pre-sale') $claimCatDisplay = 'เคมรถก่อนขาย';
                      elseif ($claimCatDisplay === 'technical') $claimCatDisplay = 'เคมปัญหาทางเทคนิค';
                      elseif ($claimCatDisplay === 'customer-sale' || $claimCatDisplay === 'customer') $claimCatDisplay = 'เคลมรถลูกค้า';
                  ?>
                  <tr>
                    <td class="export-col text-center" style="display: none;">
                      <input type="checkbox" class="row-checkbox form-check-input shadow-none" value="<?= $claim_id ?>" data-doc="<?= $docId ?>">
                    </td>
                    <td class="ps-4">
                        <div class="doc-id-text fw-bold" style="color: var(--primary-orange);"><?= $docId ?></div>
                        <div class="car-info-text text-muted"><small><?= $claimDateFormatted ?></small></div>
                    </td>
                    <td><?= htmlspecialchars($row['branch'] ?? '-') ?></td>
                    <td>
                        <div class="fw-medium text-dark"><?= $carTypeDisplay ?></div>
                        <div class="car-info-text text-muted small"><?= $brandDisplay ?></div>
                    </td>
                    <td class="fw-medium"><?= htmlspecialchars($row['vin'] ?? '-') ?></td>
                    <td><span class="badge bg-light text-dark fw-normal px-3 py-2" style="border-radius: 8px; border: 1px solid #eee;"><?= $claimCatDisplay ?></span></td>
                    <td><span class="text-secondary fw-medium"><?= $actionDisplay ?></span></td> 
                    <td class="text-center">
                      <span class="<?= $badgeClass ?>"><?= $statusDisplay ?></span>
                    </td>
                    <td class="text-center pe-4">
                      <a href="verify.php?id=<?= $claim_id ?>" class="btn btn-sm btn-outline-primary px-3 py-1" style="border-radius: 8px; border-color: #ddd; color: #555;">ตรวจสอบ</a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="9" class="text-center py-5"> 
                          <div class="text-muted fs-5">ไม่พบข้อมูลการเคลมตรงตามเงื่อนไข</div>
                          <p class="text-muted mb-0">ลองรีเซ็ตหรือเปลี่ยนช่วงวันที่ดูครับ</p>
                      </td>
                  </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      
      <div style="height: 100px;"></div>

    </div>
  </div>

  <div class="floating-export">
    <button id="btnCancelExport" class="btn-floating-cancel" style="display: none;">ยกเลิก</button>
    <button id="btnExportToggle" class="btn-floating-export d-flex align-items-center gap-2">
      <svg id="exportIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
        <polyline points="7 10 12 15 17 10"></polyline>
        <line x1="12" y1="15" x2="12" y2="3"></line>
      </svg>
      <span id="exportBtnText">ส่งออกรายงาน</span>
    </button>
  </div>

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
  
  <form id="realExportForm" method="POST" style="display: none;">
    <input type="hidden" name="export_ids" id="export_ids">
  </form>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Select All Toggle
      const selectAll = document.getElementById('selectAll');
      const rowCheckboxes = document.querySelectorAll('.row-checkbox');
      if (selectAll) {
        selectAll.addEventListener('change', function() {
          rowCheckboxes.forEach(cb => cb.checked = selectAll.checked);
        });
      }

      // Export System
      const btnExportToggle = document.getElementById('btnExportToggle');
      const btnCancelExport = document.getElementById('btnCancelExport');
      const exportBtnText = document.getElementById('exportBtnText');
      const exportIcon = document.getElementById('exportIcon');
      const exportCols = document.querySelectorAll('.export-col');
      const exportModal = document.getElementById('exportModal');
      const closeExportModal = document.getElementById('closeExportModal');

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
            const selectedCheckboxes = Array.from(rowCheckboxes).filter(cb => cb.checked);
            if (selectedCheckboxes.length === 0) {
              alert('กรุณาเลือกเอกสารที่ต้องการส่งออกอย่างน้อย 1 รายการ!');
              return;
            }
            const selectedIds = selectedCheckboxes.map(cb => cb.value);
            const selectedDocs = selectedCheckboxes.map(cb => cb.getAttribute('data-doc'));
            document.getElementById('export_ids').value = selectedIds.join(',');

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
          exportBtnText.textContent = 'ส่งออกรายงาน';
          btnExportToggle.style.background = 'var(--primary-orange)';
          exportIcon.style.display = 'block';
          rowCheckboxes.forEach(cb => cb.checked = false);
          if (selectAll) selectAll.checked = false;
        });
      }

      if (closeExportModal) {
        closeExportModal.addEventListener('click', () => exportModal.classList.remove('show'));
      }

      // Export Actions
      const exportPDFBtn = document.getElementById('exportPDFBtn');
      if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', function() {
          const form = document.getElementById('realExportForm');
          form.action = '../backend/export_pdf.php'; 
          form.target = '_blank';
          form.submit();                  
          exportModal.classList.remove('show');
          btnCancelExport.click();        
        });
      }

      const exportExcelBtn = document.getElementById('exportExcelBtn');
      if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function() {
          const form = document.getElementById('realExportForm');
          form.action = '../backend/export_excel.php'; 
          form.target = '_self'; 
          form.submit();                    
          exportModal.classList.remove('show');
          btnCancelExport.click();          
        });
      }
    });
  </script>
</body>
</html>
electedIds = selectedCheckboxes.map(cb => cb.value);
            const selectedDocs = selectedCheckboxes.map(cb => cb.getAttribute('data-doc'));

            // นำ ID ไปใส่ในฟอร์มซ่อนเพื่อเตรียมส่งค่า
            document.getElementById('export_ids').value = selectedIds.join(',');

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

      // เมื่อกดปุ่ม PDF
      const exportPDFBtn = document.getElementById('exportPDFBtn');
      if (exportPDFBtn) {
        exportPDFBtn.addEventListener('click', function() {
          const form = document.getElementById('realExportForm');
          form.action = '/../backend/export_pdf.php'; 
          form.target = '_blank'; // <--- เปลี่ยนกลับเป็น _blank เพื่อเปิดแท็บใหม่ที่มีหน้าโหลด
          form.submit();                  
          
          exportModal.classList.remove('show');
          btnCancelExport.click();        
        });
      }

      // เมื่อกดปุ่ม Excel
      const exportExcelBtn = document.getElementById('exportExcelBtn');
      if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function() {
          const form = document.getElementById('realExportForm');
          form.action = '/../backend/export_excel.php'; 
          form.target = '_self'; // <--- เพิ่มบรรทัดนี้: ดาวน์โหลดในหน้าเดิม จะได้ไม่เด้งหน้าขาว
          form.submit();                    
          exportModal.classList.remove('show');
          btnCancelExport.click();          
        });
      }
    });
  </script>
</body>
</html>