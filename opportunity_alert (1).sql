-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2025 at 09:42 AM
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
-- Database: `opportunity_alert`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `resume_path` varchar(255) NOT NULL,
  `cover_letter` text DEFAULT NULL,
  `status` enum('pending','approved','disapproved') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `job_id`, `resume_path`, `cover_letter`, `status`, `applied_at`) VALUES
(1, 1, 1, 'Uploads/Resumes/1744455594_API Explorer (1).pdf', 'asdzfxgdsaf', NULL, '2025-04-12 10:59:54');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `provider` varchar(100) NOT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `field` varchar(50) NOT NULL,
  `interest` varchar(50) NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bg_image` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `description`, `provider`, `duration`, `field`, `interest`, `posted_at`, `bg_image`) VALUES
(1, 'Python Basics', 'Learn Python programming.', 'CodeAcademy', NULL, 'Programming', 'Beginner', '2025-04-11 21:28:47', NULL),
(2, 'UI/UX Design', 'Master design principles.', 'DesignSchool', NULL, 'Design', 'Advanced', '2025-04-11 21:28:47', NULL),
(3, 'Business Analytics', 'Data-driven decisions.', 'BizLearn', NULL, 'Business', 'Certification', '2025-04-11 21:28:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `user_id`, `course_id`, `enrolled_at`) VALUES
(1, 3, 3, '2025-04-12 18:44:20'),
(2, 3, 2, '2025-04-12 18:44:38'),
(3, 3, 1, '2025-04-12 18:48:23');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `company` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `field` varchar(50) NOT NULL,
  `interest` varchar(50) NOT NULL,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiration_date` date NOT NULL DEFAULT '2025-12-31',
  `bg_image` longblob DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `description`, `company`, `location`, `field`, `interest`, `posted_at`, `expiration_date`, `bg_image`) VALUES
(1, 'Software Engineer', 'Develop web applications.', 'TechCorp', 'Remote', 'IT', 'Full-time', '2025-04-11 21:28:47', '2025-12-31', NULL),
(2, 'Graphic Designer', 'Create stunning visuals.', 'DesignHub', 'Mumbai', 'Design', 'Freelance', '2025-04-11 21:28:47', '2025-12-31', NULL),
(3, 'Finance Analyst', 'Analyze financial data.', 'FinBank', 'Delhi', 'Finance', 'Internship', '2025-04-11 21:28:47', '2025-12-31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('job','course') NOT NULL,
  `item_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `item_id`, `message`, `created_at`, `is_read`) VALUES
(1, 2, 'job', 6, 'New job: asdfg', '2025-04-12 13:17:02', 0),
(2, 3, 'job', 6, 'New job: asdfg', '2025-04-12 13:17:02', 1),
(3, 6, 'job', 6, 'New job: asdfg', '2025-04-12 13:17:02', 0),
(4, 2, 'job', 8, 'New job: sdfg', '2025-04-12 16:27:48', 0),
(5, 3, 'job', 8, 'New job: sdfg', '2025-04-12 16:27:48', 1),
(6, 6, 'job', 8, 'New job: sdfg', '2025-04-12 16:27:48', 0),
(7, 2, 'course', 4, 'New course: sdf', '2025-04-12 16:42:19', 0),
(8, 3, 'course', 4, 'New course: sdf', '2025-04-12 16:42:19', 1),
(9, 6, 'course', 4, 'New course: sdf', '2025-04-12 16:42:19', 0),
(10, 2, 'job', 9, 'New job: asdfgh', '2025-04-12 16:42:54', 0),
(11, 3, 'job', 9, 'New job: asdfgh', '2025-04-12 16:42:54', 1),
(12, 6, 'job', 9, 'New job: asdfgh', '2025-04-12 16:42:54', 0),
(13, 2, 'job', 10, 'New job: sdgfh', '2025-04-12 16:45:42', 0),
(14, 3, 'job', 10, 'New job: sdgfh', '2025-04-12 16:45:42', 1),
(15, 6, 'job', 10, 'New job: sdgfh', '2025-04-12 16:45:42', 0),
(16, 2, 'job', 11, 'New job: wsdfg', '2025-04-12 16:48:36', 0),
(17, 3, 'job', 11, 'New job: wsdfg', '2025-04-12 16:48:36', 1),
(18, 6, 'job', 11, 'New job: wsdfg', '2025-04-12 16:48:36', 0),
(19, 3, 'job', 0, 'You have enrolled in the course: Business Analytics', '2025-04-13 00:14:20', 0),
(20, 3, 'job', 0, 'You have enrolled in the course: UI/UX Design', '2025-04-13 00:14:38', 0),
(21, 3, 'job', 0, 'You have enrolled in the course: Python Basics', '2025-04-13 00:18:23', 0),
(22, 2, 'job', 12, 'New job: adsf', '2025-04-13 00:57:11', 0),
(23, 3, 'job', 12, 'New job: adsf', '2025-04-13 00:57:11', 0),
(24, 6, 'job', 12, 'New job: adsf', '2025-04-13 00:57:11', 0),
(25, 2, 'job', 13, 'New job: adsf', '2025-04-13 01:19:47', 0),
(26, 3, 'job', 13, 'New job: adsf', '2025-04-13 01:19:47', 0),
(27, 6, 'job', 13, 'New job: adsf', '2025-04-13 01:19:47', 0),
(28, 2, 'course', 5, 'New course: erdfh', '2025-04-13 12:19:14', 0),
(29, 3, 'course', 5, 'New course: erdfh', '2025-04-13 12:19:14', 0),
(30, 6, 'course', 5, 'New course: erdfh', '2025-04-13 12:19:14', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', 'admin@opportunityalert.com', '2025-04-11 21:22:17'),
(2, 'testuser', 'user123', 'user', 'testuser@opportunityalert.com', '2025-04-11 21:22:17'),
(3, 'Aishwary', 'asdf123', 'user', '', '2025-04-11 21:22:50'),
(6, 'Ayush', 'asdf', 'user', 'asdf@gmail.com', '2025-04-11 22:12:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`);

--
-- Constraints for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD CONSTRAINT `course_enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `course_enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
