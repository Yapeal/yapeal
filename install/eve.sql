/**
 * MySQL file.
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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE TABLE IF NOT EXISTS `eveAllianceList` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `executorCorpID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `memberCount` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `name` VARCHAR(255) NULL DEFAULT NULL ,
  `shortName` VARCHAR(255) NULL DEFAULT NULL ,
  `startDate` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`allianceID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `eveConquerableStationList` (
  `corporationID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `corporationName` VARCHAR(255) NULL DEFAULT NULL ,
  `solarSystemID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `stationID` BIGINT UNSIGNED NOT NULL ,
  `stationName` VARCHAR(255) NULL DEFAULT NULL ,
  `stationTypeID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  PRIMARY KEY (`stationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `eveErrorList` (
  `errorCode` SMALLINT UNSIGNED NOT NULL ,
  `errorText` TEXT NOT NULL ,
  PRIMARY KEY (`errorCode`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `eveMemberCorporations` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `startDate` DATETIME NULL DEFAULT NULL ,
  PRIMARY KEY (`corporationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from AllianceList';

CREATE TABLE IF NOT EXISTS `eveRefTypes` (
  `refTypeID` SMALLINT UNSIGNED NOT NULL ,
  `refTypeName` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`refTypeID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
