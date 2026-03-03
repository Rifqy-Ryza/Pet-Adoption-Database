-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 03, 2026 at 07:04 PM
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
-- Database: `pet_adoption`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `username`, `password`, `name`, `email`) VALUES
(12, 'admin2', 'admin123', 'Shelter Manager', 'admin@shelter.com'),
(19, 'Admin', '12345678', 'Test', 'test@gmail.com'),
(20, 'Admin3', 'admin321', 'Admin sub', 'Sub@gmail.com'),
(21, 'Admin4', '87654321', 'Admin Assist', 'Assist@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `adopter`
--

CREATE TABLE `adopter` (
  `adopterID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adopter`
--

INSERT INTO `adopter` (`adopterID`, `name`, `email`, `phone`, `address`, `password`) VALUES
(45, 'Ali Rahman', 'ali@email.com', '012-3456789', '123 Jalan Merdeka, Kuala Lumpur', 'password123'),
(46, 'Siti Aminah', 'siti@email.com', '013-4567890', '456 Lorong Bahagia, Kuching, Sarawak', 'password123'),
(47, 'Tan Wei Ming', 'wei@email.com', '014-5678901', '789 Jalan Seri, George Town, Penang', 'password123'),
(48, 'Nurul Huda', 'nurul@email.com', '015-6789012', '101 Taman Sentosa, Johor Bahru', 'password123'),
(49, 'Ahmad Faisal', 'ahmad@email.com', '016-7890123', '202 Kampung Baru, Shah Alam, Selangor', 'password123'),
(55, 'Rifqy Nazhan Ryza', 'rifqynazhan@gmail.com', '01131069494', 'UPM', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `adoptionrequest`
--

CREATE TABLE `adoptionrequest` (
  `requestID` int(11) NOT NULL,
  `petID` int(11) DEFAULT NULL,
  `adopterID` int(11) DEFAULT NULL,
  `requestDate` date DEFAULT curdate(),
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adoptionrequest`
--

INSERT INTO `adoptionrequest` (`requestID`, `petID`, `adopterID`, `requestDate`, `status`) VALUES
(36, 56, 55, '2025-10-22', 'Rejected'),
(37, 54, 55, '2025-10-22', 'Approved'),
(38, 59, 45, '2025-10-22', 'Rejected'),
(39, 62, 45, '2025-10-22', 'Approved'),
(40, 60, 45, '2025-10-22', 'Pending'),
(41, 58, 47, '2025-10-22', 'Rejected'),
(42, 61, 47, '2025-10-22', 'Pending'),
(43, 57, 55, '2025-10-22', 'Approved'),
(44, 53, 55, '2025-10-22', 'Pending'),
(45, 56, 55, '2025-10-24', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `pet`
--

CREATE TABLE `pet` (
  `petID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `breed` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('Available','Pending','Adopted') DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet`
--

INSERT INTO `pet` (`petID`, `name`, `type`, `age`, `breed`, `created_at`, `status`) VALUES
(52, 'Tweak', 'Bird', 1, 'Parrot', '2025-10-22 01:49:24', 'Available'),
(53, 'Mini', 'Hamster', 1, '', '2025-10-22 01:49:45', 'Pending'),
(54, 'Diva', 'Cat', 1, 'Mancoon', '2025-10-22 02:39:58', 'Adopted'),
(55, 'bew', 'Rabbit', 1, '', '2025-10-22 02:40:18', 'Available'),
(56, 'gard', 'Dog', 1, 'Pitbull', '2025-10-22 02:40:30', 'Pending'),
(57, 'Buddy', 'Dog', 3, 'Golden Retriever', '2025-10-22 10:57:27', 'Adopted'),
(58, 'Whiskers', 'Cat', 2, 'Persian', '2025-10-22 10:57:27', 'Available'),
(59, 'Rocky', 'Dog', 5, 'Bulldog', '2025-10-22 10:57:27', 'Available'),
(60, 'Luna', 'Cat', 1, 'Siamese', '2025-10-22 10:57:27', 'Pending'),
(61, 'Max', 'Dog', 4, 'German Shepherd', '2025-10-22 10:57:27', 'Pending'),
(62, 'Bella', 'Rabbit', 2, 'Dutch', '2025-10-22 10:57:27', 'Adopted'),
(63, 'Donut', 'Cat', 1, 'Siamese', '2025-10-22 20:38:04', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `event_name` varchar(255) NOT NULL DEFAULT 'Happy Paws Animal Shelter'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `event_name`) VALUES
(1, 'Happy PAWS Days');

-- --------------------------------------------------------

--
-- Table structure for table `systemsettings`
--

CREATE TABLE `systemsettings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `event_name` varchar(255) NOT NULL DEFAULT 'Pet Adoption Program'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `systemsettings`
--

INSERT INTO `systemsettings` (`id`, `event_name`) VALUES
(1, 'Happy Paws Adoption Day 2025');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `adopter`
--
ALTER TABLE `adopter`
  ADD PRIMARY KEY (`adopterID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `adoptionrequest`
--
ALTER TABLE `adoptionrequest`
  ADD PRIMARY KEY (`requestID`),
  ADD KEY `petID` (`petID`),
  ADD KEY `adopterID` (`adopterID`);

--
-- Indexes for table `pet`
--
ALTER TABLE `pet`
  ADD PRIMARY KEY (`petID`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `systemsettings`
--
ALTER TABLE `systemsettings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `adopter`
--
ALTER TABLE `adopter`
  MODIFY `adopterID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `adoptionrequest`
--
ALTER TABLE `adoptionrequest`
  MODIFY `requestID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `pet`
--
ALTER TABLE `pet`
  MODIFY `petID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adoptionrequest`
--
ALTER TABLE `adoptionrequest`
  ADD CONSTRAINT `adoptionrequest_ibfk_1` FOREIGN KEY (`petID`) REFERENCES `pet` (`petID`) ON DELETE CASCADE,
  ADD CONSTRAINT `adoptionrequest_ibfk_2` FOREIGN KEY (`adopterID`) REFERENCES `adopter` (`adopterID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
