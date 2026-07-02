-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2026 at 03:57 AM
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
(1, 22, 'SafeJourney Overseas Agency', 'DMW-LIC-2026-458921', '10TH FLOOR, GLOBALLINK TOWER, AYALA AVENUE EXTENSION, MAKATI CITY, METRO MANILA, PHILIPPINES 1200', '09100596236', 'active', 21, '2026-06-24 10:59:30');

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
(9, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-24 10:55:05'),
(10, 21, 'CREATE_AGENCY', 'agencies', 22, '::1', '2026-06-24 10:59:30'),
(11, 21, 'CREATE_OFW', 'ofws', 23, '::1', '2026-06-24 11:00:54'),
(12, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 11:01:17'),
(13, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-24 11:01:32'),
(14, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 11:02:18'),
(15, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 11:02:28'),
(16, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-24 15:41:35'),
(17, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 15:41:42'),
(18, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 15:42:06'),
(19, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 15:52:19'),
(20, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 15:52:34'),
(21, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 15:57:57'),
(22, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 15:58:08'),
(23, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 15:58:28'),
(24, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 16:00:51'),
(25, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 16:56:39'),
(26, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-24 17:06:12'),
(27, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-24 17:13:40'),
(28, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-25 01:42:05'),
(29, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-25 02:09:25'),
(30, 1, 'LOGIN', NULL, NULL, '::1', '2026-06-25 02:09:31'),
(31, 1, 'LOGOUT', NULL, NULL, '::1', '2026-06-25 02:11:54'),
(32, 21, 'LOGIN', NULL, NULL, '::1', '2026-06-25 02:12:01'),
(33, 21, 'LOGOUT', NULL, NULL, '::1', '2026-06-25 02:13:00'),
(34, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-26 14:58:36'),
(35, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-27 01:43:21'),
(36, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-27 01:43:34'),
(37, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-27 01:46:40'),
(38, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-27 01:46:53'),
(39, 22, 'LOGOUT', NULL, NULL, '::1', '2026-06-27 01:49:26'),
(40, 23, 'LOGIN', NULL, NULL, '::1', '2026-06-27 01:49:34'),
(41, 23, 'LOGOUT', NULL, NULL, '::1', '2026-06-27 01:53:40'),
(42, 22, 'LOGIN', NULL, NULL, '::1', '2026-06-27 01:53:53');

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
  `end_of_contract` date DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_number`, `ofw_id`, `agency_id`, `type`, `status`, `description`, `location_abroad`, `city`, `current_address`, `employer_name`, `end_of_contract`, `emergency_contact_name`, `emergency_contact_number`, `created_at`, `updated_at`) VALUES
(1, 'ARMAS-415A1-2026', 1, 1, 'Unpaid Wages', 'closed', 'jsandonsadnj', 'China', 'Ancheng', NULL, NULL, NULL, NULL, NULL, '2026-06-27 01:46:28', '2026-06-27 01:48:02'),
(2, 'ARMAS-0C9D6-2026', 1, 1, 'Illegal Recruitment', 'closed', 'asssasd', 'Singapore', 'Bedok New Town', NULL, NULL, NULL, NULL, NULL, '2026-06-27 01:53:36', '2026-06-27 01:55:34');

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

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `ofw_id` int(11) DEFAULT NULL,
  `read_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `ofw_id`, `read_at`, `created_at`) VALUES
(1, 22, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603540360425,121.00481682195', 'sos_emergency', 1, '2026-06-24 19:25:40', '2026-06-24 11:02:15'),
(2, 1, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603540360425,121.00481682195', 'sos_emergency', 1, NULL, '2026-06-24 11:02:15'),
(3, 21, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603540360425,121.00481682195', 'sos_emergency', 1, NULL, '2026-06-24 11:02:15'),
(4, 22, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603543077175,121.00483680886', 'sos_emergency', 1, '2026-06-24 23:42:11', '2026-06-24 15:41:39'),
(5, 1, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603543077175,121.00483680886', 'sos_emergency', 1, NULL, '2026-06-24 15:41:39'),
(6, 21, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603543077175,121.00483680886', 'sos_emergency', 1, NULL, '2026-06-24 15:41:39'),
(7, 22, 'New case report ARMAS-415A1-2026 has been submitted by LAWRENCE ALBA.', 'new_case', NULL, '2026-06-27 09:46:58', '2026-06-27 01:46:28'),
(8, 22, 'New case report ARMAS-0C9D6-2026 has been submitted by LAWRENCE ALBA.', 'new_case', NULL, '2026-06-27 09:53:58', '2026-06-27 01:53:36');

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
  `end_of_contract` date DEFAULT NULL,
  `date_of_arrival` date DEFAULT NULL,
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

INSERT INTO `ofws` (`id`, `user_id`, `last_name`, `first_name`, `middle_name`, `suffix`, `agency_id`, `ofw_type`, `work_category`, `work_type`, `document_type`, `address`, `contact_number`, `created_at`, `end_of_contract`, `date_of_arrival`, `country`, `city`, `work_address`, `latitude`, `longitude`, `location_updated_at`) VALUES
(1, 23, 'ALBA', 'LAWRENCE', 'MADEJA', '', 1, 'land-based', 'Security & Safety', 'Security Guard', '', '10TH FLOOR, GLOBALLINK TOWER, AYALA AVENUE EXTENSION, MAKATI CITY, METRO MANILA, PHILIPPINES 1200', '09100596235', '2026-06-24 11:00:54', NULL, NULL, NULL, NULL, NULL, 14.60353953, 121.00481915, '2026-06-27 03:49:37');

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

-- --------------------------------------------------------

--
-- Table structure for table `sos_alerts`
--

CREATE TABLE `sos_alerts` (
  `id` int(11) NOT NULL,
  `ofw_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('active','acknowledged','resolved') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `acknowledged_at` datetime DEFAULT NULL,
  `acknowledged_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sos_alerts`
--

INSERT INTO `sos_alerts` (`id`, `ofw_id`, `user_id`, `latitude`, `longitude`, `message`, `status`, `created_at`, `acknowledged_at`, `acknowledged_by`) VALUES
(1, 1, 23, 14.60354036, 121.00481682, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603540360425,121.00481682195', 'active', '2026-06-24 11:02:15', NULL, NULL),
(2, 1, 23, 14.60354308, 121.00483681, '???? SOS EMERGENCY from OFW LAWRENCE ALBA! Location: https://maps.google.com/?q=14.603543077175,121.00483680886', 'active', '2026-06-24 15:41:39', NULL, NULL);

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
(1, 'admin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 0, NULL, '2026-06-24 10:53:34', '2026-06-25 02:09:31', '2026-06-25 04:09:31', '::1'),
(21, 'superadmin@armas.gov.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', 'active', 0, NULL, '2026-06-24 10:53:34', '2026-06-25 02:12:01', '2026-06-25 04:12:01', '::1'),
(22, 'ianaquino@SJOA.com', '$2y$10$fJKURBnHWbZJb.AbxVxiFu.JfDGR9amzaIL5YZXmIZcqxd.sWYRNe', 'agency', 'active', 0, NULL, '2026-06-24 10:59:30', '2026-06-27 01:53:53', '2026-06-27 03:53:53', '::1'),
(23, 'opaparmas@gmail.com', '$2y$10$P5EZg.rON7BDY9k/kluzguEFlYID2YsiTSmFEBqvkPTkJ.b4gataG', 'ofw', 'active', 0, NULL, '2026-06-24 11:00:54', '2026-06-27 01:49:34', '2026-06-27 03:49:34', '::1');

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
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ofw_id` (`ofw_id`);

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
-- Indexes for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ofw_id` (`ofw_id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `case_updates`
--
ALTER TABLE `case_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ofws`
--
ALTER TABLE `ofws`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD CONSTRAINT `sos_alerts_ibfk_1` FOREIGN KEY (`ofw_id`) REFERENCES `ofws` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sos_alerts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
