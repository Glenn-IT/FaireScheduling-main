-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 26, 2025 at 03:20 AM
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
-- Database: `dbschedule`
--

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('info','success','warning','error') DEFAULT 'info',
  `title` varchar(120) NOT NULL,
  `message` text DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 4, 'info', 'Booking received', 'We received your booking for September 26, 2025, 10:59 AM - 12:59 PM. Status: Pending review.', '../schedule/schedule.php', 1, '2025-09-26 07:59:58'),
(2, 4, 'success', 'Booking approved', 'Good news! Your booking for August 15, 2025 (3:27 PM–5:27 PM) has been approved.', '../schedule/schedule.php', 1, '2025-09-26 08:54:52'),
(3, 4, 'info', 'Booking received', 'We received your booking for September 27, 2025, 9:18 AM - 11:18 AM. Status: Pending review.', '../schedule/schedule.php', 1, '2025-09-26 09:18:57');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `ID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `serviceID` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `other_contact_person` varchar(120) DEFAULT NULL,
  `contact_phone` varchar(30) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`ID`, `userID`, `serviceID`, `date`, `time_start`, `time_end`, `other_contact_person`, `contact_phone`, `notes`, `date_created`, `status`) VALUES
(2, 4, 1, '2025-08-15', '15:27:00', '17:27:00', NULL, NULL, NULL, '2025-08-09 16:27:42', 'Approved'),
(3, 4, NULL, '2025-09-26', '10:59:00', '12:59:00', 'Gege Akutami', '09123123123', 'gege', '2025-09-26 07:59:58', 'Cancelled'),
(4, 4, 1, '2025-09-27', '09:18:00', '11:18:00', 'Gege Akutami', '09123123123', 'geeg', '2025-09-26 09:18:57', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `ID` int(11) NOT NULL,
  `service_name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`ID`, `service_name`, `description`, `date_created`) VALUES
(1, 'Wedding', 'gege', '2025-08-09 19:39:12'),
(3, 'Mass', 'Gege', '2025-08-09 20:42:25');

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) NOT NULL,
  `birthday` date NOT NULL,
  `age` int(3) NOT NULL,
  `mobilenumber` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL,
  `datecreated` datetime NOT NULL,
  `user_role` varchar(50) NOT NULL,
  `code` int(11) NOT NULL,
  `user_active` int(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `lastname`, `firstname`, `middlename`, `birthday`, `age`, `mobilenumber`, `email`, `password`, `datecreated`, `user_role`, `code`, `user_active`) VALUES
(3, 'ege', 'john michael', 'asd', '2003-07-26', 21, '09123123123', 'diewithasmile@gmail.com', '$2y$10$wHkzS1Wig6IccsX.dDyO0O69CJ/uiiFfKFFm.pd2Rlt4Lubp7EKfm', '2025-06-06 00:00:00', 'Admin', 0, 1),
(4, 'dwd', 'naysu', 'g', '2003-05-07', 22, '09234534521', 'dolero2.ai@gmail.com', '$2y$10$u03NFv3F44EF88jBFpCD4O8uSCPCfGMwoRzlrpjkdsPrHbern7Qy2', '2025-08-09 00:00:00', 'User', 0, 1),
(5, 'asd', 'sd', 'sd', '2019-06-08', 6, '09123123123', 'gegegege@gmail.com', '$2y$10$zPaURSMuQlZwRLRgZLnJHudQe7VH6Atzftt2iqk7sOfIw.vWQNvVe', '2025-08-09 00:00:00', 'Admin', 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_read_created` (`user_id`,`is_read`,`created_at`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_sched_date_service` (`date`,`serviceID`,`time_start`,`time_end`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
