-- MySQL dump 10.13  Distrib 8.0.28, for Linux (x86_64)
--
-- Host: localhost    Database: sams
-- ------------------------------------------------------
-- Server version	8.0.28

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
-- Current Database: `sams`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `sams` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `sams`;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `jID` int unsigned NOT NULL AUTO_INCREMENT,
  `parameters` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `targetID` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `jobID` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `sessionID` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `uID` int unsigned NOT NULL,
  `submittedDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notified` tinyint(1) DEFAULT '0',
  `expireDateTime` datetime DEFAULT NULL,
  PRIMARY KEY (`jID`),
  UNIQUE KEY `jobID` (`jobID`),
  UNIQUE KEY `sessionID` (`sessionID`),
  KEY `uID` (`uID`),
  CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8422 DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plotfiles`
--

DROP TABLE IF EXISTS `plotfiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plotfiles` (
  `plotID` int unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(150) NOT NULL,
  `plotType` varchar(50) NOT NULL,
  `selectionID` int unsigned NOT NULL,
  `version` varchar(50) NOT NULL,
  PRIMARY KEY (`plotID`),
  UNIQUE KEY `filename` (`filename`),
  KEY `plotFiles_ibfk_1` (`selectionID`),
  CONSTRAINT `plotFiles_ibfk_1` FOREIGN KEY (`selectionID`) REFERENCES `selectedtargets` (`selectionID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=706651 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `requests`
--

DROP TABLE IF EXISTS `requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `requests` (
  `rID` int unsigned NOT NULL AUTO_INCREMENT,
  `uID` int unsigned NOT NULL,
  `requestRegion` geometry NOT NULL,
  `startDate` date DEFAULT NULL,
  `endDate` date DEFAULT NULL,
  `obsType` varchar(10) NOT NULL DEFAULT 'sam',
  `justification` text NOT NULL,
  `nickname` varchar(250) NOT NULL,
  `submitted` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `scheduledDate` varchar(150) DEFAULT NULL,
  `approved` enum('Pending','Yes','No') NOT NULL DEFAULT 'Pending',
  PRIMARY KEY (`rID`),
  UNIQUE KEY `uID_2` (`uID`,`nickname`),
  KEY `uID` (`uID`),
  SPATIAL KEY `requestRegion` (`requestRegion`),
  CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `selectedtargets`
--

DROP TABLE IF EXISTS `selectedtargets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `selectedtargets` (
  `selectionID` int unsigned NOT NULL AUTO_INCREMENT,
  `mode` varchar(100) NOT NULL,
  `targetID` varchar(50) NOT NULL,
  `oldName` varchar(150) NOT NULL DEFAULT '',
  `orbit` int DEFAULT NULL,
  `cID` int unsigned DEFAULT NULL,
  `targetTimeStart` datetime NOT NULL,
  `targetTimeEnd` datetime DEFAULT NULL,
  `dwellTime` float NOT NULL,
  `deltaLat` float DEFAULT NULL,
  `deltaLon` float DEFAULT NULL,
  `sza` float DEFAULT NULL,
  `soundings` int unsigned DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `cloudFraction` float DEFAULT NULL,
  `useful` enum('Y','N','M') DEFAULT NULL,
  `comments` text,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `fID` int unsigned NOT NULL,
  PRIMARY KEY (`selectionID`),
  UNIQUE KEY `targetTimeStart` (`targetTimeStart`),
  KEY `targetID` (`targetID`),
  KEY `fID` (`fID`),
  KEY `cID` (`cID`),
  CONSTRAINT `selectedTargets_ibfk_1` FOREIGN KEY (`targetID`) REFERENCES `sites` (`targetID`) ON DELETE CASCADE,
  CONSTRAINT `selectedTargets_ibfk_2` FOREIGN KEY (`fID`) REFERENCES `atsfiles` (`fID`) ON DELETE CASCADE,
  CONSTRAINT `selectedTargets_ibfk_3` FOREIGN KEY (`cID`) REFERENCES `cameraimages` (`cID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=52008 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `targetID` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL DEFAULT '',
  `description` varchar(250) DEFAULT NULL,
  `targetGeo` point DEFAULT NULL,
  `targetShape` geometry DEFAULT NULL,
  `targetAlt` float DEFAULT NULL,
  `ascPolygon` geometry DEFAULT NULL,
  `ascCentroid` point DEFAULT NULL,
  `ascAlong` float DEFAULT NULL,
  `ascAcross` float DEFAULT NULL,
  `descPolygon` geometry DEFAULT NULL,
  `descCentroid` point DEFAULT NULL,
  `descAlong` float DEFAULT NULL,
  `descAcross` float DEFAULT NULL,
  `swathScale` float DEFAULT NULL,
  `scanDirection` int DEFAULT NULL,
  `timezone` varchar(150) DEFAULT NULL,
  `contact` varchar(250) DEFAULT NULL,
  `contactLink` text,
  `tcconStatusText` varchar(250) DEFAULT NULL,
  `tcconStatusValue` tinyint(1) DEFAULT NULL,
  `tcconStatusLink` varchar(250) DEFAULT NULL,
  `emailRecipients` text,
  `siteType` enum('TCCON','volcano','SIF_High','SIF_Low','ODIAC','calibration','fossil','validation','desert') DEFAULT NULL,
  `siteTypePriority` float DEFAULT NULL,
  `priorityNumber` int DEFAULT NULL,
  `desiredObs` int DEFAULT NULL,
  `mode` varchar(100) DEFAULT NULL,
  `rotateTarget` tinyint(1) NOT NULL DEFAULT '1',
  `glintCheck` tinyint(1) NOT NULL DEFAULT '1',
  `clearSky1` float DEFAULT NULL,
  `clearSky2` float DEFAULT NULL,
  `clearSky3` float DEFAULT NULL,
  `clearSky4` float DEFAULT NULL,
  `clearSky5` float DEFAULT NULL,
  `clearSky6` float DEFAULT NULL,
  `clearSky7` float DEFAULT NULL,
  `clearSky8` float DEFAULT NULL,
  `clearSky9` float DEFAULT NULL,
  `clearSky10` float DEFAULT NULL,
  `clearSky11` float DEFAULT NULL,
  `clearSky12` float DEFAULT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `highPriorityStart` date DEFAULT NULL,
  `highPriorityEnd` date DEFAULT NULL,
  PRIMARY KEY (`targetID`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `uID` int unsigned NOT NULL AUTO_INCREMENT,
  `firstName` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lastName` varchar(25) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `affiliation` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(500) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `resetKey` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `debug` tinyint(1) DEFAULT '0',
  `attempt` int NOT NULL DEFAULT '0',
  `attemptTime` datetime DEFAULT '1970-01-01 00:00:00',
  `locked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`uID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=275 DEFAULT CHARSET=utf8mb3 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_sites`
--

DROP TABLE IF EXISTS `users_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_sites` (
  `uID` int unsigned NOT NULL,
  `targetID` varchar(50) NOT NULL,
  PRIMARY KEY (`uID`,`targetID`),
  CONSTRAINT `users_sites_ibfk_1` FOREIGN KEY (`uID`) REFERENCES `users` (`uID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'sams'
--

--
-- Dumping routines for database 'sams'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-05  9:57:56
