<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

try {
    $pdo = getServiceCenterPDO();
    $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    $user_branch = $_SESSION['branch'] ?? '';

    // 1. นำข้อมูลภาพรวมออกมา (Summary Cards)
    $whereClause = $is_admin ? "" : " WHERE branch = " . $pdo->quote($user_branch);
    
    // เคสทั้งหมด
    $stmtCount = $pdo->query("SELECT COUNT(*) as total FROM claims" . $whereClause);
    $totalClaims = $stmtCount->fetch()['total'];

    // แยกตามสถานะ (ละเอียด)
    $stmtStatus = $pdo->query("SELECT status, COUNT(*) as count FROM claims" . $whereClause . " GROUP BY status");
    $statusCounts = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // สรุปยอดสำหรับการ์ด
    $pending = ($statusCounts['Pending'] ?? 0) + ($statusCounts['Pending Fix'] ?? 0);
    $approved = ($statusCounts['Approved Claim'] ?? 0) + ($statusCounts['Approved Replacement'] ?? 0);
    $rejected = $statusCounts['Rejected'] ?? 0;

    // 2. ข้อมูลรายเดือน (Monthly Trend)
    $stmtMonthly = $pdo->query("
        SELECT DATE_FORMAT(claim_date, '%Y-%m') as month, COUNT(*) as total 
        FROM claims 
        " . $whereClause . ($is_admin ? " WHERE " : " AND ") . " claim_date IS NOT NULL 
        GROUP BY month 
        ORDER BY month ASC 
        LIMIT 12
    ");
    $monthlyData = $stmtMonthly->fetchAll(PDO::FETCH_ASSOC);
    $months = array_column($monthlyData, 'month');
    $monthlyCounts = array_column($monthlyData, 'total');

    // 3. ข้อมูลแยกตามประเภท (Category Breakdown)
    $stmtCat = $pdo->query("SELECT claim_category, COUNT(*) as total FROM claims" . $whereClause . " GROUP BY claim_category");
    $catData = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. รายการล่าสุด 5 เคส
    $recentStmt = $pdo->query("SELECT id, owner_name, vin, status, claim_date FROM claims" . $whereClause . " ORDER BY id DESC LIMIT 5");
    $recentItems = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error loading dashboard data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ระบบจัดการเคลม</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../shared/assets/css/theme.css">
    <link rel="stylesheet" href="../shared/assets/css/dashboard_v3.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body class="bg-light">

<?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-5 mt-2">
            <div>
                <h2 class="fw-bold m-0" style="color: #1e293b; letter-spacing: -0.5px;">Dashboard</h2>
                <p class="text-muted m-0 mt-1">ภาพรวมระบบจัดการเคลม</p>
            </div>
            <div class="bg-white px-4 py-2 rounded-pill shadow-sm border">
                <span class="text-muted fw-500 fs-sm"><i class="far fa-calendar-alt me-2"></i><?= date('d M Y H:i') ?></span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-blue text-white">
                    <div class="stat-icon-box"><i class="fas fa-file-invoice"></i></div>
                    <div class="stat-label">การเคลมทั้งหมด</div>
                    <div class="stat-number"><?= number_format($totalClaims) ?></div>
                    <div class="fs-xs opacity-75">เคสทั้งหมดในระบบ</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-orange text-white">
                    <div class="stat-icon-box"><i class="fas fa-clock"></i></div>
                    <div class="stat-label">รอการอนุมัติ</div>
                    <div class="stat-number"><?= number_format($pending) ?></div>
                    <div class="fs-xs opacity-75">รอตรวจสอบและแก้ไข</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-green text-white">
                    <div class="stat-icon-box"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-label">อนุมัติแล้ว</div>
                    <div class="stat-number"><?= number_format($approved) ?></div>
                    <div class="fs-xs opacity-75">อนุมัติแล้วทั้งหมด</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-red text-white">
                    <div class="stat-icon-box"><i class="fas fa-times-circle"></i></div>
                    <div class="stat-label">ไม่อนุมัติ</div>
                    <div class="stat-number"><?= number_format($rejected) ?></div>
                    <div class="fs-xs opacity-75">ไม่อนุมัติ / ยกเลิก</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Charts Section -->
            <div class="col-12 col-lg-8">
                <div class="chart-container">
                    <div class="section-title"><i class="fas fa-chart-line text-primary"></i> Monthly Performance</div>
                    <div style="height: 350px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-container">
                    <div class="section-title"><i class="fas fa-chart-pie text-orange"></i> Category Breakdown</div>
                    <div style="height: 350px;">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-12">
                <div class="chart-container p-0 overflow-hidden">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="m-0 fw-bold d-flex align-items-center gap-2"><i class="fas fa-history text-secondary"></i> รายการเคลมล่าสุด</h5>
                        <a href="history.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3">ดูประวัติทั้งหมด</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>ผู้ใช้งาน / VIN</th>
                                    <th>วันที่ส่งเคลม</th>
                                    <th>สถานะ</th>
                                    <th class="text-end">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($recentItems) > 0): ?>
                                    <?php foreach($recentItems as $item): 
                                        $stClass = str_contains($item['status'], 'Approved') ? 'status-approved' : (str_contains($item['status'], 'Pending') ? 'status-pending' : 'status-rejected');
                                    ?>
                                    <tr>
                                        <td><span class="fw-bold">C<?= str_pad($item['id'], 3, '0', STR_PAD_LEFT) ?></span></td>
                                        <td>
                                            <div class="fw-600"><?= htmlspecialchars($item['owner_name']) ?></div>
                                            <div class="text-muted fs-xs"><?= htmlspecialchars($item['vin']) ?></div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($item['claim_date'])) ?></td>
                                        <td><span class="badge-status <?= $stClass ?>"><?= $item['status'] ?></span></td>
                                        <td class="text-end">
                                            <?php if($is_admin): ?>
                                                <a href="verify.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-light border rounded-pill px-3">ตรวจสอบ</a>
                                            <?php else: ?>
                                                <a href="edit.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-light border rounded-pill px-3">ดูรายละเอียด</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center py-5 text-muted">ยังไม่มีรายการข้อมูลในระบบ</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 📊 Monthly Trend Chart (Premium Style)
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyBg = monthlyCtx.createLinearGradient(0, 0, 0, 400);
    monthlyBg.addColorStop(0, 'rgba(242, 114, 43, 0.2)');
    monthlyBg.addColorStop(1, 'rgba(242, 114, 43, 0)');

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'จำนวนเคส',
                data: <?= json_encode($monthlyCounts) ?>,
                borderColor: '#f2722b',
                backgroundColor: monthlyBg,
                borderWidth: 4,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#f2722b',
                pointBorderWidth: 2,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    displayColors: false,
                    callbacks: {
                        label: function(context) { return 'จำนวน: ' + context.parsed.y + ' เคส'; }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { borderDash: [5, 5], color: '#f0f0f0' },
                    ticks: { font: { size: 12 }, color: '#94a3b8' }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 12 }, color: '#94a3b8' }
                }
            }
        }
    });

    // 🥧 Category Breakdown Chart
    <?php
        $catLabels = [];
        $catCounts = [];
        $catColors = [
            'pre-sale' => '#f2722b',
            'technical' => '#3b82f6',
            'customer' => '#10b981',
            'default' => '#94a3b8'
        ];
        $bgColors = [];
        foreach($catData as $cat) {
            $label = $cat['claim_category'] === 'pre-sale' ? 'ก่อนขาย' : ($cat['claim_category'] === 'technical' ? 'ปัญหาเทคนิค' : 'ลูกค้า');
            $catLabels[] = $label;
            $catCounts[] = $cat['total'];
            $bgColors[] = $catColors[$cat['claim_category']] ?? $catColors['default'];
        }
    ?>
    
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($catLabels) ?>,
            datasets: [{
                data: <?= json_encode($catCounts) ?>,
                backgroundColor: <?= json_encode($bgColors) ?>,
                borderWidth: 5,
                borderColor: '#ffffff',
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        usePointStyle: true, 
                        padding: 25,
                        font: { size: 12, weight: '500' },
                        color: '#475569'
                    } 
                }
            },
            cutout: '75%'
        }
    });
</script>

</body>
</html>
