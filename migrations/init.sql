-- MySQL dump 10.14  Distrib 5.5.52-MariaDB, for Linux (x86_64)
--
-- Host: 172.31.19.66    Database: gettoeat
-- ------------------------------------------------------
-- Server version	5.5.52-MariaDB

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
-- Table structure for table `bill`
--

DROP TABLE IF EXISTS `bill`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `year` int(10) unsigned NOT NULL,
  `month` tinyint(3) unsigned NOT NULL,
  `date` tinyint(3) unsigned NOT NULL,
  `day` tinyint(3) unsigned NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `ordered_at` int(10) unsigned NOT NULL,
  `paid_at` int(10) unsigned NOT NULL,
  `custermers` tinyint(3) unsigned NOT NULL,
  `table` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `year` (`year`),
  KEY `month` (`month`),
  KEY `date` (`date`),
  KEY `day` (`day`),
  KEY `table` (`table`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill`
--

LOCK TABLES `bill` WRITE;
/*!40000 ALTER TABLE `bill` DISABLE KEYS */;
INSERT INTO `bill` VALUES (1,1,2017,2,2,4,600,1486054013,1486054069,2,'middle_1'),(2,1,2017,2,2,4,350,1486057364,1486057386,3,'bar_1'),(3,1,2017,2,19,7,250,1487503672,1487949954,0,'吧2'),(4,1,2017,2,24,5,280,1487950019,1487950676,2,'吧1'),(5,1,2017,2,24,5,100,1487955193,1487955208,1,'吧2'),(6,1,2017,2,22,3,200,1487760418,1488007054,1,'方1'),(7,1,2017,2,25,6,80,1488007077,1488007097,1,'吧2'),(10,1,2017,2,25,6,200,1488010279,1488010428,1,'方3');
/*!40000 ALTER TABLE `bill` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_discount`
--

DROP TABLE IF EXISTS `bill_discount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_discount` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  `event_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_id` (`bill_id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_discount`
--

LOCK TABLES `bill_discount` WRITE;
/*!40000 ALTER TABLE `bill_discount` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_discount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_item`
--

DROP TABLE IF EXISTS `bill_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_item` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bill_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `unit_price` int(10) unsigned NOT NULL,
  `amount` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bill_id` (`bill_id`,`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_item`
--

LOCK TABLES `bill_item` WRITE;
/*!40000 ALTER TABLE `bill_item` DISABLE KEYS */;
INSERT INTO `bill_item` VALUES (1,1,1,120,3),(2,2,5,160,2),(3,2,4,170,1),(4,2,1,120,2),(5,3,1,120,2),(6,3,5,160,1),(7,4,1,120,2),(8,4,5,160,1),(9,4,4,170,1),(10,5,5,160,2),(11,6,1,120,2),(12,7,5,160,1),(13,7,4,170,1),(17,10,1,120,2);
/*!40000 ALTER TABLE `bill_item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `name` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `off` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `category`
--

LOCK TABLES `category` WRITE;
/*!40000 ALTER TABLE `category` DISABLE KEYS */;
INSERT INTO `category` VALUES (1,1,'酒'),(2,2,'果汁'),(3,2,'冰淇淋'),(4,1,'菜'),(5,1,'點心'),(6,3,'超好吃'),(7,3,'羅偉創意');
/*!40000 ALTER TABLE `category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `type_id` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  `updated_at` int(10) unsigned NOT NULL,
  `start_at` int(10) unsigned NOT NULL,
  `end_at` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `note` varchar(255) NOT NULL,
  `data` text,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `type_id` (`type_id`),
  KEY `created_at` (`created_at`),
  KEY `updated_at` (`updated_at`),
  KEY `start_at` (`start_at`),
  KEY `end_at` (`end_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event`
--

LOCK TABLES `event` WRITE;
/*!40000 ALTER TABLE `event` DISABLE KEYS */;
/*!40000 ALTER TABLE `event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `event_type`
--

DROP TABLE IF EXISTS `event_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `event_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `event_type`
--

LOCK TABLES `event_type` WRITE;
/*!40000 ALTER TABLE `event_type` DISABLE KEYS */;
/*!40000 ALTER TABLE `event_type` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product`
--

DROP TABLE IF EXISTS `product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `price` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `position` tinyint(3) unsigned NOT NULL,
  `off` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`,`position`) USING BTREE,
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product`
--

LOCK TABLES `product` WRITE;
/*!40000 ALTER TABLE `product` DISABLE KEYS */;
INSERT INTO `product` VALUES (1,1,'小糊塗仙',100,1,0,0),(2,2,'芭樂汁',70,2,0,0),(3,2,'海膽冰淇淋',100,3,0,0),(4,1,'紅蘿葡',30,4,2,0),(5,1,'蕃茄',50,4,1,0),(6,1,'牛奶',50,4,0,1),(7,3,'超好吃自來水',500,6,1,0),(8,3,'超好吃wifi',300,6,0,0),(9,3,'大大大漢堡',20,7,1,0),(10,3,'Macbook',40000,7,2,0);
/*!40000 ALTER TABLE `product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `store`
--

DROP TABLE IF EXISTS `store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `nickname` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `date_change_at` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account` (`account`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `store`
--

LOCK TABLES `store` WRITE;
/*!40000 ALTER TABLE `store` DISABLE KEYS */;
INSERT INTO `store` VALUES (1,'temperbeer','好啤氣','好啤氣'),(2,'3035','参零参伍冰果室','参零参伍'),(3,'demo','DEMO 商家','DEMO');
/*!40000 ALTER TABLE `store` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables_info`
--

DROP TABLE IF EXISTS `tables_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tables_info` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `version` tinyint(3) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_version` (`store_id`,`version`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables_info`
--

LOCK TABLES `tables_info` WRITE;
/*!40000 ALTER TABLE `tables_info` DISABLE KEYS */;
INSERT INTO `tables_info` VALUES (1,1,1,'{\"totalHeight\":460,\"totalWidth\":420,\"tables\":[{\"name\":\"吧2\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"80px\",\"active\":true},{\"name\":\"吧6\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"320px\",\"active\":true},{\"name\":\"吧5\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"260px\",\"active\":true},{\"name\":\"吧4\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"200px\",\"active\":true},{\"name\":\"吧3\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"140px\",\"active\":true},{\"name\":\"吧1\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"20px\",\"active\":true},{\"name\":\"方6\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"320px\",\"active\":true},{\"name\":\"外1\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"20px\",\"active\":true},{\"name\":\"外2\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"80px\",\"active\":true},{\"name\":\"長1\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"140px\",\"active\":true},{\"name\":\"高1\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"200px\",\"active\":true},{\"name\":\"高2\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"260px\",\"active\":true},{\"name\":\"高3\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"320px\",\"active\":true},{\"name\":\"圓1\",\"width\":80,\"height\":40,\"x\":\"220px\",\"y\":\"20px\",\"active\":true},{\"name\":\"圓2\",\"width\":80,\"height\":40,\"x\":\"220px\",\"y\":\"80px\",\"active\":true},{\"name\":\"圓3\",\"width\":80,\"height\":40,\"x\":\"220px\",\"y\":\"140px\",\"active\":true},{\"name\":\"圓4\",\"width\":80,\"height\":40,\"x\":\"220px\",\"y\":\"200px\",\"active\":true},{\"name\":\"方2\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"80px\",\"active\":true},{\"name\":\"方3\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"140px\",\"active\":true},{\"name\":\"方4\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"200px\",\"active\":true},{\"name\":\"方5\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"260px\",\"active\":true},{\"name\":\"外帶4\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"400px\",\"active\":true},{\"name\":\"外帶3\",\"width\":80,\"height\":40,\"x\":\"220px\",\"y\":\"400px\",\"active\":true},{\"name\":\"外帶2\",\"width\":80,\"height\":40,\"x\":\"120px\",\"y\":\"400px\",\"active\":true},{\"name\":\"外帶1\",\"width\":80,\"height\":40,\"x\":\"20px\",\"y\":\"400px\",\"active\":true},{\"name\":\"方1\",\"width\":80,\"height\":40,\"x\":\"320px\",\"y\":\"20px\",\"active\":true}]}'),(2,2,1,'{\"totalHeight\":180,\"totalWidth\":300,\"tables\":[]}'),(4,3,1,'');
/*!40000 ALTER TABLE `tables_info` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-06-21 10:03:07
