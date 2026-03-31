<?php
/**
 * logout.php — ออกจากระบบ
 */
require_once __DIR__ . '/../backend/auth.php';
doLogout();
header('Location: login.php');
exit;
?>
