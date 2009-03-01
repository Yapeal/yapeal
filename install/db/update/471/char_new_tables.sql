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
-- Table `charAccountBalance`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charAccountBalance` (
  `accountID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charAssetList`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charAssetList` (
  `flag` SMALLINT UNSIGNED NOT NULL ,
  `itemID` BIGINT UNSIGNED NOT NULL ,
  `lft` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `quantity` BIGINT UNSIGNED NOT NULL ,
  `rgt` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `singleton` BOOLEAN NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `itemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charAttributes`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charAttributes` (
  `charisma` SMALLINT UNSIGNED NOT NULL ,
  `intelligence` SMALLINT UNSIGNED NOT NULL ,
  `memory` SMALLINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `perception` SMALLINT UNSIGNED NOT NULL ,
  `willpower` SMALLINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet';

-- -----------------------------------------------------
-- Table `charAttributeEnhancers`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charAttributeEnhancers` (
  `augmentatorName` VARCHAR(255) NOT NULL ,
  `augmentatorValue` SMALLINT UNSIGNED NOT NULL ,
  `bonusName` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `bonusName`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet';

-- -----------------------------------------------------
-- Table `charcertificates`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCertificates` (
  `certificateID` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `certificateID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet';

-- -----------------------------------------------------
-- Table `charCharacterSheet`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCharacterSheet` (
  `balance` DECIMAL(17,2) NOT NULL ,
  `bloodLine` VARCHAR(255) NOT NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `cloneName` VARCHAR(255) NOT NULL ,
  `cloneSkillPoints` BIGINT UNSIGNED NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `gender` VARCHAR(255) NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `race` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charCorporationRoles`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCorporationRoles` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charCorporationRolesAtBase`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCorporationRolesAtBase` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charCorporationRolesAtHQ`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCorporationRolesAtHQ` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charCorporationRolesAtOther`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCorporationRolesAtOther` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charCorporationTitles`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charCorporationTitles` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `titleID` BIGINT UNSIGNED NOT NULL ,
  `titleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `titleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charIndustryJobs`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charIndustryJobs` (
  `activityID` SMALLINT UNSIGNED NOT NULL ,
  `assemblyLineID` BIGINT UNSIGNED NOT NULL ,
  `beginProductionTime` DATETIME NOT NULL ,
  `charMaterialMultiplier` DECIMAL(17,2) NOT NULL ,
  `charTimeMultiplier` DECIMAL(17,2) NOT NULL ,
  `completed` SMALLINT UNSIGNED NOT NULL ,
  `completedStatus` SMALLINT UNSIGNED NOT NULL ,
  `completedSuccessfully` SMALLINT UNSIGNED NOT NULL ,
  `containerID` BIGINT UNSIGNED NOT NULL ,
  `containerLocationID` BIGINT UNSIGNED NOT NULL ,
  `containerTypeID` BIGINT UNSIGNED NOT NULL ,
  `endProductionTime` DATETIME NOT NULL ,
  `installedInSolarSystemID` BIGINT UNSIGNED NOT NULL ,
  `installedItemCopy` BIGINT UNSIGNED NOT NULL ,
  `installedItemFlag` SMALLINT UNSIGNED NOT NULL ,
  `installedItemID` BIGINT UNSIGNED NOT NULL ,
  `installedItemLicensedProductionRunsRemaining` BIGINT NOT NULL ,
  `installedItemLocationID` BIGINT UNSIGNED NOT NULL ,
  `installedItemMaterialLevel` INT NOT NULL ,
  `installedItemProductivityLevel` INT NOT NULL ,
  `installedItemQuantity` BIGINT UNSIGNED NOT NULL ,
  `installedItemTypeID` BIGINT UNSIGNED NOT NULL ,
  `installerID` BIGINT UNSIGNED NOT NULL ,
  `installTime` DATETIME NOT NULL ,
  `jobID` BIGINT UNSIGNED NOT NULL ,
  `licensedProductionRuns` BIGINT UNSIGNED NOT NULL ,
  `materialMultiplier` DECIMAL(17,2) NOT NULL ,
  `outputFlag` SMALLINT UNSIGNED NOT NULL ,
  `outputLocationID` BIGINT UNSIGNED NOT NULL ,
  `outputTypeID` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `pauseProductionTime` DATETIME NOT NULL ,
  `runs` BIGINT UNSIGNED NOT NULL ,
  `timeMultiplier` DECIMAL(17,2) NOT NULL ,
  PRIMARY KEY (`ownerID`, `installTime`, `jobID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charMarketOrders`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charMarketOrders` (
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `bid` BOOLEAN NOT NULL ,
  `changed` TIMESTAMP NOT NULL COMMENT 'Added to API to allow tracking of when order was last active. Auto updated by MySQL' ,
  `charID` BIGINT UNSIGNED NOT NULL ,
  `duration` SMALLINT UNSIGNED NOT NULL ,
  `escrow` DECIMAL(17,2) NOT NULL ,
  `issued` DATETIME NOT NULL ,
  `minVolume` BIGINT UNSIGNED NOT NULL ,
  `orderID` BIGINT UNSIGNED NOT NULL ,
  `orderState` TINYINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `price` DECIMAL(17,2) NOT NULL ,
  `range` SMALLINT NOT NULL ,
  `stationID` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `volEntered` BIGINT UNSIGNED NOT NULL ,
  `volRemaining` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `issued`, `orderID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charskills`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charSkills` (
  `level` SMALLINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `skillpoints` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `unpublished` BOOLEAN NOT NULL DEFAULT FALSE ,
  PRIMARY KEY (`ownerID`, `typeID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet API';

-- -----------------------------------------------------
-- Table `charWalletJournal`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charWalletJournal` (
  `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
  `amount` DECIMAL(17,2) NOT NULL ,
  `argID1` BIGINT UNSIGNED NULL ,
  `argName1` VARCHAR(255) NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `date` DATETIME NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `ownerID1` BIGINT UNSIGNED NULL ,
  `ownerID2` BIGINT UNSIGNED NULL ,
  `ownerName1` VARCHAR(255) NULL ,
  `ownerName2` VARCHAR(255) NULL ,
  `reason` TEXT NULL ,
  `refID` BIGINT UNSIGNED NOT NULL ,
  `refTypeID` TINYINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `date`, `refID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `charWalletTransactions`
-- -----------------------------------------------------
CREATE TABLE `%prefix%charWalletTransactions` (
  `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
  `characterID` BIGINT UNSIGNED NULL ,
  `characterName` VARCHAR(255) NULL ,
  `clientID` BIGINT UNSIGNED NULL ,
  `clientName` VARCHAR(255) NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `price` DECIMAL(17,2) NOT NULL ,
  `quantity` BIGINT UNSIGNED NOT NULL ,
  `stationID` BIGINT UNSIGNED NULL ,
  `stationName` VARCHAR(255) NULL ,
  `transactionDateTime` DATETIME NOT NULL ,
  `transactionFor` VARCHAR(255) NOT NULL DEFAULT 'corporation' ,
  `transactionID` BIGINT UNSIGNED NOT NULL ,
  `transactionType` VARCHAR(255) NOT NULL DEFAULT 'sell' ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  `typeName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `transactionDateTime`, `transactionID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
