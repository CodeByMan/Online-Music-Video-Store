-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 14, 2025 at 12:38 AM
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
-- Database: `sounds`
--

-- --------------------------------------------------------

--
-- Table structure for table `audio`
--

CREATE TABLE `audio` (
  `audio_id` int(11) NOT NULL,
  `audio_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audio`
--

INSERT INTO `audio` (`audio_id`, `audio_name`, `category_id`) VALUES
(28, 'Lm3allem.mp3', 30),
(29, 'Guli Mata.mp3', 31),
(30, 'Baddek Eih.mp3', 32),
(34, 'Enty Hayaty.mp3', 34),
(35, 'Gangnam Style.mp3', 35),
(36, 'Gentleman.mp3', 36),
(37, 'Bad.mp3', 37),
(38, 'Beat It.mp3', 38),
(39, 'Dangerous.mp3', 39),
(40, 'Shape Of You.mp3', 40),
(41, 'Bloodstream.mp3', 41),
(42, 'Perfect.mp3', 40),
(43, 'Photograph.mp3', 41),
(44, 'Closer.mp3', 42),
(45, 'Despacito.mp3', 43),
(46, 'Count On Me.mp3', 44),
(47, 'Thats What I Like.mp3', 45),
(48, 'Uptown Funk.mp3', 46),
(49, 'Baby.mp3', 47),
(50, 'Boyfriend.mp3', 48),
(51, 'Yummy.mp3', 49);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_artist` varchar(255) NOT NULL,
  `category_album` varchar(255) NOT NULL,
  `category_genre` varchar(255) NOT NULL,
  `category_language` varchar(255) NOT NULL,
  `category_year` int(11) NOT NULL,
  `category_image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_artist`, `category_album`, `category_genre`, `category_language`, `category_year`, `category_image`) VALUES
(30, 'Saad Lamjarred', 'LM3ALLEM', 'Pop', 'Arabic', 2015, 'Lm3allem.jpg'),
(31, 'Saad Lamjarred', 'Guli Mata', 'Romance', 'Arabic', 2023, 'Guli Mata.jpg'),
(32, 'Saad Lamjarred', 'Baddek Eih', 'Pop', 'Arabic', 2018, 'Baddek Eih.jpg'),
(34, 'Saad Lamjarred', 'Enty Hayaty', 'Pop', 'Arabic', 2021, 'Enty Hayaty.jpg'),
(35, 'Psy', 'Psy 6 (Six Rules)', 'K-Pop', 'Korean', 2012, 'Gangnam Style.png'),
(36, 'Psy', 'Gentleman', 'K-Pop', 'Korean', 2013, 'Gentleman.jpg'),
(37, 'Michael Jackson', 'Bad', 'Dance-Pop', 'English', 1987, 'Bad.jpg'),
(38, 'Michael Jackson', 'Thriller', 'Dance-Pop', 'English', 1982, 'Beat It.jpg'),
(39, 'Michael Jackson', 'Studio Album', 'Pop-Music', 'English', 1991, 'Dangerous.jpg'),
(40, 'Ed Sheeran', '÷', 'Pop-Music', 'English', 2017, 'Shape Of You.jpg'),
(41, 'Ed Sheeran', 'x', 'Pop', 'English', 2014, 'bloodstream.jpg'),
(42, 'The Chainsmokers', 'Memories...Do Not Open', 'Pop', 'English', 2017, 'closer.jpg'),
(43, ' Luis Fonsi', 'Despacito', 'Pop', 'Spanish', 2017, 'descacito.png'),
(44, 'Bruno Mars', 'Doo-Wops & Hooligans', 'Folk', 'English', 2010, 'count.jpg'),
(45, 'Bruno Mars', '24K Magic', 'Hip-Hop', 'English', 2016, '24k.jpg'),
(46, 'Bruno Mars', 'Uptown Special', 'Funk', 'English', 2014, 'funk.jpg'),
(47, 'Justin Bieber', 'Studio Album', 'Pop', 'English', 2010, 'baby.jpg'),
(48, 'Justin Bieber', 'Studio Album', 'Pop', 'English', 2012, 'boyfriend.jpg'),
(49, 'Justin Bieber', 'Changes', 'Pop', 'English', 2020, 'yummy.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `message_name` varchar(255) NOT NULL,
  `message_email` varchar(255) NOT NULL,
  `message_subject` varchar(255) NOT NULL,
  `message_detail` varchar(255) NOT NULL,
  `message_status` enum('pending','seen') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `message_name`, `message_email`, `message_subject`, `message_detail`, `message_status`) VALUES
(46, 'Irbaz', 'irbaz@gmail.com', 'Hello', 'Call me!', 'pending'),
(47, 'Ali', 'ali@ali.com', 'Email login error', 'Help', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `review_audio`
--

CREATE TABLE `review_audio` (
  `review_id` int(11) NOT NULL,
  `review_text` varchar(255) NOT NULL,
  `review_rating` varchar(255) NOT NULL,
  `audio_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_audio`
--

INSERT INTO `review_audio` (`review_id`, `review_text`, `review_rating`, `audio_id`, `user_id`, `review_date`) VALUES
(32, 'Feeling Relaxed :)', '5', 50, 2, '2025-02-13 05:49:06');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_type`) VALUES
(1, 'Administrator'),
(2, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `user_phone` varchar(255) NOT NULL,
  `user_address` varchar(255) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_image` varchar(255) NOT NULL DEFAULT 'vector.jpg',
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `user_name`, `user_phone`, `user_address`, `user_email`, `user_password`, `user_image`, `role_id`) VALUES
(1, 'Admin', '1234567890', 'Karachi', 'admin@admin.com', '$2y$10$YATESuqBmRBoxBMz53Xmd.Zaqx5fchswkef/W8L43DFAJliLgzGaW', 'vector.jpg', 1),
(2, 'Ali Nawaz', '1234567890', ' Karachi', 'ali@ali.com', '$2y$10$bjKoeCmeRZrul7pE0NH/W.UlB/C7QNf4iEOC0Y3oEH71dRNrMcnV6', 'vector.jpg', 2);

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `video_id` int(11) NOT NULL,
  `video_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`video_id`, `video_name`, `category_id`) VALUES
(13, 'Lm3allem.mp4', 30),
(14, 'Guli Mata.mp4', 31),
(15, 'Gangnam.mp4', 35),
(16, 'Bad.mp4', 37),
(17, 'Despacito.mp4', 43),
(18, 'Closer.mp4', 42),
(19, 'yummy.mp4', 49),
(20, 'Thats What I Like.mp4', 45),
(21, 'funk.mp4', 46),
(22, 'Perfect.mp4', 40),
(23, 'boyfriend.mp4', 48),
(24, 'Baby.mp4', 47);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audio`
--
ALTER TABLE `audio`
  ADD PRIMARY KEY (`audio_id`),
  ADD KEY `category_audio_fk` (`category_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `review_audio`
--
ALTER TABLE `review_audio`
  ADD PRIMARY KEY (`review_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `role_id_fk` (`role_id`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`video_id`),
  ADD KEY `category_video_fk` (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audio`
--
ALTER TABLE `audio`
  MODIFY `audio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `review_audio`
--
ALTER TABLE `review_audio`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `video_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audio`
--
ALTER TABLE `audio`
  ADD CONSTRAINT `category_audio_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `role_id_fk` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `category_video_fk` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
