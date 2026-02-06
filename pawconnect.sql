-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 24, 2025 at 07:33 PM
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
-- Database: `pawconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `adoptions`
--

CREATE TABLE `adoptions` (
  `id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `adopter_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` datetime NOT NULL DEFAULT current_timestamp(),
  `adoption_date` date DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoptions`
--

INSERT INTO `adoptions` (`id`, `pet_id`, `adopter_id`, `status`, `requested_at`, `adoption_date`, `updated_at`) VALUES
(1, 12, 10, 'approved', '2025-08-12 23:18:53', '2025-08-12', '2025-08-12 23:23:11'),
(2, 11, 10, 'approved', '2025-08-12 23:18:57', '2025-08-12', '2025-08-12 23:23:08'),
(3, 13, 10, 'pending', '2025-09-28 08:02:45', '2025-09-28', '2025-09-28 08:02:45'),
(4, 18, 10, 'approved', '2025-09-28 08:07:19', '2025-09-28', '2025-09-28 08:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact`
--

INSERT INTO `contact` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Visitor1', 'visit1@mail.com', 'Interested in adopting a pet.', '2025-08-06 23:03:03'),
(2, 'Visitor2', 'visit2@mail.com', 'Need help with the registration process.', '2025-08-06 23:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `message`, `created_at`) VALUES
(1, 10, 'hi', '2025-08-12 22:42:14'),
(3, 10, 'thank you for the pet', '2025-08-12 23:24:16'),
(4, 10, 'hi,i need', '2025-09-28 08:02:54');

-- --------------------------------------------------------

--
-- Table structure for table `pets`
--

CREATE TABLE `pets` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `sale_status` enum('available','sold') DEFAULT 'available',
  `added_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pets`
--

INSERT INTO `pets` (`id`, `name`, `type`, `age`, `description`, `image`, `sale_status`, `added_by`, `created_at`) VALUES
(8, 'Mani', 'Dog', 1, 'Friendly Dog', '1755002344_manu.jpg', 'available', 4, '2025-08-12 12:39:04'),
(9, 'Neni', 'Cat', 1, 'Good cat this cat free not payment', '1755002617_Neni.jpg', 'available', 4, '2025-08-12 12:43:37'),
(10, 'Petter', 'Dog', 2, 'Good soul dog', '1755002894_Petter.jpg', 'available', 7, '2025-08-12 12:48:14'),
(11, 'John', 'Dog', 1, 'This is a good dog', '1755002932_John.jpg', 'sold', 7, '2025-08-12 12:48:52'),
(12, 'Kopal', 'Dog', 1, 'Good and friendly dog', '1755003096_kopal.jpg', '', 8, '2025-08-12 12:51:36'),
(13, 'Mino', 'Dog', 2, 'Cute and free dog', '1759026355_mino.jpg', 'available', 4, '2025-09-28 02:25:55'),
(14, 'Kannu', 'Cat', 1, 'Cute cat', '1759026466_kannu.jpg', 'available', 4, '2025-09-28 02:27:46'),
(15, 'Beu', 'Cat', 1, 'Cute cat', '1759026504_beu.jpg', 'available', 4, '2025-09-28 02:28:24'),
(16, 'Swetty', 'Cat', 1, 'Swetty is cute cat', '1759026560_swetty.jpg', 'available', 4, '2025-09-28 02:29:20'),
(17, 'Juwel', 'Cat', 1, 'Cute dog', '1759026673_juwel.jpg', 'available', 4, '2025-09-28 02:31:13'),
(18, 'Vena', 'Dog', 1, 'Cute dog', '1759026702_vena.jpg', '', 4, '2025-09-28 02:31:42');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `user_type` enum('admin','center','adopter') NOT NULL DEFAULT 'adopter',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `contact`, `location`, `user_type`, `created_at`) VALUES
(2, 'Bob Adopter', 'bob.adopter@example.com', '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', '0987654321', 'California', 'adopter', '2025-08-12 10:16:24'),
(4, 'Center1', 'center1@gmail.com', '$2y$10$uEKcNf7s7PL86gXd15Vzo.HSdtpNBSK.mLAllgd6OP6saxn1D4y0e', '0771212311', 'Kandy main street,kandy', 'center', '2025-08-12 11:13:03'),
(6, 'admin', 'admin@gmail.com', '$2y$10$8TLWk.znVyJ5McAo0SLKy.G1kXREggDHqxkJeca5L8Jo.MqxrIZR.', '0771212354', '', 'admin', '2025-08-12 11:16:16'),
(7, 'Center2', 'center2@gmail.com', '$2y$10$XMEsqQ48bJApM9evizX/4uj3cnyW4hy5Hm1BYjJ8kubMHX//47.5i', '0771212321', 'colombo', 'center', '2025-08-12 12:46:46'),
(8, 'Center3', 'center3@gmail.com', '$2y$10$4ZjLrvSfFnKYwLW0p3TeiOhbTCXuq2X6CI9TY.kntWHKIezPVypq.', '0771212327', 'colombo', 'center', '2025-08-12 12:50:49'),
(9, 'Thaksha', 'thaksha@gmail.com', '$2y$10$l4zuEq4Nkop2859MG27V6.6TIYUoqcsZrAD/JJKIZ4/YSVwfxaski', '0771212327', 'Kandy', 'adopter', '2025-08-12 12:52:40'),
(10, 'Anu', 'anu@gmail.com', '$2y$10$58Tp0cxS6Y1Kxlc2rNrbj.23X3prPhNnkyZSv9iDJSduXsTR9wqlu', '0771212311', 'Kandy', 'adopter', '2025-08-12 16:58:29'),
(11, 'Vasanth', 'vasanth@gmail.com', '$2y$10$vyznl575tDAIUaR/93Ik4uO5TrPTrdCD4w1XuFYf2GsX6jWIDdkRW', '0771212354', 'Jaffna', 'adopter', '2025-08-12 18:28:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_adoptions_pet` (`pet_id`),
  ADD KEY `fk_adoptions_adopter` (`adopter_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `pets`
--
ALTER TABLE `pets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `added_by` (`added_by`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `generated_by` (`generated_by`);

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
-- AUTO_INCREMENT for table `adoptions`
--
ALTER TABLE `adoptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pets`
--
ALTER TABLE `pets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoptions`
--
ALTER TABLE `adoptions`
  ADD CONSTRAINT `fk_adoptions_adopter` FOREIGN KEY (`adopter_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_adoptions_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pets`
--
ALTER TABLE `pets`
  ADD CONSTRAINT `pets_ibfk_1` FOREIGN KEY (`added_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
