-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 29, 2024 at 01:56 PM
-- Server version: 8.2.0
-- PHP Version: 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `velzon_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` int DEFAULT '0',
  `photo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `shop_name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Cairo',
  `access_cities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `phone`, `role_id`, `photo`, `password`, `status`, `remember_token`, `created_at`, `updated_at`, `shop_name`, `city`, `access_cities`) VALUES
(1, 'admin', 'admin@demo.com', '01004282491', 0, '1698361242about.jpg', '$2y$10$Vk2VYyIrgclkeQd3Sb.96.RN6/PYJiEGcnaRP3fPiwHVLKs29.udS', 1, 'zFMRp56WqQ1NeB74AH4qcmZYdHSb30VjtkiMZGezTCp15faocrlK5BYcApeK', '2018-02-28 23:27:08', '2023-10-26 21:00:42', 'admin', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `counters`
--

DROP TABLE IF EXISTS `counters`;
CREATE TABLE IF NOT EXISTS `counters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('referral','browser') NOT NULL DEFAULT 'referral',
  `referral` varchar(255) DEFAULT NULL,
  `total_count` int NOT NULL DEFAULT '0',
  `todays_count` int NOT NULL DEFAULT '0',
  `today` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=latin1;

--
-- Dumping data for table `counters`
--

INSERT INTO `counters` (`id`, `type`, `referral`, `total_count`, `todays_count`, `today`) VALUES
(1, 'referral', 'www.facebook.com', 6, 0, NULL),
(2, 'referral', 'geniusocean.com', 2, 0, NULL),
(3, 'browser', 'Windows 10', 30472, 0, NULL),
(4, 'browser', 'Linux', 727, 0, NULL),
(5, 'browser', 'Unknown OS Platform', 15522, 0, NULL),
(6, 'browser', 'Windows 7', 1817, 0, NULL),
(7, 'referral', 'yandex.ru', 17, 0, NULL),
(8, 'browser', 'Windows 8.1', 2297, 0, NULL),
(9, 'referral', 'www.google.com', 1667, 0, NULL),
(10, 'browser', 'Android', 8413, 0, NULL),
(11, 'browser', 'Mac OS X', 1390, 0, NULL),
(12, 'referral', 'l.facebook.com', 54, 0, NULL),
(13, 'referral', 'codecanyon.net', 6, 0, NULL),
(14, 'browser', 'Windows XP', 46, 0, NULL),
(15, 'browser', 'Windows 8', 13, 0, NULL),
(16, 'browser', 'iPad', 24, 0, NULL),
(17, 'browser', 'Ubuntu', 178, 0, NULL),
(18, 'browser', 'iPhone', 383, 0, NULL),
(19, 'referral', 'com.google.android.googlequicksearchbox', 15, 0, NULL),
(20, 'referral', 'm.facebook.com', 19, 0, NULL),
(21, 'referral', 'www.mm4web.net', 1, 0, NULL),
(22, 'referral', 'com.google.android.gm', 4, 0, NULL),
(23, 'referral', 'ALAAELDDIN.COM', 2, 0, NULL),
(24, 'referral', 'www.bing.com', 7, 0, NULL),
(25, 'browser', 'Windows 2000', 3, 0, NULL),
(26, 'referral', 'builtwith.com', 2, 0, NULL),
(27, 'referral', 'sys.busineks.com', 2, 0, NULL),
(28, 'referral', 'baidu.com', 34, 0, NULL),
(29, 'referral', '51.89.203.191', 7, 0, NULL),
(30, 'referral', NULL, 228, 0, NULL),
(31, 'browser', 'Windows Vista', 7, 0, NULL),
(32, 'browser', 'iPod', 1, 0, NULL),
(33, 'referral', 'multimega-eg.com', 14, 0, NULL),
(34, 'referral', 'babyisland.vooecommerce.com', 2, 0, NULL),
(35, 'referral', 'rushstoreeg.com', 2, 0, NULL),
(36, 'referral', 'zapolaa.com', 1, 0, NULL),
(37, 'referral', 'www.zapolaa.com', 3, 0, NULL),
(38, 'referral', 'clfurniture.shop', 3, 0, NULL),
(39, 'referral', 'vooecommerce.com', 2, 0, NULL),
(40, 'referral', 'business.facebook.com', 5, 0, NULL),
(41, 'referral', 'banquemisr.gateway.mastercard.com', 2, 0, NULL),
(42, 'referral', 'test-nbe.gateway.mastercard.com', 5, 0, NULL),
(43, 'referral', 'www.google.com.eg', 37, 0, NULL),
(44, 'referral', 'osmangroup.co', 1, 0, NULL),
(45, 'referral', 'martaba.net', 1, 0, NULL),
(46, 'referral', 'www.organo-market.com', 1, 0, NULL),
(47, 'referral', 'uatcheckout.thawani.om', 1, 0, NULL),
(48, 'referral', 'l.instagram.com', 20, 0, NULL),
(49, 'referral', 'lm.facebook.com', 3, 0, NULL),
(50, 'referral', 't.co', 1, 0, NULL),
(51, 'referral', 'vmi642664.contaboserver.net', 2, 0, NULL),
(52, 'referral', 'fbkwriter.com', 1, 0, NULL),
(53, 'referral', 'gxjs.qxnr.net', 2, 0, NULL),
(54, 'referral', 'nhedws.qxnr.net', 2, 0, NULL),
(55, 'referral', 'magmuhendislik.com', 2, 0, NULL),
(56, 'referral', 'www.5rkj.com', 2, 0, NULL),
(57, 'referral', '194.163.159.150', 8, 0, NULL),
(58, 'referral', 'com.linkedin.android', 5, 0, NULL),
(59, 'referral', 'bit.ly', 36, 0, NULL),
(60, 'referral', 'www.talentwm.com', 2, 0, NULL),
(61, 'referral', 'webtechsurvey.com', 3, 0, NULL),
(62, 'referral', 'greenpharmacy.me', 11, 0, NULL),
(63, 'referral', 'watertank-eg.com', 4, 0, NULL),
(64, 'referral', 'beautiful.cangrow.online', 1, 0, NULL),
(65, 'referral', 'www.google.com.hk', 2, 0, NULL),
(66, 'browser', 'BlackBerry', 1, 0, NULL),
(67, 'referral', 'talentwm.com', 1, 0, NULL),
(68, 'referral', 'www.imdbpress.top', 1, 0, NULL),
(69, 'referral', 'mawasem-eg.com', 2, 0, NULL),
(70, 'referral', 'tagassistant.google.com', 5, 0, NULL),
(71, 'referral', 'www.google.co.in', 1, 0, NULL),
(72, 'referral', 'www.google.fr', 1, 0, NULL),
(73, 'referral', 'www.google.jo', 3, 0, NULL),
(74, 'referral', 'www.youtube.com', 2, 0, NULL),
(75, 'referral', 'youtube.com', 2, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` int NOT NULL,
  `email_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email_subject` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `email_body` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `email_type`, `email_subject`, `email_body`, `status`) VALUES
(1, 'new_order', 'Your Order Placed Successfully', '<p>Hello {customer_name},<br>Your Order Number is {order_number}<br>Your order has been placed successfully</p>', 1),
(2, 'new_registration', 'Welcome To Vowalaa E-Commerce', '<p>Hello {customer_name},<br>You have successfully registered to {website_title}, We wish you will have a wonderful experience using our service.</p><p>Thank You<br></p>', 1),
(3, 'vendor_accept', 'Your Vendor Account Activated', '<p>Hello {customer_name},<br>Your Vendor Account Activated Successfully. Please Login to your account and build your own shop.</p><p>Thank You<br></p>', 1),
(4, 'subscription_warning', 'Your subscrption plan will end after five days', '<p>Hello {customer_name},<br>Your subscription plan duration will end after five days. Please renew your plan otherwise all of your products will be deactivated.</p><p>Thank You<br></p>', 1),
(5, 'vendor_verification', 'Request for verification.', '<p>Hello {customer_name},<br>You are requested verify your account. Please send us photo of your passport.</p><p>Thank You<br></p>', 1);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `generalsettings`
--

DROP TABLE IF EXISTS `generalsettings`;
CREATE TABLE IF NOT EXISTS `generalsettings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_ar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title_ar` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `header_email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `header_phone` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `footer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `footer_ar` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `copyright` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `copyright_ar` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `colors` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loader` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_loader` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_talkto` tinyint(1) NOT NULL DEFAULT '1',
  `talkto` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_drift` int DEFAULT NULL,
  `drift` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_messenger` int DEFAULT '1',
  `messenger` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_language` tinyint(1) NOT NULL DEFAULT '1',
  `is_loader` tinyint(1) NOT NULL DEFAULT '1',
  `map_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_disqus` tinyint(1) NOT NULL DEFAULT '0',
  `disqus` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_contact` tinyint(1) NOT NULL DEFAULT '0',
  `is_faq` tinyint(1) NOT NULL DEFAULT '0',
  `guest_checkout` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_check` tinyint(1) NOT NULL DEFAULT '0',
  `cod_check` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stripe_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `currency_format` tinyint(1) NOT NULL DEFAULT '0',
  `withdraw_fee` double NOT NULL DEFAULT '0',
  `withdraw_charge` double NOT NULL DEFAULT '0',
  `tax` double NOT NULL DEFAULT '0',
  `shipping_cost` double NOT NULL DEFAULT '0',
  `smtp_host` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_port` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_user` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `smtp_pass` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_smtp` tinyint(1) NOT NULL DEFAULT '0',
  `is_comment` tinyint(1) NOT NULL DEFAULT '1',
  `is_currency` tinyint(1) NOT NULL DEFAULT '1',
  `add_cart` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `out_stock` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `add_wish` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `already_wish` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wish_remove` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `add_compare` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `already_compare` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `compare_remove` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `color_change` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `coupon_found` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `no_coupon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `already_coupon` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `order_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `order_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_affilate` tinyint(1) NOT NULL DEFAULT '1',
  `affilate_charge` int NOT NULL DEFAULT '0',
  `affilate_banner` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `already_cart` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fixed_commission` double NOT NULL DEFAULT '0',
  `percentage_commission` double NOT NULL DEFAULT '0',
  `multiple_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `multiple_packaging` tinyint NOT NULL DEFAULT '0',
  `vendor_ship_info` tinyint(1) NOT NULL DEFAULT '0',
  `reg_vendor` tinyint(1) NOT NULL DEFAULT '0',
  `cod_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `paypal_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `stripe_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `header_color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright_color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_admin_loader` tinyint(1) NOT NULL DEFAULT '0',
  `menu_color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_hover_color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_home` tinyint(1) NOT NULL DEFAULT '0',
  `is_verification_email` tinyint(1) NOT NULL DEFAULT '0',
  `instamojo_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instamojo_token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instamojo_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_instamojo` tinyint(1) NOT NULL DEFAULT '0',
  `instamojo_sandbox` tinyint(1) NOT NULL DEFAULT '0',
  `is_paystack` tinyint(1) NOT NULL DEFAULT '0',
  `paystack_key` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `paystack_email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `paystack_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wholesell` int NOT NULL DEFAULT '0',
  `is_capcha` tinyint(1) NOT NULL DEFAULT '0',
  `error_banner` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_popup` tinyint(1) NOT NULL DEFAULT '0',
  `popup_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `popup_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `popup_title_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `popup_text_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `popup_background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `invoice_logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vendor_color` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_secure` tinyint(1) NOT NULL DEFAULT '0',
  `is_report` tinyint(1) NOT NULL,
  `paypal_check` tinyint(1) DEFAULT '0',
  `paypal_business` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `footer_logo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `email_encryption` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_maintain` tinyint(1) NOT NULL DEFAULT '0',
  `maintain_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `wallet_photo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `loyalty_photo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipment` int DEFAULT '1',
  `templatee_select` int NOT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_ar` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_hours` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paymentsicon` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` double DEFAULT NULL,
  `refelar_points` int DEFAULT NULL,
  `subscribee_message` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subscribee_message_ar` varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `messagee` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `messagee_ar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `light_dark` tinyint DEFAULT '1',
  `is_brand` int DEFAULT NULL,
  `brandphoto` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `map` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `policy` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `policy_ar` text CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `feature_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `best_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `top_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `big_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hot_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trending_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discount_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_icon` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sidebar_logo` varchar(200) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL,
  `choose_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `choose_detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `choose_title_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `choose_detail_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `percent_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `percent_title_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `percent_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about_img1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about_img2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about_img3` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about_img4` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `home_about_link` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `about_background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `service_background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `about_page_background` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `management` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `management_ar` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `contact_emails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `press` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `press_ar` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `press_image` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `generalsettings`
--

INSERT INTO `generalsettings` (`id`, `logo`, `logo_ar`, `favicon`, `title`, `title_ar`, `header_email`, `header_phone`, `footer`, `footer_ar`, `copyright`, `copyright_ar`, `colors`, `loader`, `admin_loader`, `is_talkto`, `talkto`, `is_drift`, `drift`, `is_messenger`, `messenger`, `is_language`, `is_loader`, `map_key`, `is_disqus`, `disqus`, `is_contact`, `is_faq`, `guest_checkout`, `stripe_check`, `cod_check`, `stripe_key`, `stripe_secret`, `currency_format`, `withdraw_fee`, `withdraw_charge`, `tax`, `shipping_cost`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `from_email`, `from_name`, `is_smtp`, `is_comment`, `is_currency`, `add_cart`, `out_stock`, `add_wish`, `already_wish`, `wish_remove`, `add_compare`, `already_compare`, `compare_remove`, `color_change`, `coupon_found`, `no_coupon`, `already_coupon`, `order_title`, `order_text`, `is_affilate`, `affilate_charge`, `affilate_banner`, `already_cart`, `fixed_commission`, `percentage_commission`, `multiple_shipping`, `multiple_packaging`, `vendor_ship_info`, `reg_vendor`, `cod_text`, `paypal_text`, `stripe_text`, `header_color`, `footer_color`, `copyright_color`, `is_admin_loader`, `menu_color`, `menu_hover_color`, `is_home`, `is_verification_email`, `instamojo_key`, `instamojo_token`, `instamojo_text`, `is_instamojo`, `instamojo_sandbox`, `is_paystack`, `paystack_key`, `paystack_email`, `paystack_text`, `wholesell`, `is_capcha`, `error_banner`, `is_popup`, `popup_title`, `popup_text`, `popup_title_ar`, `popup_text_ar`, `popup_background`, `invoice_logo`, `user_image`, `vendor_color`, `is_secure`, `is_report`, `paypal_check`, `paypal_business`, `footer_logo`, `email_encryption`, `is_maintain`, `maintain_text`, `wallet_photo`, `loyalty_photo`, `shipment`, `templatee_select`, `address`, `address_ar`, `working_hours`, `phone`, `email`, `paymentsicon`, `points`, `refelar_points`, `subscribee_message`, `subscribee_message_ar`, `messagee`, `messagee_ar`, `light_dark`, `is_brand`, `brandphoto`, `map`, `policy`, `policy_ar`, `feature_icon`, `best_icon`, `top_icon`, `big_icon`, `new_icon`, `hot_icon`, `trending_icon`, `discount_icon`, `contact_icon`, `sidebar_logo`, `choose_title`, `choose_detail`, `choose_title_ar`, `choose_detail_ar`, `percent_title`, `percent_title_ar`, `percent_value`, `home_about`, `home_about_ar`, `home_about_img1`, `home_about_img2`, `home_about_img3`, `home_about_img4`, `home_about_link`, `about_background`, `service_background`, `contact_background`, `about_page_background`, `management`, `management_ar`, `contact_emails`, `press`, `press_ar`, `press_image`) VALUES
(1, '1701377654ezgif.com-gif-maker-1.webp', '1701377653ezgif.com-gif-maker-1.webp', '1701377663ezgif.com-gif-maker-1.webp', 'Haydro Racks', 'Haydro Racks', 'Info@example.com', '015203043012', '<p>Characterized by good company for Trading &amp; Supplies manufactures hydraulic products for labor services commensurate with Taibah work in the factories and warehouses And also provides good company what it needs from the customer equipment and winches imported and domestic demand is also characterized by the company after-sales service and warranty Which includes manufacturing defects and provide the necessary maintenance of the vehicles,</p>', '<p>Characterized by good company for Trading &amp; Supplies manufactures hydraulic products for labor services commensurate with Taibah work in the factories and warehouses And also provides good company what it needs from the customer equipment and winches imported and domestic demand is also characterized by the company after-sales service and warranty Which includes manufacturing defects and provide the necessary maintenance of the vehicles,</p>', '<p><a href=\"https://www.cangrowonline.com/\">cangrowonline</a>&nbsp;All Rights Reserved&nbsp;Designed</p>', '<p><a href=\"https://www.cangrowonline.com/\">cangrowonline</a>&nbsp;All Rights Reserved&nbsp;Designed</p>', '#0d0609', '16110739481595675963preloader.gif', '1641055499giphy.gif', 0, NULL, 0, NULL, 0, NULL, 1, 1, 'AIzaSyB1GpE4qeoJ__70UZxvX9CTMUTZRZNHcu8', 0, NULL, 1, 1, 1, 0, 1, 'pk_test_UnU1Coi1p5qFGwtpjZMRMgJM', 'sk_test_QQcg3vGsKRPlW6T3dXcNJsor', 1, 5, 5, 0, 5, 'in-v3.mailjet.com', '587', '0e05029e2dc70da691aa2223aa53c5be', '5df1b6242e86bce602c3fd9adc178460', 'official@cangrowonline.com', 'CanGrow', 1, 1, 1, 'Successfully Added To Cart', 'Out Of Stock', 'Add To Wishlist', 'Already Added To Wishlist', 'Successfully Removed From The Wishlist', 'Successfully Added To Compare', 'Already Added To Compare', 'Successfully Removed From The Compare', 'Successfully Changed The Color', 'Coupon Found', 'No Coupon Found', 'Coupon Already Applied', 'THANK YOU FOR YOUR PURCHASE.', 'We\'ll email you an order confirmation with details and tracking info.', 0, 8, '15587771131554048228onepiece.jpeg', 'Already Added To Cart', 5, 5, 1, 1, 0, 1, 'Pay with cash upon delivery.', 'Pay via your PayPal account.', 'Pay via your Credit Card.', '#ffffff', '#cfb2c2', '#02020c', 1, '#ff5500', '#02020c', 1, 1, 'test_172371aa837ae5cad6047dc3052', 'test_4ac5a785e25fc596b67dbc5c267', 'Pay via your Instamojo account.', 0, 1, 0, 'pk_test_162a56d42131cbb01932ed0d2c48f9cb99d8e8e2', 'junnuns@gmail.com', 'Pay via your Paystack account.', 6, 1, '16110761881566878455404.png', 1, 'NEWSLETTER', '<div class=\"modal-body\">\r\n<div class=\"text\">\r\n<p>تواصل معنا لأى إستفسار أو أسئلة أو لإستلام وتبليغ النتائج أو لحجز موعد للزيارة المنزلية أو إذا كنت تحتاج إلى أى نوع من أنواع التحاليل ولا يمكنك العثور عليها حيث تتوفر لدينا تحاليل إضافية</p>\r\n&nbsp;\r\n\r\n<p>برجاء ملئ <a href=\"https://localhost/arabslab/contact.html\">استمارتك</a> وسيقوم مندوب خدمة العملاء بالتواصل معك قريبـًا بالبريد الالكترونى <a class=\"icons_single_contact\" href=\"mailto:info@arabilabs.com\">info@arabilabs.com</a></p>\r\n&nbsp;\r\n\r\n<p>أو إمل <a href=\"https://localhost/arabslab/contact.html\"> نموذج التواصل</a> فى صفحة اتصل بنا</p>\r\n</div>\r\n</div>', 'النشرة الإخبارية', '<div class=\"modal-body\">\r\n<div class=\"text\">\r\n<p>تواصل معنا لأى إستفسار أو أسئلة أو لإستلام وتبليغ النتائج أو لحجز موعد للزيارة المنزلية أو إذا كنت تحتاج إلى أى نوع من أنواع التحاليل ولا يمكنك العثور عليها حيث تتوفر لدينا تحاليل إضافية</p>\r\n&nbsp;\r\n\r\n<p>برجاء ملئ <a href=\"https://localhost/arabslab/contact.html\">استمارتك</a> وسيقوم مندوب خدمة العملاء بالتواصل معك قريبـًا بالبريد الالكترونى <a class=\"icons_single_contact\" href=\"mailto:info@arabilabs.com\">info@arabilabs.com</a></p>\r\n&nbsp;\r\n\r\n<p>أو إمل <a href=\"https://localhost/arabslab/contact.html\"> نموذج التواصل</a> فى صفحة اتصل بنا</p>\r\n</div>\r\n</div>', '1640564470logo.jpg', '1581489581logo-signs.png', '1567655174profile.jpg', '#666666', 1, 1, 0, 'mostafa_hamdi235@yahoo.com', '16110739941595537899Untitdsadsaled.png', 'tls', 0, '<div style=\"text-align:center\">&nbsp;</div>\r\n\r\n<h2 style=\"text-align:center\"><span style=\"font-family:impact; font-size:xx-large\"><img alt=\"laugh\" src=\"https://cdn.ckeditor.com/4.15.0/full/plugins/smiley/images/teeth_smile.png\" style=\"height:23px; width:23px\" title=\"laugh\" />UNDER MAINTENANCE<img alt=\"laugh\" src=\"https://cdn.ckeditor.com/4.15.0/full/plugins/smiley/images/teeth_smile.png\" style=\"height:23px; width:23px\" title=\"laugh\" /></span></h2>\r\n\r\n<p><span style=\"font-family:impact; font-size:xx-large\">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <img alt=\"\" src=\"https://localhost/cangrow/assets/images/1614632768output-onlinepngtools(2).png\" style=\"height:168px; width:419px\" /></span></p>', '16110740721585070655Vowaa.png', '16110740721585070655Vowaa.png', 1, 155, 'Factory: New Cairo , Third compound , Area A , factory plot No.208', 'Factory: New Cairo , Third compound , Area A , factory plot No.208', 'Mon To Sat - 10.00 - 07.00', '233060021', 'info@talentwm.com', '1590681359payments.png', 0.2, 100, 'subscribe', 'اشترك', 'Vowalaa-APP', 'فوالا-تطبيقات', 1, 1, NULL, NULL, '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incidid unt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitat ion ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', '<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incidid unt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitat ion ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>', '16967140321212.jpeg', '16967140511212.jpeg', '16967140491212.jpeg', '1655310067slider.png', '1644156155computer-desk-laptop-stethoscope-48604-scaled-1.webp', '1655310076slider.png', '16967140481212.jpeg', '16967140461212.jpeg', '1696713948O9FG4R0.jpg', '1639836528logo.png', 'توفير الوقت,توفير المال,مجاناً وبدون التزام', 'احصل على ما يصل إلى 3 عروض من موردينا المحددين عن طريق ملء نموذج واحد فقط,احصل على ما يصل إلى 3 عروض من موردينا المحددين عن طريق ملء نموذج واحد فقط,احصل على ما يصل إلى 3 عروض من موردينا المحددين عن طريق ملء نموذج واحد فقط', 'توفير الوقت,توفير المال,مجاناً وبدون التزام', 'احصل على ما يصل إلى 3 عروض من موردينا المحددين عن طريق ملء نموذج واحد فقط,احصل على ما يصل إلى 3 عروض من موردينا المحددين عن طريق ملء نموذج واحد فقط,احصل على ما يصل إلى 3 عروض من موردينا المحددين عن طريق ملء نموذج واحد فقط', 'Business Strategy,Marketing Solution,Business Analysis,Marketing Strategy,Final Chart', 'Business Strategy,Marketing Solution,Business Analysis,Marketing Strategy,Final Chart', '85,60,70', 'Lorem ipsum dolor sit amet, consectetur notted adipisicing elit sed do eiusmod tempor incididunt ut labore et simply free text dolore magna aliqua lonm andhn.', 'Lorem ipsum dolor sit amet, consectetur notted adipisicing elit sed do eiusmod tempor incididunt ut labore et simply free text dolore magna aliqua lonm andhn.', '1679754073slide.png', '17080958491.jpg', '1687452179slider.jpg', '1678130109shape3.jpeg', 'https://www.youtube.com/watch?v=xKxrkht7CpY', '1637436950d4bc10_cdb32f58b48d490db75ca0a1ee087565_mv2_d_5760_3840_s_4_2.webp', '1637436951d4bc10_cdb32f58b48d490db75ca0a1ee087565_mv2_d_5760_3840_s_4_2.webp', '1637436952parallax-bg8.jpg', '1637436953d4bc10_cdb32f58b48d490db75ca0a1ee087565_mv2_d_5760_3840_s_4_2.webp', 'Our goal is always to obtain customer satisfaction, so we always strive to develop products and maintain the quality level. We have a special department for maintenance and renewal and a one-year warranty against manufacturing defects', 'هدفنا دائمًا هو الحصول علي رضاء العملاء ​​، لذلك نسعى دائمًا لتطوير المنتجات والحفاظ على مستوي الجودة، لدينا قسم خاص للصيانة والتجديد و ضمان لمدة عام ضد عيوب التصنيع.', 'info@cangrowonline.com', 'elamine Mix is our unique feature, as it can produce more than 100 melamine boards per hour and reaches a production of 3000 boards per day', 'ميكس الميلامين هو الميزة الفريدة من نوعها لدينا حيث بأمكانه انتاج اكثر من 100 لوح ميلامين في الساعة ويصل لانتاج 3000 لوح في اليوم', '1678134243shape2.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `socialsettings`
--

DROP TABLE IF EXISTS `socialsettings`;
CREATE TABLE IF NOT EXISTS `socialsettings` (
  `id` int UNSIGNED NOT NULL,
  `facebook` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gplus` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `twitter` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkedin` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dribble` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ystatus` tinyint NOT NULL DEFAULT '0',
  `i_status` int DEFAULT NULL,
  `instagram` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `f_status` tinyint NOT NULL DEFAULT '1',
  `g_status` tinyint NOT NULL DEFAULT '1',
  `t_status` tinyint NOT NULL DEFAULT '1',
  `l_status` tinyint NOT NULL DEFAULT '1',
  `d_status` tinyint NOT NULL DEFAULT '1',
  `f_check` tinyint DEFAULT NULL,
  `g_check` tinyint DEFAULT NULL,
  `fclient_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fclient_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fredirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gclient_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gclient_secret` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `gredirect` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `socialsettings`
--

INSERT INTO `socialsettings` (`id`, `facebook`, `gplus`, `twitter`, `linkedin`, `dribble`, `youtube`, `ystatus`, `i_status`, `instagram`, `f_status`, `g_status`, `t_status`, `l_status`, `d_status`, `f_check`, `g_check`, `fclient_id`, `fclient_secret`, `fredirect`, `gclient_id`, `gclient_secret`, `gredirect`) VALUES
(1, 'https://www.facebook.com/', 'https://plus.google.com/', 'https://www.Twitter.com/', 'https://Linkedin.com/', 'https://www.behance.com/', 'https://youtube.com/channel', 0, 1, 'https://instagram.com/', 1, 0, 0, 1, 0, 1, 1, '503140663460329', 'f66cd670ec43d14209a2728af26dcc43', 'https://vowalaa.com/demo-saas/auth/facebook/callback', '904681031719-sh1aolu42k7l93ik0bkiddcboghbpcfi.apps.googleusercontent.com', 'yGBWmUpPtn5yWhDAsXnswEX3', 'http://localhost/marketplace/auth/google/callback');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `avatar`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@themesbrand.com', NULL, '$2y$10$bt8ZcxjmnHRdsRIFTAWoIOW0CTlHSecnnoB0Xtw..TuijXBN50wKK', 'avatar-1.jpg', NULL, '2024-06-28 05:21:26', '2024-06-28 05:21:26');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
