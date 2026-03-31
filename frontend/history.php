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
        $whereConditions[] = "(owner_name LIKE ? OR vin LIKE ? OR id LIKE ? OR problem_desc LIKE ?)";
        $params[] = "%$search%";
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
  <title>ประวัติเคลม - ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/theme.css">
  <link rel="stylesheet" href="../shared/assets/css/styles-history.css">
  <style>
    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 25px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    .filter-label { font-size: 0.85rem; font-weight: 600; color: #666; margin-bottom: 5px; }
    .history-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .hc-header { border-bottom: 1px solid #f0f0f0; padding-bottom: 15px; }
  </style>
</head>
<body>

  <?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

  <div class="main-content">
    <div class="container-fluid">
      
      <div class="d-flex justify-content-between align-items-center mb-4">
          <h2 class="fw-bold m-0" style="color: #2c3e50;">📜 ประวัติการส่งเคลม (V3)</h2>
      </div>

      <div class="filter-card">
        <form method="GET" action="history.php">
            <div class="row g-3">
                <div class="col-12 col-md-4 col-lg-3">
                    <div class="filter-label">ค้นหาข้อมูล</div>
                    <input type="text" name="search" placeholder="ชื่อ, ตัวถัง, ปัญหา..." class="form-control" value="<?= htmlspecialchars($search) ?>">
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
                    <a href="history.php" class="btn btn-light py-2" style="border-radius: 10px;">&times;</a>
                </div>
            </div>
        </form>
      </div>

      <div class="row row-cols-1 row-cols-lg-2 g-4">
        
        <?php if (count($claims) > 0): ?>
            <?php foreach ($claims as $row): 
                $claim_id = $row['id'];
                $claimDateFormatted = !empty($row['claim_date']) ? date('d/m/Y', strtotime($row['claim_date'])) : '-';
                
                // เลขที่เอกสาร C001-280369
                $idPart = "C" . str_pad($claim_id, 3, '0', STR_PAD_LEFT);
                $datePart = "000000";
                if (!empty($row['claim_date']) && $row['claim_date'] !== '0000-00-00') {
                    $timestamp = strtotime($row['claim_date']);
                    $datePart = date('dm', $timestamp) . substr((date('Y', $timestamp) + 543), -2);
                }
                $docId = $idPart . "-" . $datePart;
                
                $dbStatus = $row['status'] ?? 'Pending';
                $badgeClass = 'badge px-3 py-2 '; 
                $statusDisplay = 'รอดำเนินการ';

                if ($dbStatus === 'Approved') {
                    $badgeClass .= 'bg-success';
                    $statusDisplay = 'อนุมัติ';
                } elseif ($dbStatus === 'Rejected') {
                    $badgeClass .= 'bg-danger';
                    $statusDisplay = 'ปฏิเสธ';
                } else {
                    $badgeClass .= 'bg-warning text-dark';
                    $statusDisplay = 'รอดำเนินการ';
                }
            ?>
            <div class="col">
              <div class="history-card bg-white p-4">
                <div class="hc-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                  <div class="d-flex flex-column">
                    <div class="hc-date text-muted small"><?= $claimDateFormatted ?></div>
                    <div class="hc-doc fw-bold" style="color: var(--primary-orange); font-size: 1.1rem;">เลขที่ : <?= $docId ?></div>
                  </div>
                  <div class="d-flex gap-2 align-items-center">
                    <span class="<?= $badgeClass ?>" style="border-radius: 10px;"><?= htmlspecialchars($statusDisplay) ?></span>
                    <a href="edit.php?id=<?= $claim_id ?>" class="btn btn-sm btn-primary px-3" style="border-radius: 8px; background: var(--primary-orange); border: none;">ดู/แก้ไข</a>
                  </div>
                </div>
                
                <div class="row g-3">
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label text-muted small fw-bold">สาขา :</div>
                      <div class="fw-medium"><?= htmlspecialchars($row['branch'] ?? '-') ?></div>
                    </div>
                  </div>
                  <div class="col-12 col-md-6">
                    <div class="hc-field-group">
                      <div class="hc-label text-muted small fw-bold">ประเภทการเคลม :</div>
                      <?php 
                        $cat = $row['claim_category'] ?? '';
                        $catTH = ($cat == 'pre-sale') ? 'เคลมรถก่อนขาย' : (($cat == 'technical') ? 'เคลมปัญหาทางเทคนิค' : (($cat == 'customer-sale' || $cat == 'customer') ? 'เคลมรถลูกค้า' : $cat));
                      ?>
                  <div class="col-12 col-md-6">
                    <div class="hc-textarea-group">
                      <label class="hc-textarea-label fw-bold mb-1 d-block">สาเหตุของปัญหา :</label>
                      <textarea class="hc-textarea form-control" readonly><?= htmlspecialchars($row['inspect_cause']) ?></textarea>
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
</body>
</html>