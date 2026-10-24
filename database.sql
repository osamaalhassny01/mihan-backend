-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 11:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mihan`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'تسجيل دخول المدير', NULL, '2026-09-22 20:23:28'),
(2, 4, 'accept_request', 'قبول الطلب REQ-2026-000001', NULL, '2026-09-22 20:23:28'),
(3, 7, 'logout', 'تسجيل خروج', '::1', '2026-09-22 20:34:37'),
(4, 7, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 20:35:20'),
(5, 7, 'employee_complaint_status', 'تغيير حالة الشكوى #1 إلى open', '::1', '2026-09-22 20:36:36'),
(6, 7, 'logout', 'تسجيل خروج', '::1', '2026-09-22 20:37:44'),
(7, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 20:43:30'),
(8, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 21:28:34'),
(9, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 21:28:55'),
(10, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 21:44:03'),
(11, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 22:05:00'),
(12, 1, 'update_user', 'تعديل المستخدم #2 وتحديث صلاحياته', '::1', '2026-09-22 22:05:44'),
(13, 1, 'update_user', 'تعديل المستخدم #3 وتحديث صلاحياته', '::1', '2026-09-22 22:06:08'),
(14, 1, 'update_user', 'تعديل المستخدم #4 وتحديث صلاحياته', '::1', '2026-09-22 22:06:48'),
(15, 1, 'update_user', 'تعديل المستخدم #5 وتحديث صلاحياته', '::1', '2026-09-22 22:07:12'),
(16, 1, 'update_user', 'تعديل المستخدم #7 وتحديث صلاحياته', '::1', '2026-09-22 22:08:12'),
(17, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 22:08:20'),
(18, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 22:14:33'),
(19, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 22:19:45'),
(20, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 22:20:01'),
(21, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:25:10'),
(22, 2, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:25:16'),
(23, 2, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:26:52'),
(24, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:37:04'),
(25, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:38:17'),
(26, 4, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:38:25'),
(27, 4, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:40:08'),
(28, 4, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:40:15'),
(29, 4, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:40:20'),
(30, 2, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:40:28'),
(31, 2, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:41:29'),
(32, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:43:46'),
(33, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:52:48'),
(34, 4, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:52:52'),
(35, 4, 'logout', 'تسجيل خروج', '::1', '2026-09-22 23:53:12'),
(36, 2, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-22 23:53:16'),
(37, 2, 'create_request', 'إنشاء طلب خدمة جديد #5', NULL, '2026-09-23 00:32:31'),
(38, 2, 'create_request', 'إنشاء طلب خدمة جديد #6', NULL, '2026-09-23 00:33:47'),
(39, 2, 'logout', 'تسجيل خروج', '::1', '2026-09-23 00:44:12'),
(40, 1, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-23 00:44:32'),
(41, 1, 'logout', 'تسجيل خروج', '::1', '2026-09-23 00:44:55'),
(42, 7, 'login', 'تسجيل دخول ناجح', '::1', '2026-09-23 00:45:22'),
(43, 7, 'logout', 'تسجيل خروج', '::1', '2026-09-23 00:45:38');

-- --------------------------------------------------------

--
-- Table structure for table `ai_interactions`
--

CREATE TABLE `ai_interactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `input_text` text NOT NULL,
  `detected_category_id` int(11) DEFAULT NULL,
  `detected_service_id` int(11) DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT 0.00,
  `engine_used` varchar(30) DEFAULT 'local',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_logs`
--

CREATE TABLE `ai_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `provider` varchar(40) NOT NULL,
  `operation` varchar(40) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `fallback_used` tinyint(1) NOT NULL DEFAULT 0,
  `duration_ms` int(11) DEFAULT NULL,
  `error_message` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_logs`
--

INSERT INTO `ai_logs` (`id`, `user_id`, `provider`, `operation`, `success`, `fallback_used`, `duration_ms`, `error_message`, `created_at`) VALUES
(1, NULL, 'local', 'analyze', 1, 0, 4, NULL, '2026-09-20 03:45:43'),
(2, NULL, 'local', 'analyze', 1, 0, 1, NULL, '2026-09-20 03:55:31'),
(3, NULL, 'local', 'analyze', 1, 0, 1, NULL, '2026-09-22 18:36:55'),
(4, NULL, 'local', 'analyze', 1, 0, 1, NULL, '2026-09-22 19:21:32'),
(5, NULL, 'local', 'analyze', 1, 0, 2, NULL, '2026-09-22 19:25:06'),
(6, NULL, 'local', 'analyze', 1, 0, 1, NULL, '2026-09-22 19:33:40'),
(7, NULL, 'local', 'analyze', 1, 0, 1, NULL, '2026-09-22 19:40:43'),
(8, NULL, 'local', 'analyze', 1, 0, 7, NULL, '2026-09-22 20:40:04'),
(9, NULL, 'local', 'analyze', 1, 0, 2, NULL, '2026-09-22 21:56:56'),
(10, 1, 'gemini', 'connection_test', 1, 0, 2217, NULL, '2026-09-22 23:38:02'),
(11, 4, 'gemini', 'chat', 1, 0, 6767, NULL, '2026-09-22 23:39:26'),
(12, 2, 'gemini', 'chat', 1, 0, 5088, NULL, '2026-09-22 23:41:04'),
(13, 1, 'gemini', 'connection_test', 1, 0, 3273, NULL, '2026-09-22 23:44:09'),
(14, 1, 'gemini', 'connection_test', 1, 0, 5745, NULL, '2026-09-22 23:44:33'),
(15, NULL, 'gemini', 'analyze', 1, 0, 8997, NULL, '2026-09-23 00:36:18'),
(16, NULL, 'gemini', 'analyze', 1, 0, 5286, NULL, '2026-09-23 00:40:24');

-- --------------------------------------------------------

--
-- Table structure for table `ai_provider_settings`
--

CREATE TABLE `ai_provider_settings` (
  `id` int(11) NOT NULL,
  `provider` varchar(40) NOT NULL,
  `model` varchar(120) DEFAULT NULL,
  `api_key_encrypted` text DEFAULT NULL,
  `base_url` varchar(500) DEFAULT NULL,
  `temperature` decimal(4,2) DEFAULT 0.20,
  `max_tokens` int(11) DEFAULT 700,
  `timeout` int(11) DEFAULT 20,
  `system_prompt` text DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_provider_settings`
--

INSERT INTO `ai_provider_settings` (`id`, `provider`, `model`, `api_key_encrypted`, `base_url`, `temperature`, `max_tokens`, `timeout`, `system_prompt`, `is_enabled`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'local', NULL, NULL, NULL, 0.20, 700, 20, '', 1, 0, '2026-09-22 20:40:04', '2026-09-22 23:44:35'),
(2, 'gemini', 'gemini-3.1-flash-lite', '/uMykG+4eOFRItLNM/03tYe6rwKUOzmdGF3BMxQHQ0yplv27VwG1QhkKWFF4f02cVplAn3h28aBDNQ9o5F2sqlNQH1Io09QeCo7ih0ckjLro', 'https://generativelanguage.googleapis.com/v1beta', 0.20, 700, 20, NULL, 1, 1, '2026-09-22 21:06:46', '2026-09-22 23:44:35'),
(3, 'openai', 'gpt-4o-mini', NULL, 'https://api.openai.com/v1', 0.20, 700, 20, NULL, 0, 0, '2026-09-22 21:06:46', '2026-09-22 21:06:46'),
(4, 'groq', NULL, NULL, 'https://api.groq.com/openai/v1', 0.20, 700, 20, NULL, 0, 0, '2026-09-22 21:06:46', '2026-09-22 21:06:46'),
(5, 'openai_compatible', NULL, NULL, NULL, 0.20, 700, 20, NULL, 0, 0, '2026-09-22 21:06:46', '2026-09-22 21:06:46');

-- --------------------------------------------------------

--
-- Table structure for table `ai_recommendations`
--

CREATE TABLE `ai_recommendations` (
  `id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `score` decimal(6,2) NOT NULL,
  `score_breakdown` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`score_breakdown`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_recommendations`
--

INSERT INTO `ai_recommendations` (`id`, `request_id`, `customer_id`, `provider_id`, `score`, `score_breakdown`, `created_at`) VALUES
(1, 5, 2, 2, 81.10, '{\"service_match\":30,\"location_match\":20,\"rating_score\":18,\"experience_score\":6,\"availability_score\":0,\"availability_checked\":false,\"reliability_score\":7.1,\"verified\":true,\"total\":81.1}', '2026-09-23 00:32:31'),
(2, 6, 2, 2, 81.10, '{\"service_match\":30,\"location_match\":20,\"rating_score\":18,\"experience_score\":6,\"availability_score\":0,\"availability_checked\":false,\"reliability_score\":7.1,\"verified\":true,\"total\":81.1}', '2026-09-23 00:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name_ar` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `icon` varchar(100) DEFAULT 'fa-toolbox',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_ar`, `name_en`, `icon`, `is_active`, `created_at`) VALUES
(1, 'سباكة', 'Plumbing', 'fa-faucet', 1, '2026-09-22 20:23:28'),
(2, 'كهرباء', 'Electrical', 'fa-bolt', 1, '2026-09-22 20:23:28'),
(3, 'نجارة', 'Carpentry', 'fa-hammer', 1, '2026-09-22 20:23:28'),
(4, 'تكييف وتبريد', 'Air Conditioning', 'fa-fan', 1, '2026-09-22 20:23:28'),
(5, 'صيانة أجهزة منزلية', 'Home Appliance Repair', 'fa-blender', 1, '2026-09-22 20:23:28'),
(6, 'دهان', 'Painting', 'fa-paint-roller', 1, '2026-09-22 20:23:28'),
(7, 'تنظيف', 'Cleaning', 'fa-broom', 1, '2026-09-22 20:23:28'),
(8, 'صيانة سيارات', 'Car Maintenance', 'fa-car', 1, '2026-09-22 20:23:28'),
(9, 'صيانة هواتف وحواسيب', 'Phone & Computer Repair', 'fa-laptop', 1, '2026-09-22 20:23:28'),
(10, 'حدادة وألمنيوم', 'Blacksmith & Aluminum', 'fa-industry', 1, '2026-09-22 20:23:28'),
(11, 'بلاط وسيراميك', 'Tiling', 'fa-th', 1, '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `complaint_number` varchar(30) NOT NULL,
  `created_by` int(11) NOT NULL,
  `against_user_id` int(11) DEFAULT NULL,
  `request_id` int(11) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_review','resolved','rejected','closed') NOT NULL DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `complaint_number`, `created_by`, `against_user_id`, `request_id`, `subject`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'CMP-2026-000001', 3, 5, 2, 'تأخر في الوصول', 'مقدم الخدمة تأخر عن الموعد المتفق عليه بساعة', 'open', '2026-09-22 20:23:28', '2026-09-22 20:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `complaint_messages`
--

CREATE TABLE `complaint_messages` (
  `id` int(11) NOT NULL,
  `complaint_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complaint_messages`
--

INSERT INTO `complaint_messages` (`id`, `complaint_id`, `sender_id`, `message`, `created_at`) VALUES
(1, 1, 3, 'تأخر الفني عن الموعد المحدد بدون إشعار مسبق', '2026-09-22 20:23:28'),
(2, 1, 1, 'تم التواصل مع مقدم الخدمة وسيتم المتابعة', '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `last_message_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_messages`
--

CREATE TABLE `conversation_messages` (
  `id` bigint(20) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(30) NOT NULL,
  `request_id` int(11) NOT NULL,
  `service_price` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,4) NOT NULL DEFAULT 0.1000,
  `commission_amount` decimal(10,2) NOT NULL,
  `provider_net` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `request_id`, `service_price`, `commission_rate`, `commission_amount`, `provider_net`, `total_amount`, `payment_status`, `created_at`) VALUES
(1, 'INV-2026-000001', 1, 6000.00, 0.1000, 600.00, 5400.00, 6000.00, 'paid', '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name_ar` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `level` enum('governorate','city','district','area') NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `parent_id`, `name_ar`, `name_en`, `level`, `is_active`) VALUES
(1, NULL, 'أمانة العاصمة', 'Sana\'a Capital', 'governorate', 1),
(2, NULL, 'عدن', 'Aden', 'governorate', 1),
(3, NULL, 'تعز', 'Taiz', 'governorate', 1),
(4, NULL, 'حضرموت', 'Hadhramaut', 'governorate', 1),
(5, 1, 'شعوب', 'Shu\'aub', 'district', 1),
(6, 1, 'السبعين', 'As-Sabain', 'district', 1),
(7, 1, 'معين', 'Ma\'in', 'district', 1),
(8, 2, 'المعلا', 'Al-Mualla', 'district', 1),
(9, 2, 'كريتر', 'Crater', 'district', 1),
(10, 2, 'خورمكسر', 'Khormaksar', 'district', 1),
(11, 3, 'صالة', 'Salah', 'district', 1),
(12, 3, 'المظفر', 'Al-Mudhaffar', 'district', 1),
(13, 4, 'المكلا', 'Al-Mukalla', 'city', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `message` varchar(500) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 4, 'طلب جديد', 'لديك طلب خدمة جديد بانتظار الرد', '/provider/requests', 0, '2026-09-22 20:23:28'),
(2, 2, 'تم قبول طلبك', 'قام مقدم الخدمة بقبول طلبك', '/customer/requests', 1, '2026-09-22 20:23:28'),
(3, 2, 'تم إكمال الخدمة', 'تم إكمال طلبك بنجاح، يمكنك الآن تقييم مقدم الخدمة', '/customer/requests', 1, '2026-09-22 20:23:28'),
(4, 5, 'طلب خدمة جديد', 'لديك طلب خدمة جديد قد يناسب تخصصك', '/provider/requests', 0, '2026-09-23 00:32:31'),
(5, 5, 'طلب خدمة جديد', 'لديك طلب خدمة جديد قد يناسب تخصصك', '/provider/requests', 0, '2026-09-23 00:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `method` enum('cash','simulated_card','simulated_wallet') NOT NULL DEFAULT 'simulated_wallet',
  `amount` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid','refunded') NOT NULL DEFAULT 'paid',
  `paid_at` datetime DEFAULT current_timestamp(),
  `is_simulated` tinyint(1) NOT NULL DEFAULT 1,
  `reference` varchar(100) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `method`, `amount`, `status`, `paid_at`, `is_simulated`, `reference`, `notes`) VALUES
(1, 1, 'simulated_wallet', 6000.00, 'paid', '2026-09-22 20:23:28', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `key`, `description`) VALUES
(1, 'manage_users', 'إدارة المستخدمين'),
(2, 'manage_categories', 'إدارة التصنيفات والخدمات'),
(3, 'manage_locations', 'إدارة المناطق'),
(4, 'verify_providers', 'توثيق مقدمي الخدمة'),
(5, 'manage_complaints', 'إدارة الشكاوى'),
(6, 'view_reports', 'مشاهدة التقارير'),
(7, 'manage_requests', 'إدارة الطلبات'),
(8, 'manage_finance', 'إدارة المالية والفواتير'),
(9, 'manage_settings', 'إدارة إعدادات النظام'),
(10, 'manage_ai', 'إدارة مزودي ونماذج الذكاء الاصطناعي'),
(11, 'create_request', 'إنشاء طلب خدمة'),
(12, 'provide_service', 'تقديم الخدمات'),
(13, 'manage_payouts', 'إدارة مستحقات مقدمي الخدمة'),
(14, 'manage_employees', 'إدارة موظفي المنصة'),
(15, 'view_providers', 'عرض مقدمي الخدمة'),
(16, 'view_finance', 'عرض التقارير المالية'),
(17, 'view_conversations', 'عرض المحادثات'),
(18, 'manage_reviews', 'إدارة التقييمات'),
(19, 'view_dashboard', 'عرض لوحة التحكم');

-- --------------------------------------------------------

--
-- Table structure for table `platform_commissions`
--

CREATE TABLE `platform_commissions` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_commissions`
--

INSERT INTO `platform_commissions` (`id`, `invoice_id`, `amount`, `created_at`) VALUES
(1, 1, 600.00, '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `provider_availability`
--

CREATE TABLE `provider_availability` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sunday..6=Saturday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_availability`
--

INSERT INTO `provider_availability` (`id`, `provider_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 1, 0, '08:00:00', '18:00:00'),
(2, 1, 1, '08:00:00', '18:00:00'),
(3, 2, 0, '09:00:00', '17:00:00'),
(4, 2, 2, '09:00:00', '17:00:00'),
(5, 3, 1, '10:00:00', '20:00:00'),
(6, 3, 3, '10:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `provider_evaluations`
--

CREATE TABLE `provider_evaluations` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `quality_score` tinyint(4) NOT NULL,
  `reliability_score` tinyint(4) NOT NULL,
  `communication_score` tinyint(4) NOT NULL,
  `compliance_score` tinyint(4) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_locations`
--

CREATE TABLE `provider_locations` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_locations`
--

INSERT INTO `provider_locations` (`id`, `provider_id`, `location_id`) VALUES
(1, 1, 1),
(2, 1, 6),
(4, 2, 1),
(3, 2, 2),
(5, 3, 3),
(6, 3, 8);

-- --------------------------------------------------------

--
-- Table structure for table `provider_payouts`
--

CREATE TABLE `provider_payouts` (
  `id` bigint(20) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','failed','cancelled') NOT NULL DEFAULT 'paid',
  `reference` varchar(100) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  `paid_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `provider_portfolio`
--

CREATE TABLE `provider_portfolio` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_portfolio`
--

INSERT INTO `provider_portfolio` (`id`, `provider_id`, `image_path`, `caption`, `created_at`) VALUES
(1, 1, 'https://picsum.photos/seed/mihan-ac-1/600/400', 'تركيب مكيف سبليت لعميل في صنعاء', '2026-09-22 20:23:28'),
(2, 1, 'https://picsum.photos/seed/mihan-ac-2/600/400', 'صيانة دورية لوحدة تكييف مركزي', '2026-09-22 20:23:28'),
(3, 2, 'https://picsum.photos/seed/mihan-plumb-1/600/400', 'إصلاح تسريب مواسير مطبخ', '2026-09-22 20:23:28'),
(4, 3, 'https://picsum.photos/seed/mihan-elec-1/600/400', 'تمديد كهربائي جديد لشقة سكنية', '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `provider_profiles`
--

CREATE TABLE `provider_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `profession` varchar(150) DEFAULT NULL,
  `specialization` varchar(150) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `avg_rating` decimal(3,2) NOT NULL DEFAULT 0.00,
  `rating_count` int(11) NOT NULL DEFAULT 0,
  `completed_jobs` int(11) NOT NULL DEFAULT 0,
  `response_rate` decimal(5,2) NOT NULL DEFAULT 100.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_profiles`
--

INSERT INTO `provider_profiles` (`id`, `user_id`, `category_id`, `profession`, `specialization`, `bio`, `experience_years`, `is_verified`, `is_available`, `avg_rating`, `rating_count`, `completed_jobs`, `response_rate`, `created_at`) VALUES
(1, 4, 4, 'فني تكييف', 'صيانة وتركيب مكيفات', 'خبرة واسعة في صيانة جميع أنواع المكيفات', 8, 1, 1, 4.80, 12, 40, 96.50, '2026-09-22 20:23:28'),
(2, 5, 1, 'سباك محترف', 'إصلاح تسريبات وتمديدات', 'سباك معتمد بخبرة طويلة في المنازل والمنشآت', 6, 1, 1, 4.50, 9, 25, 92.00, '2026-09-22 20:23:28'),
(3, 6, 2, 'كهربائي منازل', 'تمديدات وإصلاح أعطال', 'كهربائي متخصص في الأعطال المنزلية والتمديدات الجديدة', 5, 0, 1, 4.20, 5, 14, 88.00, '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `provider_services`
--

CREATE TABLE `provider_services` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `price_from` decimal(10,2) DEFAULT 0.00,
  `price_to` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `provider_services`
--

INSERT INTO `provider_services` (`id`, `provider_id`, `service_id`, `price_from`, `price_to`) VALUES
(1, 1, 7, 3000.00, 8000.00),
(2, 1, 8, 15000.00, 40000.00),
(3, 2, 1, 2000.00, 6000.00),
(4, 2, 2, 5000.00, 15000.00),
(5, 3, 3, 2500.00, 7000.00),
(6, 3, 4, 10000.00, 30000.00);

-- --------------------------------------------------------

--
-- Table structure for table `request_status_history`
--

CREATE TABLE `request_status_history` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_status_history`
--

INSERT INTO `request_status_history` (`id`, `request_id`, `changed_by`, `old_status`, `new_status`, `changed_at`) VALUES
(1, 1, 2, NULL, 'new', '2026-09-22 20:23:28'),
(2, 1, 2, 'new', 'pending', '2026-09-22 20:23:28'),
(3, 1, 4, 'pending', 'accepted', '2026-09-22 20:23:28'),
(4, 1, 4, 'accepted', 'in_progress', '2026-09-22 20:23:28'),
(5, 1, 4, 'in_progress', 'completed', '2026-09-22 20:23:28'),
(6, 2, 3, NULL, 'new', '2026-09-22 20:23:28'),
(7, 2, 3, 'new', 'pending', '2026-09-22 20:23:28'),
(8, 2, 5, 'pending', 'accepted', '2026-09-22 20:23:28'),
(9, 2, 5, 'accepted', 'in_progress', '2026-09-22 20:23:28'),
(10, 3, 2, NULL, 'new', '2026-09-22 20:23:28'),
(11, 3, 2, 'new', 'pending', '2026-09-22 20:23:28'),
(12, 4, 3, NULL, 'new', '2026-09-22 20:23:28'),
(13, 4, 3, 'new', 'pending', '2026-09-22 20:23:28'),
(14, 4, 1, 'pending', 'accepted', '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `provider_reply` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `request_id`, `customer_id`, `provider_id`, `rating`, `comment`, `provider_reply`, `created_at`) VALUES
(1, 1, 2, 1, 5, 'خدمة ممتازة وسريعة، شكرًا لك', NULL, '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('customer','provider','employee','admin') NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission_id`) VALUES
(32, 'customer', 11),
(33, 'provider', 12),
(34, 'employee', 5),
(41, 'employee', 6),
(35, 'employee', 7),
(40, 'employee', 15),
(39, 'employee', 16),
(37, 'employee', 17),
(36, 'employee', 18),
(38, 'employee', 19),
(12, 'admin', 1),
(3, 'admin', 2),
(7, 'admin', 3),
(14, 'admin', 4),
(4, 'admin', 5),
(19, 'admin', 6),
(9, 'admin', 7),
(6, 'admin', 8),
(11, 'admin', 9),
(2, 'admin', 10),
(1, 'admin', 11),
(13, 'admin', 12),
(8, 'admin', 13),
(5, 'admin', 14),
(18, 'admin', 15),
(17, 'admin', 16),
(15, 'admin', 17),
(10, 'admin', 18),
(16, 'admin', 19);

-- --------------------------------------------------------

--
-- Table structure for table `schema_migrations`
--

CREATE TABLE `schema_migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `applied_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schema_migrations`
--

INSERT INTO `schema_migrations` (`id`, `migration`, `applied_at`) VALUES
(1, '003_rbac_permissions.sql', '2026-09-22 21:06:46'),
(2, '004_ai_provider_settings.sql', '2026-09-22 21:06:46'),
(3, '005_provider_evaluations.sql', '2026-09-22 21:06:46'),
(4, '007_admin_settings.sql', '2026-09-22 21:06:46'),
(5, '008_finance_request_final_price.sql', '2026-09-22 21:06:46'),
(6, '009_ai_rule_based_fallback.sql', '2026-09-22 21:06:46'),
(7, '010_gemini_native_api_defaults.sql', '2026-09-22 21:06:46'),
(8, '011_platform_roles_messaging_payouts.sql', '2026-09-22 21:06:46'),
(9, '012_user_management_employee_dashboard.sql', '2026-09-22 21:06:46');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name_ar` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `category_id`, `name_ar`, `name_en`, `description`, `is_active`) VALUES
(1, 1, 'إصلاح تسريب المياه', 'Water Leak Repair', 'الكشف عن تسريبات المياه وإصلاحها', 1),
(2, 1, 'تركيب أدوات صحية', 'Sanitary Fixture Installation', 'تركيب مغاسل ومراحيض وخلاطات', 1),
(3, 2, 'إصلاح أعطال كهربائية', 'Electrical Fault Repair', 'تشخيص وإصلاح الأعطال الكهربائية المنزلية', 1),
(4, 2, 'تمديدات كهربائية جديدة', 'New Wiring', 'تمديدات كهربائية للمنازل والمكاتب', 1),
(5, 3, 'تصنيع أثاث خشبي', 'Custom Furniture', 'تصميم وتصنيع أثاث حسب الطلب', 1),
(6, 3, 'إصلاح أبواب ونوافذ خشبية', 'Door & Window Repair', 'صيانة الأبواب والنوافذ الخشبية', 1),
(7, 4, 'صيانة مكيفات', 'AC Maintenance', 'تنظيف وصيانة وإصلاح المكيفات', 1),
(8, 4, 'تركيب مكيفات', 'AC Installation', 'تركيب وحدات تكييف جديدة', 1),
(9, 5, 'صيانة غسالات', 'Washing Machine Repair', 'إصلاح أعطال الغسالات', 1),
(10, 5, 'صيانة ثلاجات', 'Refrigerator Repair', 'إصلاح أعطال الثلاجات', 1),
(11, 6, 'دهان داخلي', 'Interior Painting', 'دهان جدران وأسقف داخلية', 1),
(12, 6, 'دهان خارجي', 'Exterior Painting', 'دهان واجهات خارجية', 1),
(13, 7, 'تنظيف منازل', 'Home Cleaning', 'تنظيف شامل للمنازل', 1),
(14, 7, 'تنظيف مكاتب', 'Office Cleaning', 'تنظيف دوري للمكاتب', 1),
(15, 8, 'صيانة عامة للسيارات', 'General Car Maintenance', 'فحص وصيانة دورية للسيارات', 1),
(16, 9, 'إصلاح شاشات الهواتف', 'Phone Screen Repair', 'استبدال وإصلاح شاشات الهواتف', 1),
(17, 10, 'أعمال حدادة عامة', 'General Blacksmith Work', 'تصنيع بوابات وشبابيك حديدية', 1),
(18, 11, 'تركيب بلاط', 'Tile Installation', 'تركيب بلاط الأرضيات والحمامات', 1);

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `request_number` varchar(30) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `final_price` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('new','pending','accepted','in_progress','completed','cancelled','rejected') NOT NULL DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `request_number`, `customer_id`, `provider_id`, `service_id`, `location_id`, `description`, `preferred_date`, `preferred_time`, `budget`, `final_price`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'REQ-2026-000001', 2, 1, 7, 1, 'المكيف يعمل لكنه لا يبرد بشكل جيد', '2026-09-15', '10:00:00', 6000.00, NULL, NULL, 'completed', '2026-09-22 20:23:28', '2026-09-22 20:23:28'),
(2, 'REQ-2026-000002', 3, 2, 1, 2, 'تسريب مياه أسفل المغسلة', '2026-09-16', '14:00:00', 4000.00, NULL, NULL, 'in_progress', '2026-09-22 20:23:28', '2026-09-22 20:23:28'),
(3, 'REQ-2026-000003', 2, NULL, 3, 1, 'يوجد عطل متكرر في الكهرباء بالمطبخ', '2026-09-20', '09:00:00', 5000.00, NULL, NULL, 'pending', '2026-09-22 20:23:28', '2026-09-22 20:23:28'),
(4, 'REQ-2026-000004', 3, 1, 8, 6, 'أحتاج تركيب مكيف جديد في غرفة النوم', '2026-09-18', '11:00:00', 25000.00, NULL, NULL, 'accepted', '2026-09-22 20:23:28', '2026-09-22 20:23:28');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `key`, `value`, `updated_at`) VALUES
(1, 'site_name', 'مِهَن', '2026-09-22 20:16:10'),
(2, 'site_tagline', 'منصة الخدمات المهنية', '2026-09-22 20:16:10'),
(3, 'commission_rate', '0.10', '2026-09-18 01:31:02'),
(4, 'default_language', 'ar', '2026-09-18 01:31:02'),
(5, 'ai_engine', 'local', '2026-09-18 01:31:02'),
(6, 'platform_commission_rate', '0.10', '2026-09-22 20:16:10'),
(7, 'timezone', 'Asia/Aden', '2026-09-22 20:16:10'),
(8, 'allow_customer_registration', '1', '2026-09-22 20:16:10'),
(9, 'allow_provider_registration', '1', '2026-09-22 20:16:10'),
(10, 'maintenance_mode', '0', '2026-09-22 20:16:10'),
(11, 'maintenance_message', 'المنصة تحت الصيانة المؤقتة، نعود إليكم قريبًا.', '2026-09-22 20:16:10'),
(12, 'auto_provider_available', '1', '2026-09-22 20:16:10'),
(13, 'reviews_enabled', '1', '2026-09-22 20:16:10'),
(14, 'complaints_enabled', '1', '2026-09-22 20:16:10'),
(15, 'currency', 'ريال يمني', '2026-09-22 20:16:10'),
(16, 'currency_code', 'YER', '2026-09-22 20:16:10'),
(17, 'invoice_prefix', 'INV', '2026-09-22 20:16:10'),
(18, 'notifications_enabled', '1', '2026-09-22 20:16:10'),
(19, 'notify_on_request', '1', '2026-09-22 20:16:10'),
(20, 'notify_on_completion', '1', '2026-09-22 20:16:10'),
(21, 'notify_on_payment', '1', '2026-09-22 20:16:10'),
(22, 'notify_on_complaint', '1', '2026-09-22 20:16:10'),
(23, 'force_https', '0', '2026-09-22 20:16:10'),
(24, 'activity_log_enabled', '1', '2026-09-22 20:16:10'),
(25, 'show_system_errors', '0', '2026-09-22 20:16:10'),
(52, 'internal_messaging_enabled', '1', '2026-09-22 20:22:15'),
(53, 'platform_payment_required', '1', '2026-09-22 20:22:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','provider','employee','admin') NOT NULL DEFAULT 'customer',
  `profile_photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_verified_email` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password_hash`, `role`, `profile_photo`, `is_active`, `is_verified_email`, `created_at`, `updated_at`) VALUES
(1, 'مدير النظام', 'admin@mihan.test', '777000001', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'admin', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 20:23:28'),
(2, 'أسماء العبيدي', 'customer1@mihan.test', '777000002', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'customer', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 22:05:43'),
(3, 'سميه عبال', 'customer2@mihan.test', '777000003', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'customer', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 22:06:08'),
(4, 'براءة الخالدي', 'provider1@mihan.test', '777000004', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'provider', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 22:06:48'),
(5, 'لينا نبيل', 'provider2@mihan.test', '777000005', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'provider', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 22:07:12'),
(6, 'ياسر المقاول', 'provider3@mihan.test', '777000006', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'provider', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 20:23:28'),
(7, 'الهنوف السلطان', 'employee@mihan.test', '777000007', '$2y$10$8ctxTzTVx6s01wAJV2gyI.XCZptLSxMUntRRMBTDT0vMvEoi919TC', 'employee', NULL, 1, 1, '2026-09-22 20:23:28', '2026-09-22 22:08:12');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_interactions`
--
ALTER TABLE `ai_interactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ai_logs_created` (`created_at`),
  ADD KEY `idx_ai_logs_provider` (`provider`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ai_provider_settings`
--
ALTER TABLE `ai_provider_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `provider` (`provider`);

--
-- Indexes for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `complaint_number` (`complaint_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `against_user_id` (`against_user_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `idx_complaints_status` (`status`);

--
-- Indexes for table `complaint_messages`
--
ALTER TABLE `complaint_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_id` (`complaint_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `conversation_messages`
--
ALTER TABLE `conversation_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation_messages_conversation_created` (`conversation_id`,`created_at`),
  ADD KEY `idx_conversation_messages_sender_read` (`sender_id`,`is_read`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD UNIQUE KEY `request_id` (`request_id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `platform_commissions`
--
ALTER TABLE `platform_commissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `provider_availability`
--
ALTER TABLE `provider_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_provider_availability_provider_day_time` (`provider_id`,`day_of_week`,`start_time`,`end_time`);

--
-- Indexes for table `provider_evaluations`
--
ALTER TABLE `provider_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_provider_eval` (`provider_id`,`created_at`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `provider_locations`
--
ALTER TABLE `provider_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_provider_location` (`provider_id`,`location_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `provider_payouts`
--
ALTER TABLE `provider_payouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_provider_payout_provider_status` (`provider_id`,`status`),
  ADD KEY `idx_provider_payout_invoice` (`invoice_id`);

--
-- Indexes for table `provider_portfolio`
--
ALTER TABLE `provider_portfolio`
  ADD PRIMARY KEY (`id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_provider_rating` (`avg_rating`);

--
-- Indexes for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_provider_service` (`provider_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_perm` (`role`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migration` (`migration`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_number` (`request_number`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `idx_requests_status` (`status`),
  ADD KEY `idx_requests_customer` (`customer_id`),
  ADD KEY `idx_requests_provider` (`provider_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_perm` (`user_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `idx_user_perm_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `ai_interactions`
--
ALTER TABLE `ai_interactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ai_logs`
--
ALTER TABLE `ai_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `ai_provider_settings`
--
ALTER TABLE `ai_provider_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaint_messages`
--
ALTER TABLE `complaint_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversation_messages`
--
ALTER TABLE `conversation_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `platform_commissions`
--
ALTER TABLE `platform_commissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `provider_availability`
--
ALTER TABLE `provider_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `provider_evaluations`
--
ALTER TABLE `provider_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provider_locations`
--
ALTER TABLE `provider_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `provider_payouts`
--
ALTER TABLE `provider_payouts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `provider_portfolio`
--
ALTER TABLE `provider_portfolio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `provider_services`
--
ALTER TABLE `provider_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `request_status_history`
--
ALTER TABLE `request_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `schema_migrations`
--
ALTER TABLE `schema_migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_interactions`
--
ALTER TABLE `ai_interactions`
  ADD CONSTRAINT `ai_interactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_logs`
--
ALTER TABLE `ai_logs`
  ADD CONSTRAINT `ai_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ai_recommendations`
--
ALTER TABLE `ai_recommendations`
  ADD CONSTRAINT `ai_recommendations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ai_recommendations_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`against_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_ibfk_3` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complaint_messages`
--
ALTER TABLE `complaint_messages`
  ADD CONSTRAINT `complaint_messages_ibfk_1` FOREIGN KEY (`complaint_id`) REFERENCES `complaints` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complaint_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversations_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversation_messages`
--
ALTER TABLE `conversation_messages`
  ADD CONSTRAINT `conversation_messages_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `platform_commissions`
--
ALTER TABLE `platform_commissions`
  ADD CONSTRAINT `platform_commissions_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_availability`
--
ALTER TABLE `provider_availability`
  ADD CONSTRAINT `provider_availability_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_evaluations`
--
ALTER TABLE `provider_evaluations`
  ADD CONSTRAINT `provider_evaluations_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_evaluations_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_locations`
--
ALTER TABLE `provider_locations`
  ADD CONSTRAINT `provider_locations_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_locations_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_payouts`
--
ALTER TABLE `provider_payouts`
  ADD CONSTRAINT `provider_payouts_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_payouts_ibfk_2` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_payouts_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `provider_portfolio`
--
ALTER TABLE `provider_portfolio`
  ADD CONSTRAINT `provider_portfolio_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `provider_profiles`
--
ALTER TABLE `provider_profiles`
  ADD CONSTRAINT `provider_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_profiles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `provider_services`
--
ALTER TABLE `provider_services`
  ADD CONSTRAINT `provider_services_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `provider_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_status_history`
--
ALTER TABLE `request_status_history`
  ADD CONSTRAINT `request_status_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `request_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`provider_id`) REFERENCES `provider_profiles` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `service_requests_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_requests_ibfk_4` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
