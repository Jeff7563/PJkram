-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 03, 2026 at 06:26 AM
-- Server version: 8.0.45
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `service_center`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int UNSIGNED NOT NULL,
  `branch_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_code`, `branch_name`, `created_at`) VALUES
(4, 'SK-01', 'สาขาสกลนคร', '2026-04-03 04:32:53');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int UNSIGNED NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `claim_type` enum('RepairBranch','SendHQ','ReplaceVehicle','Other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `claim_date` date DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `claim_end_date` date DEFAULT NULL,
  `vin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mileage` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `car_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `car_brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `used_grade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_address` text COLLATE utf8mb4_unicode_ci,
  `problem_desc` text COLLATE utf8mb4_unicode_ci,
  `inspect_method` text COLLATE utf8mb4_unicode_ci,
  `inspect_cause` text COLLATE utf8mb4_unicode_ci,
  `claim_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorder_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editor_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `claim_images` text COLLATE utf8mb4_unicode_ci,
  `verify_remarks` text COLLATE utf8mb4_unicode_ci,
  `verifier_emp_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verifier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verifier_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verify_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_items`
--

CREATE TABLE `claim_items` (
  `id` int UNSIGNED NOT NULL,
  `claim_id` int UNSIGNED NOT NULL,
  `part_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `part_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int DEFAULT '1',
  `unit_price` decimal(12,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_repair_details`
--

CREATE TABLE `claim_repair_details` (
  `id` int UNSIGNED NOT NULL,
  `claim_id` int UNSIGNED NOT NULL,
  `job_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_amount` decimal(12,2) DEFAULT NULL,
  `parts_delivery` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approver_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approver_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_replacement_details`
--

CREATE TABLE `claim_replacement_details` (
  `id` int UNSIGNED NOT NULL,
  `claim_id` int UNSIGNED NOT NULL,
  `old_down_balance` decimal(12,2) DEFAULT NULL,
  `new_down_balance` decimal(12,2) DEFAULT NULL,
  `replace_vin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replace_brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replace_model` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replace_color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replace_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replace_used_grade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replace_receive_date` date DEFAULT NULL,
  `replace_reason` text COLLATE utf8mb4_unicode_ci,
  `approver_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approver_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approver_signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approve_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_brands`
--

CREATE TABLE `master_brands` (
  `id` int NOT NULL,
  `brand_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_brands`
--

INSERT INTO `master_brands` (`id`, `brand_name`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Honda', 1, 1, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(2, 'Yamaha', 1, 2, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(3, 'Vespa', 1, 3, '2026-04-03 04:08:33', '2026-04-03 04:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `master_claim_categories`
--

CREATE TABLE `master_claim_categories` (
  `id` int NOT NULL,
  `category_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_claim_categories`
--

INSERT INTO `master_claim_categories` (`id`, `category_name`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'เคลมรถก่อนขาย', 1, 1, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(2, 'เคลมปัญหาทางเทคนิค', 1, 2, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(3, 'เคลมรถลูกค้า', 1, 3, '2026-04-03 04:08:33', '2026-04-03 04:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `master_grades`
--

CREATE TABLE `master_grades` (
  `id` int NOT NULL,
  `grade_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `grade_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_grades`
--

INSERT INTO `master_grades` (`id`, `grade_code`, `grade_name`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'A_premium', 'A พรีเมี่ยม', 1, 1, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(2, 'A_w6', 'A (6ด.)', 1, 2, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(3, 'C_w1', 'C (1ด.)', 1, 3, '2026-04-03 04:08:33', '2026-04-03 04:08:33'),
(4, 'C_as_is', 'C (ตามสภาพ)', 1, 4, '2026-04-03 04:08:33', '2026-04-03 04:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `master_statuses`
--

CREATE TABLE `master_statuses` (
  `id` int NOT NULL,
  `status_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `badge_class` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'status-pending',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_statuses`
--

INSERT INTO `master_statuses` (`id`, `status_code`, `status_name`, `badge_class`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Approved Claim', 'อนุมัติการเคลม', 'status-approve', 1, 1, '2026-04-03 04:08:34', '2026-04-03 04:08:34'),
(2, 'Approved Replacement', 'อนุมัติเปลี่ยนคัน', 'status-approve', 1, 2, '2026-04-03 04:08:34', '2026-04-03 04:08:34'),
(3, 'Rejected', 'ไม่อนุมัติ', 'status-reject', 1, 3, '2026-04-03 04:08:34', '2026-04-03 04:08:34'),
(4, 'Replaced', 'เปลี่ยนคัน', 'bg-info text-white', 1, 4, '2026-04-03 04:08:34', '2026-04-03 04:08:34'),
(5, 'Pending Fix', 'รอแก้ไข', 'status-pending', 1, 5, '2026-04-03 04:08:34', '2026-04-03 04:08:34'),
(6, 'Completed', 'ดำเนินการเสร็จสิ้น', 'bg-success text-white', 1, 6, '2026-04-03 04:08:34', '2026-04-03 04:08:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `employee_id` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `signature` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_id`, `name`, `signature`, `password`, `role`, `branch`, `tags`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'ผู้ดูแลระบบ', 'Admin', '1234', 'admin', 'สาขาสกลนคร', '[\"repairBranch\",\"sendHQ\",\"replaceVehicleNew\",\"replaceVehicleUsed\"]', 1, '2026-04-02 04:49:00', '2026-04-03 04:15:12'),
(5, '64063', 'รัตนา ขาวสุวรรณ', 'H', '64063', 'user', '', '[]', 1, '2026-04-03 01:25:40', NULL),
(6, '63096', 'วัฒนา กรุงศรี', 'H', '63096', 'user', '', '[]', 1, '2026-04-03 01:27:23', NULL),
(7, '67039', 'ศรีหนุ่ม', 'Y', '67039', 'user', '', '[]', 1, '2026-04-03 01:28:10', NULL),
(8, '65179', 'กล้วย', 'Y', '65179', 'user', '', '[]', 1, '2026-04-03 01:28:33', NULL),
(9, '47045', 'นายสีเมือง แสนภูวา', '-', '47045', 'user', 'สาขาสกลนคร', '[\"repairBranch\",\"sendHQ\"]', 1, '2026-04-03 01:29:10', '2026-04-03 01:46:06'),
(10, '48061', 'เนตรนภา มุ่งคำคุณ', '-', '48061', 'user', 'สาขาสกลนคร', '[\"replaceVehicleNew\",\"replaceVehicleUsed\"]', 1, '2026-04-03 01:29:52', '2026-04-03 06:00:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_branch_name` (`branch_name`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_vin` (`vin`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `claim_items`
--
ALTER TABLE `claim_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`);

--
-- Indexes for table `claim_repair_details`
--
ALTER TABLE `claim_repair_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_claim_id` (`claim_id`);

--
-- Indexes for table `claim_replacement_details`
--
ALTER TABLE `claim_replacement_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_claim_id` (`claim_id`);

--
-- Indexes for table `master_brands`
--
ALTER TABLE `master_brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_claim_categories`
--
ALTER TABLE `master_claim_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_grades`
--
ALTER TABLE `master_grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `master_statuses`
--
ALTER TABLE `master_statuses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claim_items`
--
ALTER TABLE `claim_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `claim_repair_details`
--
ALTER TABLE `claim_repair_details`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `claim_replacement_details`
--
ALTER TABLE `claim_replacement_details`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `master_brands`
--
ALTER TABLE `master_brands`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `master_claim_categories`
--
ALTER TABLE `master_claim_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `master_grades`
--
ALTER TABLE `master_grades`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `master_statuses`
--
ALTER TABLE `master_statuses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claim_items`
--
ALTER TABLE `claim_items`
  ADD CONSTRAINT `claim_items_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claim_repair_details`
--
ALTER TABLE `claim_repair_details`
  ADD CONSTRAINT `claim_repair_details_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claim_replacement_details`
--
ALTER TABLE `claim_replacement_details`
  ADD CONSTRAINT `claim_replacement_details_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
