-- ============================================================
-- PJclaim V3 Normalized Schema
-- ออกแบบใหม่แยกตารางตามประเภทข้อมูล (Main, Parts, Repair, Replacement)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;

-- 1. ตาราง users (ระบบจัดการผู้ใช้)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `branch` varchar(255) DEFAULT NULL,
  `tags` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`employee_id`, `name`, `signature`, `password`, `role`, `branch`, `tags`) VALUES
('admin', 'ผู้ดูแลระบบ', 'Admin', '1234', 'admin', 'สาขา สกลนคร', '["repairBranch","sendHQ","replaceVehicle"]');

-- 2. ตารางหลัก claims (ข้อมูลพื้นฐาน)
CREATE TABLE IF NOT EXISTS `claims` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `claim_type` enum('RepairBranch', 'SendHQ', 'ReplaceVehicle', 'Other') NOT NULL,
  `claim_date` date DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `claim_end_date` date DEFAULT NULL,
  
  -- ข้อมูลรถ
  `vin` varchar(100) DEFAULT NULL,
  `mileage` varchar(50) DEFAULT NULL,
  `car_type` varchar(100) DEFAULT NULL,
  `car_brand` varchar(100) DEFAULT NULL,
  `used_grade` varchar(50) DEFAULT NULL,
  
  -- ข้อมูลเจ้าของ
  `owner_name` varchar(255) DEFAULT NULL,
  `owner_phone` varchar(50) DEFAULT NULL,
  `owner_address` text DEFAULT NULL,
  
  -- ข้อมูลปัญหา
  `problem_desc` text DEFAULT NULL,
  `inspect_method` text DEFAULT NULL,
  `inspect_cause` text DEFAULT NULL,
  `claim_category` varchar(100) DEFAULT NULL,
  
  -- Metadata
  `branch` varchar(255) DEFAULT NULL,
  `recorder_id` varchar(50) DEFAULT NULL,
  `editor_id` varchar(50) DEFAULT NULL,
  `claim_images` text DEFAULT NULL, -- JSON
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_vin` (`vin`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. ตารางรายการอะไหล่ (Parts)
CREATE TABLE IF NOT EXISTS `claim_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_id` int UNSIGNED NOT NULL,
  `part_name` varchar(255) NOT NULL,
  `part_code` varchar(100) DEFAULT NULL,
  `quantity` int DEFAULT 1,
  `unit_price` decimal(12,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`claim_id`) REFERENCES `claims`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. ตารางรายละเอียดงานซ่อม (Repair Details)
CREATE TABLE IF NOT EXISTS `claim_repair_details` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_id` int UNSIGNED NOT NULL,
  `job_number` varchar(100) DEFAULT NULL,
  `job_amount` decimal(12,2) DEFAULT NULL,
  `parts_delivery` varchar(100) DEFAULT NULL,
  
  -- ผู้อนุมัติ
  `approver_id` varchar(50) DEFAULT NULL,
  `approver_name` varchar(255) DEFAULT NULL,
  `approver_signature` varchar(255) DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_claim_id` (`claim_id`),
  FOREIGN KEY (`claim_id`) REFERENCES `claims`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. ตารางรายละเอียดการเปลี่ยนคัน (Replacement Details)
CREATE TABLE IF NOT EXISTS `claim_replacement_details` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `claim_id` int UNSIGNED NOT NULL,
  
  -- ข้อมูลเงิน
  `old_down_balance` decimal(12,2) DEFAULT NULL,
  `new_down_balance` decimal(12,2) DEFAULT NULL,
  
  -- รถคันใหม่
  `replace_vin` varchar(100) DEFAULT NULL,
  `replace_brand` varchar(100) DEFAULT NULL,
  `replace_model` varchar(100) DEFAULT NULL,
  `replace_color` varchar(50) DEFAULT NULL,
  `replace_type` varchar(100) DEFAULT NULL,
  `replace_used_grade` varchar(50) DEFAULT NULL,
  `replace_receive_date` date DEFAULT NULL,
  `replace_reason` text DEFAULT NULL,
  
  -- ผู้อนุมัติ
  `approver_id` varchar(50) DEFAULT NULL,
  `approver_name` varchar(255) DEFAULT NULL,
  `approver_signature` varchar(255) DEFAULT NULL,
  `approve_date` date DEFAULT NULL,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_claim_id` (`claim_id`),
  FOREIGN KEY (`claim_id`) REFERENCES `claims`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
