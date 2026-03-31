<?php
require_once __DIR__ . '/../backend/auth.php';
requireLogin();
require_once __DIR__ . '/../shared/config/db_connect.php';

try {
    $pdo = getServiceCenterPDO();
    
    // 1. นำข้อมูลภาพรวมออกมา (Summary Cards)
    $stmtCount = $pdo->query("SELECT COUNT(*) as total FROM claims");
    $totalClaims = $stmtCount->fetch()['total'];

    $stmtStatus = $pdo->query("SELECT status, COUNT(*) as count FROM claims GROUP BY status");
    $statusData = $stmtStatus->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $pending = $statusData['Pending'] ?? 0;
    $approved = $statusData['Approved'] ?? 0;
    $rejected = $statusData['Rejected'] ?? 0;

    // 2. ข้อมูลรายเดือน (Monthly Trend)
    $stmtMonthly = $pdo->query("
        SELECT DATE_FORMAT(claim_date, '%Y-%m') as month, COUNT(*) as total 
        FROM claims 
        WHERE claim_date IS NOT NULL 
        GROUP BY month 
        ORDER BY month ASC 
        LIMIT 12
    ");
    $monthlyData = $stmtMonthly->fetchAll(PDO::FETCH_ASSOC);
    $months = array_column($monthlyData, 'month');
    $monthlyCounts = array_column($monthlyData, 'total');

    // 3. ข้อมูลรายสาขา (Branch Breakdown)
    $stmtBranch = $pdo->query("SELECT branch, COUNT(*) as total FROM claims GROUP BY branch ORDER BY total DESC");
    $branchData = $stmtBranch->fetchAll(PDO::FETCH_ASSOC);
    $branches = array_column($branchData, 'branch');
    $branchCounts = array_column($branchData, 'total');

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            border-radius: 20px;
            padding: 25px;
            color: white;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .bg-gradient-orange { background: linear-gradient(135deg, #f2722b, #ff9b50); }
        .bg-gradient-blue   { background: linear-gradient(135deg, #2c3e50, #4ca1af); }
        .bg-gradient-green  { background: linear-gradient(135deg, #00b551, #00e676); }
        .bg-gradient-red    { background: linear-gradient(135deg, #e74c3c, #ff5252); }
        
        .stat-number { font-size: 2.8rem; font-weight: 700; line-height: 1; }
        .stat-label { font-size: 1.1rem; opacity: 0.9; font-weight: 500; }
        .stat-icon { font-size: 2rem; opacity: 0.3; align-self: flex-end; }
        
        .chart-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.03);
            margin-bottom: 25px;
        }
        .chart-title { font-weight: 700; margin-bottom: 20px; color: #333; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../shared/assets/includes/sidebar.php'; ?>

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold m-0" style="color: #2c3e50;">📊 ภาพรวมระบบจัดการเคลม</h2>
            <div class="text-muted fw-500">อัปเดตล่าสุด: <?= date('d/m/Y H:i') ?></div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-blue">
                    <div class="stat-label">รายการเคลมทั้งหมด</div>
                    <div class="stat-number"><?= $totalClaims ?></div>
                    <div class="stat-icon">📑</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-orange">
                    <div class="stat-label">รอดำเนินการ</div>
                    <div class="stat-number"><?= $pending ?></div>
                    <div class="stat-icon">⏳</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-green">
                    <div class="stat-label">อนุมัติแล้ว</div>
                    <div class="stat-number"><?= $approved ?></div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="stat-card bg-gradient-red">
                    <div class="stat-label">ปฏิเสธ</div>
                    <div class="stat-number"><?= $rejected ?></div>
                    <div class="stat-icon">❌</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <div class="chart-container">
                    <h5 class="chart-title">📈 แนวโน้มการเคลมรายเดือน</h5>
                    <div style="height: 350px;">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-container">
                    <h5 class="chart-title">🏢 ยอดเคลมแยกตามสาขา</h5>
                    <div style="height: 350px;">
                        <canvas id="branchChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'จำนวนเคส',
                data: <?= json_encode($monthlyCounts) ?>,
                borderColor: '#f2722b',
                backgroundColor: 'rgba(242, 114, 43, 0.1)',
                borderWidth: 4,
                tension: 0.4,
                fill: true,
                pointRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#f2722b',
                pointBorderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                x: { grid: { display: false } }
            }
        }
    });

    // Branch Chart
    const branchCtx = document.getElementById('branchChart').getContext('2d');
    new Chart(branchCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($branches) ?>,
            datasets: [{
                data: <?= json_encode($branchCounts) ?>,
                backgroundColor: ['#f2722b', '#2c3e50', '#00b551', '#e74c3c', '#f39c12'],
                borderWidth: 0,
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
            },
            cutout: '70%'
        }
    });
</script>

</body>
</html>
