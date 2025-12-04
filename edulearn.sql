-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2025 at 10:23 PM
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
-- Database: `edulearn`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Programming', 'Learn programming languages', '2025-07-22 16:50:57'),
(2, 'Design', 'UI/UX and Graphic Design', '2025-07-22 16:50:57'),
(3, 'Business', 'Business skills', '2025-07-22 16:50:57'),
(4, 'Marketing', 'Digital marketing', '2025-07-22 16:50:57');

-- --------------------------------------------------------

--
-- Table structure for table `course_enrollments`
--

CREATE TABLE `course_enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('in_progress','completed','failed','dropped') DEFAULT 'in_progress',
  `progress_percentage` int(11) DEFAULT 0,
  `enrolled_at` datetime DEFAULT current_timestamp(),
  `last_accessed` datetime DEFAULT NULL,
  `completion_date` datetime DEFAULT NULL,
  `time_spent` int(11) DEFAULT 0,
  `exam_score` decimal(5,2) DEFAULT NULL,
  `exam_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_enrollments`
--

INSERT INTO `course_enrollments` (`id`, `user_id`, `course_id`, `status`, `progress_percentage`, `enrolled_at`, `last_accessed`, `completion_date`, `time_spent`, `exam_score`, `exam_status`) VALUES
(1, 2, 5, 'completed', 100, '2025-07-26 14:33:42', '2025-07-29 06:31:52', '2025-07-27 14:36:07', 0, 80.00, 'passed'),
(2, 2, 4, 'in_progress', 0, '2025-07-26 14:39:08', '2025-07-29 11:19:13', NULL, 0, NULL, NULL),
(3, 3, 5, 'completed', 100, '2025-07-27 14:51:00', '2025-07-27 14:53:46', '2025-07-27 14:53:33', 0, 80.00, 'passed'),
(4, 4, 5, 'completed', 100, '2025-07-27 15:15:40', '2025-07-27 15:16:36', '2025-07-27 15:16:12', 0, 80.00, 'passed'),
(5, 6, 5, 'completed', 100, '2025-07-29 11:52:09', '2025-07-29 11:54:37', '2025-07-29 11:54:24', 0, 80.00, 'passed'),
(6, 7, 5, 'completed', 100, '2025-07-29 13:54:00', '2025-07-29 13:56:27', '2025-07-29 13:56:13', 0, 80.00, 'passed');

-- --------------------------------------------------------

--
-- Table structure for table `course_list`
--

CREATE TABLE `course_list` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT 1,
  `category_id` int(11) DEFAULT 1,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `duration_hours` int(11) DEFAULT 0,
  `level` enum('beginner','intermediate','advanced') DEFAULT 'beginner',
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `curriculum` text DEFAULT NULL,
  `overview` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_list`
--

INSERT INTO `course_list` (`id`, `teacher_id`, `category_id`, `title`, `description`, `price`, `duration_hours`, `level`, `thumbnail`, `status`, `created_at`, `curriculum`, `overview`) VALUES
(1, 1, 1, 'Web Development 2', 'Learn HTML, CSS, JavaScript from scratch', 49.99, 25, 'beginner', NULL, 'active', '2025-07-22 17:07:34', '', ''),
(2, 1, 1, 'JavaScript Masterclass', 'Advanced JavaScript concepts and frameworks', 79.99, 30, 'advanced', NULL, 'active', '2025-07-22 17:07:34', NULL, NULL),
(3, 1, 1, 'UI/UX Design Basics', 'Design principles and user experience', 39.99, 20, 'beginner', NULL, 'active', '2025-07-22 17:07:34', NULL, NULL),
(4, 1, 1, 'Digital Marketing Complete', 'Master online marketing strategies', 29.99, 15, 'beginner', NULL, 'active', '2025-07-22 17:07:34', NULL, NULL),
(5, 1, 1, 'Python Programming', 'Learn Python from zero to hero', 59.99, 22, 'beginner', NULL, 'active', '2025-07-22 17:07:34', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `progress` int(11) DEFAULT 0,
  `completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam`
--

CREATE TABLE `exam` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` enum('a','b','c','d') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam`
--

INSERT INTO `exam` (`id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `created_at`, `updated_at`) VALUES
(1, '1+1', '1', '2', '3', '4', 'b', '2025-07-29 03:30:20', '2025-07-29 03:30:20');

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--
-- Error reading structure for table edulearn.exams: #1932 - Table &#039;edulearn.exams&#039; doesn&#039;t exist in engine
-- Error reading data for table edulearn.exams: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `edulearn`.`exams`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--
-- Error reading structure for table edulearn.subscription: #1932 - Table &#039;edulearn.subscription&#039; doesn&#039;t exist in engine
-- Error reading data for table edulearn.subscription: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `edulearn`.`subscription`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `subscription-plans`
--
-- Error reading structure for table edulearn.subscription-plans: #1932 - Table &#039;edulearn.subscription-plans&#039; doesn&#039;t exist in engine
-- Error reading data for table edulearn.subscription-plans: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `edulearn`.`subscription-plans`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `teacher_application`
--
-- Error reading structure for table edulearn.teacher_application: #1932 - Table &#039;edulearn.teacher_application&#039; doesn&#039;t exist in engine
-- Error reading data for table edulearn.teacher_application: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `edulearn`.`teacher_application`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `teacher_profile`
--
-- Error reading structure for table edulearn.teacher_profile: #1932 - Table &#039;edulearn.teacher_profile&#039; doesn&#039;t exist in engine
-- Error reading data for table edulearn.teacher_profile: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `edulearn`.`teacher_profile`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `users_acc`
--

CREATE TABLE `users_acc` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('student','teacher','admin') DEFAULT 'student',
  `subscription_type` enum('free','pro','university') DEFAULT 'free',
  `university_id` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 1,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users_acc`
--

INSERT INTO `users_acc` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `role`, `subscription_type`, `university_id`, `phone`, `profile_image`, `email_verified`, `status`, `created_at`) VALUES
(1, 'alex', 'alex@gmail.com', '$2y$10$uOudtfmIj3EC6ivKpdebEuhso30rSaR93Vkr3OTKXkR1M4og7cLUm', 'alex', 'a', 'student', 'free', NULL, '', NULL, 0, 'active', '2025-07-26 09:43:50'),
(2, 'Ali', 'Ali@gmail.com', '$2y$10$90MLi5YQClIr1NCw56O9CO1XjzZfc1nwaETODT27Anxol66XlAxKS', 'Ali', 'a', 'admin', 'pro', NULL, '', NULL, 0, 'active', '2025-07-26 10:06:58'),
(3, 'jad', 'jad@gmail.com', '$2y$10$H9NCWiKufd.ACWqfufzlBOUXNZQByv6let1dLnrjPM1vrYGGheqAm', 'jad', 'm', 'student', 'pro', NULL, '', NULL, 0, 'active', '2025-07-27 10:49:59'),
(4, 'moe', 'moe@gmail.com', '$2y$10$GSupYFis1g08kfXkqyHSXeZYC2g66C2rIrde5HugL6S5zXXvD.sRa', 'moe', 'm', 'student', 'pro', NULL, '', NULL, 0, 'active', '2025-07-27 11:13:42'),
(5, 'Joe', 'Joe@gmail.com', '$2y$10$vtqTd40Zs4b9kYqqbCE9iunCoyaPEWm7wwt2na0.35UJpjYCs02aS', 'Joe', 'j', 'teacher', 'free', NULL, '', NULL, 0, 'active', '2025-07-28 13:15:36'),
(6, 'batata', 'batata@gmail.com', '$2y$10$lsZvoFzOfByRC3Eg6wk9xe8OlQLyYNX/BV493IA0J435hpS800rWG', 'batata', 'm', 'student', 'free', NULL, '', NULL, 0, 'active', '2025-07-29 07:49:51'),
(7, 'hasan', 'hasan@gmail.com', '$2y$10$oIIOdnvlesg1WqS3ykhaLO4/C2hvdQ97SzSnuGa5oNPID1AoXwivi', 'hasan', 'm', 'student', 'free', NULL, '', NULL, 0, 'active', '2025-07-29 09:50:36');

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

CREATE TABLE `user_subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subscription_type` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_subscriptions`
--

INSERT INTO `user_subscriptions` (`id`, `user_id`, `subscription_type`, `status`, `start_date`, `end_date`, `price`, `payment_method`, `created_at`) VALUES
(1, 2, 'premium', 'active', '2025-07-26 13:15:39', '2025-08-26 13:15:39', 19.99, 'credit_card', '2025-07-26 13:15:39'),
(2, 2, 'university', 'active', '2025-07-27 12:49:45', '2025-08-27 12:49:45', 9.99, 'credit_card', '2025-07-27 12:49:45'),
(3, 3, 'premium', 'active', '2025-07-27 13:50:50', '2025-08-27 13:50:50', 19.99, 'credit_card', '2025-07-27 13:50:50'),
(4, 4, 'premium', 'active', '2025-07-27 14:15:30', '2025-08-27 14:15:30', 19.99, 'credit_card', '2025-07-27 14:15:30'),
(5, 2, 'premium', 'active', '2025-07-28 02:24:11', '2025-08-28 02:24:11', 19.99, 'credit_card', '2025-07-28 02:24:11'),
(6, 6, 'premium', 'active', '2025-07-29 10:51:57', '2025-08-29 10:51:57', 19.99, 'credit_card', '2025-07-29 10:51:57'),
(7, 7, 'premium', 'active', '2025-07-29 12:53:43', '2025-08-29 12:53:43', 19.99, 'credit_card', '2025-07-29 12:53:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_list`
--
ALTER TABLE `course_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam`
--
ALTER TABLE `exam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_correct_option` (`correct_option`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `exam_id` (`exam_id`);

--
-- Indexes for table `users_acc`
--
ALTER TABLE `users_acc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `course_enrollments`
--
ALTER TABLE `course_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `course_list`
--
ALTER TABLE `course_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam`
--
ALTER TABLE `exam`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users_acc`
--
ALTER TABLE `users_acc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_subscriptions`
--
ALTER TABLE `user_subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
