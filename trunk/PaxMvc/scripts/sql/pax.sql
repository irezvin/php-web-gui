-- MySQL dump 10.13  Distrib 5.1.37, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pax
-- ------------------------------------------------------
-- Server version	5.1.37-1ubuntu5

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
-- Table structure for table `pax_item_adjacent`
--

DROP TABLE IF EXISTS `pax_item_adjacent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pax_item_adjacent` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parentId` int(10) unsigned DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(45) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pax_item_adjacent`
--

LOCK TABLES `pax_item_adjacent` WRITE;
/*!40000 ALTER TABLE `pax_item_adjacent` DISABLE KEYS */;
/*!40000 ALTER TABLE `pax_item_adjacent` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pax_item_nested`
--

DROP TABLE IF EXISTS `pax_item_nested`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pax_item_nested` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(45) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pax_item_nested`
--

LOCK TABLES `pax_item_nested` WRITE;
/*!40000 ALTER TABLE `pax_item_nested` DISABLE KEYS */;
/*!40000 ALTER TABLE `pax_item_nested` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pax_nested_sets`
--

DROP TABLE IF EXISTS `pax_nested_sets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pax_nested_sets` (
  `id` int(10) unsigned NOT NULL,
  `treeId` int(10) unsigned NOT NULL,
  `leftCol` int(10) unsigned NOT NULL DEFAULT '0',
  `rightCol` int(10) unsigned NOT NULL DEFAULT '1',
  `parentId` int(10) unsigned DEFAULT NULL,
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` varchar(40) NOT NULL DEFAULT '',
  `ignore` int(1) unsigned NOT NULL DEFAULT '0',
  `depth` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`,`treeId`),
  KEY `index_2` (`leftCol`),
  KEY `index_3` (`rightCol`),
  KEY `index_4` (`parentId`),
  KEY `index_5` (`ordering`),
  KEY `index_6` (`ignore`),
  KEY `index_7` (`id`),
  KEY `index_8` (`treeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pax_nested_sets`
--

LOCK TABLES `pax_nested_sets` WRITE;
/*!40000 ALTER TABLE `pax_nested_sets` DISABLE KEYS */;
/*!40000 ALTER TABLE `pax_nested_sets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-01-27 19:49:51
