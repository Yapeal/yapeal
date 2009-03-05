/**
 * Yapeal install util SQL.
 *
 *
 * SQL
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know as Yapeal.
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Table `utilconfig`
-- -----------------------------------------------------
CREATE TABLE `%prefix%utilConfig` (
  `Name` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `Value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Name`) )
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Data `utilconfig`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilConfig` VALUES('dbPrefix', '%prefix%');
INSERT INTO `%prefix%utilConfig` VALUES('accountData', '%accountData%');
INSERT INTO `%prefix%utilConfig` VALUES('charData', '%charData%');
INSERT INTO `%prefix%utilConfig` VALUES('corpData', '%corpData%');
INSERT INTO `%prefix%utilConfig` VALUES('eveData', '%eveData%');
INSERT INTO `%prefix%utilConfig` VALUES('mapData', '%mapData%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorAPIfullApiKey', '%fullApiKey%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorAPIlimitedApiKey', '%limitedApiKey%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorAPIuserID', '%userID%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorCharacterID', '%characterID%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorCorporationID', '%corporationID%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorCorporationName', '%corporationName%');
INSERT INTO `%prefix%utilConfig` VALUES('creatorName', '%name%');
INSERT INTO `%prefix%utilConfig` VALUES('charAPIs', '%activeCharAPI%');
INSERT INTO `%prefix%utilConfig` VALUES('corpAPIs', '%activeCorpAPI%');
INSERT INTO `%prefix%utilConfig` VALUES('password', '%password%');
INSERT INTO `%prefix%utilConfig` VALUES('version', '$Revision$');

-- -----------------------------------------------------
-- Table `utilCachedUntil`
-- -----------------------------------------------------
CREATE TABLE `%prefix%utilCachedUntil` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `tableName` VARCHAR(255) NOT NULL ,
  `cachedUntil` DATETIME NOT NULL ,
  PRIMARY KEY (`tableName`, `ownerID`) )
ENGINE = MEMORY
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `utilRegisteredUser`
-- -----------------------------------------------------
CREATE TABLE `%prefix%utilRegisteredUser` (
  `userID` BIGINT UNSIGNED NOT NULL ,
  `fullApiKey` VARCHAR(64) NULL DEFAULT NULL ,
  `limitedApiKey` VARCHAR(64) NULL DEFAULT NULL ,
  PRIMARY KEY (`userID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Data `utilRegisteredUser`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilRegisteredUser` VALUES('%userID%', '%fullApiKey%', '%limitedApiKey%');

-- -----------------------------------------------------
-- Table `utilRegisteredCharacter`
-- -----------------------------------------------------
CREATE TABLE `%prefix%utilRegisteredCharacter` (
  `activeAPI` TEXT COMMENT 'A space separated list of APIs to get for this character' ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `userID` BIGINT UNSIGNED NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
  `graphic` BLOB NULL DEFAULT NULL ,
  `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Data `utilRegisteredCharacter`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilRegisteredCharacter` VALUES('%activeCharAPI%', '%characterID%', '%userID%', '%name%', '%corporationID%', '%corporationName%', '%charisactive%', null, null);

-- -----------------------------------------------------
-- Table `utilRegisteredCorporation`
-- -----------------------------------------------------
CREATE TABLE `%prefix%utilRegisteredCorporation` (
  `activeAPI` TEXT COMMENT 'A space separated list of APIs to get for this corporation' ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
  `graphic` BLOB NULL DEFAULT NULL ,
  `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
  PRIMARY KEY (`corporationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Data `utilRegisteredCorporation`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilRegisteredCorporation` VALUES('%activeCorpAPI%', '%corporationID%', '%characterID%', '%corpisactive%', null, null);

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
