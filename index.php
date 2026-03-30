<?php
/**
 * หน้า Redirect อัตโนมัติสำหรับ Docker Multi-Service
 * ไฟล์นี้จะถูกวางไว้ที่หน้าแรกเพื่อให้การเข้าถึง URL ง่ายขึ้น
 */

$app_role = getenv('APP_ROLE');

if ($app_role === 'backend') {
    // ถ้าเป็นเครื่อง Backend ให้พาไปหน้าเช็คงานทันที
    header('Location: /backend/check.php');
} else {
    // ถ้าเป็นอื่นๆ หรือเครื่อง Frontend ให้พาไปหน้าส่งเคลมทันที
    header('Location: /frontend/index.php');
}
exit;
