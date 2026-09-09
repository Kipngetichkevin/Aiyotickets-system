-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 09, 2026 at 05:15 PM
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
-- Database: `aiyo_tickets`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendees`
--

CREATE TABLE `attendees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_status` varchar(255) NOT NULL DEFAULT 'pending',
  `transaction_code` varchar(255) DEFAULT NULL,
  `payment_mode` varchar(255) DEFAULT NULL,
  `payment_mode_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `uuid` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
('laravel_cache_system_settings', 'O:17:\"App\\Models\\System\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:6:\"system\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:29:{s:2:\"id\";i:1;s:4:\"name\";s:13:\"Sharet Africa\";s:4:\"logo\";N;s:9:\"logo_dark\";N;s:9:\"logo_icon\";N;s:7:\"favicon\";N;s:6:\"slogan\";s:25:\"Smart Property Management\";s:8:\"timezone\";s:14:\"Africa/Nairobi\";s:11:\"date_format\";s:5:\"d-m-Y\";s:11:\"time_format\";s:5:\"H:i:s\";s:8:\"currency\";s:3:\"KES\";s:15:\"currency_symbol\";s:3:\"KSh\";s:13:\"primary_color\";s:7:\"#0d6efd\";s:15:\"secondary_color\";s:7:\"#6c757d\";s:13:\"contact_email\";s:18:\"info@sharet.africa\";s:13:\"contact_phone\";s:13:\"+254700000000\";s:7:\"address\";s:14:\"Nairobi, Kenya\";s:8:\"location\";N;s:16:\"meta_description\";s:40:\"Sharet Africa Property Management System\";s:13:\"meta_keywords\";s:40:\"property,rent,tenant,landlord,management\";s:16:\"maintenance_mode\";i:0;s:16:\"pagination_limit\";i:15;s:10:\"custom_css\";N;s:9:\"custom_js\";N;s:8:\"settings\";N;s:13:\"website_pages\";N;s:12:\"social_media\";N;s:10:\"created_at\";s:19:\"2026-07-07 00:40:51\";s:10:\"updated_at\";s:19:\"2026-07-07 00:40:51\";}s:11:\"\0*\0original\";a:29:{s:2:\"id\";i:1;s:4:\"name\";s:13:\"Sharet Africa\";s:4:\"logo\";N;s:9:\"logo_dark\";N;s:9:\"logo_icon\";N;s:7:\"favicon\";N;s:6:\"slogan\";s:25:\"Smart Property Management\";s:8:\"timezone\";s:14:\"Africa/Nairobi\";s:11:\"date_format\";s:5:\"d-m-Y\";s:11:\"time_format\";s:5:\"H:i:s\";s:8:\"currency\";s:3:\"KES\";s:15:\"currency_symbol\";s:3:\"KSh\";s:13:\"primary_color\";s:7:\"#0d6efd\";s:15:\"secondary_color\";s:7:\"#6c757d\";s:13:\"contact_email\";s:18:\"info@sharet.africa\";s:13:\"contact_phone\";s:13:\"+254700000000\";s:7:\"address\";s:14:\"Nairobi, Kenya\";s:8:\"location\";N;s:16:\"meta_description\";s:40:\"Sharet Africa Property Management System\";s:13:\"meta_keywords\";s:40:\"property,rent,tenant,landlord,management\";s:16:\"maintenance_mode\";i:0;s:16:\"pagination_limit\";i:15;s:10:\"custom_css\";N;s:9:\"custom_js\";N;s:8:\"settings\";N;s:13:\"website_pages\";N;s:12:\"social_media\";N;s:10:\"created_at\";s:19:\"2026-07-07 00:40:51\";s:10:\"updated_at\";s:19:\"2026-07-07 00:40:51\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:5:{s:16:\"maintenance_mode\";s:7:\"boolean\";s:8:\"settings\";s:5:\"array\";s:13:\"website_pages\";s:5:\"array\";s:12:\"social_media\";s:5:\"array\";s:8:\"location\";s:5:\"array\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:26:{i:0;s:4:\"name\";i:1;s:4:\"logo\";i:2;s:9:\"logo_dark\";i:3;s:9:\"logo_icon\";i:4;s:7:\"favicon\";i:5;s:6:\"slogan\";i:6;s:8:\"timezone\";i:7;s:11:\"date_format\";i:8;s:11:\"time_format\";i:9;s:8:\"currency\";i:10;s:15:\"currency_symbol\";i:11;s:13:\"primary_color\";i:12;s:15:\"secondary_color\";i:13;s:13:\"contact_email\";i:14;s:13:\"contact_phone\";i:15;s:7:\"address\";i:16;s:8:\"location\";i:17;s:16:\"meta_description\";i:18;s:13:\"meta_keywords\";i:19;s:16:\"maintenance_mode\";i:20;s:16:\"pagination_limit\";i:21;s:10:\"custom_css\";i:22;s:9:\"custom_js\";i:23;s:8:\"settings\";i:24;s:13:\"website_pages\";i:25;s:12:\"social_media\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}', 2100079873);

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
(10, 1, 3, '0125f019-fa83-44e9-98e8-d37fe480116f', 'what-it-takes-0125f019-fa83-44e9-98e8-d37fe480116f', 1, 'What It Takes', 'Stand Up Comedy Special', '2025-05-24 18:00:00', '2025-05-24 20:00:00', 'Stand Up Comedy Special', 'KES', 'active', 'images/3H2OZp9iBsyjH9L7m86fZ6ltuXMQZDcUQQzmFqZ5.jpg', 'banners/xh9oNjYbefgE35UbqWHH3H6CesxUUjQGG8fchJpR.jpg', '2025-05-06 07:03:06', '2026-07-21 14:28:22'),
(11, 1, 5, 'e148b4cb-0155-4a69-8b23-95cf36c87214', 'dennis-kibet', 3, 'Dennis Kibet', 'h', '2026-07-21 01:34:00', '2026-07-21 01:34:00', 'Acorn House, James Gichuru rd', 'KES', 'active', 'images/3H2OZp9iBsyjH9L7m86fZ6ltuXMQZDcUQQzmFqZ5.jpg', 'banners/7xwp4ufvqg84l3TsC4neDcFlucPcUPtroJhqKeUN.jpg', '2026-07-06 22:40:05', '2026-07-21 14:28:22'),
(12, 1, 5, '2e03268b-ed85-4e43-9888-448db6769d23', 'crdb-bank-international-marathon-2026', 3, 'CRDB Bank International Marathon 2026', '<p>The seventh edition of the CRDB Bank International Marathon in Dar es Salaam is scheduled for August 16, 2026, preceded by the IMBEJU Sauti Moja Concert on August 14.</p><p><br></p><p><br></p><p>Event Schedule &amp; Highlights</p><ul><li><strong>Concert Date:</strong> August 14, 2026, at the TTCL Grounds in Kijitonyama, headlined by Trey Songz.</li><li><strong>Race Date:</strong> August 16, 2026.</li><li><strong>Categories:</strong> Full marathon, half marathon, and fun run options.</li><li><strong>Purpose:</strong> Raising funds for charity initiatives including child heart surgeries, maternal care, and youth projects.</li></ul><p><br></p><p>Departure Nairobi: <strong>13th August Nightfall</strong></p><p>Departure Dar: <strong>17th August Morning</strong></p><p><br></p><p><br></p>', '2026-08-14 08:00:00', '2026-08-17 08:00:00', 'Kijitonyama Postal Grounds, Dar es Salaam, Tanzania', 'KES', 'active', 'images/gePX1Tb0KfiP0JwSPE29eDXcdP2zz8dW47MPlLyW.png', 'banners/ixwmBnZsj0tKB8KKSTgD1Ci0JlM5JCQu0ILbfiX4.jpg', '2026-07-21 12:03:11', '2026-08-03 12:42:57');

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE `event_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_types`
--

INSERT INTO `event_types` (`id`, `category`, `name`, `description`, `icon`, `created_at`, `updated_at`) VALUES
(1, 'Sports', 'Marathon', 'Running races including marathons, 5K, 10K, trail and fun runs.', 'Footprints', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(2, 'Sports', 'Formula 1', 'Formula One Grand Prix and motorsport racing events.', 'Flag', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(3, 'Sports', 'Golf', 'Golf tournaments and championships.', 'Golf', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(4, 'Sports', 'Football', 'Football matches, leagues and tournaments.', 'Football', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(5, 'Sports', 'Rugby', 'Rugby competitions and championships.', 'Rugby', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(6, 'Sports', 'Basketball', 'Basketball leagues and tournaments.', 'Basketball', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(7, 'Sports', 'Volleyball', 'Indoor and beach volleyball events.', 'Volleyball', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(8, 'Sports', 'Tennis', 'Professional and amateur tennis events.', 'TennisBall', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(9, 'Sports', 'Badminton', 'Badminton competitions.', 'Badminton', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(10, 'Sports', 'Swimming', 'Swimming meets and championships.', 'Swimming', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(11, 'Sports', 'Cycling', 'Road cycling, mountain biking and BMX events.', 'Bike', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(12, 'Sports', 'Boxing', 'Professional and amateur boxing events.', 'BoxingGlove', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(13, 'Sports', 'Mixed Martial Arts', 'MMA and UFC-style competitions.', 'Shield', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(14, 'Sports', 'Athletics', 'Track and field competitions.', 'Running', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(15, 'Sports', 'Cricket', 'Cricket leagues and tournaments.', 'Cricket', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(16, 'Sports', 'Horse Racing', 'Horse racing meetings and championships.', 'Horse', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(17, 'Sports', 'Motocross', 'Motocross and off-road motorcycle competitions.', 'Motorcycle', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(18, 'Sports', 'Surfing', 'Surfing competitions.', 'Surfing', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(19, 'Sports', 'Skating', 'Skateboarding and roller sports.', 'Skateboard', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(20, 'Sports', 'Skiing', 'Skiing and snow sports competitions.', 'Skiing', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(21, 'Sports', 'Fitness', 'Fitness, CrossFit and bodybuilding competitions.', 'Dumbbell', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(22, 'Sports', 'Esports', 'Competitive gaming tournaments.', 'Gamepad2', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(23, 'Entertainment', 'Concert', 'Live music concerts and performances.', 'Music', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(24, 'Entertainment', 'Music Festival', 'Multi-day music festivals.', 'Headphones', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(25, 'Entertainment', 'Comedy', 'Stand-up comedy performances.', 'Smile', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(26, 'Entertainment', 'Theatre', 'Stage plays and theatrical performances.', 'Theater', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(27, 'Entertainment', 'Movie Premiere', 'Movie premieres and screenings.', 'Film', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(28, 'Entertainment', 'Nightlife', 'Club events, DJ shows and nightlife experiences.', 'GlassCocktail', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(29, 'Entertainment', 'Art Exhibition', 'Art galleries and exhibitions.', 'Palette', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(30, 'Business', 'Conference', 'Business conferences and summits.', 'Users', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(31, 'Business', 'Workshop', 'Hands-on workshops and training sessions.', 'Chalkboard', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(32, 'Business', 'Seminar', 'Educational seminars and talks.', 'Presentation', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(33, 'Business', 'Networking', 'Business networking events.', 'Handshake', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(34, 'Business', 'Expo', 'Trade exhibitions and expos.', 'Building2', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(35, 'Business', 'Product Launch', 'Launch events for products and services.', 'Rocket', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(36, 'Business', 'Hackathon', 'Innovation and software development competitions.', 'Code2', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(37, 'Community', 'Festival', 'Community and cultural festivals.', 'Sparkles', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(38, 'Community', 'Charity', 'Charity and fundraising events.', 'Heart', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(39, 'Community', 'Religious', 'Religious gatherings and conferences.', 'Church', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(40, 'Community', 'Family', 'Family-friendly events.', 'Users', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(41, 'Community', 'Awards', 'Awards ceremonies and galas.', 'Trophy', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(42, 'Community', 'School Event', 'School and education-related events.', 'GraduationCap', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(43, 'Lifestyle', 'Fashion', 'Fashion shows and runway events.', 'Shirt', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(44, 'Lifestyle', 'Food & Drink', 'Food festivals, wine tastings and culinary events.', 'Utensils', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(45, 'Lifestyle', 'Technology', 'Technology meetups and product showcases.', 'Microchip', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(46, 'Lifestyle', 'Health & Wellness', 'Health, wellness and mindfulness events.', 'Flower2', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(47, 'Lifestyle', 'Travel', 'Travel and tourism expos.', 'Plane', '2026-07-22 13:06:33', '2026-07-22 13:06:33'),
(48, 'Lifestyle', 'Wedding', 'Wedding expos and bridal shows.', 'Ring', '2026-07-22 13:06:33', '2026-07-22 13:06:33');

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
(2, 4, 'Rongo University', '0722346003', 'info@rongouni.ac.ke', 'Test', 'Rongo', '2025-03-15 03:34:15', '2025-03-15 03:34:15'),
(3, 5, 'Active On The Go', NULL, 'kibettdennis@gmail.com', NULL, NULL, '2026-07-21 14:23:00', '2026-07-21 14:23:00'),
(4, 1, 'Admin User', NULL, 'admin@aiyotickets.com', NULL, NULL, '2026-07-21 14:28:22', '2026-07-21 14:28:22');

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
(26, '2025_05_06_095059_add_ticket_no_to_tickets_table', 22),
(27, '0001_01_01_000003_create_system_table', 1);

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
-- Table structure for table `mpesa_stks`
--

CREATE TABLE `mpesa_stks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `merchant_request_id` varchar(255) DEFAULT NULL,
  `checkout_request_id` varchar(255) DEFAULT NULL,
  `response_code` varchar(50) DEFAULT NULL,
  `response_description` text DEFAULT NULL,
  `customer_message` text DEFAULT NULL,
  `response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `attendee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `result_code` varchar(50) DEFAULT NULL,
  `result_desc` text DEFAULT NULL,
  `amount` decimal(18,2) DEFAULT NULL,
  `mpesa_receipt_number` varchar(255) DEFAULT NULL,
  `transaction_date` varchar(20) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mpesa_stks`
--

INSERT INTO `mpesa_stks` (`id`, `merchant_request_id`, `checkout_request_id`, `response_code`, `response_description`, `customer_message`, `response`, `user_id`, `order_id`, `attendee_id`, `result_code`, `result_desc`, `amount`, `mpesa_receipt_number`, `transaction_date`, `phone_number`, `created_at`, `updated_at`) VALUES
(1, '26b9-428b-9d5d-a44defcfb2cc8376283', 'ws_CO_03082026165802625717492048', '0', 'The service request is processed successfully.', 'The service request is processed successfully.', '{\"MerchantRequestID\":\"26b9-428b-9d5d-a44defcfb2cc8376283\",\"CheckoutRequestID\":\"ws_CO_03082026165802625717492048\",\"ResultCode\":0,\"ResultDesc\":\"The service request is processed successfully.\",\"CallbackMetadata\":{\"Item\":[{\"Name\":\"Amount\",\"Value\":1},{\"Name\":\"MpesaReceiptNumber\",\"Value\":\"UH36O1CWL6\"},{\"Name\":\"Balance\"},{\"Name\":\"TransactionDate\",\"Value\":20260803165811},{\"Name\":\"PhoneNumber\",\"Value\":254717492048}]},\"raw\":{\"Body\":{\"stkCallback\":{\"MerchantRequestID\":\"26b9-428b-9d5d-a44defcfb2cc8376283\",\"CheckoutRequestID\":\"ws_CO_03082026165802625717492048\",\"ResultCode\":0,\"ResultDesc\":\"The service request is processed successfully.\",\"CallbackMetadata\":{\"Item\":[{\"Name\":\"Amount\",\"Value\":1},{\"Name\":\"MpesaReceiptNumber\",\"Value\":\"UH36O1CWL6\"},{\"Name\":\"Balance\"},{\"Name\":\"TransactionDate\",\"Value\":20260803165811},{\"Name\":\"PhoneNumber\",\"Value\":254717492048}]}}}}}', NULL, NULL, NULL, '0', 'The service request is processed successfully.', 1.00, 'UH36O1CWL6', '20260803165811', '254717492048', '2026-08-03 14:58:12', '2026-08-03 14:58:12'),
(2, '764a-4f11-8051-caef214d327c1955595', 'ws_CO_06082026095915556724606690', '1', 'The balance is insufficient for the transaction.', 'The balance is insufficient for the transaction.', '{\"MerchantRequestID\":\"764a-4f11-8051-caef214d327c1955595\",\"CheckoutRequestID\":\"ws_CO_06082026095915556724606690\",\"ResultCode\":1,\"ResultDesc\":\"The balance is insufficient for the transaction.\",\"raw\":{\"Body\":{\"stkCallback\":{\"MerchantRequestID\":\"764a-4f11-8051-caef214d327c1955595\",\"CheckoutRequestID\":\"ws_CO_06082026095915556724606690\",\"ResultCode\":1,\"ResultDesc\":\"The balance is insufficient for the transaction.\"}}}}', NULL, NULL, NULL, '1', 'The balance is insufficient for the transaction.', 2500.00, NULL, NULL, '254724606690', '2026-08-06 07:59:15', '2026-08-06 07:59:29');

-- --------------------------------------------------------

--
-- Table structure for table `network_prefixes`
--

CREATE TABLE `network_prefixes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `network` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `network_prefixes`
--

INSERT INTO `network_prefixes` (`id`, `prefix`, `network`, `created_at`, `updated_at`) VALUES
(1, '71', 'Safaricom', NULL, NULL),
(2, '72', 'Safaricom', NULL, NULL),
(3, '73', 'Safaricom', NULL, NULL),
(4, '74', 'Safaricom', NULL, NULL),
(5, '75', 'Safaricom', NULL, NULL),
(6, '76', 'Safaricom', NULL, NULL),
(7, '77', 'Safaricom', NULL, NULL),
(8, '78', 'Safaricom', NULL, NULL),
(9, '79', 'Safaricom', NULL, NULL),
(10, '10', 'Airtel', NULL, NULL),
(11, '11', 'Airtel', NULL, NULL),
(12, '12', 'Airtel', NULL, NULL),
(13, '13', 'Airtel', NULL, NULL),
(14, '14', 'Airtel', NULL, NULL),
(15, '15', 'Airtel', NULL, NULL),
(16, '16', 'Airtel', NULL, NULL),
(17, '17', 'Airtel', NULL, NULL),
(18, '18', 'Airtel', NULL, NULL),
(19, '19', 'Airtel', NULL, NULL),
(20, '70', 'Telkom', NULL, NULL);

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
  `transaction_code` varchar(255) DEFAULT NULL,
  `initiator` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `attendee_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uuid` varchar(255) NOT NULL,
  `discount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `refund` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(8,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('kibettdennis@gmail.com', '$2y$12$qYKv2M8b8Nd0O2aGgZzElOWhu6ecgZOQykLJuV4dkMNPNQbdmBEFu', '2026-07-21 16:26:13');

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
-- Table structure for table `payment_mode`
--

CREATE TABLE `payment_mode` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_mode`
--

INSERT INTO `payment_mode` (`id`, `name`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Mobile Money', 'mobile-money.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(2, 'Credit Card', 'credit-card.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(3, 'Debit Card', 'debit-card.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(4, 'Bank Transfer', 'bank-transfer.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(5, 'Cash', 'cash.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(6, 'USDT', 'usdt.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(7, 'USDC', 'usdc.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(8, 'SOL', 'sol.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(9, 'Bitcoin', 'bitcoin.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(10, 'Ethereum', 'ethereum.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35'),
(11, 'PayPal', 'paypal.png', '2026-07-07 10:55:35', '2026-07-07 10:55:35');

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
(3, 'merchant', 'web', '2025-03-13 07:01:25', '2025-03-13 07:01:25'),
(4, 'Super Admin', 'web', '2026-07-21 10:39:52', '2026-07-21 10:39:52'),
(5, 'Customer', 'web', '2026-07-21 10:40:14', '2026-07-21 10:40:14');

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
('tVi2gg8rlHUf4vrlf6lRi3RcMgjaEz42FLE5dDEf', NULL, '127.0.0.1', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoicmpjSUNjTVVVelBqS2V3bWhBZWM1dzNSSUZHRTRoYWxaa1VreG43VSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jYXJ0Ijt9czo0OiJjYXJ0IjthOjE6e2k6MjY7YTo1OntzOjk6InRpY2tldF9pZCI7czoyOiIyNiI7czo4OiJldmVudF9pZCI7aToxMTtzOjQ6Im5hbWUiO3M6MTI6IkRlbm5pcyBLaWJldCI7czo1OiJwcmljZSI7aToxO3M6ODoicXVhbnRpdHkiO2k6MTt9fX0=', 1784661452);

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
-- Table structure for table `system`
--

CREATE TABLE `system` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `logo_dark` varchar(255) DEFAULT NULL,
  `logo_icon` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `slogan` varchar(255) DEFAULT NULL,
  `timezone` varchar(255) NOT NULL DEFAULT 'UTC',
  `date_format` varchar(255) NOT NULL DEFAULT 'd-m-Y',
  `time_format` varchar(255) NOT NULL DEFAULT 'H:i:s',
  `currency` varchar(255) NOT NULL DEFAULT 'KES',
  `currency_symbol` varchar(255) NOT NULL DEFAULT 'KSh',
  `primary_color` varchar(255) DEFAULT NULL,
  `secondary_color` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `location` longtext DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `meta_keywords` text DEFAULT NULL,
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT 0,
  `pagination_limit` int(11) NOT NULL DEFAULT 15,
  `custom_css` text DEFAULT NULL,
  `custom_js` text DEFAULT NULL,
  `settings` longtext DEFAULT NULL,
  `website_pages` longtext DEFAULT NULL,
  `social_media` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `system`
--

INSERT INTO `system` (`id`, `name`, `logo`, `logo_dark`, `logo_icon`, `favicon`, `slogan`, `timezone`, `date_format`, `time_format`, `currency`, `currency_symbol`, `primary_color`, `secondary_color`, `contact_email`, `contact_phone`, `address`, `location`, `meta_description`, `meta_keywords`, `maintenance_mode`, `pagination_limit`, `custom_css`, `custom_js`, `settings`, `website_pages`, `social_media`, `created_at`, `updated_at`) VALUES
(1, 'Aiyo Tickets', NULL, NULL, NULL, NULL, 'Making Events Fun', 'Africa/Nairobi', 'd-m-Y', 'H:i:s', 'KES', 'KSh', '#0d6efd', '#6c757d', 'info@aiyotickets.com', '+254717492048', 'Nairobi, Kenya', NULL, 'AiyoTickets is Kenya\'s trusted online event ticketing platform. Discover, book, and manage tickets for concerts, conferences, sports, festivals, exhibitions, and corporate events with secure digital ticketing.', 'AiyoTickets, Kenya ticketing platform, online event tickets, event booking, Nairobi events, concerts, conferences, festivals, sports, exhibitions, secure payments, QR tickets, digital ticketing', 0, 15, NULL, NULL, NULL, NULL, NULL, '2026-07-06 21:40:51', '2026-07-06 21:40:51');

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
(18, 'TCK309D262C', 1, 10, 'Early Bird (VIP)', 'KES', 3500.00, 10, '2025-05-10 14:06:00', '2025-05-23 14:07:00', '2025-05-06 08:07:16', '2025-05-06 08:07:16'),
(24, 'TKT-V7ZXFIBTML', 1, 11, 'general admission', 'KES', 5000.00, 100, '2026-07-31 00:00:00', '2026-08-01 00:00:00', '2026-07-06 23:13:16', '2026-07-06 23:13:16'),
(25, 'TKT-RKTFGVSPY7', 1, 11, 'vip', 'KES', 550.00, 100, '2026-07-31 00:00:00', '2026-08-01 00:00:00', '2026-07-06 23:13:41', '2026-07-06 23:13:41'),
(26, 'TKT-NQ01W8UR6Q', 1, 11, '1 BOB!', 'KES', 1.00, 1000, '2026-05-30 00:00:00', '2026-06-28 00:00:00', '2026-07-07 11:38:18', '2026-08-03 12:11:39'),
(28, 'TKT-E1AY1S6LZG', 3, 11, 'Test', 'KES', 1.00, 1000, '2026-05-01 21:12:24', '2026-06-01 00:00:00', '2026-07-21 14:23:00', '2026-07-22 19:10:46'),
(29, 'TKT-MWDCHDIYMH', 3, 12, 'Race Bib', 'KES', 2500.00, 15, '2026-07-02 00:00:00', '2026-08-13 00:00:00', '2026-07-21 17:26:23', '2026-08-03 12:13:01'),
(30, 'TKT-ZZUTKARAOW', 3, 12, 'Race Bib + Trip + Stay 1 Nights', 'KES', 13500.00, 10, '2026-07-08 00:00:00', '2026-08-09 00:00:00', '2026-07-21 17:30:21', '2026-08-03 14:29:59'),
(31, 'TKT-LZOVGSZ4BB', 3, 12, 'Race Bib + Trip + Stay 2 Nights', 'KES', 15000.00, 10, '2026-07-09 00:00:00', '2026-08-09 00:00:00', '2026-07-22 12:53:49', '2026-08-03 14:27:39'),
(32, 'TKT-E67EOEOWW3', 3, 12, 'Race Bib + Trip + Stay - 3 Nights', 'KES', 17000.00, 10, '2026-07-10 00:00:00', '2026-08-02 00:00:00', '2026-07-22 12:55:53', '2026-08-03 14:28:32'),
(33, 'TKT-OMKAESEPQI', 3, 12, 'Race Bib + Trey Songz + Trip + Stay - 2 Nights', 'KES', 17500.00, 10, '2026-07-06 00:00:00', '2026-08-04 00:00:00', '2026-07-22 12:57:07', '2026-08-03 14:29:23'),
(34, 'TKT-VFYDCBRUWF', 3, 12, 'Race Bib + Trey Songz + Trip + Stay - 3 Nights', 'KES', 20000.00, 10, '2026-07-07 00:00:00', '2026-08-06 00:00:00', '2026-07-22 12:59:15', '2026-08-03 14:30:20');

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
(4, 'Rongo University', 'rongo.uin@rongouni.ac.ke', NULL, '$2y$12$nQsBsFeU/S6C.KTEsmB1C.nloP30g1umDzwtgIDdfmrvYG/xvziAC', NULL, '2025-03-15 03:34:15', '2025-03-15 03:34:15', 1),
(5, 'Dennis Kibet', 'kibettdennis@gmail.com', NULL, '$2y$12$Nav20kKNzmzTUGJC.k/5d.Y6aRRceTWWaKAOeY.IjaGeSMtiyGLr2', NULL, '2025-06-14 06:29:14', '2025-06-14 06:29:14', 0),
(6, 'xqakgdj', 'mvxe8m9ks4@oeqfut.com', NULL, '$2y$12$nCVd5tEneelomIEk/U.ZWu9UhbETOdgW0soPSTZ9xuksh5RBCbks2', NULL, '2026-09-04 12:37:31', '2026-09-04 12:37:31', 0);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_name` (`name`);

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
-- Indexes for table `mpesa_stks`
--
ALTER TABLE `mpesa_stks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_checkout_request_id` (`checkout_request_id`),
  ADD UNIQUE KEY `uk_merchant_request_id` (`merchant_request_id`),
  ADD KEY `idx_checkout_request_id` (`checkout_request_id`),
  ADD KEY `idx_merchant_request_id` (`merchant_request_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_attendee_id` (`attendee_id`);

--
-- Indexes for table `network_prefixes`
--
ALTER TABLE `network_prefixes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `network_prefixes_prefix_unique` (`prefix`);

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
-- Indexes for table `payment_mode`
--
ALTER TABLE `payment_mode`
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
-- Indexes for table `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `event_types`
--
ALTER TABLE `event_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `mpesa_stks`
--
ALTER TABLE `mpesa_stks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `network_prefixes`
--
ALTER TABLE `network_prefixes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_mode`
--
ALTER TABLE `payment_mode`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system`
--
ALTER TABLE `system`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- Constraints for table `mpesa_stks`
--
ALTER TABLE `mpesa_stks`
  ADD CONSTRAINT `fk_mpesa_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mpesa_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_mpesa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
