-- ============================================================
-- PJclaim V2 Migration
-- รันไฟล์นี้ใน phpMyAdmin หรือ MySQL CLI
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

-- ============================================================
-- 1. สร้างตาราง users (ระบบ Login / Role / Tag)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รหัสพนักงาน (ใช้ Login)',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ชื่อ-นามสกุล',
  `signature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ลายเซ็นต์ (ข้อความ)',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'รหัสผ่าน (plain text สำหรับ V2 ง่ายๆ)',
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user' COMMENT 'สิทธิ์: admin หรือ user',
  `branch` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'สาขาที่สังกัด',
  `tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'JSON array ของ tags เช่น ["repairBranch","sendHQ","replaceVehicle"]',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'สถานะใช้งาน 1=ใช้งาน 0=ปิดการใช้งาน',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. เพิ่ม Admin ตัวอย่าง (รหัสพนักงาน: admin, รหัสผ่าน: 1234)
-- ============================================================
INSERT INTO `users` (`employee_id`, `name`, `signature`, `password`, `role`, `branch`, `tags`) VALUES
('admin', 'ผู้ดูแลระบบ', 'Admin', '1234', 'admin', 'สาขา สกลนคร', '["repairBranch","sendHQ","replaceVehicle"]');

-- ============================================================
-- 3. ALTER ตาราง claims — เพิ่มคอลัมน์ใหม่
-- ============================================================

-- 3.1 เลขไมล์
ALTER TABLE `claims` ADD COLUMN `mileage` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เลขไมล์ของรถ' AFTER `vin`;

-- 3.2 วันที่ขายรถ
ALTER TABLE `claims` ADD COLUMN `sale_date` date DEFAULT NULL COMMENT 'วันที่ขายรถ' AFTER `claimDate`;

-- 3.3 เลขที่ Job + จำนวนเงิน (แทนค่าแรง)
ALTER TABLE `claims` ADD COLUMN `job_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'เลขที่ Job' AFTER `partsDelivery`;
ALTER TABLE `claims` ADD COLUMN `job_amount` decimal(12,2) DEFAULT NULL COMMENT 'จำนวนเงิน Job' AFTER `job_number`;

-- 3.4 ผู้อนุมัติ ซ่อมที่สาขา
ALTER TABLE `claims` ADD COLUMN `approver_repair_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'รหัสพนักงาน ผู้อนุมัติซ่อมสาขา' AFTER `replace_approve_date`;
ALTER TABLE `claims` ADD COLUMN `approver_repair_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อ ผู้อนุมัติซ่อมสาขา' AFTER `approver_repair_id`;
ALTER TABLE `claims` ADD COLUMN `approver_repair_signature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ลายเซ็นต์ ผู้อนุมัติซ่อมสาขา' AFTER `approver_repair_name`;

-- 3.5 ผู้อนุมัติ ส่งซ่อม สนญ.
ALTER TABLE `claims` ADD COLUMN `approver_sendhq_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'รหัสพนักงาน ผู้อนุมัติส่งซ่อม สนญ.' AFTER `approver_repair_signature`;
ALTER TABLE `claims` ADD COLUMN `approver_sendhq_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ชื่อ ผู้อนุมัติส่งซ่อม สนญ.' AFTER `approver_sendhq_id`;
ALTER TABLE `claims` ADD COLUMN `approver_sendhq_signature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ลายเซ็นต์ ผู้อนุมัติส่งซ่อม สนญ.' AFTER `approver_sendhq_name`;

-- 3.6 วันที่สิ้นสุดเคลม
ALTER TABLE `claims` ADD COLUMN `claim_end_date` date DEFAULT NULL COMMENT 'วันสิ้นสุดเคลม' AFTER `approver_sendhq_signature`;

-- 3.7 เปลี่ยน status ENUM ให้รองรับสถานะใหม่
ALTER TABLE `claims` MODIFY COLUMN `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending' COMMENT 'สถานะ: Pending, PendingEdit, ApprovedClaim, ApprovedReplace, Rejected, Completed, Replaced';
