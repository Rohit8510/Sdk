-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 24, 2026 at 05:29 AM
-- Server version: 10.11.17-MariaDB-cll-lve
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `Your_Db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activated_packages`
--

CREATE TABLE `activated_packages` (
  `id` int(11) NOT NULL,
  `license_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `device_id` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `usage_count` int(11) DEFAULT 1,
  `last_used` timestamp NULL DEFAULT current_timestamp(),
  `is_allowed` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `alert_history`
--

CREATE TABLE `alert_history` (
  `id` int(11) NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `sent_by` varchar(100) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `alert_history`
--

INSERT INTO `alert_history` (`id`, `alert_type`, `message`, `sent_by`, `sent_at`) VALUES
(12, 'danger', 'Hi my jaan', 'RIYAZOWNER', '2026-02-16 12:02:22');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `app_name` varchar(255) DEFAULT NULL,
  `device_model` varchar(255) DEFAULT NULL,
  `license_key` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('connected','disconnected','blocked') DEFAULT 'disconnected',
  `last_seen` timestamp NULL DEFAULT NULL,
  `last_broadcast_version` int(11) DEFAULT NULL,
  `connected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_id`, `package_name`, `app_name`, `device_model`, `license_key`, `ip_address`, `status`, `last_seen`, `last_broadcast_version`, `connected_at`, `created_at`) VALUES
(65, 'cde3f86cdc66325b', 'com.riyaz', 'Riyaz App', 'Pixel 6 Pro', 'VBOX-ACE4AED6-754', '152.57.146.251', 'disconnected', '2026-02-16 11:52:39', NULL, '2026-02-16 11:49:29', '2026-02-16 11:49:29'),
(67, '695124015772be85', 'com.riyaz', 'Riyaz App', 'Galaxy S23', 'VBOX-ACE4AED6-754', '106.77.164.163', 'connected', '2026-02-16 12:08:59', NULL, '2026-02-16 11:51:31', '2026-02-16 11:51:31'),
(73, 'f59c1eaf9caa89c1', 'com.mk.server', 'MK Server', 'Redmi Note 12', 'RIYAZ-11F76034-598', '157.50.173.75', 'connected', '2026-02-16 12:12:39', NULL, '2026-02-16 12:07:59', '2026-02-16 12:07:59'),
(77, '536169c14ccccb78', 'com.exodia', 'Exodia Mod', 'OnePlus 11', 'RIYAZ-AFC2E5E8-215', '106.77.156.46', 'connected', '2026-02-16 12:31:02', NULL, '2026-02-16 12:10:56', '2026-02-16 12:10:56');

-- --------------------------------------------------------

--
-- Table structure for table `licenses`
--

CREATE TABLE `licenses` (
  `id` int(11) NOT NULL,
  `license_key` varchar(255) NOT NULL,
  `expiry_date` date NOT NULL,
  `daemon` tinyint(1) DEFAULT 1,
  `hide_root` tinyint(1) DEFAULT 1,
  `hide_xposed` tinyint(1) DEFAULT 1,
  `toggle_expiry` tinyint(1) DEFAULT 1,
  `force_logout` tinyint(1) DEFAULT 0,
  `controller_interval` int(11) DEFAULT 30,
  `toast_message` varchar(255) DEFAULT '',
  `generated_by` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `package_name` varchar(255) DEFAULT NULL,
  `activated_packages` text DEFAULT NULL,
  `usage_count` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `blocked_by_server` tinyint(1) DEFAULT 0,
  `last_blocked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `licenses`
--

INSERT INTO `licenses` (`id`, `license_key`, `expiry_date`, `daemon`, `hide_root`, `hide_xposed`, `toggle_expiry`, `force_logout`, `controller_interval`, `toast_message`, `generated_by`, `status`, `package_name`, `activated_packages`, `usage_count`, `created_at`, `blocked_by_server`, `last_blocked_at`) VALUES
(48, 'REN-63884E71-459', '2026-07-01', 1, 1, 1, 1, 0, 30, '', NULL, 1, 'com.renn.matrix', NULL, 0, '2026-06-24 08:53:26', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `referral_codes`
--

CREATE TABLE `referral_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `assigned_to` varchar(50) DEFAULT 'user',
  `created_by` int(11) DEFAULT NULL,
  `used_by` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `server_settings`
--

CREATE TABLE `server_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `server_settings`
--

INSERT INTO `server_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(28, 'server_status', 'online', '2026-02-16 12:03:32'),
(29, 'maintenance_message', 'Back soon.', '2026-06-23 07:36:43'),
(30, 'admin_chat_ids', '8127154833', '2026-02-16 18:48:00'),
(31, 'server_mode', 'online', '2026-06-23 06:13:32'),
(32, 'owner_contact', '@RennZohh', '2026-06-23 07:36:43'),
(33, 'server_notification_json', '{\"enabled\":1,\"title\":\"Welcome\",\"message\":\"RennZohh SDK Panel is live!\",\"iconType\":\"event\"}', '2026-06-23 07:36:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','user','owner') DEFAULT 'user',
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_online` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `status`, `created_at`, `is_online`) VALUES
(5, 'RennZohh', '$2y$12$51rH2x7oAfyMgqG4XyB/Ue8UEPv2zVFmJAv/XR26.k8K59yP/rvNO', 'owner@rennzohh.com', 'owner', 1, '2026-06-23 07:36:43', 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_active_devices`
-- (See below for the actual view)
--
CREATE TABLE `v_active_devices` (
`package_name` varchar(255)
,`app_name` varchar(255)
,`device_id` varchar(255)
,`device_model` varchar(255)
,`ip_address` varchar(45)
,`last_seen` timestamp
,`license_key` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_blocked_licenses`
-- (See below for the actual view)
--
CREATE TABLE `v_blocked_licenses` (
`blocked_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_connected_devices`
-- (See below for the actual view)
--
CREATE TABLE `v_connected_devices` (
`package_name` varchar(255)
,`app_name` varchar(255)
,`connected_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_server_stats`
-- (See below for the actual view)
--
CREATE TABLE `v_server_stats` (
`active_licenses` bigint(21)
,`total_licenses` bigint(21)
,`connected_devices` bigint(21)
,`total_users` bigint(21)
,`server_status` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_total_licenses`
-- (See below for the actual view)
--
CREATE TABLE `v_total_licenses` (
`total_licenses` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_total_users`
-- (See below for the actual view)
--
CREATE TABLE `v_total_users` (
`total_users` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_unused_licenses`
-- (See below for the actual view)
--
CREATE TABLE `v_unused_licenses` (
`unused_licenses` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_used_licenses`
-- (See below for the actual view)
--
CREATE TABLE `v_used_licenses` (
`used_licenses` bigint(21)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activated_packages`
--
ALTER TABLE `activated_packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `license_id` (`license_id`),
  ADD KEY `package_name` (`package_name`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `idx_license_package` (`license_id`,`package_name`);

--
-- Indexes for table `alert_history`
--
ALTER TABLE `alert_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`),
  ADD KEY `package_name` (`package_name`),
  ADD KEY `license_key` (`license_key`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_last_seen` (`last_seen`);

--
-- Indexes for table `licenses`
--
ALTER TABLE `licenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `license_key` (`license_key`),
  ADD KEY `idx_status_package` (`status`,`package_name`),
  ADD KEY `idx_license_package` (`license_key`,`package_name`);

--
-- Indexes for table `referral_codes`
--
ALTER TABLE `referral_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `used_by` (`used_by`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `server_settings`
--
ALTER TABLE `server_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activated_packages`
--
ALTER TABLE `activated_packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `alert_history`
--
ALTER TABLE `alert_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `licenses`
--
ALTER TABLE `licenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `referral_codes`
--
ALTER TABLE `referral_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `server_settings`
--
ALTER TABLE `server_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------

--
-- Structure for view `v_active_devices`
--
DROP TABLE IF EXISTS `v_active_devices`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_active_devices`  AS SELECT `d`.`package_name` AS `package_name`, `d`.`app_name` AS `app_name`, `d`.`device_id` AS `device_id`, `d`.`device_model` AS `device_model`, `d`.`ip_address` AS `ip_address`, `d`.`last_seen` AS `last_seen`, `l`.`license_key` AS `license_key` FROM (`devices` `d` left join `licenses` `l` on(`l`.`package_name` = `d`.`package_name`)) WHERE `d`.`status` = 'connected' ORDER BY `d`.`last_seen` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `v_blocked_licenses`
--
DROP TABLE IF EXISTS `v_blocked_licenses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_blocked_licenses`  AS SELECT count(0) AS `blocked_count` FROM `licenses` WHERE `licenses`.`blocked_by_server` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `v_connected_devices`
--
DROP TABLE IF EXISTS `v_connected_devices`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_connected_devices`  AS SELECT `devices`.`package_name` AS `package_name`, `devices`.`app_name` AS `app_name`, count(0) AS `connected_count` FROM `devices` WHERE `devices`.`status` = 'connected' GROUP BY `devices`.`package_name` ;

-- --------------------------------------------------------

--
-- Structure for view `v_server_stats`
--
DROP TABLE IF EXISTS `v_server_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_server_stats`  AS SELECT (select count(0) from `licenses` where `licenses`.`status` = 1) AS `active_licenses`, (select count(0) from `licenses`) AS `total_licenses`, (select count(0) from `devices` where `devices`.`status` = 'connected') AS `connected_devices`, (select count(0) from `users`) AS `total_users`, (select `server_settings`.`setting_value` from `server_settings` where `server_settings`.`setting_key` = 'server_status') AS `server_status` ;

-- --------------------------------------------------------

--
-- Structure for view `v_total_licenses`
--
DROP TABLE IF EXISTS `v_total_licenses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_total_licenses`  AS SELECT count(0) AS `total_licenses` FROM `licenses` ;

-- --------------------------------------------------------

--
-- Structure for view `v_total_users`
--
DROP TABLE IF EXISTS `v_total_users`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_total_users`  AS SELECT count(0) AS `total_users` FROM `users` ;

-- --------------------------------------------------------

--
-- Structure for view `v_unused_licenses`
--
DROP TABLE IF EXISTS `v_unused_licenses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_unused_licenses`  AS SELECT count(0) AS `unused_licenses` FROM (`licenses` `l` left join `activated_packages` `ap` on(`ap`.`license_id` = `l`.`id`)) WHERE `ap`.`id` is null ;

-- --------------------------------------------------------

--
-- Structure for view `v_used_licenses`
--
DROP TABLE IF EXISTS `v_used_licenses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`whmywarv_panel`@`localhost` SQL SECURITY DEFINER VIEW `v_used_licenses`  AS SELECT count(distinct `l`.`id`) AS `used_licenses` FROM (`licenses` `l` join `activated_packages` `ap` on(`ap`.`license_id` = `l`.`id`)) ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activated_packages`
--
ALTER TABLE `activated_packages`
  ADD CONSTRAINT `fk_activated_license` FOREIGN KEY (`license_id`) REFERENCES `licenses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referral_codes`
--
ALTER TABLE `referral_codes`
  ADD CONSTRAINT `fk_referral_created` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
  
