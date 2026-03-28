-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Mar 28, 2026 at 09:55 AM
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
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `branch` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `claimDate` date DEFAULT NULL,
  `carType` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carBrand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vin` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ownerName` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ownerPhone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `problemDesc` longtext COLLATE utf8mb4_unicode_ci,
  `inspectMethod` longtext COLLATE utf8mb4_unicode_ci,
  `inspectCause` longtext COLLATE utf8mb4_unicode_ci,
  `claimCategory` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repairBranch` tinyint(1) DEFAULT '0',
  `sendHQ` tinyint(1) DEFAULT '0',
  `parts` longtext COLLATE utf8mb4_unicode_ci,
  `partsDelivery` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `editor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `files` longtext COLLATE utf8mb4_unicode_ci,
  `status` enum('Pending','Approved','Rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `created_at`, `branch`, `claimDate`, `carType`, `carBrand`, `vin`, `ownerName`, `ownerPhone`, `problemDesc`, `inspectMethod`, `inspectCause`, `claimCategory`, `repairBranch`, `sendHQ`, `parts`, `partsDelivery`, `recorder`, `editor`, `updated_at`, `files`, `status`) VALUES
(6, '2026-03-28 08:35:27', 'สาขา สกลนคร', '2026-03-28', 'new', 'Honda', 'AG165168465', 'นายเทส ระบบ', '0888999898', 'เทส ระบบ', 'เทส ระบบ', 'เทส ระบบ', 'pre-sale', 0, 0, '[]', 'in_stock', 'นายเทส ระบบ', 'นายแก้ระบบ', '2026-03-28 09:54:29', '[]', 'Pending'),
(7, '2026-03-28 08:38:17', 'สาขา สกลนคร', '2026-03-28', 'used', 'Yamaha', 'HAH549651', 'นายเทส ระบบ', '07752158491', 'คือ อะไร', 'แบบนั้นแหละ', 'ไม่รู้', 'pre-sale', 1, 0, '[{\"code\":\"A001\",\"name\":\"เทส ระบบ\",\"qty\":\"1\",\"price\":\"200\",\"note\":\"\"},{\"code\":\"A002\",\"name\":\"เทส ระบบ\",\"qty\":\"1\",\"price\":\"450\",\"note\":\"\"},{\"code\":\"ฺฺB001\",\"name\":\"เทส ระบบ ผ่านไหมนะ\",\"qty\":\"1\",\"price\":\"3000\",\"note\":\"\"}]', 'wait_hq', 'นายเทสระบบผ่านไหมนะ', NULL, NULL, '[]', 'Approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
