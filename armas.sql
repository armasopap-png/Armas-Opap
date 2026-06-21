-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 21, 2026 at 04:53 PM
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
(12, 6, 'Maharlika International Staffing', 'POEA-MNL-2018-0201', '654 Shaw Blvd, Mandaluyong City', '09561234567', 'active', 1, '2026-06-06 03:20:00'),
(13, 7, 'Sunrise Overseas Placement Inc.', 'POEA-MNL-2023-0009', '987 Ortigas Ave, Pasig City', '09671234567', 'active', 1, '2026-06-06 03:20:00'),
(14, 8, 'Kabayan Workers Alliance', 'POEA-ILO-2020-0055', '147 General Luna St, Iloilo City', '09781234567', 'active', 1, '2026-06-06 03:20:00');

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
(84, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:08:40'),
(85, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:50:01'),
(86, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:51:35'),
(87, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 08:52:22'),
(88, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:52:49'),
(89, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:53:41'),
(90, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 08:59:08'),
(91, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:59:20'),
(92, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-07 08:59:31'),
(93, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 11:50:05'),
(94, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 12:07:08'),
(95, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:14:23'),
(96, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:15:26'),
(97, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:29:37'),
(98, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:30:31'),
(99, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:32:51'),
(100, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:33:59'),
(101, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:35:45'),
(102, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:36:11'),
(103, 2, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:36:45'),
(104, 2, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:37:37'),
(105, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:43:20'),
(106, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:43:49'),
(107, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:44:23'),
(108, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:44:47'),
(109, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 14:50:44'),
(110, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 14:50:51'),
(111, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 16:33:26'),
(112, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-07 16:35:06'),
(113, 22, 'SUBMIT_CASE', 'cases', NULL, '::1', '2026-06-07 16:36:11'),
(114, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 16:38:31'),
(115, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 16:38:37'),
(116, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 16:39:15'),
(117, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-07 16:39:25'),
(118, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 16:42:35'),
(119, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-07 16:42:41'),
(120, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 17:01:32'),
(121, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-07 17:01:38'),
(122, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 17:01:47'),
(123, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-07 17:02:00'),
(124, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-07 17:16:01'),
(125, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-07 17:16:20'),
(126, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-08 02:54:09'),
(127, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 02:56:16'),
(128, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:01:09'),
(129, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:02:06'),
(130, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:02:23'),
(131, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:02:33'),
(132, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:02:40'),
(133, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:14:51'),
(134, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:15:03'),
(135, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:15:41'),
(136, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:26:34'),
(137, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:43:37'),
(138, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:43:52'),
(139, 19, 'SUBMIT_CASE', 'cases', NULL, '::1', '2026-06-08 03:44:55'),
(140, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:44:56'),
(141, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:45:10'),
(142, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:45:21'),
(143, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-08 03:45:27'),
(144, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-08 03:47:18'),
(145, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-10 14:41:39'),
(146, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-10 14:46:37'),
(147, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-10 14:46:42'),
(148, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-11 14:19:05'),
(149, 19, 'LOGOUT', NULL, NULL, '::1', '2026-06-11 14:22:24'),
(150, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-11 14:23:16'),
(151, 19, 'LOGIN', NULL, NULL, '::1', '2026-06-11 14:40:52'),
(152, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 03:37:21'),
(153, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 03:37:53'),
(154, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 03:59:18'),
(155, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 03:59:23'),
(156, 1, 'TOGGLE_STATUS_ACTIVE', 'users', 6, '::1', '2026-06-20 04:11:17'),
(157, 1, 'TOGGLE_STATUS_ACTIVE', 'users', 8, '::1', '2026-06-20 04:11:19'),
(158, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 04:47:36'),
(159, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 04:47:42'),
(160, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 04:48:31'),
(161, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 04:49:17'),
(162, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 04:54:31'),
(163, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 04:54:53'),
(164, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 04:54:59'),
(165, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 04:55:31'),
(166, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-20 04:55:36'),
(167, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:32:09'),
(168, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:32:11'),
(169, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:33:34'),
(170, 4, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:33:51'),
(171, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:33:56'),
(172, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:35:11'),
(173, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:35:58'),
(174, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:36:50'),
(175, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:37:23'),
(176, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:37:47'),
(177, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:38:01'),
(178, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:38:38'),
(179, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:39:02'),
(180, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:39:11'),
(181, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:49:52'),
(182, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:52:30'),
(183, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:53:10'),
(184, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 06:54:05'),
(185, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:56:10'),
(186, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 06:56:48'),
(187, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 07:18:09'),
(188, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:19:03'),
(189, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:19:25'),
(190, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:19:52'),
(191, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:20:22'),
(192, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:21:06'),
(193, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 07:21:32'),
(194, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:21:48'),
(195, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:22:04'),
(196, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:22:14'),
(197, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:22:48'),
(198, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 07:23:36'),
(199, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 07:29:41'),
(200, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-20 07:29:52'),
(201, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-20 15:35:40'),
(202, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-20 15:43:06'),
(203, 23, 'PASSWORD_RESET_REQUESTED', NULL, NULL, '::1', '2026-06-21 09:50:16'),
(204, 23, 'PASSWORD_RESET_REQUESTED', NULL, NULL, '::1', '2026-06-21 09:50:20'),
(205, 23, 'PASSWORD_RESET', NULL, NULL, '::1', '2026-06-21 09:51:25'),
(206, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-21 09:51:42'),
(207, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-21 10:29:35'),
(208, 22, 'PASSWORD_RESET_REQUESTED', NULL, NULL, '::1', '2026-06-21 14:48:26'),
(209, 22, 'PASSWORD_RESET_REQUESTED', NULL, NULL, '::1', '2026-06-21 14:48:40'),
(210, 22, 'PASSWORD_RESET_REQUESTED', NULL, NULL, '::1', '2026-06-21 14:48:52'),
(211, 4, 'LOGIN', NULL, NULL, '::1', '2026-06-21 14:50:45');

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
  `city` varchar(100) DEFAULT NULL,
  `current_address` varchar(255) DEFAULT NULL,
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

INSERT INTO `cases` (`id`, `case_number`, `ofw_id`, `agency_id`, `type`, `status`, `description`, `location_abroad`, `city`, `current_address`, `employer_name`, `date_of_departure`, `emergency_contact_name`, `emergency_contact_number`, `created_at`, `updated_at`) VALUES
(1, 'ARMAS-2026-0001', 14, 10, 'Legal Assistance', 'pending', 'N/A', '2313 SAUDI', NULL, NULL, 'ROBIN PANALIGAN', '2026-06-17', 'MARK JOSEPH', '9103028321', '2026-06-07 06:55:00', '2026-06-07 06:55:00'),
(2, 'ARMAS-2026-0002', 14, 10, 'Financial Aid', 'pending', 'NANANANA', '', NULL, NULL, 'REANAN BANTA', '2026-06-07', 'ALBA LAWRENCE', '01928488244', '2026-06-07 07:05:16', '2026-06-07 07:05:16'),
(3, 'ARMAS-2026-0003', 14, 10, 'Emergency Repatriation', 'in_process', 'SADASDDSADSA', '', NULL, NULL, 'MJ', '2026-06-18', 'ALBA LAWRENCE', '921902938', '2026-06-07 07:27:04', '2026-06-07 07:47:04'),
(4, 'ARMAS-2026-0004', 14, 10, 'Psychosocial Support', 'closed', 'ASDAS', 'EUROPE', NULL, NULL, 'MATT', '2026-06-09', 'ASDADAS', '23423424', '2026-06-07 07:28:55', '2026-06-07 07:43:39'),
(5, 'ARMAS-2026-0005', 13, 9, 'Emergency Repatriation', 'pending', 'INAANO ANO GINAGANON GANON KAWAWA NAMAN AKO', 'RIYADH, MALAYSIA', NULL, '', 'ABC COMPANY', '2026-06-24', 'LAWRENCE ALBA', '09123456789', '2026-06-07 16:36:11', '2026-06-07 16:36:11'),
(6, 'ARMAS-2026-0006', 11, 13, 'Emergency Repatriation', 'pending', 'LKHIHHKHLKNLK', 'CITY HALL, UNITED ARAB EMIRATES', 'CITY HALL', 'DITO LANG', 'AKO LANG', '2026-06-08', 'SIYA LANG', '09123456789', '2026-06-08 03:44:55', '2026-06-08 03:44:55');

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
(7, 23, 'Your case ARMAS-2026-0003 status has been updated to in_process.', 'status_update', '2026-06-07 15:53:10', '2026-06-07 07:47:04'),
(8, 3, 'New repatriation request ARMAS-2026-0005 has been submitted.', 'new_case', NULL, '2026-06-07 16:36:11'),
(9, 7, 'New repatriation request ARMAS-2026-0006 has been submitted.', 'new_case', NULL, '2026-06-08 03:44:55');

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
  `work_category` varchar(100) NOT NULL DEFAULT '',
  `work_type` varchar(150) NOT NULL DEFAULT '',
  `document_type` varchar(20) NOT NULL DEFAULT '',
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_of_departure` date DEFAULT NULL,
  `end_of_contract` date DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `work_address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `location_updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ofws`
--

INSERT INTO `ofws` (`id`, `user_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `agency_id`, `ofw_type`, `work_category`, `work_type`, `document_type`, `address`, `contact_number`, `created_at`, `date_of_departure`, `end_of_contract`, `country`, `city`, `work_address`, `latitude`, `longitude`, `location_updated_at`) VALUES
(11, 19, 'BANTA', 'JOHN REANAN', 'LEGASPI', '', 13, 'land-based', '', '', '', '780 KAGANDAHAN ST', '09619878435', '2026-06-06 04:00:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 20, 'AQUINO', 'IAN LAURENCE', 'TOMAS', '', 10, 'land-based', '', '', '', '555 PALTOC SAMPALOC MANILA', '09617244155', '2026-06-06 04:51:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 22, 'PANALIGAN', 'ROBIN', 'E.', '', 9, 'sea-based', '', '', '', '661 MARY ANN ST. GAGALANGIN TONDO MANILA', '', '2026-06-07 05:04:12', NULL, NULL, NULL, NULL, NULL, 14.32072391, 120.97126205, '2026-06-20 08:35:20'),
(14, 23, 'ALBA', 'LAWRENCE', 'MADEJA', '', 10, 'land-based', 'Retail & Sales', 'Salesperson / Sales Associate', '', '', '231332766787', '2026-06-07 05:04:31', NULL, NULL, NULL, NULL, NULL, 14.60352866, 121.00478583, '2026-06-21 12:29:23');

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
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used`, `created_at`) VALUES
(1, 23, '31ba3b51087d496f80a3f59d0ca3e11333b6c4d53891569df838162f31661f37', '2026-06-21 12:20:12', 1, '2026-06-21 09:50:12'),
(2, 23, '4b79a14baddb68b486b8f3cf98701783f134db84d5780e1f73aa7a6459792fd8', '2026-06-21 12:20:16', 0, '2026-06-21 09:50:16'),
(3, 22, 'e92f2e82171414329e07eb50c4cec860fc0bcac35615b69e7fb1bd737c329ae5', '2026-06-21 17:18:20', 0, '2026-06-21 14:48:20'),
(4, 22, 'fee0d7253b574858014135dffa50d21f312d42a1a8fdb92391f467dd91691ba8', '2026-06-21 17:18:35', 0, '2026-06-21 14:48:35'),
(5, 22, '5cf350d7bd9a2ebc46a2b3bb25c00f5b435f9153aa2b1884051e07f11e0a2701', '2026-06-21 17:18:47', 0, '2026-06-21 14:48:47');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `role`, `status`, `login_attempts`, `locked_until`, `created_at`, `updated_at`, `last_login`, `last_login_ip`) VALUES
(1, 'admin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-20 15:35:40', '2026-06-20 17:35:40', '::1'),
(2, 'agency1@filworkers.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 06:58:52', NULL, NULL),
(3, 'agency2@globalfilipino.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42', NULL, NULL),
(4, 'agency3@ofwbridge.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-21 14:50:45', '2026-06-21 16:50:45', '::1'),
(5, 'agency4@bagongbayani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42', NULL, NULL),
(6, 'agency5@maharlika.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-20 04:11:17', NULL, NULL),
(7, 'agency6@sunrise.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-07 03:27:42', NULL, NULL),
(8, 'agency7@kabayan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agency', 'active', 0, NULL, '2026-06-06 03:19:48', '2026-06-20 04:11:19', NULL, NULL),
(19, 'reananbanta@gmail.com', '$2y$10$y9snz8g93RPLM2s/qaQZ7.LVFTwpaygiRiA5cUQEvW0SBosoJmsz.', 'ofw', 'active', 0, NULL, '2026-06-06 04:00:04', '2026-06-08 03:43:52', NULL, NULL),
(20, 'ianaquino0208@gmail.com', '$2y$10$SVKYD6qHCFcH.ABizUr2uOLPBeOR6aqIm4bsDM9VbUtrOEVKcngjW', 'ofw', 'active', 0, NULL, '2026-06-06 04:51:44', '2026-06-06 04:52:34', NULL, NULL),
(21, 'superadmin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'active', 0, NULL, '2026-06-07 04:01:25', '2026-06-20 07:21:48', '2026-06-20 09:21:48', '::1'),
(22, 'panaliganrobin48@gmail.com', '$2y$10$ZR9dz7ROeIRxhnKYjQJLxu1vFiIaCvZ5utb6aesePy2gRUIfnJpYS', 'ofw', 'active', 0, NULL, '2026-06-07 05:04:12', '2026-06-20 06:35:11', '2026-06-20 08:35:11', '::1'),
(23, 'opaparmas@gmail.com', '$2y$10$ohaUpGKcIKIOA7AeM27MbuKRM5QiOwDbMrL5GYfCt9yuC4I0JZZUm', 'ofw', 'active', 0, NULL, '2026-06-07 05:04:31', '2026-06-21 09:51:42', '2026-06-21 11:51:42', '::1');

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
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `token_hash` (`token_hash`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `case_updates`
--
ALTER TABLE `case_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

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

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
