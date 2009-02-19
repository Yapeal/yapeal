/**
 * Yapeal install map SQL.
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
-- Table `mapKills`
-- -----------------------------------------------------
CREATE TABLE `%prefix%mapKills` (
  `factionKills` BIGINT UNSIGNED NOT NULL ,
  `podKills` BIGINT UNSIGNED NOT NULL ,
  `shipKills` BIGINT UNSIGNED NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`solarSystemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `mapJumps`
-- -----------------------------------------------------
CREATE TABLE `%prefix%mapJumps` (
  `shipJumps` BIGINT UNSIGNED NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`solarSystemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `mapSovereignty`
-- -----------------------------------------------------
CREATE TABLE `%prefix%mapSovereignty` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `constellationSovereignty` BIGINT UNSIGNED NOT NULL ,
  `factionID` BIGINT UNSIGNED NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  `solarSystemName` VARCHAR(255) NOT NULL ,
  `sovereigntyLevel` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`solarSystemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
