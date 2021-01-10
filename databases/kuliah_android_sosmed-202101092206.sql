-- MySQL dump 10.13  Distrib 5.5.62, for Win64 (AMD64)
--
-- Host: localhost    Database: kuliah_android_sosmed
-- ------------------------------------------------------
-- Server version	5.5.5-10.1.38-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `act_user_detail`
--

DROP TABLE IF EXISTS `act_user_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_user_detail` (
  `detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `telepon` varchar(100) DEFAULT NULL,
  `gender` varchar(1) DEFAULT 'M',
  `birth_date` date NOT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `update_dtm` datetime NOT NULL,
  PRIMARY KEY (`detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `act_user_detail`
--

LOCK TABLES `act_user_detail` WRITE;
/*!40000 ALTER TABLE `act_user_detail` DISABLE KEYS */;
/*!40000 ALTER TABLE `act_user_detail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `act_user_follows`
--

DROP TABLE IF EXISTS `act_user_follows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_user_follows` (
  `user_follow_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_id` int(11) NOT NULL COMMENT 'user_id yang jadi subject',
  `object_id` int(11) NOT NULL COMMENT 'user_id yang jadi objectnya',
  `start_dtm` datetime NOT NULL,
  `end_dtm` datetime DEFAULT NULL,
  `update_dtm` datetime NOT NULL,
  `update_by` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0' COMMENT '0=allow, 1=block',
  `type` int(11) NOT NULL DEFAULT '0' COMMENT '0=reguler, 1=close friend',
  PRIMARY KEY (`user_follow_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `act_user_follows`
--

LOCK TABLES `act_user_follows` WRITE;
/*!40000 ALTER TABLE `act_user_follows` DISABLE KEYS */;
/*!40000 ALTER TABLE `act_user_follows` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `act_users`
--

DROP TABLE IF EXISTS `act_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_users` (
  `user_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(125) NOT NULL,
  `password` varchar(60) NOT NULL,
  `user_status_id` int(11) NOT NULL,
  `create_dtm` datetime NOT NULL,
  `active_dtm` datetime DEFAULT NULL,
  `terminate_dtm` datetime DEFAULT NULL,
  `attempts` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `act_users`
--

LOCK TABLES `act_users` WRITE;
/*!40000 ALTER TABLE `act_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `act_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_article`
--

DROP TABLE IF EXISTS `post_article`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_article` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `scope` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = all; 1 = close friend',
  `status_id` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1=published, 2=deleted, 3=archive',
  `published_dtm` datetime DEFAULT NULL,
  `created_dtm` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `last_update_dtm` datetime DEFAULT NULL,
  PRIMARY KEY (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_article`
--

LOCK TABLES `post_article` WRITE;
/*!40000 ALTER TABLE `post_article` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_article` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_article_comment`
--

DROP TABLE IF EXISTS `post_article_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_article_comment` (
  `article_comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_comment_id` int(11) DEFAULT NULL,
  `article_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `comment_by` int(11) NOT NULL,
  `comment_dtm` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0=active, 1=delete',
  `update_dtm` datetime NOT NULL,
  PRIMARY KEY (`article_comment_id`),
  KEY `fk_kwlcmt_knowledge_idx` (`article_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_article_comment`
--

LOCK TABLES `post_article_comment` WRITE;
/*!40000 ALTER TABLE `post_article_comment` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_article_comment` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_article_counter`
--

DROP TABLE IF EXISTS `post_article_counter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_article_counter` (
  `article_counter_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `view_by` int(11) NOT NULL,
  `num_viewed` int(11) NOT NULL DEFAULT '0',
  `num_commented` int(11) NOT NULL,
  `read_confirm_dtm` datetime DEFAULT NULL,
  `liked` int(1) NOT NULL DEFAULT '0' COMMENT '0=normal; 1=like',
  PRIMARY KEY (`article_counter_id`),
  KEY `fk_arctr_article_idx` (`article_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_article_counter`
--

LOCK TABLES `post_article_counter` WRITE;
/*!40000 ALTER TABLE `post_article_counter` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_article_counter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_article_counter_sumary`
--

DROP TABLE IF EXISTS `post_article_counter_sumary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_article_counter_sumary` (
  `article_counter_summary_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `num_viewed` int(11) NOT NULL DEFAULT '0',
  `num_commented` int(11) NOT NULL DEFAULT '0',
  `num_liked` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`article_counter_summary_id`),
  KEY `fk_kcs_knowledge_idx` (`article_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_article_counter_sumary`
--

LOCK TABLES `post_article_counter_sumary` WRITE;
/*!40000 ALTER TABLE `post_article_counter_sumary` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_article_counter_sumary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post_article_subjects`
--

DROP TABLE IF EXISTS `post_article_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `post_article_subjects` (
  `article_subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL COMMENT 'person/employee id',
  `added_by` int(11) NOT NULL,
  `added_dtm` datetime NOT NULL,
  PRIMARY KEY (`article_subject_id`),
  KEY `fk_knwsubj_knowledge_idx` (`article_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post_article_subjects`
--

LOCK TABLES `post_article_subjects` WRITE;
/*!40000 ALTER TABLE `post_article_subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `post_article_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'kuliah_android_sosmed'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-01-09 22:06:32
