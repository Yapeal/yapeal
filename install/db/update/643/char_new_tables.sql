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
-- Table `charStandingsFromAgents`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charStandingsFromAgents` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

-- -----------------------------------------------------
-- Table `charStandingsFromFactions`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charStandingsFromFactions` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

-- -----------------------------------------------------
-- Table `charStandingsFromNPCCorporations`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charStandingsFromNPCCorporations` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

-- -----------------------------------------------------
-- Table `charStandingsToCharacters`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charStandingsToCharacters` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

-- -----------------------------------------------------
-- Table `charStandingsToCorporations`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charStandingsToCorporations` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

-- -----------------------------------------------------

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
