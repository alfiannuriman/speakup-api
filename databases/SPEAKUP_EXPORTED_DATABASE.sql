-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 19, 2021 at 09:05 AM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.3.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kuliah_android_sosmed`
--

-- --------------------------------------------------------

--
-- Table structure for table `act_users`
--

CREATE TABLE `act_users` (
  `user_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(125) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_status_id` int(11) NOT NULL,
  `create_dtm` datetime NOT NULL,
  `active_dtm` datetime DEFAULT NULL,
  `terminate_dtm` datetime DEFAULT NULL,
  `attempts` tinyint(3) DEFAULT 0,
  `token` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `act_user_detail`
--

CREATE TABLE `act_user_detail` (
  `detail_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `telepon` varchar(100) DEFAULT NULL,
  `gender` varchar(1) DEFAULT 'M',
  `birth_date` date NOT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `update_dtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `act_user_follows`
--

CREATE TABLE `act_user_follows` (
  `user_follow_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL COMMENT 'user_id yang jadi subject',
  `object_id` int(11) NOT NULL COMMENT 'user_id yang jadi objectnya',
  `start_dtm` datetime NOT NULL,
  `end_dtm` datetime DEFAULT NULL,
  `update_dtm` datetime NOT NULL,
  `update_by` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0 COMMENT '0=allow, 1=block',
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '0=reguler, 1=close friend'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `act_user_status`
--

CREATE TABLE `act_user_status` (
  `act_user_status_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `act_user_status`
--

INSERT INTO `act_user_status` (`act_user_status_id`, `name`) VALUES
(1, 'Active'),
(2, 'Inactive'),
(3, 'Banned');

-- --------------------------------------------------------

--
-- Table structure for table `post_article`
--

CREATE TABLE `post_article` (
  `article_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `scope` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 = all; 1 = close friend',
  `status_id` tinyint(2) NOT NULL DEFAULT 1 COMMENT '1=published, 2=deleted, 3=archive',
  `published_dtm` datetime DEFAULT NULL,
  `created_dtm` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `last_update_dtm` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post_article_comment`
--

CREATE TABLE `post_article_comment` (
  `article_comment_id` int(11) NOT NULL,
  `parent_comment_id` int(11) DEFAULT NULL,
  `article_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_by` int(11) NOT NULL,
  `comment_dtm` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=active, 1=delete',
  `update_dtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post_article_counter`
--

CREATE TABLE `post_article_counter` (
  `article_counter_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `view_by` int(11) NOT NULL,
  `num_viewed` int(11) NOT NULL DEFAULT 0,
  `num_commented` int(11) NOT NULL,
  `read_confirm_dtm` datetime DEFAULT NULL,
  `liked` int(1) NOT NULL DEFAULT 0 COMMENT '0=normal; 1=like'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post_article_counter_sumary`
--

CREATE TABLE `post_article_counter_sumary` (
  `article_counter_summary_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `num_viewed` int(11) NOT NULL DEFAULT 0,
  `num_commented` int(11) NOT NULL DEFAULT 0,
  `num_liked` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post_article_media`
--

CREATE TABLE `post_article_media` (
  `post_article_media_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `post_article_subjects`
--

CREATE TABLE `post_article_subjects` (
  `article_subject_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL COMMENT 'person/employee id',
  `added_by` int(11) NOT NULL,
  `added_dtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `act_users`
--
ALTER TABLE `act_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `act_user_detail`
--
ALTER TABLE `act_user_detail`
  ADD PRIMARY KEY (`detail_id`);

--
-- Indexes for table `act_user_follows`
--
ALTER TABLE `act_user_follows`
  ADD PRIMARY KEY (`user_follow_id`);

--
-- Indexes for table `act_user_status`
--
ALTER TABLE `act_user_status`
  ADD PRIMARY KEY (`act_user_status_id`);

--
-- Indexes for table `post_article`
--
ALTER TABLE `post_article`
  ADD PRIMARY KEY (`article_id`);

--
-- Indexes for table `post_article_comment`
--
ALTER TABLE `post_article_comment`
  ADD PRIMARY KEY (`article_comment_id`),
  ADD KEY `fk_kwlcmt_knowledge_idx` (`article_id`) USING BTREE;

--
-- Indexes for table `post_article_counter`
--
ALTER TABLE `post_article_counter`
  ADD PRIMARY KEY (`article_counter_id`),
  ADD KEY `fk_arctr_article_idx` (`article_id`) USING BTREE;

--
-- Indexes for table `post_article_counter_sumary`
--
ALTER TABLE `post_article_counter_sumary`
  ADD PRIMARY KEY (`article_counter_summary_id`),
  ADD KEY `fk_kcs_knowledge_idx` (`article_id`) USING BTREE;

--
-- Indexes for table `post_article_media`
--
ALTER TABLE `post_article_media`
  ADD PRIMARY KEY (`post_article_media_id`);

--
-- Indexes for table `post_article_subjects`
--
ALTER TABLE `post_article_subjects`
  ADD PRIMARY KEY (`article_subject_id`),
  ADD KEY `fk_knwsubj_knowledge_idx` (`article_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `act_users`
--
ALTER TABLE `act_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `act_user_detail`
--
ALTER TABLE `act_user_detail`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `act_user_follows`
--
ALTER TABLE `act_user_follows`
  MODIFY `user_follow_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `act_user_status`
--
ALTER TABLE `act_user_status`
  MODIFY `act_user_status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `post_article`
--
ALTER TABLE `post_article`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_article_comment`
--
ALTER TABLE `post_article_comment`
  MODIFY `article_comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_article_counter`
--
ALTER TABLE `post_article_counter`
  MODIFY `article_counter_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_article_counter_sumary`
--
ALTER TABLE `post_article_counter_sumary`
  MODIFY `article_counter_summary_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_article_media`
--
ALTER TABLE `post_article_media`
  MODIFY `post_article_media_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `post_article_subjects`
--
ALTER TABLE `post_article_subjects`
  MODIFY `article_subject_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
