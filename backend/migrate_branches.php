<?php
require_once __DIR__ . '/../shared/config/db_connect.php';

try {
    $pdo = getServiceCenterPDO();
    
    // 1. ตราจสอบช่อง branch_code (ถ้ายังไม่มีให้เพิ่ม)
    $checkSql = "SHOW COLUMNS FROM `branches` LIKE 'branch_code'";
    $exists = $pdo->query($checkSql)->fetch();
    if (!$exists) {
        $pdo->exec("ALTER TABLE `branches` ADD COLUMN `branch_code` varchar(50) AFTER `id` ");
    }
    
    // 2. อัปเดตรหัสสาขาที่ยังเป็นค่าว่างให้เป็นรหัสตัวอย่างอัตโนมัติ
    $pdo->exec("UPDATE branches SET branch_code = 'BR001' WHERE (branch_code IS NULL OR branch_code = '') AND branch_name LIKE '%สกลนคร%' ");
    $pdo->exec("UPDATE branches SET branch_code = 'TEMP' WHERE branch_code IS NULL OR branch_code = '' "); // กันพลาดสำหรับสาขาอื่น

    // 3. ปรับปรุง Unique Key (ถ้ามีปัญหา)
    $sql = "CREATE TABLE IF NOT EXISTS `branches` (
      `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
      `branch_code` varchar(50) DEFAULT NULL,
      `branch_name` varchar(255) NOT NULL,
      `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `uk_branch_name` (`branch_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $pdo->exec($sql);
    
    echo "✅ อัปเดตรหัสสาขาเก่าเข้าสู่ระบบเรียบร้อยแล้ว!<br>ตอนนี้รหัสควรจะแสดงในหน้า Admin แล้วครับ";
    
} catch (Exception $e) {
    echo "❌ เกิดข้อผิดพลาด: " . $e->getMessage();
}
