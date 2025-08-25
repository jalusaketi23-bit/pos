
-- revo_pos.sql
-- POS Database for PHP 7 app (InnoDB, utf8mb4)
-- Created: 2025-08-25

SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

-- Create database (you can change the name as needed)
CREATE DATABASE IF NOT EXISTS `revo_pos` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `revo_pos`;

-- Drop tables if exist (safe re-import)
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `purchase_items`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `purchases`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `suppliers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;

-- USERS
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','cashier') NOT NULL DEFAULT 'cashier',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CATEGORIES
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `parent_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `idx_categories_parent` (`parent_id`),
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PRODUCTS
CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sku` VARCHAR(64) NOT NULL,
  `barcode` VARCHAR(64) NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `category_id` INT UNSIGNED NULL,
  `cost_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sell_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `stock` INT NOT NULL DEFAULT 0,
  `unit` VARCHAR(20) NOT NULL DEFAULT 'pcs',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_products_sku` (`sku`),
  UNIQUE KEY `uk_products_barcode` (`barcode`),
  KEY `idx_products_name` (`name`),
  KEY `idx_products_category` (`category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CUSTOMERS
CREATE TABLE `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `address` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SUPPLIERS
CREATE TABLE `suppliers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `address` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PURCHASES
CREATE TABLE `purchases` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_id` INT UNSIGNED NOT NULL,
  `invoice_no` VARCHAR(50) NOT NULL,
  `purchase_date` DATETIME NOT NULL,
  `note` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_purchases_invoice_no` (`invoice_no`),
  KEY `idx_purchases_supplier` (`supplier_id`),
  CONSTRAINT `fk_purchases_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PURCHASE ITEMS
CREATE TABLE `purchase_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `purchase_id` BIGINT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `qty` INT NOT NULL,
  `cost_price` DECIMAL(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_pitems_purchase` (`purchase_id`),
  KEY `idx_pitems_product` (`product_id`),
  CONSTRAINT `fk_pitems_purchase` FOREIGN KEY (`purchase_id`) REFERENCES `purchases`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pitems_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SALES
CREATE TABLE `sales` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NULL,
  `invoice_no` VARCHAR(50) NOT NULL,
  `sale_date` DATETIME NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `grand_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sales_invoice_no` (`invoice_no`),
  KEY `idx_sales_customer` (`customer_id`),
  KEY `idx_sales_user` (`user_id`),
  CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SALE ITEMS
CREATE TABLE `sale_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sale_id` BIGINT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `qty` INT NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `discount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_sitems_sale` (`sale_id`),
  KEY `idx_sitems_product` (`product_id`),
  CONSTRAINT `fk_sitems_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sitems_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PAYMENTS
CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sale_id` BIGINT UNSIGNED NOT NULL,
  `method` ENUM('cash','transfer','mixed') NOT NULL DEFAULT 'cash',
  `paid_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `notes` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_sale` (`sale_id`),
  CONSTRAINT `fk_payments_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- STOCK MOVEMENTS
CREATE TABLE `stock_movements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `ref_type` ENUM('purchase','sale','adjustment') NOT NULL,
  `ref_id` BIGINT UNSIGNED NULL,
  `qty` INT NOT NULL, -- positive for in, negative for out
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sm_product` (`product_id`),
  KEY `idx_sm_ref` (`ref_type`, `ref_id`),
  CONSTRAINT `fk_sm_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SETTINGS (key-value)
CREATE TABLE `settings` (
  `key` VARCHAR(64) NOT NULL,
  `value` TEXT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET foreign_key_checks = 1;

-- =====================
-- SEED DATA
-- =====================

-- Users (password for both = "password")
INSERT INTO `users` (`name`,`email`,`password_hash`,`role`) VALUES
('Administrator','admin@revo.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin'),
('Kasir Demo','kasir@revo.local','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','cashier');

-- Categories
INSERT INTO `categories` (`name`,`parent_id`) VALUES
('Minuman',NULL),
('Makanan',NULL),
('Kebersihan',NULL);

-- Products (10 examples)
INSERT INTO `products` (`sku`,`barcode`,`name`,`category_id`,`cost_price`,`sell_price`,`stock`,`unit`,`is_active`) VALUES
('SKU-TEH-001','8991002100011','Teh Botol 350ml',1,3000,5000,50,'btl',1),
('SKU-AIR-002','8991002100028','Air Mineral 600ml',1,2000,3500,80,'btl',1),
('SKU-MIE-003','8991002100035','Mie Instan Ayam',2,2000,3500,120,'pcs',1),
('SKU-MIE-004','8991002100042','Mie Instan Kari',2,2000,3500,100,'pcs',1),
('SKU-SAB-005','8991002100059','Sabun Mandi 80g',3,2500,4500,60,'pcs',1),
('SKU-SAM-006','8991002100066','Sampo 100ml',3,4000,7000,40,'btl',1),
('SKU-GUL-007','8991002100073','Gula Pasir 1kg',2,11000,14000,30,'sak',1),
('SKU-KOP-008','8991002100080','Kopi Sachet',1,1200,2000,200,'sct',1),
('SKU-SUS-009','8991002100097','Susu Kotak 250ml',1,4000,6500,70,'kot',1),
('SKU-KUE-010','8991002100103','Biskuit Cokelat',2,3500,6000,90,'pcs',1);

-- Customers
INSERT INTO `customers` (`name`,`phone`,`address`) VALUES
('Umum',NULL,NULL),
('Budi','08123456789','Serpong'),
('Sari','08129876543','Ciputat');

-- Suppliers
INSERT INTO `suppliers` (`name`,`phone`,`address`) VALUES
('PT Sumber Minuman','021-555111','Jakarta'),
('CV Maju Jaya','021-777888','Bogor');

-- Example Purchase + items (restock)
INSERT INTO `purchases` (`supplier_id`,`invoice_no`,`purchase_date`,`note`) VALUES
(1,'PO-202508-0001','2025-08-01 10:00:00','Restock awal');

INSERT INTO `purchase_items` (`purchase_id`,`product_id`,`qty`,`cost_price`) VALUES
(1,1,100,3000.00),
(1,2,150,2000.00),
(1,3,200,2000.00);

-- Add corresponding stock movements (in)
INSERT INTO `stock_movements` (`product_id`,`ref_type`,`ref_id`,`qty`,`created_at`) VALUES
(1,'purchase',1,100,'2025-08-01 10:05:00'),
(2,'purchase',1,150,'2025-08-01 10:05:00'),
(3,'purchase',1,200,'2025-08-01 10:05:00');

-- Example Sale + items + payment
INSERT INTO `sales` (`customer_id`,`invoice_no`,`sale_date`,`subtotal`,`discount_total`,`tax_total`,`grand_total`,`user_id`) VALUES
(1,'SL-202508-0001','2025-08-20 09:30:00',13500.00,500.00,0.00,13000.00,2);

INSERT INTO `sale_items` (`sale_id`,`product_id`,`qty`,`unit_price`,`discount`) VALUES
(1,1,1,5000.00,0.00),
(1,3,1,3500.00,0.00),
(1,2,1,3500.00,500.00);

INSERT INTO `payments` (`sale_id`,`method`,`paid_amount`,`change_amount`,`notes`) VALUES
(1,'cash',15000.00,2000.00,'Pembayaran tunai');

-- Stock movements (out)
INSERT INTO `stock_movements` (`product_id`,`ref_type`,`ref_id`,`qty`,`created_at`) VALUES
(1,'sale',1,-1,'2025-08-20 09:31:00'),
(3,'sale',1,-1,'2025-08-20 09:31:00'),
(2,'sale',1,-1,'2025-08-20 09:31:00');

-- Settings defaults
INSERT INTO `settings` (`key`,`value`) VALUES
('store_name','Minimarket Revo'),
('store_address','Jl. Contoh No. 123, Tangerang Selatan'),
('tax_percent','0'),
('receipt_width','58'),
('invoice_prefix','SL');

-- Helpful Views (optional)
DROP VIEW IF EXISTS `v_sales_summary_daily`;
CREATE VIEW `v_sales_summary_daily` AS
SELECT DATE(`sale_date`) AS `sale_day`,
       COUNT(*) AS `transactions`,
       SUM(`grand_total`) AS `grand_total`
FROM `sales`
GROUP BY DATE(`sale_date`);

-- Index sanity
ANALYZE TABLE `products`, `sales`, `sale_items`, `purchases`, `purchase_items`;
