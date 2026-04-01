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

    // 3. สร้างเงื่อนไข SQL แบบไดนามิก
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

    $whereSql = implode(' AND ', $whereConditions);
    
    // ดึงข้อมูลเรียงจากใหม่ไปเก่า (DESC)
    $stmt = $pdo->prepare("SELECT * FROM claims WHERE $whereSql ORDER BY id DESC");
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
  <title>ประวัติเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles-history.css">
</head>
<body>

  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="container-fluid p-0">
      
      <div class="filter-card">
        <form method="GET" action="history.php">
            <div class="row w-100 g-3 align-items-center">
            <div class="col-12 col-lg-auto flex-grow-1">
                <div class="d-flex flex-wrap gap-2">
                <input type="text" name="search" placeholder="ค้นหาชื่อ, ทะเบียน, เลขเอกสาร..." class="form-control" style="width: 250px;" value="<?= htmlspecialchars($search) ?>">
                <select name="branch" class="form-select" style="width: auto; min-width: 140px;">
                  <option value="">ทุกสาขา</option>
                  <option value="สาขา สกลนคร" <?= $branch == 'สาขา สกลนคร' ? 'selected' : '' ?>>สกลนคร</option>
                </select>
                <select name="status" class="form-select" style="width: auto; min-width: 140px;">
                  <option value="">ทุกสถานะ</option>
                  <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>ส่งไปตรวจสอบ</option>
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
                <a href="history.php" class="btn-reset text-decoration-none text-center">รีเซ็ต</a>
                </div>
            </div>
            </div>
        </form>
      </div>

      <div class="row row-cols-1 row-cols-lg-2 g-4">
        
        <?php if (count($claims) > 0): ?>
            <?php foreach ($claims as $row): 
                // จัดรูปแบบวันที่สำหรับแสดงผล
                $claimDateFormatted = $row['claim_date'] ? date('d/m/Y', strtotime($row['claim_date'])) : '-';
                
                // จัดรูปแบบเลขเอกสาร C001-280369
                $idPart = "C" . str_pad($row['id'], 3, '0', STR_PAD_LEFT);
                if ($row['claim_date']) {
                    $timestamp = strtotime($row['claim_date']);
                    $buddhistYearShort = substr((date('Y', $timestamp) + 543), -2); // เอาปี ค.ศ. + 543 แล้วตัดมาแค่ 2 ตัวท้าย
                    $datePart = date('dm', $timestamp) . $buddhistYearShort; // รวม วัน(2) + เดือน(2) + ปี(2)
                } else {
                    $datePart = "000000"; // กรณีไม่ได้ระบุวันที่
                }
                $docId = $idPart . "-" . $datePart;
                
                // เช็คสถานะเพื่อปรับสี Badge และแปลเป็นภาษาไทย
                $dbStatus = $row['status'] ?? 'Pending';
                $badgeClass = 'hc-badge'; 
                $statusDisplay = 'รอดำเนินการ'; // ค่าเริ่มต้น

                if ($dbStatus === 'Approved' || $dbStatus === 'Approved Claim' || $dbStatus === 'Approved Replacement') {
                    $badgeClass .= ' bg-success text-white';
                    $statusDisplay = $dbStatus === 'Approved Replacement' ? 'อนุมัติเปลี่ยนคัน' : ($dbStatus === 'Approved Claim' ? 'อนุมัติเคลม' : 'อนุมัติ');
                } elseif ($dbStatus === 'Rejected') {
                    $badgeClass .= ' bg-danger text-white';
                    $statusDisplay = 'ปฏิเสธ';
                } elseif ($dbStatus === 'Pending Fix') {
                    $badgeClass .= ' bg-warning text-dark';
                    $statusDisplay = 'รอแก้ไข';
                } elseif ($dbStatus === 'Completed') {
                    $badgeClass .= ' bg-primary text-white'; 
                    $statusDisplay = 'ดำเนินการเสร็จสิ้น';
                } elseif ($dbStatus === 'Replaced') {
                    $badgeClass .= ' bg-info text-white'; 
                    $statusDisplay = 'เปลี่ยนคัน';
                } elseif ($dbStatus === 'Pending') {
                    $badgeClass .= ' bg-secondary text-white'; 
                    $statusDisplay = 'ส่งไปตรวจสอบ';
                }
                
            ?>
            <div class="col">
              <div class="history-card">
                <div class="hc-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                  <div class="col d-flex flex-column">
                    <div class="hc-date"><?= $claimDateFormatted ?></div>
                    <div class="hc-doc fw-bold">เลขที่เอกสาร : <?= $docId ?></div>
                    <?php if(!empty($row['sale_date']) && $row['sale_date'] !== '0000-00-00'): ?>
                    <div class="text-danger fw-bold realtime-age mt-1" style="font-size: 0.8rem;" data-saledate="<?= $row['sale_date'] ?>"></div>
                    <?php endif; ?>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <div class="<?= $badgeClass ?>">สถานะ : <?= htmlspecialchars($statusDisplay) ?></div>
                    <?php if (isAdmin()): ?>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="hc-btn">ดู/แก้ไข</a>
                    <?php else: ?>
                    <a href="verify.php?id=<?= $row['id'] ?>" class="hc-btn">ดูรายละเอียด</a>
                    <?php endif; ?>
                  </div>
                </div>
                
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label">สาขา :</div>
                      <input type="text" class="hc-input form-control" value="<?= htmlspecialchars($row['branch'] ?? '') ?>" readonly>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label">ประเภทการเคลม :</div>
                      <?php 
                        $cat = !empty($row['claim_category']) ? $row['claim_category'] : '- ไม่ระบุ -';
                        $catTH = ($cat == 'pre-sale') ? 'เคลมรถก่อนขาย' : (($cat == 'technical') ? 'เคลมปัญหาทางเทคนิค' : (($cat == 'customer-sale' || $cat == 'customer') ? 'เคลมรถลูกค้า' : $cat));
                      ?>
                      <input type="text" class="hc-input form-control" value="<?= htmlspecialchars($catTH ?? '') ?>" readonly>
                    </div>
                  </div>
                  
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label">ชื่อผู้ใช้งาน :</div>
                      <input type="text" class="hc-input form-control" value="<?= htmlspecialchars($row['owner_name'] ?? '') ?>" readonly>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label">หมายเลขตัวถัง :</div>
                      <input type="text" class="hc-input form-control" value="<?= htmlspecialchars($row['vin'] ?? '') ?>" readonly>
                    </div>
                  </div>
                  
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label">วันที่ส่งเคลม :</div>
                      <input type="text" class="hc-input form-control" value="<?= $claimDateFormatted ?>" readonly>
                    </div>
                  </div>

                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label">เบอร์โทรศัพท์ :</div>
                      <input type="text" class="hc-input form-control" value="<?= htmlspecialchars($row['owner_phone'] ?? '') ?>" readonly>
                    </div>
                  </div>
                </div>

                <div class="hc-textarea-group mt-3">
                  <label class="hc-textarea-label fw-bold mb-1 d-block">รายละเอียดปัญหาที่ลูกค้าแจ้ง :</label>
                  <textarea class="hc-textarea form-control" readonly><?= htmlspecialchars($row['problem_desc'] ?? '') ?></textarea>
                </div>

                <div class="row g-3 mt-1">
                  <div class="col-12 col-md-6">
                    <div class="hc-textarea-group">
                      <label class="hc-textarea-label fw-bold mb-1 d-block">วิธีการตรวจเช็ค :</label>
                      <textarea class="hc-textarea form-control" readonly><?= htmlspecialchars($row['inspect_method'] ?? '') ?></textarea>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="hc-textarea-group">
                      <label class="hc-textarea-label fw-bold mb-1 d-block">สาเหตุของปัญหา :</label>
                      <textarea class="hc-textarea form-control" readonly><?= htmlspecialchars($row['inspect_cause'] ?? '') ?></textarea>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-6 offset-3 text-center py-5">
                <h5 class="text-muted">ไม่มีข้อมูลการส่งเคลมในระบบ</h5>
            </div>
        <?php endif; ?>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function applyRealTimeAges() {
        const ageElements = document.querySelectorAll('.realtime-age');
        
        ageElements.forEach(el => {
            const saleDateStr = el.getAttribute('data-saledate');
            if (!saleDateStr) return;
            
            const saleDate = new Date(saleDateStr);
            const now = new Date();
            
            if (now < saleDate) {
                el.textContent = 'วันที่ขายเกินวันปัจจุบัน';
                return;
            }
            
            let years = now.getFullYear() - saleDate.getFullYear();
            let months = now.getMonth() - saleDate.getMonth();
            let days = now.getDate() - saleDate.getDate();
            let hours = now.getHours() - saleDate.getHours();
            let minutes = now.getMinutes() - saleDate.getMinutes();
            
            if (minutes < 0) {
                minutes += 60;
                hours--;
            }
            if (hours < 0) {
                hours += 24;
                days--;
            }
            if (days < 0) {
                const previousMonth = new Date(now.getFullYear(), now.getMonth(), 0);
                days += previousMonth.getDate();
                months--;
            }
            if (months < 0) {
                months += 12;
                years--;
            }
            
            el.textContent = `อายุการใช้งาน: ${years} ปี ${months} เดือน ${days} วัน ${hours} ชม. ${minutes} น.`;
        });
    }

    // ทำงานทันทีที่โหลดหน้าจอ 
    applyRealTimeAges();
    
    // อัปเดตข้อมูลอัตโนมัติทุกๆ 1 นาที (60000 ms)
    setInterval(applyRealTimeAges, 60000);
  </script>
</body>
</html>