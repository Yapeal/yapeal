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
-- Table `corpAccountBalance`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpAccountBalance` (
  `accountID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `corpAssetList`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpAssetList` (
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
-- Table `corpCorporationSheet`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpCorporationSheet` (
  `allianceId` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `allianceName` VARCHAR(255) NULL DEFAULT NULL ,
  `ceoID` BIGINT UNSIGNED NOT NULL ,
  `ceoName` VARCHAR(255) NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `description` TEXT NULL DEFAULT NULL ,
  `memberCount` SMALLINT UNSIGNED NOT NULL ,
  `memberLimit` SMALLINT UNSIGNED NOT NULL ,
  `shares` BIGINT UNSIGNED NOT NULL ,
  `stationID` BIGINT UNSIGNED NOT NULL ,
  `stationName` VARCHAR(255) NOT NULL ,
  `taxRate` DECIMAL(17,2) UNSIGNED NOT NULL ,
  `ticker` VARCHAR(255) NOT NULL ,
  `url` VARCHAR(255) NULL DEFAULT NULL ,
  PRIMARY KEY (`corporationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `corpContainerLog`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpContainerLog` (
  `action` VARCHAR(255) NOT NULL ,
  `actorID` BIGINT UNSIGNED NOT NULL ,
  `actorName` VARCHAR(255) NOT NULL ,
  `flag` BIGINT UNSIGNED NOT NULL ,
  `itemID` BIGINT UNSIGNED NOT NULL ,
  `itemTypeID` BIGINT UNSIGNED NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `logTime` DATETIME NOT NULL ,
  `newConfiguration` BIGINT UNSIGNED NOT NULL ,
  `oldConfiguration` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `passwordType` VARCHAR(255) NOT NULL ,
  `quantity` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `logTime`, `itemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `corpdivisions`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpDivisions` (
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountKey`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CorporationSheet API';

-- -----------------------------------------------------
-- Table `corpIndustryJobs`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpIndustryJobs` (
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
-- Table `corpItems`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpItems` (
  `flag` SMALLINT UNSIGNED NOT NULL ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `lft` BIGINT UNSIGNED NOT NULL ,
  `lvl` SMALLINT UNSIGNED NOT NULL ,
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
  `killTime` DATETIME NOT NULL ,
  `lastKillboard` VARCHAR(255) NOT NULL ,
  `moonID` BIGINT UNSIGNED NOT NULL ,
  `originalKillboard` VARCHAR(255) NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  `stratum` SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (`killID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `corpLogo`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpLogo` (
  `color1` BIGINT UNSIGNED NOT NULL ,
  `color2` BIGINT UNSIGNED NOT NULL ,
  `color3` BIGINT UNSIGNED NOT NULL ,
  `graphicID` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `shape1` SMALLINT UNSIGNED NOT NULL ,
  `shape2` SMALLINT UNSIGNED NOT NULL ,
  `shape3` SMALLINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `graphicID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CorporationSheet API';

-- -----------------------------------------------------
-- Table `corpMarketOrders`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpMarketOrders` (
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
-- Table `corpMemberTracking`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpMemberTracking` (
  `base` VARCHAR(255) NULL DEFAULT NULL ,
  `baseID` BIGINT UNSIGNED NULL DEFAULT NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `grantableRoles` VARCHAR(64) NOT NULL ,
  `location` VARCHAR(255) NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `logoffDateTime` DATETIME NOT NULL ,
  `logonDateTime` DATETIME NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roles` VARCHAR(64) NOT NULL ,
  `shipType` VARCHAR(255) NOT NULL ,
  `shipTypeID` BIGINT UNSIGNED NOT NULL ,
  `startDateTime` DATETIME NOT NULL ,
  `title` TEXT NULL DEFAULT NULL ,
  INDEX `corpindex1` (`ownerID` ASC) ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

-- -----------------------------------------------------
-- Table `corpStandingsFromAgents`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStandingsFromAgents` (
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
-- Table `corpStandingsFromFactions`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStandingsFromFactions` (
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
-- Table `corpStandingsFromNPCCorporations`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStandingsFromNPCCorporations` (
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
-- Table `corpStandingsToAlliances`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStandingsToAlliances` (
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
-- Table `corpStandingsToCharacters`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStandingsToCharacters` (
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
-- Table `corpStandingsToCorporations`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStandingsToCorporations` (
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
-- Table `corpStarbaseList`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpStarbaseList` (
  `itemID` BIGINT UNSIGNED NOT NULL ,
  `locationID` BIGINT UNSIGNED NOT NULL ,
  `moonID` BIGINT UNSIGNED NOT NULL ,
  `onlineTimestamp` DATETIME NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `state` SMALLINT UNSIGNED NOT NULL ,
  `stateTimestamp` DATETIME NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `itemID`) )
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
-- Table `corpwalletDivisions`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpWalletDivisions` (
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountKey`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'This is sub-table from CorporationSheet API';

-- -----------------------------------------------------
-- Table `corpWalletJournal`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpWalletJournal` (
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
-- Table `corpWalletTransactions`
-- -----------------------------------------------------
CREATE TABLE `%prefix%corpWalletTransactions` (
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

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
