-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 26, 2026 at 05:27 PM
-- Server version: 10.11.19-MariaDB
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tech3_shipment_demo2`
--

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('freight_forwarder','cnf_agent') NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`id`, `name`, `type`, `company_name`, `email`, `phone`, `address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Alex Transport', 'freight_forwarder', 'AT Logistics Ltd', 'alex@atlogistics.com', '01710000001', 'Dhaka, Bangladesh', 'Trusted partner for Hong Kong shipments.', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(2, 'Global Freight BD', 'freight_forwarder', 'Global Freight Corporation', 'info@globalfreight.com', '01710000002', 'Chittagong, Bangladesh', 'Handles air cargo.', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(3, 'Skyline Cargo', 'freight_forwarder', 'Skyline Shipping Ltd', 'support@skyline.com', '01710000003', 'Kawran Bazar, Dhaka', NULL, '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(4, 'OceanWay Logistics', 'freight_forwarder', 'OceanWay Co.', 'contact@oceanway.com', '01710000004', 'Uttara, Dhaka', 'Specializes in sea freight.', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(5, 'BD Clearing House', 'cnf_agent', 'BD CH Ltd', 'bdch@mail.com', '01820000001', 'Kamalapur ICD', 'Fast dhaka clearance.', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(6, 'Portside CNF', 'cnf_agent', 'Portside BD', 'portside@mail.com', '01820000002', 'Chittagong Port', 'Expert for port clearance.', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(7, 'Elite Customs BD', 'cnf_agent', 'Elite Customs Co.', 'elite@customsbd.com', '01820000003', 'Airport Customs, Dhaka', NULL, '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(8, 'Prime CNF Agency', 'cnf_agent', 'Prime Logistics BD', 'primecnf@mail.com', '01820000004', 'Gazipur', 'Handles garment shipments.', '2026-10-25 13:47:11', '2026-10-25 13:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `cargo_items`
--

CREATE TABLE `cargo_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `goods_received` int(10) UNSIGNED DEFAULT NULL,
  `printed_ctn_no` varchar(255) DEFAULT NULL,
  `written_ctn_no` varchar(255) DEFAULT NULL,
  `box_no` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `tracking_no` varchar(255) DEFAULT NULL,
  `goods_name_en` varchar(255) DEFAULT NULL,
  `goods_name_local` varchar(255) DEFAULT NULL,
  `pack_no` varchar(30) DEFAULT NULL,
  `unit_pcs` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `weight_kg` decimal(10,3) NOT NULL,
  `length_cm` decimal(8,2) DEFAULT NULL,
  `width_cm` decimal(8,2) DEFAULT NULL,
  `height_cm` decimal(8,2) DEFAULT NULL,
  `cbm` decimal(10,6) DEFAULT NULL,
  `shipping_mark` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `is_copy` tinyint(1) NOT NULL DEFAULT 0,
  `warehouse` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('pending','packed','ready_to_ship','shipping_ongoing','arrived_bd','waiting_for_customs','ready_to_deliver','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancel_reason` varchar(255) DEFAULT NULL,
  `status_before_cancel` varchar(30) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `price_per_kg` decimal(10,2) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `third_party_id` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `delivered_at` date DEFAULT NULL,
  `receiver_name` varchar(255) DEFAULT NULL,
  `receiver_phone` varchar(30) DEFAULT NULL,
  `is_third_party` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cargo_items`
--

INSERT INTO `cargo_items` (`id`, `goods_received`, `printed_ctn_no`, `written_ctn_no`, `box_no`, `receipt_no`, `tracking_no`, `goods_name_en`, `goods_name_local`, `pack_no`, `unit_pcs`, `weight_kg`, `length_cm`, `width_cm`, `height_cm`, `cbm`, `shipping_mark`, `remark`, `is_copy`, `warehouse`, `date`, `status`, `cancelled_at`, `cancel_reason`, `status_before_cancel`, `price`, `price_per_kg`, `customer_id`, `third_party_id`, `paid_at`, `created_at`, `updated_at`, `delivered_at`, `receiver_name`, `receiver_phone`, `is_third_party`) VALUES
(1, 1, 'c10', 'H1001', NULL, '7855820', '自送', 'Backer rod （15mm）', '背衬杆（15mm）', '4', 10, 6.000, 130.00, 100.00, 70.00, 0.910000, 'CPL/MD Baijid Kahman Akash', NULL, 0, NULL, '2026-08-28', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(2, 1, 'c11', 'H3913', NULL, NULL, '188148729386', 'shoes', '鞋子', '2', 12, 15.000, 72.00, 47.00, 35.00, 0.118440, 'Rony Nadim88', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(3, 1, 'c12', 'H3914', NULL, NULL, '188148729386', 'shoes', '鞋子', '2', 12, 15.000, 72.00, 47.00, 35.00, 0.118440, 'Rony Nadim88', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(4, 1, 'c13', 'H3915', NULL, NULL, '188148729387', 'shoes', '鞋子', '2', 12, 15.000, 72.00, 47.00, 35.00, 0.118440, 'Rony/Shakil88417', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(5, 1, 'c14', 'H3916', NULL, NULL, '188148729387', 'shoes', '鞋子', '2', 12, 15.000, 72.00, 47.00, 35.00, 0.118440, 'Rony/Shakil88417', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(6, 1, 'c15', 'H3917', NULL, NULL, '997578285', 'shoes', '鞋子', '3', 20, 25.000, 90.00, 60.00, 30.00, 0.162000, 'Fast Import/RK', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 17500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(7, 1, 'c16', 'H3918', NULL, NULL, '997578285', 'shoes', '鞋子', '3', 20, 25.000, 90.00, 60.00, 30.00, 0.162000, 'Fast Import/RK', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 17500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(8, 1, 'c17', 'H3919', NULL, NULL, '997578285', 'shoes', '鞋子', '3', 20, 25.000, 90.00, 60.00, 30.00, 0.162000, 'Fast Import/RK', NULL, 1, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 17500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(9, 1, 'c18', 'H3920', NULL, NULL, '302354556887', 'bags', '包包', '1', 24, 7.600, 55.00, 40.00, 35.00, 0.077000, 'Chinacart', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 5320.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(10, 1, 'c19', 'H3965', NULL, NULL, '888077504381', 'bra', '文胸', '3', 1000, 33.000, 100.00, 96.00, 26.00, 0.249600, 'MK/MEM', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 23100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(11, 1, 'c20', 'H3966', NULL, NULL, '888077504381', 'bra', '文胸', '3', 1000, 33.000, 100.00, 96.00, 26.00, 0.249600, 'MK/MEM', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 23100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(12, 1, 'c21', 'H3967', NULL, NULL, '888077504381', 'bra', '文胸', '3', 1000, 33.000, 100.00, 96.00, 26.00, 0.249600, 'MK/MEM', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 23100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(13, 1, 'c22', 'H3132', NULL, NULL, '0212848408254', 'disposable RF Electrode', '医用耗材', '1', 120, 5.000, 56.00, 39.00, 23.00, 0.050232, 'CPL/ALI/BD', 'custom declare', 0, NULL, '2026-09-21', 'arrived_bd', NULL, NULL, NULL, 3500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(14, 1, 'c23', 'H3133', NULL, NULL, '0212848408254', 'RF Cannula', '医用耗材', '2', 60, 1.500, 40.00, 35.00, 16.00, 0.022400, 'CPL/ALI/BD', 'custom declare', 0, NULL, '2026-09-21', 'arrived_bd', NULL, NULL, NULL, 1050.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(15, 1, 'c24', 'H3957', NULL, '7595262', '自送', 'velcro', '魔术贴', '8', 5250, 23.000, 43.00, 43.00, 66.00, 0.122034, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 16100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(16, 1, 'c25', 'H3958', NULL, '7595262', '自送', 'velcro', '魔术贴', '8', 5250, 23.000, 43.00, 43.00, 66.00, 0.122034, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 16100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(17, 1, 'c26', 'H3959', NULL, '7595262', '自送', 'velcro', '魔术贴', '8', 5250, 23.000, 43.00, 43.00, 66.00, 0.122034, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 16100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(18, 1, 'c27', 'H3960', NULL, '7595262', '自送', 'ELASTIC SANDING BLOCK HEAD', '弹性打磨块磨头', '8', 100, 10.000, 32.00, 26.00, 15.00, 0.012480, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 7000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(19, 1, 'c28', 'H3961', NULL, '7595262', '自送', 'ELASTIC SANDING BLOCK HEAD', '弹性打磨块磨头', '8', 100, 10.000, 32.00, 26.00, 15.00, 0.012480, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 7000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(20, 1, 'c29', 'H3962', NULL, '7595262', '自送', 'ELASTIC SANDING BLOCK HEAD', '弹性打磨块磨头', '8', 100, 10.000, 32.00, 26.00, 15.00, 0.012480, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 7000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(21, 1, 'c30', 'H3963', NULL, '7595262', '自送', 'ELASTIC SANDING BLOCK HEAD', '弹性打磨块磨头', '8', 100, 10.000, 32.00, 26.00, 15.00, 0.012480, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 7000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(22, 1, 'c31', 'H3964', NULL, '7595262', '自送', 'ELASTIC SANDING BLOCK HEAD', '弹性打磨块磨头', '8', 100, 10.000, 32.00, 26.00, 15.00, 0.012480, 'Mr.Afzal(CBC)', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 7000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(23, 1, 'c32', 'H3467', NULL, NULL, '773443587655552', 'condom', '安全套', '1', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(24, 1, 'c33', 'H3468', NULL, NULL, '773443587655552', 'condom', '安全套', '2', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(25, 1, 'c34', 'H3469', NULL, NULL, '773443587655552', 'condom', '安全套', '3', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(26, 1, 'c35', 'H3470', NULL, NULL, '773443587655552', 'condom', '安全套', '4', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(27, 1, 'c36', 'H3471', NULL, NULL, '773443587655552', 'condom', '安全套', '5', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(28, 1, 'c37', 'H3472', NULL, NULL, '773443587655552', 'condom', '安全套', '6', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(29, 1, 'c38', 'H3473', NULL, NULL, '773443587655552', 'condom', '安全套', '7', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(30, 1, 'c39', 'H3474', NULL, NULL, '773443587655552', 'condom', '安全套', '8', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(31, 1, 'c40', 'H3475', NULL, NULL, '773443587655552', 'condom', '安全套', '9', 0, 5.700, 48.00, 29.00, 26.00, 0.036192, 'None', NULL, 0, NULL, '2026-09-28', 'arrived_bd', NULL, NULL, NULL, 3990.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(32, 1, 'c41', 'H3698', NULL, NULL, '79036753024106', 'keychain', '钥匙扣', '1', 2, 1.000, 10.00, 8.00, 6.00, 0.000480, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(33, 1, 'c42', 'H3699', NULL, NULL, '7647420128750', 'earstuds', '耳钉', '1', 0, 1.000, 10.00, 8.00, 3.00, 0.000240, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(34, 1, 'c43', 'H3700', NULL, NULL, '7647052173700', 'rings', '戒指', '1', 1, 1.000, 8.00, 6.00, 4.00, 0.000192, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(35, 1, 'c44', 'H3701', NULL, NULL, '3179396427791', 'hair clips', '发卡', '1', 1, 1.000, 18.00, 15.00, 3.00, 0.000810, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(36, 1, 'c45', 'H3702', NULL, NULL, '79036983128805', 'phone case', '手机壳', '1', 1, 1.000, 18.00, 14.00, 2.00, 0.000504, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(37, 1, 'c46', 'H3703', NULL, NULL, '79036760289638', 'filter cotton pack', '过滤棉袋', '1', 0, 1.000, 20.00, 15.00, 3.00, 0.000900, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(38, 1, 'c47', 'H3704', NULL, NULL, '79036754228006', 'kids gloves', '儿童手套', '1', 1, 1.000, 15.00, 10.00, 2.00, 0.000300, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(39, 1, 'c48', 'H3705', NULL, NULL, '9823311641759', 'stickers', '贴纸', '1', 1, 1.000, 15.00, 10.00, 2.00, 0.000300, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(40, 1, 'c49', 'H3706', NULL, NULL, '3179424549555', 'phone case', '手机壳', '1', 1, 1.000, 18.00, 12.00, 2.00, 0.000432, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(41, 1, 'c50', 'H3707', NULL, NULL, '3179509035607', 'phone case', '手机壳', '1', 1, 1.000, 18.00, 12.00, 2.00, 0.000432, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(42, 1, 'c51', 'H3708', NULL, NULL, '3179406605121', 'phone case', '手机壳', '1', 1, 1.000, 18.00, 12.00, 2.00, 0.000432, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(43, 1, 'c52', 'H3709', NULL, NULL, '3179424744352', 'phone case', '手机壳', '1', 1, 1.000, 16.00, 12.00, 2.00, 0.000384, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(44, 1, 'c53', 'H3710', NULL, NULL, '3179225083042', 'necklace', '项链', '1', 1, 1.000, 12.00, 10.00, 2.00, 0.000240, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(45, 1, 'c54', 'H3712', NULL, NULL, '773444426246914', 'wallets', '钱包', '1', 1, 1.000, 15.00, 12.00, 2.00, 0.000360, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(46, 1, 'c55', 'H3713', NULL, NULL, '79036336923869', 'necklace', '项链', '1', 0, 1.000, 15.00, 12.00, 2.00, 0.000360, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(47, 1, 'c56', 'H3714', NULL, NULL, '7647055986879', 'rings', '戒指', '1', 1, 1.000, 12.00, 10.00, 3.00, 0.000360, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(48, 1, 'c57', 'H3715', NULL, NULL, '3179226650417', 'necklace', '项链', '1', 2, 1.000, 12.00, 10.00, 3.00, 0.000360, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(49, 1, 'c58', 'H3716', NULL, NULL, '79036794738559', 'keychain', '挂件', '1', 0, 1.000, 12.00, 10.00, 2.00, 0.000240, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(50, 1, 'c59', 'H3717', NULL, NULL, '773444428965494', 'wallets', '钱包', '1', 1, 1.000, 12.00, 10.00, 3.00, 0.000360, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(51, 1, 'c60', 'H3719', NULL, NULL, '79037023578602', 'makeup mirror', '化妆镜', '1', 1, 2.000, 20.00, 17.00, 5.00, 0.001700, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(52, 1, 'c61', 'H3720', NULL, NULL, '79037097099896', 'socks', '凝胶袜', '1', 2, 1.000, 20.00, 10.00, 2.00, 0.000400, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(53, 1, 'c62', 'H3721', NULL, NULL, '773444661403507', 'keychain', '钥匙扣', '1', 2, 1.000, 10.00, 10.00, 2.00, 0.000200, 'Chinacart', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(54, 1, 'c63', 'H3722', NULL, NULL, '801908408956', 'LED Light', 'LED灯', '1', 0, 2.000, 35.00, 20.00, 15.00, 0.010500, 'SAIN EXPRESS IBRAHIM', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(55, 1, 'c64', 'H3734', NULL, NULL, '435384531982331', 'cartoon', '动漫摆件', '1', 0, 1.000, 20.00, 14.00, 11.00, 0.003080, 'Chinacart', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(56, 1, 'c65', 'H3640', NULL, NULL, '1226779463378', 'mannequin', '服装展示模特', '10', 1, 10.500, 91.00, 45.00, 32.00, 0.131040, 'Fast Import/Gafir', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 7350.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(57, 1, 'c66', 'H3641', NULL, NULL, '1226779463378', 'mannequin', '服装展示模特', '10', 1, 10.500, 91.00, 45.00, 32.00, 0.131040, 'Fast Import/Gafir', NULL, 0, NULL, '2026-10-05', 'arrived_bd', NULL, NULL, NULL, 7350.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(58, 1, 'c67', 'H3936', NULL, '7595261', '自送', 'Bungee trampoline accessorise', '儿童游乐滑道', '6', 1, 20.000, 0.80, 0.60, 0.30, 0.000000, 'Zony/Jakria Likgbo', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 14000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:07', NULL, NULL, NULL, 0),
(59, 1, 'c68', 'H3937', NULL, '7595261', '自送', 'Pvc foam pipe', '儿童游乐滑道', '6', 1, 6.000, 2.50, 0.50, 0.50, 0.000001, 'Zony/Jakria Likgbo', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(60, 1, 'c69', 'H3938', NULL, '7595261', '自送', 'Bungee trampoline accessorise', '儿童游乐滑道', '6', 1, 8.000, 1.80, 1.80, 0.10, 0.000000, 'Zony/Jakria Likgbo', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 5600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(61, 1, 'c70', 'H3939', NULL, '7595261', '自送', 'Bungee trampoline accessorise', '儿童游乐滑道', '6', 1, 8.000, 1.80, 1.80, 0.10, 0.000000, 'Zony/Jakria Likgbo', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 5600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(62, 1, 'c71', 'H3940', NULL, '7595261', '自送', 'Bungee trampoline accessorise', '儿童游乐滑道', '6', 1, 8.000, 1.80, 1.80, 0.10, 0.000000, 'Zony/Jakria Likgbo', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 5600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(63, 1, 'c72', 'H3941', NULL, '7595261', '自送', 'Bungee trampoline accessorise', '儿童游乐滑道', '6', 1, 8.000, 1.80, 1.80, 0.10, 0.000000, 'Zony/Jakria Likgbo', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 5600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(64, 1, 'c73', 'H4129', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(65, 1, 'c74', 'H4130', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(66, 1, 'c75', 'H4131', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(67, 1, 'c76', 'H3729', NULL, NULL, '4001194301116', 'plastic boxes', '塑料盒', '5', 0, 6.000, 50.00, 35.00, 28.00, 0.049000, 'JP/Riyad', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(68, 1, 'c77', 'H3730', NULL, NULL, '4001194301116', 'plastic boxes', '塑料盒', '5', 0, 6.000, 50.00, 35.00, 28.00, 0.049000, 'JP/Riyad', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(69, 1, 'c78', 'H3731', NULL, NULL, '4001194301116', 'plastic boxes', '塑料盒', '5', 0, 6.000, 50.00, 35.00, 28.00, 0.049000, 'JP/Riyad', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(70, 1, 'c79', 'H3732', NULL, NULL, '4001194301116', 'plastic boxes', '塑料盒', '5', 0, 6.000, 50.00, 35.00, 28.00, 0.049000, 'JP/Riyad', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(71, 1, 'c80', 'H3733', NULL, NULL, '4001194301116', 'plastic boxes', '塑料盒', '5', 0, 6.000, 50.00, 35.00, 28.00, 0.049000, 'JP/Riyad', NULL, 0, NULL, '2026-10-06', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(72, 1, 'c81', 'H3788', NULL, NULL, '130009285433', 'desk light', '台灯', '1', 10, 3.600, 35.00, 35.00, 35.00, 0.042875, 'Chinacart', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 2520.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(73, 1, 'c82', 'H3792', NULL, NULL, '435386213496731', 'phone case', '手机壳', '1', 0, 1.000, 20.00, 14.00, 12.00, 0.003360, 'Ladeh', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(74, 1, 'c83', 'H3793', NULL, NULL, '79037807351728', 'jewelry organizer', '首饰盒', '1', 0, 2.000, 25.00, 15.00, 13.00, 0.004875, 'Ladeh', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(75, 1, 'c84', 'H3794', NULL, NULL, '3179944306611', 'lipstick', '唇彩', '1', 0, 1.000, 25.00, 15.00, 12.00, 0.004500, 'Ladeh', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(76, 1, 'c85', 'H3795', NULL, NULL, '79037766470723', 'rings', '戒指', '1', 0, 1.000, 12.00, 10.00, 8.00, 0.000960, 'Ladeh', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(77, 1, 'c86', 'H3796', NULL, NULL, '79037809327492', 'rings', '戒指', '1', 0, 1.000, 12.00, 10.00, 8.00, 0.000960, 'Ladeh', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(78, 1, 'c87', 'H3797', NULL, NULL, '79037776290586', '戒指', 'rings', '1', 3, 1.000, 15.00, 12.00, 3.00, 0.000540, 'LADEH', NULL, 0, NULL, '2026-10-08', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(79, 1, 'c88', 'H3799', NULL, NULL, '435386345500565', 'shoes', '鞋子', '1', 0, 4.200, 30.00, 26.00, 20.00, 0.015600, 'Chinacart', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 2940.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(80, 1, 'c89', 'H3800', NULL, NULL, '435386289748213', 'stationary box', '文具盒', '1', 68, 5.000, 68.00, 46.00, 43.00, 0.134504, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 3500.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(81, 1, 'c90', 'H3801', NULL, NULL, '79037770663705', 'bath towel', '搓澡巾', '1', 0, 4.000, 45.00, 36.00, 19.00, 0.030780, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 2800.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(82, 1, 'c91', 'H3802', NULL, NULL, '773445263095732', 'toothbrush', '牙刷', '1', 40, 6.000, 40.00, 27.00, 22.00, 0.023760, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(83, 1, 'c92', 'H3804', NULL, NULL, '435386272645131', 'toothbrush', '牙刷', '1', 37, 3.000, 42.00, 18.00, 20.00, 0.015120, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 2100.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(84, 1, 'c93', 'H3806', NULL, NULL, '764824470300', 'makeup bag', '化妆包', '1', 8, 2.000, 30.00, 22.00, 20.00, 0.013200, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(85, 1, 'c94', 'H3807', NULL, NULL, '3180033307202', 'wallets', '钱包', '1', 0, 2.000, 30.00, 22.00, 20.00, 0.013200, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(86, 1, 'c95', 'H3809', NULL, NULL, '79037798430958', 'cap', '帽子', '1', 0, 1.000, 30.00, 20.00, 20.00, 0.012000, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(87, 1, 'c96', 'H3811', NULL, NULL, '7648249036105', 'earstuds', '耳钉', '1', 0, 1.000, 23.00, 12.00, 13.00, 0.003588, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(88, 1, 'c97', 'H3815', NULL, NULL, '435386918625116', 'phone case', '手机壳', '1', 10, 1.000, 18.00, 12.00, 10.00, 0.002160, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(89, 1, 'c98', 'H3816', NULL, NULL, '79037776990910', 'hair bands', '发带', '1', 37, 1.500, 20.00, 20.00, 10.00, 0.004000, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1050.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(90, 1, 'c99', 'H3817', NULL, NULL, '79037774856012', 'rings', '戒指', '1', 0, 1.000, 20.00, 15.00, 10.00, 0.003000, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(91, 1, 'c100', 'H3818', NULL, NULL, '773445159156736', 'brooch', '胸针', '1', 0, 1.000, 18.00, 12.00, 10.00, 0.002160, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(92, 1, 'c101', 'H3819', NULL, NULL, '79037945829915', 'rings', '戒指', '1', 3, 1.000, 15.00, 12.00, 3.00, 0.000540, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(93, 1, 'c102', 'H3820', NULL, NULL, '435386333916978', 'earstuds', '耳钉', '1', 18, 1.000, 12.00, 10.00, 8.00, 0.000960, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(94, 1, 'c103', 'H4326', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(95, 1, 'c104', 'H4327', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(96, 1, 'c105', 'H4328', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(97, 1, 'c106', 'H4329', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(98, 1, 'c107', 'H4330', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(99, 1, 'c108', 'H4331', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(100, 1, 'c109', 'H4332', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(101, 1, 'c110', 'H4333', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(102, 1, 'c111', 'H4334', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(103, 1, 'c112', 'H4335', NULL, NULL, '61013065444', 'hand mask', '手霜', '10', 600, 34.200, 0.00, 0.00, 0.00, 0.115000, 'STAR/SABAR', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 23940.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(104, 1, 'c113', 'H3821', NULL, NULL, '71448566458', 'shoes', '鞋子', '3', 29, 18.000, 66.00, 55.00, 46.00, 0.166980, 'Fast Import', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 12600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(105, 1, 'c114', 'H3822', NULL, NULL, '71448566458', 'shoes', '鞋子', '3', 29, 18.000, 66.00, 55.00, 46.00, 0.166980, 'Fast Import', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 12600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(106, 1, 'c115', 'H3823', NULL, NULL, '71448566458', 'shoes', '鞋子', '3', 29, 18.000, 66.00, 55.00, 46.00, 0.166980, 'Fast Import', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 12600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(107, 1, 'c116', 'H3824', NULL, NULL, '0215589359838', 'industrial computer', '工业电脑', '1', 1, 10.200, 67.00, 55.00, 46.00, 0.169510, 'Tiger/nayeam', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 7140.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(108, 1, 'c117', 'H4132', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(109, 1, 'c118', 'H4133', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(110, 1, 'c119', 'H4134', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(111, 1, 'c120', 'H3825', NULL, NULL, '188148729369', 'shoes', '鞋子', '1', 12, 10.000, 68.00, 37.00, 35.00, 0.088060, 'Rony/Nadim88432', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 7000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(112, 1, 'c121', 'H3826', NULL, '7855937', '自送', 'HP cartridge', '惠普硒鼓', '1', 20, 19.000, 48.00, 45.00, 40.00, 0.086400, 'MTL/Antor', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(113, 1, 'c122', 'H3828', NULL, NULL, '2433159910', 'laser light', '激光灯', '1', 0, 13.000, 44.00, 38.00, 31.00, 0.051832, 'OMAR', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 9100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(114, 1, 'c123', 'H4257', NULL, '7595266', '自送', 'LCD', '手机屏', '21', 264, 28.200, 59.00, 45.00, 36.00, 0.095580, 'RGT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 56400.00, 2000.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(115, 1, 'c124', 'H4258', NULL, '7595266', '自送', 'LCD', '手机屏', '21', 582, 35.300, 59.00, 45.00, 36.00, 0.095580, 'RGT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 70600.00, 2000.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(116, 1, 'c125', 'H4296', NULL, '送7595268', '自送', 'garments button', '纽扣', '12', 20440, 12.000, 36.00, 25.00, 15.00, 0.013500, 'EHL-SLR-BT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 8400.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(117, 1, 'c126', 'H4297', NULL, '送7595268', '自送', 'garments button', '纽扣', '12', 20440, 16.000, 36.00, 25.00, 15.00, 0.013500, 'EHL-SLR-BT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 11200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(118, 1, 'c127', 'H4298', NULL, '送7595268', '自送', 'garments button', '纽扣', '12', 31200, 14.600, 40.00, 20.00, 20.00, 0.016000, 'EHL-SLR-BT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 10220.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(119, 1, 'c128', 'H4299', NULL, '送7595268', '自送', 'garments button', '纽扣', '12', 40000, 19.000, 40.00, 20.00, 20.00, 0.016000, 'EHL-SLR-BT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(120, 1, 'c129', 'H4300', NULL, '送7595268', '自送', 'garments button', '纽扣', '12', 31360, 21.400, 40.00, 20.00, 20.00, 0.016000, 'EHL-SLR-BT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14980.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(121, 1, 'c130', 'H3829', NULL, NULL, '113433583625', 'bracelets shelf', '手镯架', '1', 0, 9.800, 52.00, 43.00, 42.00, 0.093912, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 6860.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(122, 1, 'c131', 'H3830', NULL, NULL, '302353235386', 'bags', '包', '1', 20, 6.800, 40.00, 31.00, 28.00, 0.034720, 'Chinacart', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 4760.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(123, 1, 'c132', 'H3831', NULL, NULL, '302354632087', 'toothbrush shelf', '牙刷置物架', '1', 0, 7.000, 70.00, 47.00, 29.00, 0.095410, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 4900.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(124, 1, 'c133', 'H4135', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(125, 1, 'c134', 'H4136', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(126, 1, 'c135', 'H4137', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(127, 1, 'c136', 'H3832', NULL, NULL, '7648327052751', 'pen shelf', '笔架', '1', 38, 2.500, 40.00, 30.00, 20.00, 0.024000, 'Chinacart', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1750.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(128, 1, 'c137', 'H3835', NULL, NULL, '7648340097834', 'cup', '杯子', '1', 25, 2.000, 35.00, 25.00, 20.00, 0.017500, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(129, 1, 'c138', 'H3839', NULL, NULL, '79037991693477', 'iron clips', '铁夹', '1', 0, 1.000, 30.00, 12.00, 10.00, 0.003600, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(130, 1, 'c139', 'H3840', NULL, NULL, '79037807684234', 'bags', '包', '1', 1, 1.000, 30.00, 22.00, 11.00, 0.007260, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(131, 1, 'c140', 'H3841', NULL, NULL, '7648460802370', 'phoen case', '手机壳', '1', 13, 1.500, 20.00, 16.00, 12.00, 0.003840, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1050.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(132, 1, 'c141', 'H3843', NULL, NULL, '7648238735271', 'earrings', '耳环', '1', 56, 1.000, 17.00, 12.00, 10.00, 0.002040, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(133, 1, 'c142', 'H3844', NULL, NULL, '79038061840984', 'phone case', '手机壳', '1', 0, 1.000, 20.00, 12.00, 10.00, 0.002400, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(134, 1, 'c143', 'H3845', NULL, NULL, '648431282447', 'stainless tableware', '无锈钢餐具', '1', 0, 1.000, 30.00, 10.00, 3.00, 0.000900, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(135, 1, 'c144', 'H3846', NULL, NULL, '3180039441632', 'rings', '戒指', '1', 0, 1.000, 12.00, 10.00, 3.00, 0.000360, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(136, 1, 'c145', 'H3847', NULL, NULL, '79037917500384', 'earstuds', '耳钉', '1', 4, 1.000, 10.00, 6.00, 4.00, 0.000240, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(137, 1, 'c146', 'H3848', NULL, NULL, '7648253621953', 'rings', '戒指', '1', 36, 1.000, 10.00, 8.00, 4.00, 0.000320, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(138, 1, 'c147', 'H3850', NULL, NULL, '79037977918970', 'hair clips', '发卡', '1', 19, 1.000, 30.00, 20.00, 12.00, 0.007200, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(139, 1, 'c148', 'H3851', NULL, NULL, '79037870457661', 'hair clips', '发夹', '1', 16, 1.000, 18.00, 12.00, 10.00, 0.002160, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(140, 1, 'c149', 'H3852', NULL, NULL, '79038001442180', 'hair clips', '发夹', '1', 16, 1.000, 18.00, 12.00, 10.00, 0.002160, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(141, 1, 'c150', 'H3854', NULL, NULL, '79038040827988', 'bracelets', '手镯', '1', 25, 1.000, 15.00, 10.00, 8.00, 0.001200, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(142, 1, 'c151', 'H3855', NULL, NULL, '79038029582963', 'earstuds', '耳环', '1', 5, 1.000, 12.00, 10.00, 10.00, 0.001200, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(143, 1, 'c152', 'H3856', NULL, NULL, '79037788206916', 'earstuds', '耳钉', '1', 11, 1.000, 10.00, 8.00, 6.00, 0.000480, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(144, 1, 'c153', 'H3857', NULL, NULL, '7648430259623', 'hair clips', '发夹', '1', 0, 1.000, 12.00, 10.00, 5.00, 0.000600, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(145, 1, 'c154', 'H3833', NULL, NULL, '130009298578', 'Led light', '灯', '2', 0, 8.800, 65.00, 36.00, 26.00, 0.060840, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 6160.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(146, 1, 'c155', 'H3834', NULL, NULL, '130009298578', 'Led light', '灯', '2', 0, 7.000, 87.00, 47.00, 37.00, 0.151293, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 4900.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(147, 1, 'c156', 'H4138', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(148, 1, 'c157', 'H4139', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(149, 1, 'c158', 'H4140', NULL, NULL, '自送', 'embroidered scarves', '绣花围巾', '128', 100, 44.600, 56.00, 43.00, 74.00, 0.178192, 'TGL-EHL-SLR-EBC', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 32112.00, 720.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(150, 1, 'c159', 'H3860', NULL, NULL, '7648312568067', 'hair clips', '发夹', '1', 22, 1.000, 23.00, 14.00, 13.00, 0.004186, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(151, 1, 'c160', 'H3862', NULL, NULL, '7648365337289', 'baseball cap', '棒球帽', '1', 60, 4.500, 50.00, 40.00, 22.00, 0.044000, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 3150.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(152, 1, 'c161', 'H3863', NULL, NULL, '773445212294205', 'oil drain clip', '沥油夹', '1', 33, 2.000, 40.00, 25.00, 22.00, 0.022000, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(153, 1, 'c162', 'H3864', NULL, NULL, '79038029326949', 'scarf', '围巾', '1', 10, 3.000, 35.00, 30.00, 18.00, 0.018900, 'Ladeh', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 2100.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(154, 1, 'c163', 'H3911', NULL, NULL, '7648343739933', 'shoes', '鞋子', '1', 11, 2.500, 40.00, 40.00, 30.00, 0.048000, 'LADEH', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 1750.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(155, 1, 'c164', 'H3912', NULL, NULL, '773445290886699', 'bags', '女包', '1', 12, 2.000, 4.00, 33.00, 20.00, 0.002640, 'Ladeh', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 1400.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(156, 1, 'c165', 'H3921', NULL, NULL, '773445260627715', 'bags', '包包', '1', 18, 3.000, 41.00, 34.00, 30.00, 0.041820, 'Chinacart', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 2100.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(157, 1, 'c166', 'H3922', NULL, NULL, '79037901724685', 'shoes', '鞋子', '1', 3, 1.000, 30.00, 30.00, 16.00, 0.014400, 'Ladeh', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 700.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(158, 1, 'c167', 'H3923', NULL, NULL, '79038292087483', 'hair bands', '发圈', '1', 65, 4.000, 43.00, 27.00, 20.00, 0.023220, 'Ladeh', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 2800.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(159, 1, 'c168', 'H3924', NULL, NULL, '773445209446818', 'perfume bottles', '香水瓶', '1', 0, 3.000, 30.00, 22.00, 18.00, 0.011880, 'Ladeh', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 2100.00, 700.00, 5, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:32:09', NULL, NULL, NULL, 0),
(160, 1, 'c169', 'H3865', NULL, NULL, '0075697675', 'charger', '充电头', '2', 320, 23.000, 65.00, 47.00, 36.00, 0.109980, 'NZ/Milon enterprise', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 16100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(161, 1, 'c170', 'H3866', NULL, NULL, '0075697675', 'charger', '充电头', '2', 30, 4.000, 42.00, 33.00, 17.00, 0.023562, 'NZ/Milon enterprise', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 2800.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(162, 1, 'c171', 'H3867', NULL, NULL, '773445490209209', 'pendant light', '吊灯', '1', 0, 9.000, 61.00, 49.00, 49.00, 0.146461, '穆', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 6300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(163, 1, 'c172', 'H3868', NULL, NULL, '773445491613804', 'pendant light', '吊灯', '1', 0, 9.000, 61.00, 49.00, 49.00, 0.146461, '穆', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 6300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(164, 1, 'c173', 'H3870', NULL, '7855938', '自送', 'bags', '包', '6', 60, 28.000, 75.00, 66.00, 50.00, 0.247500, 'SHR/Point/Babu', NULL, 1, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 19600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(165, 1, 'c174', 'H3871', NULL, '7855938', '自送', 'keychain', '钥匙扣', '6', 600, 23.000, 77.00, 35.00, 32.00, 0.086240, 'SHR/Point/Babu', NULL, 0, NULL, '2026-10-09', 'arrived_bd', NULL, NULL, NULL, 16100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(166, 1, 'c175', 'H4259', NULL, '7595266', '自送', 'LCD', '手机屏', '21', 576, 37.400, 59.00, 45.00, 36.00, 0.095580, 'RGT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 74800.00, 2000.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(167, 1, 'c176', 'H4260', NULL, '7595266', '自送', 'LCD', '手机屏', '21', 576, 37.900, 59.00, 45.00, 36.00, 0.095580, 'RGT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 75800.00, 2000.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(168, 1, 'c177', 'H3929', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 800, 31.200, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 21840.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0);
INSERT INTO `cargo_items` (`id`, `goods_received`, `printed_ctn_no`, `written_ctn_no`, `box_no`, `receipt_no`, `tracking_no`, `goods_name_en`, `goods_name_local`, `pack_no`, `unit_pcs`, `weight_kg`, `length_cm`, `width_cm`, `height_cm`, `cbm`, `shipping_mark`, `remark`, `is_copy`, `warehouse`, `date`, `status`, `cancelled_at`, `cancel_reason`, `status_before_cancel`, `price`, `price_per_kg`, `customer_id`, `third_party_id`, `paid_at`, `created_at`, `updated_at`, `delivered_at`, `receiver_name`, `receiver_phone`, `is_third_party`) VALUES
(169, 1, 'c178', 'H3930', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 700, 33.200, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 23240.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(170, 1, 'c179', 'H3931', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 550, 33.200, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 23240.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(171, 1, 'c180', 'H3932', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 700, 25.000, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 17500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(172, 1, 'c181', 'H3933', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 600, 30.200, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 21140.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(173, 1, 'c182', 'H3934', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 500, 30.000, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 21000.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(174, 1, 'c183', 'H3935', NULL, '7855920', '自送', 'beads string', '窜珠绳', '7', 550, 31.000, 44.00, 35.00, 33.00, 0.050820, 'TGL/EHL SLR WB', NULL, 0, NULL, '2026-10-10', 'arrived_bd', NULL, NULL, NULL, 21700.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(175, 1, 'c184', 'H1280', NULL, '3998623', '自送', 'empty cosmetics bottle caps', '空化妆瓶盖子', '14', 2500, 12.400, 70.00, 46.00, 30.00, 0.096600, 'JP/AD', NULL, 0, NULL, '2026-09-01', 'arrived_bd', NULL, NULL, NULL, 8680.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(176, 1, 'c185', 'H1281', NULL, '3998623', '自送', 'empty cosmetics bottle caps', '空化妆瓶盖子', '15', 2500, 12.400, 70.00, 46.00, 30.00, 0.096600, 'JP/AD', NULL, 0, NULL, '2026-09-01', 'arrived_bd', NULL, NULL, NULL, 8680.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(177, 1, 'c186', 'H3971', NULL, NULL, '7649032622965', 'audio box cover', '音箱保护套', '1', 18, 6.000, 37.00, 30.00, 21.00, 0.023310, 'CPL/Hangkai Tech', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 4200.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(178, 1, 'c187', 'H4071', NULL, '7595264', '自送', 'Soap Dispenser', '皂液器', '3', 50, 21.000, 55.00, 50.00, 50.00, 0.137500, 'JP/SD', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 14700.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(179, 1, 'c188', 'H4072', NULL, '7595264', '自送', 'Soap Dispenser', '皂液器', '3', 50, 21.000, 55.00, 50.00, 50.00, 0.137500, 'JP/SD', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 14700.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(180, 1, 'c189', 'H4073', NULL, '7595264', '自送', 'Soap Dispenser', '皂液器', '3', 50, 21.000, 55.00, 50.00, 50.00, 0.137500, 'JP/SD', NULL, 0, NULL, '2026-10-12', 'arrived_bd', NULL, NULL, NULL, 14700.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(181, 1, 'c190', 'H4236', NULL, NULL, '188148729425', 'shoes', '鞋子', '2', 0, 19.000, 78.00, 42.00, 35.00, 0.114660, 'Rony/Nadim88432', NULL, 1, NULL, '2026-10-13', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(182, 1, 'c191', 'H4237', NULL, NULL, '188148729425', 'shoes', '鞋子', '2', 0, 19.000, 78.00, 42.00, 35.00, 0.114660, 'Rony/Nadim88432', NULL, 1, NULL, '2026-10-13', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(183, 1, 'c192', 'H4261', NULL, '7595266', '自送', 'LCD', '手机屏', '21', 570, 36.600, 59.00, 45.00, 36.00, 0.095580, 'RGT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 73200.00, 2000.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(184, 1, 'c193', 'H4262', NULL, '7595266', '自送', 'LCD', '手机屏', '21', 576, 35.600, 59.00, 45.00, 36.00, 0.095580, 'RGT', NULL, 0, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 71200.00, 2000.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(185, 1, 'c194', 'H4276', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(186, 1, 'c195', 'H4277', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(187, 1, 'c196', 'H4278', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(188, 1, 'c197', 'H4279', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(189, 1, 'c198', 'H4280', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(190, 1, 'c199', 'H4281', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(191, 1, 'c200', 'H4282', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(192, 1, 'c201', 'H4283', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(193, 1, 'c202', 'H4284', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(194, 1, 'c203', 'H4285', NULL, '7595267', '自送', 'body cream color 1', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 13300.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(195, 1, 'c204', 'H4286', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(196, 1, 'c205', 'H4287', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(197, 1, 'c206', 'H4288', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(198, 1, 'c207', 'H4289', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(199, 1, 'c208', 'H4290', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(200, 1, 'c209', 'H4291', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(201, 1, 'c210', 'H4292', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(202, 1, 'c211', 'H4293', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(203, 1, 'c212', 'H4294', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(204, 1, 'c213', 'H4295', NULL, '7595267', '自送', 'body cream color 2', '身体乳', '20', 48, 19.000, 59.00, 33.00, 28.00, 0.054516, 'Star/Sabar', NULL, 1, NULL, '2026-10-14', 'arrived_bd', NULL, NULL, NULL, 14250.00, 750.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(205, 1, 'c214', 'H4314', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 31, 5.500, 0.00, 0.00, 0.00, 0.040000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 3850.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(206, 1, 'c215', 'H4315', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 101, 11.500, 0.00, 0.00, 0.00, 0.030000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 8050.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(207, 1, 'c216', 'H4316', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 4, 15.000, 0.00, 0.00, 0.00, 0.110000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(208, 1, 'c217', 'H4317', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 4, 15.000, 0.00, 0.00, 0.00, 0.110000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(209, 1, 'c218', 'H4318', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 24, 37.000, 0.00, 0.00, 0.00, 0.180000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 25900.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(210, 1, 'c219', 'H4319', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 24, 37.000, 0.00, 0.00, 0.00, 0.180000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 25900.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(211, 1, 'c220', 'H4320', NULL, NULL, '4001189350350', 'door sensor', '门传感器', '7', 12, 6.500, 0.00, 0.00, 0.00, 0.040000, 'TGL/TTB', NULL, 0, NULL, '2026-10-15', 'arrived_bd', NULL, NULL, NULL, 4550.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:48:53', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(212, 0, 'c221', NULL, NULL, NULL, 'JHGVHGVHV-1', 'Ceramic vase three-piece set', '陶瓷花瓶三件套', '0', 8, 13.400, 0.00, 0.00, 0.00, 0.186048, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9380.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(213, 0, 'c222', NULL, NULL, NULL, 'JHGVHGVHV-2', 'Ceramic vase three-piece set', '陶瓷花瓶三件套', '0', 8, 13.500, 0.00, 0.00, 0.00, 0.186048, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9450.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(214, 0, 'c223', NULL, NULL, NULL, 'JHGVHGVHV-3', 'Ceramic vase three-piece set', '陶瓷花瓶三件套', '0', 8, 13.100, 0.00, 0.00, 0.00, 0.186048, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9170.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(215, 0, 'c224', NULL, NULL, NULL, 'JHGVHGVHV-4', 'Ceramic vase three-piece set', '陶瓷花瓶三件套', '0', 8, 13.000, 0.00, 0.00, 0.00, 0.186048, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9100.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(216, 0, 'c225', NULL, NULL, NULL, 'JHGVHGVHV-5', 'desk lamp', '台灯', '0', 4, 21.700, 0.00, 0.00, 0.00, 0.264992, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 15190.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(217, 0, 'c226', NULL, NULL, NULL, 'JHGVHGVHV-6', 'desk lamp', '台灯', '0', 4, 21.500, 0.00, 0.00, 0.00, 0.264992, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 15050.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(218, 0, 'c227', NULL, NULL, NULL, 'JHGVHGVHV-7', 'desk lamp', '台灯', '0', 4, 21.700, 0.00, 0.00, 0.00, 0.264992, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 15190.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(219, 0, 'c228', NULL, NULL, NULL, 'JHGVHGVHV-8', 'desk lamp', '台灯', '0', 4, 21.700, 0.00, 0.00, 0.00, 0.264992, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 15190.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(220, 0, 'c229', NULL, NULL, NULL, 'JHGVHGVHV-9', 'desk lamp', '台灯', '0', 3, 20.400, 0.00, 0.00, 0.00, 0.241744, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 14280.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(221, 0, 'c230', NULL, NULL, NULL, 'JHGVHGVHV-10', 'mobile phone back cover', '手机后盖', '0', 120, 4.700, 0.00, 0.00, 0.00, 0.066000, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 3290.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(222, 0, 'c231', NULL, NULL, NULL, 'JHGVHGVHV-11', 'gear pump', '齿轮泵', '0', 1, 12.800, 0.00, 0.00, 0.00, 0.023805, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 8960.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(223, 0, 'c232', NULL, NULL, NULL, 'JHGVHGVHV-12', 'plastic cap', '塑料帽盖', '0', 60362, 30.100, 0.00, 0.00, 0.00, 0.200704, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 21070.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(224, 0, 'c233', NULL, NULL, NULL, 'JHGVHGVHV-13', 'plastic cap', '塑料帽盖', '0', 60362, 30.100, 0.00, 0.00, 0.00, 0.200704, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 21070.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(225, 0, 'c234', NULL, NULL, NULL, 'JHGVHGVHV-14', 'plastic cap', '塑料帽盖', '0', 60362, 30.100, 0.00, 0.00, 0.00, 0.200704, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 21070.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(226, 0, 'c235', NULL, NULL, NULL, 'JHGVHGVHV-15', 'plastic cap', '塑料帽盖', '0', 60362, 30.100, 0.00, 0.00, 0.00, 0.200704, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 21070.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(227, 0, 'c236', NULL, NULL, NULL, 'JHGVHGVHV-16', 'Clothes, shoes, bags, building blocks, toys, beauty tools', '衣服 鞋子包包 积木玩具 美妆工具', '0', 50, 27.400, 0.00, 0.00, 0.00, 0.219356, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 19180.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(228, 0, 'c237', NULL, NULL, NULL, 'JHGVHGVHV-17', 'Pillowcases and table runners', '抱枕套 桌旗', '0', 180, 42.000, 0.00, 0.00, 0.00, 0.254240, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 29400.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(229, 0, 'c238', NULL, NULL, NULL, 'JHGVHGVHV-18', 'Throw pillowcase', '抱枕套', '0', 150, 27.000, 0.00, 0.00, 0.00, 0.299280, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 18900.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(230, 0, 'c239', NULL, NULL, NULL, 'JHGVHGVHV-19', 'Apple iPhone back cover (copy)', '苹果手机后盖（副本）', '0', 577, 18.100, 0.00, 0.00, 0.00, 0.092598, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 12670.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(231, 0, 'c240', NULL, NULL, NULL, 'JHGVHGVHV-20', 'mobile phone back cover', '手机后盖', '0', 300, 5.000, 0.00, 0.00, 0.00, 0.037950, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 3500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(232, 0, 'c241', NULL, NULL, NULL, 'JHGVHGVHV-21', 'Acrylic square box', '亚克力方盒', '0', 2, 12.900, 0.00, 0.00, 0.00, 0.178080, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9030.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(233, 0, 'c242', NULL, NULL, NULL, 'JHGVHGVHV-22', 'Clothes hangers, trouser hangers, gold bag racks', '衣架 裤架 金色包架', '0', 120, 27.800, 0.00, 0.00, 0.00, 0.079980, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 19460.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(234, 0, 'c243', NULL, NULL, NULL, 'JHGVHGVHV-23', 'Acrylic trouser hanger', '亚克力裤架', '0', 50, 11.800, 0.00, 0.00, 0.00, 0.054945, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 8260.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(235, 0, 'c244', NULL, NULL, NULL, 'JHGVHGVHV-24', 'Flat model, female model', '扁模 女模', '0', 3, 20.300, 0.00, 0.00, 0.00, 0.181632, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 14210.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(236, 0, 'c245', NULL, NULL, NULL, 'JHGVHGVHV-25', 'Cloth-covered, handless female half-body', '包布无手女半身', '0', 2, 15.800, 0.00, 0.00, 0.00, 0.167670, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 11060.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(237, 0, 'c246', NULL, NULL, NULL, 'JHGVHGVHV-26', 'Acrylic brand gold women\'s handbag with curved hand and half-face design', '亚克力牌 金色女包布弯手半脸手', '0', 9, 15.600, 0.00, 0.00, 0.00, 0.132132, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 10920.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(238, 0, 'c247', NULL, NULL, NULL, 'JHGVHGVHV-27', 'Gold double-sided hanging cloth women\'s half-body dress', '金色双面挂 包布女半身', '0', 2, 11.100, 0.00, 0.00, 0.00, 0.134160, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 7770.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(239, 0, 'c248', NULL, NULL, NULL, 'JHGVHGVHV-28', 'child model', '童模', '0', 2, 11.300, 0.00, 0.00, 0.00, 0.097920, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 7910.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(240, 0, 'c249', NULL, NULL, NULL, 'JHGVHGVHV-29', 'Round golden bar', '圆球金色单杠', '0', 3, 30.300, 0.00, 0.00, 0.00, 0.479196, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 21210.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(241, 0, 'c250', NULL, NULL, NULL, 'JHGVHGVHV-30', 'Round golden bar', '圆球金色单杠', '0', 2, 19.700, 0.00, 0.00, 0.00, 0.312048, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 13790.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 0),
(242, 0, 'c251', NULL, NULL, NULL, 'JHGVHGVHV-31', 'Golden Square Column Single Bar', '金色方柱单杠', '0', 2, 12.800, 0.00, 0.00, 0.00, 0.221445, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 8960.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(243, 0, 'c252', NULL, NULL, NULL, 'JHGVHGVHV-32', 'Electricity meter seal', '电表封条', '0', 5000, 15.000, 0.00, 0.00, 0.00, 0.054567, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 10500.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(244, 0, 'c253', NULL, NULL, NULL, 'JHGVHGVHV-33', 'Electricity meter seal', '电表封条', '0', 5000, 14.100, 0.00, 0.00, 0.00, 0.054567, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9870.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(245, 0, 'c254', NULL, NULL, NULL, 'JHGVHGVHV-34', 'Electricity meter seal', '电表封条', '0', 5000, 13.900, 0.00, 0.00, 0.00, 0.054567, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 9730.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(246, 0, 'c255', NULL, NULL, NULL, 'JHGVHGVHV-35', 'door lock set', '门锁套装', '0', 10, 18.000, 0.00, 0.00, 0.00, 0.044616, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 12600.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(247, 0, 'c256', NULL, NULL, NULL, 'JHGVHGVHV-36', 'mobile phone back cover', '手机后盖', '0', 320, 7.700, 0.00, 0.00, 0.00, 0.093840, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 5390.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(248, 0, 'c257', NULL, NULL, NULL, 'JHGVHGVHV-37', 'mobile phone back cover', '手机后盖', '0', 202, 5.400, 0.00, 0.00, 0.00, 0.060000, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 3780.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(249, 0, 'c258', NULL, NULL, NULL, 'JHGVHGVHV-38', 'Cardboard box slitting knife', '纸箱分切刀', '0', 34, 11.900, 0.00, 0.00, 0.00, 0.065664, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 8330.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(250, 0, 'c259', NULL, NULL, NULL, 'JHGVHGVHV-39', 'mobile phone back cover', '手机后盖', '0', 50, 1.600, 0.00, 0.00, 0.00, 0.007220, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 1120.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(251, 0, 'c260', NULL, NULL, NULL, 'JHGVHGVHV-40', 'license plate frame', '牌照框', '0', 2, 3.400, 0.00, 0.00, 0.00, 0.013986, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 2380.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(252, 0, 'c261', NULL, NULL, NULL, 'JHGVHGVHV-41', 'baby bottle', '奶瓶', '0', 1, 0.200, 0.00, 0.00, 0.00, 0.001760, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 140.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(253, 0, 'c262', NULL, NULL, NULL, 'JHGVHGVHV-42', 'car tail light', '汽车尾灯', '0', 2, 3.800, 0.00, 0.00, 0.00, 0.043036, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 2660.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(254, 0, 'c263', NULL, NULL, NULL, 'JHGVHGVHV-43', 'children\'s clothing', '儿童服装', '0', 1, 0.400, 0.00, 0.00, 0.00, 0.003360, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 280.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(255, 0, 'c264', NULL, NULL, NULL, 'JHGVHGVHV-44', 'Intelligent serial port industrial screen', '智能串口工业屏', '0', 1, 0.500, 0.00, 0.00, 0.00, 0.005733, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 350.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(256, 0, 'c265', NULL, NULL, NULL, 'JHGVHGVHV-45', 'flow sensor', '流量传感器', '0', 3, 2.100, 0.00, 0.00, 0.00, 0.015500, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 1470.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(257, 0, 'c266', NULL, NULL, NULL, 'JHGVHGVHV-46', 'Current transformer', '电流互感器', '0', 3, 1.500, 0.00, 0.00, 0.00, 0.003000, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 1050.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(258, 0, 'c267', NULL, NULL, NULL, 'JHGVHGVHV-47', 'mobile phone back cover', '手机后盖', '0', 100, 2.200, 0.00, 0.00, 0.00, 0.015000, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 1540.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(259, 0, 'c268', NULL, NULL, NULL, 'JHGVHGVHV-48', 'fan blade', '风扇叶', '0', 4, 0.500, 0.00, 0.00, 0.00, 0.004500, NULL, 'NONE', 0, NULL, NULL, 'arrived_bd', NULL, NULL, NULL, 350.00, 700.00, NULL, NULL, NULL, '2026-10-25 13:49:52', '2026-10-25 14:23:06', NULL, NULL, NULL, 1),
(260, NULL, '654321`', 'S1234', NULL, '123456', '12345678', 'Mobile Cover', 'Mobile cover', '4', 500, 500.000, NULL, NULL, NULL, NULL, 'sa12345', NULL, 1, 'Chaina', '2026-10-26', 'ready_to_deliver', NULL, NULL, NULL, 100000.00, 200.00, 2, NULL, NULL, '2026-10-26 05:16:36', '2026-10-26 06:25:30', NULL, NULL, NULL, 0),
(261, NULL, '5432', '453s', NULL, '87654', '654321', 'Mouse Pad', 'Mouse Pad', '5', 10, 200.000, NULL, NULL, NULL, NULL, 'sd23456', NULL, 0, 'Chaina', '2026-10-26', 'ready_to_deliver', NULL, NULL, NULL, 20000.00, 100.00, 5, NULL, NULL, '2026-10-26 05:16:36', '2026-10-26 06:25:30', NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cargo_item_payments`
--

CREATE TABLE `cargo_item_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_payment_id` bigint(20) UNSIGNED NOT NULL,
  `cargo_item_id` bigint(20) UNSIGNED NOT NULL,
  `amount_paid` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cargo_item_shipment`
--

CREATE TABLE `cargo_item_shipment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cargo_item_id` bigint(20) UNSIGNED NOT NULL,
  `items_shipment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cargo_item_shipment`
--

INSERT INTO `cargo_item_shipment` (`id`, `cargo_item_id`, `items_shipment_id`, `created_at`, `updated_at`) VALUES
(4, 255, 1, NULL, NULL),
(5, 250, 1, NULL, NULL),
(6, 249, 1, NULL, NULL),
(7, 244, 1, NULL, NULL),
(8, 241, 1, NULL, NULL),
(9, 239, 1, NULL, NULL),
(10, 235, 1, NULL, NULL),
(11, 224, 1, NULL, NULL),
(12, 222, 1, NULL, NULL),
(13, 259, 1, NULL, NULL),
(14, 258, 1, NULL, NULL),
(15, 257, 1, NULL, NULL),
(16, 256, 1, NULL, NULL),
(17, 254, 1, NULL, NULL),
(18, 253, 1, NULL, NULL),
(19, 252, 1, NULL, NULL),
(20, 251, 1, NULL, NULL),
(21, 248, 1, NULL, NULL),
(22, 247, 1, NULL, NULL),
(23, 246, 1, NULL, NULL),
(24, 245, 1, NULL, NULL),
(25, 243, 1, NULL, NULL),
(26, 242, 1, NULL, NULL),
(27, 240, 1, NULL, NULL),
(28, 238, 1, NULL, NULL),
(29, 237, 1, NULL, NULL),
(30, 236, 1, NULL, NULL),
(31, 234, 1, NULL, NULL),
(32, 233, 1, NULL, NULL),
(33, 232, 1, NULL, NULL),
(34, 231, 1, NULL, NULL),
(35, 230, 1, NULL, NULL),
(36, 229, 1, NULL, NULL),
(37, 228, 1, NULL, NULL),
(38, 227, 1, NULL, NULL),
(39, 226, 1, NULL, NULL),
(40, 225, 1, NULL, NULL),
(41, 223, 1, NULL, NULL),
(42, 221, 1, NULL, NULL),
(43, 220, 1, NULL, NULL),
(44, 219, 1, NULL, NULL),
(45, 218, 1, NULL, NULL),
(46, 217, 1, NULL, NULL),
(47, 216, 1, NULL, NULL),
(48, 215, 1, NULL, NULL),
(49, 214, 1, NULL, NULL),
(50, 213, 1, NULL, NULL),
(51, 212, 1, NULL, NULL),
(52, 211, 1, NULL, NULL),
(53, 210, 1, NULL, NULL),
(54, 209, 1, NULL, NULL),
(55, 208, 1, NULL, NULL),
(56, 207, 1, NULL, NULL),
(57, 206, 1, NULL, NULL),
(58, 205, 1, NULL, NULL),
(59, 204, 1, NULL, NULL),
(60, 203, 1, NULL, NULL),
(61, 202, 1, NULL, NULL),
(62, 201, 1, NULL, NULL),
(63, 200, 1, NULL, NULL),
(64, 199, 1, NULL, NULL),
(65, 198, 1, NULL, NULL),
(66, 197, 1, NULL, NULL),
(67, 196, 1, NULL, NULL),
(68, 195, 1, NULL, NULL),
(69, 194, 1, NULL, NULL),
(70, 193, 1, NULL, NULL),
(71, 192, 1, NULL, NULL),
(72, 191, 1, NULL, NULL),
(73, 190, 1, NULL, NULL),
(74, 189, 1, NULL, NULL),
(75, 188, 1, NULL, NULL),
(76, 187, 1, NULL, NULL),
(77, 186, 1, NULL, NULL),
(78, 185, 1, NULL, NULL),
(79, 184, 1, NULL, NULL),
(80, 183, 1, NULL, NULL),
(81, 182, 1, NULL, NULL),
(82, 181, 1, NULL, NULL),
(83, 180, 1, NULL, NULL),
(84, 179, 1, NULL, NULL),
(85, 178, 1, NULL, NULL),
(86, 177, 1, NULL, NULL),
(87, 176, 1, NULL, NULL),
(88, 175, 1, NULL, NULL),
(89, 174, 1, NULL, NULL),
(90, 173, 1, NULL, NULL),
(91, 172, 1, NULL, NULL),
(92, 171, 1, NULL, NULL),
(93, 170, 1, NULL, NULL),
(94, 169, 1, NULL, NULL),
(95, 168, 1, NULL, NULL),
(96, 167, 1, NULL, NULL),
(97, 166, 1, NULL, NULL),
(98, 165, 1, NULL, NULL),
(99, 164, 1, NULL, NULL),
(100, 163, 1, NULL, NULL),
(101, 162, 1, NULL, NULL),
(102, 161, 1, NULL, NULL),
(103, 160, 1, NULL, NULL),
(104, 159, 1, NULL, NULL),
(105, 158, 1, NULL, NULL),
(106, 157, 1, NULL, NULL),
(107, 156, 1, NULL, NULL),
(108, 155, 1, NULL, NULL),
(109, 154, 1, NULL, NULL),
(110, 153, 1, NULL, NULL),
(111, 152, 1, NULL, NULL),
(112, 151, 1, NULL, NULL),
(113, 150, 1, NULL, NULL),
(114, 149, 1, NULL, NULL),
(115, 148, 1, NULL, NULL),
(116, 147, 1, NULL, NULL),
(117, 146, 1, NULL, NULL),
(118, 145, 1, NULL, NULL),
(119, 144, 1, NULL, NULL),
(120, 143, 1, NULL, NULL),
(121, 142, 1, NULL, NULL),
(122, 141, 1, NULL, NULL),
(123, 140, 1, NULL, NULL),
(124, 139, 1, NULL, NULL),
(125, 138, 1, NULL, NULL),
(126, 137, 1, NULL, NULL),
(127, 136, 1, NULL, NULL),
(128, 135, 1, NULL, NULL),
(129, 134, 1, NULL, NULL),
(130, 133, 1, NULL, NULL),
(131, 132, 1, NULL, NULL),
(132, 131, 1, NULL, NULL),
(133, 130, 1, NULL, NULL),
(134, 129, 1, NULL, NULL),
(135, 128, 1, NULL, NULL),
(136, 127, 1, NULL, NULL),
(137, 126, 1, NULL, NULL),
(138, 125, 1, NULL, NULL),
(139, 124, 1, NULL, NULL),
(140, 123, 1, NULL, NULL),
(141, 122, 1, NULL, NULL),
(142, 121, 1, NULL, NULL),
(143, 120, 1, NULL, NULL),
(144, 119, 1, NULL, NULL),
(145, 118, 1, NULL, NULL),
(146, 117, 1, NULL, NULL),
(147, 116, 1, NULL, NULL),
(148, 115, 1, NULL, NULL),
(149, 114, 1, NULL, NULL),
(150, 113, 1, NULL, NULL),
(151, 112, 1, NULL, NULL),
(152, 111, 1, NULL, NULL),
(153, 110, 1, NULL, NULL),
(154, 109, 1, NULL, NULL),
(155, 108, 1, NULL, NULL),
(156, 107, 1, NULL, NULL),
(157, 106, 1, NULL, NULL),
(158, 105, 1, NULL, NULL),
(159, 104, 1, NULL, NULL),
(160, 103, 1, NULL, NULL),
(161, 102, 1, NULL, NULL),
(162, 101, 1, NULL, NULL),
(163, 100, 1, NULL, NULL),
(164, 99, 1, NULL, NULL),
(165, 98, 1, NULL, NULL),
(166, 97, 1, NULL, NULL),
(167, 96, 1, NULL, NULL),
(168, 95, 1, NULL, NULL),
(169, 94, 1, NULL, NULL),
(170, 93, 1, NULL, NULL),
(171, 92, 1, NULL, NULL),
(172, 91, 1, NULL, NULL),
(173, 90, 1, NULL, NULL),
(174, 89, 1, NULL, NULL),
(175, 88, 1, NULL, NULL),
(176, 87, 1, NULL, NULL),
(177, 86, 1, NULL, NULL),
(178, 85, 1, NULL, NULL),
(179, 84, 1, NULL, NULL),
(180, 83, 1, NULL, NULL),
(181, 82, 1, NULL, NULL),
(182, 81, 1, NULL, NULL),
(183, 80, 1, NULL, NULL),
(184, 79, 1, NULL, NULL),
(185, 78, 1, NULL, NULL),
(186, 77, 1, NULL, NULL),
(187, 76, 1, NULL, NULL),
(188, 75, 1, NULL, NULL),
(189, 74, 1, NULL, NULL),
(190, 73, 1, NULL, NULL),
(191, 72, 1, NULL, NULL),
(192, 71, 1, NULL, NULL),
(193, 70, 1, NULL, NULL),
(194, 69, 1, NULL, NULL),
(195, 68, 1, NULL, NULL),
(196, 67, 1, NULL, NULL),
(197, 66, 1, NULL, NULL),
(198, 65, 1, NULL, NULL),
(199, 64, 1, NULL, NULL),
(200, 63, 1, NULL, NULL),
(201, 62, 1, NULL, NULL),
(202, 61, 1, NULL, NULL),
(203, 60, 1, NULL, NULL),
(204, 59, 1, NULL, NULL),
(205, 58, 1, NULL, NULL),
(206, 57, 1, NULL, NULL),
(207, 56, 1, NULL, NULL),
(208, 55, 1, NULL, NULL),
(209, 54, 1, NULL, NULL),
(210, 53, 1, NULL, NULL),
(211, 52, 1, NULL, NULL),
(212, 51, 1, NULL, NULL),
(213, 50, 1, NULL, NULL),
(214, 49, 1, NULL, NULL),
(215, 48, 1, NULL, NULL),
(216, 47, 1, NULL, NULL),
(217, 46, 1, NULL, NULL),
(218, 45, 1, NULL, NULL),
(219, 44, 1, NULL, NULL),
(220, 43, 1, NULL, NULL),
(221, 42, 1, NULL, NULL),
(222, 41, 1, NULL, NULL),
(223, 40, 1, NULL, NULL),
(224, 39, 1, NULL, NULL),
(225, 38, 1, NULL, NULL),
(226, 37, 1, NULL, NULL),
(227, 36, 1, NULL, NULL),
(228, 35, 1, NULL, NULL),
(229, 34, 1, NULL, NULL),
(230, 33, 1, NULL, NULL),
(231, 32, 1, NULL, NULL),
(232, 31, 1, NULL, NULL),
(233, 30, 1, NULL, NULL),
(234, 29, 1, NULL, NULL),
(235, 28, 1, NULL, NULL),
(236, 27, 1, NULL, NULL),
(237, 26, 1, NULL, NULL),
(238, 25, 1, NULL, NULL),
(239, 24, 1, NULL, NULL),
(240, 23, 1, NULL, NULL),
(241, 22, 1, NULL, NULL),
(242, 21, 1, NULL, NULL),
(243, 20, 1, NULL, NULL),
(244, 19, 1, NULL, NULL),
(245, 18, 1, NULL, NULL),
(246, 17, 1, NULL, NULL),
(247, 16, 1, NULL, NULL),
(248, 15, 1, NULL, NULL),
(249, 14, 1, NULL, NULL),
(250, 13, 1, NULL, NULL),
(251, 12, 1, NULL, NULL),
(252, 11, 1, NULL, NULL),
(253, 10, 1, NULL, NULL),
(254, 9, 1, NULL, NULL),
(255, 8, 1, NULL, NULL),
(256, 7, 1, NULL, NULL),
(257, 6, 1, NULL, NULL),
(258, 5, 1, NULL, NULL),
(259, 4, 1, NULL, NULL),
(260, 3, 1, NULL, NULL),
(261, 2, 1, NULL, NULL),
(262, 1, 1, NULL, NULL),
(263, 261, 2, NULL, NULL),
(264, 260, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cargo_item_status_logs`
--

CREATE TABLE `cargo_item_status_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cargo_item_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(30) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cargo_item_status_logs`
--

INSERT INTO `cargo_item_status_logs` (`id`, `cargo_item_id`, `status`, `changed_at`, `user_id`) VALUES
(1, 1, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(2, 2, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(3, 3, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(4, 4, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(5, 5, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(6, 6, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(7, 7, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(8, 8, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(9, 9, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(10, 10, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(11, 11, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(12, 12, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(13, 13, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(14, 14, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(15, 15, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(16, 16, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(17, 17, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(18, 18, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(19, 19, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(20, 20, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(21, 21, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(22, 22, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(23, 23, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(24, 24, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(25, 25, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(26, 26, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(27, 27, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(28, 28, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(29, 29, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(30, 30, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(31, 31, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(32, 32, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(33, 33, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(34, 34, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(35, 35, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(36, 36, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(37, 37, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(38, 38, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(39, 39, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(40, 40, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(41, 41, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(42, 42, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(43, 43, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(44, 44, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(45, 45, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(46, 46, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(47, 47, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(48, 48, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(49, 49, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(50, 50, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(51, 51, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(52, 52, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(53, 53, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(54, 54, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(55, 55, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(56, 56, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(57, 57, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(58, 58, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(59, 59, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(60, 60, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(61, 61, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(62, 62, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(63, 63, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(64, 64, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(65, 65, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(66, 66, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(67, 67, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(68, 68, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(69, 69, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(70, 70, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(71, 71, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(72, 72, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(73, 73, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(74, 74, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(75, 75, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(76, 76, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(77, 77, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(78, 78, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(79, 79, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(80, 80, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(81, 81, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(82, 82, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(83, 83, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(84, 84, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(85, 85, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(86, 86, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(87, 87, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(88, 88, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(89, 89, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(90, 90, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(91, 91, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(92, 92, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(93, 93, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(94, 94, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(95, 95, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(96, 96, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(97, 97, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(98, 98, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(99, 99, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(100, 100, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(101, 101, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(102, 102, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(103, 103, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(104, 104, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(105, 105, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(106, 106, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(107, 107, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(108, 108, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(109, 109, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(110, 110, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(111, 111, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(112, 112, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(113, 113, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(114, 114, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(115, 115, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(116, 116, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(117, 117, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(118, 118, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(119, 119, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(120, 120, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(121, 121, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(122, 122, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(123, 123, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(124, 124, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(125, 125, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(126, 126, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(127, 127, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(128, 128, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(129, 129, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(130, 130, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(131, 131, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(132, 132, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(133, 133, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(134, 134, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(135, 135, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(136, 136, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(137, 137, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(138, 138, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(139, 139, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(140, 140, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(141, 141, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(142, 142, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(143, 143, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(144, 144, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(145, 145, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(146, 146, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(147, 147, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(148, 148, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(149, 149, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(150, 150, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(151, 151, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(152, 152, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(153, 153, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(154, 154, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(155, 155, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(156, 156, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(157, 157, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(158, 158, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(159, 159, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(160, 160, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(161, 161, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(162, 162, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(163, 163, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(164, 164, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(165, 165, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(166, 166, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(167, 167, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(168, 168, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(169, 169, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(170, 170, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(171, 171, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(172, 172, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(173, 173, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(174, 174, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(175, 175, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(176, 176, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(177, 177, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(178, 178, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(179, 179, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(180, 180, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(181, 181, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(182, 182, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(183, 183, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(184, 184, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(185, 185, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(186, 186, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(187, 187, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(188, 188, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(189, 189, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(190, 190, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(191, 191, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(192, 192, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(193, 193, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(194, 194, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(195, 195, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(196, 196, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(197, 197, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(198, 198, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(199, 199, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(200, 200, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(201, 201, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(202, 202, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(203, 203, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(204, 204, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(205, 205, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(206, 206, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(207, 207, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(208, 208, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(209, 209, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(210, 210, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(211, 211, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(212, 212, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(213, 213, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(214, 214, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(215, 215, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(216, 216, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(217, 217, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(218, 218, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(219, 219, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(220, 220, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(221, 221, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(222, 222, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(223, 223, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(224, 224, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(225, 225, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(226, 226, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(227, 227, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(228, 228, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(229, 229, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(230, 230, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(231, 231, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(232, 232, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(233, 233, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(234, 234, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(235, 235, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(236, 236, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(237, 237, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(238, 238, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(239, 239, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(240, 240, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(241, 241, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(242, 242, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(243, 243, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(244, 244, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(245, 245, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(246, 246, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(247, 247, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(248, 248, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(249, 249, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(250, 250, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(251, 251, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(252, 252, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(253, 253, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(254, 254, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(255, 255, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(256, 256, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(257, 257, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(258, 258, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(259, 259, 'ready_to_ship', '2026-10-25 14:00:53', 2),
(260, 255, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(261, 250, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(262, 249, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(263, 244, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(264, 241, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(265, 239, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(266, 235, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(267, 224, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(268, 222, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(269, 259, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(270, 258, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(271, 257, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(272, 256, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(273, 254, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(274, 253, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(275, 252, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(276, 251, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(277, 248, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(278, 247, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(279, 246, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(280, 245, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(281, 243, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(282, 242, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(283, 240, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(284, 238, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(285, 237, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(286, 236, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(287, 234, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(288, 233, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(289, 232, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(290, 231, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(291, 230, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(292, 229, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(293, 228, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(294, 227, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(295, 226, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(296, 225, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(297, 223, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(298, 221, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(299, 220, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(300, 219, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(301, 218, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(302, 217, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(303, 216, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(304, 215, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(305, 214, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(306, 213, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(307, 212, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(308, 211, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(309, 210, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(310, 209, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(311, 208, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(312, 207, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(313, 206, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(314, 205, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(315, 204, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(316, 203, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(317, 202, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(318, 201, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(319, 200, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(320, 199, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(321, 198, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(322, 197, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(323, 196, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(324, 195, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(325, 194, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(326, 193, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(327, 192, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(328, 191, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(329, 190, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(330, 189, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(331, 188, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(332, 187, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(333, 186, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(334, 185, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(335, 184, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(336, 183, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(337, 182, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(338, 181, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(339, 180, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(340, 179, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(341, 178, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(342, 177, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(343, 176, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(344, 175, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(345, 174, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(346, 173, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(347, 172, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(348, 171, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(349, 170, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(350, 169, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(351, 168, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(352, 167, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(353, 166, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(354, 165, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(355, 164, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(356, 163, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(357, 162, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(358, 161, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(359, 160, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(360, 159, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(361, 158, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(362, 157, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(363, 156, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(364, 155, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(365, 154, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(366, 153, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(367, 152, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(368, 151, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(369, 150, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(370, 149, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(371, 148, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(372, 147, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(373, 146, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(374, 145, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(375, 144, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(376, 143, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(377, 142, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(378, 141, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(379, 140, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(380, 139, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(381, 138, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(382, 137, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(383, 136, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(384, 135, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(385, 134, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(386, 133, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(387, 132, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(388, 131, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(389, 130, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(390, 129, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(391, 128, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(392, 127, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(393, 126, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(394, 125, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(395, 124, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(396, 123, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(397, 122, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(398, 121, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(399, 120, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(400, 119, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(401, 118, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(402, 117, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(403, 116, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(404, 115, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(405, 114, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(406, 113, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(407, 112, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(408, 111, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(409, 110, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(410, 109, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(411, 108, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(412, 107, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(413, 106, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(414, 105, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(415, 104, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(416, 103, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(417, 102, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(418, 101, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(419, 100, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(420, 99, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(421, 98, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(422, 97, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(423, 96, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(424, 95, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(425, 94, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(426, 93, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(427, 92, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(428, 91, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(429, 90, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(430, 89, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(431, 88, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(432, 87, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(433, 86, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(434, 85, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(435, 84, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(436, 83, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(437, 82, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(438, 81, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(439, 80, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(440, 79, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(441, 78, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(442, 77, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(443, 76, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(444, 75, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(445, 74, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(446, 73, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(447, 72, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(448, 71, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(449, 70, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(450, 69, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(451, 68, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(452, 67, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(453, 66, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(454, 65, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(455, 64, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(456, 63, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(457, 62, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(458, 61, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(459, 60, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(460, 59, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(461, 58, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(462, 57, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(463, 56, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(464, 55, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(465, 54, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(466, 53, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(467, 52, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(468, 51, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(469, 50, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(470, 49, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(471, 48, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(472, 47, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(473, 46, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(474, 45, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(475, 44, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(476, 43, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(477, 42, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(478, 41, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(479, 40, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(480, 39, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(481, 38, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(482, 37, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(483, 36, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(484, 35, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(485, 34, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(486, 33, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(487, 32, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(488, 31, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(489, 30, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(490, 29, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(491, 28, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(492, 27, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(493, 26, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(494, 25, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(495, 24, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(496, 23, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(497, 22, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(498, 21, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(499, 20, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(500, 19, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(501, 18, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(502, 17, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(503, 16, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(504, 15, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(505, 14, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(506, 13, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(507, 12, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(508, 11, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(509, 10, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(510, 9, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(511, 8, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(512, 7, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(513, 6, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(514, 5, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(515, 4, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(516, 3, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(517, 2, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(518, 1, 'shipping_ongoing', '2026-10-25 14:03:23', 2),
(519, 255, 'arrived_bd', '2026-10-25 14:03:48', 2),
(520, 250, 'arrived_bd', '2026-10-25 14:03:48', 2),
(521, 249, 'arrived_bd', '2026-10-25 14:03:48', 2),
(522, 244, 'arrived_bd', '2026-10-25 14:03:48', 2),
(523, 241, 'arrived_bd', '2026-10-25 14:03:48', 2),
(524, 239, 'arrived_bd', '2026-10-25 14:03:48', 2),
(525, 235, 'arrived_bd', '2026-10-25 14:03:48', 2),
(526, 224, 'arrived_bd', '2026-10-25 14:03:48', 2),
(527, 222, 'arrived_bd', '2026-10-25 14:03:48', 2),
(528, 259, 'arrived_bd', '2026-10-25 14:03:48', 2),
(529, 258, 'arrived_bd', '2026-10-25 14:03:48', 2),
(530, 257, 'arrived_bd', '2026-10-25 14:03:48', 2),
(531, 256, 'arrived_bd', '2026-10-25 14:03:48', 2),
(532, 254, 'arrived_bd', '2026-10-25 14:03:48', 2),
(533, 253, 'arrived_bd', '2026-10-25 14:03:48', 2),
(534, 252, 'arrived_bd', '2026-10-25 14:03:48', 2),
(535, 251, 'arrived_bd', '2026-10-25 14:03:48', 2),
(536, 248, 'arrived_bd', '2026-10-25 14:03:48', 2),
(537, 247, 'arrived_bd', '2026-10-25 14:03:48', 2),
(538, 246, 'arrived_bd', '2026-10-25 14:03:48', 2),
(539, 245, 'arrived_bd', '2026-10-25 14:03:48', 2),
(540, 243, 'arrived_bd', '2026-10-25 14:03:48', 2),
(541, 242, 'arrived_bd', '2026-10-25 14:03:48', 2),
(542, 240, 'arrived_bd', '2026-10-25 14:03:48', 2),
(543, 238, 'arrived_bd', '2026-10-25 14:03:48', 2),
(544, 237, 'arrived_bd', '2026-10-25 14:03:48', 2),
(545, 236, 'arrived_bd', '2026-10-25 14:03:48', 2),
(546, 234, 'arrived_bd', '2026-10-25 14:03:48', 2),
(547, 233, 'arrived_bd', '2026-10-25 14:03:48', 2),
(548, 232, 'arrived_bd', '2026-10-25 14:03:48', 2),
(549, 231, 'arrived_bd', '2026-10-25 14:03:48', 2),
(550, 230, 'arrived_bd', '2026-10-25 14:03:48', 2),
(551, 229, 'arrived_bd', '2026-10-25 14:03:48', 2),
(552, 228, 'arrived_bd', '2026-10-25 14:03:48', 2),
(553, 227, 'arrived_bd', '2026-10-25 14:03:48', 2),
(554, 226, 'arrived_bd', '2026-10-25 14:03:48', 2),
(555, 225, 'arrived_bd', '2026-10-25 14:03:48', 2),
(556, 223, 'arrived_bd', '2026-10-25 14:03:48', 2),
(557, 221, 'arrived_bd', '2026-10-25 14:03:48', 2),
(558, 220, 'arrived_bd', '2026-10-25 14:03:48', 2),
(559, 219, 'arrived_bd', '2026-10-25 14:03:48', 2),
(560, 218, 'arrived_bd', '2026-10-25 14:03:48', 2),
(561, 217, 'arrived_bd', '2026-10-25 14:03:48', 2),
(562, 216, 'arrived_bd', '2026-10-25 14:03:48', 2),
(563, 215, 'arrived_bd', '2026-10-25 14:03:48', 2),
(564, 214, 'arrived_bd', '2026-10-25 14:03:48', 2),
(565, 213, 'arrived_bd', '2026-10-25 14:03:48', 2),
(566, 212, 'arrived_bd', '2026-10-25 14:03:48', 2),
(567, 211, 'arrived_bd', '2026-10-25 14:03:48', 2),
(568, 210, 'arrived_bd', '2026-10-25 14:03:48', 2),
(569, 209, 'arrived_bd', '2026-10-25 14:03:48', 2),
(570, 208, 'arrived_bd', '2026-10-25 14:03:48', 2),
(571, 207, 'arrived_bd', '2026-10-25 14:03:48', 2),
(572, 206, 'arrived_bd', '2026-10-25 14:03:48', 2),
(573, 205, 'arrived_bd', '2026-10-25 14:03:48', 2),
(574, 204, 'arrived_bd', '2026-10-25 14:03:48', 2),
(575, 203, 'arrived_bd', '2026-10-25 14:03:48', 2),
(576, 202, 'arrived_bd', '2026-10-25 14:03:48', 2),
(577, 201, 'arrived_bd', '2026-10-25 14:03:48', 2),
(578, 200, 'arrived_bd', '2026-10-25 14:03:48', 2),
(579, 199, 'arrived_bd', '2026-10-25 14:03:48', 2),
(580, 198, 'arrived_bd', '2026-10-25 14:03:48', 2),
(581, 197, 'arrived_bd', '2026-10-25 14:03:48', 2),
(582, 196, 'arrived_bd', '2026-10-25 14:03:48', 2),
(583, 195, 'arrived_bd', '2026-10-25 14:03:48', 2),
(584, 194, 'arrived_bd', '2026-10-25 14:03:48', 2),
(585, 193, 'arrived_bd', '2026-10-25 14:03:48', 2),
(586, 192, 'arrived_bd', '2026-10-25 14:03:48', 2),
(587, 191, 'arrived_bd', '2026-10-25 14:03:48', 2),
(588, 190, 'arrived_bd', '2026-10-25 14:03:48', 2),
(589, 189, 'arrived_bd', '2026-10-25 14:03:48', 2),
(590, 188, 'arrived_bd', '2026-10-25 14:03:48', 2),
(591, 187, 'arrived_bd', '2026-10-25 14:03:48', 2),
(592, 186, 'arrived_bd', '2026-10-25 14:03:48', 2),
(593, 185, 'arrived_bd', '2026-10-25 14:03:48', 2),
(594, 184, 'arrived_bd', '2026-10-25 14:03:48', 2),
(595, 183, 'arrived_bd', '2026-10-25 14:03:48', 2),
(596, 182, 'arrived_bd', '2026-10-25 14:03:48', 2),
(597, 181, 'arrived_bd', '2026-10-25 14:03:48', 2),
(598, 180, 'arrived_bd', '2026-10-25 14:03:48', 2),
(599, 179, 'arrived_bd', '2026-10-25 14:03:48', 2),
(600, 178, 'arrived_bd', '2026-10-25 14:03:48', 2),
(601, 177, 'arrived_bd', '2026-10-25 14:03:48', 2),
(602, 176, 'arrived_bd', '2026-10-25 14:03:48', 2),
(603, 175, 'arrived_bd', '2026-10-25 14:03:48', 2),
(604, 174, 'arrived_bd', '2026-10-25 14:03:48', 2),
(605, 173, 'arrived_bd', '2026-10-25 14:03:48', 2),
(606, 172, 'arrived_bd', '2026-10-25 14:03:48', 2),
(607, 171, 'arrived_bd', '2026-10-25 14:03:48', 2),
(608, 170, 'arrived_bd', '2026-10-25 14:03:48', 2),
(609, 169, 'arrived_bd', '2026-10-25 14:03:48', 2),
(610, 168, 'arrived_bd', '2026-10-25 14:03:48', 2),
(611, 167, 'arrived_bd', '2026-10-25 14:03:48', 2),
(612, 166, 'arrived_bd', '2026-10-25 14:03:48', 2),
(613, 165, 'arrived_bd', '2026-10-25 14:03:48', 2),
(614, 164, 'arrived_bd', '2026-10-25 14:03:48', 2),
(615, 163, 'arrived_bd', '2026-10-25 14:03:48', 2),
(616, 162, 'arrived_bd', '2026-10-25 14:03:48', 2),
(617, 161, 'arrived_bd', '2026-10-25 14:03:48', 2),
(618, 160, 'arrived_bd', '2026-10-25 14:03:48', 2),
(619, 159, 'arrived_bd', '2026-10-25 14:03:48', 2),
(620, 158, 'arrived_bd', '2026-10-25 14:03:48', 2),
(621, 157, 'arrived_bd', '2026-10-25 14:03:48', 2),
(622, 156, 'arrived_bd', '2026-10-25 14:03:48', 2),
(623, 155, 'arrived_bd', '2026-10-25 14:03:48', 2),
(624, 154, 'arrived_bd', '2026-10-25 14:03:48', 2),
(625, 153, 'arrived_bd', '2026-10-25 14:03:48', 2),
(626, 152, 'arrived_bd', '2026-10-25 14:03:48', 2),
(627, 151, 'arrived_bd', '2026-10-25 14:03:48', 2),
(628, 150, 'arrived_bd', '2026-10-25 14:03:48', 2),
(629, 149, 'arrived_bd', '2026-10-25 14:03:48', 2),
(630, 148, 'arrived_bd', '2026-10-25 14:03:48', 2),
(631, 147, 'arrived_bd', '2026-10-25 14:03:48', 2),
(632, 146, 'arrived_bd', '2026-10-25 14:03:48', 2),
(633, 145, 'arrived_bd', '2026-10-25 14:03:48', 2),
(634, 144, 'arrived_bd', '2026-10-25 14:03:48', 2),
(635, 143, 'arrived_bd', '2026-10-25 14:03:48', 2),
(636, 142, 'arrived_bd', '2026-10-25 14:03:48', 2),
(637, 141, 'arrived_bd', '2026-10-25 14:03:48', 2),
(638, 140, 'arrived_bd', '2026-10-25 14:03:48', 2),
(639, 139, 'arrived_bd', '2026-10-25 14:03:48', 2),
(640, 138, 'arrived_bd', '2026-10-25 14:03:48', 2),
(641, 137, 'arrived_bd', '2026-10-25 14:03:48', 2),
(642, 136, 'arrived_bd', '2026-10-25 14:03:48', 2),
(643, 135, 'arrived_bd', '2026-10-25 14:03:48', 2),
(644, 134, 'arrived_bd', '2026-10-25 14:03:48', 2),
(645, 133, 'arrived_bd', '2026-10-25 14:03:48', 2),
(646, 132, 'arrived_bd', '2026-10-25 14:03:48', 2),
(647, 131, 'arrived_bd', '2026-10-25 14:03:48', 2),
(648, 130, 'arrived_bd', '2026-10-25 14:03:48', 2),
(649, 129, 'arrived_bd', '2026-10-25 14:03:48', 2),
(650, 128, 'arrived_bd', '2026-10-25 14:03:48', 2),
(651, 127, 'arrived_bd', '2026-10-25 14:03:48', 2),
(652, 126, 'arrived_bd', '2026-10-25 14:03:48', 2),
(653, 125, 'arrived_bd', '2026-10-25 14:03:48', 2),
(654, 124, 'arrived_bd', '2026-10-25 14:03:48', 2),
(655, 123, 'arrived_bd', '2026-10-25 14:03:48', 2),
(656, 122, 'arrived_bd', '2026-10-25 14:03:48', 2),
(657, 121, 'arrived_bd', '2026-10-25 14:03:48', 2),
(658, 120, 'arrived_bd', '2026-10-25 14:03:48', 2),
(659, 119, 'arrived_bd', '2026-10-25 14:03:48', 2),
(660, 118, 'arrived_bd', '2026-10-25 14:03:48', 2),
(661, 117, 'arrived_bd', '2026-10-25 14:03:48', 2),
(662, 116, 'arrived_bd', '2026-10-25 14:03:48', 2),
(663, 115, 'arrived_bd', '2026-10-25 14:03:48', 2),
(664, 114, 'arrived_bd', '2026-10-25 14:03:48', 2),
(665, 113, 'arrived_bd', '2026-10-25 14:03:48', 2),
(666, 112, 'arrived_bd', '2026-10-25 14:03:48', 2),
(667, 111, 'arrived_bd', '2026-10-25 14:03:48', 2),
(668, 110, 'arrived_bd', '2026-10-25 14:03:48', 2),
(669, 109, 'arrived_bd', '2026-10-25 14:03:48', 2),
(670, 108, 'arrived_bd', '2026-10-25 14:03:48', 2),
(671, 107, 'arrived_bd', '2026-10-25 14:03:48', 2),
(672, 106, 'arrived_bd', '2026-10-25 14:03:48', 2),
(673, 105, 'arrived_bd', '2026-10-25 14:03:48', 2),
(674, 104, 'arrived_bd', '2026-10-25 14:03:48', 2),
(675, 103, 'arrived_bd', '2026-10-25 14:03:48', 2),
(676, 102, 'arrived_bd', '2026-10-25 14:03:48', 2),
(677, 101, 'arrived_bd', '2026-10-25 14:03:48', 2),
(678, 100, 'arrived_bd', '2026-10-25 14:03:48', 2),
(679, 99, 'arrived_bd', '2026-10-25 14:03:48', 2),
(680, 98, 'arrived_bd', '2026-10-25 14:03:48', 2),
(681, 97, 'arrived_bd', '2026-10-25 14:03:48', 2),
(682, 96, 'arrived_bd', '2026-10-25 14:03:48', 2),
(683, 95, 'arrived_bd', '2026-10-25 14:03:48', 2),
(684, 94, 'arrived_bd', '2026-10-25 14:03:48', 2),
(685, 93, 'arrived_bd', '2026-10-25 14:03:48', 2),
(686, 92, 'arrived_bd', '2026-10-25 14:03:48', 2),
(687, 91, 'arrived_bd', '2026-10-25 14:03:48', 2),
(688, 90, 'arrived_bd', '2026-10-25 14:03:48', 2),
(689, 89, 'arrived_bd', '2026-10-25 14:03:48', 2),
(690, 88, 'arrived_bd', '2026-10-25 14:03:48', 2),
(691, 87, 'arrived_bd', '2026-10-25 14:03:48', 2),
(692, 86, 'arrived_bd', '2026-10-25 14:03:48', 2),
(693, 85, 'arrived_bd', '2026-10-25 14:03:48', 2),
(694, 84, 'arrived_bd', '2026-10-25 14:03:48', 2),
(695, 83, 'arrived_bd', '2026-10-25 14:03:48', 2),
(696, 82, 'arrived_bd', '2026-10-25 14:03:48', 2),
(697, 81, 'arrived_bd', '2026-10-25 14:03:48', 2),
(698, 80, 'arrived_bd', '2026-10-25 14:03:48', 2),
(699, 79, 'arrived_bd', '2026-10-25 14:03:48', 2),
(700, 78, 'arrived_bd', '2026-10-25 14:03:48', 2),
(701, 77, 'arrived_bd', '2026-10-25 14:03:48', 2),
(702, 76, 'arrived_bd', '2026-10-25 14:03:48', 2),
(703, 75, 'arrived_bd', '2026-10-25 14:03:48', 2),
(704, 74, 'arrived_bd', '2026-10-25 14:03:48', 2),
(705, 73, 'arrived_bd', '2026-10-25 14:03:48', 2),
(706, 72, 'arrived_bd', '2026-10-25 14:03:48', 2),
(707, 71, 'arrived_bd', '2026-10-25 14:03:48', 2),
(708, 70, 'arrived_bd', '2026-10-25 14:03:48', 2),
(709, 69, 'arrived_bd', '2026-10-25 14:03:48', 2),
(710, 68, 'arrived_bd', '2026-10-25 14:03:48', 2),
(711, 67, 'arrived_bd', '2026-10-25 14:03:48', 2),
(712, 66, 'arrived_bd', '2026-10-25 14:03:48', 2),
(713, 65, 'arrived_bd', '2026-10-25 14:03:48', 2),
(714, 64, 'arrived_bd', '2026-10-25 14:03:48', 2),
(715, 63, 'arrived_bd', '2026-10-25 14:03:48', 2),
(716, 62, 'arrived_bd', '2026-10-25 14:03:48', 2),
(717, 61, 'arrived_bd', '2026-10-25 14:03:48', 2),
(718, 60, 'arrived_bd', '2026-10-25 14:03:48', 2),
(719, 59, 'arrived_bd', '2026-10-25 14:03:48', 2),
(720, 58, 'arrived_bd', '2026-10-25 14:03:48', 2),
(721, 57, 'arrived_bd', '2026-10-25 14:03:48', 2),
(722, 56, 'arrived_bd', '2026-10-25 14:03:48', 2),
(723, 55, 'arrived_bd', '2026-10-25 14:03:48', 2),
(724, 54, 'arrived_bd', '2026-10-25 14:03:48', 2),
(725, 53, 'arrived_bd', '2026-10-25 14:03:48', 2),
(726, 52, 'arrived_bd', '2026-10-25 14:03:48', 2),
(727, 51, 'arrived_bd', '2026-10-25 14:03:48', 2),
(728, 50, 'arrived_bd', '2026-10-25 14:03:48', 2),
(729, 49, 'arrived_bd', '2026-10-25 14:03:48', 2),
(730, 48, 'arrived_bd', '2026-10-25 14:03:48', 2),
(731, 47, 'arrived_bd', '2026-10-25 14:03:48', 2),
(732, 46, 'arrived_bd', '2026-10-25 14:03:48', 2),
(733, 45, 'arrived_bd', '2026-10-25 14:03:48', 2),
(734, 44, 'arrived_bd', '2026-10-25 14:03:48', 2),
(735, 43, 'arrived_bd', '2026-10-25 14:03:48', 2),
(736, 42, 'arrived_bd', '2026-10-25 14:03:48', 2),
(737, 41, 'arrived_bd', '2026-10-25 14:03:48', 2),
(738, 40, 'arrived_bd', '2026-10-25 14:03:48', 2),
(739, 39, 'arrived_bd', '2026-10-25 14:03:48', 2),
(740, 38, 'arrived_bd', '2026-10-25 14:03:48', 2),
(741, 37, 'arrived_bd', '2026-10-25 14:03:48', 2),
(742, 36, 'arrived_bd', '2026-10-25 14:03:48', 2),
(743, 35, 'arrived_bd', '2026-10-25 14:03:48', 2),
(744, 34, 'arrived_bd', '2026-10-25 14:03:48', 2),
(745, 33, 'arrived_bd', '2026-10-25 14:03:48', 2),
(746, 32, 'arrived_bd', '2026-10-25 14:03:48', 2),
(747, 31, 'arrived_bd', '2026-10-25 14:03:48', 2),
(748, 30, 'arrived_bd', '2026-10-25 14:03:48', 2),
(749, 29, 'arrived_bd', '2026-10-25 14:03:48', 2),
(750, 28, 'arrived_bd', '2026-10-25 14:03:48', 2),
(751, 27, 'arrived_bd', '2026-10-25 14:03:48', 2),
(752, 26, 'arrived_bd', '2026-10-25 14:03:48', 2),
(753, 25, 'arrived_bd', '2026-10-25 14:03:48', 2),
(754, 24, 'arrived_bd', '2026-10-25 14:03:48', 2),
(755, 23, 'arrived_bd', '2026-10-25 14:03:48', 2),
(756, 22, 'arrived_bd', '2026-10-25 14:03:48', 2),
(757, 21, 'arrived_bd', '2026-10-25 14:03:48', 2),
(758, 20, 'arrived_bd', '2026-10-25 14:03:48', 2),
(759, 19, 'arrived_bd', '2026-10-25 14:03:48', 2),
(760, 18, 'arrived_bd', '2026-10-25 14:03:48', 2),
(761, 17, 'arrived_bd', '2026-10-25 14:03:48', 2),
(762, 16, 'arrived_bd', '2026-10-25 14:03:48', 2),
(763, 15, 'arrived_bd', '2026-10-25 14:03:48', 2),
(764, 14, 'arrived_bd', '2026-10-25 14:03:48', 2),
(765, 13, 'arrived_bd', '2026-10-25 14:03:48', 2),
(766, 12, 'arrived_bd', '2026-10-25 14:03:48', 2),
(767, 11, 'arrived_bd', '2026-10-25 14:03:48', 2),
(768, 10, 'arrived_bd', '2026-10-25 14:03:48', 2),
(769, 9, 'arrived_bd', '2026-10-25 14:03:48', 2),
(770, 8, 'arrived_bd', '2026-10-25 14:03:48', 2),
(771, 7, 'arrived_bd', '2026-10-25 14:03:48', 2),
(772, 6, 'arrived_bd', '2026-10-25 14:03:48', 2),
(773, 5, 'arrived_bd', '2026-10-25 14:03:48', 2),
(774, 4, 'arrived_bd', '2026-10-25 14:03:48', 2),
(775, 3, 'arrived_bd', '2026-10-25 14:03:48', 2),
(776, 2, 'arrived_bd', '2026-10-25 14:03:48', 2),
(777, 1, 'arrived_bd', '2026-10-25 14:03:48', 2),
(778, 255, 'arrived_bd', '2026-10-25 14:10:15', 2),
(779, 250, 'arrived_bd', '2026-10-25 14:10:15', 2),
(780, 249, 'arrived_bd', '2026-10-25 14:10:15', 2),
(781, 244, 'arrived_bd', '2026-10-25 14:10:15', 2),
(782, 241, 'arrived_bd', '2026-10-25 14:10:15', 2),
(783, 239, 'arrived_bd', '2026-10-25 14:10:15', 2),
(784, 235, 'arrived_bd', '2026-10-25 14:10:15', 2),
(785, 224, 'arrived_bd', '2026-10-25 14:10:15', 2),
(786, 222, 'arrived_bd', '2026-10-25 14:10:15', 2),
(787, 259, 'arrived_bd', '2026-10-25 14:10:15', 2),
(788, 258, 'arrived_bd', '2026-10-25 14:10:15', 2),
(789, 257, 'arrived_bd', '2026-10-25 14:10:15', 2),
(790, 256, 'arrived_bd', '2026-10-25 14:10:15', 2),
(791, 254, 'arrived_bd', '2026-10-25 14:10:15', 2),
(792, 253, 'arrived_bd', '2026-10-25 14:10:15', 2),
(793, 252, 'arrived_bd', '2026-10-25 14:10:15', 2),
(794, 251, 'arrived_bd', '2026-10-25 14:10:15', 2),
(795, 248, 'arrived_bd', '2026-10-25 14:10:15', 2),
(796, 247, 'arrived_bd', '2026-10-25 14:10:15', 2),
(797, 246, 'arrived_bd', '2026-10-25 14:10:15', 2),
(798, 245, 'arrived_bd', '2026-10-25 14:10:15', 2),
(799, 243, 'arrived_bd', '2026-10-25 14:10:15', 2),
(800, 242, 'arrived_bd', '2026-10-25 14:10:15', 2),
(801, 240, 'arrived_bd', '2026-10-25 14:10:15', 2),
(802, 238, 'arrived_bd', '2026-10-25 14:10:15', 2),
(803, 237, 'arrived_bd', '2026-10-25 14:10:15', 2),
(804, 236, 'arrived_bd', '2026-10-25 14:10:15', 2),
(805, 234, 'arrived_bd', '2026-10-25 14:10:15', 2),
(806, 233, 'arrived_bd', '2026-10-25 14:10:15', 2),
(807, 232, 'arrived_bd', '2026-10-25 14:10:15', 2),
(808, 231, 'arrived_bd', '2026-10-25 14:10:15', 2),
(809, 230, 'arrived_bd', '2026-10-25 14:10:15', 2),
(810, 229, 'arrived_bd', '2026-10-25 14:10:15', 2),
(811, 228, 'arrived_bd', '2026-10-25 14:10:15', 2),
(812, 227, 'arrived_bd', '2026-10-25 14:10:15', 2),
(813, 226, 'arrived_bd', '2026-10-25 14:10:15', 2),
(814, 225, 'arrived_bd', '2026-10-25 14:10:15', 2),
(815, 223, 'arrived_bd', '2026-10-25 14:10:15', 2),
(816, 221, 'arrived_bd', '2026-10-25 14:10:15', 2),
(817, 220, 'arrived_bd', '2026-10-25 14:10:15', 2),
(818, 219, 'arrived_bd', '2026-10-25 14:10:15', 2),
(819, 218, 'arrived_bd', '2026-10-25 14:10:15', 2),
(820, 217, 'arrived_bd', '2026-10-25 14:10:15', 2),
(821, 216, 'arrived_bd', '2026-10-25 14:10:15', 2),
(822, 215, 'arrived_bd', '2026-10-25 14:10:15', 2),
(823, 214, 'arrived_bd', '2026-10-25 14:10:15', 2),
(824, 213, 'arrived_bd', '2026-10-25 14:10:15', 2),
(825, 212, 'arrived_bd', '2026-10-25 14:10:15', 2),
(826, 211, 'arrived_bd', '2026-10-25 14:10:15', 2),
(827, 210, 'arrived_bd', '2026-10-25 14:10:15', 2),
(828, 209, 'arrived_bd', '2026-10-25 14:10:15', 2),
(829, 208, 'arrived_bd', '2026-10-25 14:10:15', 2),
(830, 207, 'arrived_bd', '2026-10-25 14:10:15', 2),
(831, 206, 'arrived_bd', '2026-10-25 14:10:15', 2),
(832, 205, 'arrived_bd', '2026-10-25 14:10:15', 2),
(833, 204, 'arrived_bd', '2026-10-25 14:10:15', 2),
(834, 203, 'arrived_bd', '2026-10-25 14:10:15', 2),
(835, 202, 'arrived_bd', '2026-10-25 14:10:15', 2),
(836, 201, 'arrived_bd', '2026-10-25 14:10:15', 2),
(837, 200, 'arrived_bd', '2026-10-25 14:10:15', 2),
(838, 199, 'arrived_bd', '2026-10-25 14:10:15', 2),
(839, 198, 'arrived_bd', '2026-10-25 14:10:15', 2),
(840, 197, 'arrived_bd', '2026-10-25 14:10:15', 2),
(841, 196, 'arrived_bd', '2026-10-25 14:10:15', 2),
(842, 195, 'arrived_bd', '2026-10-25 14:10:15', 2),
(843, 194, 'arrived_bd', '2026-10-25 14:10:15', 2),
(844, 193, 'arrived_bd', '2026-10-25 14:10:15', 2),
(845, 192, 'arrived_bd', '2026-10-25 14:10:15', 2),
(846, 191, 'arrived_bd', '2026-10-25 14:10:15', 2),
(847, 190, 'arrived_bd', '2026-10-25 14:10:15', 2),
(848, 189, 'arrived_bd', '2026-10-25 14:10:15', 2),
(849, 188, 'arrived_bd', '2026-10-25 14:10:15', 2),
(850, 187, 'arrived_bd', '2026-10-25 14:10:15', 2),
(851, 186, 'arrived_bd', '2026-10-25 14:10:15', 2),
(852, 185, 'arrived_bd', '2026-10-25 14:10:15', 2),
(853, 184, 'arrived_bd', '2026-10-25 14:10:15', 2),
(854, 183, 'arrived_bd', '2026-10-25 14:10:15', 2),
(855, 182, 'arrived_bd', '2026-10-25 14:10:15', 2),
(856, 181, 'arrived_bd', '2026-10-25 14:10:15', 2),
(857, 180, 'arrived_bd', '2026-10-25 14:10:15', 2),
(858, 179, 'arrived_bd', '2026-10-25 14:10:15', 2),
(859, 178, 'arrived_bd', '2026-10-25 14:10:15', 2),
(860, 177, 'arrived_bd', '2026-10-25 14:10:15', 2),
(861, 176, 'arrived_bd', '2026-10-25 14:10:15', 2),
(862, 175, 'arrived_bd', '2026-10-25 14:10:15', 2),
(863, 174, 'arrived_bd', '2026-10-25 14:10:15', 2),
(864, 173, 'arrived_bd', '2026-10-25 14:10:15', 2),
(865, 172, 'arrived_bd', '2026-10-25 14:10:15', 2),
(866, 171, 'arrived_bd', '2026-10-25 14:10:15', 2),
(867, 170, 'arrived_bd', '2026-10-25 14:10:15', 2),
(868, 169, 'arrived_bd', '2026-10-25 14:10:15', 2),
(869, 168, 'arrived_bd', '2026-10-25 14:10:15', 2),
(870, 167, 'arrived_bd', '2026-10-25 14:10:15', 2),
(871, 166, 'arrived_bd', '2026-10-25 14:10:15', 2),
(872, 165, 'arrived_bd', '2026-10-25 14:10:15', 2),
(873, 164, 'arrived_bd', '2026-10-25 14:10:15', 2),
(874, 163, 'arrived_bd', '2026-10-25 14:10:15', 2),
(875, 162, 'arrived_bd', '2026-10-25 14:10:15', 2),
(876, 161, 'arrived_bd', '2026-10-25 14:10:15', 2),
(877, 160, 'arrived_bd', '2026-10-25 14:10:15', 2),
(878, 159, 'arrived_bd', '2026-10-25 14:10:15', 2),
(879, 158, 'arrived_bd', '2026-10-25 14:10:15', 2),
(880, 157, 'arrived_bd', '2026-10-25 14:10:15', 2),
(881, 156, 'arrived_bd', '2026-10-25 14:10:15', 2),
(882, 155, 'arrived_bd', '2026-10-25 14:10:15', 2),
(883, 154, 'arrived_bd', '2026-10-25 14:10:15', 2),
(884, 153, 'arrived_bd', '2026-10-25 14:10:15', 2),
(885, 152, 'arrived_bd', '2026-10-25 14:10:15', 2),
(886, 151, 'arrived_bd', '2026-10-25 14:10:15', 2),
(887, 150, 'arrived_bd', '2026-10-25 14:10:15', 2),
(888, 149, 'arrived_bd', '2026-10-25 14:10:15', 2),
(889, 148, 'arrived_bd', '2026-10-25 14:10:15', 2),
(890, 147, 'arrived_bd', '2026-10-25 14:10:15', 2),
(891, 146, 'arrived_bd', '2026-10-25 14:10:15', 2),
(892, 145, 'arrived_bd', '2026-10-25 14:10:15', 2),
(893, 144, 'arrived_bd', '2026-10-25 14:10:15', 2),
(894, 143, 'arrived_bd', '2026-10-25 14:10:15', 2),
(895, 142, 'arrived_bd', '2026-10-25 14:10:15', 2),
(896, 141, 'arrived_bd', '2026-10-25 14:10:15', 2),
(897, 140, 'arrived_bd', '2026-10-25 14:10:15', 2),
(898, 139, 'arrived_bd', '2026-10-25 14:10:15', 2),
(899, 138, 'arrived_bd', '2026-10-25 14:10:15', 2),
(900, 137, 'arrived_bd', '2026-10-25 14:10:15', 2),
(901, 136, 'arrived_bd', '2026-10-25 14:10:15', 2),
(902, 135, 'arrived_bd', '2026-10-25 14:10:15', 2),
(903, 134, 'arrived_bd', '2026-10-25 14:10:15', 2),
(904, 133, 'arrived_bd', '2026-10-25 14:10:15', 2),
(905, 132, 'arrived_bd', '2026-10-25 14:10:15', 2),
(906, 131, 'arrived_bd', '2026-10-25 14:10:15', 2),
(907, 130, 'arrived_bd', '2026-10-25 14:10:15', 2),
(908, 129, 'arrived_bd', '2026-10-25 14:10:15', 2),
(909, 128, 'arrived_bd', '2026-10-25 14:10:15', 2),
(910, 127, 'arrived_bd', '2026-10-25 14:10:15', 2),
(911, 126, 'arrived_bd', '2026-10-25 14:10:15', 2),
(912, 125, 'arrived_bd', '2026-10-25 14:10:15', 2),
(913, 124, 'arrived_bd', '2026-10-25 14:10:15', 2),
(914, 123, 'arrived_bd', '2026-10-25 14:10:15', 2),
(915, 122, 'arrived_bd', '2026-10-25 14:10:15', 2),
(916, 121, 'arrived_bd', '2026-10-25 14:10:15', 2),
(917, 120, 'arrived_bd', '2026-10-25 14:10:15', 2),
(918, 119, 'arrived_bd', '2026-10-25 14:10:15', 2),
(919, 118, 'arrived_bd', '2026-10-25 14:10:15', 2),
(920, 117, 'arrived_bd', '2026-10-25 14:10:15', 2),
(921, 116, 'arrived_bd', '2026-10-25 14:10:15', 2),
(922, 115, 'arrived_bd', '2026-10-25 14:10:15', 2),
(923, 114, 'arrived_bd', '2026-10-25 14:10:15', 2),
(924, 113, 'arrived_bd', '2026-10-25 14:10:15', 2),
(925, 112, 'arrived_bd', '2026-10-25 14:10:15', 2),
(926, 111, 'arrived_bd', '2026-10-25 14:10:15', 2),
(927, 110, 'arrived_bd', '2026-10-25 14:10:15', 2),
(928, 109, 'arrived_bd', '2026-10-25 14:10:15', 2),
(929, 108, 'arrived_bd', '2026-10-25 14:10:15', 2),
(930, 107, 'arrived_bd', '2026-10-25 14:10:15', 2),
(931, 106, 'arrived_bd', '2026-10-25 14:10:15', 2),
(932, 105, 'arrived_bd', '2026-10-25 14:10:15', 2),
(933, 104, 'arrived_bd', '2026-10-25 14:10:15', 2),
(934, 103, 'arrived_bd', '2026-10-25 14:10:15', 2),
(935, 102, 'arrived_bd', '2026-10-25 14:10:15', 2),
(936, 101, 'arrived_bd', '2026-10-25 14:10:15', 2),
(937, 100, 'arrived_bd', '2026-10-25 14:10:15', 2),
(938, 99, 'arrived_bd', '2026-10-25 14:10:15', 2),
(939, 98, 'arrived_bd', '2026-10-25 14:10:15', 2),
(940, 97, 'arrived_bd', '2026-10-25 14:10:15', 2),
(941, 96, 'arrived_bd', '2026-10-25 14:10:15', 2),
(942, 95, 'arrived_bd', '2026-10-25 14:10:15', 2),
(943, 94, 'arrived_bd', '2026-10-25 14:10:15', 2),
(944, 93, 'arrived_bd', '2026-10-25 14:10:15', 2),
(945, 92, 'arrived_bd', '2026-10-25 14:10:15', 2),
(946, 91, 'arrived_bd', '2026-10-25 14:10:15', 2),
(947, 90, 'arrived_bd', '2026-10-25 14:10:15', 2),
(948, 89, 'arrived_bd', '2026-10-25 14:10:15', 2),
(949, 88, 'arrived_bd', '2026-10-25 14:10:15', 2),
(950, 87, 'arrived_bd', '2026-10-25 14:10:15', 2),
(951, 86, 'arrived_bd', '2026-10-25 14:10:15', 2),
(952, 85, 'arrived_bd', '2026-10-25 14:10:15', 2),
(953, 84, 'arrived_bd', '2026-10-25 14:10:15', 2),
(954, 83, 'arrived_bd', '2026-10-25 14:10:15', 2),
(955, 82, 'arrived_bd', '2026-10-25 14:10:15', 2),
(956, 81, 'arrived_bd', '2026-10-25 14:10:15', 2),
(957, 80, 'arrived_bd', '2026-10-25 14:10:15', 2),
(958, 79, 'arrived_bd', '2026-10-25 14:10:15', 2),
(959, 78, 'arrived_bd', '2026-10-25 14:10:15', 2),
(960, 77, 'arrived_bd', '2026-10-25 14:10:15', 2);
INSERT INTO `cargo_item_status_logs` (`id`, `cargo_item_id`, `status`, `changed_at`, `user_id`) VALUES
(961, 76, 'arrived_bd', '2026-10-25 14:10:15', 2),
(962, 75, 'arrived_bd', '2026-10-25 14:10:15', 2),
(963, 74, 'arrived_bd', '2026-10-25 14:10:15', 2),
(964, 73, 'arrived_bd', '2026-10-25 14:10:15', 2),
(965, 72, 'arrived_bd', '2026-10-25 14:10:15', 2),
(966, 71, 'arrived_bd', '2026-10-25 14:10:15', 2),
(967, 70, 'arrived_bd', '2026-10-25 14:10:15', 2),
(968, 69, 'arrived_bd', '2026-10-25 14:10:15', 2),
(969, 68, 'arrived_bd', '2026-10-25 14:10:15', 2),
(970, 67, 'arrived_bd', '2026-10-25 14:10:15', 2),
(971, 66, 'arrived_bd', '2026-10-25 14:10:15', 2),
(972, 65, 'arrived_bd', '2026-10-25 14:10:15', 2),
(973, 64, 'arrived_bd', '2026-10-25 14:10:15', 2),
(974, 63, 'arrived_bd', '2026-10-25 14:10:15', 2),
(975, 62, 'arrived_bd', '2026-10-25 14:10:15', 2),
(976, 61, 'arrived_bd', '2026-10-25 14:10:15', 2),
(977, 60, 'arrived_bd', '2026-10-25 14:10:15', 2),
(978, 59, 'arrived_bd', '2026-10-25 14:10:15', 2),
(979, 58, 'arrived_bd', '2026-10-25 14:10:15', 2),
(980, 57, 'arrived_bd', '2026-10-25 14:10:15', 2),
(981, 56, 'arrived_bd', '2026-10-25 14:10:15', 2),
(982, 55, 'arrived_bd', '2026-10-25 14:10:15', 2),
(983, 54, 'arrived_bd', '2026-10-25 14:10:15', 2),
(984, 53, 'arrived_bd', '2026-10-25 14:10:15', 2),
(985, 52, 'arrived_bd', '2026-10-25 14:10:15', 2),
(986, 51, 'arrived_bd', '2026-10-25 14:10:15', 2),
(987, 50, 'arrived_bd', '2026-10-25 14:10:15', 2),
(988, 49, 'arrived_bd', '2026-10-25 14:10:15', 2),
(989, 48, 'arrived_bd', '2026-10-25 14:10:15', 2),
(990, 47, 'arrived_bd', '2026-10-25 14:10:15', 2),
(991, 46, 'arrived_bd', '2026-10-25 14:10:15', 2),
(992, 45, 'arrived_bd', '2026-10-25 14:10:15', 2),
(993, 44, 'arrived_bd', '2026-10-25 14:10:15', 2),
(994, 43, 'arrived_bd', '2026-10-25 14:10:15', 2),
(995, 42, 'arrived_bd', '2026-10-25 14:10:15', 2),
(996, 41, 'arrived_bd', '2026-10-25 14:10:15', 2),
(997, 40, 'arrived_bd', '2026-10-25 14:10:15', 2),
(998, 39, 'arrived_bd', '2026-10-25 14:10:15', 2),
(999, 38, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1000, 37, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1001, 36, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1002, 35, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1003, 34, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1004, 33, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1005, 32, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1006, 31, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1007, 30, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1008, 29, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1009, 28, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1010, 27, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1011, 26, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1012, 25, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1013, 24, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1014, 23, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1015, 22, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1016, 21, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1017, 20, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1018, 19, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1019, 18, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1020, 17, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1021, 16, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1022, 15, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1023, 14, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1024, 13, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1025, 12, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1026, 11, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1027, 10, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1028, 9, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1029, 8, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1030, 7, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1031, 6, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1032, 5, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1033, 4, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1034, 3, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1035, 2, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1036, 1, 'arrived_bd', '2026-10-25 14:10:15', 2),
(1037, 261, 'ready_to_ship', '2026-10-26 05:19:03', 2),
(1038, 260, 'ready_to_ship', '2026-10-26 05:19:03', 2),
(1039, 261, 'shipping_ongoing', '2026-10-26 05:21:44', 2),
(1040, 260, 'shipping_ongoing', '2026-10-26 05:21:44', 2),
(1041, 261, 'shipping_ongoing', '2026-10-26 05:40:03', 2),
(1042, 260, 'shipping_ongoing', '2026-10-26 05:40:03', 2),
(1043, 261, 'waiting_for_customs', '2026-10-26 05:40:30', 2),
(1044, 260, 'waiting_for_customs', '2026-10-26 05:40:30', 2),
(1045, 261, 'arrived_bd', '2026-10-26 05:40:49', 2),
(1046, 260, 'arrived_bd', '2026-10-26 05:40:49', 2),
(1047, 260, 'ready_to_deliver', '2026-10-26 06:25:30', 2),
(1048, 261, 'ready_to_deliver', '2026-10-26 06:25:30', 2);

-- --------------------------------------------------------

--
-- Table structure for table `cost_types`
--

CREATE TABLE `cost_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `have_agent` tinyint(1) NOT NULL DEFAULT 0,
  `have_partial_payment` tinyint(1) NOT NULL DEFAULT 0,
  `type` enum('fixed','per_kg') NOT NULL DEFAULT 'fixed',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cost_types`
--

INSERT INTO `cost_types` (`id`, `name`, `description`, `have_agent`, `have_partial_payment`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Freight Agent Cost', 'Cost per kg for freight forwarder', 1, 1, 'per_kg', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(2, 'CNF Agent Cost', 'Cost per kg for CNF agent', 1, 1, 'per_kg', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(3, 'Transport Cost Chine warehouse to freight', 'Transportation cost', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(4, 'Labor Cost Chine warehouse to freight', 'Loading/unloading labor cost', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(5, 'Documentation Cost', 'Documentation related charges', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(6, 'Bill of Lading Cost', 'Cost for Bill of Lading', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(7, 'LC Personal', 'LC personal charges', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(8, 'LC Other', 'Other LC related costs', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(9, 'Tax', 'Tax charges', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(10, 'Airport Warehouse', 'Airport warehouse bill', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(11, 'Unloading Cost airport warehouse to our warehouse', 'Unloading cost at warehouse', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(12, 'Miscellaneous airport warehouse to our warehouse', 'Other miscellaneous costs', 0, 0, 'fixed', '2026-10-25 13:47:11', '2026-10-25 13:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `ctn_sequences`
--

CREATE TABLE `ctn_sequences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prefix` varchar(255) NOT NULL,
  `last_number` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `pad_width` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ctn_sequences`
--

INSERT INTO `ctn_sequences` (`id`, `prefix`, `last_number`, `pad_width`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'c', 268, 0, '2026-10-25 14:00:53', '2026-10-25 14:00:53', '2026-10-25 14:00:53');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `customer_code` varchar(255) NOT NULL,
  `shipping_mark1` varchar(255) DEFAULT NULL,
  `shipping_mark2` varchar(255) DEFAULT NULL,
  `shipping_mark3` varchar(255) DEFAULT NULL,
  `shipping_mark4` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `is_partner` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `customer_code`, `shipping_mark1`, `shipping_mark2`, `shipping_mark3`, `shipping_mark4`, `address`, `is_partner`, `created_at`, `updated_at`) VALUES
(1, 'John Doe', 'john@example.com', '01700000001', 'CUST001', NULL, NULL, NULL, NULL, 'Dhaka, Bangladesh', 0, '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(2, 'Sarah Khan', 'sarah@example.com', '01700000002', 'CUST002', NULL, NULL, NULL, NULL, 'Chittagong, Bangladesh', 0, '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(3, 'Mahmud Rahman', 'mahmud@example.com', '01700000003', 'CUST003', NULL, NULL, NULL, NULL, 'Sylhet, Bangladesh', 0, '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(4, 'Ayesha Akter', 'ayesha@example.com', '01700000004', 'CUST004', NULL, NULL, NULL, NULL, 'Rajshahi, Bangladesh', 0, '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(5, 'Imran Hossain', 'imran@example.com', '01700000005', 'CUST005', 'Chinacart', 'Ladeh', NULL, NULL, 'Khulna, Bangladesh', 0, '2026-10-25 13:47:09', '2026-10-25 14:31:57');

-- --------------------------------------------------------

--
-- Table structure for table `customer_extra_shipping_marks`
--

CREATE TABLE `customer_extra_shipping_marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `shipping_mark` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_payments`
--

CREATE TABLE `customer_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `itemshipment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `payment_date` date NOT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `remaining_due` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'bdt',
  `foreign_amount` decimal(15,2) DEFAULT NULL,
  `exchange_rate` decimal(15,4) NOT NULL DEFAULT 1.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_payments`
--

INSERT INTO `customer_payments` (`id`, `customer_id`, `user_id`, `itemshipment_id`, `amount`, `payment_method`, `payment_date`, `reference_no`, `notes`, `remaining_due`, `created_at`, `updated_at`, `currency`, `foreign_amount`, `exchange_rate`) VALUES
(1, 5, 2, 2, 20000.00, 'cash', '2026-10-26', NULL, NULL, 114310.00, '2026-10-26 05:43:20', '2026-10-26 05:43:20', 'bdt', NULL, 1.0000),
(2, 2, 2, 2, 100000.00, 'bank_transfer', '2026-10-26', NULL, NULL, 50000.00, '2026-10-26 05:44:08', '2026-10-26 05:44:08', 'bdt', NULL, 1.0000),
(3, 2, 2, 2, 20000.00, 'cheque', '2026-10-26', NULL, NULL, 30000.00, '2026-10-26 15:39:16', '2026-10-26 15:39:16', 'bdt', NULL, 1.0000);

-- --------------------------------------------------------

--
-- Table structure for table `customer_payment_allocations`
--

CREATE TABLE `customer_payment_allocations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_payment_id` bigint(20) UNSIGNED NOT NULL,
  `itemshipment_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customer_payment_allocations`
--

INSERT INTO `customer_payment_allocations` (`id`, `customer_payment_id`, `itemshipment_id`, `amount`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 20000.00, '2026-10-26 05:43:20', '2026-10-26 05:43:20'),
(2, 2, 2, 50000.00, '2026-10-26 05:44:08', '2026-10-26 05:44:08'),
(3, 3, 2, 20000.00, '2026-10-26 15:39:16', '2026-10-26 15:39:16');

-- --------------------------------------------------------

--
-- Table structure for table `customer_payment_items`
--

CREATE TABLE `customer_payment_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_payment_id` bigint(20) UNSIGNED NOT NULL,
  `cargo_item_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `office_id` bigint(20) UNSIGNED NOT NULL,
  `salary` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `imported_excel_files`
--

CREATE TABLE `imported_excel_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `total_rows` int(11) NOT NULL DEFAULT 0,
  `imported_rows` int(11) NOT NULL DEFAULT 0,
  `duplicate_rows` int(11) NOT NULL DEFAULT 0,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `imported_excel_files`
--

INSERT INTO `imported_excel_files` (`id`, `file_name`, `file_path`, `title`, `note`, `total_rows`, `imported_rows`, `duplicate_rows`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 'GZ0439 Packing List Oct.14.2026(2).xlsx', 'imported_excel/1792936095_GZ0439_Packing_List_Oct.14.2026_2_.xlsx', 'GZ0439 Packing List Oct.14.2026(2).xlsx', NULL, 211, 211, 0, 2, '2026-10-25 13:48:15', '2026-10-25 13:48:53'),
(2, 'GZ0439 Packing List Oct.14.2026(3) - Copy.xlsx', 'imported_excel/1792936180_GZ0439_Packing_List_Oct.14.2026_3__-_Copy.xlsx', 'GZ0439 Packing List Oct.14.2026(3) - Copy.xlsx', NULL, 48, 48, 0, 2, '2026-10-25 13:49:40', '2026-10-25 13:49:52');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `itemshipment_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `customer_id`, `itemshipment_id`, `created_at`, `updated_at`) VALUES
(1, 'INV2600001', 5, 1, '2026-10-25 14:35:22', '2026-10-25 14:35:22'),
(2, 'INV2600002', 5, 2, '2026-10-26 05:41:50', '2026-10-26 05:41:50'),
(3, 'INV2600003', 2, 2, '2026-10-26 05:45:48', '2026-10-26 05:45:48');

-- --------------------------------------------------------

--
-- Table structure for table `items_shipments`
--

CREATE TABLE `items_shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `shipment_name` varchar(255) NOT NULL,
  `shipment_date` date DEFAULT NULL,
  `note` text DEFAULT NULL,
  `status` enum('draft','pending','shipped','arrived_bd','waiting_for_customs','delivered','cancelled') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items_shipments`
--

INSERT INTO `items_shipments` (`id`, `shipment_name`, `shipment_date`, `note`, `status`, `created_at`, `updated_at`) VALUES
(1, 'GZ0439', '2026-10-14', NULL, 'arrived_bd', '2026-10-25 13:50:08', '2026-10-25 14:10:15'),
(2, 'Test Shipment', '2026-10-26', NULL, 'delivered', '2026-10-26 05:18:06', '2026-10-26 06:25:30');

-- --------------------------------------------------------

--
-- Table structure for table `items_shipment_costs`
--

CREATE TABLE `items_shipment_costs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `items_shipment_id` bigint(20) UNSIGNED NOT NULL,
  `cost_type_id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'bdt',
  `exchange_rate_bdt` decimal(15,4) NOT NULL DEFAULT 1.0000,
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `description` text DEFAULT NULL,
  `rate_per_kg` decimal(10,2) DEFAULT NULL,
  `total_weight` decimal(10,2) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `final_bdt_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `cost_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items_shipment_costs`
--

INSERT INTO `items_shipment_costs` (`id`, `items_shipment_id`, `cost_type_id`, `currency`, `exchange_rate_bdt`, `agent_id`, `description`, `rate_per_kg`, `total_weight`, `amount`, `final_bdt_cost`, `paid_amount`, `due_amount`, `status`, `cost_date`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'rmb', 19.2000, 1, NULL, 14.00, 4240.00, 59360.00, 1139712.00, 0.00, 1139712.00, 'pending', '2026-10-25', '2026-10-25 14:09:06', '2026-10-25 14:09:06'),
(2, 2, 1, 'rmb', 19.0000, 3, NULL, 5.00, 700.00, 3500.00, 66500.00, 66500.00, 0.00, 'paid', '2026-10-26', '2026-10-26 05:34:28', '2026-10-26 05:39:10'),
(3, 2, 2, 'bdt', 1.0000, 8, NULL, 10.00, 700.00, 7000.00, 7000.00, 7000.00, 0.00, 'paid', '2026-10-26', '2026-10-26 05:36:32', '2026-10-26 05:39:33'),
(4, 2, 4, 'rmb', 19.0000, NULL, NULL, NULL, 700.00, 60.00, 1140.00, 1140.00, 0.00, 'paid', '2026-10-26', '2026-10-26 05:37:43', '2026-10-26 05:38:07');

-- --------------------------------------------------------

--
-- Table structure for table `items_shipment_cost_attachments`
--

CREATE TABLE `items_shipment_cost_attachments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `items_shipment_cost_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items_shipment_cost_attachments`
--

INSERT INTO `items_shipment_cost_attachments` (`id`, `items_shipment_cost_id`, `file_path`, `original_name`, `mime_type`, `file_size`, `created_at`, `updated_at`) VALUES
(1, 1, '0', 'DEBIT NOTE.jpg', 'image/jpeg', 149521, '2026-10-25 14:09:06', '2026-10-25 14:09:06'),
(2, 2, '0', 'TechLab33_Company_Summary.pdf', 'application/pdf', 182840, '2026-10-26 05:34:28', '2026-10-26 05:34:28'),
(3, 3, '0', 'Container Ship at Golden Horizon.png', 'image/png', 2040944, '2026-10-26 05:36:32', '2026-10-26 05:36:32'),
(4, 4, '0', 'TechLab Logistics Dashboard Overview.png', 'image/png', 1502957, '2026-10-26 05:37:43', '2026-10-26 05:37:43');

-- --------------------------------------------------------

--
-- Table structure for table `items_shipment_payments`
--

CREATE TABLE `items_shipment_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `items_shipment_cost_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_by` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `shipment_bill_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'bdt',
  `foreign_amount` decimal(15,2) DEFAULT NULL,
  `exchange_rate` decimal(15,4) NOT NULL DEFAULT 1.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items_shipment_payments`
--

INSERT INTO `items_shipment_payments` (`id`, `items_shipment_cost_id`, `amount`, `payment_date`, `payment_method`, `reference_no`, `notes`, `paid_by`, `user_id`, `shipment_bill_payment_id`, `created_at`, `updated_at`, `currency`, `foreign_amount`, `exchange_rate`) VALUES
(1, 4, 1140.00, '2026-10-26', 'cash', NULL, NULL, 'Admin User', 2, NULL, '2026-10-26 05:38:07', '2026-10-26 05:38:07', 'bdt', NULL, 1.0000),
(2, 2, 66500.00, '2026-10-26', 'bank_transfer', NULL, NULL, 'sajib', 2, NULL, '2026-10-26 05:39:10', '2026-10-26 05:39:10', 'rmb', 3500.00, 19.0000),
(3, 3, 7000.00, '2026-10-26', 'cheque', NULL, NULL, 'sajib', 2, NULL, '2026-10-26 05:39:33', '2026-10-26 05:39:33', 'bdt', NULL, 1.0000);

-- --------------------------------------------------------

--
-- Table structure for table `item_return_histories`
--

CREATE TABLE `item_return_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cargo_item_id` bigint(20) UNSIGNED NOT NULL,
  `items_shipment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `note` text DEFAULT NULL,
  `return_date` date NOT NULL,
  `previous_status` varchar(255) DEFAULT NULL,
  `returned_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_customers`
--

CREATE TABLE `legacy_customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `upload_id` bigint(20) UNSIGNED NOT NULL,
  `customer_code` varchar(30) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `aliases` text DEFAULT NULL,
  `receivers` text DEFAULT NULL,
  `unapplied_credit` decimal(14,2) NOT NULL DEFAULT 0.00,
  `client_dues_list` decimal(14,2) DEFAULT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_payments`
--

CREATE TABLE `legacy_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `upload_id` bigint(20) UNSIGNED NOT NULL,
  `customer_code` varchar(30) NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `shipment_no` varchar(30) NOT NULL,
  `payment_date` date DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL DEFAULT 'cash',
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `customer_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_rows`
--

CREATE TABLE `legacy_rows` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `upload_id` bigint(20) UNSIGNED NOT NULL,
  `old_row` int(10) UNSIGNED DEFAULT NULL,
  `delivered_at` date DEFAULT NULL,
  `customer_code` varchar(30) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `shipment_no` varchar(30) DEFAULT NULL,
  `warehouse` varchar(50) DEFAULT NULL,
  `shipping_mark` varchar(255) DEFAULT NULL,
  `tracking_no` varchar(100) DEFAULT NULL,
  `tracking_source` varchar(60) DEFAULT NULL,
  `goods_name_en` varchar(255) DEFAULT NULL,
  `pack_no` int(10) UNSIGNED DEFAULT NULL,
  `pack_note` varchar(50) DEFAULT NULL,
  `weight_kg` decimal(12,3) NOT NULL DEFAULT 0.000,
  `price_per_kg` decimal(10,2) DEFAULT NULL,
  `price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `receiver_name` varchar(255) DEFAULT NULL,
  `receiver_phone` varchar(30) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'delivered',
  `paid_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `name_as_written` varchar(255) DEFAULT NULL,
  `review_flag` text DEFAULT NULL,
  `issues` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`issues`)),
  `error_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `warning_count` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `import_status` varchar(20) NOT NULL DEFAULT 'staged',
  `cargo_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_uploads`
--

CREATE TABLE `legacy_uploads` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_hash` varchar(40) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'staged',
  `total_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `error_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `warning_rows` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_weight` decimal(14,3) NOT NULL DEFAULT 0.000,
  `total_billed` decimal(16,2) NOT NULL DEFAULT 0.00,
  `total_paid` decimal(16,2) NOT NULL DEFAULT 0.00,
  `source_totals` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_totals`)),
  `note` text DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `imported_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_11_18_054738_create_customers_table', 1),
(6, '2025_11_18_095741_create_agents_table', 1),
(7, '2025_11_19_105403_create_cost_types_table', 1),
(8, '2025_11_26_064709_create_cargo_items_table', 1),
(9, '2025_11_27_051910_create_items_shipments_table', 1),
(10, '2025_11_29_084623_items_shipment_costs', 1),
(11, '2025_11_29_084634_items_shipment_payments', 1),
(12, '2025_12_01_105703_create_customer_payments_table', 1),
(13, '2025_12_02_043543_cargo_item_payments', 1),
(14, '2026_09_25_113126_create_customer_extra_shipping_marks_table', 1),
(15, '2026_09_25_160009_create_imported_excel_files_table', 1),
(16, '2026_09_26_071038_create_item_return_histories_table', 1),
(17, '2026_09_26_180715_create_invoices_table', 1),
(18, '2026_10_07_000001_create_legacy_tables', 1),
(19, '2026_10_07_000002_add_receiver_columns_to_cargo_items', 1),
(20, '2026_10_07_154116_create_roles_table', 1),
(21, '2026_10_07_155218_create_permissions_table', 1),
(22, '2026_10_08_000003_create_role_permission_table', 1),
(23, '2026_10_12_000001_add_currency_fields_to_items_shipment_costs_table', 1),
(24, '2026_10_12_000002_create_items_shipment_cost_attachments_table', 1),
(25, '2026_10_12_173209_create_offices_table', 1),
(26, '2026_10_12_173240_create_employees_table', 1),
(27, '2026_10_12_173257_create_office_bills_table', 1),
(28, '2026_10_12_173549_create_warehouse_bills_table', 1),
(29, '2026_10_12_175530_create_monthly_summaries_table', 1),
(30, '2026_10_13_000001_create_customer_payment_allocations_table', 1),
(31, '2026_10_13_000010_add_receipt_no_and_text_pack_no_to_cargo_items_table', 1),
(32, '2026_10_15_000001_create_old_shipments_table', 1),
(33, '2026_10_15_000002_create_old_customers_table', 1),
(34, '2026_10_15_000003_create_old_shipment_items_table', 1),
(35, '2026_10_15_000004_create_old_shipment_bills_table', 1),
(36, '2026_10_15_000005_create_old_payment_and_receives_table', 1),
(37, '2026_10_16_000001_create_shipment_bill_payments_and_user_tracking', 1),
(38, '2026_10_17_000001_create_receipt_status_notifications_table', 1),
(39, '2026_10_21_000001_create_ctn_sequences_table', 1),
(40, '2026_10_21_000002_add_cancel_and_box_to_cargo_items_table', 1),
(41, '2026_10_21_000003_multi_office_bills_salary_records_summaries', 1),
(42, '2026_10_22_000001_third_parties_status_logs_payment_currency', 1),
(43, '2026_10_23_000001_normalize_payment_methods', 1),
(44, '2026_10_23_000002_partner_customers_and_third_party_items', 1),
(45, '2026_10_24_000001_create_settings_table', 1),
(46, '2026_10_8_000005_add_role_id_to_users_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `monthly_summaries`
--

CREATE TABLE `monthly_summaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` year(4) NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `office_bills_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `salaries_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `warehouse_bills_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `default_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `cost_per_kg` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `office_bills`
--

CREATE TABLE `office_bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `office_id` bigint(20) UNSIGNED NOT NULL,
  `bill_type` varchar(80) DEFAULT NULL,
  `bill_date` date DEFAULT NULL,
  `year` year(4) NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `old_customers`
--

CREATE TABLE `old_customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `old_payment_and_receives`
--

CREATE TABLE `old_payment_and_receives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('paid','received') NOT NULL,
  `old_shipment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(14,2) NOT NULL,
  `date` date NOT NULL,
  `method` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `old_shipments`
--

CREATE TABLE `old_shipments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `shipping_mark` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `old_shipment_bills`
--

CREATE TABLE `old_shipment_bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `old_shipment_id` bigint(20) UNSIGNED NOT NULL,
  `cost_type` varchar(255) NOT NULL,
  `total_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `old_shipment_items`
--

CREATE TABLE `old_shipment_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `old_shipment_id` bigint(20) UNSIGNED NOT NULL,
  `old_customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `weight` decimal(12,3) NOT NULL DEFAULT 0.000,
  `ctn_no` varchar(255) DEFAULT NULL,
  `cbm` decimal(12,3) NOT NULL DEFAULT 0.000,
  `remark` varchar(255) DEFAULT NULL,
  `shipping_mark` varchar(255) DEFAULT NULL,
  `price_per_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `final_price` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL DEFAULT 'General',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `group_name`, `created_at`, `updated_at`) VALUES
(1, 'View Dashboard', 'view-dashboard', 'Dashboard', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(2, 'View Users', 'view-users', 'User Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(3, 'Create Users', 'create-users', 'User Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(4, 'Edit Users', 'edit-users', 'User Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(5, 'Delete Users', 'delete-users', 'User Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(6, 'Toggle User Status', 'toggle-user-status', 'User Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(7, 'View Roles', 'view-roles', 'Role Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(8, 'Create Roles', 'create-roles', 'Role Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(9, 'Edit Roles', 'edit-roles', 'Role Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(10, 'Delete Roles', 'delete-roles', 'Role Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(11, 'Toggle Role Status', 'toggle-role-status', 'Role Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(12, 'View Permissions', 'view-permissions', 'Permission Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(13, 'Create Permissions', 'create-permissions', 'Permission Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(14, 'Edit Permissions', 'edit-permissions', 'Permission Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(15, 'Delete Permissions', 'delete-permissions', 'Permission Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(16, 'Bulk Create Permissions', 'bulk-create-permissions', 'Permission Management', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(17, 'View Customers', 'view-customers', 'Customer', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(18, 'Create Customers', 'create-customers', 'Customer', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(19, 'Edit Customers', 'edit-customers', 'Customer', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(20, 'Delete Customers', 'delete-customers', 'Customer', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(21, 'Manage Partners', 'manage-partners', 'Customer', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(22, 'Assign Customers', 'assign-customers', 'Customer', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(23, 'Manage Warehouse', 'manage-warehouse', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(24, 'View Warehouse', 'view-warehouse', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(25, 'Add Warehouse Item', 'add-warehouse-item', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(26, 'Edit Warehouse Item', 'edit-warehouse-item', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(27, 'Delete Warehouse Item', 'delete-warehouse-item', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(28, 'View Warehouse Item', 'view-warehouse-item', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(29, 'Import Warehouse File', 'import-warehouse-file', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(30, 'Cancel Cargo Item', 'cancel-cargo-item', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(31, 'Restore Cargo Item', 'restore-cargo-item', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(32, 'Mark Third Party', 'mark-third-party', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(33, 'Manage Box', 'manage-box', 'Warehouse', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(34, 'View Shipments', 'view-shipments', 'Shipment', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(35, 'Create Shipments', 'create-shipments', 'Shipment', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(36, 'Edit Shipments', 'edit-shipments', 'Shipment', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(37, 'Delete Shipments', 'delete-shipments', 'Shipment', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(38, 'Shipment Report', 'shipment-report', 'Shipment', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(39, 'Shipment Summary', 'shipment-summary', 'Shipment', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(40, 'View Shipment Cost', 'view-shipment-cost', 'Shipment Cost', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(41, 'Add Shipment Cost', 'add-shipment-cost', 'Shipment Cost', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(42, 'Edit Shipment Cost', 'edit-shipment-cost', 'Shipment Cost', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(43, 'Delete Shipment Cost', 'delete-shipment-cost', 'Shipment Cost', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(44, 'Add Bill Payment', 'add-bill-payment', 'Shipment Cost', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(45, 'Delete Bill Payment', 'delete-bill-payment', 'Shipment Cost', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(46, 'View CTN', 'view-ctn', 'Carton', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(47, 'Edit CTN', 'edit-ctn', 'Carton', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(48, 'Print CTN', 'print-ctn', 'Carton', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(49, 'Auto Assign CTN', 'auto-assign-ctn', 'Carton', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(50, 'View Price', 'view-price', 'Price', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(51, 'Edit Price', 'edit-price', 'Price', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(52, 'Manage Shipment Status', 'manage-shipment-status', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(53, 'Status Ongoing', 'status-ongoing', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(54, 'Status Shipped', 'status-shipped', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(55, 'Status Arrived BD', 'status-arrived-bd', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(56, 'Status Waiting For Custom', 'status-waiting-for-custom', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(57, 'Status Delivered', 'status-delivered', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(58, 'Status Cancelled', 'status-cancelled', 'Shipment Status', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(59, 'View Invoices', 'view-invoices', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(60, 'Create Invoices', 'create-invoices', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(61, 'Edit Invoices', 'edit-invoices', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(62, 'Delete Invoices', 'delete-invoices', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(63, 'View Payment History', 'view-payment-history', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(64, 'Add Payment', 'add-payment', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(65, 'Edit Payment', 'edit-payment', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(66, 'Delete Payment', 'delete-payment', 'Billing', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(67, 'View Returns', 'view-returns', 'Return', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(68, 'Add Returns', 'add-returns', 'Return', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(69, 'Edit Returns', 'edit-returns', 'Return', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(70, 'Delete Returns', 'delete-returns', 'Return', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(71, 'View Shipping Mark', 'view-shipping-mark', 'Shipping Mark', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(72, 'Add Shipping Mark', 'add-shipping-mark', 'Shipping Mark', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(73, 'Edit Shipping Mark', 'edit-shipping-mark', 'Shipping Mark', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(74, 'Delete Shipping Mark', 'delete-shipping-mark', 'Shipping Mark', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(75, 'View Agents', 'view-agents', 'Agents', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(76, 'Add Agents', 'add-agents', 'Agents', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(77, 'Edit Agents', 'edit-agents', 'Agents', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(78, 'Delete Agents', 'delete-agents', 'Agents', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(79, 'View Cost Types', 'view-cost-types', 'Cost Type', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(80, 'Add Cost Types', 'add-cost-types', 'Cost Type', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(81, 'Edit Cost Types', 'edit-cost-types', 'Cost Type', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(82, 'Delete Cost Types', 'delete-cost-types', 'Cost Type', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(83, 'View Old Data', 'view-old-data', 'Old Data', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(84, 'Add Old Data', 'add-old-data', 'Old Data', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(85, 'Edit Old Data', 'edit-old-data', 'Old Data', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(86, 'Delete Old Data', 'delete-old-data', 'Old Data', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(87, 'Manage Old Data', 'manage-old-data', 'Old Data', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(88, 'View Notifications', 'view-notifications', 'Customer Notification', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(89, 'Manage Notifications', 'manage-notifications', 'Customer Notification', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(90, 'View Office Expense', 'view-office-expense', 'Office Expense', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(91, 'Manage Offices', 'manage-offices', 'Office Expense', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(92, 'Manage Employees', 'manage-employees', 'Office Expense', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(93, 'Manage Office Bills', 'manage-office-bills', 'Office Expense', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(94, 'Manage Warehouse Bills', 'manage-warehouse-bills', 'Office Expense', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(95, 'View Salary History', 'view-salary-history', 'Office Expense', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(96, 'View Customer Ledger', 'view-customer-ledger', 'Ledger', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(97, 'View Agent Ledger', 'view-agent-ledger', 'Ledger', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(98, 'View Shipment Ledger', 'view-shipment-ledger', 'Ledger', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(99, 'Export Ledger', 'export-ledger', 'Ledger', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(100, 'View Final Report', 'view-final-report', 'Reports', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(101, 'View Business Summary', 'view-business-summary', 'Reports', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(102, 'View Period Report', 'view-period-report', 'Reports', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(103, 'View Customer Report', 'view-customer-report', 'Reports', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(104, 'View Shipment Report', 'view-shipment-report', 'Reports', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(105, 'View Office Report', 'view-office-report', 'Reports', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(106, 'Export Reports', 'export-reports', 'Reports', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(107, 'View Legacy Upload', 'view-legacy-upload', 'Legacy Upload', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(108, 'Manage Legacy Upload', 'manage-legacy-upload', 'Legacy Upload', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(109, 'View Settings', 'view-settings', 'Settings', '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(110, 'Edit Settings', 'edit-settings', 'Settings', '2026-10-25 13:47:10', '2026-10-25 13:47:10');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipt_status_notifications`
--

CREATE TABLE `receipt_status_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `receipt_no` varchar(50) NOT NULL,
  `status` varchar(40) NOT NULL,
  `is_notified` tinyint(1) NOT NULL DEFAULT 0,
  `notified_at` timestamp NULL DEFAULT NULL,
  `notified_by` bigint(20) UNSIGNED DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin', 'Full access to everything. Cannot be restricted.', 1, '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(2, 'Admin', 'admin', 'Administrative access. Manages users, warehouse, shipments, billing.', 1, '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(3, 'Manager', 'manager', 'Operational access. Handles daily shipment, warehouse, and billing tasks.', 1, '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(4, 'Account Manager', 'account-manager', NULL, 1, '2026-10-26 06:37:15', '2026-10-26 06:37:15');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, 76, NULL, NULL),
(2, 1, 44, NULL, NULL),
(3, 1, 80, NULL, NULL),
(4, 1, 84, NULL, NULL),
(5, 1, 64, NULL, NULL),
(6, 1, 68, NULL, NULL),
(7, 1, 41, NULL, NULL),
(8, 1, 72, NULL, NULL),
(9, 1, 25, NULL, NULL),
(10, 1, 22, NULL, NULL),
(11, 1, 49, NULL, NULL),
(12, 1, 16, NULL, NULL),
(13, 1, 30, NULL, NULL),
(14, 1, 18, NULL, NULL),
(15, 1, 60, NULL, NULL),
(16, 1, 13, NULL, NULL),
(17, 1, 8, NULL, NULL),
(18, 1, 35, NULL, NULL),
(19, 1, 3, NULL, NULL),
(20, 1, 78, NULL, NULL),
(21, 1, 45, NULL, NULL),
(22, 1, 82, NULL, NULL),
(23, 1, 20, NULL, NULL),
(24, 1, 62, NULL, NULL),
(25, 1, 86, NULL, NULL),
(26, 1, 66, NULL, NULL),
(27, 1, 15, NULL, NULL),
(28, 1, 70, NULL, NULL),
(29, 1, 10, NULL, NULL),
(30, 1, 43, NULL, NULL),
(31, 1, 37, NULL, NULL),
(32, 1, 74, NULL, NULL),
(33, 1, 5, NULL, NULL),
(34, 1, 27, NULL, NULL),
(35, 1, 77, NULL, NULL),
(36, 1, 81, NULL, NULL),
(37, 1, 47, NULL, NULL),
(38, 1, 19, NULL, NULL),
(39, 1, 61, NULL, NULL),
(40, 1, 85, NULL, NULL),
(41, 1, 65, NULL, NULL),
(42, 1, 14, NULL, NULL),
(43, 1, 51, NULL, NULL),
(44, 1, 69, NULL, NULL),
(45, 1, 9, NULL, NULL),
(46, 1, 110, NULL, NULL),
(47, 1, 42, NULL, NULL),
(48, 1, 36, NULL, NULL),
(49, 1, 73, NULL, NULL),
(50, 1, 4, NULL, NULL),
(51, 1, 26, NULL, NULL),
(52, 1, 99, NULL, NULL),
(53, 1, 106, NULL, NULL),
(54, 1, 29, NULL, NULL),
(55, 1, 33, NULL, NULL),
(56, 1, 92, NULL, NULL),
(57, 1, 108, NULL, NULL),
(58, 1, 89, NULL, NULL),
(59, 1, 93, NULL, NULL),
(60, 1, 91, NULL, NULL),
(61, 1, 87, NULL, NULL),
(62, 1, 21, NULL, NULL),
(63, 1, 52, NULL, NULL),
(64, 1, 23, NULL, NULL),
(65, 1, 94, NULL, NULL),
(66, 1, 32, NULL, NULL),
(67, 1, 48, NULL, NULL),
(68, 1, 31, NULL, NULL),
(69, 1, 38, NULL, NULL),
(70, 1, 39, NULL, NULL),
(71, 1, 55, NULL, NULL),
(72, 1, 58, NULL, NULL),
(73, 1, 57, NULL, NULL),
(74, 1, 53, NULL, NULL),
(75, 1, 54, NULL, NULL),
(76, 1, 56, NULL, NULL),
(77, 1, 11, NULL, NULL),
(78, 1, 6, NULL, NULL),
(79, 1, 97, NULL, NULL),
(80, 1, 75, NULL, NULL),
(81, 1, 101, NULL, NULL),
(82, 1, 79, NULL, NULL),
(83, 1, 46, NULL, NULL),
(84, 1, 96, NULL, NULL),
(85, 1, 103, NULL, NULL),
(86, 1, 17, NULL, NULL),
(87, 1, 1, NULL, NULL),
(88, 1, 100, NULL, NULL),
(89, 1, 59, NULL, NULL),
(90, 1, 107, NULL, NULL),
(91, 1, 88, NULL, NULL),
(92, 1, 90, NULL, NULL),
(93, 1, 105, NULL, NULL),
(94, 1, 83, NULL, NULL),
(95, 1, 63, NULL, NULL),
(96, 1, 102, NULL, NULL),
(97, 1, 12, NULL, NULL),
(98, 1, 50, NULL, NULL),
(99, 1, 67, NULL, NULL),
(100, 1, 7, NULL, NULL),
(101, 1, 95, NULL, NULL),
(102, 1, 109, NULL, NULL),
(103, 1, 40, NULL, NULL),
(104, 1, 98, NULL, NULL),
(105, 1, 104, NULL, NULL),
(106, 1, 34, NULL, NULL),
(107, 1, 71, NULL, NULL),
(108, 1, 2, NULL, NULL),
(109, 1, 24, NULL, NULL),
(110, 1, 28, NULL, NULL),
(111, 2, 76, NULL, NULL),
(112, 2, 44, NULL, NULL),
(113, 2, 80, NULL, NULL),
(114, 2, 84, NULL, NULL),
(115, 2, 64, NULL, NULL),
(116, 2, 68, NULL, NULL),
(117, 2, 41, NULL, NULL),
(118, 2, 72, NULL, NULL),
(119, 2, 25, NULL, NULL),
(120, 2, 22, NULL, NULL),
(121, 2, 49, NULL, NULL),
(122, 2, 16, NULL, NULL),
(123, 2, 30, NULL, NULL),
(124, 2, 18, NULL, NULL),
(125, 2, 60, NULL, NULL),
(126, 2, 13, NULL, NULL),
(127, 2, 8, NULL, NULL),
(128, 2, 35, NULL, NULL),
(129, 2, 3, NULL, NULL),
(130, 2, 78, NULL, NULL),
(131, 2, 45, NULL, NULL),
(132, 2, 82, NULL, NULL),
(133, 2, 20, NULL, NULL),
(134, 2, 62, NULL, NULL),
(135, 2, 86, NULL, NULL),
(136, 2, 66, NULL, NULL),
(137, 2, 15, NULL, NULL),
(138, 2, 70, NULL, NULL),
(139, 2, 10, NULL, NULL),
(140, 2, 43, NULL, NULL),
(141, 2, 37, NULL, NULL),
(142, 2, 74, NULL, NULL),
(143, 2, 5, NULL, NULL),
(144, 2, 27, NULL, NULL),
(145, 2, 77, NULL, NULL),
(146, 2, 81, NULL, NULL),
(147, 2, 47, NULL, NULL),
(148, 2, 19, NULL, NULL),
(149, 2, 61, NULL, NULL),
(150, 2, 85, NULL, NULL),
(151, 2, 65, NULL, NULL),
(152, 2, 14, NULL, NULL),
(153, 2, 51, NULL, NULL),
(154, 2, 69, NULL, NULL),
(155, 2, 9, NULL, NULL),
(156, 2, 110, NULL, NULL),
(157, 2, 42, NULL, NULL),
(158, 2, 36, NULL, NULL),
(159, 2, 73, NULL, NULL),
(160, 2, 4, NULL, NULL),
(161, 2, 26, NULL, NULL),
(162, 2, 99, NULL, NULL),
(163, 2, 106, NULL, NULL),
(164, 2, 29, NULL, NULL),
(165, 2, 33, NULL, NULL),
(166, 2, 92, NULL, NULL),
(167, 2, 108, NULL, NULL),
(168, 2, 89, NULL, NULL),
(169, 2, 93, NULL, NULL),
(170, 2, 91, NULL, NULL),
(171, 2, 87, NULL, NULL),
(172, 2, 21, NULL, NULL),
(173, 2, 52, NULL, NULL),
(174, 2, 23, NULL, NULL),
(175, 2, 94, NULL, NULL),
(176, 2, 32, NULL, NULL),
(177, 2, 48, NULL, NULL),
(178, 2, 31, NULL, NULL),
(179, 2, 38, NULL, NULL),
(180, 2, 39, NULL, NULL),
(181, 2, 55, NULL, NULL),
(182, 2, 58, NULL, NULL),
(183, 2, 57, NULL, NULL),
(184, 2, 53, NULL, NULL),
(185, 2, 54, NULL, NULL),
(186, 2, 56, NULL, NULL),
(187, 2, 11, NULL, NULL),
(188, 2, 6, NULL, NULL),
(189, 2, 97, NULL, NULL),
(190, 2, 75, NULL, NULL),
(191, 2, 101, NULL, NULL),
(192, 2, 79, NULL, NULL),
(193, 2, 46, NULL, NULL),
(194, 2, 96, NULL, NULL),
(195, 2, 103, NULL, NULL),
(196, 2, 17, NULL, NULL),
(197, 2, 1, NULL, NULL),
(198, 2, 100, NULL, NULL),
(199, 2, 59, NULL, NULL),
(200, 2, 107, NULL, NULL),
(201, 2, 88, NULL, NULL),
(202, 2, 90, NULL, NULL),
(203, 2, 105, NULL, NULL),
(204, 2, 83, NULL, NULL),
(205, 2, 63, NULL, NULL),
(206, 2, 102, NULL, NULL),
(207, 2, 12, NULL, NULL),
(208, 2, 50, NULL, NULL),
(209, 2, 67, NULL, NULL),
(210, 2, 7, NULL, NULL),
(211, 2, 95, NULL, NULL),
(212, 2, 109, NULL, NULL),
(213, 2, 40, NULL, NULL),
(214, 2, 98, NULL, NULL),
(215, 2, 104, NULL, NULL),
(216, 2, 34, NULL, NULL),
(217, 2, 71, NULL, NULL),
(218, 2, 2, NULL, NULL),
(219, 2, 24, NULL, NULL),
(220, 2, 28, NULL, NULL),
(221, 3, 1, NULL, NULL),
(222, 3, 17, NULL, NULL),
(223, 3, 18, NULL, NULL),
(224, 3, 19, NULL, NULL),
(225, 3, 20, NULL, NULL),
(226, 3, 21, NULL, NULL),
(227, 3, 22, NULL, NULL),
(228, 3, 23, NULL, NULL),
(229, 3, 24, NULL, NULL),
(230, 3, 25, NULL, NULL),
(231, 3, 26, NULL, NULL),
(232, 3, 27, NULL, NULL),
(233, 3, 28, NULL, NULL),
(234, 3, 29, NULL, NULL),
(235, 3, 30, NULL, NULL),
(236, 3, 31, NULL, NULL),
(237, 3, 32, NULL, NULL),
(238, 3, 33, NULL, NULL),
(239, 3, 34, NULL, NULL),
(240, 3, 35, NULL, NULL),
(241, 3, 36, NULL, NULL),
(242, 3, 37, NULL, NULL),
(243, 3, 38, NULL, NULL),
(244, 3, 39, NULL, NULL),
(245, 3, 40, NULL, NULL),
(246, 3, 41, NULL, NULL),
(247, 3, 42, NULL, NULL),
(248, 3, 43, NULL, NULL),
(249, 3, 44, NULL, NULL),
(250, 3, 45, NULL, NULL),
(251, 3, 46, NULL, NULL),
(252, 3, 47, NULL, NULL),
(253, 3, 48, NULL, NULL),
(254, 3, 49, NULL, NULL),
(255, 3, 50, NULL, NULL),
(256, 3, 51, NULL, NULL),
(257, 3, 52, NULL, NULL),
(258, 3, 53, NULL, NULL),
(259, 3, 54, NULL, NULL),
(260, 3, 55, NULL, NULL),
(261, 3, 56, NULL, NULL),
(262, 3, 57, NULL, NULL),
(263, 3, 58, NULL, NULL),
(264, 3, 59, NULL, NULL),
(265, 3, 60, NULL, NULL),
(266, 3, 61, NULL, NULL),
(267, 3, 62, NULL, NULL),
(268, 3, 63, NULL, NULL),
(269, 3, 64, NULL, NULL),
(270, 3, 65, NULL, NULL),
(271, 3, 66, NULL, NULL),
(272, 3, 67, NULL, NULL),
(273, 3, 68, NULL, NULL),
(274, 3, 69, NULL, NULL),
(275, 3, 70, NULL, NULL),
(276, 3, 71, NULL, NULL),
(277, 3, 72, NULL, NULL),
(278, 3, 73, NULL, NULL),
(279, 3, 74, NULL, NULL),
(280, 3, 75, NULL, NULL),
(281, 3, 76, NULL, NULL),
(282, 3, 77, NULL, NULL),
(283, 3, 78, NULL, NULL),
(284, 3, 79, NULL, NULL),
(285, 3, 80, NULL, NULL),
(286, 3, 81, NULL, NULL),
(287, 3, 82, NULL, NULL),
(288, 3, 88, NULL, NULL),
(289, 3, 89, NULL, NULL),
(290, 3, 90, NULL, NULL),
(291, 3, 91, NULL, NULL),
(292, 3, 92, NULL, NULL),
(293, 3, 93, NULL, NULL),
(294, 3, 94, NULL, NULL),
(295, 3, 95, NULL, NULL),
(296, 3, 96, NULL, NULL),
(297, 3, 97, NULL, NULL),
(298, 3, 98, NULL, NULL),
(299, 3, 99, NULL, NULL),
(300, 3, 100, NULL, NULL),
(301, 3, 101, NULL, NULL),
(302, 3, 102, NULL, NULL),
(303, 3, 103, NULL, NULL),
(304, 3, 104, NULL, NULL),
(305, 3, 105, NULL, NULL),
(306, 3, 106, NULL, NULL),
(307, 4, 76, NULL, NULL),
(308, 4, 77, NULL, NULL),
(309, 4, 75, NULL, NULL),
(310, 4, 64, NULL, NULL),
(311, 4, 60, NULL, NULL),
(312, 4, 62, NULL, NULL),
(313, 4, 66, NULL, NULL),
(314, 4, 61, NULL, NULL),
(315, 4, 65, NULL, NULL),
(316, 4, 59, NULL, NULL),
(317, 4, 63, NULL, NULL),
(318, 4, 80, NULL, NULL),
(319, 4, 82, NULL, NULL),
(320, 4, 81, NULL, NULL),
(321, 4, 79, NULL, NULL),
(322, 4, 89, NULL, NULL),
(323, 4, 88, NULL, NULL),
(324, 4, 1, NULL, NULL),
(325, 4, 99, NULL, NULL),
(326, 4, 97, NULL, NULL),
(327, 4, 96, NULL, NULL),
(328, 4, 98, NULL, NULL),
(329, 4, 92, NULL, NULL),
(330, 4, 93, NULL, NULL),
(331, 4, 91, NULL, NULL),
(332, 4, 94, NULL, NULL),
(333, 4, 90, NULL, NULL),
(334, 4, 95, NULL, NULL),
(335, 4, 51, NULL, NULL),
(336, 4, 50, NULL, NULL),
(337, 4, 106, NULL, NULL),
(338, 4, 101, NULL, NULL),
(339, 4, 103, NULL, NULL),
(340, 4, 100, NULL, NULL),
(341, 4, 105, NULL, NULL),
(342, 4, 102, NULL, NULL),
(343, 4, 104, NULL, NULL),
(344, 4, 38, NULL, NULL),
(345, 4, 39, NULL, NULL),
(346, 4, 34, NULL, NULL),
(347, 4, 44, NULL, NULL),
(348, 4, 41, NULL, NULL),
(349, 4, 45, NULL, NULL),
(350, 4, 43, NULL, NULL),
(351, 4, 42, NULL, NULL),
(352, 4, 40, NULL, NULL),
(353, 4, 52, NULL, NULL),
(354, 4, 55, NULL, NULL),
(355, 4, 58, NULL, NULL),
(356, 4, 57, NULL, NULL),
(357, 4, 53, NULL, NULL),
(358, 4, 54, NULL, NULL),
(359, 4, 56, NULL, NULL),
(360, 4, 72, NULL, NULL),
(361, 4, 73, NULL, NULL),
(362, 4, 71, NULL, NULL),
(363, 4, 25, NULL, NULL),
(364, 4, 30, NULL, NULL),
(365, 4, 26, NULL, NULL),
(366, 4, 29, NULL, NULL),
(367, 4, 33, NULL, NULL),
(368, 4, 23, NULL, NULL),
(369, 4, 32, NULL, NULL),
(370, 4, 31, NULL, NULL),
(371, 4, 24, NULL, NULL),
(372, 4, 28, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `salary_records`
--

CREATE TABLE `salary_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `year` smallint(5) UNSIGNED NOT NULL,
  `month` tinyint(3) UNSIGNED NOT NULL,
  `employee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `employee_name` varchar(255) NOT NULL,
  `office_id` bigint(20) UNSIGNED DEFAULT NULL,
  `office_name` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `created_at`, `updated_at`) VALUES
(1, 'site_title', 'Techlab33 - Custom Software', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(2, 'site_tagline', 'Custom Software', '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(3, 'company_name', 'Jamalpur Associates', '2026-10-25 13:47:09', '2026-10-26 05:52:31'),
(4, 'company_address', 'House-23, Road-9/B, Sector-5, Uttara, Dhaka, Bangladesh', '2026-10-25 13:47:09', '2026-10-26 05:52:31'),
(5, 'company_phone', '+880 1717 941256', '2026-10-25 13:47:09', '2026-10-26 05:52:31'),
(6, 'company_email', 'info@jamalpurassociates.com', '2026-10-25 13:47:09', '2026-10-26 05:52:31'),
(7, 'footer_text', NULL, '2026-10-25 13:47:09', '2026-10-25 13:47:09'),
(8, 'logo', 'settings/0ceIrgDKMYXVg0tO7KxbKKjE8YglYFarQYxbXTwy.png', '2026-10-25 13:47:09', '2026-10-26 15:48:40'),
(9, 'logo_mini', 'settings/AJ1kZRaxr35wFBIfUJKrmZmLZxciB5LRGX06OF9h.png', '2026-10-25 13:47:09', '2026-10-26 15:48:40'),
(10, 'favicon', NULL, '2026-10-25 13:47:09', '2026-10-25 13:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `shipment_bill_payments`
--

CREATE TABLE `shipment_bill_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_no` varchar(30) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `agent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'bdt',
  `foreign_amount` decimal(15,2) DEFAULT NULL,
  `exchange_rate` decimal(15,4) NOT NULL DEFAULT 1.0000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `third_parties`
--

CREATE TABLE `third_parties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(40) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `third_party_payments`
--

CREATE TABLE `third_party_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `third_party_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'bdt',
  `foreign_amount` decimal(15,2) DEFAULT NULL,
  `exchange_rate` decimal(15,4) NOT NULL DEFAULT 1.0000,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) NOT NULL DEFAULT 'cash',
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` tinyint(3) UNSIGNED NOT NULL DEFAULT 1 COMMENT '1 for user, 2 for admin',
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `role`, `role_id`, `email_verified_at`, `password`, `image`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@admin.com', 2, 1, NULL, '$2y$12$Vxv6v2pgKHLFU8TdS9s8huxCjMXnMjGWRQf8E41q9hMHgZmZo05KK', NULL, 1, NULL, '2026-10-25 13:47:10', '2026-10-25 13:47:10'),
(2, 'Admin User', 'admin@admin.com', 2, 2, NULL, '$2y$12$a2K7u0KsyHoKFUUiXFP9BODjoZFohuNbJum.sia9vB1cFJoxQibDC', NULL, 1, NULL, '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(3, 'Manager', 'manager@admin.com', 1, 3, NULL, '$2y$12$xg/OMKP3BMeH3r2tsvXl3.PM3e2lvvYekFWaBr3lwjIK.QOW/Yx0G', NULL, 1, NULL, '2026-10-25 13:47:11', '2026-10-25 13:47:11'),
(4, 'Account Manager', 'account@admin.com', 1, 3, NULL, '$2y$12$UtSLhixFfCN1k8gg.ObZ2ezoe66NVDL6c7yDtQfffgnhWujHsmyOO', '1792996407_6adef4379a534.jpeg', 1, NULL, '2026-10-26 06:32:53', '2026-10-26 06:33:27');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_bills`
--

CREATE TABLE `warehouse_bills` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `office_id` bigint(20) UNSIGNED NOT NULL,
  `kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `rate_per_kg` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `bill_date` date NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cargo_items`
--
ALTER TABLE `cargo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo_items_customer_id_foreign` (`customer_id`),
  ADD KEY `cargo_items_box_no_index` (`box_no`),
  ADD KEY `cargo_items_third_party_id_foreign` (`third_party_id`);

--
-- Indexes for table `cargo_item_payments`
--
ALTER TABLE `cargo_item_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo_item_payments_customer_payment_id_foreign` (`customer_payment_id`),
  ADD KEY `cargo_item_payments_cargo_item_id_foreign` (`cargo_item_id`);

--
-- Indexes for table `cargo_item_shipment`
--
ALTER TABLE `cargo_item_shipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo_item_shipment_cargo_item_id_foreign` (`cargo_item_id`),
  ADD KEY `cargo_item_shipment_items_shipment_id_foreign` (`items_shipment_id`);

--
-- Indexes for table `cargo_item_status_logs`
--
ALTER TABLE `cargo_item_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cargo_item_status_logs_cargo_item_id_foreign` (`cargo_item_id`),
  ADD KEY `cargo_item_status_logs_user_id_foreign` (`user_id`),
  ADD KEY `cargo_item_status_logs_status_changed_at_index` (`status`,`changed_at`);

--
-- Indexes for table `cost_types`
--
ALTER TABLE `cost_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ctn_sequences`
--
ALTER TABLE `ctn_sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ctn_sequences_prefix_unique` (`prefix`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_email_unique` (`email`),
  ADD UNIQUE KEY `customers_phone_unique` (`phone`),
  ADD UNIQUE KEY `customers_customer_code_unique` (`customer_code`);

--
-- Indexes for table `customer_extra_shipping_marks`
--
ALTER TABLE `customer_extra_shipping_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_extra_shipping_marks_customer_id_shipping_mark_unique` (`customer_id`,`shipping_mark`),
  ADD KEY `customer_extra_shipping_marks_shipping_mark_index` (`shipping_mark`);

--
-- Indexes for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_payments_customer_id_foreign` (`customer_id`),
  ADD KEY `customer_payments_itemshipment_id_foreign` (`itemshipment_id`),
  ADD KEY `customer_payments_user_id_foreign` (`user_id`);

--
-- Indexes for table `customer_payment_allocations`
--
ALTER TABLE `customer_payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_payment_allocations_customer_payment_id_foreign` (`customer_payment_id`),
  ADD KEY `cpa_shipment_payment_idx` (`itemshipment_id`,`customer_payment_id`);

--
-- Indexes for table `customer_payment_items`
--
ALTER TABLE `customer_payment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_payment_items_customer_payment_id_foreign` (`customer_payment_id`),
  ADD KEY `customer_payment_items_cargo_item_id_foreign` (`cargo_item_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employees_office_id_foreign` (`office_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `imported_excel_files`
--
ALTER TABLE `imported_excel_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `imported_excel_files_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoices_customer_id_itemshipment_id_unique` (`customer_id`,`itemshipment_id`),
  ADD UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
  ADD KEY `invoices_itemshipment_id_foreign` (`itemshipment_id`);

--
-- Indexes for table `items_shipments`
--
ALTER TABLE `items_shipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items_shipment_costs`
--
ALTER TABLE `items_shipment_costs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `items_shipment_costs_items_shipment_id_foreign` (`items_shipment_id`),
  ADD KEY `items_shipment_costs_cost_type_id_foreign` (`cost_type_id`),
  ADD KEY `items_shipment_costs_agent_id_foreign` (`agent_id`);

--
-- Indexes for table `items_shipment_cost_attachments`
--
ALTER TABLE `items_shipment_cost_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `items_shipment_cost_attachments_items_shipment_cost_id_foreign` (`items_shipment_cost_id`);

--
-- Indexes for table `items_shipment_payments`
--
ALTER TABLE `items_shipment_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `items_shipment_payments_items_shipment_cost_id_foreign` (`items_shipment_cost_id`),
  ADD KEY `items_shipment_payments_user_id_foreign` (`user_id`),
  ADD KEY `items_shipment_payments_shipment_bill_payment_id_foreign` (`shipment_bill_payment_id`);

--
-- Indexes for table `item_return_histories`
--
ALTER TABLE `item_return_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_return_histories_items_shipment_id_foreign` (`items_shipment_id`),
  ADD KEY `item_return_histories_returned_by_foreign` (`returned_by`),
  ADD KEY `item_return_histories_cargo_item_id_index` (`cargo_item_id`),
  ADD KEY `item_return_histories_return_date_index` (`return_date`);

--
-- Indexes for table `legacy_customers`
--
ALTER TABLE `legacy_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_customers_upload_id_customer_code_unique` (`upload_id`,`customer_code`);

--
-- Indexes for table `legacy_payments`
--
ALTER TABLE `legacy_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `legacy_payments_upload_id_customer_code_index` (`upload_id`,`customer_code`);

--
-- Indexes for table `legacy_rows`
--
ALTER TABLE `legacy_rows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `legacy_rows_upload_id_shipment_no_index` (`upload_id`,`shipment_no`),
  ADD KEY `legacy_rows_upload_id_customer_code_index` (`upload_id`,`customer_code`),
  ADD KEY `legacy_rows_upload_id_tracking_no_index` (`upload_id`,`tracking_no`);

--
-- Indexes for table `legacy_uploads`
--
ALTER TABLE `legacy_uploads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `legacy_uploads_file_hash_unique` (`file_hash`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monthly_summaries`
--
ALTER TABLE `monthly_summaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `monthly_summaries_year_month_idx` (`year`,`month`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office_bills`
--
ALTER TABLE `office_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_bills_office_id_idx` (`office_id`),
  ADD KEY `office_bills_year_month_index` (`year`,`month`);

--
-- Indexes for table `old_customers`
--
ALTER TABLE `old_customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `old_payment_and_receives`
--
ALTER TABLE `old_payment_and_receives`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_payment_and_receives_old_shipment_id_foreign` (`old_shipment_id`),
  ADD KEY `old_payment_and_receives_old_customer_id_foreign` (`old_customer_id`);

--
-- Indexes for table `old_shipments`
--
ALTER TABLE `old_shipments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `old_shipment_bills`
--
ALTER TABLE `old_shipment_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_shipment_bills_old_shipment_id_foreign` (`old_shipment_id`);

--
-- Indexes for table `old_shipment_items`
--
ALTER TABLE `old_shipment_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `old_shipment_items_old_shipment_id_foreign` (`old_shipment_id`),
  ADD KEY `old_shipment_items_old_customer_id_foreign` (`old_customer_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`),
  ADD KEY `permissions_group_name_index` (`group_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `receipt_status_notifications`
--
ALTER TABLE `receipt_status_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rsn_receipt_status_unique` (`receipt_no`,`status`),
  ADD KEY `receipt_status_notifications_notified_by_foreign` (`notified_by`),
  ADD KEY `rsn_status_notified_idx` (`status`,`is_notified`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permission_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permission_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `salary_records`
--
ALTER TABLE `salary_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_records_employee_id_foreign` (`employee_id`),
  ADD KEY `salary_records_office_id_foreign` (`office_id`),
  ADD KEY `salary_records_year_month_index` (`year`,`month`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `shipment_bill_payments`
--
ALTER TABLE `shipment_bill_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipment_bill_payments_voucher_no_unique` (`voucher_no`),
  ADD KEY `shipment_bill_payments_user_id_foreign` (`user_id`),
  ADD KEY `shipment_bill_payments_agent_id_foreign` (`agent_id`),
  ADD KEY `shipment_bill_payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `third_parties`
--
ALTER TABLE `third_parties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `third_parties_code_unique` (`code`);

--
-- Indexes for table `third_party_payments`
--
ALTER TABLE `third_party_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `third_party_payments_third_party_id_foreign` (`third_party_id`),
  ADD KEY `third_party_payments_user_id_foreign` (`user_id`),
  ADD KEY `third_party_payments_payment_date_index` (`payment_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Indexes for table `warehouse_bills`
--
ALTER TABLE `warehouse_bills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_bills_office_id_foreign` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cargo_items`
--
ALTER TABLE `cargo_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `cargo_item_payments`
--
ALTER TABLE `cargo_item_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cargo_item_shipment`
--
ALTER TABLE `cargo_item_shipment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=265;

--
-- AUTO_INCREMENT for table `cargo_item_status_logs`
--
ALTER TABLE `cargo_item_status_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1049;

--
-- AUTO_INCREMENT for table `cost_types`
--
ALTER TABLE `cost_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ctn_sequences`
--
ALTER TABLE `ctn_sequences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customer_extra_shipping_marks`
--
ALTER TABLE `customer_extra_shipping_marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_payments`
--
ALTER TABLE `customer_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_payment_allocations`
--
ALTER TABLE `customer_payment_allocations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customer_payment_items`
--
ALTER TABLE `customer_payment_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `imported_excel_files`
--
ALTER TABLE `imported_excel_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `items_shipments`
--
ALTER TABLE `items_shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `items_shipment_costs`
--
ALTER TABLE `items_shipment_costs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `items_shipment_cost_attachments`
--
ALTER TABLE `items_shipment_cost_attachments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `items_shipment_payments`
--
ALTER TABLE `items_shipment_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `item_return_histories`
--
ALTER TABLE `item_return_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_customers`
--
ALTER TABLE `legacy_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_payments`
--
ALTER TABLE `legacy_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_rows`
--
ALTER TABLE `legacy_rows`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `legacy_uploads`
--
ALTER TABLE `legacy_uploads`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `monthly_summaries`
--
ALTER TABLE `monthly_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `office_bills`
--
ALTER TABLE `office_bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `old_customers`
--
ALTER TABLE `old_customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `old_payment_and_receives`
--
ALTER TABLE `old_payment_and_receives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `old_shipments`
--
ALTER TABLE `old_shipments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `old_shipment_bills`
--
ALTER TABLE `old_shipment_bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `old_shipment_items`
--
ALTER TABLE `old_shipment_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipt_status_notifications`
--
ALTER TABLE `receipt_status_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permission`
--
ALTER TABLE `role_permission`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=373;

--
-- AUTO_INCREMENT for table `salary_records`
--
ALTER TABLE `salary_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `shipment_bill_payments`
--
ALTER TABLE `shipment_bill_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `third_parties`
--
ALTER TABLE `third_parties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `third_party_payments`
--
ALTER TABLE `third_party_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `warehouse_bills`
--
ALTER TABLE `warehouse_bills`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cargo_items`
--
ALTER TABLE `cargo_items`
  ADD CONSTRAINT `cargo_items_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cargo_items_third_party_id_foreign` FOREIGN KEY (`third_party_id`) REFERENCES `third_parties` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cargo_item_payments`
--
ALTER TABLE `cargo_item_payments`
  ADD CONSTRAINT `cargo_item_payments_cargo_item_id_foreign` FOREIGN KEY (`cargo_item_id`) REFERENCES `cargo_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cargo_item_payments_customer_payment_id_foreign` FOREIGN KEY (`customer_payment_id`) REFERENCES `customer_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cargo_item_shipment`
--
ALTER TABLE `cargo_item_shipment`
  ADD CONSTRAINT `cargo_item_shipment_cargo_item_id_foreign` FOREIGN KEY (`cargo_item_id`) REFERENCES `cargo_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cargo_item_shipment_items_shipment_id_foreign` FOREIGN KEY (`items_shipment_id`) REFERENCES `items_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cargo_item_status_logs`
--
ALTER TABLE `cargo_item_status_logs`
  ADD CONSTRAINT `cargo_item_status_logs_cargo_item_id_foreign` FOREIGN KEY (`cargo_item_id`) REFERENCES `cargo_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cargo_item_status_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_extra_shipping_marks`
--
ALTER TABLE `customer_extra_shipping_marks`
  ADD CONSTRAINT `customer_extra_shipping_marks_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD CONSTRAINT `customer_payments_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_payments_itemshipment_id_foreign` FOREIGN KEY (`itemshipment_id`) REFERENCES `items_shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `customer_payment_allocations`
--
ALTER TABLE `customer_payment_allocations`
  ADD CONSTRAINT `customer_payment_allocations_customer_payment_id_foreign` FOREIGN KEY (`customer_payment_id`) REFERENCES `customer_payments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_payment_allocations_itemshipment_id_foreign` FOREIGN KEY (`itemshipment_id`) REFERENCES `items_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_payment_items`
--
ALTER TABLE `customer_payment_items`
  ADD CONSTRAINT `customer_payment_items_cargo_item_id_foreign` FOREIGN KEY (`cargo_item_id`) REFERENCES `cargo_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customer_payment_items_customer_payment_id_foreign` FOREIGN KEY (`customer_payment_id`) REFERENCES `customer_payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `imported_excel_files`
--
ALTER TABLE `imported_excel_files`
  ADD CONSTRAINT `imported_excel_files_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_itemshipment_id_foreign` FOREIGN KEY (`itemshipment_id`) REFERENCES `items_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items_shipment_costs`
--
ALTER TABLE `items_shipment_costs`
  ADD CONSTRAINT `items_shipment_costs_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_shipment_costs_cost_type_id_foreign` FOREIGN KEY (`cost_type_id`) REFERENCES `cost_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `items_shipment_costs_items_shipment_id_foreign` FOREIGN KEY (`items_shipment_id`) REFERENCES `items_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items_shipment_cost_attachments`
--
ALTER TABLE `items_shipment_cost_attachments`
  ADD CONSTRAINT `items_shipment_cost_attachments_items_shipment_cost_id_foreign` FOREIGN KEY (`items_shipment_cost_id`) REFERENCES `items_shipment_costs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items_shipment_payments`
--
ALTER TABLE `items_shipment_payments`
  ADD CONSTRAINT `items_shipment_payments_items_shipment_cost_id_foreign` FOREIGN KEY (`items_shipment_cost_id`) REFERENCES `items_shipment_costs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `items_shipment_payments_shipment_bill_payment_id_foreign` FOREIGN KEY (`shipment_bill_payment_id`) REFERENCES `shipment_bill_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `items_shipment_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `item_return_histories`
--
ALTER TABLE `item_return_histories`
  ADD CONSTRAINT `item_return_histories_cargo_item_id_foreign` FOREIGN KEY (`cargo_item_id`) REFERENCES `cargo_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_return_histories_items_shipment_id_foreign` FOREIGN KEY (`items_shipment_id`) REFERENCES `items_shipments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `item_return_histories_returned_by_foreign` FOREIGN KEY (`returned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `legacy_customers`
--
ALTER TABLE `legacy_customers`
  ADD CONSTRAINT `legacy_customers_upload_id_foreign` FOREIGN KEY (`upload_id`) REFERENCES `legacy_uploads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_payments`
--
ALTER TABLE `legacy_payments`
  ADD CONSTRAINT `legacy_payments_upload_id_foreign` FOREIGN KEY (`upload_id`) REFERENCES `legacy_uploads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `legacy_rows`
--
ALTER TABLE `legacy_rows`
  ADD CONSTRAINT `legacy_rows_upload_id_foreign` FOREIGN KEY (`upload_id`) REFERENCES `legacy_uploads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `office_bills`
--
ALTER TABLE `office_bills`
  ADD CONSTRAINT `office_bills_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `old_payment_and_receives`
--
ALTER TABLE `old_payment_and_receives`
  ADD CONSTRAINT `old_payment_and_receives_old_customer_id_foreign` FOREIGN KEY (`old_customer_id`) REFERENCES `old_customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `old_payment_and_receives_old_shipment_id_foreign` FOREIGN KEY (`old_shipment_id`) REFERENCES `old_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `old_shipment_bills`
--
ALTER TABLE `old_shipment_bills`
  ADD CONSTRAINT `old_shipment_bills_old_shipment_id_foreign` FOREIGN KEY (`old_shipment_id`) REFERENCES `old_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `old_shipment_items`
--
ALTER TABLE `old_shipment_items`
  ADD CONSTRAINT `old_shipment_items_old_customer_id_foreign` FOREIGN KEY (`old_customer_id`) REFERENCES `old_customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `old_shipment_items_old_shipment_id_foreign` FOREIGN KEY (`old_shipment_id`) REFERENCES `old_shipments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipt_status_notifications`
--
ALTER TABLE `receipt_status_notifications`
  ADD CONSTRAINT `receipt_status_notifications_notified_by_foreign` FOREIGN KEY (`notified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permission_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_records`
--
ALTER TABLE `salary_records`
  ADD CONSTRAINT `salary_records_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `salary_records_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `shipment_bill_payments`
--
ALTER TABLE `shipment_bill_payments`
  ADD CONSTRAINT `shipment_bill_payments_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_bill_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `third_party_payments`
--
ALTER TABLE `third_party_payments`
  ADD CONSTRAINT `third_party_payments_third_party_id_foreign` FOREIGN KEY (`third_party_id`) REFERENCES `third_parties` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `third_party_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `warehouse_bills`
--
ALTER TABLE `warehouse_bills`
  ADD CONSTRAINT `warehouse_bills_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
