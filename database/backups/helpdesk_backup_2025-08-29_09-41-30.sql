-- Helpdesk Database Backup
-- Generated on: 2025-08-29 09:41:30


-- Table structure for table `comments`
DROP TABLE IF EXISTS `comments`;
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

-- Dumping data for table `comments`
INSERT INTO `comments` VALUES ('1','3','4','0','comment','2019-05-31 16:54:56','2019-05-31 16:54:56');
INSERT INTO `comments` VALUES ('2','2','1','0','comment on ticket','2019-05-31 16:57:19','2019-05-31 16:57:19');
INSERT INTO `comments` VALUES ('3','3','4','0','test comment','2019-06-03 19:59:16','2019-06-03 19:59:16');
INSERT INTO `comments` VALUES ('4','3','4','0','test ticket comment','2019-06-03 19:59:43','2019-06-03 19:59:43');
INSERT INTO `comments` VALUES ('5','10','4','0','ddmo','2023-03-20 10:01:34','2023-03-20 10:01:34');


-- Table structure for table `requester`
DROP TABLE IF EXISTS `requester`;
CREATE TABLE `requester` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `room` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table `requester`
INSERT INTO `requester` VALUES ('31','mofiqul','2019-05-19 16:24:08','2019-05-19 16:24:08',NULL);
INSERT INTO `requester` VALUES ('32','mofiqul','2019-05-19 16:45:22','2019-05-19 16:45:22',NULL);
INSERT INTO `requester` VALUES ('33','mofiqul','2019-05-19 16:46:01','2019-05-19 16:46:01',NULL);
INSERT INTO `requester` VALUES ('34','mofiqul','2019-05-19 16:46:27','2019-05-19 16:46:27',NULL);
INSERT INTO `requester` VALUES ('35','mofiqul','2019-05-19 16:47:51','2019-05-19 16:47:51',NULL);
INSERT INTO `requester` VALUES ('36','mofiqul','2019-05-19 16:48:31','2019-05-19 16:48:31',NULL);
INSERT INTO `requester` VALUES ('37','mofiqul','2019-05-19 16:48:37','2019-05-19 16:48:37',NULL);
INSERT INTO `requester` VALUES ('38','mofiqul','2019-05-19 16:51:05','2019-05-19 16:51:05',NULL);
INSERT INTO `requester` VALUES ('39','injamul ','2019-05-23 20:18:25','2019-05-23 20:18:25',NULL);
INSERT INTO `requester` VALUES ('40','injamul ','2019-05-30 16:55:17','2019-05-30 16:55:17',NULL);
INSERT INTO `requester` VALUES ('41','test','2019-06-07 05:07:43','2019-06-07 05:07:43',NULL);
INSERT INTO `requester` VALUES ('42','test ticket','2019-06-07 05:11:23','2019-06-07 05:11:23',NULL);
INSERT INTO `requester` VALUES ('43','test123','2019-06-07 09:51:33','2019-06-07 09:51:33',NULL);
INSERT INTO `requester` VALUES ('44','test ticket','2019-06-07 09:52:04','2019-06-07 09:52:04',NULL);
INSERT INTO `requester` VALUES ('45','demo ticket','2023-03-20 09:57:25','2023-03-20 09:57:25',NULL);
INSERT INTO `requester` VALUES ('46','demo','2023-03-20 14:11:23','2023-03-20 14:11:23',NULL);
INSERT INTO `requester` VALUES ('47','june katunge','2025-07-28 09:15:31','2025-07-28 09:15:31','2281234567');
INSERT INTO `requester` VALUES ('48','june katunge','2025-07-28 09:15:46','2025-07-28 09:15:46','2281234567');
INSERT INTO `requester` VALUES ('49','june katunge','2025-07-28 09:16:38','2025-07-28 09:16:38','2281234567');
INSERT INTO `requester` VALUES ('50','oscar','2025-08-26 21:38:07','2025-08-26 21:38:07',NULL);
INSERT INTO `requester` VALUES ('51','jine','2025-08-26 21:48:09','2025-08-26 21:48:09',NULL);
INSERT INTO `requester` VALUES ('52','jine','2025-08-26 21:48:23','2025-08-26 21:48:23',NULL);
INSERT INTO `requester` VALUES ('53','jine','2025-08-26 22:09:30','2025-08-26 22:09:30',NULL);
INSERT INTO `requester` VALUES ('54','jine','2025-08-26 22:09:44','2025-08-26 22:09:44',NULL);
INSERT INTO `requester` VALUES ('55','jine','2025-08-26 22:10:03','2025-08-26 22:10:03',NULL);
INSERT INTO `requester` VALUES ('56','jine','2025-08-26 22:11:23','2025-08-26 22:11:23',NULL);
INSERT INTO `requester` VALUES ('57','jine','2025-08-26 22:15:26','2025-08-26 22:15:26',NULL);
INSERT INTO `requester` VALUES ('58','katunge','2025-08-26 22:16:35','2025-08-26 22:16:35',NULL);


-- Table structure for table `settings`
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `settings`
INSERT INTO `settings` VALUES ('1','site_name','Helpdesk System','2025-08-28 15:12:14');
INSERT INTO `settings` VALUES ('2','admin_email','johndoe@helpdesk.com','2025-08-28 15:12:14');
INSERT INTO `settings` VALUES ('3','timezone','UTC','2025-08-28 15:12:14');
INSERT INTO `settings` VALUES ('4','maintenance_mode','0','2025-08-28 15:12:14');


-- Table structure for table `team`
DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table `team`
INSERT INTO `team` VALUES ('1','Server','2019-05-19 12:49:15','2019-05-19 12:49:15');
INSERT INTO `team` VALUES ('2','Devops','2019-05-19 12:49:15','2019-05-19 12:49:15');
INSERT INTO `team` VALUES ('3','injamul ','2019-05-23 22:16:36','2019-05-23 22:16:36');
INSERT INTO `team` VALUES ('4','Support A','2025-07-31 09:41:18','2025-07-31 09:41:18');
INSERT INTO `team` VALUES ('5','IT Operations','2025-08-22 10:51:34','2025-08-22 10:51:34');
INSERT INTO `team` VALUES ('6','IT Operations','2025-08-22 11:21:14','2025-08-22 11:21:14');
INSERT INTO `team` VALUES ('7','network ','2025-08-22 11:55:40','2025-08-22 11:55:40');


-- Table structure for table `team_member`
DROP TABLE IF EXISTS `team_member`;
CREATE TABLE `team_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `team_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table `team_member`
INSERT INTO `team_member` VALUES ('1','1','2019-05-19 18:08:37','2019-05-19 18:08:37','0');
INSERT INTO `team_member` VALUES ('4','4','2019-05-30 14:45:10','2019-05-30 14:45:10','0');
INSERT INTO `team_member` VALUES ('5','4','2019-05-30 14:46:15','2019-05-30 14:46:15','0');
INSERT INTO `team_member` VALUES ('6','4','2019-05-30 14:47:53','2019-05-30 14:47:53','0');
INSERT INTO `team_member` VALUES ('7','2','2019-05-30 14:51:38','2019-05-30 14:51:38','0');
INSERT INTO `team_member` VALUES ('9','4','2019-05-31 10:35:45','2019-05-31 10:35:45','0');
INSERT INTO `team_member` VALUES ('10','1','2025-07-31 09:41:33','2025-07-31 09:41:33','0');
INSERT INTO `team_member` VALUES ('11','4','2025-08-21 14:39:26','2025-08-21 14:39:26','0');
INSERT INTO `team_member` VALUES ('12','4','2025-08-22 13:31:07','2025-08-22 13:31:07','7');
INSERT INTO `team_member` VALUES ('13','3','2025-08-22 13:31:12','2025-08-22 13:31:12','7');


-- Table structure for table `ticket`
DROP TABLE IF EXISTS `ticket`;
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
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table `ticket`
INSERT INTO `ticket` VALUES ('1','subject','thi ','36','2','3','closed','low','0','2019-05-19 16:48:31',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('2','subject','thi ','37','2','1','solved','low','0','2019-05-19 16:48:37',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('4','test','this is a comment','39','2','4','open','low','0','2019-05-23 20:18:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('5','test','hfg','40','1','1','pending','high','0','2019-05-30 16:55:17',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('6','abcd','no comment','41','3','4','open','low','0','2019-06-07 05:07:43',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('8','abcd','abcd','43','1','4','open','low','0','2019-06-07 09:51:33',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('9','no subject','abcd','44','1','4','open','high','0','2019-06-07 09:52:04',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('10','demo subject','se','45','2','9','closed','low','0','2023-03-20 09:57:25',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('11','demo subject','demo comment','46','1','4','solved','medium','0','2023-03-20 14:11:23',NULL,NULL,NULL,NULL,NULL,NULL,NULL);
INSERT INTO `ticket` VALUES ('12','Test','test body','1','1',NULL,'open','low','0','2025-07-18 12:51:34',NULL,NULL,NULL,NULL,'A102',NULL,NULL);
INSERT INTO `ticket` VALUES ('13','Printer Troubleshoot','test','48','2','','open','low','0','2025-07-28 09:15:46',NULL,NULL,'bima','pensions','2281234567','hardware','test\r\n');
INSERT INTO `ticket` VALUES ('14','Printer Troubleshoot','test','49','2','','open','medium','0','2025-07-28 09:16:38',NULL,NULL,'bima','pensions','2281234567','hardware','test');
INSERT INTO `ticket` VALUES ('15','Printer Troubleshoot','broken printer','50','4','','open','high','0','2025-08-26 21:38:07',NULL,NULL,'tnt','accounts','21','hardware','');
INSERT INTO `ticket` VALUES ('16','Printer Troubleshoot','broken','51','4','','open','low','0','2025-08-26 21:48:09',NULL,NULL,'tnt','accounts','3','hardware','');
INSERT INTO `ticket` VALUES ('17','Printer Troubleshoot','broken','52','4','','open','low','0','2025-08-26 21:48:23',NULL,NULL,'tnt','accounts','3','hardware','');
INSERT INTO `ticket` VALUES ('18','Printer Troubleshoot','rr','53','4','','open','low','0','2025-08-26 22:09:30',NULL,NULL,'tnt','accounts','21','hardware','');
INSERT INTO `ticket` VALUES ('19','Printer Troubleshoot','rr','54','4','','open','low','0','2025-08-26 22:09:44',NULL,NULL,'tnt','accounts','21','hardware','');
INSERT INTO `ticket` VALUES ('20','Printer Troubleshoot','rr','55','4','','open','low','0','2025-08-26 22:10:03',NULL,NULL,'tnt','accounts','21','hardware','');
INSERT INTO `ticket` VALUES ('21','Printer Troubleshoot','ff','56','7','','open','medium','0','2025-08-26 22:11:23',NULL,NULL,'tnt','accounts','21','software','');
INSERT INTO `ticket` VALUES ('22','Printer Troubleshoot','ff','57','7','','open','medium','0','2025-08-26 22:15:26',NULL,NULL,'tnt','accounts','21','software','');
INSERT INTO `ticket` VALUES ('23','antivirus','expired','58','4','','open','urgent','0','2025-08-26 22:16:35',NULL,NULL,'tnt','procurement','4','software','');


-- Table structure for table `ticket_event`
DROP TABLE IF EXISTS `ticket_event`;
CREATE TABLE `ticket_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket` int(11) NOT NULL,
  `user` int(11) NOT NULL,
  `body` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table `ticket_event`
INSERT INTO `ticket_event` VALUES ('1','4','1','Ticket created','2019-05-23 20:18:25','2019-05-23 20:18:25');
INSERT INTO `ticket_event` VALUES ('2','5','1','Ticket created','2019-05-30 16:55:17','2019-05-30 16:55:17');
INSERT INTO `ticket_event` VALUES ('3','6','1','Ticket created','2019-06-07 05:07:43','2019-06-07 05:07:43');
INSERT INTO `ticket_event` VALUES ('4','7','1','Ticket created','2019-06-07 05:11:23','2019-06-07 05:11:23');
INSERT INTO `ticket_event` VALUES ('5','8','4','Ticket created','2019-06-07 09:51:33','2019-06-07 09:51:33');
INSERT INTO `ticket_event` VALUES ('6','9','4','Ticket created','2019-06-07 09:52:04','2019-06-07 09:52:04');
INSERT INTO `ticket_event` VALUES ('7','10','1','Ticket created','2023-03-20 09:57:25','2023-03-20 09:57:25');
INSERT INTO `ticket_event` VALUES ('8','11','1','Ticket created','2023-03-20 14:11:23','2023-03-20 14:11:23');
INSERT INTO `ticket_event` VALUES ('9','13','1','Ticket created','2025-07-28 09:15:46','2025-07-28 09:15:46');
INSERT INTO `ticket_event` VALUES ('10','14','1','Ticket created','2025-07-28 09:16:38','2025-07-28 09:16:38');
INSERT INTO `ticket_event` VALUES ('11','15','1','Ticket created','2025-08-26 21:38:07','2025-08-26 21:38:07');
INSERT INTO `ticket_event` VALUES ('12','16','1','Ticket created','2025-08-26 21:48:09','2025-08-26 21:48:09');
INSERT INTO `ticket_event` VALUES ('13','17','1','Ticket created','2025-08-26 21:48:23','2025-08-26 21:48:23');
INSERT INTO `ticket_event` VALUES ('14','18','1','Ticket created','2025-08-26 22:09:30','2025-08-26 22:09:30');
INSERT INTO `ticket_event` VALUES ('15','19','1','Ticket created','2025-08-26 22:09:44','2025-08-26 22:09:44');
INSERT INTO `ticket_event` VALUES ('16','20','1','Ticket created','2025-08-26 22:10:03','2025-08-26 22:10:03');
INSERT INTO `ticket_event` VALUES ('17','21','1','Ticket created','2025-08-26 22:11:23','2025-08-26 22:11:23');
INSERT INTO `ticket_event` VALUES ('18','22','1','Ticket created','2025-08-26 22:15:26','2025-08-26 22:15:26');
INSERT INTO `ticket_event` VALUES ('19','23','1','Ticket created','2025-08-26 22:16:35','2025-08-26 22:16:35');


-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(256) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'member',
  `avatar` varchar(150) DEFAULT NULL,
  `last_password` varchar(256) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `room` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Dumping data for table `users`
INSERT INTO `users` VALUES ('1','John Doe','johndoe@helpdesk.com','$2y$10$PHXjdcPjksokkGryfqK.WePBgiQB30Gw.ytYBHdmGtqtoGtVHtAm.','admin',NULL,'$2y$10$PHXjdcPjksokkGryfqK.WePBgiQB30Gw.ytYBHdmGtqtoGtVHtAm.','2025-08-28 15:13:41','2019-05-19 12:01:34','240');
INSERT INTO `users` VALUES ('3','injamul ','johndoe@helpdesk.com','$2y$10$6N4gbdypYQvRkU2ke9Q1f.Gm4fcGY/PEpv2rSB77wiSLZaOy8kq5i','member',NULL,'$2y$10$6N4gbdypYQvRkU2ke9Q1f.Gm4fcGY/PEpv2rSB77wiSLZaOy8kq5i','2023-03-20 10:16:07','2019-05-24 10:58:53',NULL);
INSERT INTO `users` VALUES ('4','Alex','kangkan@email.com','$2y$10$Q0rxoFO4fSrcdp58CO0RNOSDP7znVc9eGY6Z4xjQ8MTLHYhx0TF.6','member',NULL,'$2y$10$Q0rxoFO4fSrcdp58CO0RNOSDP7znVc9eGY6Z4xjQ8MTLHYhx0TF.6','2023-03-20 09:36:52','2019-05-30 11:49:22',NULL);
INSERT INTO `users` VALUES ('5','june katunge','jkatunge13@gmail.com','$2y$10$8eDJX3t18Ah5r4D0aqCJW.LbvvgZj2ZRg.aWUFReK.fGipEDpzQoa','member',NULL,'$2y$10$8eDJX3t18Ah5r4D0aqCJW.LbvvgZj2ZRg.aWUFReK.fGipEDpzQoa','2025-08-26 09:47:45','2025-08-26 09:47:45','0798739956');
INSERT INTO `users` VALUES ('6','Duncan Maina','jkatunge13@gmail.com','$2y$10$SQL1SRUYFiT1Vnbon1mz6.qh69fZlHRxuHNYa40I1Jkd/bZEceAHC','member',NULL,'$2y$10$SQL1SRUYFiT1Vnbon1mz6.qh69fZlHRxuHNYa40I1Jkd/bZEceAHC','2025-08-26 09:52:49','2025-08-26 09:52:49','240');

