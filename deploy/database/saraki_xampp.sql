-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: saraki_local
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `appointment_services`
--

DROP TABLE IF EXISTS `appointment_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointment_services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name_fr` varchar(120) NOT NULL,
  `name_en` varchar(120) NOT NULL,
  `duration_min` smallint(5) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointment_services_is_active_index` (`is_active`),
  CONSTRAINT `chk_services_duration` CHECK (`duration_min` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_services`
--

LOCK TABLES `appointment_services` WRITE;
/*!40000 ALTER TABLE `appointment_services` DISABLE KEYS */;
INSERT INTO `appointment_services` VALUES (1,'Consultation','Consultation',30,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(2,'Prise de mesures','Measurements',45,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(3,'Essayage','Fitting',45,1,'2026-11-06 10:11:06','2026-11-06 10:11:06');
/*!40000 ALTER TABLE `appointment_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `service_id` bigint(20) unsigned NOT NULL,
  `appointment_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','approved','rescheduled','cancelled','completed') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointments_appointment_date_start_time_index` (`appointment_date`,`start_time`),
  KEY `appointments_appointment_date_status_index` (`appointment_date`,`status`),
  KEY `appointments_user_id_index` (`user_id`),
  KEY `appointments_service_id_index` (`service_id`),
  KEY `appointments_status_index` (`status`),
  CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `appointment_services` (`id`),
  CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_appointments_range` CHECK (`end_time` > `start_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_dates`
--

DROP TABLE IF EXISTS `blocked_dates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blocked_dates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blocked_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blocked_dates_blocked_date_unique` (`blocked_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_dates`
--

LOCK TABLES `blocked_dates` WRITE;
/*!40000 ALTER TABLE `blocked_dates` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_dates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blog_posts`
--

DROP TABLE IF EXISTS `blog_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `blog_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` bigint(20) unsigned NOT NULL,
  `title_fr` varchar(200) NOT NULL,
  `title_en` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `excerpt_fr` varchar(500) DEFAULT NULL,
  `excerpt_en` varchar(500) DEFAULT NULL,
  `body_fr` longtext NOT NULL,
  `body_en` longtext NOT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_posts_slug_unique` (`slug`),
  KEY `blog_posts_status_published_at_index` (`status`,`published_at`),
  KEY `blog_posts_author_id_index` (`author_id`),
  CONSTRAINT `blog_posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blog_posts`
--

LOCK TABLES `blog_posts` WRITE;
/*!40000 ALTER TABLE `blog_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `blog_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cart_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `variant_id` bigint(20) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `unit_price` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_variant_id_unique` (`cart_id`,`variant_id`),
  KEY `cart_items_product_id_index` (`product_id`),
  KEY `cart_items_variant_id_index` (`variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_cart_items_qty` CHECK (`quantity` >= 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart_items`
--

LOCK TABLES `cart_items` WRITE;
/*!40000 ALTER TABLE `cart_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`),
  UNIQUE KEY `carts_session_id_unique` (`session_id`),
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carts`
--

LOCK TABLES `carts` WRITE;
/*!40000 ALTER TABLE `carts` DISABLE KEYS */;
/*!40000 ALTER TABLE `carts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `name_fr` varchar(120) NOT NULL,
  `name_en` varchar(120) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_is_active_sort_order_index` (`is_active`,`sort_order`),
  KEY `categories_parent_id_index` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,NULL,'Boubous','Boubous','boubous',1,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(2,NULL,'Caftans','Caftans','caftans',2,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(3,NULL,'Ensembles Wax','Wax Sets','ensembles-wax',3,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(4,NULL,'Robes de soirée','Evening Dresses','robes-de-soiree',4,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(5,NULL,'Accessoires','Accessories','accessoires',5,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(6,NULL,'Prêt-à-porter','Ready to Wear','pret-a-porter',6,1,'2026-11-06 10:11:05','2026-11-06 10:11:05');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(180) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_messages_status_created_at_index` (`status`,`created_at`),
  KEY `contact_messages_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_measurements`
--

DROP TABLE IF EXISTS `customer_measurements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `customer_measurements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `height_cm` decimal(5,2) DEFAULT NULL,
  `chest_cm` decimal(5,2) DEFAULT NULL,
  `underbust_cm` decimal(5,2) DEFAULT NULL,
  `waist_cm` decimal(5,2) DEFAULT NULL,
  `hips_cm` decimal(5,2) DEFAULT NULL,
  `shoulder_cm` decimal(5,2) DEFAULT NULL,
  `back_width_cm` decimal(5,2) DEFAULT NULL,
  `neck_cm` decimal(5,2) DEFAULT NULL,
  `arm_length_cm` decimal(5,2) DEFAULT NULL,
  `sleeve_length_cm` decimal(5,2) DEFAULT NULL,
  `bicep_cm` decimal(5,2) DEFAULT NULL,
  `inseam_cm` decimal(5,2) DEFAULT NULL,
  `thigh_cm` decimal(5,2) DEFAULT NULL,
  `knee_cm` decimal(5,2) DEFAULT NULL,
  `calf_cm` decimal(5,2) DEFAULT NULL,
  `dress_length_cm` decimal(5,2) DEFAULT NULL,
  `fit_preference` enum('slim','regular','relaxed') DEFAULT NULL,
  `taken_at` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_measurements_user_id_unique` (`user_id`),
  CONSTRAINT `customer_measurements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_measurements`
--

LOCK TABLES `customer_measurements` WRITE;
/*!40000 ALTER TABLE `customer_measurements` DISABLE KEYS */;
INSERT INTO `customer_measurements` VALUES (1,2,165.50,94.00,79.00,76.00,101.00,41.00,37.00,37.00,57.00,59.00,31.00,76.00,58.00,41.00,39.00,118.00,'regular','2026-09-14','Préfère les manches trois-quarts. Taille haute pour les boubous.','2026-11-06 10:11:06','2026-11-06 10:11:06'),(2,3,178.00,104.00,92.00,88.00,103.00,46.00,42.00,41.00,63.00,65.00,36.00,84.00,62.00,44.00,41.00,NULL,'slim','2026-10-02',NULL,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(3,4,159.00,88.00,74.00,71.00,96.00,39.00,36.00,35.00,NULL,NULL,NULL,71.00,55.00,NULL,NULL,104.00,NULL,'2026-08-21','Fiche incomplète : manches à prendre au prochain rendez-vous.','2026-11-06 10:11:07','2026-11-06 10:11:07');
/*!40000 ALTER TABLE `customer_measurements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gallery_items`
--

DROP TABLE IF EXISTS `gallery_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gallery_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title_fr` varchar(180) NOT NULL,
  `title_en` varchar(180) NOT NULL,
  `category` enum('collection','event','completed_design') NOT NULL DEFAULT 'collection',
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gallery_items_category_is_published_sort_order_index` (`category`,`is_published`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gallery_items`
--

LOCK TABLES `gallery_items` WRITE;
/*!40000 ALTER TABLE `gallery_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `gallery_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'2026_11_05_000100_create_catalogue_tables',1),(4,'2026_11_05_000200_create_commerce_tables',1),(5,'2026_11_05_000300_create_payment_tables',1),(6,'2026_11_05_000400_create_appointment_tables',1),(7,'2026_11_05_000500_create_content_tables',1),(8,'2026_11_05_000600_add_account_profile_fields',1),(9,'2026_11_07_000100_add_restocked_at_to_orders_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned DEFAULT NULL,
  `variant_id` bigint(20) unsigned DEFAULT NULL,
  `product_name` varchar(180) NOT NULL,
  `variant_label` varchar(120) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `line_total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  KEY `order_items_variant_id_index` (`variant_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_order_items_values` CHECK (`quantity` >= 1 and `unit_price` >= 0 and `line_total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','confirmed','in_production','ready','delivered','cancelled') NOT NULL,
  `note` text DEFAULT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_history_changed_by_foreign` (`changed_by`),
  KEY `order_status_history_order_id_created_at_index` (`order_id`,`created_at`),
  CONSTRAINT `order_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_status_history_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `order_number` varchar(32) NOT NULL,
  `status` enum('pending','confirmed','in_production','ready','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','pending','paid','failed','refunded','partially_refunded') NOT NULL DEFAULT 'unpaid',
  `payment_method` enum('paystack','cod','transfer') NOT NULL DEFAULT 'paystack',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `currency` char(3) NOT NULL DEFAULT 'GHS',
  `delivery_name` varchar(120) NOT NULL,
  `delivery_phone` varchar(30) NOT NULL,
  `delivery_email` varchar(191) DEFAULT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `delivery_city` varchar(120) NOT NULL,
  `notes` text DEFAULT NULL,
  `placed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `restocked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_payment_method_index` (`payment_method`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `chk_orders_amounts` CHECK (`subtotal` >= 0 and `shipping_fee` >= 0 and `total` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) NOT NULL,
  `title_fr` varchar(180) NOT NULL,
  `title_en` varchar(180) NOT NULL,
  `body_fr` longtext NOT NULL,
  `body_en` longtext NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
INSERT INTO `pages` VALUES (1,'about','La maison SARAKI','The SARAKI house','SARAKI est un atelier de mode africaine contemporaine installé à Accra. Chaque pièce commence par une fiche de mesures et se termine par une couture finie à la main.\n\nNous travaillons le boubou, le caftan, l\'ensemble wax et la tenue de soirée dans des tissus choisis un à un chez nos fournisseurs de Makola et de Kumasi. Les coupes restent sobres : le tissu et la main du tailleur font le travail.\n\nL\'atelier reçoit sur rendez-vous, du lundi au samedi. Réservez un créneau pour une consultation, une prise de mesures ou un essayage.','SARAKI is a contemporary African fashion atelier based in Accra. Every piece starts as a measurement sheet and ends as a hand-finished seam.\n\nWe make boubous, caftans, wax sets and evening wear in fabrics chosen one by one from our suppliers in Makola and Kumasi. The cuts stay quiet: the cloth and the tailor\'s hand do the work.\n\nThe atelier receives visitors by appointment, Monday to Saturday. Book a slot for a consultation, a fitting or a measurement session.',1,'2026-11-08 17:32:05','2026-11-08 17:32:05'),(2,'shipping','Livraison','Shipping','Nous livrons partout au Ghana. Les frais dépendent de la zone choisie au moment de la commande et s\'affichent avant le paiement.\n\nGrand Accra : 2 jours ouvrés. Tema : 3 jours. Kumasi : 4 jours. Autres villes : 6 jours.\n\nLes pièces sur mesure demandent un délai de confection supplémentaire, confirmé par l\'atelier après la commande. Vous recevez un message à chaque étape, de la confirmation à la remise au livreur.\n\nLa livraison est offerte à partir de 1 500 GH₵ d\'achat dans le Grand Accra.','We deliver across Ghana. Fees depend on the zone chosen at checkout and are shown before payment.\n\nGreater Accra: 2 working days. Tema: 3 days. Kumasi: 4 days. Other cities: 6 days.\n\nMade-to-measure pieces need additional production time, which the atelier confirms after your order. You get a message at every step, from confirmation to handover to the courier.\n\nDelivery is free above GH₵1,500 within Greater Accra.',1,'2026-11-08 17:32:05','2026-11-08 17:32:05'),(3,'returns','Retours et retouches','Returns and alterations','Une pièce sur mesure ne se revend pas : nous préférons la retoucher. Les retouches de coupe et d\'ourlet sont offertes dans les trente jours suivant la livraison.\n\nPour une pièce de la collection en taille standard, vous pouvez la retourner non portée et non lavée dans les sept jours, avec son étiquette. Les frais de retour restent à votre charge.\n\nSignalez tout défaut de fabrication à contact@saraki.test avec une photo : nous prenons en charge le retour et la réparation.','A made-to-measure piece cannot be resold, so we would rather alter it. Cut and hem alterations are free within thirty days of delivery.\n\nA ready-sized piece from the collection can be returned unworn and unwashed within seven days, with its label. Return shipping is at your cost.\n\nReport any manufacturing fault to contact@saraki.test with a photo: we cover the return and the repair.',1,'2026-11-08 17:32:05','2026-11-08 17:32:05'),(4,'privacy','Confidentialité','Privacy','Nous gardons uniquement ce qui sert à vous livrer : votre nom, vos coordonnées, votre adresse de livraison et votre fiche de mesures.\n\nLes paiements sont traités par Paystack. Aucune donnée de carte n\'est stockée sur nos serveurs.\n\nVos mesures ne sont visibles que par l\'atelier. Vous pouvez demander la suppression de votre compte et de vos données en écrivant à contact@saraki.test : nous répondons sous trente jours.','We keep only what is needed to deliver to you: your name, contact details, delivery address and measurement sheet.\n\nPayments are processed by Paystack. No card data is stored on our servers.\n\nYour measurements are visible to the atelier only. You can ask for your account and data to be deleted by writing to contact@saraki.test: we reply within thirty days.',1,'2026-11-08 17:32:05','2026-11-08 17:32:05'),(5,'terms','Conditions de vente','Terms of sale','Prix affichés en cedis ghanéens (GH₵), taxes comprises. Le montant total, livraison incluse, apparaît avant la validation de la commande.\n\nUne commande non réglée est annulée après le délai indiqué au paiement et les pièces sont remises en vente.\n\nLes pièces sur mesure sont fabriquées selon les mesures que vous fournissez. Un écart entre la fiche et les mesures réelles peut demander une retouche, que nous réalisons dans les conditions décrites sur la page Retours.\n\nLe droit applicable est celui du Ghana.','Prices are shown in Ghanaian cedis (GH₵), taxes included. The total, delivery included, appears before you confirm the order.\n\nAn unpaid order is cancelled after the window shown at payment, and the pieces go back on sale.\n\nMade-to-measure pieces are cut to the measurements you provide. A gap between the sheet and your actual measurements may need an alteration, which we make under the conditions described on the Returns page.\n\nGhanaian law applies.',1,'2026-11-08 17:32:05','2026-11-08 17:32:05');
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(191) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_events`
--

DROP TABLE IF EXISTS `payment_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payment_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned DEFAULT NULL,
  `provider` varchar(30) NOT NULL DEFAULT 'paystack',
  `event_id` varchar(100) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `signature_valid` tinyint(1) NOT NULL DEFAULT 0,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_events_event_id_unique` (`event_id`),
  KEY `payment_events_payment_id_index` (`payment_id`),
  KEY `payment_events_reference_index` (`reference`),
  KEY `payment_events_processed_at_index` (`processed_at`),
  CONSTRAINT `payment_events_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_events`
--

LOCK TABLES `payment_events` WRITE;
/*!40000 ALTER TABLE `payment_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `provider` enum('paystack','offline') NOT NULL DEFAULT 'paystack',
  `reference` varchar(100) NOT NULL,
  `provider_reference` varchar(100) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'GHS',
  `channel` enum('card','mobile_money','bank_transfer','ussd','cash','transfer') DEFAULT NULL,
  `status` enum('initialized','pending','success','failed','abandoned','refunded','partially_refunded') NOT NULL DEFAULT 'initialized',
  `paid_at` timestamp NULL DEFAULT NULL,
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gateway_response`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_reference_unique` (`reference`),
  KEY `payments_order_id_index` (`order_id`),
  KEY `payments_provider_reference_index` (`provider_reference`),
  KEY `payments_status_index` (`status`),
  CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `chk_payments_amount` CHECK (`amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `alt_fr` varchar(255) DEFAULT NULL,
  `alt_en` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_sort_order_index` (`product_id`,`sort_order`),
  KEY `product_images_product_id_is_primary_index` (`product_id`,`is_primary`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_images`
--

LOCK TABLES `product_images` WRITE;
/*!40000 ALTER TABLE `product_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product_variants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint(20) unsigned NOT NULL,
  `size` varchar(40) NOT NULL DEFAULT 'Default',
  `color` varchar(60) NOT NULL DEFAULT 'Default',
  `sku` varchar(64) NOT NULL,
  `stock_qty` int(10) unsigned NOT NULL DEFAULT 0,
  `price_delta` decimal(12,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_product_id_size_color_unique` (`product_id`,`size`,`color`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_is_active_index` (`product_id`,`is_active`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_variants_stock` CHECK (`stock_qty` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=140 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
INSERT INTO `product_variants` VALUES (1,1,'S','Or','SRK-BOU-001-S-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(2,1,'S','Ivoire','SRK-BOU-001-S-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(3,1,'M','Or','SRK-BOU-001-M-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(4,1,'M','Ivoire','SRK-BOU-001-M-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(5,1,'L','Or','SRK-BOU-001-L-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(6,1,'L','Ivoire','SRK-BOU-001-L-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(7,1,'XL','Or','SRK-BOU-001-XL-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(8,1,'XL','Ivoire','SRK-BOU-001-XL-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(9,2,'S','Terracotta','SRK-BOU-002-S-TERRACOTTA',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(10,2,'S','Sable','SRK-BOU-002-S-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(11,2,'M','Terracotta','SRK-BOU-002-M-TERRACOTTA',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(12,2,'M','Sable','SRK-BOU-002-M-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(13,2,'L','Terracotta','SRK-BOU-002-L-TERRACOTTA',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(14,2,'L','Sable','SRK-BOU-002-L-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(15,2,'XL','Terracotta','SRK-BOU-002-XL-TERRACOTTA',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(16,2,'XL','Sable','SRK-BOU-002-XL-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(17,3,'S','Noir','SRK-BOU-003-S-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(18,3,'S','Bleu nuit','SRK-BOU-003-S-BLEU-NUIT',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(19,3,'M','Noir','SRK-BOU-003-M-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(20,3,'M','Bleu nuit','SRK-BOU-003-M-BLEU-NUIT',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(21,3,'L','Noir','SRK-BOU-003-L-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(22,3,'L','Bleu nuit','SRK-BOU-003-L-BLEU-NUIT',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(23,3,'XL','Noir','SRK-BOU-003-XL-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(24,3,'XL','Bleu nuit','SRK-BOU-003-XL-BLEU-NUIT',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(25,4,'S','Bordeaux','SRK-BOU-004-S-BORDEAUX',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(26,4,'S','Or','SRK-BOU-004-S-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(27,4,'M','Bordeaux','SRK-BOU-004-M-BORDEAUX',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(28,4,'M','Or','SRK-BOU-004-M-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(29,4,'L','Bordeaux','SRK-BOU-004-L-BORDEAUX',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(30,4,'L','Or','SRK-BOU-004-L-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(31,4,'XL','Bordeaux','SRK-BOU-004-XL-BORDEAUX',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(32,4,'XL','Or','SRK-BOU-004-XL-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(33,5,'S','Émeraude','SRK-CAF-005-S-EMERAUDE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(34,5,'S','Ivoire','SRK-CAF-005-S-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(35,5,'M','Émeraude','SRK-CAF-005-M-EMERAUDE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(36,5,'M','Ivoire','SRK-CAF-005-M-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(37,5,'L','Émeraude','SRK-CAF-005-L-EMERAUDE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(38,5,'L','Ivoire','SRK-CAF-005-L-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(39,5,'XL','Émeraude','SRK-CAF-005-XL-EMERAUDE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(40,5,'XL','Ivoire','SRK-CAF-005-XL-IVOIRE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(41,6,'S','Sable','SRK-CAF-006-S-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(42,6,'S','Or','SRK-CAF-006-S-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(43,6,'M','Sable','SRK-CAF-006-M-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(44,6,'M','Or','SRK-CAF-006-M-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(45,6,'L','Sable','SRK-CAF-006-L-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(46,6,'L','Or','SRK-CAF-006-L-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(47,6,'XL','Sable','SRK-CAF-006-XL-SABLE',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(48,6,'XL','Or','SRK-CAF-006-XL-OR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(49,7,'S','Blanc','SRK-CAF-007-S-BLANC',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(50,7,'S','Noir','SRK-CAF-007-S-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(51,7,'M','Blanc','SRK-CAF-007-M-BLANC',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(52,7,'M','Noir','SRK-CAF-007-M-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(53,7,'L','Blanc','SRK-CAF-007-L-BLANC',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(54,7,'L','Noir','SRK-CAF-007-L-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(55,7,'XL','Blanc','SRK-CAF-007-XL-BLANC',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(56,7,'XL','Noir','SRK-CAF-007-XL-NOIR',12,0.00,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(57,8,'S','Orange','SRK-WAX-008-S-ORANGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(58,8,'S','Turquoise','SRK-WAX-008-S-TURQUOISE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(59,8,'M','Orange','SRK-WAX-008-M-ORANGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(60,8,'M','Turquoise','SRK-WAX-008-M-TURQUOISE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(61,8,'L','Orange','SRK-WAX-008-L-ORANGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(62,8,'L','Turquoise','SRK-WAX-008-L-TURQUOISE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(63,8,'XL','Orange','SRK-WAX-008-XL-ORANGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(64,8,'XL','Turquoise','SRK-WAX-008-XL-TURQUOISE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(65,9,'S','Rose','SRK-WAX-009-S-ROSE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(66,9,'S','Vert','SRK-WAX-009-S-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(67,9,'M','Rose','SRK-WAX-009-M-ROSE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(68,9,'M','Vert','SRK-WAX-009-M-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(69,9,'L','Rose','SRK-WAX-009-L-ROSE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(70,9,'L','Vert','SRK-WAX-009-L-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(71,9,'XL','Rose','SRK-WAX-009-XL-ROSE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(72,9,'XL','Vert','SRK-WAX-009-XL-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(73,10,'S','Indigo','SRK-WAX-010-S-INDIGO',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(74,10,'S','Sable','SRK-WAX-010-S-SABLE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(75,10,'M','Indigo','SRK-WAX-010-M-INDIGO',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(76,10,'M','Sable','SRK-WAX-010-M-SABLE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(77,10,'L','Indigo','SRK-WAX-010-L-INDIGO',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(78,10,'L','Sable','SRK-WAX-010-L-SABLE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(79,10,'XL','Indigo','SRK-WAX-010-XL-INDIGO',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(80,10,'XL','Sable','SRK-WAX-010-XL-SABLE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(81,11,'S','Or','SRK-WAX-011-S-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(82,11,'S','Noir','SRK-WAX-011-S-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(83,11,'M','Or','SRK-WAX-011-M-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(84,11,'M','Noir','SRK-WAX-011-M-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(85,11,'L','Or','SRK-WAX-011-L-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(86,11,'L','Noir','SRK-WAX-011-L-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(87,11,'XL','Or','SRK-WAX-011-XL-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(88,11,'XL','Noir','SRK-WAX-011-XL-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(89,12,'S','Noir','SRK-SOI-012-S-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(90,12,'S','Or','SRK-SOI-012-S-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(91,12,'M','Noir','SRK-SOI-012-M-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(92,12,'M','Or','SRK-SOI-012-M-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(93,12,'L','Noir','SRK-SOI-012-L-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(94,12,'L','Or','SRK-SOI-012-L-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(95,12,'XL','Noir','SRK-SOI-012-XL-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(96,12,'XL','Or','SRK-SOI-012-XL-OR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(97,13,'S','Bordeaux','SRK-SOI-013-S-BORDEAUX',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(98,13,'S','Vert','SRK-SOI-013-S-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(99,13,'M','Bordeaux','SRK-SOI-013-M-BORDEAUX',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(100,13,'M','Vert','SRK-SOI-013-M-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(101,13,'L','Bordeaux','SRK-SOI-013-L-BORDEAUX',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(102,13,'L','Vert','SRK-SOI-013-L-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(103,13,'XL','Bordeaux','SRK-SOI-013-XL-BORDEAUX',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(104,13,'XL','Vert','SRK-SOI-013-XL-VERT',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(105,14,'S','Ivoire','SRK-SOI-014-S-IVOIRE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(106,14,'S','Bleu ciel','SRK-SOI-014-S-BLEU-CIEL',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(107,14,'M','Ivoire','SRK-SOI-014-M-IVOIRE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(108,14,'M','Bleu ciel','SRK-SOI-014-M-BLEU-CIEL',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(109,14,'L','Ivoire','SRK-SOI-014-L-IVOIRE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(110,14,'L','Bleu ciel','SRK-SOI-014-L-BLEU-CIEL',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(111,14,'XL','Ivoire','SRK-SOI-014-XL-IVOIRE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(112,14,'XL','Bleu ciel','SRK-SOI-014-XL-BLEU-CIEL',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(113,15,'Default','Default','SRK-ACC-015-DEF',15,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(114,16,'Default','Default','SRK-ACC-016-DEF',15,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(115,17,'Default','Default','SRK-ACC-017-DEF',15,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(116,18,'S','Rouge','SRK-PRE-018-S-ROUGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(117,18,'S','Noir','SRK-PRE-018-S-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(118,18,'M','Rouge','SRK-PRE-018-M-ROUGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(119,18,'M','Noir','SRK-PRE-018-M-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(120,18,'L','Rouge','SRK-PRE-018-L-ROUGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(121,18,'L','Noir','SRK-PRE-018-L-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(122,18,'XL','Rouge','SRK-PRE-018-XL-ROUGE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(123,18,'XL','Noir','SRK-PRE-018-XL-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(124,19,'S','Blanc','SRK-PRE-019-S-BLANC',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(125,19,'S','Bleu','SRK-PRE-019-S-BLEU',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(126,19,'M','Blanc','SRK-PRE-019-M-BLANC',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(127,19,'M','Bleu','SRK-PRE-019-M-BLEU',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(128,19,'L','Blanc','SRK-PRE-019-L-BLANC',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(129,19,'L','Bleu','SRK-PRE-019-L-BLEU',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(130,19,'XL','Blanc','SRK-PRE-019-XL-BLANC',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(131,19,'XL','Bleu','SRK-PRE-019-XL-BLEU',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(132,20,'S','Multicolore','SRK-PRE-020-S-MULTICOLORE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(133,20,'S','Noir','SRK-PRE-020-S-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(134,20,'M','Multicolore','SRK-PRE-020-M-MULTICOLORE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(135,20,'M','Noir','SRK-PRE-020-M-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(136,20,'L','Multicolore','SRK-PRE-020-L-MULTICOLORE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(137,20,'L','Noir','SRK-PRE-020-L-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(138,20,'XL','Multicolore','SRK-PRE-020-XL-MULTICOLORE',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(139,20,'XL','Noir','SRK-PRE-020-XL-NOIR',12,0.00,1,'2026-11-06 10:11:06','2026-11-06 10:11:06');
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint(20) unsigned NOT NULL,
  `name_fr` varchar(180) NOT NULL,
  `name_en` varchar(180) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `base_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `base_sku` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_base_sku_unique` (`base_sku`),
  KEY `products_is_active_is_featured_index` (`is_active`,`is_featured`),
  KEY `products_category_id_index` (`category_id`),
  FULLTEXT KEY `products_name_fr_name_en_description_fr_description_en_fulltext` (`name_fr`,`name_en`,`description_fr`,`description_en`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `chk_products_base_price` CHECK (`base_price` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Boubou Royale','Royale Boubou','boubou-royale','Boubou Royale — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Royale Boubou — hand-made in our atelier, premium fabric and careful finishing.',1450.00,'SRK-BOU-001',1,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(2,1,'Boubou Sahara','Sahara Boubou','boubou-sahara','Boubou Sahara — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Sahara Boubou — hand-made in our atelier, premium fabric and careful finishing.',1200.00,'SRK-BOU-002',1,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(3,1,'Boubou Nuit d’Encre','Ink Night Boubou','boubou-nuit-dencre','Boubou Nuit d’Encre — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Ink Night Boubou — hand-made in our atelier, premium fabric and careful finishing.',1650.00,'SRK-BOU-003',1,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(4,1,'Boubou Héritage','Heritage Boubou','boubou-heritage','Boubou Héritage — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Heritage Boubou — hand-made in our atelier, premium fabric and careful finishing.',1950.00,'SRK-BOU-004',1,1,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(5,2,'Caftan Djoliba','Djoliba Caftan','caftan-djoliba','Caftan Djoliba — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Djoliba Caftan — hand-made in our atelier, premium fabric and careful finishing.',980.00,'SRK-CAF-005',1,0,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(6,2,'Caftan Sable d’Or','Golden Sand Caftan','caftan-sable-dor','Caftan Sable d’Or — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Golden Sand Caftan — hand-made in our atelier, premium fabric and careful finishing.',1120.00,'SRK-CAF-006',1,0,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(7,2,'Caftan Médina','Medina Caftan','caftan-medina','Caftan Médina — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Medina Caftan — hand-made in our atelier, premium fabric and careful finishing.',1250.00,'SRK-CAF-007',1,0,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(8,3,'Ensemble Sunrise','Sunrise Set','ensemble-sunrise','Ensemble Sunrise — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Sunrise Set — hand-made in our atelier, premium fabric and careful finishing.',620.00,'SRK-WAX-008',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(9,3,'Ensemble Cotonou Bloom','Cotonou Bloom Set','ensemble-cotonou-bloom','Ensemble Cotonou Bloom — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Cotonou Bloom Set — hand-made in our atelier, premium fabric and careful finishing.',540.00,'SRK-WAX-009',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(10,3,'Ensemble Sahel Wave','Sahel Wave Set','ensemble-sahel-wave','Ensemble Sahel Wave — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Sahel Wave Set — hand-made in our atelier, premium fabric and careful finishing.',680.00,'SRK-WAX-010',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(11,3,'Ensemble Kente Dream','Kente Dream Set','ensemble-kente-dream','Ensemble Kente Dream — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Kente Dream Set — hand-made in our atelier, premium fabric and careful finishing.',760.00,'SRK-WAX-011',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(12,4,'Robe Étoile de Dakar','Dakar Star Gown','robe-etoile-de-dakar','Robe Étoile de Dakar — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Dakar Star Gown — hand-made in our atelier, premium fabric and careful finishing.',1800.00,'SRK-SOI-012',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(13,4,'Robe Velvet Nights','Velvet Nights Gown','robe-velvet-nights','Robe Velvet Nights — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Velvet Nights Gown — hand-made in our atelier, premium fabric and careful finishing.',2100.00,'SRK-SOI-013',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(14,4,'Robe Perle de l’Océan','Ocean Pearl Gown','robe-perle-de-locean','Robe Perle de l’Océan — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Ocean Pearl Gown — hand-made in our atelier, premium fabric and careful finishing.',1950.00,'SRK-SOI-014',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(15,5,'Foulard Impérial','Imperial Scarf','foulard-imperial','Foulard Impérial — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Imperial Scarf — hand-made in our atelier, premium fabric and careful finishing.',180.00,'SRK-ACC-015',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(16,5,'Bijoux de Mariée','Bridal Jewellery','bijoux-de-mariee','Bijoux de Mariée — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Bridal Jewellery — hand-made in our atelier, premium fabric and careful finishing.',320.00,'SRK-ACC-016',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(17,5,'Sac Raffia','Raffia Bag','sac-raffia','Sac Raffia — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Raffia Bag — hand-made in our atelier, premium fabric and careful finishing.',260.00,'SRK-ACC-017',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(18,6,'Ensemble Tam-Tam','Tam-Tam Set','ensemble-tam-tam','Ensemble Tam-Tam — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Tam-Tam Set — hand-made in our atelier, premium fabric and careful finishing.',480.00,'SRK-PRE-018',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(19,6,'Chemise Sahel','Sahel Shirt','chemise-sahel','Chemise Sahel — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Sahel Shirt — hand-made in our atelier, premium fabric and careful finishing.',350.00,'SRK-PRE-019',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(20,6,'Jupe Wax Classique','Classic Wax Skirt','jupe-wax-classique','Jupe Wax Classique — pièce confectionée à la main dans nos ateliers, tissu premium et finitions soignées.','Classic Wax Skirt — hand-made in our atelier, premium fabric and careful finishing.',290.00,'SRK-PRE-020',1,0,'2026-11-06 10:11:06','2026-11-06 10:11:06');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refunds`
--

DROP TABLE IF EXISTS `refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `refunds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint(20) unsigned NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `provider_reference` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failed') NOT NULL DEFAULT 'pending',
  `processed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `refunds_processed_by_foreign` (`processed_by`),
  KEY `refunds_created_at_index` (`created_at`),
  KEY `refunds_payment_id_index` (`payment_id`),
  KEY `refunds_status_index` (`status`),
  CONSTRAINT `refunds_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),
  CONSTRAINT `refunds_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_refunds_amount` CHECK (`amount` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refunds`
--

LOCK TABLES `refunds` WRITE;
/*!40000 ALTER TABLE `refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`),
  CONSTRAINT `sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `group` varchar(50) NOT NULL DEFAULT 'general',
  `key` varchar(100) NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`value`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'general','store_name','\"SARAKI\"','2026-11-06 10:11:05','2026-11-06 10:11:05'),(2,'general','currency','\"GHS\"','2026-11-06 10:11:05','2026-11-06 10:11:05'),(3,'general','default_locale','\"fr\"','2026-11-06 10:11:05','2026-11-06 10:11:05'),(4,'general','whatsapp_number',NULL,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(5,'general','map_embed',NULL,'2026-11-06 10:11:05','2026-11-06 10:11:05'),(6,'commerce','shipping_zones','[{\"name\":\"Grand Accra\",\"name_en\":\"Greater Accra\",\"fee\":30,\"delivery_days\":2},{\"name\":\"Tema\",\"name_en\":\"Tema\",\"fee\":45,\"delivery_days\":3},{\"name\":\"Kumasi\",\"name_en\":\"Kumasi\",\"fee\":60,\"delivery_days\":4},{\"name\":\"Autre ville\",\"name_en\":\"Other city\",\"fee\":80,\"delivery_days\":6}]','2026-11-06 10:11:05','2026-11-06 10:11:05'),(7,'commerce','payment_methods','{\"paystack\":true,\"cod\":false,\"transfer\":false}','2026-11-06 10:11:05','2026-11-06 10:11:05'),(8,'commerce','tax_display','false','2026-11-06 10:11:05','2026-11-06 10:11:05'),(9,'commerce','order_expiry_minutes','30','2026-11-06 10:11:05','2026-11-06 10:11:05'),(10,'social','social_links','{\"instagram\":null,\"facebook\":null,\"tiktok\":null}','2026-11-06 10:11:05','2026-11-06 10:11:05'),(11,'general','contact_email','\"admin@saraki.test\"','2026-11-08 17:32:05','2026-11-08 17:32:05'),(12,'general','contact_phone',NULL,'2026-11-08 17:32:05','2026-11-08 17:32:05'),(13,'general','store_address',NULL,'2026-11-08 17:32:05','2026-11-08 17:32:05');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `locale` char(2) NOT NULL DEFAULT 'fr',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_index` (`role`),
  KEY `users_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'SARAKI Admin','admin@saraki.test',NULL,NULL,'2026-11-06 10:11:05','$2y$12$sWB20YuESQ3u84rGiad4SutNI8fGux9p8hhcOZ89O5XrJH4fberKG','admin','fr',1,NULL,'2026-11-06 10:11:05','2026-11-08 17:32:05'),(2,'Ama Serwaa','ama.serwaa@example.test','+233 24 555 0112',NULL,'2026-11-06 10:11:06','$2y$12$4sjcXaBk6L/oy8kHSVdj.uMYnLNTYTbnzoLusNXltOjrhaQp2AIXy','customer','fr',1,NULL,'2026-11-06 10:11:06','2026-11-08 17:32:06'),(3,'Kwame Boateng','kwame.boateng@example.test','+233 20 555 0174',NULL,'2026-11-06 10:11:06','$2y$12$kMgFFpnpe2M62KolTsQplurnP8T1vYqa.HYlJ2Z9oEDxTeG8P6B6m','customer','fr',1,NULL,'2026-11-06 10:11:06','2026-11-08 17:32:06'),(4,'Fatima Iddrisu','fatima.iddrisu@example.test','+233 27 555 0139',NULL,'2026-11-06 10:11:07','$2y$12$OVNjyYZdkYeBwh1gmWs0P.9tH8FWgk0qfjRVD4ovLyw/3tRpwWPNK','customer','fr',1,NULL,'2026-11-06 10:11:07','2026-11-08 17:32:06'),(5,'Yaw Adjei','yaw.adjei@example.test','+233 54 555 0186',NULL,'2026-11-06 10:11:07','$2y$12$2Fjd8t3.Q1pVLbwNOaPe5uy61zcW4MuAe.xof8jqqyfe7LnCuGv6a','customer','fr',1,NULL,'2026-11-06 10:11:07','2026-11-08 17:32:06');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  KEY `wishlists_product_id_index` (`product_id`),
  CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `working_hours`
--

DROP TABLE IF EXISTS `working_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `working_hours` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `day_of_week` tinyint(3) unsigned NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `working_hours_day_of_week_unique` (`day_of_week`),
  CONSTRAINT `chk_working_hours_day` CHECK (`day_of_week` between 0 and 6),
  CONSTRAINT `chk_working_hours_range` CHECK (`is_closed` = 1 or `open_time` is not null and `close_time` is not null and `close_time` > `open_time`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `working_hours`
--

LOCK TABLES `working_hours` WRITE;
/*!40000 ALTER TABLE `working_hours` DISABLE KEYS */;
INSERT INTO `working_hours` VALUES (1,0,NULL,NULL,1,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(2,1,'09:00:00','18:00:00',0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(3,2,'09:00:00','18:00:00',0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(4,3,'09:00:00','18:00:00',0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(5,4,'09:00:00','18:00:00',0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(6,5,'09:00:00','18:00:00',0,'2026-11-06 10:11:06','2026-11-06 10:11:06'),(7,6,'09:00:00','18:00:00',0,'2026-11-06 10:11:06','2026-11-06 10:11:06');
/*!40000 ALTER TABLE `working_hours` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-08 17:32:24
