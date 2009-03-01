/**
 * MySQL file used to move data from old style database tables to new ones.
 *
 * Only use this if you know what you are doing as it can cause data loss or
 * otherwise cause problems with your database. Never use it without a complete
 * backup.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
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
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Table `utilconfig`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `%prefix%utilconfig` (
  `Name` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `Value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Name`) )
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- -----------------------------------------------------
-- Data `utilconfig`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilconfig` (`Name`,`Value`) VALUES
('dbPrefix', '%prefix%'),
('accountData', '%accountData%'),
('charData', '%charData%'),
('corpData', '%corpData%'),
('eveData', '%eveData%'),
('mapData', '%mapData%'),
('creatorAPIfullApiKey', '%fullApiKey%'),
('creatorAPIlimitedApiKey', '%limitedApiKey%'),
('creatorAPIuserID', '%userID%'),
('creatorCharacterID', '%characterID%'),
('creatorCorporationID', '%corporationID%'),
('creatorCorporationName', '%corporationName%'),
('creatorName', '%name%'),
('password', '%password%'),
('version', '$Revision: 572 $')
ON DUPLICATE KEY UPDATE `Value`=VALUES(`Value`);

-- -----------------------------------------------------
-- Table `utilCachedUntil`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `%prefix%utilCachedUntil` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `tableName` VARCHAR(255) NOT NULL ,
  `cachedUntil` DATETIME NOT NULL ,
  PRIMARY KEY (`tableName`, `ownerID`) )
ENGINE = MEMORY
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `utilRegisteredCharacter`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `%prefix%utilRegisteredCharacter` (
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
INSERT INTO `%prefix%utilRegisteredCharacter` VALUES('%characterID%', '%userID%', '%name%', '%corporationID%', '%corporationName%', '%charisactive%', null, null)
ON DUPLICATE KEY UPDATE `userID`=VALUES(`userID`),`name`=VALUES(`name`),
  `corporationID`=VALUES(`corporationID`),`corporationName`=VALUES(`corporationName`),
  `isActive`=VALUES(`isActive`),`graphic`=VALUES(`graphic`),
  `graphicType`=VALUES(`graphicType`);

-- -----------------------------------------------------
-- Table `utilRegisteredCorporation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `%prefix%utilRegisteredCorporation` (
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
INSERT INTO `%prefix%utilRegisteredCorporation` VALUES('%corporationID%', '%characterID%', '%corpisactive%', null, null)
ON DUPLICATE KEY UPDATE `characterID`=VALUES(`characterID`),`graphic`=VALUES(`graphic`),
  `graphicType`=VALUES(`graphicType`),`isActive`=VALUES(`isActive`);

-- -----------------------------------------------------
-- Table `utilRegisteredUser`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `%prefix%utilRegisteredUser` (
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
INSERT INTO `%prefix%utilRegisteredUser` VALUES('%userID%', '%fullApiKey%', '%limitedApiKey%')
ON DUPLICATE KEY UPDATE `fullApiKey`=VALUES(`fullApiKey`),`limitedApiKey`=VALUES(`limitedApiKey`);

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
