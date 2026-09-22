-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2026 at 04:44 PM
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
(5, 1, 17, '2025-07-27 01:03:17', '../uploads/1753578197_flowchart.jpg', 55, 'bit', 'Science', 'rejected', 0),
(6, 1, 17, '2025-07-27 01:03:38', '../uploads/1753578218_flowchart.jpg', 55, 'bit', 'Science', 'approved', 0),
(7, 7, 35, '2025-07-30 03:53:16', '../uploads/1753847596_Screenshot 2024-09-17 180235.png', 50, 'bit', 'Science', 'pending', 0),
(8, 14, 22, '2025-11-30 09:25:19', '../uploads/1764494719_mikrotik router.jpg', 50, 'bit', 'Science', 'rejected', 0);

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
(16, 'Nepal College of Information Technology', 'BSc CSIT,BIT', 'Pokhara University', 'Kathmandu', '9844925255', 'Merit and need-based scholarships.', '4', 'Courses & Fees: BSc CSIT - 170,000 NPR', '2025-05-17 11:36:46'),
(17, 'Patan Multiple Campus', 'BCA,BBA,BBS', 'Tribhuvan University', 'Lalitpur', '99999999', 'Limited scholarships available.', '4', 'Courses & Fees: BBA - 110,000 NPR, BBS - 100,000 NPR, BCA - 115,000 NPR. Located in city center.', '2025-05-17 11:36:46'),
(18, 'Himalayan College of Engineering', 'BIT,BCA', 'Kathmandu University', 'Kathmandu', '01-4412345', 'Scholarships for top 3 rankers.', '4', 'Courses & Fees: BIT - 160,000 NPR, BCA - 150,000 NPR. Focus on innovation and startups.', '2025-05-17 11:36:46'),
(19, 'Everest College of Management', 'BBA,BIM,BBS', 'Purbanchal University', 'Dharan', '025-543210', 'Sports scholarships available.', '4', 'Courses & Fees: BBA - 135,000 NPR, BIM - 140,000 NPR, BBS - 130,000 NPR. Active alumni network.', '2025-05-17 11:36:46'),
(20, 'Nepal Law Campus', 'BBS,BBA', 'Tribhuvan University', 'Kathmandu', '01-4234567', 'Scholarships for law and management students.', '4', 'Courses & Fees: BBS - 120,000 NPR, BBA - 125,000 NPR. Renowned faculty.', '2025-05-17 11:36:46'),
(21, 'Western College of Engineering', 'BSc CSIT,BIT,BCA', 'Pokhara University', 'Pokhara', '061-567890', 'Merit scholarships for engineering courses.', '4', 'Courses & Fees: BSc CSIT - 165,000 NPR, BIT - 170,000 NPR, BCA - 160,000 NPR. Industry partnerships.', '2025-05-17 11:36:46'),
(22, 'Central Department of Management', 'BBA,BIM', 'Tribhuvan University', 'Kathmandu', '01-4223456', 'Need-based scholarships.', '4', 'Courses & Fees: BBA - 115,000 NPR, BIM - 120,000 NPR. Strong academic track record.', '2025-05-17 11:36:46'),
(23, 'Purbanchal University School of Engineering', 'BSc CSIT,BIT', 'Purbanchal University', 'Biratnagar', '0135574545', 'Scholarships for top performers.', '4', 'Courses & Fees: BIT - 150,000 NPR, BSc CSIT - 155,000 NPR. Excellent labs and faculty.', '2025-05-17 11:36:46'),
(24, 'Kathmandu College of Science and Technology', 'BCA,BIT', 'Kathmandu University', 'Kathmandu', '01-4412344', 'Merit and need-based scholarships.', '4', 'Courses & Fees: BCA - 140,000 NPR, BIT - 145,000 NPR. Focus on practical skills.', '2025-05-17 11:36:46'),
(25, 'Nepal Academy of Tourism and Hotel Management', 'BHM,BBA', 'Tribhuvan University', 'Kathmandu', '01-4267890', 'Scholarships for hospitality students.', '4', 'Courses & Fees: BHM - 130,000 NPR, BBA - 125,000 NPR. Renowned for tourism studies.', '2025-05-17 11:36:46'),
(26, 'Patan Academy of Health Sciences', 'BSc CSIT,BIM', 'Tribhuvan University', 'Lalitpur', '9898989', 'Need-based scholarships.', '4', 'Courses & Fees: BSc CSIT - 150,000 NPR, BIM - 155,000 NPR. Strong emphasis on research.', '2025-05-17 11:36:46'),
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
(38, 'texas colleges', 'BCA,BSc CSIT,BBA', 'Tribhuvan University', 'pokhara', '99999999', 'depend upon +12 grade', '4', 'one of the best colleges. The fee structure of BCA is 14000, bscsit 11000 and bba is 40000', '2025-06-08 04:42:35'),
(39, 'Mahendra multiple campus', 'BBA,BBS', 'Tribhuvan University', 'Dang', '90999099', 'yes based on your entrance rank and +12 marks', '4', 'The college fee of bba is 4lakh 12 thousand and bbs is 2 lakh in four years', '2025-07-29 16:50:13'),
(40, 'advanced engineering colleges', 'BCA,BSc CSIT', 'Tribhuvan University', 'kathamandu', '99999999', 'nha', '1', 'hahxd', '2025-08-08 05:04:42');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `fullname`, `gender`, `email`, `password`, `created_at`) VALUES
(1, 'hemraj roka', 'Male', 'hemrajroka.hr@gmail.com', NULL, '2025-05-17 05:22:42'),
(2, 'sachin shahi', 'Male', 'sachinshahi123@gmail.com', NULL, '2025-05-17 05:46:23'),
(3, 'nikesh shrestha', 'Male', 'nikesh123@gmail.com', NULL, '2025-05-17 12:14:24'),
(4, 'Bidur', 'Male', 'bidur@gmail.com', NULL, '2025-05-18 04:17:54'),
(5, 'Hello world', 'Male', 'hello123@gmail.com', NULL, '2025-06-07 08:56:30'),
(6, 'suman thapa', 'Male', 'suman123@gmail.com', NULL, '2025-06-07 14:25:59'),
(7, 'hemraj roka', 'Male', 'hemrajroka@icloud.com', NULL, '2025-06-08 03:45:49'),
(8, 'suhel maharjan', 'Male', 'suhel123@gmail.com', NULL, '2025-06-08 04:40:16'),
(9, 'ghale', 'Male', 'ghale123@gmail.com', NULL, '2025-06-08 05:26:18'),
(12, 'suman', 'Male', 'suman456@gmail.com', NULL, '2025-07-29 13:08:44'),
(13, 'roka hemraj', 'Male', 'roka123@gmail.com', NULL, '2025-07-30 04:09:07'),
(14, 'rabin shrestha', 'Male', 'rabinshrestha@gmail.com', NULL, '2025-11-30 09:22:20'),
(15, 'hemraj roka', 'Male', 'hhhh@mdada.com', NULL, '2026-09-22 12:22:22'),
(16, 'jwd', 'Male', 'sdsd2@gmail.com', NULL, '2026-09-22 12:24:38'),
(19, 'roka hemraj', 'Male', 'roka21@gmail.com', NULL, '2026-09-22 12:36:00'),
(20, 'gfghgh', 'Male', 'rabinshrestha12@gmail.com', '$2y$10$6mXt1jF2zxJ4uMkTQJMyV..97R/sfvdBS7k.0QCrW7wkNHA7gVXz6', '2026-09-22 12:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `student_preferences`
--

CREATE TABLE `student_preferences` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `preferred_course` varchar(255) DEFAULT NULL,
  `preferred_university` varchar(255) DEFAULT NULL,
  `preferred_location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_preferences`
--

INSERT INTO `student_preferences` (`id`, `student_id`, `preferred_course`, `preferred_university`, `preferred_location`, `created_at`) VALUES
(11, 20, 'bca', 'tribhuwan', 'kathmandu', '2026-09-22 14:33:39');

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
-- Indexes for table `student_preferences`
--
ALTER TABLE `student_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `colleges`
--
ALTER TABLE `colleges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `student_preferences`
--
ALTER TABLE `student_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`);

--
-- Constraints for table `student_preferences`
--
ALTER TABLE `student_preferences`
  ADD CONSTRAINT `student_preferences_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
