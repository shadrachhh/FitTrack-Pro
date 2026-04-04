-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: db
-- Generation Time: Apr 04, 2026 at 12:25 PM
-- Server version: 8.0.44
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fittrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `exercises`
--

CREATE TABLE `exercises` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `muscle_group` varchar(100) DEFAULT NULL,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `exercises`
--

INSERT INTO `exercises` (`id`, `name`, `muscle_group`, `description`) VALUES
(1, 'Bench Press', 'Chest', 'A workout that focuses on the chest muscles'),
(2, 'Squat', 'Legs', 'A fundamental lower-body exercise that targets the quadriceps, hamstrings, and glutes through a bending and lifting motion.'),
(3, 'Deadlift', 'Back', 'A full-body compound movement that primarily targets the lower back, glutes, and hamstrings by lifting weight from the ground.'),
(4, 'Pull up', 'Back', 'An upper-body exercise that targets the back and biceps by pulling your body up toward a bar.'),
(5, 'Shoulder press', 'Shoulders', 'A strength exercise that targets the shoulder muscles by pressing weight overhead.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`) VALUES
(1, 'Terkuma Uker', 'terkumauker50@gmail.com', '$2y$10$VLm.zoeHQb4bTp6Z3Y4ydu/Z2SDu8IL5.9fwRidmAHbpD4B/R.g.2', '2026-04-04 08:21:19', 'admin'),
(2, 'Romeny Leito', 'romeny.leito@hotmail.com', '$2y$10$7bn3M8nfAYNUOs5r1Uy5aO6dLH0ylMq5X/.FuB00.9NhIUQA6V3dq', '2026-04-04 09:26:52', 'user'),
(3, 'Sefa Uker', 'sefa@gmail.com', '$2y$10$CWBv7FlvsWEDeYdkZ5Bx7OGDIPyWb2adIPU5UT4a8wsISPSuScy1K', '2026-04-04 12:15:04', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `workouts`
--

CREATE TABLE `workouts` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `workout_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workouts`
--

INSERT INTO `workouts` (`id`, `user_id`, `workout_date`, `created_at`) VALUES
(1, 1, '2026-04-04', '2026-04-04 09:03:15'),
(2, 2, '2026-04-04', '2026-04-04 09:28:03'),
(3, 2, '2026-04-01', '2026-04-04 10:05:56');

-- --------------------------------------------------------

--
-- Table structure for table `workout_entries`
--

CREATE TABLE `workout_entries` (
  `id` int NOT NULL,
  `workout_id` int DEFAULT NULL,
  `exercise_id` int DEFAULT NULL,
  `sets` int DEFAULT NULL,
  `reps` int DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `workout_entries`
--

INSERT INTO `workout_entries` (`id`, `workout_id`, `exercise_id`, `sets`, `reps`, `weight`) VALUES
(1, 1, 1, 3, 10, 15.00),
(2, 2, 3, 3, 8, 20.00),
(3, 2, 3, 3, 6, 60.00),
(4, 2, 5, 3, 10, 20.00),
(5, 3, 1, 3, 10, 10.00),
(6, 3, 2, 3, 8, 15.00),
(7, 3, 3, 3, 6, 20.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `exercises`
--
ALTER TABLE `exercises`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workouts`
--
ALTER TABLE `workouts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `workout_entries`
--
ALTER TABLE `workout_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workout_id` (`workout_id`),
  ADD KEY `exercise_id` (`exercise_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `exercises`
--
ALTER TABLE `exercises`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `workouts`
--
ALTER TABLE `workouts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `workout_entries`
--
ALTER TABLE `workout_entries`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `workouts`
--
ALTER TABLE `workouts`
  ADD CONSTRAINT `workouts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `workout_entries`
--
ALTER TABLE `workout_entries`
  ADD CONSTRAINT `workout_entries_ibfk_1` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `workout_entries_ibfk_2` FOREIGN KEY (`exercise_id`) REFERENCES `exercises` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
