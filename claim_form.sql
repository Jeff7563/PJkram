-- SQL schema for claim_form.php database storage
-- Database configuration from claim_form.php:
--   host=127.0.0.1, port=3306, dbname=service_center, table=claims

CREATE DATABASE IF NOT EXISTS `service_center` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `service_center`;

CREATE TABLE IF NOT EXISTS `claims` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME DEFAULT NULL,
  `branch` VARCHAR(255) DEFAULT NULL,
  `claimDate` DATE DEFAULT NULL,
  `carType` VARCHAR(50) DEFAULT NULL,
  `carBrand` VARCHAR(100) DEFAULT NULL,
  `vin` VARCHAR(80) DEFAULT NULL,
  `ownerName` VARCHAR(255) DEFAULT NULL,
  `problemDesc` LONGTEXT,
  `inspectMethod` LONGTEXT,
  `inspectCause` LONGTEXT,
  `claimCategory` VARCHAR(100) DEFAULT NULL,
  `repairBranch` TINYINT(1) DEFAULT 0,
  `sendHQ` TINYINT(1) DEFAULT 0,
  `parts` LONGTEXT,
  `partsDelivery` VARCHAR(50) DEFAULT NULL,
  `recorder` VARCHAR(255) DEFAULT NULL,
  `files` LONGTEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
