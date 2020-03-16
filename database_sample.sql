/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE IF NOT EXISTS `imagehoster` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `imagehoster`;

CREATE TABLE IF NOT EXISTS `imgup_api` (
  `api_id` int(11) NOT NULL AUTO_INCREMENT,
  `api_uid` int(11) NOT NULL,
  `api_public` varchar(255) NOT NULL,
  `api_private` varchar(255) NOT NULL,
  PRIMARY KEY (`api_id`),
  KEY `api_uid` (`api_uid`),
  CONSTRAINT `api_uid` FOREIGN KEY (`api_uid`) REFERENCES `imgup_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `imgup_imgdata` (
  `img_id` int(11) NOT NULL AUTO_INCREMENT,
  `img_uid` int(11) DEFAULT NULL,
  `img_dir` varchar(255) CHARACTER SET latin1 NOT NULL,
  `img_name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `img_slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `img_deleteToken` varchar(255) CHARACTER SET latin1 NOT NULL,
  `img_size` int(11) NOT NULL,
  `img_date` datetime NOT NULL,
  PRIMARY KEY (`img_id`),
  KEY `img_uid` (`img_uid`),
  CONSTRAINT `img_uid` FOREIGN KEY (`img_uid`) REFERENCES `imgup_users` (`usr_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `imgup_users` (
  `usr_id` int(11) NOT NULL AUTO_INCREMENT,
  `usr_name` varchar(50) CHARACTER SET latin1 NOT NULL,
  `usr_slug` varchar(50) CHARACTER SET latin1 NOT NULL,
  `usr_email` varchar(255) CHARACTER SET latin1 NOT NULL,
  `usr_pswd` varchar(255) CHARACTER SET latin1 NOT NULL,
  `usr_admin` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`usr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
