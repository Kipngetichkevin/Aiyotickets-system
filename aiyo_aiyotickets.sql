-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 12, 2025 at 11:06 AM
-- Server version: 10.3.39-MariaDB-0ubuntu0.20.04.2
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aiyo_aiyotickets`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendees`
--

CREATE TABLE `attendees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `uuid` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendees`
--

INSERT INTO `attendees` (`id`, `ticket_id`, `name`, `phone`, `email`, `qr_code`, `status`, `uuid`, `created_at`, `updated_at`, `created_by`) VALUES
(22, 17, 'Shadrack', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', 'd46ba080-b4aa-4876-80f2-84b814b17442', '2025-05-06 08:10:19', '2025-05-06 08:10:19', '1857c3e3-3d73-4512-89b3-ba3af94ef58a'),
(23, 17, 'Shady Kip', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', '35da2448-24a0-4d87-bdcf-b66eef48e476', '2025-05-09 04:40:03', '2025-05-09 04:40:03', 'e40f5795-681a-4c68-ab93-7688326bf42f'),
(24, 17, 'Shadrack', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', '9da8911a-015d-48a2-82c2-01e312bdca3d', '2025-05-09 05:48:36', '2025-05-09 05:48:36', '085d0866-da33-407b-a9d2-1743bedacafb'),
(25, 17, 'John Doe', '254724606690', 'soishady7@gmail.com', NULL, 'pending', '44e3925e-db7f-4ab9-9ca2-b0975429bc0e', '2025-05-09 06:22:01', '2025-05-09 06:22:01', 'fdb3f070-8d32-49da-bac8-062c3873654f'),
(26, 17, 'Kipkorir Soi', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', 'c00e4057-acc4-495a-ab90-de1e0c91cca4', '2025-05-09 06:25:23', '2025-05-09 06:25:23', 'df098c28-4351-4e79-aa77-d81107514968'),
(27, 17, 'Shady', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', 'e1e2a7ef-25d3-4334-9c1b-0f4ecb666127', '2025-05-09 06:30:36', '2025-05-09 06:30:36', 'de93f831-4819-4af4-8950-e54cf698e7cd'),
(28, 17, 'Shady', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', 'b9536ec3-43a5-4eb8-806d-9127d0b49fa9', '2025-05-10 01:11:15', '2025-05-10 01:11:15', '9f33a77a-3195-48c2-91e8-819715d23b59'),
(29, 17, 'Shady', '254724606690', 'shadykipkorir@gmail.com', NULL, 'pending', 'c59188ed-7eb4-42b8-a6de-41ac4823a6bf', '2025-05-12 04:10:00', '2025-05-12 04:10:00', '1d7aaff1-f60e-4529-9b85-de11c508b246');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('aiyotickets_cache_imaginenationke@gmail.com|41.90.172.59', 'i:1;', 1747986579),
('aiyotickets_cache_imaginenationke@gmail.com|41.90.172.59:timer', 'i:1747986579;', 1747986579),
('aiyotickets_cache_kibettdennis@gmail.com|197.232.44.175', 'i:1;', 1747902160),
('aiyotickets_cache_kibettdennis@gmail.com|197.232.44.175:timer', 'i:1747902160;', 1747902160),
('aiyotickets_cache_kibettdennis@gmail.com|41.90.172.59', 'i:1;', 1747986571),
('aiyotickets_cache_kibettdennis@gmail.com|41.90.172.59:timer', 'i:1747986571;', 1747986571),
('aiyotickets_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:0:{}s:11:\"permissions\";a:0:{}s:5:\"roles\";a:0:{}}', 1746612311);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `event_type_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `merchant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `currency` varchar(3) NOT NULL,
  `status` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `event_type_id`, `user_id`, `uuid`, `slug`, `merchant_id`, `name`, `description`, `date`, `end_date`, `location`, `currency`, `status`, `image`, `banner`, `created_at`, `updated_at`) VALUES
(10, 1, 3, '0125f019-fa83-44e9-98e8-d37fe480116f', 'what-it-takes-0125f019-fa83-44e9-98e8-d37fe480116f', 1, 'What It Takes', 'Stand Up Comedy Special', '2025-05-24 18:00:00', '2025-05-24 20:00:00', 'Stand Up Comedy Special', 'KES', 'active', 'images/uQi47itrkHhebwLLgJopaZNfONaIQOp9AKUNqdSM.jpg', 'banners/xh9oNjYbefgE35UbqWHH3H6CesxUUjQGG8fchJpR.jpg', '2025-05-06 07:03:06', '2025-05-06 07:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE `event_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_types`
--

INSERT INTO `event_types` (`id`, `name`, `created_at`, `updated_at`, `description`, `icon`, `user_id`) VALUES
(1, 'Concert', '2025-03-13 08:50:47', '2025-03-13 08:50:47', 'A musical performance in which several voices or instruments take part.', NULL, NULL),
(2, 'Workshop', '2025-03-13 08:50:47', '2025-03-13 08:50:47', 'Hands-on learning experiences.', NULL, NULL),
(3, 'Conference', '2025-03-13 08:50:47', '2025-03-13 08:50:47', 'Formal meetings for discussion.', NULL, NULL);

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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `merchants`
--

CREATE TABLE `merchants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `merchants`
--

INSERT INTO `merchants` (`id`, `user_id`, `name`, `phone`, `email`, `description`, `location`, `created_at`, `updated_at`) VALUES
(1, 3, 'Pulser', '0723456746', NULL, 'Photography', 'Westlands', '2025-03-14 11:57:48', '2025-03-14 11:57:48'),
(2, 4, 'Rongo University', '0722346003', 'info@rongouni.ac.ke', 'Test', 'Rongo', '2025-03-15 03:34:15', '2025-03-15 03:34:15');

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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_03_13_095638_create_permission_tables', 2),
(5, '2025_03_13_114114_create_event_types_table', 3),
(6, '2025_03_13_114615_add_description_to_event_types_table', 4),
(7, '2025_03_13_115429_create_events_table', 5),
(8, '2025_03_14_141907_add_is_merchant_to_users_table', 6),
(9, '2025_03_14_143637_create_merchants_table', 7),
(10, '2025_03_14_153140_create_tickets_table', 8),
(11, '2025_03_15_051727_create_attendees_table', 9),
(12, '2025_03_15_075205_create_settings_table', 10),
(13, '2025_03_16_060155_create_mpesa_s_t_k_s_table', 11),
(14, '2025_03_16_063737_add_amount_to_mpesa_s_t_k_s_table', 12),
(15, '2025_03_16_065436_create_personal_access_tokens_table', 13),
(16, '2025_03_17_063436_create_attendees_table', 14),
(17, '2025_03_17_071000_create_orders_table', 15),
(18, '2025_03_17_071013_create_order_items_table', 15),
(19, '2025_03_17_080919_add_created_by_to_attendees_table', 16),
(20, '2025_03_17_081629_add_event_id_to_orders_table', 16),
(21, '2025_03_17_082855_add_uuid_to_orders_table', 17),
(22, '2025_03_17_084725_create_payments_table', 18),
(23, '2025_03_17_091501_add_description_to_payments_table', 19),
(24, '2025_03_17_092217_add_reference_id_to_payments_table', 20),
(25, '2025_03_26_073836_add_refund_to_orders_table', 21),
(26, '2025_05_06_095059_add_ticket_no_to_tickets_table', 22);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Table structure for table `mpesa_s_t_k_s`
--

CREATE TABLE `mpesa_s_t_k_s` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `merchant_request_id` varchar(255) DEFAULT NULL,
  `checkout_request_id` varchar(255) DEFAULT NULL,
  `result_code` varchar(255) NOT NULL DEFAULT '1',
  `result_desc` varchar(255) DEFAULT NULL,
  `response` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `amount` varchar(255) DEFAULT NULL,
  `mpesa_receipt_number` varchar(255) DEFAULT NULL,
  `transaction_date` varchar(255) DEFAULT NULL,
  `phonenumber` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mpesa_s_t_k_s`
--

INSERT INTO `mpesa_s_t_k_s` (`id`, `merchant_request_id`, `checkout_request_id`, `result_code`, `result_desc`, `response`, `created_at`, `updated_at`, `amount`, `mpesa_receipt_number`, `transaction_date`, `phonenumber`) VALUES
(1, '801f-4ce5-97b8-f81bf214ea04332552', 'ws_CO_09052025111926849724606690', '0', 'Success. Request accepted for processing', '{\"MerchantRequestID\":\"801f-4ce5-97b8-f81bf214ea04332552\",\"CheckoutRequestID\":\"ws_CO_09052025111926849724606690\",\"ResponseCode\":\"0\",\"ResponseDescription\":\"Success. Request accepted for processing\",\"CustomerMessage\":\"Success. Request accepted for processing\"}', '2025-05-09 06:19:28', '2025-05-09 06:19:28', '1', NULL, NULL, '254724606690'),
(2, '5772-45ff-adb5-823779ddd1bc347963', 'ws_CO_09052025112421655724606690', '0', 'Success. Request accepted for processing', '{\"MerchantRequestID\":\"5772-45ff-adb5-823779ddd1bc347963\",\"CheckoutRequestID\":\"ws_CO_09052025112421655724606690\",\"ResponseCode\":\"0\",\"ResponseDescription\":\"Success. Request accepted for processing\",\"CustomerMessage\":\"Success. Request accepted for processing\"}', '2025-05-09 06:22:25', '2025-05-09 06:22:25', '1', NULL, NULL, '254724606690');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(255) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `total_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `initiator` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `uuid` varchar(255) NOT NULL,
  `discount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `refund` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(8,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `qty`, `total_amount`, `status`, `initiator`, `created_at`, `updated_at`, `event_id`, `uuid`, `discount`, `refund`, `tax`) VALUES
(22, 'ORD-356FE1FE', 1, 1500.00, 'pending', '1857c3e3-3d73-4512-89b3-ba3af94ef58a', '2025-05-06 08:10:19', '2025-05-06 08:10:19', 10, '52c6b737-03bb-474c-8496-02fb1435fa73', 0.00, 0.00, 0.00),
(27, 'ORD-B5D412B0', 1, 1.00, 'pending', 'de93f831-4819-4af4-8950-e54cf698e7cd', '2025-05-09 06:30:36', '2025-05-09 06:30:36', 10, 'f698a1c8-e517-471f-b46b-19dec5a0ccd8', 0.00, 0.00, 0.00),
(29, 'ORD-988FF996', 1, 1.00, 'pending', '1d7aaff1-f60e-4529-9b85-de11c508b246', '2025-05-12 04:10:00', '2025-05-12 04:10:00', 10, 'ccf44429-ef71-4a61-b463-eed1016da511', 0.00, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `event` varchar(255) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `total_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `price` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `ticket_id`, `order_id`, `event`, `qty`, `total_amount`, `price`, `created_at`, `updated_at`) VALUES
(17, 17, 22, 'What It Takes', 1, 1500.00, 1500.00, '2025-05-06 08:10:19', '2025-05-06 08:10:19'),
(22, 17, 27, 'What It Takes', 1, 1.00, 1.00, '2025-05-09 06:30:36', '2025-05-09 06:30:36'),
(24, 17, 29, 'What It Takes', 1, 1.00, 1.00, '2025-05-12 04:10:00', '2025-05-12 04:10:00');

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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(255) NOT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `amount_paid` decimal(18,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(18,2) NOT NULL DEFAULT 0.00,
  `transaction_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `currency` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_no`, `payment_mode`, `amount_paid`, `balance`, `transaction_id`, `name`, `phone`, `email`, `paid_at`, `status`, `created_at`, `updated_at`, `description`, `reference_id`, `currency`) VALUES
(2, 'ORD-C10D3232', 'Mpesa', 1.00, 0.00, 'TD283X0EYK', 'Shafrack Soi', '254724606690', 'shadykipkorir@gmail.com', NULL, 'paid', '2025-03-17 07:03:40', '2025-03-17 07:03:40', NULL, NULL, NULL),
(3, 'ORD-9413F4FF', NULL, 0.00, 1.00, NULL, 'shady', NULL, 'shadykipkorir@gmail.com', NULL, 'pending', '2025-05-09 06:19:28', '2025-05-09 06:19:28', 'Payment for event What It Takes', '801f-4ce5-97b8-f81bf214ea04332552', NULL),
(4, 'ORD-D3B7499A', NULL, 0.00, 1.00, NULL, 'Shady', NULL, 'shadykipkorir@gmail.com', NULL, 'pending', '2025-05-09 06:22:25', '2025-05-09 06:22:25', 'Payment for event What It Takes', '5772-45ff-adb5-823779ddd1bc347963', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-03-13 07:01:25', '2025-03-13 07:01:25'),
(2, 'user', 'web', '2025-03-13 07:01:25', '2025-03-13 07:01:25'),
(3, 'merchant', 'web', '2025-03-13 07:01:25', '2025-03-13 07:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1f8iiUNElQiCh8jsuaXsyFnj7CbLFd07gwGWBmc1', NULL, '176.9.91.109', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiNXVza3NyQmRtR1Mwc0c2NEIyMzIxR0tJY3pYWTE5T0U5MnpMT25IZSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749703758),
('CLiKhlKQy5FrBNzPDVtA3csRLUqwmRsKK2nLWL50', NULL, '43.156.156.96', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiV2o1TElMU3RURnpWcEk1ZWt0dEtQNHZ6akxxZklUUkdTTjRGQjB0YyI7czoxODoiZmxhc2hlcjo6ZW52ZWxvcGVzIjthOjA6e31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMjoiaHR0cDovL2FpeW90aWNrZXRzLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1749703472),
('fuz9XOQm9Nx9Gn61iMhFLJHVxabb9jujG20CGXp0', NULL, '82.156.68.74', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTTZqNlRzUGtCeDhMdldTQ2lrZTJIZndhYTVXdk93bzhOQXFnMFk2cCI7czoxODoiZmxhc2hlcjo6ZW52ZWxvcGVzIjthOjA6e31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMjoiaHR0cDovL2FpeW90aWNrZXRzLmNvbSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1749710146),
('nu6NPyZ3THLt5RT3AhzmH31VHOjQh0HyQz6NHdPr', NULL, '41.90.172.69', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNEhOYUtHS3o5b1FIMFVsTkF0WWFUNUpUTTZoZ1BoSThBNnRFMmlnWSI7czoxODoiZmxhc2hlcjo6ZW52ZWxvcGVzIjthOjA6e31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czoyMzoiaHR0cHM6Ly9haXlvdGlja2V0cy5jb20iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1749721096);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'sliders', '[\"uploads\\/s2Ke7uyjuFqzlMyb6CH1jpeP5BvdeGMBuKgfyD37.jpg\",\"uploads\\/Pgwkb5Pdfe3nKpNC2QTlEug3GLJxPuuNJTnmdJXp.jpg\",\"uploads\\/WYkq5I51ymMQGqBnRZDbrliLBywYLjrXy6f6l0si.jpg\"]', NULL, '2025-03-15 05:44:14', '2025-03-15 23:54:39');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_no` varchar(255) NOT NULL,
  `merchant_id` bigint(20) UNSIGNED NOT NULL,
  `event_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `currency` enum('KES','USD','USDT','EUROS','POUND') NOT NULL DEFAULT 'KES',
  `price` decimal(8,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_no`, `merchant_id`, `event_id`, `name`, `currency`, `price`, `quantity`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(17, 'TCKF32A3BE5', 1, 10, 'Early Bird', 'KES', 1.00, 100, '2025-05-05 00:00:00', '2025-05-24 00:00:00', '2025-05-06 08:05:37', '2025-05-06 08:05:37'),
(18, 'TCK309D262C', 1, 10, 'Early Bird (VIP)', 'KES', 3500.00, 10, '2025-05-10 14:06:00', '2025-05-23 14:07:00', '2025-05-06 08:07:16', '2025-05-06 08:07:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_merchant` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `is_merchant`) VALUES
(1, 'Admin User', 'admin@aiyotickets.com', '2025-03-13 07:02:36', '$2y$12$qhzcUWaqAc9NFRYlqW95vevk6rM.0sMIMWbAC4/OFzS7kig0Io69S', 'AaRNk9nmJMX5txIqphlDqEGAuFbFP056NxkwzC7oMu3ULIzKwzf8PhKyFcAq', '2025-03-13 07:02:36', '2025-03-13 07:02:36', 0),
(3, 'Kipkorir Soi', 'kipkorir.soi@gmail.com', NULL, '$2y$12$z.7hZr6m1Auo51dL2NItX.G3fKoiUw3ccFkXz/qfYgAsGV38FMuYy', NULL, '2025-03-14 11:57:48', '2025-03-14 11:57:48', 1),
(4, 'Rongo University', 'rongo.uin@rongouni.ac.ke', NULL, '$2y$12$nQsBsFeU/S6C.KTEsmB1C.nloP30g1umDzwtgIDdfmrvYG/xvziAC', NULL, '2025-03-15 03:34:15', '2025-03-15 03:34:15', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendees`
--
ALTER TABLE `attendees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `attendees_uuid_unique` (`uuid`),
  ADD KEY `attendees_ticket_id_foreign` (`ticket_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `events_uuid_unique` (`uuid`),
  ADD UNIQUE KEY `events_slug_unique` (`slug`);

--
-- Indexes for table `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `merchants_user_id_foreign` (`user_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `mpesa_s_t_k_s`
--
ALTER TABLE `mpesa_s_t_k_s`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_no_unique` (`order_no`),
  ADD UNIQUE KEY `orders_uuid_unique` (`uuid`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tickets_ticket_no_unique` (`ticket_no`),
  ADD KEY `tickets_merchant_id_foreign` (`merchant_id`),
  ADD KEY `tickets_event_id_foreign` (`event_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendees`
--
ALTER TABLE `attendees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `merchants`
--
ALTER TABLE `merchants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `mpesa_s_t_k_s`
--
ALTER TABLE `mpesa_s_t_k_s`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendees`
--
ALTER TABLE `attendees`
  ADD CONSTRAINT `attendees_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `merchants`
--
ALTER TABLE `merchants`
  ADD CONSTRAINT `merchants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
