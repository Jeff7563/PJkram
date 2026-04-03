<?php
require_once __DIR__ . '/../backend/auth.php';

// ถ้า Login อยู่แล้ว ให้ redirect ไปหน้าหลัก
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empId = trim($_POST['employee_id'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    
    if (empty($empId) || empty($pass)) {
        $error = 'กรุณากรอกรหัสพนักงานและรหัสผ่าน';
    } else {
        $user = doLogin($empId, $pass);
        if ($user) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'รหัสพนักงานหรือรหัสผ่านไม่ถูกต้อง';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ — ระบบจัดการฟอร์มส่งเคลม</title>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../shared/assets/css/styles-login.css">
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="logo-section">
        <div class="logo-badge">
          <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
        </div>
        <h1 class="login-title">เข้าสู่ระบบ</h1>
        <p class="login-subtitle">ระบบจัดการฟอร์มส่งเคลม — อึ่งกุ่ยเฮง</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="login.php" autocomplete="off">
        <div class="form-group">
          <label class="form-label" for="employee_id">รหัสพนักงาน</label>
          <input type="text" id="employee_id" name="employee_id" class="form-input" 
                 placeholder="กรอกรหัสพนักงาน" required autofocus
                 value="<?= htmlspecialchars($_POST['employee_id'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">รหัสผ่าน</label>
          <input type="password" id="password" name="password" class="form-input" 
                 placeholder="กรอกรหัสผ่าน" required>
        </div>

        <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
      </form>

      <div class="login-footer">
        PJclaim V2 &copy; <?= date('Y') + 543 ?> อึ่งกุ่ยเฮง สกลนคร
      </div>
    </div>
  </div>
</body>
</html>
