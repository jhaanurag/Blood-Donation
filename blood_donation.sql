-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 17, 2025 at 04:54 AM
-- Server version: 8.0.41-0ubuntu0.24.04.1
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `blood_donation`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `appointment_date` date DEFAULT NULL,
  `status` enum('pending','approved','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `appointment_date`, `status`, `created_at`) VALUES
(2, 2, '2025-04-23', 'pending', '2025-04-12 06:16:34'),
(3, 1, '2025-04-28', 'completed', '2025-04-12 08:32:28'),
(4, 4, '2025-04-23', 'completed', '2025-04-12 10:50:56'),
(5, 3, '2025-04-15', 'pending', '2025-04-13 17:34:48'),
(6, 5, '2025-04-15', 'completed', '2025-04-13 17:38:02'),
(7, 6, '2025-05-15', 'completed', '2025-04-16 08:53:09');

-- --------------------------------------------------------

--
-- Table structure for table `blood_camps`
--

CREATE TABLE `blood_camps` (
  `id` int NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `blood_camps`
--

INSERT INTO `blood_camps` (`id`, `title`, `location`, `city`, `state`, `date`, `description`, `created_at`) VALUES
(1, 'Community Blood Drive', 'Town Hall Square', 'Metropolis', 'NY', '2025-05-15', 'Annual community drive. All welcome!', '2025-04-12 08:24:04'),
(2, 'Community Blood Drive', 'LPU', 'Jalandhar', 'Punjab', '2025-05-15', 'Annual community drive. All welcome!', '2025-04-12 08:25:09'),
(3, 'LPU Community Blood Drive', 'LPU', 'Delhi', 'Delhi', '2025-04-15', 'Annual community drive. All welcome!', '2025-04-12 11:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int NOT NULL,
  `requester_name` varchar(100) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `message` text,
  `matched_donor_id` int DEFAULT NULL,
  `status` enum('pending','contacted','completed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `requester_name`, `blood_group`, `city`, `state`, `message`, `matched_donor_id`, `status`, `created_at`) VALUES
(3, 'Vikash', 'B-', 'Delhi', 'Delhi', 'Hi Anurag!', 1, 'contacted', '2025-04-12 05:26:43'),
(4, 'Demo', 'B-', 'Delhi', 'Delhi', 'Hi!', 1, 'contacted', '2025-04-12 10:26:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `age` int DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `last_donation_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `age`, `blood_group`, `city`, `state`, `last_donation_date`, `created_at`) VALUES
(1, 'Anurag Jha', 'jha.anurag2017@outlook.com', '$2y$10$fxF/pRwJo3jZju/8F.qG8etyAfBB/1C1SRt1QkdMNJGWFuwOmdBV2', '9899276453', 20, 'B-', 'Delhi', 'Delhi', '2025-04-28', '2025-04-12 04:56:53'),
(2, 'demo1', 'demo@gmail.com', '$2y$10$81ZbyoC.jDkt9VDvJ/UIWO/6znziNHKeXrbQIL9OBhMNf1sVjzHZC', '1234567890', 20, 'B-', 'Delhi', 'Delhi', NULL, '2025-04-12 06:15:23'),
(3, 'demo2', 'demo2@gmail.com', '$2y$10$K5J9czxrtZoXF94legKrI.kUaBd7wQWprgMnL9t8AGZsWMX9OOcl.', '2384939203', 20, 'B-', 'MS', 'US', NULL, '2025-04-12 07:23:07'),
(4, 'demo3', 'fulfutureful@gmail.com', '$2y$10$VjEWWusKjvB5LgHNYajJmeY3j2400.BCle3il6pEgD4OcxPFhh76i', '8937928392', 20, 'B-', 'Delhi', 'Delhi', '2025-04-23', '2025-04-12 09:40:16'),
(5, 'demo4', 'demo4@gmail.com', '$2y$10$dop3OPoFQlk.vdeo1g6EvOYJ5uzklCe9NYpQhNS/69iJJJqh9q8nu', '1986387289', 20, 'A-', 'Delhi', 'Delhi', '2025-04-15', '2025-04-13 17:37:45'),
(6, 'aarav gandhi', 'aaravdb10@gmail.com', '$2y$10$d/uEDlXXpnzahE31PZyTweP7wdJI161O1O.RUFnLLtdt8BFJjHMJy', '7754978800', 20, 'B+', 'lucknow', 'up', '2025-05-15', '2025-04-16 08:50:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `blood_camps`
--
ALTER TABLE `blood_camps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `matched_donor_id` (`matched_donor_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `blood_camps`
--
ALTER TABLE `blood_camps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`matched_donor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
