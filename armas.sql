-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 07, 2026 at 10:14 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `armas`
--

-- --------------------------------------------------------

--
-- Table structure for table `agencies`
--

CREATE TABLE `agencies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by_admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agencies`
--

INSERT INTO `agencies` (`id`, `user_id`, `name`, `license_number`, `address`, `contact_number`, `status`, `created_by_admin_id`, `created_at`) VALUES
(8, 2, 'FilWorkers Placement Agency', 'POEA-MNL-2021-0045', '123 Taft Avenue, Malate, Manila', '09171234567', 'active', 1, '2026-06-06 03:20:00'),
(9, 3, 'Global Filipino Manpower Services', 'POEA-MNL-2020-0112', '456 EDSA, Quezon City, Metro Manila', '09281234567', 'active', 1, '2026-06-06 03:20:00'),
(10, 4, 'OFW Bridge Recruitment Corp.', 'POEA-CEV-2019-0078', '789 Colon Street, Cebu City', '09391234567', 'active', 1, '2026-06-06 03:20:00'),
(11, 5, 'Bagong Bayani Employment Agency', 'POEA-DAV-2022-0033', '321 Claveria Street, Davao City', '09451234567', 'active', 1, '2026-06-06 03:20:00'),
(12, 6, 'Maharlika International Staffing', 'POEA-MNL-2018-0201', '654 Shaw Blvd, Mandaluyong City', '09561234567', '', 1, '2026-06-06 03:20:00'),
(13, 7, 'Sunrise Overseas Placement Inc.', 'POEA-MNL-2023-0009', '987 Ortigas Ave, Pasig City', '09671234567', 'active', 1, '2026-06-06 03:20:00'),
(14, 8, 'Kabayan Workers Alliance', 'POEA-ILO-2020-0055', '147 General Luna St, Iloilo City', '09781234567', '', 1, '2026-06-06 03:20:00');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target_type` varchar(100) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `actor_id`, `action`, `target_type`, `target_id`, `ip_address`, `created_at`) VALUES
(1, 19, 'EMAIL_VERIFIED', NULL, NULL, '::1', '2026-06-06 04:00:35'),
(2, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:00:45'),
(3, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:00:56'),
(4, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:01:15'),
(5, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:24:04'),
(6, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:24:45'),
(7, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:50:11'),
(8, 20, 'EMAIL_VERIFIED', NULL, NULL, '::1', '2026-06-06 04:52:34'),
(9, 20, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:52:38'),
(10, 20, 'LOGIN', NULL, NULL, '::1', '2026-06-06 04:52:49'),
(11, 20, 'LOGOUT', NULL, NULL, '::1', '2026-06-06 04:54:12'),
(12, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-06 05:40:37'),
(13, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 05:43:14'),
(14, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-06 05:43:32'),
(15, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-06 06:16:20'),
(16, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 03:23:26'),
(17, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 03:23:43'),
(18, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 03:25:48'),
(19, 7, 'LOGIN', NULL, NULL, '::1', '2026-06-07 03:28:24'),
(20, 7, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 03:53:25'),
(21, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 03:53:43'),
(22, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 04:01:58'),
(23, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-07 04:02:04'),
(24, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 04:10:41'),
(25, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 04:12:06'),
(26, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 04:14:49'),
(27, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 04:16:06'),
(28, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 04:19:07'),
(29, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 04:19:23'),
(30, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 04:19:26'),
(31, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 04:30:41'),
(32, 22, 'EMAIL_VERIFIED', NULL, NULL, '::1', '2026-06-07 05:05:07'),
(33, 23, 'EMAIL_VERIFIED', NULL, NULL, '::1', '2026-06-07 05:05:12'),
(34, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 05:05:27'),
(35, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-07 05:05:46'),
(36, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 05:24:47'),
(37, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:06:10'),
(38, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 06:26:52'),
(39, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:26:58'),
(40, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:46:57'),
(41, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 06:48:25'),
(42, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:49:23'),
(43, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 06:49:29'),
(44, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:50:37'),
(45, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 06:50:39'),
(46, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:50:54'),
(47, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:52:53'),
(48, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 06:57:56'),
(49, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:58:52'),
(50, 2, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 06:59:20'),
(51, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 06:59:36'),
(52, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:00:10'),
(53, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:03:19'),
(54, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:03:26'),
(55, 23, 'SUBMIT_CASE', 'cases', NULL, '::1', '2026-06-07 07:05:16'),
(56, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:17:37'),
(57, 3, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:18:56'),
(58, 3, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:19:09'),
(59, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:19:34'),
(60, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:22:37'),
(61, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:23:07'),
(62, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:25:56'),
(63, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:26:21'),
(64, 23, 'SUBMIT_CASE', 'cases', NULL, '::1', '2026-06-07 07:27:04'),
(65, 23, 'SUBMIT_CASE', 'cases', NULL, '::1', '2026-06-07 07:28:55'),
(66, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:29:19'),
(67, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:29:29'),
(68, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:33:41'),
(69, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:33:51'),
(70, 2, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:35:40'),
(71, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:35:50'),
(72, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:36:26'),
(73, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:36:31'),
(74, 2, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:36:58'),
(75, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:37:16'),
(76, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:44:36'),
(77, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:44:54'),
(78, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:46:31'),
(79, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:46:50'),
(80, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:53:05'),
(81, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 07:55:28'),
(82, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 07:56:13'),
(83, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 08:08:27'),
(84, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_number` varchar(30) DEFAULT NULL,
  `ofw_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `status` enum('pending','in_process','resolved','closed') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `location_abroad` varchar(255) DEFAULT NULL,
  `employer_name` varchar(255) DEFAULT NULL,
  `date_of_departure` date DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `ofw_id`, `agency_id`, `type`, `status`, `description`, `location_abroad`, `employer_name`, `date_of_departure`, `emergency_contact_name`, `emergency_contact_number`, `created_at`, `updated_at`) VALUES
(1, 'ARMAS-2026-0001', 14, 10, 'Legal Assistance', 'pending', 'N/A', '2313 SAUDI', 'ROBIN PANALIGAN', '2026-06-17', 'MARK JOSEPH', '9103028321', '2026-06-07 06:55:00', '2026-06-07 06:55:00'),
(2, 'ARMAS-2026-0002', 14, 10, 'Financial Aid', 'pending', 'NANANANA', '', 'REANAN BANTA', '2026-06-07', 'ALBA LAWRENCE', '01928488244', '2026-06-07 07:05:16', '2026-06-07 07:05:16'),
(3, 'ARMAS-2026-0003', 14, 10, 'Emergency Repatriation', 'in_process', 'SADASDDSADSA', '', 'MJ', '2026-06-18', 'ALBA LAWRENCE', '921902938', '2026-06-07 07:27:04', '2026-06-07 07:47:04'),
(4, 'ARMAS-2026-0004', 14, 10, 'Psychosocial Support', 'closed', 'ASDAS', 'EUROPE', 'MATT', '2026-06-09', 'ASDADAS', '23423424', '2026-06-07 07:28:55', '2026-06-07 07:43:39');

-- --------------------------------------------------------

--
-- Table structure for table `case_updates`
--

CREATE TABLE `case_updates` (
  `id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `case_updates`
--

INSERT INTO `case_updates` (`id`, `case_id`, `note`, `updated_by`, `created_at`) VALUES
(1, 4, 'Status updated to closed.', 4, '2026-06-07 07:43:39'),
(2, 3, 'Status updated to in_process.', 4, '2026-06-07 07:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `read_at`, `created_at`) VALUES
(2, 4, 'New repatriation request ARMAS-2026-0002 has been submitted.', 'new_case', NULL, '2026-06-07 07:05:16'),
(3, 4, 'New repatriation request ARMAS-2026-0003 has been submitted.', 'new_case', NULL, '2026-06-07 07:27:04'),
(4, 4, 'New repatriation request ARMAS-2026-0004 has been submitted.', 'new_case', NULL, '2026-06-07 07:28:55'),
(5, 23, 'Your case ARMAS-2026-0004 status has been updated to resolved.', 'status_update', '2026-06-07 15:46:59', '2026-06-07 07:42:05'),
(6, 23, 'Your case ARMAS-2026-0004 status has been updated to closed.', 'status_update', '2026-06-07 15:46:57', '2026-06-07 07:43:39'),
(7, 23, 'Your case ARMAS-2026-0003 status has been updated to in_process.', 'status_update', '2026-06-07 15:53:10', '2026-06-07 07:47:04');

-- --------------------------------------------------------

--
-- Table structure for table `ofws`
--

CREATE TABLE `ofws` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `suffix` varchar(20) DEFAULT NULL,
  `agency_id` int(11) NOT NULL,
  `ofw_type` enum('land-based','sea-based') NOT NULL DEFAULT 'land-based',
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ofws`
--

INSERT INTO `ofws` (`id`, `user_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `agency_id`, `ofw_type`, `address`, `contact_number`, `created_at`) VALUES
(11, 19, 'BANTA', 'JOHN', 'REANAN L.', '', 13, 'land-based', '780 KAGANDAHAN ST', '09619878435', '2026-06-06 04:00:04'),
(12, 20, 'AQUINO', 'IAN LAURENCE', 'TOMAS', '', 10, 'land-based', '555 PALTOC SAMPALOC MANILA', '09617244155', '2026-06-06 04:51:44'),
(13, 22, 'PANALIGAN', 'ROBIN', 'E.', '', 9, 'sea-based', '661 MARY ANN ST. GAGALANGIN TONDO MANILA', '', '2026-06-07 05:04:12'),
(14, 23, 'ALBA', 'LAWRENCE', 'MADEJA', '', 10, 'sea-based', '', '231332766787', '2026-06-07 05:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `code_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `user_id`, `code_hash`, `expires_at`, `used`, `created_at`) VALUES
(11, 19, '$2y$10$7GWoj46Rmq.M8w2huNLoTOmkzl/C2Aw4.wooAK44hJDDJBonoBJUi', '2026-06-06 06:10:04', 1, '2026-06-06 04:00:04'),
(12, 20, '$2y$10$S6YMvOQOrWo.pWJUezfo4OqVAqeY/An37a.SRDxe//PGnpQaWroQG', '2026-06-06 07:01:45', 1, '2026-06-06 04:51:45'),
(13, 22, '$2y$10$ILtmyR9CttD/jrn6orFiauka3haq7NieSb7zedJ3tikZYY0Qecr9a', '2026-06-07 07:14:12', 1, '2026-06-07 05:04:12'),
(14, 23, '$2y$10$LMdwsDB1T5pDOpphOxW58OjX5dPDRalrtj7vCgA0dlctu1DM5.cHy', '2026-06-07 07:14:31', 1, '2026-06-07 05:04:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('ofw','agency','admin','superadmin') NOT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `status`, `login_attempts`, `locked_until`, `created_at`, `updated_at`) VALUES
(1, 'admin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 07:23:07'),
(2, 'agency1@filworkers.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 06:58:52'),
(3, 'agency2@globalfilipino.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42'),
(4, 'agency3@ofwbridge.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42'),
(5, 'agency4@bagongbayani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42'),
(6, 'agency5@maharlika.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'pending', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42'),
(7, 'agency6@sunrise.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42'),
(8, 'agency7@kabayan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', '', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42'),
(19, 'reananbanta@gmail.com', '$2y$10$y9snz8g93RPLM2s/qaQZ7.LVFTwpaygiRiA5cUQEvW0SBosoJmsz.', 'ofw', 'active', 0, NULL, '2026-06-06 04:00:04', '2026-06-06 04:24:45'),
(20, 'ianaquino0208@gmail.com', '$2y$10$SVKYD6qHCFcH.ABizUr2uOLPBeOR6aqIm4bsDM9VbUtrOEVKcngjW', 'ofw', 'active', 0, NULL, '2026-06-06 04:51:44', '2026-06-06 04:52:34'),
(21, 'superadmin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'active', 0, NULL, '2026-06-07 04:01:25', '2026-06-07 04:01:25'),
(22, 'panaliganrobin48@gmail.com', '$2y$10$ZR9dz7ROeIRxhnKYjQJLxu1vFiIaCvZ5utb6aesePy2gRUIfnJpYS', 'ofw', 'active', 0, NULL, '2026-06-07 05:04:12', '2026-06-07 05:05:07'),
(23, 'opaparmas@gmail.com', '$2y$10$rNyzrgKwGzXoHAqP5QJW5.L26TKq/Aps9uPKtT/6QAu2xRc0ufwvC', 'ofw', 'active', 0, NULL, '2026-06-07 05:04:31', '2026-06-07 06:52:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agencies`
--
ALTER TABLE `agencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actor_id` (`actor_id`);

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `case_number` (`case_number`),
  ADD KEY `ofw_id` (`ofw_id`),
  ADD KEY `agency_id` (`agency_id`);

--
-- Indexes for table `case_updates`
--
ALTER TABLE `case_updates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `case_id` (`case_id`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `ofws`
--
ALTER TABLE `ofws`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agencies`
--
ALTER TABLE `agencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `case_updates`
--
ALTER TABLE `case_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ofws`
--
ALTER TABLE `ofws`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `agencies`
--
ALTER TABLE `agencies`
  ADD CONSTRAINT `agencies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cases`
--
ALTER TABLE `cases`
  ADD CONSTRAINT `cases_ibfk_1` FOREIGN KEY (`ofw_id`) REFERENCES `ofws` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cases_ibfk_2` FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `case_updates`
--
ALTER TABLE `case_updates`
  ADD CONSTRAINT `case_updates_ibfk_1` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `case_updates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ofws`
--
ALTER TABLE `ofws`
  ADD CONSTRAINT `ofws_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

ALTER TABLE `ofws` 
  DROP FOREIGN KEY `cases_ibfk_1`, -- Drop constraints temporarily if needed for table re-alignment
  DROP COLUMN `agency_id`,
  ADD COLUMN `sex` ENUM('MALE', 'FEMALE') NOT NULL AFTER `suffix`,
  ADD COLUMN `birthdate` DATE NOT NULL AFTER `sex`,
  ADD COLUMN `supporting_document` VARCHAR(255) NOT NULL AFTER `contact_number`;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;