-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 09:35 AM
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
-- Database: `college_recommendation`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'hemraj roka', 'hemrajroka.hr@gmail.com', '$2y$10$kfgVlXwbbbRQ8M93WbEivuSWC3KSxuAQraEt5LHq5Ce12GGlnoXVS', '2025-06-07 13:37:56');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `college_id` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `marksheet_path` varchar(255) DEFAULT NULL,
  `entrance_rank` int(11) DEFAULT NULL,
  `course` varchar(100) NOT NULL,
  `background_faculty` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `student_id`, `college_id`, `applied_at`, `marksheet_path`, `entrance_rank`, `course`, `background_faculty`, `status`, `notified`) VALUES
(1, 3, 36, '2025-05-18 03:33:03', '../uploads/1747539183_Image-013_21_37_23.jpg', 50, 'BIT', 'Commerce', 'approved', 0),
(2, 4, 26, '2025-05-18 07:32:28', '../uploads/1747553548_Image-013_21_37_23.jpg', 55, 'bit', 'Science', 'approved', 0),
(3, 5, 23, '2025-06-07 13:06:03', '../uploads/1749301563_Picture1.png', 55, 'bit', 'Science', 'pending', 0),
(4, 9, 32, '2025-06-08 05:27:38', '../uploads/1749360458_Picture1.png', 55, 'bit', 'Science', 'pending', 0),
(5, 1, 17, '2025-07-27 01:03:17', '../uploads/1753578197_flowchart.jpg', 55, 'bit', 'Science', 'pending', 0),
(6, 1, 17, '2025-07-27 01:03:38', '../uploads/1753578218_flowchart.jpg', 55, 'bit', 'Science', 'pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course` text NOT NULL,
  `university` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `scholarships` text DEFAULT NULL,
  `duration` varchar(10) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`id`, `name`, `course`, `university`, `location`, `phone_number`, `scholarships`, `duration`, `description`, `created_at`) VALUES
(16, 'Nepal College of Information Technology', 'BSc CSIT,BIT', 'Pokhara University', 'Kathmandu', '9844321567', 'Merit and need-based scholarships.', '4', 'Courses & Fees: BSc CSIT - 170,000 NPR', '2025-05-17 11:36:46'),
(17, 'Patan Multiple Campus', 'BBA,BBS,BCA', 'Tribhuvan University', 'Lalitpur', '01-5523456', 'Limited scholarships available.', '4', 'Courses & Fees: BBA - 110,000 NPR, BBS - 100,000 NPR, BCA - 115,000 NPR. Located in city center.', '2025-05-17 11:36:46'),
(18, 'Himalayan College of Engineering', 'BIT,BCA', 'Kathmandu University', 'Kathmandu', '01-4412345', 'Scholarships for top 3 rankers.', '4', 'Courses & Fees: BIT - 160,000 NPR, BCA - 150,000 NPR. Focus on innovation and startups.', '2025-05-17 11:36:46'),
(19, 'Everest College of Management', 'BBA,BIM,BBS', 'Purbanchal University', 'Dharan', '025-543210', 'Sports scholarships available.', '4', 'Courses & Fees: BBA - 135,000 NPR, BIM - 140,000 NPR, BBS - 130,000 NPR. Active alumni network.', '2025-05-17 11:36:46'),
(20, 'Nepal Law Campus', 'BBS,BBA', 'Tribhuvan University', 'Kathmandu', '01-4234567', 'Scholarships for law and management students.', '4', 'Courses & Fees: BBS - 120,000 NPR, BBA - 125,000 NPR. Renowned faculty.', '2025-05-17 11:36:46'),
(21, 'Western College of Engineering', 'BSc CSIT,BIT,BCA', 'Pokhara University', 'Pokhara', '061-567890', 'Merit scholarships for engineering courses.', '4', 'Courses & Fees: BSc CSIT - 165,000 NPR, BIT - 170,000 NPR, BCA - 160,000 NPR. Industry partnerships.', '2025-05-17 11:36:46'),
(22, 'Central Department of Management', 'BBA,BIM', 'Tribhuvan University', 'Kathmandu', '01-4223456', 'Need-based scholarships.', '4', 'Courses & Fees: BBA - 115,000 NPR, BIM - 120,000 NPR. Strong academic track record.', '2025-05-17 11:36:46'),
(23, 'Purbanchal University School of Engineering', 'BIT,BSc CSIT', 'Purbanchal University', 'Biratnagar', '021-555432', 'Scholarships for top performers.', '4', 'Courses & Fees: BIT - 150,000 NPR, BSc CSIT - 155,000 NPR. Excellent labs and faculty.', '2025-05-17 11:36:46'),
(24, 'Kathmandu College of Science and Technology', 'BCA,BIT', 'Kathmandu University', 'Kathmandu', '01-4412344', 'Merit and need-based scholarships.', '4', 'Courses & Fees: BCA - 140,000 NPR, BIT - 145,000 NPR. Focus on practical skills.', '2025-05-17 11:36:46'),
(25, 'Nepal Academy of Tourism and Hotel Management', 'BHM,BBA', 'Tribhuvan University', 'Kathmandu', '01-4267890', 'Scholarships for hospitality students.', '4', 'Courses & Fees: BHM - 130,000 NPR, BBA - 125,000 NPR. Renowned for tourism studies.', '2025-05-17 11:36:46'),
(26, 'Patan Academy of Health Sciences', 'BSc CSIT,BIM', 'Tribhuvan University', 'Lalitpur', '01-5523344', 'Need-based scholarships.', '4', 'Courses & Fees: BSc CSIT - 150,000 NPR, BIM - 155,000 NPR. Strong emphasis on research.', '2025-05-17 11:36:46'),
(27, 'Biratnagar International College', 'BBA,BCA,BIT', 'Purbanchal University', 'Biratnagar', '021-543212', 'Scholarships for top scorers.', '4', 'Courses & Fees: BBA - 135,000 NPR, BCA - 130,000 NPR, BIT - 140,000 NPR. Modern campus facilities.', '2025-05-17 11:36:46'),
(28, 'Nepal Institute of Technology', 'BSc CSIT,BCA', 'Kathmandu University', 'Kathmandu', '01-4412000', 'Merit scholarships.', '4', 'Courses & Fees: BSc CSIT - 160,000 NPR, BCA - 155,000 NPR. Focus on technology innovation.', '2025-05-17 11:36:46'),
(29, 'Hetauda College', 'BBA,BBS,BIM', 'Tribhuvan University', 'Hetauda', '057-420123', 'Limited scholarships.', '4', 'Courses & Fees: BBA - 115,000 NPR, BBS - 110,000 NPR, BIM - 120,000 NPR. Growing academic reputation.', '2025-05-17 11:36:46'),
(30, 'Kathmandu University School of Management', 'BBA,BIM', 'Kathmandu University', 'Dhulikhel', '011-660200', 'Scholarships for merit and sports.', '4', 'Courses & Fees: BBA - 145,000 NPR, BIM - 150,000 NPR. Excellent faculty and industry exposure.', '2025-05-17 11:36:46'),
(31, 'Eastern College of Engineering', 'BIT,BSc CSIT', 'Purbanchal University', 'Dharan', '025-554321', 'Merit scholarships.', '4', 'Courses & Fees: BIT - 155,000 NPR, BSc CSIT - 160,000 NPR. Strong technical programs.', '2025-05-17 11:36:46'),
(32, 'National College of Science and Technology', 'BCA,BBA,BIM', 'Tribhuvan University', 'Kathmandu', '01-4223321', 'Scholarships for needy students.', '4', 'Courses & Fees: BCA - 130,000 NPR, BBA - 125,000 NPR, BIM - 135,000 NPR. Good academic environment.', '2025-05-17 11:36:46'),
(33, 'Tribhuvan University Central Campus', 'BBS,BBA,BCA', 'Tribhuvan University', 'Kirtipur', '01-4333445', 'Limited merit scholarships.', '4', 'Courses & Fees: BBS - 100,000 NPR, BBA - 110,000 NPR, BCA - 115,000 NPR. One of the oldest campuses.', '2025-05-17 11:36:46'),
(34, 'Pokhara University School of Management', 'BBA,BIM', 'Pokhara University', 'Pokhara', '061-567800', 'Scholarships for top students.', '4', 'Courses & Fees: BBA - 140,000 NPR, BIM - 145,000 NPR. Emphasis on business research.', '2025-05-17 11:36:46'),
(35, 'asian college of higher', 'BSc CSIT,BIT', 'Tribhuvan University', 'pokhara', '9806289642', 'no', '4', 'no', '2025-05-17 12:06:18'),
(36, 'asian college', 'BCA,BIT', 'Tribhuvan University', 'pokhara', '9806289642', 'no', '4', 'no', '2025-05-17 13:05:22'),
(37, 'Deepshika higher secondary colleges', 'BBA,BBS', 'Tribhuvan University', 'Dang', '99999999', '', '4', '', '2025-06-07 08:16:09'),
(38, 'texas colleges', 'BCA,BSc CSIT,BBA', 'Tribhuvan University', 'pokhara', '99999999', 'depend upon +12 grade', '4', 'one of the best colleges. The fee structure of BCA is 14000, bscsit 11000 and bba is 40000', '2025-06-08 04:42:35');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `email` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `qualification` varchar(100) NOT NULL,
  `university` varchar(100) NOT NULL,
  `course` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `fullname`, `gender`, `email`, `city`, `password`, `qualification`, `university`, `course`, `created_at`) VALUES
(1, 'hemraj roka', 'Male', 'hemrajroka.hr@gmail.com', 'kathamandu', '$2y$10$aEj8oc84phVwiaEvcJQgH.lLRLcVEmjfwZ50FIlqdkdWHm6FyUfQW', '+2', 'Tribhuvan University', '[\"BIT\",\"BSc CSIT\"]', '2025-05-17 05:22:42'),
(2, 'sachin shahi', 'Male', 'sachinshahi123@gmail.com', 'kathamandu', '$2y$10$EpAOT6NLEHO.iR8FREWl1.A69R.ApJQ20m6jhc/rcsnf2QcdpmPIm', '+2', 'Kathmandu University', 'BIT', '2025-05-17 05:46:23'),
(3, 'nikesh shrestha', 'Male', 'nikesh123@gmail.com', 'pokhara', '$2y$10$XodIfESVzNlrCjVf0JdSOOgqZ0YHfIGy/4ijUDmdo8zvOrjdIua5m', '+2', 'Tribhuvan University', 'BIT', '2025-05-17 12:14:24'),
(4, 'Bidur', 'Male', 'bidur@gmail.com', 'Lalitpur', '$2y$10$v7uyxONk9cT1Ieg3abwx9.UXwI/8JD7QqB5dbfDCkyq4sHlJoHDpm', '+2', 'Tribhuvan University', 'BIM', '2025-05-18 04:17:54'),
(5, 'Hello world', 'Male', 'hello123@gmail.com', 'kathamandu', '$2y$10$6TerOy1.jcoGrVL64Vu8purQrMthexdcZR3Ug4OqHaRavi/.QpMMW', '+2', 'Tribhuvan University', '[\"BCA\",\"BSc CSIT\"]', '2025-06-07 08:56:30'),
(6, 'suman thapa', 'Male', 'suman123@gmail.com', 'kathamandu', '$2y$10$vI9ES2KpR72k1x74lJW1BOwa1eOUKkesxGSddxWeKnFJnJN60CVR2', '+2', 'Tribhuvan University', '[\"BCA\",\"BSc CSIT\"]', '2025-06-07 14:25:59'),
(7, 'hemraj roka', 'Male', 'hemrajroka@icloud.com', 'kathamandu', '$2y$10$Obd7wtIvWtO/s8sCwowN1uVZpAz2dXtM6zkJCVQL4Nm1UYnIW7QSG', '+2', 'Tribhuvan University', '[\"BCA\",\"BSc CSIT\"]', '2025-06-08 03:45:49'),
(8, 'suhel maharjan', 'Male', 'suhel123@gmail.com', 'pokhara', '$2y$10$srBk0W7YurFlmBMruRX1sO6U6aN5Kjk4W5EvTlIMjGUhyg58FxMn6', '+2', 'Tribhuvan University', '[\"BCA\",\"BSc CSIT\"]', '2025-06-08 04:40:16'),
(9, 'ghale', 'Male', 'ghale123@gmail.com', 'kathmandu', '$2y$10$WuA3KXcYOz6MbAjhNOwNbOrvwxC08QEtIScGGCVstZg.KVX6WSWLC', '+2', 'Tribhuvan University', '[\"BCA\"]', '2025-06-08 05:26:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `college_id` (`college_id`);

--
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
