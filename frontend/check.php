<?php
// 1. เชื่อมต่อฐานข้อมูล
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

try {
    $pdo = getServiceCenterPDO();
    $table = getServiceCenterTable();

    // 2. รับค่าจากฟอร์มค้นหา (Filter)
    $search = $_GET['search'] ?? '';
    $branch = $_GET['branch'] ?? '';
    $status = $_GET['status'] ?? '';
    $date_start = $_GET['date_start'] ?? '';
    $date_end = $_GET['date_end'] ?? '';

    // 3. สร้างเงื่อนไข SQL
    $whereConditions = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $searchIdMatch = false;
        $searchIdValue = '';
        if (preg_match('/^[Cc]0*(\d+)/', $search, $matches)) {
            $searchIdMatch = true;
            $searchIdValue = (int)$matches[1];
        }

        if ($searchIdMatch) {
            $whereConditions[] = "(owner_name LIKE ? OR vin LIKE ? OR id = ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = $searchIdValue;
        } else {
            $whereConditions[] = "(owner_name LIKE ? OR vin LIKE ? OR id LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
    }
    if (!empty($branch)) {
        $whereConditions[] = "branch = ?";
        $params[] = $branch;
    }
    if (!empty($status)) {
        $whereConditions[] = "status = ?";
        $params[] = $status;
    }
    if (!empty($date_start)) {
        $whereConditions[] = "claim_date >= ?";
        $params[] = $date_start;
    }
    if (!empty($date_end)) {
        $whereConditions[] = "claim_date <= ?";
        $params[] = $date_end;
    }

    // New: If not admin, restrict to their own branch
    $is_admin = isAdmin();
    $user_branch = $_SESSION['user_branch'] ?? '';
    
    if (!$is_admin) {
        if (!empty($user_branch)) {
            $whereConditions[] = "branch = ?";
            $params[] = $user_branch;
        } else {
            // Safety: If no branch set for user, show nothing or handle error
            $whereConditions[] = "1=0"; 
        }
    }

    $whereSql = implode(' AND ', $whereConditions);
    
    // ดึงข้อมูลเรียงจากใหม่ไปเก่า
    $stmt = $pdo->prepare("SELECT * FROM `$table` WHERE $whereSql ORDER BY id DESC");
    $stmt->execute($params);
    $claims = $stmt->fetchAll();

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
</head>
<body>

  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

  <div class="main-content">
    <!-- Hidden Iframe for Direct PDF Export -->
    <iframe name="export_iframe" id="export_iframe" style="display: none;"></iframe>

    <div class="container-fluid p-0">
    
      <div class="filter-card">
        <form method="GET" action="check.php">
            <div class="row w-100 g-3 align-items-center">
            <div class="col-12 col-lg-auto flex-grow-1">
                <div class="d-flex flex-wrap gap-2">
                <input type="text" name="search" placeholder="ค้นหาชื่อ, ทะเบียน, เลขเอกสาร..." class="form-control" style="width: 250px;" value="<?= htmlspecialchars($search) ?>">
                
                <?php if ($is_admin): ?>
                <select id="branchFilter" name="branch" class="form-select" style="width: auto; min-width: 140px;" data-current="<?= htmlspecialchars($branch) ?>">
                    <option value="">ทุกสาขา</option>
                </select>
                <?php else: ?>
                    <input type="hidden" name="branch" value="<?= htmlspecialchars($user_branch) ?>">
                    <div class="px-3 py-2 bg-light rounded-pill border fw-bold text-secondary" style="font-size: 0.9rem;">
                        📍 <?= htmlspecialchars($user_branch) ?>
                    </div>
                <?php endif; ?>
                
                <select name="status" class="form-select" style="width: auto; min-width: 140px;">
                    <option value="">ทุกสถานะ</option>
                    <option value="Pending Fix" <?= $status == 'Pending Fix' ? 'selected' : '' ?>>รอแก้ไข</option>
                    <option value="Completed" <?= $status == 'Completed' ? 'selected' : '' ?>>ดำเนินการเสร็จสิ้น</option>
                    <option value="Replaced" <?= $status == 'Replaced' ? 'selected' : '' ?>>เปลี่ยนคัน</option>
                    <option value="Approved Claim" <?= $status == 'Approved Claim' ? 'selected' : '' ?>>อนุมัติเคลม</option>
                    <option value="Approved Replacement" <?= $status == 'Approved Replacement' ? 'selected' : '' ?>>อนุมัติเปลี่ยนคัน</option>
                    <option value="Rejected" <?= $status == 'Rejected' ? 'selected' : '' ?>>ปฏิเสธ</option>
                </select>
                
                <div class="input-group" style="width: auto;">
                    <span class="input-group-text">ตั้งแต่</span>
                    <input type="date" name="date_start" class="form-control" value="<?= htmlspecialchars($date_start) ?>">
                    <span class="input-group-text">ถึง</span>
                    <input type="date" name="date_end" class="form-control" value="<?= htmlspecialchars($date_end) ?>">
                </div>
                </div>
            </div>
            <div class="col-12 col-lg-auto">
                <div class="d-flex gap-2 justify-content-lg-end">
                <button type="submit" class="btn-search">ค้นหา</button>
                <a href="check.php" class="btn-reset text-decoration-none text-center">รีเซ็ต</a>
                </div>
            </div>
            </div>
        </form>
      </div>

      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th class="export-col text-center" style="display: none; width: 50px;">
                  <input type="checkbox" id="selectAll" class="form-check-input shadow-none">
                </th>
                <th class="ps-4">เลขที่เอกสาร</th>
                <th>สาขา</th>
                <th>ข้อมูลรถ</th>
                <th>หมายเลขตัวถัง</th>
                <th>ประเภทการเคลม</th>
                <th>การดำเนินการ</th> <th class="text-center">สถานะ</th>
                <th class="text-center pe-4">จัดการ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($claims) > 0): ?>
                  <?php foreach ($claims as $row): 
                      // 1. จัดรูปแบบวันที่
                      $claimDateFormatted = $row['claim_date'] ? date('d/m/Y', strtotime($row['claim_date'])) : '-';
                      
                      // 2. จัดรูปแบบเลขเอกสาร C001-280369
                      $idPart = "C" . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                      $datePart = "000000";
                      if (!empty($row['claim_date']) && $row['claim_date'] !== '0000-00-00') {
                          $timestamp = strtotime($row['claim_date']);
                          if ($timestamp !== false) {
                              $buddhistYearShort = substr((date('Y', $timestamp) + 543), -2);
                              $datePart = date('dm', $timestamp) . $buddhistYearShort;
                          }
                      }
                      $docId = $idPart . "-" . $datePart;

                      // 3. กำหนด Class ของ Badge สถานะตาม CSS
                      $dbStatus = $row['status'] ?? 'Pending';
                      $badgeClass = 'status-badge '; 
                      $statusDisplay = 'รอดำเนินการ';

                      if ($dbStatus === 'Approved Claim' || $dbStatus === 'Approved') {
                          $badgeClass .= 'status-approve';
                          $statusDisplay = $dbStatus === 'Approved Claim' ? 'อนุมัติเคลม' : 'อนุมัติ';
                      } elseif ($dbStatus === 'Approved Replacement') {
                          $badgeClass .= 'status-approve';
                          $statusDisplay = 'อนุมัติเปลี่ยนคัน';
                      } elseif ($dbStatus === 'Pending Fix') {
                          $badgeClass .= 'status-pending'; 
                          $statusDisplay = 'รอแก้ไข';
                      } elseif ($dbStatus === 'Completed') {
                          $badgeClass .= 'bg-success text-white'; 
                          $statusDisplay = 'ดำเนินการเสร็จสิ้น';
                      } elseif ($dbStatus === 'Replaced') {
                          $badgeClass .= 'bg-info text-white'; 
                          $statusDisplay = 'เปลี่ยนคัน';
                      } elseif ($dbStatus === 'Rejected') {
                          $badgeClass .= 'status-reject';
                          $statusDisplay = 'ปฏิเสธ';
                      } else {
                          $badgeClass .= 'status-pending'; 
                          $statusDisplay = 'รอดำเนินการ';
                      }

                      // 4. เช็คข้อมูล การดำเนินการ (Action) จาก claim_type ใหม่
                      $actionDisplay = '-';
                      $cType = $row['claim_type'] ?? '';
                      if ($cType === 'RepairBranch') {
                          $actionDisplay = 'ซ่อมที่สาขา';
                      } elseif ($cType === 'SendHQ') {
                          $actionDisplay = 'ส่งซ่อมที่สนญ.';
                      } elseif ($cType === 'ReplaceVehicle') {
                          $actionDisplay = 'เปลี่ยนคัน';
                      } elseif ($cType === 'Other') {
                          $actionDisplay = 'อื่นๆ';
                      }

                      // 5. แปลงประเภทรถและยี่ห้อ (แบบดั้งเดิม V1)
                       $brandDisplay = htmlspecialchars($row['car_brand'] ?? '');
                       if (($row['car_type'] ?? '') === 'new') {
                           $carTypeDisplay = 'รถใหม่<br><span style="font-size:0.85rem; color:#666;">'.$brandDisplay.'</span>';
                       } else {
                           $gradeMap = [
                               'A_premium' => 'A พรีเมี่ยม',
                               'A_w6' => 'A (ประกัน 6 ด.)',
                               'C_w1' => 'C (ประกัน 1 ด.)',
                               'C_as_is' => 'C (ตามสภาพ)'
                           ];
                           $gradeText = $gradeMap[$row['used_grade']] ?? $row['used_grade'];
                           $carTypeDisplay = 'รถมือสอง<br><span style="font-size:0.85rem; color:#666;">'.$brandDisplay.' / เกรด: '.$gradeText.'</span>';
                       }

                      // 7. แปลงประเภทการเคลม (ถ้าเป็นภาษาอังกฤษให้แปลงเป็นไทย ถ้าเป็นไทยอยู่แล้วให้แสดงเลย)
                       $claimCatRaw = $row['claim_category'] ?? '';
                       $catMap = [
                           'pre-sale' => 'เคลมรถก่อนขาย',
                           'technical' => 'เคลมปัญหาทางเทคนิค',
                           'customer' => 'เคลมรถลูกค้า',
                           'customer-sale' => 'เคลมรถลูกค้า'
                       ];
                       $claimCatDisplay = $catMap[$claimCatRaw] ?? ($claimCatRaw ?: '-');
                  ?>
                  <tr>
                    <td class="export-col text-center" style="display: none;">
                      <input type="checkbox" class="row-checkbox form-check-input shadow-none text-nowrap" value="<?= $row['id'] ?>" data-doc="<?= $docId ?>">
                    </td>
                    <td class="ps-4">
                        <div class="doc-id-text text-nowrap"><?= $docId ?></div>
                        <div class="car-info-text"><small><?= $claimDateFormatted ?></small></div>
                        <?php if(!empty($row['sale_date']) && $row['sale_date'] !== '0000-00-00'): ?>
                        <div class="car-info-text text-danger fw-bold realtime-age mt-1" style="font-size: 0.8rem;" data-saledate="<?= $row['sale_date'] ?>"></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap"><?= htmlspecialchars($row['branch']) ?></td>
                    <td>
                        <div class="fw-medium text-dark text-nowrap"><?= $carTypeDisplay ?></div>
                    </td>
                    <td class="fw-medium text-nowrap"><?= htmlspecialchars($row['vin']) ?></td>
                    
                    <td><span class="text-dark text-nowrap"><?= $claimCatDisplay ?></span></td>
                    
                    <td><span class="text-secondary fw-medium text-nowrap"><?= $actionDisplay ?></span></td> 
                    <td class="text-center">
                      <span class="<?= $badgeClass ?>"><?= $statusDisplay ?></span>
                    </td>
                    <td class="text-center pe-4 text-nowrap">
                      <a href="verify.php?id=<?= $row['id'] ?>" class="btn-verify text-decoration-none">
                        <?= $is_admin ? 'ตรวจสอบ' : 'ดูรายละเอียด' ?>
                      </a>
                    </td>
                  </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="9" class="text-center py-5"> 
                          <div class="text-muted fs-5">ไม่มีข้อมูลเอกสารการเคลม</div>
                          <p class="text-muted mb-0">ลองเปลี่ยนเงื่อนไขการค้นหาดูอีกครั้ง</p>
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

  <iframe name="export_iframe" style="display:none;"></iframe>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Export System
      const btnExportToggle = document.getElementById('btnExportToggle');
      const btnCancelExport = document.getElementById('btnCancelExport');
      const exportBtnText = document.getElementById('exportBtnText');
      const exportIcon = document.getElementById('exportIcon');
      const exportCols = document.querySelectorAll('.export-col');
      const selectAll = document.getElementById('selectAll');
      const rowCheckboxes = document.querySelectorAll('.row-checkbox');
      const exportModal = document.getElementById('exportModal');

      let isExportMode = false;

      // Select All Functionality
      if (selectAll) {
        selectAll.addEventListener('change', function() {
          const allCheckboxes = document.querySelectorAll('.row-checkbox');
          allCheckboxes.forEach(cb => {
            cb.checked = selectAll.checked;
          });
        });
      }

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
            // ดึง Checkbox ที่ถูกติ๊กเลือก
            const selectedCheckboxes = Array.from(rowCheckboxes).filter(cb => cb.checked);
            
            if (selectedCheckboxes.length === 0) {
              alert('กรุณาเลือกเอกสารที่ต้องการส่งออกอย่างน้อย 1 รายการ!');
              return;
            }

            // แยกค่า ID ส่งให้ PHP และแยกชื่อเอกสารไว้แสดงในหน้าต่าง
            const selectedIds = selectedCheckboxes.map(cb => cb.value);
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
          form.action = '../backend/export_pdf.php'; 
          form.target = 'export_iframe'; // <--- ส่งไปที่ Hidden Iframe สั่งพิมพ์ในหน้าเดิม
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
      // Real-time Age Calculation
      function applyRealTimeAges() {
        const ageElements = document.querySelectorAll('.realtime-age');
        const now = new Date();
        
        ageElements.forEach(el => {
            const saleDateStr = el.getAttribute('data-saledate');
            if(!saleDateStr) return;
            
            const saleDate = new Date(saleDateStr);
            if(now < saleDate) {
                el.innerText = '(!)';
                return;
            }

            let years = now.getFullYear() - saleDate.getFullYear();
            let months = now.getMonth() - saleDate.getMonth();
            let days = now.getDate() - saleDate.getDate();
            let hours = now.getHours() - saleDate.getHours();
            let minutes = now.getMinutes() - saleDate.getMinutes();

            if (minutes < 0) { minutes += 60; hours--; }
            if (hours < 0) { hours += 24; days--; }
            if (days < 0) {
                const previousMonth = new Date(now.getFullYear(), now.getMonth(), 0);
                days += previousMonth.getDate();
                months--;
            }
            if (months < 0) { months += 12; years--; }

            let textParts = [];
            if (years > 0) textParts.push(`${years} ปี`);
            if (months > 0 || years > 0) textParts.push(`${months} เดือน`);
            textParts.push(`${days} วัน`);
            textParts.push(`${hours} ชม.`);
            textParts.push(`${minutes} นาที`);

            el.innerText = textParts.join(' ');
        });
      }
      
      applyRealTimeAges();
      setInterval(applyRealTimeAges, 60000); // 1 minute

      // Load Dynamic Branches for Admin Filter
      const branchFilter = document.getElementById('branchFilter');
      if (branchFilter) {
          fetch('../backend/api_branches.php')
              .then(res => res.json())
              .then(resp => {
                  if (resp.success) {
                      const currentVal = branchFilter.getAttribute('data-current');
                      resp.data.forEach(b => {
                          const opt = document.createElement('option');
                          opt.value = b.branch_name;
                          opt.textContent = b.branch_name;
                          if (b.branch_name === currentVal) opt.selected = true;
                          branchFilter.appendChild(opt);
                      });
                  }
              })
              .catch(err => console.error('Failed to load branches:', err));
      }
      
    });
  </script>
</body>
</html>