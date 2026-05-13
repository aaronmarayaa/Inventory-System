-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 13, 2026 at 11:28 AM
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
-- Database: `chocodb`
--

-- --------------------------------------------------------

--
-- Table structure for table `chocolate_inventory`
--

CREATE TABLE `chocolate_inventory` (
  `id` int(11) NOT NULL,
  `chocolate_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('ACTIVE','PENDING','ARCHIVED') DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chocolate_inventory`
--

INSERT INTO `chocolate_inventory` (`id`, `chocolate_id`, `quantity`, `status`, `created_by`, `approved_by`, `created_at`) VALUES
(39, 1, 50, 'ACTIVE', 2, 1, '2026-02-10 01:15:00'),
(40, 2, 40, 'ACTIVE', 3, 1, '2026-02-10 01:20:00'),
(41, 3, 35, 'ACTIVE', 2, 1, '2026-02-10 01:25:00'),
(42, 4, 60, 'ACTIVE', 4, 2, '2026-02-10 02:00:00'),
(43, 5, 45, 'ACTIVE', 5, 2, '2026-02-10 02:05:00'),
(44, 6, 30, 'PENDING', 4, NULL, '2026-02-11 00:10:00'),
(45, 7, 25, 'PENDING', 5, NULL, '2026-02-11 00:15:00'),
(46, 8, 20, 'ACTIVE', 4, 3, '2026-02-11 01:00:00'),
(47, 9, 55, 'ACTIVE', 3, 2, '2026-02-12 03:30:00'),
(48, 10, 70, 'ACTIVE', 2, 1, '2026-02-12 03:45:00'),
(49, 11, 65, 'ACTIVE', 3, 1, '2026-02-12 04:00:00'),
(50, 12, 30, 'PENDING', 5, NULL, '2026-02-12 23:50:00'),
(51, 13, 90, 'ACTIVE', 2, 1, '2026-02-13 00:00:00'),
(52, 14, 75, 'ACTIVE', 3, 2, '2026-02-13 00:10:00'),
(53, 8, 12, 'ACTIVE', 5, NULL, '2026-02-19 13:18:00'),
(54, 14, 1, 'ACTIVE', 5, NULL, '2026-05-10 10:59:26'),
(55, 1, 1, 'ACTIVE', 2, NULL, '2026-05-13 06:35:47'),
(56, 1, 1, 'PENDING', 5, NULL, '2026-05-13 06:39:01');

-- --------------------------------------------------------

--
-- Table structure for table `chocolate_items`
--

CREATE TABLE `chocolate_items` (
  `id` int(11) NOT NULL,
  `chocolate_name` varchar(250) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chocolate_items`
--

INSERT INTO `chocolate_items` (`id`, `chocolate_name`, `image_path`) VALUES
(1, 'Cadbury Fruit & Nuts', '../assets/img/cadbury_fruit_nuts.jpg'),
(2, 'Dove Dark Chocolate', '../assets/img/dove_dark_chocolate.jpg'),
(3, 'Dove Milk Chocolate', '../assets/img/dove_milk_chocolate.jpg'),
(4, 'Ferrero Raffaello', '../assets/img/ferrero_raffaello.jpg'),
(5, 'Ferrero Rocher', '../assets/img/ferrero_rocher.jpg'),
(6, 'Godiva Dark Chocolate Bar', '../assets/img/godiva_dark_chocolate_bar.jpg'),
(7, 'KitKat Dark', '../assets/img/kitkat_dark.jpg'),
(8, 'KitKat White', '../assets/img/kitkat_white.jpg'),
(9, 'Milka Oreo', '../assets/img/milka_oreo.jpg'),
(10, 'Nestlé Milkybar', '../assets/img/nestle_milkybar.jpg'),
(11, 'Reese\'s Peanut Butter Cups', '../assets/img/reeses_peanut_butter_cups.jpg'),
(12, 'Reese\'s Pieces', '../assets/img/reeses_pieces.jpg'),
(13, 'Ritter Sport Hazelnut', '../assets/img/ritter_sport_hazelnut.jpg'),
(14, 'Ritter Sport Milk Chocolate', '../assets/img/ritter_sport_milk_chocolate.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('REGULAR','ADMIN','SUPER_ADMIN') NOT NULL,
  `status` enum('ACTIVE','ARCHIVED') DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'System', 'Owner', 'superadmin@example.com', '$2y$10$2BGKbJMWu4EdRFgDrWS/XOACQh96GtFvMNyFUpaywVMrCBwP66lv2', 'SUPER_ADMIN', 'ACTIVE', '2026-01-19 03:40:40'),
(2, 'Alice', 'Reyes', 'admin1@example.com', '$2y$10$dTSBSCoNpm.dtCIB6Psdh.MPRW2N8gXhbIjBzs/r.vVU8coipsUla', 'ADMIN', 'ACTIVE', '2026-01-19 03:40:40'),
(3, 'Mark', 'Santos', 'admin2@example.com', '$2y$10$2BGKbJMWu4EdRFgDrWS/XOACQh96GtFvMNyFUpaywVMrCBwP66lv2', 'ADMIN', 'ACTIVE', '2026-01-19 03:40:40'),
(4, 'John', 'Dela Cruz', 'user1@example.com', '$2y$10$2BGKbJMWu4EdRFgDrWS/XOACQh96GtFvMNyFUpaywVMrCBwP66lv2', 'REGULAR', 'ACTIVE', '2026-01-19 03:40:40'),
(5, 'Maria', 'Lopez', 'user2@example.com', '$2y$10$2BGKbJMWu4EdRFgDrWS/XOACQh96GtFvMNyFUpaywVMrCBwP66lv2', 'REGULAR', 'ACTIVE', '2026-01-19 03:40:40'),
(6, 'Garlic', 'garlic', 'garlic@gmail.com', '$2y$10$2BGKbJMWu4EdRFgDrWS/XOACQh96GtFvMNyFUpaywVMrCBwP66lv2', 'REGULAR', 'ACTIVE', '2026-05-13 07:52:32'),
(7, 'Onion', 'onion', 'onion@gmail.com', '$2y$10$l0asDVhfma8NscQiH8D0Ou3Q.8ED1tijCj5CCq0cqG4R0RfP1CZ/e', 'REGULAR', 'ACTIVE', '2026-05-13 09:26:31');

-- --------------------------------------------------------

--
-- Table structure for table `user_session`
--

CREATE TABLE `user_session` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `selector` varchar(12) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chocolate_inventory`
--
ALTER TABLE `chocolate_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `fk_inventory_chocolate` (`chocolate_id`);

--
-- Indexes for table `chocolate_items`
--
ALTER TABLE `chocolate_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_session`
--
ALTER TABLE `user_session`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_selector` (`selector`),
  ADD KEY `user_session_user_id_index` (`user_id`),
  ADD KEY `user_session_expires_index` (`expires`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chocolate_inventory`
--
ALTER TABLE `chocolate_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `chocolate_items`
--
ALTER TABLE `chocolate_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_session`
--
ALTER TABLE `user_session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chocolate_inventory`
--
ALTER TABLE `chocolate_inventory`
  ADD CONSTRAINT `chocolate_inventory_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chocolate_inventory_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_inventory_chocolate` FOREIGN KEY (`chocolate_id`) REFERENCES `chocolate_items` (`id`);

--
-- Constraints for table `user_session`
--
ALTER TABLE `user_session`
  ADD CONSTRAINT `user_session_user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
