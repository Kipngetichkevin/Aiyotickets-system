-- MySQL dump 10.13  Distrib 8.0.41, for macos15 (x86_64)
--
-- Host: localhost    Database: aiyotickets
-- ------------------------------------------------------
-- Server version	9.4.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendees`
--

DROP TABLE IF EXISTS `attendees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `payment_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `transaction_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_mode_id` bigint unsigned DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '1',
  `total_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendees_uuid_unique` (`uuid`),
  KEY `attendees_ticket_id_foreign` (`ticket_id`),
  CONSTRAINT `attendees_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendees`
--

LOCK TABLES `attendees` WRITE;
/*!40000 ALTER TABLE `attendees` DISABLE KEYS */;
INSERT INTO `attendees` VALUES (22,17,'Shadrack',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','d46ba080-b4aa-4876-80f2-84b814b17442','2025-05-06 08:10:19','2025-05-06 08:10:19','1857c3e3-3d73-4512-89b3-ba3af94ef58a'),(23,17,'Shady Kip',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','35da2448-24a0-4d87-bdcf-b66eef48e476','2025-05-09 04:40:03','2025-05-09 04:40:03','e40f5795-681a-4c68-ab93-7688326bf42f'),(24,17,'Shadrack',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','9da8911a-015d-48a2-82c2-01e312bdca3d','2025-05-09 05:48:36','2025-05-09 05:48:36','085d0866-da33-407b-a9d2-1743bedacafb'),(25,17,'John Doe',NULL,'254724606690','soishady7@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','44e3925e-db7f-4ab9-9ca2-b0975429bc0e','2025-05-09 06:22:01','2025-05-09 06:22:01','fdb3f070-8d32-49da-bac8-062c3873654f'),(26,17,'Kipkorir Soi',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','c00e4057-acc4-495a-ab90-de1e0c91cca4','2025-05-09 06:25:23','2025-05-09 06:25:23','df098c28-4351-4e79-aa77-d81107514968'),(27,17,'Shady',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','e1e2a7ef-25d3-4334-9c1b-0f4ecb666127','2025-05-09 06:30:36','2025-05-09 06:30:36','de93f831-4819-4af4-8950-e54cf698e7cd'),(28,17,'Shady',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','b9536ec3-43a5-4eb8-806d-9127d0b49fa9','2025-05-10 01:11:15','2025-05-10 01:11:15','9f33a77a-3195-48c2-91e8-819715d23b59'),(29,17,'Shady',NULL,'254724606690','shadykipkorir@gmail.com',NULL,NULL,'pending',NULL,NULL,NULL,1,0.00,'pending','c59188ed-7eb4-42b8-a6de-41ac4823a6bf','2025-05-12 04:10:00','2025-05-12 04:10:00','1d7aaff1-f60e-4529-9b85-de11c508b246');
/*!40000 ALTER TABLE `attendees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel_cache_system_settings','O:17:\"App\\Models\\System\":33:{s:13:\"\0*\0connection\";s:5:\"mysql\";s:8:\"\0*\0table\";s:6:\"system\";s:13:\"\0*\0primaryKey\";s:2:\"id\";s:10:\"\0*\0keyType\";s:3:\"int\";s:12:\"incrementing\";b:1;s:7:\"\0*\0with\";a:0:{}s:12:\"\0*\0withCount\";a:0:{}s:19:\"preventsLazyLoading\";b:0;s:10:\"\0*\0perPage\";i:15;s:6:\"exists\";b:1;s:18:\"wasRecentlyCreated\";b:0;s:28:\"\0*\0escapeWhenCastingToString\";b:0;s:13:\"\0*\0attributes\";a:29:{s:2:\"id\";i:1;s:4:\"name\";s:13:\"Sharet Africa\";s:4:\"logo\";N;s:9:\"logo_dark\";N;s:9:\"logo_icon\";N;s:7:\"favicon\";N;s:6:\"slogan\";s:25:\"Smart Property Management\";s:8:\"timezone\";s:14:\"Africa/Nairobi\";s:11:\"date_format\";s:5:\"d-m-Y\";s:11:\"time_format\";s:5:\"H:i:s\";s:8:\"currency\";s:3:\"KES\";s:15:\"currency_symbol\";s:3:\"KSh\";s:13:\"primary_color\";s:7:\"#0d6efd\";s:15:\"secondary_color\";s:7:\"#6c757d\";s:13:\"contact_email\";s:18:\"info@sharet.africa\";s:13:\"contact_phone\";s:13:\"+254700000000\";s:7:\"address\";s:14:\"Nairobi, Kenya\";s:8:\"location\";N;s:16:\"meta_description\";s:40:\"Sharet Africa Property Management System\";s:13:\"meta_keywords\";s:40:\"property,rent,tenant,landlord,management\";s:16:\"maintenance_mode\";i:0;s:16:\"pagination_limit\";i:15;s:10:\"custom_css\";N;s:9:\"custom_js\";N;s:8:\"settings\";N;s:13:\"website_pages\";N;s:12:\"social_media\";N;s:10:\"created_at\";s:19:\"2026-07-07 00:40:51\";s:10:\"updated_at\";s:19:\"2026-07-07 00:40:51\";}s:11:\"\0*\0original\";a:29:{s:2:\"id\";i:1;s:4:\"name\";s:13:\"Sharet Africa\";s:4:\"logo\";N;s:9:\"logo_dark\";N;s:9:\"logo_icon\";N;s:7:\"favicon\";N;s:6:\"slogan\";s:25:\"Smart Property Management\";s:8:\"timezone\";s:14:\"Africa/Nairobi\";s:11:\"date_format\";s:5:\"d-m-Y\";s:11:\"time_format\";s:5:\"H:i:s\";s:8:\"currency\";s:3:\"KES\";s:15:\"currency_symbol\";s:3:\"KSh\";s:13:\"primary_color\";s:7:\"#0d6efd\";s:15:\"secondary_color\";s:7:\"#6c757d\";s:13:\"contact_email\";s:18:\"info@sharet.africa\";s:13:\"contact_phone\";s:13:\"+254700000000\";s:7:\"address\";s:14:\"Nairobi, Kenya\";s:8:\"location\";N;s:16:\"meta_description\";s:40:\"Sharet Africa Property Management System\";s:13:\"meta_keywords\";s:40:\"property,rent,tenant,landlord,management\";s:16:\"maintenance_mode\";i:0;s:16:\"pagination_limit\";i:15;s:10:\"custom_css\";N;s:9:\"custom_js\";N;s:8:\"settings\";N;s:13:\"website_pages\";N;s:12:\"social_media\";N;s:10:\"created_at\";s:19:\"2026-07-07 00:40:51\";s:10:\"updated_at\";s:19:\"2026-07-07 00:40:51\";}s:10:\"\0*\0changes\";a:0:{}s:11:\"\0*\0previous\";a:0:{}s:8:\"\0*\0casts\";a:5:{s:16:\"maintenance_mode\";s:7:\"boolean\";s:8:\"settings\";s:5:\"array\";s:13:\"website_pages\";s:5:\"array\";s:12:\"social_media\";s:5:\"array\";s:8:\"location\";s:5:\"array\";}s:17:\"\0*\0classCastCache\";a:0:{}s:21:\"\0*\0attributeCastCache\";a:0:{}s:13:\"\0*\0dateFormat\";N;s:10:\"\0*\0appends\";a:0:{}s:19:\"\0*\0dispatchesEvents\";a:0:{}s:14:\"\0*\0observables\";a:0:{}s:12:\"\0*\0relations\";a:0:{}s:10:\"\0*\0touches\";a:0:{}s:27:\"\0*\0relationAutoloadCallback\";N;s:26:\"\0*\0relationAutoloadContext\";N;s:10:\"timestamps\";b:1;s:13:\"usesUniqueIds\";b:0;s:9:\"\0*\0hidden\";a:0:{}s:10:\"\0*\0visible\";a:0:{}s:11:\"\0*\0fillable\";a:26:{i:0;s:4:\"name\";i:1;s:4:\"logo\";i:2;s:9:\"logo_dark\";i:3;s:9:\"logo_icon\";i:4;s:7:\"favicon\";i:5;s:6:\"slogan\";i:6;s:8:\"timezone\";i:7;s:11:\"date_format\";i:8;s:11:\"time_format\";i:9;s:8:\"currency\";i:10;s:15:\"currency_symbol\";i:11;s:13:\"primary_color\";i:12;s:15:\"secondary_color\";i:13;s:13:\"contact_email\";i:14;s:13:\"contact_phone\";i:15;s:7:\"address\";i:16;s:8:\"location\";i:17;s:16:\"meta_description\";i:18;s:13:\"meta_keywords\";i:19;s:16:\"maintenance_mode\";i:20;s:16:\"pagination_limit\";i:21;s:10:\"custom_css\";i:22;s:9:\"custom_js\";i:23;s:8:\"settings\";i:24;s:13:\"website_pages\";i:25;s:12:\"social_media\";}s:10:\"\0*\0guarded\";a:1:{i:0;s:1:\"*\";}}',2100079873);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_types`
--

DROP TABLE IF EXISTS `event_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `event_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext,
  `icon` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_types`
--

LOCK TABLES `event_types` WRITE;
/*!40000 ALTER TABLE `event_types` DISABLE KEYS */;
INSERT INTO `event_types` VALUES (1,'Sports','Marathon','Running races including marathons, 5K, 10K, trail and fun runs.','Footprints','2026-07-22 13:06:33','2026-07-22 13:06:33'),(2,'Sports','Formula 1','Formula One Grand Prix and motorsport racing events.','Flag','2026-07-22 13:06:33','2026-07-22 13:06:33'),(3,'Sports','Golf','Golf tournaments and championships.','Golf','2026-07-22 13:06:33','2026-07-22 13:06:33'),(4,'Sports','Football','Football matches, leagues and tournaments.','Football','2026-07-22 13:06:33','2026-07-22 13:06:33'),(5,'Sports','Rugby','Rugby competitions and championships.','Rugby','2026-07-22 13:06:33','2026-07-22 13:06:33'),(6,'Sports','Basketball','Basketball leagues and tournaments.','Basketball','2026-07-22 13:06:33','2026-07-22 13:06:33'),(7,'Sports','Volleyball','Indoor and beach volleyball events.','Volleyball','2026-07-22 13:06:33','2026-07-22 13:06:33'),(8,'Sports','Tennis','Professional and amateur tennis events.','TennisBall','2026-07-22 13:06:33','2026-07-22 13:06:33'),(9,'Sports','Badminton','Badminton competitions.','Badminton','2026-07-22 13:06:33','2026-07-22 13:06:33'),(10,'Sports','Swimming','Swimming meets and championships.','Swimming','2026-07-22 13:06:33','2026-07-22 13:06:33'),(11,'Sports','Cycling','Road cycling, mountain biking and BMX events.','Bike','2026-07-22 13:06:33','2026-07-22 13:06:33'),(12,'Sports','Boxing','Professional and amateur boxing events.','BoxingGlove','2026-07-22 13:06:33','2026-07-22 13:06:33'),(13,'Sports','Mixed Martial Arts','MMA and UFC-style competitions.','Shield','2026-07-22 13:06:33','2026-07-22 13:06:33'),(14,'Sports','Athletics','Track and field competitions.','Running','2026-07-22 13:06:33','2026-07-22 13:06:33'),(15,'Sports','Cricket','Cricket leagues and tournaments.','Cricket','2026-07-22 13:06:33','2026-07-22 13:06:33'),(16,'Sports','Horse Racing','Horse racing meetings and championships.','Horse','2026-07-22 13:06:33','2026-07-22 13:06:33'),(17,'Sports','Motocross','Motocross and off-road motorcycle competitions.','Motorcycle','2026-07-22 13:06:33','2026-07-22 13:06:33'),(18,'Sports','Surfing','Surfing competitions.','Surfing','2026-07-22 13:06:33','2026-07-22 13:06:33'),(19,'Sports','Skating','Skateboarding and roller sports.','Skateboard','2026-07-22 13:06:33','2026-07-22 13:06:33'),(20,'Sports','Skiing','Skiing and snow sports competitions.','Skiing','2026-07-22 13:06:33','2026-07-22 13:06:33'),(21,'Sports','Fitness','Fitness, CrossFit and bodybuilding competitions.','Dumbbell','2026-07-22 13:06:33','2026-07-22 13:06:33'),(22,'Sports','Esports','Competitive gaming tournaments.','Gamepad2','2026-07-22 13:06:33','2026-07-22 13:06:33'),(23,'Entertainment','Concert','Live music concerts and performances.','Music','2026-07-22 13:06:33','2026-07-22 13:06:33'),(24,'Entertainment','Music Festival','Multi-day music festivals.','Headphones','2026-07-22 13:06:33','2026-07-22 13:06:33'),(25,'Entertainment','Comedy','Stand-up comedy performances.','Smile','2026-07-22 13:06:33','2026-07-22 13:06:33'),(26,'Entertainment','Theatre','Stage plays and theatrical performances.','Theater','2026-07-22 13:06:33','2026-07-22 13:06:33'),(27,'Entertainment','Movie Premiere','Movie premieres and screenings.','Film','2026-07-22 13:06:33','2026-07-22 13:06:33'),(28,'Entertainment','Nightlife','Club events, DJ shows and nightlife experiences.','GlassCocktail','2026-07-22 13:06:33','2026-07-22 13:06:33'),(29,'Entertainment','Art Exhibition','Art galleries and exhibitions.','Palette','2026-07-22 13:06:33','2026-07-22 13:06:33'),(30,'Business','Conference','Business conferences and summits.','Users','2026-07-22 13:06:33','2026-07-22 13:06:33'),(31,'Business','Workshop','Hands-on workshops and training sessions.','Chalkboard','2026-07-22 13:06:33','2026-07-22 13:06:33'),(32,'Business','Seminar','Educational seminars and talks.','Presentation','2026-07-22 13:06:33','2026-07-22 13:06:33'),(33,'Business','Networking','Business networking events.','Handshake','2026-07-22 13:06:33','2026-07-22 13:06:33'),(34,'Business','Expo','Trade exhibitions and expos.','Building2','2026-07-22 13:06:33','2026-07-22 13:06:33'),(35,'Business','Product Launch','Launch events for products and services.','Rocket','2026-07-22 13:06:33','2026-07-22 13:06:33'),(36,'Business','Hackathon','Innovation and software development competitions.','Code2','2026-07-22 13:06:33','2026-07-22 13:06:33'),(37,'Community','Festival','Community and cultural festivals.','Sparkles','2026-07-22 13:06:33','2026-07-22 13:06:33'),(38,'Community','Charity','Charity and fundraising events.','Heart','2026-07-22 13:06:33','2026-07-22 13:06:33'),(39,'Community','Religious','Religious gatherings and conferences.','Church','2026-07-22 13:06:33','2026-07-22 13:06:33'),(40,'Community','Family','Family-friendly events.','Users','2026-07-22 13:06:33','2026-07-22 13:06:33'),(41,'Community','Awards','Awards ceremonies and galas.','Trophy','2026-07-22 13:06:33','2026-07-22 13:06:33'),(42,'Community','School Event','School and education-related events.','GraduationCap','2026-07-22 13:06:33','2026-07-22 13:06:33'),(43,'Lifestyle','Fashion','Fashion shows and runway events.','Shirt','2026-07-22 13:06:33','2026-07-22 13:06:33'),(44,'Lifestyle','Food & Drink','Food festivals, wine tastings and culinary events.','Utensils','2026-07-22 13:06:33','2026-07-22 13:06:33'),(45,'Lifestyle','Technology','Technology meetups and product showcases.','Microchip','2026-07-22 13:06:33','2026-07-22 13:06:33'),(46,'Lifestyle','Health & Wellness','Health, wellness and mindfulness events.','Flower2','2026-07-22 13:06:33','2026-07-22 13:06:33'),(47,'Lifestyle','Travel','Travel and tourism expos.','Plane','2026-07-22 13:06:33','2026-07-22 13:06:33'),(48,'Lifestyle','Wedding','Wedding expos and bridal shows.','Ring','2026-07-22 13:06:33','2026-07-22 13:06:33');
/*!40000 ALTER TABLE `event_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_type_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merchant_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `events_uuid_unique` (`uuid`),
  UNIQUE KEY `events_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (10,1,3,'0125f019-fa83-44e9-98e8-d37fe480116f','what-it-takes-0125f019-fa83-44e9-98e8-d37fe480116f',1,'What It Takes','Stand Up Comedy Special','2025-05-24 18:00:00','2025-05-24 20:00:00','Stand Up Comedy Special','KES','active','images/uQi47itrkHhebwLLgJopaZNfONaIQOp9AKUNqdSM.jpg','banners/xh9oNjYbefgE35UbqWHH3H6CesxUUjQGG8fchJpR.jpg','2025-05-06 07:03:06','2026-07-21 14:28:22'),(11,1,5,'e148b4cb-0155-4a69-8b23-95cf36c87214','dennis-kibet',3,'Dennis Kibet','h','2026-07-31 01:34:00','2026-08-01 01:34:00','Acorn House, James Gichuru rd','KES','active','images/3H2OZp9iBsyjH9L7m86fZ6ltuXMQZDcUQQzmFqZ5.jpg','banners/7xwp4ufvqg84l3TsC4neDcFlucPcUPtroJhqKeUN.jpg','2026-07-06 22:40:05','2026-07-21 14:28:22'),(12,1,5,'2e03268b-ed85-4e43-9888-448db6769d23','crdb-bank-international-marathon-2026',3,'CRDB Bank International Marathon 2026','<p>The seventh edition of the CRDB Bank International Marathon in Dar es Salaam is scheduled for August 16, 2026, preceded by the IMBEJU Sauti Moja Concert on August 14.</p><p><br></p><p><br></p><p>Event Schedule &amp; Highlights</p><ul><li><strong>Concert Date:</strong> August 14, 2026, at the TTCL Grounds in Kijitonyama, headlined by Trey Songz.</li><li><strong>Race Date:</strong> August 16, 2026.</li><li><strong>Categories:</strong> Full marathon, half marathon, and fun run options.</li><li><strong>Purpose:</strong> Raising funds for charity initiatives including child heart surgeries, maternal care, and youth projects.</li></ul><p><br></p><p><br></p>','2026-08-14 08:00:00','2026-08-17 08:00:00','Kijitonyama Postal Grounds, Dar es Salaam, Tanzania','KES','active','images/gePX1Tb0KfiP0JwSPE29eDXcdP2zz8dW47MPlLyW.png','banners/7qedrijubbAqRIriZv1QJTUiIs6DZlmv2CChc4b1.jpg','2026-07-21 12:03:11','2026-07-22 10:14:37');
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `merchants`
--

DROP TABLE IF EXISTS `merchants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `merchants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `merchants_user_id_foreign` (`user_id`),
  CONSTRAINT `merchants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `merchants`
--

LOCK TABLES `merchants` WRITE;
/*!40000 ALTER TABLE `merchants` DISABLE KEYS */;
INSERT INTO `merchants` VALUES (1,3,'Pulser','0723456746',NULL,'Photography','Westlands','2025-03-14 11:57:48','2025-03-14 11:57:48'),(2,4,'Rongo University','0722346003','info@rongouni.ac.ke','Test','Rongo','2025-03-15 03:34:15','2025-03-15 03:34:15'),(3,5,'Dennis Kibet',NULL,'kibettdennis@gmail.com',NULL,NULL,'2026-07-21 14:23:00','2026-07-21 14:23:00'),(4,1,'Admin User',NULL,'admin@aiyotickets.com',NULL,NULL,'2026-07-21 14:28:22','2026-07-21 14:28:22');
/*!40000 ALTER TABLE `merchants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_03_13_095638_create_permission_tables',2),(5,'2025_03_13_114114_create_event_types_table',3),(6,'2025_03_13_114615_add_description_to_event_types_table',4),(7,'2025_03_13_115429_create_events_table',5),(8,'2025_03_14_141907_add_is_merchant_to_users_table',6),(9,'2025_03_14_143637_create_merchants_table',7),(10,'2025_03_14_153140_create_tickets_table',8),(11,'2025_03_15_051727_create_attendees_table',9),(12,'2025_03_15_075205_create_settings_table',10),(13,'2025_03_16_060155_create_mpesa_s_t_k_s_table',11),(14,'2025_03_16_063737_add_amount_to_mpesa_s_t_k_s_table',12),(15,'2025_03_16_065436_create_personal_access_tokens_table',13),(16,'2025_03_17_063436_create_attendees_table',14),(17,'2025_03_17_071000_create_orders_table',15),(18,'2025_03_17_071013_create_order_items_table',15),(19,'2025_03_17_080919_add_created_by_to_attendees_table',16),(20,'2025_03_17_081629_add_event_id_to_orders_table',16),(21,'2025_03_17_082855_add_uuid_to_orders_table',17),(22,'2025_03_17_084725_create_payments_table',18),(23,'2025_03_17_091501_add_description_to_payments_table',19),(24,'2025_03_17_092217_add_reference_id_to_payments_table',20),(25,'2025_03_26_073836_add_refund_to_orders_table',21),(26,'2025_05_06_095059_add_ticket_no_to_tickets_table',22),(27,'0001_01_01_000003_create_system_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(3,'App\\Models\\User',2),(3,'App\\Models\\User',3),(3,'App\\Models\\User',4);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpesa_s_t_k_s`
--

DROP TABLE IF EXISTS `mpesa_s_t_k_s`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpesa_s_t_k_s` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_request_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checkout_request_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `result_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `result_desc` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `response` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `amount` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mpesa_receipt_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phonenumber` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpesa_s_t_k_s`
--

LOCK TABLES `mpesa_s_t_k_s` WRITE;
/*!40000 ALTER TABLE `mpesa_s_t_k_s` DISABLE KEYS */;
INSERT INTO `mpesa_s_t_k_s` VALUES (1,'801f-4ce5-97b8-f81bf214ea04332552','ws_CO_09052025111926849724606690','0','Success. Request accepted for processing','{\"MerchantRequestID\":\"801f-4ce5-97b8-f81bf214ea04332552\",\"CheckoutRequestID\":\"ws_CO_09052025111926849724606690\",\"ResponseCode\":\"0\",\"ResponseDescription\":\"Success. Request accepted for processing\",\"CustomerMessage\":\"Success. Request accepted for processing\"}','2025-05-09 06:19:28','2025-05-09 06:19:28','1',NULL,NULL,'254724606690'),(2,'5772-45ff-adb5-823779ddd1bc347963','ws_CO_09052025112421655724606690','0','Success. Request accepted for processing','{\"MerchantRequestID\":\"5772-45ff-adb5-823779ddd1bc347963\",\"CheckoutRequestID\":\"ws_CO_09052025112421655724606690\",\"ResponseCode\":\"0\",\"ResponseDescription\":\"Success. Request accepted for processing\",\"CustomerMessage\":\"Success. Request accepted for processing\"}','2025-05-09 06:22:25','2025-05-09 06:22:25','1',NULL,NULL,'254724606690');
/*!40000 ALTER TABLE `mpesa_s_t_k_s` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mpesa_stks`
--

DROP TABLE IF EXISTS `mpesa_stks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mpesa_stks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_request_id` varchar(255) DEFAULT NULL,
  `checkout_request_id` varchar(255) DEFAULT NULL,
  `response_code` varchar(50) DEFAULT NULL,
  `response_description` text,
  `customer_message` text,
  `response` json DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `attendee_id` bigint unsigned DEFAULT NULL,
  `result_code` varchar(50) DEFAULT NULL,
  `result_desc` text,
  `amount` decimal(18,2) DEFAULT NULL,
  `mpesa_receipt_number` varchar(255) DEFAULT NULL,
  `transaction_date` varchar(20) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_checkout_request_id` (`checkout_request_id`),
  UNIQUE KEY `uk_merchant_request_id` (`merchant_request_id`),
  KEY `idx_checkout_request_id` (`checkout_request_id`),
  KEY `idx_merchant_request_id` (`merchant_request_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_attendee_id` (`attendee_id`),
  CONSTRAINT `fk_mpesa_attendee` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mpesa_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_mpesa_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mpesa_stks`
--

LOCK TABLES `mpesa_stks` WRITE;
/*!40000 ALTER TABLE `mpesa_stks` DISABLE KEYS */;
INSERT INTO `mpesa_stks` VALUES (1,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"6dec-4384-9d8e-266265e15c171098799\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:13:33','2026-07-07 11:13:33'),(2,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"b38a-4eaa-827f-b7108586b3ba1357697\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:13:38','2026-07-07 11:13:38'),(3,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"e5ec-4eaa-a39b-6ade3fba7d901018530\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:13:46','2026-07-07 11:13:46'),(4,NULL,NULL,NULL,NULL,NULL,'{\"errorCode\": \"404.001.03\", \"requestId\": \"74ee-4222-b5c3-2fc1f466a2611110505\", \"errorMessage\": \"Invalid Access Token\"}',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:17:08','2026-07-07 11:17:08'),(5,NULL,NULL,NULL,NULL,NULL,'{\"errorCode\": \"404.001.03\", \"requestId\": \"f5b5-465a-a063-4e2c175fdae510407411\", \"errorMessage\": \"Invalid Access Token\"}',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:17:34','2026-07-07 11:17:34'),(6,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"48b4-4946-807f-43ca645e24f5511280\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:33:57','2026-07-07 11:33:57'),(7,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"48b4-4946-807f-43ca645e24f5514617\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:36:47','2026-07-07 11:36:47'),(8,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"0cd8-47a1-84e1-d0c207367dab1401152\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:38:41','2026-07-07 11:38:41'),(9,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"0cd8-47a1-84e1-d0c207367dab1404809\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:41:47','2026-07-07 11:41:47'),(10,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"b38a-4eaa-827f-b7108586b3ba1394112\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 11:44:18','2026-07-07 11:44:18'),(11,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"0cd8-47a1-84e1-d0c207367dab1457553\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 12:25:56','2026-07-07 12:25:56'),(12,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"ddab-4cac-b1d2-f2d72edc091b4449727\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 15:14:35','2026-07-07 15:14:35'),(13,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"2b5d-48d5-b0f2-d7c8ffe0cfc01228157\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-07 15:16:46','2026-07-07 15:16:46'),(14,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"6045-4f7f-ba5e-386a6df576841987660\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 11:00:49','2026-07-17 11:00:49'),(15,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"6977-49c0-9c76-c451f8a44b591718001\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 11:00:54','2026-07-17 11:00:54'),(16,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"eba7-4497-8c98-061e08ff22491813495\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 11:01:12','2026-07-17 11:01:12'),(17,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"2bb0-438d-bf46-e8b7bebe04471679766\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 11:03:08','2026-07-17 11:03:08'),(18,NULL,NULL,NULL,NULL,NULL,'\"{\\\"requestId\\\":\\\"64a3-4fc0-b422-f4608dc9f1bb1590530\\\",\\\"errorCode\\\":\\\"404.001.03\\\",\\\"errorMessage\\\":\\\"Invalid Access Token\\\"}\"',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-07-17 11:15:32','2026-07-17 11:15:32'),(19,NULL,NULL,'ERROR','M-Pesa Error: Invalid Access Token','Payment failed','{\"error\": \"M-Pesa Error: Invalid Access Token\"}',NULL,NULL,NULL,'2','M-Pesa Error: Invalid Access Token',1.00,NULL,NULL,'254708374149','2026-07-17 12:35:28','2026-07-17 12:35:28'),(20,NULL,NULL,'ERROR','M-Pesa Error: Invalid Access Token','Payment failed','{\"error\": \"M-Pesa Error: Invalid Access Token\"}',NULL,NULL,NULL,'2','M-Pesa Error: Invalid Access Token',1.00,NULL,NULL,'254708374149','2026-07-17 12:36:20','2026-07-17 12:36:20'),(21,'d58a-4a7a-996c-b61167cc3f7134955','ws_CO_17072026154112768708374149','0','Request sent to M-Pesa','Please check your phone to complete payment','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1037, \"ResultDesc\": \"No response from user.\", \"CheckoutRequestID\": \"ws_CO_17072026154112768708374149\", \"MerchantRequestID\": \"d58a-4a7a-996c-b61167cc3f7134955\"}}}, \"ResultCode\": 1037, \"ResultDesc\": \"No response from user.\", \"CheckoutRequestID\": \"ws_CO_17072026154112768708374149\", \"MerchantRequestID\": \"d58a-4a7a-996c-b61167cc3f7134955\"}',NULL,NULL,NULL,'1037','No response from user.',1.00,NULL,NULL,'254708374149','2026-07-17 12:41:12','2026-07-17 12:41:39'),(22,'d58a-4a7a-996c-b61167cc3f7135189','ws_CO_17072026154241131727371496','0','Request sent to M-Pesa','Please check your phone to complete payment','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGHJ3BGTU2\"}, {\"Name\": \"Balance\", \"Value\": 0}, {\"Name\": \"TransactionDate\", \"Value\": 20260717154310}, {\"Name\": \"PhoneNumber\", \"Value\": 254727371496}]}, \"CheckoutRequestID\": \"ws_CO_17072026154241131727371496\", \"MerchantRequestID\": \"d58a-4a7a-996c-b61167cc3f7135189\"}}}, \"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGHJ3BGTU2\"}, {\"Name\": \"Balance\", \"Value\": 0}, {\"Name\": \"TransactionDate\", \"Value\": 20260717154310}, {\"Name\": \"PhoneNumber\", \"Value\": 254727371496}]}, \"CheckoutRequestID\": \"ws_CO_17072026154241131727371496\", \"MerchantRequestID\": \"d58a-4a7a-996c-b61167cc3f7135189\"}',NULL,NULL,NULL,'0','The service request is processed successfully.',1.00,'UGHJ3BGTU2','20260717154310','254727371496','2026-07-17 12:42:40','2026-07-17 12:43:11'),(23,'d58a-4a7a-996c-b61167cc3f7135305','ws_CO_17072026154306654717492048','0','Request sent to M-Pesa','Please check your phone to complete payment','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGH6OBDBEH\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260717154316}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_17072026154306654717492048\", \"MerchantRequestID\": \"d58a-4a7a-996c-b61167cc3f7135305\"}}}, \"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGH6OBDBEH\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260717154316}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_17072026154306654717492048\", \"MerchantRequestID\": \"d58a-4a7a-996c-b61167cc3f7135305\"}',NULL,NULL,NULL,'0','The service request is processed successfully.',1.00,'UGH6OBDBEH','20260717154316','254717492048','2026-07-17 12:43:06','2026-07-17 12:43:17'),(24,NULL,NULL,'ERROR','M-Pesa Error: Invalid Access Token','Payment failed','{\"error\": \"M-Pesa Error: Invalid Access Token\"}',NULL,NULL,NULL,'2','M-Pesa Error: Invalid Access Token',1.00,NULL,NULL,'254717492048','2026-07-17 12:46:59','2026-07-17 12:46:59'),(25,'1294-459c-9072-18e604f936d523981','ws_CO_17072026235921043717492048',NULL,NULL,NULL,'{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1037, \"ResultDesc\": \"DS timeout user cannot be reached.\", \"CheckoutRequestID\": \"ws_CO_17072026235921043717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d523981\"}}}, \"ResultCode\": 1037, \"ResultDesc\": \"DS timeout user cannot be reached.\", \"CheckoutRequestID\": \"ws_CO_17072026235921043717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d523981\"}',NULL,NULL,NULL,'1037','DS timeout user cannot be reached.',NULL,NULL,NULL,NULL,'2026-07-17 20:59:22','2026-07-17 20:59:22'),(26,'bcb3-416f-b484-62249d9149d16589','ws_CO_18072026000107880717492048',NULL,NULL,NULL,'{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1037, \"ResultDesc\": \"DS timeout user cannot be reached.\", \"CheckoutRequestID\": \"ws_CO_18072026000107880717492048\", \"MerchantRequestID\": \"bcb3-416f-b484-62249d9149d16589\"}}}, \"ResultCode\": 1037, \"ResultDesc\": \"DS timeout user cannot be reached.\", \"CheckoutRequestID\": \"ws_CO_18072026000107880717492048\", \"MerchantRequestID\": \"bcb3-416f-b484-62249d9149d16589\"}',NULL,NULL,NULL,'1037','DS timeout user cannot be reached.',NULL,NULL,NULL,NULL,'2026-07-17 21:01:09','2026-07-17 21:01:09'),(27,'1294-459c-9072-18e604f936d524168','ws_CO_18072026000120989717492048',NULL,NULL,NULL,'{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGI6OBFEWG\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260718000130}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_18072026000120989717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d524168\"}}}, \"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGI6OBFEWG\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260718000130}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_18072026000120989717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d524168\"}',NULL,NULL,NULL,'0','The service request is processed successfully.',1.00,'UGI6OBFEWG','20260718000130','254717492048','2026-07-17 21:01:31','2026-07-17 21:01:31'),(28,'bcb3-416f-b484-62249d9149d16636','ws_CO_18072026000137596717492048',NULL,NULL,NULL,'{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1037, \"ResultDesc\": \"No response from user.\", \"CheckoutRequestID\": \"ws_CO_18072026000137596717492048\", \"MerchantRequestID\": \"bcb3-416f-b484-62249d9149d16636\"}}}, \"ResultCode\": 1037, \"ResultDesc\": \"No response from user.\", \"CheckoutRequestID\": \"ws_CO_18072026000137596717492048\", \"MerchantRequestID\": \"bcb3-416f-b484-62249d9149d16636\"}',NULL,NULL,NULL,'1037','No response from user.',NULL,NULL,NULL,NULL,'2026-07-17 21:02:03','2026-07-17 21:02:03'),(29,'bcb3-416f-b484-62249d9149d18088','ws_CO_18072026002407875717492048','0','The service request is processed successfully.','The service request is processed successfully.','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGI6OBFGI5\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260718002414}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_18072026002407875717492048\", \"MerchantRequestID\": \"bcb3-416f-b484-62249d9149d18088\"}}}, \"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGI6OBFGI5\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260718002414}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_18072026002407875717492048\", \"MerchantRequestID\": \"bcb3-416f-b484-62249d9149d18088\"}',NULL,NULL,NULL,'0','The service request is processed successfully.',1.00,'UGI6OBFGI5','20260718002414','254717492048','2026-07-17 21:24:15','2026-07-17 21:24:15'),(30,'1294-459c-9072-18e604f936d526947','ws_CO_18072026003315304717492048','1032','Request Cancelled by user.','Request Cancelled by user.','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1032, \"ResultDesc\": \"Request Cancelled by user.\", \"CheckoutRequestID\": \"ws_CO_18072026003315304717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d526947\"}}}, \"ResultCode\": 1032, \"ResultDesc\": \"Request Cancelled by user.\", \"CheckoutRequestID\": \"ws_CO_18072026003315304717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d526947\"}',NULL,NULL,NULL,'1032','Request Cancelled by user.',NULL,NULL,NULL,NULL,'2026-07-17 21:33:21','2026-07-17 21:33:21'),(31,'1294-459c-9072-18e604f936d5108324','ws_CO_18072026194624494717492048','1037','No response from user.','No response from user.','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1037, \"ResultDesc\": \"No response from user.\", \"CheckoutRequestID\": \"ws_CO_18072026194624494717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d5108324\"}}}, \"ResultCode\": 1037, \"ResultDesc\": \"No response from user.\", \"CheckoutRequestID\": \"ws_CO_18072026194624494717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d5108324\"}',NULL,NULL,NULL,'1037','No response from user.',NULL,NULL,NULL,NULL,'2026-07-18 16:46:51','2026-07-18 16:46:51'),(32,'baa9-4152-bfff-5b6c7760b46530098','ws_CO_18072026211441788717492048','1032','Request Cancelled by user.','Request Cancelled by user.','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 1032, \"ResultDesc\": \"Request Cancelled by user.\", \"CheckoutRequestID\": \"ws_CO_18072026211441788717492048\", \"MerchantRequestID\": \"baa9-4152-bfff-5b6c7760b46530098\"}}}, \"ResultCode\": 1032, \"ResultDesc\": \"Request Cancelled by user.\", \"CheckoutRequestID\": \"ws_CO_18072026211441788717492048\", \"MerchantRequestID\": \"baa9-4152-bfff-5b6c7760b46530098\"}',NULL,NULL,NULL,'1032','Request Cancelled by user.',NULL,NULL,NULL,NULL,'2026-07-18 18:14:47','2026-07-18 18:14:47'),(33,'1294-459c-9072-18e604f936d5116210','ws_CO_18072026211518078717492048','0','The service request is processed successfully.','The service request is processed successfully.','{\"raw\": {\"Body\": {\"stkCallback\": {\"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGI6OBIY3L\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260718211526}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_18072026211518078717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d5116210\"}}}, \"ResultCode\": 0, \"ResultDesc\": \"The service request is processed successfully.\", \"CallbackMetadata\": {\"Item\": [{\"Name\": \"Amount\", \"Value\": 1}, {\"Name\": \"MpesaReceiptNumber\", \"Value\": \"UGI6OBIY3L\"}, {\"Name\": \"Balance\"}, {\"Name\": \"TransactionDate\", \"Value\": 20260718211526}, {\"Name\": \"PhoneNumber\", \"Value\": 254717492048}]}, \"CheckoutRequestID\": \"ws_CO_18072026211518078717492048\", \"MerchantRequestID\": \"1294-459c-9072-18e604f936d5116210\"}',NULL,NULL,NULL,'0','The service request is processed successfully.',1.00,'UGI6OBIY3L','20260718211526','254717492048','2026-07-18 18:15:28','2026-07-18 18:15:28');
/*!40000 ALTER TABLE `mpesa_stks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_id` int NOT NULL,
  `order_id` bigint unsigned NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` int NOT NULL,
  `total_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `price` decimal(18,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (17,17,22,'What It Takes',1,1500.00,1500.00,'2025-05-06 08:10:19','2025-05-06 08:10:19'),(22,17,27,'What It Takes',1,1.00,1.00,'2025-05-09 06:30:36','2025-05-09 06:30:36'),(24,17,29,'What It Takes',1,1.00,1.00,'2025-05-12 04:10:00','2025-05-12 04:10:00'),(37,28,42,'run crdb',1,1.00,1.00,'2026-07-22 09:30:28','2026-07-22 09:30:28');
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `total_amount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `transaction_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `initiator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `event_id` int DEFAULT NULL,
  `attendee_id` bigint unsigned DEFAULT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount` decimal(18,2) NOT NULL DEFAULT '0.00',
  `refund` decimal(18,2) NOT NULL DEFAULT '0.00',
  `tax` decimal(8,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_no_unique` (`order_no`),
  UNIQUE KEY `orders_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (22,'ORD-356FE1FE',1,1500.00,'pending',NULL,'1857c3e3-3d73-4512-89b3-ba3af94ef58a','2025-05-06 08:10:19','2025-05-06 08:10:19',10,NULL,'52c6b737-03bb-474c-8496-02fb1435fa73',0.00,0.00,0.00),(27,'ORD-B5D412B0',1,1.00,'pending',NULL,'de93f831-4819-4af4-8950-e54cf698e7cd','2025-05-09 06:30:36','2025-05-09 06:30:36',10,NULL,'f698a1c8-e517-471f-b46b-19dec5a0ccd8',0.00,0.00,0.00),(29,'ORD-988FF996',1,1.00,'pending',NULL,'1d7aaff1-f60e-4529-9b85-de11c508b246','2025-05-12 04:10:00','2025-05-12 04:10:00',10,NULL,'ccf44429-ef71-4a61-b463-eed1016da511',0.00,0.00,0.00),(42,'ORD-SYPNEM6FML',1,1.00,'pending',NULL,'Dennis Kibet','2026-07-22 09:30:28','2026-07-22 09:30:28',12,NULL,'957ebbf4-664f-4b77-a295-5c937af297b4',0.00,0.00,0.00);
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
INSERT INTO `password_reset_tokens` VALUES ('kibettdennis@gmail.com','$2y$12$qYKv2M8b8Nd0O2aGgZzElOWhu6ecgZOQykLJuV4dkMNPNQbdmBEFu','2026-07-21 16:26:13');
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_mode`
--

DROP TABLE IF EXISTS `payment_mode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_mode` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_mode`
--

LOCK TABLES `payment_mode` WRITE;
/*!40000 ALTER TABLE `payment_mode` DISABLE KEYS */;
INSERT INTO `payment_mode` VALUES (1,'Mobile Money','mobile-money.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(2,'Credit Card','credit-card.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(3,'Debit Card','debit-card.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(4,'Bank Transfer','bank-transfer.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(5,'Cash','cash.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(6,'USDT','usdt.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(7,'USDC','usdc.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(8,'SOL','sol.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(9,'Bitcoin','bitcoin.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(10,'Ethereum','ethereum.png','2026-07-07 10:55:35','2026-07-07 10:55:35'),(11,'PayPal','paypal.png','2026-07-07 10:55:35','2026-07-07 10:55:35');
/*!40000 ALTER TABLE `payment_mode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_paid` decimal(18,2) NOT NULL DEFAULT '0.00',
  `balance` decimal(18,2) NOT NULL DEFAULT '0.00',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (2,'ORD-C10D3232','Mpesa',1.00,0.00,'TD283X0EYK','Shafrack Soi','254724606690','shadykipkorir@gmail.com',NULL,'paid','2025-03-17 07:03:40','2025-03-17 07:03:40',NULL,NULL,NULL),(3,'ORD-9413F4FF',NULL,0.00,1.00,NULL,'shady',NULL,'shadykipkorir@gmail.com',NULL,'pending','2025-05-09 06:19:28','2025-05-09 06:19:28','Payment for event What It Takes','801f-4ce5-97b8-f81bf214ea04332552',NULL),(4,'ORD-D3B7499A',NULL,0.00,1.00,NULL,'Shady',NULL,'shadykipkorir@gmail.com',NULL,'pending','2025-05-09 06:22:25','2025-05-09 06:22:25','Payment for event What It Takes','5772-45ff-adb5-823779ddd1bc347963',NULL);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'admin','web','2025-03-13 07:01:25','2025-03-13 07:01:25'),(2,'user','web','2025-03-13 07:01:25','2025-03-13 07:01:25'),(3,'merchant','web','2025-03-13 07:01:25','2025-03-13 07:01:25'),(4,'Super Admin','web','2026-07-21 10:39:52','2026-07-21 10:39:52'),(5,'Customer','web','2026-07-21 10:40:14','2026-07-21 10:40:14');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('tVi2gg8rlHUf4vrlf6lRi3RcMgjaEz42FLE5dDEf',NULL,'127.0.0.1','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36','YTo0OntzOjY6Il90b2tlbiI7czo0MDoicmpjSUNjTVVVelBqS2V3bWhBZWM1dzNSSUZHRTRoYWxaa1VreG43VSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9jYXJ0Ijt9czo0OiJjYXJ0IjthOjE6e2k6MjY7YTo1OntzOjk6InRpY2tldF9pZCI7czoyOiIyNiI7czo4OiJldmVudF9pZCI7aToxMTtzOjQ6Im5hbWUiO3M6MTI6IkRlbm5pcyBLaWJldCI7czo1OiJwcmljZSI7aToxO3M6ODoicXVhbnRpdHkiO2k6MTt9fX0=',1784661452);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'sliders','[\"uploads\\/s2Ke7uyjuFqzlMyb6CH1jpeP5BvdeGMBuKgfyD37.jpg\",\"uploads\\/Pgwkb5Pdfe3nKpNC2QTlEug3GLJxPuuNJTnmdJXp.jpg\",\"uploads\\/WYkq5I51ymMQGqBnRZDbrliLBywYLjrXy6f6l0si.jpg\"]',NULL,'2025-03-15 05:44:14','2025-03-15 23:54:39');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_dark` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slogan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'UTC',
  `date_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'd-m-Y',
  `time_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'H:i:s',
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES',
  `currency_symbol` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KSh',
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `location` longtext COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `maintenance_mode` tinyint(1) NOT NULL DEFAULT '0',
  `pagination_limit` int NOT NULL DEFAULT '15',
  `custom_css` text COLLATE utf8mb4_unicode_ci,
  `custom_js` text COLLATE utf8mb4_unicode_ci,
  `settings` longtext COLLATE utf8mb4_unicode_ci,
  `website_pages` longtext COLLATE utf8mb4_unicode_ci,
  `social_media` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system`
--

LOCK TABLES `system` WRITE;
/*!40000 ALTER TABLE `system` DISABLE KEYS */;
INSERT INTO `system` VALUES (1,'Sharet Africa',NULL,NULL,NULL,NULL,'Smart Property Management','Africa/Nairobi','d-m-Y','H:i:s','KES','KSh','#0d6efd','#6c757d','info@sharet.africa','+254700000000','Nairobi, Kenya',NULL,'Sharet Africa Property Management System','property,rent,tenant,landlord,management',0,15,NULL,NULL,NULL,NULL,NULL,'2026-07-06 21:40:51','2026-07-06 21:40:51');
/*!40000 ALTER TABLE `system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ticket_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merchant_id` bigint unsigned NOT NULL,
  `event_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` enum('KES','USD','USDT','EUROS','POUND') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'KES',
  `price` decimal(8,2) NOT NULL,
  `quantity` int NOT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_ticket_no_unique` (`ticket_no`),
  KEY `tickets_merchant_id_foreign` (`merchant_id`),
  KEY `tickets_event_id_foreign` (`event_id`),
  CONSTRAINT `tickets_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tickets_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (17,'TCKF32A3BE5',1,10,'Early Bird','KES',1.00,100,'2025-05-05 00:00:00','2025-05-24 00:00:00','2025-05-06 08:05:37','2025-05-06 08:05:37'),(18,'TCK309D262C',1,10,'Early Bird (VIP)','KES',3500.00,10,'2025-05-10 14:06:00','2025-05-23 14:07:00','2025-05-06 08:07:16','2025-05-06 08:07:16'),(24,'TKT-V7ZXFIBTML',1,11,'general admission','KES',5000.00,100,'2026-07-31 00:00:00','2026-08-01 00:00:00','2026-07-06 23:13:16','2026-07-06 23:13:16'),(25,'TKT-RKTFGVSPY7',1,11,'vip','KES',550.00,100,'2026-07-31 00:00:00','2026-08-01 00:00:00','2026-07-06 23:13:41','2026-07-06 23:13:41'),(26,'TKT-NQ01W8UR6Q',1,11,'1 BOB!','KES',1.00,1000,'2026-07-31 00:00:00','2026-08-01 00:00:00','2026-07-07 11:38:18','2026-07-07 11:38:18'),(28,'TKT-E1AY1S6LZG',3,12,'Dennis Kibet','KES',1.00,1000,NULL,'2026-10-07 00:00:00','2026-07-21 14:23:00','2026-07-22 13:13:40'),(29,'TKT-MWDCHDIYMH',3,12,'Race Bib','KES',2500.00,100,'2026-07-06 00:00:00','2026-08-13 00:00:00','2026-07-21 17:26:23','2026-07-22 13:31:02'),(30,'TKT-ZZUTKARAOW',3,12,'Race Bib + Trip','KES',12500.00,100,'2026-07-21 00:00:00','2026-08-13 00:00:00','2026-07-21 17:30:21','2026-07-22 13:31:22'),(31,'TKT-LZOVGSZ4BB',3,12,'Race Bib + Stay','KES',12500.00,100,'2026-07-20 00:00:00','2026-08-12 00:00:00','2026-07-22 12:53:49','2026-07-22 13:31:44'),(32,'TKT-E67EOEOWW3',3,12,'Race Bib + Trip + Stay - 3 Nights','KES',23000.00,100,'2026-07-20 00:00:00','2026-08-12 00:00:00','2026-07-22 12:55:53','2026-07-22 13:32:05'),(33,'TKT-OMKAESEPQI',3,12,'Trip','KES',10000.00,10,'2026-07-20 00:00:00','2026-08-11 00:00:00','2026-07-22 12:57:07','2026-07-22 13:32:18'),(34,'TKT-VFYDCBRUWF',3,12,'Stay','KES',10500.00,20,'2026-07-20 00:00:00','2026-08-11 00:00:00','2026-07-22 12:59:15','2026-07-22 13:32:40');
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_merchant` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@aiyotickets.com','2025-03-13 07:02:36','$2y$12$qhzcUWaqAc9NFRYlqW95vevk6rM.0sMIMWbAC4/OFzS7kig0Io69S','AaRNk9nmJMX5txIqphlDqEGAuFbFP056NxkwzC7oMu3ULIzKwzf8PhKyFcAq','2025-03-13 07:02:36','2025-03-13 07:02:36',0),(3,'Kipkorir Soi','kipkorir.soi@gmail.com',NULL,'$2y$12$z.7hZr6m1Auo51dL2NItX.G3fKoiUw3ccFkXz/qfYgAsGV38FMuYy',NULL,'2025-03-14 11:57:48','2025-03-14 11:57:48',1),(4,'Rongo University','rongo.uin@rongouni.ac.ke',NULL,'$2y$12$nQsBsFeU/S6C.KTEsmB1C.nloP30g1umDzwtgIDdfmrvYG/xvziAC',NULL,'2025-03-15 03:34:15','2025-03-15 03:34:15',1),(5,'Dennis Kibet','kibettdennis@gmail.com',NULL,'$2y$12$Nav20kKNzmzTUGJC.k/5d.Y6aRRceTWWaKAOeY.IjaGeSMtiyGLr2',NULL,'2025-06-14 06:29:14','2025-06-14 06:29:14',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-22 20:02:28
