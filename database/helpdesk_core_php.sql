-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for osx10.10 (x86_64)
--
-- Host: localhost    Database: helpdesk_core_php
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` int(11) NOT NULL,
  `team_member` int(11) NOT NULL,
  `private` int(11) NOT NULL DEFAULT 0,
  `body` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,3,4,0,'comment','2019-05-31 13:54:56','2019-05-31 13:54:56'),(2,2,1,0,'comment on ticket','2019-05-31 13:57:19','2019-05-31 13:57:19'),(3,3,4,0,'test comment','2019-06-03 16:59:16','2019-06-03 16:59:16'),(4,3,4,0,'test ticket comment','2019-06-03 16:59:43','2019-06-03 16:59:43'),(5,10,4,0,'ddmo','2023-03-20 07:01:34','2023-03-20 07:01:34');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `receiver_type` enum('user','requester') NOT NULL DEFAULT 'user',
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('user_created','ticket_created','ticket_assigned','ticket_updated','message_received','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`),
  KEY `type` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'system','Test Notification','This is a test notification',NULL,0,'2026-01-16 09:20:31'),(2,1,'user_created','Test Admin Notification','This is a test admin notification',NULL,0,'2026-01-16 09:20:31'),(3,1,'user_created','New User Created','A new user \'Test User 1768555527\' has been created with role: member',5,0,'2026-01-16 09:25:27'),(4,1,'user_created','New User Created','A new user \'Diana maina\' has been created with role: Member',6,0,'2026-01-16 09:36:35');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requester`
--

DROP TABLE IF EXISTS `requester`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requester` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requester`
--

LOCK TABLES `requester` WRITE;
/*!40000 ALTER TABLE `requester` DISABLE KEYS */;
INSERT INTO `requester` VALUES (31,'mofiqul','example@email.com','9876543210','2019-05-19 13:24:08','2019-05-19 13:24:08'),(32,'mofiqul','example@email.com','9876543210','2019-05-19 13:45:22','2019-05-19 13:45:22'),(33,'mofiqul','example@email.com','9876543210','2019-05-19 13:46:01','2019-05-19 13:46:01'),(34,'mofiqul','example@email.com','9876543210','2019-05-19 13:46:27','2019-05-19 13:46:27'),(35,'mofiqul','example@email.com','9876543210','2019-05-19 13:47:51','2019-05-19 13:47:51'),(36,'mofiqul','example@email.com','9876543210','2019-05-19 13:48:31','2019-05-19 13:48:31'),(37,'mofiqul','example@email.com','9876543210','2019-05-19 13:48:37','2019-05-19 13:48:37'),(38,'mofiqul','example@email.com','9876543210','2019-05-19 13:51:05','2019-05-19 13:51:05'),(39,'injamul ','injamul.haque6@gmail.com','8822677188','2019-05-23 17:18:25','2019-05-23 17:18:25'),(40,'injamul ','injamul.haque6@gmail.com','8822677188','2019-05-30 13:55:17','2019-05-30 13:55:17'),(41,'test','kangkan@email.com','1234567898','2019-06-07 02:07:43','2019-06-07 02:07:43'),(42,'test ticket','johndoe@helpdesk.com','1234567898','2019-06-07 02:11:23','2019-06-07 02:11:23'),(43,'test123','kangkan@email.com','1234567898','2019-06-07 06:51:33','2019-06-07 06:51:33'),(44,'test ticket','johndoe@helpdesk.com','1234567898','2019-06-07 06:52:04','2019-06-07 06:52:04'),(45,'demo ticket','demo@email.com','1234567899','2023-03-20 06:57:25','2023-03-20 06:57:25'),(46,'demo','demo@email.com','1234567899','2023-03-20 11:11:23','2023-03-20 11:11:23');
/*!40000 ALTER TABLE `requester` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team`
--

DROP TABLE IF EXISTS `team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team`
--

LOCK TABLES `team` WRITE;
/*!40000 ALTER TABLE `team` DISABLE KEYS */;
INSERT INTO `team` VALUES (1,'Server','2019-05-19 09:49:15','2019-05-19 09:49:15'),(2,'Devops','2019-05-19 09:49:15','2019-05-19 09:49:15'),(3,'Security','2019-05-23 19:16:36','2026-01-16 09:42:46'),(4,'IT Support Team January 2026','2026-01-16 09:07:27','2026-01-16 09:07:27');
/*!40000 ALTER TABLE `team` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `team_member`
--

DROP TABLE IF EXISTS `team_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `team_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `team` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_member`
--

LOCK TABLES `team_member` WRITE;
/*!40000 ALTER TABLE `team_member` DISABLE KEYS */;
INSERT INTO `team_member` VALUES (1,1,1,'2019-05-19 15:08:37','2019-05-19 15:08:37'),(4,4,2,'2019-05-30 11:45:10','2019-05-30 11:45:10'),(5,4,3,'2019-05-30 11:46:15','2019-05-30 11:46:15'),(6,4,3,'2019-05-30 11:47:53','2019-05-30 11:47:53'),(7,2,3,'2019-05-30 11:51:38','2019-05-30 11:51:38'),(9,4,1,'2019-05-31 07:35:45','2019-05-31 07:35:45'),(10,6,4,'2026-01-16 09:40:23','2026-01-16 09:40:23');
/*!40000 ALTER TABLE `team_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket`
--

DROP TABLE IF EXISTS `ticket`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `requester` int(11) NOT NULL,
  `team` int(11) DEFAULT NULL,
  `team_member` varchar(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `priority` varchar(20) NOT NULL DEFAULT 'low',
  `rating` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` varchar(50) DEFAULT NULL,
  `deleted_at` varchar(50) DEFAULT NULL,
  `building` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `room` varchar(20) DEFAULT NULL,
  `category` enum('hardware','software','network') DEFAULT NULL,
  `additional_info` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket`
--

LOCK TABLES `ticket` WRITE;
/*!40000 ALTER TABLE `ticket` DISABLE KEYS */;
INSERT INTO `ticket` VALUES (1,'subject','thi ',36,2,'3','closed','low',0,'2019-05-19 13:48:31',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(2,'subject','thi ',37,2,'1','solved','low',0,'2019-05-19 13:48:37',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(4,'test','this is a comment',39,2,'4','open','low',0,'2019-05-23 17:18:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,'test','hfg',40,1,'1','pending','high',0,'2019-05-30 13:55:17',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(6,'abcd','no comment',41,3,'4','open','low',0,'2019-06-07 02:07:43',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(8,'abcd','abcd',43,1,'4','open','low',0,'2019-06-07 06:51:33',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(9,'no subject','abcd',44,1,'4','open','high',0,'2019-06-07 06:52:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,'demo subject','se',45,2,'9','closed','low',0,'2023-03-20 06:57:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(11,'demo subject','demo comment',46,1,'4','solved','medium',0,'2023-03-20 11:11:23',NULL,NULL,NULL,NULL,NULL,NULL,NULL),(12,'Test','test body',1,1,NULL,'open','low',0,'2025-07-18 09:51:34',NULL,NULL,NULL,NULL,'A102',NULL,NULL);
/*!40000 ALTER TABLE `ticket` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_event`
--

DROP TABLE IF EXISTS `ticket_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ticket_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `body` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_event`
--

LOCK TABLES `ticket_event` WRITE;
/*!40000 ALTER TABLE `ticket_event` DISABLE KEYS */;
INSERT INTO `ticket_event` VALUES (1,4,1,'Ticket created','2019-05-23 17:18:25','2019-05-23 17:18:25'),(2,5,1,'Ticket created','2019-05-30 13:55:17','2019-05-30 13:55:17'),(3,6,1,'Ticket created','2019-06-07 02:07:43','2019-06-07 02:07:43'),(4,7,1,'Ticket created','2019-06-07 02:11:23','2019-06-07 02:11:23'),(5,8,4,'Ticket created','2019-06-07 06:51:33','2019-06-07 06:51:33'),(6,9,4,'Ticket created','2019-06-07 06:52:04','2019-06-07 06:52:04'),(7,10,1,'Ticket created','2023-03-20 06:57:25','2023-03-20 06:57:25'),(8,11,1,'Ticket created','2023-03-20 11:11:23','2023-03-20 11:11:23');
/*!40000 ALTER TABLE `ticket_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(256) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'member',
  `avatar` varchar(150) DEFAULT NULL,
  `last_password` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'John Doe','johndoe@helpdesk.com','8888888888','$2y$10$PHXjdcPjksokkGryfqK.WePBgiQB30Gw.ytYBHdmGtqtoGtVHtAm.','admin',NULL,'$2y$10$PHXjdcPjksokkGryfqK.WePBgiQB30Gw.ytYBHdmGtqtoGtVHtAm.','2023-03-20 07:16:20','2019-05-19 09:01:34'),(3,'injamul ','johndoe@helpdesk.com','1234567899','$2y$10$6N4gbdypYQvRkU2ke9Q1f.Gm4fcGY/PEpv2rSB77wiSLZaOy8kq5i','member',NULL,'$2y$10$6N4gbdypYQvRkU2ke9Q1f.Gm4fcGY/PEpv2rSB77wiSLZaOy8kq5i','2023-03-20 07:16:07','2019-05-24 07:58:53'),(4,'Alex','kangkan@email.com','9999999999','$2y$10$Q0rxoFO4fSrcdp58CO0RNOSDP7znVc9eGY6Z4xjQ8MTLHYhx0TF.6','member',NULL,'$2y$10$Q0rxoFO4fSrcdp58CO0RNOSDP7znVc9eGY6Z4xjQ8MTLHYhx0TF.6','2023-03-20 06:36:52','2019-05-30 08:49:22'),(5,'Test User 1768555527','test1768555527@example.com','0700000000','$2y$10$GfbwfL6mHj7pT3M0YeB2HuCmotGqsHGe5Vnuw3SMQ/yBQQ/7ZhQBa','member',NULL,'$2y$10$GfbwfL6mHj7pT3M0YeB2HuCmotGqsHGe5Vnuw3SMQ/yBQQ/7ZhQBa','2026-01-16 09:25:27','2026-01-16 09:25:27'),(6,'Diana maina','daina@gmail.com','0798739956','$2y$10$P4Of9HQw7qpue6CyQZ.2VOqio.Y0raRmu2yNTRPajIfAy.eBCqW1i','member',NULL,'$2y$10$P4Of9HQw7qpue6CyQZ.2VOqio.Y0raRmu2yNTRPajIfAy.eBCqW1i','2026-01-16 09:36:35','2026-01-16 09:36:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-16 12:58:35
