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

-- -----------------------------------------------------
-- Table `corpAttackers`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpAttackers` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `allianceName` VARCHAR(255) NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `characterName` VARCHAR(255) NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NULL ,
  `damageDone` BIGINT UNSIGNED NOT NULL ,
  `factionID` BIGINT UNSIGNED NOT NULL DEFAULT 0 ,
  `factionName` VARCHAR(255) NOT NULL DEFAULT '' ,
  `finalBlow` BOOLEAN DEFAULT FALSE ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `securityStatus` FLOAT NOT NULL DEFAULT 0.0 ,
  `shipTypeID`  BIGINT UNSIGNED NOT NULL ,
  `weaponTypeID`  BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`killID`, `characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from KillLog';

-- -----------------------------------------------------
-- Table `corpItems`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpItems` (
  `flag` SMALLINT UNSIGNED NOT NULL ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `lvl` SMALLINT UNSIGNED NOT NULL ,
  `lft` BIGINT UNSIGNED NOT NULL ,
  `rgt` BIGINT UNSIGNED NOT NULL ,
  `qtyDropped` BIGINT UNSIGNED NOT NULL ,
  `qtyDestroyed` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`killID`, `lft`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from KillLog';

-- -----------------------------------------------------
-- Table `corpKillLog`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpKillLog` (
  `killID` BIGINT UNSIGNED NOT NULL ,
  `lastKillboard` VARCHAR(255) NOT NULL ,
  `moonID` BIGINT UNSIGNED NOT NULL ,
  `originalKillboard` VARCHAR(255) NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  `killTime` DATETIME NOT NULL ,
  `stratum` SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`killID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `corpVictim`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpVictim` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `allianceName` VARCHAR(255) NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `characterName` VARCHAR(255) NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NULL ,
  `damageTaken` BIGINT UNSIGNED NOT NULL ,
  `factionID` BIGINT UNSIGNED NOT NULL DEFAULT 0 ,
  `factionName` VARCHAR(255) NOT NULL DEFAULT '' ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `shipTypeID`  BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`killID`, `characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from KillLog';

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
