-- MySQL dump 10.13  Distrib 8.0.44, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: susitiocl_gallery
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `selected`
--

DROP TABLE IF EXISTS `selected`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `selected` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `client` (`client`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `selected`
--

LOCK TABLES `selected` WRITE;
/*!40000 ALTER TABLE `selected` DISABLE KEYS */;
INSERT INTO `selected` VALUES (1,'sfvalsep','test_001.jpg',1,'2025-12-02 05:51:48'),(2,'sfvalsep','test_002.jpg',0,'2025-12-02 05:51:48'),(3,'sfvalsep','test_003.jpg',0,'2025-12-02 05:51:48'),(4,'sfvalsep','test_004.jpg',0,'2025-12-02 05:51:48'),(5,'sfvalsep','test_005.jpg',0,'2025-12-02 05:51:48'),(6,'sfvalsep','test_006.jpg',0,'2025-12-02 05:51:48'),(7,'sfvalsep','test_007.jpg',0,'2025-12-02 05:51:48'),(8,'sfvalsep','test_008.jpg',0,'2025-12-02 05:51:48'),(9,'sfvalsep','test_009.jpg',0,'2025-12-02 05:51:48'),(10,'sfvalsep','test_010.jpg',0,'2025-12-02 05:51:48'),(11,'sfvalsep','test_011.jpg',0,'2025-12-02 05:51:48'),(12,'sfvalsep','test_012.jpg',0,'2025-12-02 05:51:48'),(13,'sfvalsep','test_013.jpg',0,'2025-12-02 05:51:48'),(14,'sfvalsep','test_014.jpg',0,'2025-12-02 05:51:48'),(15,'sfvalsep','test_015.jpg',0,'2025-12-02 05:51:48'),(16,'sfvalsep','test_016.jpg',0,'2025-12-02 05:51:48'),(17,'sfvalsep','test_017.jpg',0,'2025-12-02 05:51:48'),(18,'sfvalsep','test_018.jpg',0,'2025-12-02 05:51:48'),(19,'sfvalsep','test_019.jpg',0,'2025-12-02 05:51:48'),(20,'sfvalsep','test_020.jpg',0,'2025-12-02 05:51:48'),(21,'sfvalsep','test_021.jpg',0,'2025-12-02 05:51:48'),(22,'sfvalsep','test_022.jpg',0,'2025-12-02 05:51:48'),(23,'sfvalsep','test_023.jpg',0,'2025-12-02 05:51:48'),(24,'sfvalsep','test_024.jpg',0,'2025-12-02 05:51:48'),(25,'sfvalsep','test_025.jpg',0,'2025-12-02 05:51:48'),(26,'sfvalsep','test_026.jpg',0,'2025-12-02 05:51:48'),(27,'sfvalsep','test_027.jpg',0,'2025-12-02 05:51:48'),(28,'sfvalsep','test_028.jpg',0,'2025-12-02 05:51:48'),(29,'sfvalsep','test_029.jpg',0,'2025-12-02 05:51:48'),(30,'sfvalsep','test_030.jpg',0,'2025-12-02 05:51:48'),(31,'sfvalsep','test_031.jpg',0,'2025-12-02 05:51:48'),(32,'sfvalsep','test_032.jpg',0,'2025-12-02 05:51:48'),(33,'sfvalsep','test_033.jpg',0,'2025-12-02 05:51:48'),(34,'sfvalsep','test_034.jpg',0,'2025-12-02 05:51:48'),(35,'sfvalsep','test_035.jpg',0,'2025-12-02 05:51:48'),(36,'sfvalsep','test_036.jpg',0,'2025-12-02 05:51:48'),(37,'sfvalsep','test_037.jpg',0,'2025-12-02 05:51:48'),(38,'sfvalsep','test_038.jpg',0,'2025-12-02 05:51:48'),(39,'sfvalsep','test_039.jpg',0,'2025-12-02 05:51:48'),(40,'sfvalsep','test_040.jpg',0,'2025-12-02 05:51:48'),(41,'sfvalsep','test_041.jpg',0,'2025-12-02 05:51:48'),(42,'sfvalsep','test_042.jpg',0,'2025-12-02 05:51:48'),(43,'sfvalsep','test_043.jpg',0,'2025-12-02 05:51:48'),(44,'sfvalsep','test_044.jpg',0,'2025-12-02 05:51:48'),(45,'sfvalsep','test_045.jpg',0,'2025-12-02 05:51:48'),(46,'sfvalsep','test_046.jpg',0,'2025-12-02 05:51:48'),(47,'sfvalsep','test_047.jpg',0,'2025-12-02 05:51:48'),(48,'sfvalsep','test_048.jpg',0,'2025-12-02 05:51:48'),(49,'sfvalsep','test_049.jpg',0,'2025-12-02 05:51:48'),(50,'sfvalsep','test_050.jpg',0,'2025-12-02 05:51:48'),(51,'sfvalsep','test_051.jpg',0,'2025-12-02 05:51:48'),(52,'sfvalsep','test_052.jpg',0,'2025-12-02 05:51:48'),(53,'sfvalsep','test_053.jpg',0,'2025-12-02 05:51:48'),(54,'sfvalsep','test_054.jpg',0,'2025-12-02 05:51:48'),(55,'sfvalsep','test_055.jpg',0,'2025-12-02 05:51:48'),(56,'sfvalsep','test_056.jpg',0,'2025-12-02 05:51:48'),(57,'sfvalsep','test_057.jpg',0,'2025-12-02 05:51:48'),(58,'sfvalsep','test_058.jpg',0,'2025-12-02 05:51:48'),(59,'sfvalsep','test_059.jpg',0,'2025-12-02 05:51:48'),(60,'sfvalsep','test_060.jpg',0,'2025-12-02 05:51:48');
/*!40000 ALTER TABLE `selected` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `skey` varchar(100) NOT NULL,
  `svalue` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `skey` (`skey`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'gallery_name','Galería Base de SuSitio','2025-12-02 05:04:03'),(2,'access_mode','off','2025-12-02 05:04:03'),(3,'gallery_password','','2025-12-02 05:04:03'),(4,'allow_uploads','1','2025-12-02 06:01:11'),(5,'mail_method','sendgrid','2025-12-02 07:12:27'),(6,'sendgrid_key','SG.pfVRklgOT8SlbOJlKMVtCQ.VZ-XdbxfLWVqJoeWAXXd1KVtgqk-Yfh-Sqwwertyuiop','2025-12-02 07:10:56'),(7,'php_upload_max','150M','2025-12-02 06:01:11'),(8,'php_post_max','160M','2025-12-02 06:01:11'),(9,'php_memory_limit','512M','2025-12-02 06:01:11'),(10,'watermark_enabled','1','2025-12-02 06:24:10'),(11,'watermark_file','includes/logo.svg','2025-12-02 06:24:10'),(12,'watermark_conf','{\"leftPercent\":54.05,\"topPercent\":75.42,\"widthPercent\":40.3,\"corner\":\"br\",\"edgePercentX\":4.13,\"edgePercentY\":6.88}','2025-12-02 07:09:47'),(13,'htaccess_proposed','# Generado por admin.php - configuración PHP\nphp_value upload_max_filesize 150M\nphp_value post_max_size 160M\nphp_value memory_limit 512M\n','2025-12-02 06:32:09'),(14,'sendgrid_from','contacto@susitio.cl','2025-12-02 07:10:56'),(15,'sendgrid_from_name','Admin','2025-12-02 07:10:56'),(16,'zip_threshold','5','2025-12-02 07:09:52');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `uploads`
--

DROP TABLE IF EXISTS `uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `uploads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `path` varchar(512) NOT NULL,
  `status` enum('active','deleted','bak') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `uploads`
--

LOCK TABLES `uploads` WRITE;
/*!40000 ALTER TABLE `uploads` DISABLE KEYS */;
INSERT INTO `uploads` VALUES (1,NULL,'test_001.jpg','picsum_1.jpg','image/jpeg',104792,'fotos/test_001.jpg','active','2025-12-02 05:09:05'),(2,NULL,'test_002.jpg','picsum_2.jpg','image/jpeg',72507,'fotos/test_002.jpg','active','2025-12-02 05:09:08'),(3,NULL,'test_003.jpg','picsum_3.jpg','image/jpeg',76911,'fotos/test_003.jpg','active','2025-12-02 05:09:09'),(4,NULL,'test_004.jpg','picsum_4.jpg','image/jpeg',159007,'fotos/test_004.jpg','active','2025-12-02 05:09:12'),(5,NULL,'test_005.jpg','picsum_5.jpg','image/jpeg',96061,'fotos/test_005.jpg','active','2025-12-02 05:09:14'),(6,NULL,'test_006.jpg','picsum_6.jpg','image/jpeg',232990,'fotos/test_006.jpg','active','2025-12-02 05:09:17'),(7,NULL,'test_007.jpg','picsum_7.jpg','image/jpeg',103903,'fotos/test_007.jpg','active','2025-12-02 05:09:18'),(8,NULL,'test_008.jpg','picsum_8.jpg','image/jpeg',65558,'fotos/test_008.jpg','active','2025-12-02 05:09:21'),(9,NULL,'test_009.jpg','picsum_9.jpg','image/jpeg',126185,'fotos/test_009.jpg','active','2025-12-02 05:09:23'),(10,NULL,'test_010.jpg','picsum_10.jpg','image/jpeg',60561,'fotos/test_010.jpg','active','2025-12-02 05:09:26'),(11,NULL,'test_011.jpg','picsum_11.jpg','image/jpeg',54538,'fotos/test_011.jpg','active','2025-12-02 05:09:29'),(12,NULL,'test_012.jpg','picsum_12.jpg','image/jpeg',32363,'fotos/test_012.jpg','active','2025-12-02 05:09:31'),(13,NULL,'test_013.jpg','picsum_13.jpg','image/jpeg',76391,'fotos/test_013.jpg','active','2025-12-02 05:09:33'),(14,NULL,'test_014.jpg','picsum_14.jpg','image/jpeg',223630,'fotos/test_014.jpg','active','2025-12-02 05:09:34'),(15,NULL,'test_015.jpg','picsum_15.jpg','image/jpeg',102711,'fotos/test_015.jpg','active','2025-12-02 05:09:38'),(16,NULL,'test_016.jpg','picsum_16.jpg','image/jpeg',159806,'fotos/test_016.jpg','active','2025-12-02 05:09:41'),(17,NULL,'test_017.jpg','picsum_17.jpg','image/jpeg',73223,'fotos/test_017.jpg','active','2025-12-02 05:09:42'),(18,NULL,'test_018.jpg','picsum_18.jpg','image/jpeg',123075,'fotos/test_018.jpg','active','2025-12-02 05:09:45'),(19,NULL,'test_019.jpg','picsum_19.jpg','image/jpeg',37847,'fotos/test_019.jpg','active','2025-12-02 05:09:48'),(20,NULL,'test_020.jpg','picsum_20.jpg','image/jpeg',126592,'fotos/test_020.jpg','active','2025-12-02 05:09:51'),(21,NULL,'test_021.jpg','picsum_21.jpg','image/jpeg',133127,'fotos/test_021.jpg','active','2025-12-02 05:09:53'),(22,NULL,'test_022.jpg','picsum_22.jpg','image/jpeg',29044,'fotos/test_022.jpg','active','2025-12-02 05:09:54'),(23,NULL,'test_023.jpg','picsum_23.jpg','image/jpeg',177904,'fotos/test_023.jpg','active','2025-12-02 05:09:56'),(24,NULL,'test_024.jpg','picsum_24.jpg','image/jpeg',134777,'fotos/test_024.jpg','active','2025-12-02 05:09:57'),(25,NULL,'test_025.jpg','picsum_25.jpg','image/jpeg',75037,'fotos/test_025.jpg','active','2025-12-02 05:09:59'),(26,NULL,'test_026.jpg','picsum_26.jpg','image/jpeg',170027,'fotos/test_026.jpg','active','2025-12-02 05:10:02'),(27,NULL,'test_027.jpg','picsum_27.jpg','image/jpeg',75744,'fotos/test_027.jpg','active','2025-12-02 05:10:03'),(28,NULL,'test_028.jpg','picsum_28.jpg','image/jpeg',32094,'fotos/test_028.jpg','active','2025-12-02 05:10:06'),(29,NULL,'test_029.jpg','picsum_29.jpg','image/jpeg',76489,'fotos/test_029.jpg','active','2025-12-02 05:10:07'),(30,NULL,'test_030.jpg','picsum_30.jpg','image/jpeg',147810,'fotos/test_030.jpg','active','2025-12-02 05:10:09'),(31,NULL,'test_031.jpg','picsum_31.jpg','image/jpeg',307131,'fotos/test_031.jpg','active','2025-12-02 05:10:10'),(32,NULL,'test_032.jpg','picsum_32.jpg','image/jpeg',204606,'fotos/test_032.jpg','active','2025-12-02 05:10:12'),(33,NULL,'test_033.jpg','picsum_33.jpg','image/jpeg',137346,'fotos/test_033.jpg','active','2025-12-02 05:10:14'),(34,NULL,'test_034.jpg','picsum_34.jpg','image/jpeg',83732,'fotos/test_034.jpg','active','2025-12-02 05:10:16'),(35,NULL,'test_035.jpg','picsum_35.jpg','image/jpeg',85631,'fotos/test_035.jpg','active','2025-12-02 05:10:18'),(36,NULL,'test_036.jpg','picsum_36.jpg','image/jpeg',123984,'fotos/test_036.jpg','active','2025-12-02 05:10:19'),(37,NULL,'test_037.jpg','picsum_37.jpg','image/jpeg',157166,'fotos/test_037.jpg','active','2025-12-02 05:10:22'),(38,NULL,'test_038.jpg','picsum_38.jpg','image/jpeg',32511,'fotos/test_038.jpg','active','2025-12-02 05:10:24'),(39,NULL,'test_039.jpg','picsum_39.jpg','image/jpeg',126592,'fotos/test_039.jpg','active','2025-12-02 05:10:25'),(40,NULL,'test_040.jpg','picsum_40.jpg','image/jpeg',105909,'fotos/test_040.jpg','active','2025-12-02 05:10:27'),(41,NULL,'test_041.jpg','picsum_41.jpg','image/jpeg',127889,'fotos/test_041.jpg','active','2025-12-02 05:10:28'),(42,NULL,'test_042.jpg','picsum_42.jpg','image/jpeg',66480,'fotos/test_042.jpg','active','2025-12-02 05:10:29'),(43,NULL,'test_043.jpg','picsum_43.jpg','image/jpeg',39795,'fotos/test_043.jpg','active','2025-12-02 05:10:32'),(44,NULL,'test_044.jpg','picsum_44.jpg','image/jpeg',162447,'fotos/test_044.jpg','active','2025-12-02 05:10:33'),(45,NULL,'test_045.jpg','picsum_45.jpg','image/jpeg',127486,'fotos/test_045.jpg','active','2025-12-02 05:10:35'),(46,NULL,'test_046.jpg','picsum_46.jpg','image/jpeg',44439,'fotos/test_046.jpg','active','2025-12-02 05:10:37'),(47,NULL,'test_047.jpg','picsum_47.jpg','image/jpeg',77218,'fotos/test_047.jpg','active','2025-12-02 05:10:38'),(48,NULL,'test_048.jpg','picsum_48.jpg','image/jpeg',51762,'fotos/test_048.jpg','active','2025-12-02 05:10:40'),(49,NULL,'test_049.jpg','picsum_49.jpg','image/jpeg',53348,'fotos/test_049.jpg','active','2025-12-02 05:10:41'),(50,NULL,'test_050.jpg','picsum_50.jpg','image/jpeg',107309,'fotos/test_050.jpg','active','2025-12-02 05:10:43'),(51,NULL,'test_051.jpg','picsum_51.jpg','image/jpeg',74132,'fotos/test_051.jpg','active','2025-12-02 05:10:44'),(52,NULL,'test_052.jpg','picsum_52.jpg','image/jpeg',27061,'fotos/test_052.jpg','active','2025-12-02 05:10:46'),(53,NULL,'test_053.jpg','picsum_53.jpg','image/jpeg',76835,'fotos/test_053.jpg','active','2025-12-02 05:10:47'),(54,NULL,'test_054.jpg','picsum_54.jpg','image/jpeg',113259,'fotos/test_054.jpg','active','2025-12-02 05:10:48'),(55,NULL,'test_055.jpg','picsum_55.jpg','image/jpeg',154522,'fotos/test_055.jpg','active','2025-12-02 05:10:51'),(56,NULL,'test_056.jpg','picsum_56.jpg','image/jpeg',102145,'fotos/test_056.jpg','active','2025-12-02 05:10:52'),(57,NULL,'test_057.jpg','picsum_57.jpg','image/jpeg',71488,'fotos/test_057.jpg','active','2025-12-02 05:10:55'),(58,NULL,'test_058.jpg','picsum_58.jpg','image/jpeg',125480,'fotos/test_058.jpg','active','2025-12-02 05:10:56'),(59,NULL,'test_059.jpg','picsum_59.jpg','image/jpeg',95210,'fotos/test_059.jpg','active','2025-12-02 05:10:59'),(60,NULL,'test_060.jpg','picsum_60.jpg','image/jpeg',70350,'fotos/test_060.jpg','active','2025-12-02 05:11:02');
/*!40000 ALTER TABLE `uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
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

-- Dump completed on 2025-12-02  4:38:59
