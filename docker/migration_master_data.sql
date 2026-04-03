-- =====================================================
-- Migration: Master Data Tables + verifier_emp_id
-- วันที่: 2026-04-03
-- คำอธิบาย: 
--   1. สร้างตาราง master_brands (ยี่ห้อ) — ใหม่
--   2. สร้างตาราง master_grades (เกรด) — ใหม่
--   3. สร้างตาราง master_claim_categories (ประเภทเคลม) — ใหม่
--   4. สร้างตาราง master_statuses (สถานะ) — ใหม่
--   5. เพิ่มคอลัมน์ verifier_emp_id ในตาราง claims — ใหม่
-- =====================================================

-- =====================================================
-- ตาราง 1: master_brands (ยี่ห้อรถ)
-- =====================================================
CREATE TABLE IF NOT EXISTS `master_brands` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `brand_name` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลเริ่มต้น (จาก hardcode เดิมใน index.php)
INSERT INTO `master_brands` (`brand_name`, `sort_order`) VALUES
    ('Honda', 1),
    ('Yamaha', 2),
    ('Vespa', 3);

-- =====================================================
-- ตาราง 2: master_grades (เกรดรถมือสอง)
-- =====================================================
CREATE TABLE IF NOT EXISTS `master_grades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `grade_code` VARCHAR(50) NOT NULL,
    `grade_name` VARCHAR(100) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลเริ่มต้น (จาก hardcode เดิมใน index.php)
INSERT INTO `master_grades` (`grade_code`, `grade_name`, `sort_order`) VALUES
    ('A_premium', 'A พรีเมี่ยม', 1),
    ('A_w6', 'A (6ด.)', 2),
    ('C_w1', 'C (1ด.)', 3),
    ('C_as_is', 'C (ตามสภาพ)', 4);

-- =====================================================
-- ตาราง 3: master_claim_categories (ประเภทการเคลม)
-- =====================================================
CREATE TABLE IF NOT EXISTS `master_claim_categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(150) NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลเริ่มต้น (จาก hardcode เดิมใน index.php)
INSERT INTO `master_claim_categories` (`category_name`, `sort_order`) VALUES
    ('เคลมรถก่อนขาย', 1),
    ('เคลมปัญหาทางเทคนิค', 2),
    ('เคลมรถลูกค้า', 3);

-- =====================================================
-- ตาราง 4: master_statuses (สถานะเคลม)
-- =====================================================
CREATE TABLE IF NOT EXISTS `master_statuses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `status_code` VARCHAR(50) NOT NULL,
    `status_name` VARCHAR(100) NOT NULL,
    `badge_class` VARCHAR(100) DEFAULT 'status-pending',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ข้อมูลเริ่มต้น (จากที่ user ระบุ)
INSERT INTO `master_statuses` (`status_code`, `status_name`, `badge_class`, `sort_order`) VALUES
    ('Approved Claim', 'อนุมัติการเคลม', 'status-approve', 1),
    ('Approved Replacement', 'อนุมัติเปลี่ยนคัน', 'status-approve', 2),
    ('Rejected', 'ไม่อนุมัติ', 'status-reject', 3),
    ('Replaced', 'เปลี่ยนคัน', 'bg-info text-white', 4),
    ('Pending Fix', 'รอแก้ไข', 'status-pending', 5),
    ('Completed', 'ดำเนินการเสร็จสิ้น', 'bg-success text-white', 6);

-- =====================================================
-- เพิ่มคอลัมน์ verifier_emp_id ในตาราง claims
-- =====================================================
ALTER TABLE `claims` ADD COLUMN `verifier_emp_id` VARCHAR(50) DEFAULT NULL AFTER `verify_remarks`;
