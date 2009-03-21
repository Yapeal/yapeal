/**
 * All in one MySQL file.
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

/* account section */
CREATE TABLE IF NOT EXISTS `accountCharacters` (
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `name` VARCHAR(255) NOT NULL ,
  `userID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

/* char section */
CREATE TABLE IF NOT EXISTS `charAccountBalance` (
  `accountID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charAssetList` (
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

CREATE TABLE IF NOT EXISTS `charAttackers` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `allianceName` VARCHAR(255) NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `characterName` VARCHAR(255) NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NULL ,
  `damageDone` BIGINT UNSIGNED NOT NULL ,
  `finalBlow` BOOLEAN DEFAULT FALSE ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `securityStatus` FLOAT NOT NULL DEFAULT 0.0 ,
  `shipTypeID`  BIGINT UNSIGNED NOT NULL ,
  `weaponTypeID`  BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`killID`, `characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;
COMMENT = 'Sub-table from KillLog';

CREATE TABLE IF NOT EXISTS `charAttributes` (
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

CREATE TABLE IF NOT EXISTS `charAttributeEnhancers` (
  `augmentatorName` VARCHAR(255) NOT NULL ,
  `augmentatorValue` SMALLINT UNSIGNED NOT NULL ,
  `bonusName` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `bonusName`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet';

CREATE TABLE IF NOT EXISTS `charCertificates` (
  `certificateID` BIGINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `certificateID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CharacterSheet';

CREATE TABLE IF NOT EXISTS `charCharacterSheet` (
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

CREATE TABLE IF NOT EXISTS `charCorporationRoles` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charCorporationRolesAtBase` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charCorporationRolesAtHQ` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charCorporationRolesAtOther` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `roleID` BIGINT UNSIGNED NOT NULL ,
  `roleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `roleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charCorporationTitles` (
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `titleID` BIGINT UNSIGNED NOT NULL ,
  `titleName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`, `titleID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charIndustryJobs` (
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

CREATE TABLE IF NOT EXISTS `charItems` (
  `flag` SMALLINT UNSIGNED NOT NULL ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `qtyDropped` BIGINT UNSIGNED NOT NULL ,
  `qtyDestroyed` BIGINT UNSIGNED NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`killID`, `typeID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;
COMMENT = 'Sub-table from KillLog';

CREATE TABLE IF NOT EXISTS `charKillLog` (
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

CREATE TABLE IF NOT EXISTS `charMarketOrders` (
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

CREATE TABLE IF NOT EXISTS `charSkillQueue` (
  `endSP` BIGINT UNSIGNED NOT NULL ,
  `endTime` DATETIME NOT NULL ,
  `level` SMALLINT UNSIGNED NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `queuePosition` BIGINT UNSIGNED NOT NULL ,
  `startSP` BIGINT UNSIGNED NOT NULL ,
  `startTime` DATETIME NOT NULL ,
  `typeID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `queuePosition`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `charSkills` (
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

CREATE TABLE IF NOT EXISTS `charStandingsFromAgents` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `charStandingsFromFactions` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `charStandingsFromNPCCorporations` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `charStandingsToCharacters` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `charStandingsToCorporations` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `charVictim` (
  `allianceID` BIGINT UNSIGNED NOT NULL ,
  `allianceName` VARCHAR(255) NULL ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `characterName` VARCHAR(255) NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NULL ,
  `damageTaken` BIGINT UNSIGNED NOT NULL ,
  `killID` BIGINT UNSIGNED NOT NULL ,
  `shipTypeID`  BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`killID`, `characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;
COMMENT = 'Sub-table from KillLog';

CREATE TABLE IF NOT EXISTS `charWalletJournal` (
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

CREATE TABLE IF NOT EXISTS `charWalletTransactions` (
  `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
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

/* corp section */
CREATE TABLE IF NOT EXISTS `corpAccountBalance` (
  `accountID` BIGINT UNSIGNED NOT NULL ,
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `balance` DECIMAL(17,2) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `corpAssetList` (
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

CREATE TABLE IF NOT EXISTS `corpAttackers` (
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

CREATE TABLE IF NOT EXISTS `corpCorporationSheet` (
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

CREATE TABLE IF NOT EXISTS `corpContainerLog` (
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

CREATE TABLE IF NOT EXISTS `corpDivisions` (
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountKey`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'Sub-table from CorporationSheet API';

CREATE TABLE IF NOT EXISTS `corpIndustryJobs` (
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

CREATE TABLE IF NOT EXISTS `corpItems` (
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

CREATE TABLE IF NOT EXISTS `corpKillLog` (
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

CREATE TABLE IF NOT EXISTS `corpLogo` (
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

CREATE TABLE IF NOT EXISTS `corpMarketOrders` (
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

CREATE TABLE IF NOT EXISTS `corpMemberTracking` (
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

CREATE TABLE IF NOT EXISTS `corpStandingsFromAgents` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `corpStandingsFromFactions` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `corpStandingsFromNPCCorporations` (
  `fromID` bigint(20) unsigned NOT NULL,
  `fromName` varchar(255) collate utf8_unicode_ci NOT NULL,
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  PRIMARY KEY  (`ownerID`, `fromID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `corpStandingsToAlliances` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `corpStandingsToCharacters` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `corpStandingsToCorporations` (
  `ownerID` bigint(20) unsigned NOT NULL,
  `standing` decimal(17,2) NOT NULL,
  `toID` bigint(20) unsigned NOT NULL,
  `toName` varchar(255) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`ownerID`, `toID`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci
COMMENT='Sub-table from Standings API';

CREATE TABLE IF NOT EXISTS `corpStarbaseList` (
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

CREATE TABLE IF NOT EXISTS `corpVictim` (
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

CREATE TABLE IF NOT EXISTS `corpWalletDivisions` (
  `accountKey` SMALLINT UNSIGNED NOT NULL ,
  `description` VARCHAR(255) NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerID`, `accountKey`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci
COMMENT = 'This is sub-table from CorporationSheet API';

CREATE TABLE IF NOT EXISTS `corpWalletJournal` (
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

CREATE TABLE IF NOT EXISTS `corpWalletTransactions` (
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

/* eve section */
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

/* map section */
CREATE TABLE IF NOT EXISTS `mapJumps` (
  `shipJumps` BIGINT UNSIGNED NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`solarSystemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `mapKills` (
  `factionKills` BIGINT UNSIGNED NOT NULL ,
  `podKills` BIGINT UNSIGNED NOT NULL ,
  `shipKills` BIGINT UNSIGNED NOT NULL ,
  `solarSystemID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`solarSystemID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `mapSovereignty` (
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

/* server section */
CREATE TABLE IF NOT EXISTS `serverServerStatus` (
  `onlinePlayers` BIGINT UNSIGNED NOT NULL ,
  `serverName` CHAR(32) NOT NULL,
  `serverOpen` BOOLEAN NOT NULL ,
  PRIMARY KEY (`serverName`) )
ENGINE = MEMORY
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

/* util section */
CREATE TABLE IF NOT EXISTS `utilConfig` (
  `Name` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `Value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`Name`) )
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COLLATE=utf8_unicode_ci;
INSERT INTO `utilConfig` VALUES('version', '$Revision: 694 $')
ON DUPLICATE KEY UPDATE `Value`=VALUES(`Value`);

CREATE TABLE IF NOT EXISTS `utilCachedUntil` (
  `cachedUntil` DATETIME NOT NULL ,
  `ownerID` BIGINT UNSIGNED NOT NULL ,
  `tableName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`tableName`, `ownerID`) )
ENGINE = MEMORY
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `utilRegisteredCharacter` (
  `activeAPI` TEXT COMMENT 'A space separated list of APIs to get for this character' ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `corporationName` VARCHAR(255) NOT NULL ,
  `graphic` BLOB NULL DEFAULT NULL ,
  `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
  `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
  `name` VARCHAR(255) NOT NULL ,
  `userID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`characterID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `utilRegisteredCorporation` (
  `activeAPI` TEXT COMMENT 'A space separated list of APIs to get for this corporation' ,
  `characterID` BIGINT UNSIGNED NOT NULL ,
  `corporationID` BIGINT UNSIGNED NOT NULL ,
  `graphic` BLOB NULL DEFAULT NULL ,
  `graphicType` VARCHAR(16) NULL DEFAULT NULL COMMENT 'One of jpg, png, gif' ,
  `isActive` BOOLEAN NOT NULL DEFAULT FALSE ,
  PRIMARY KEY (`corporationID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `utilRegisteredUser` (
  `fullApiKey` VARCHAR(64) NOT NULL ,
  `limitedApiKey` VARCHAR(64) NULL DEFAULT NULL ,
  `userID` BIGINT UNSIGNED NOT NULL ,
  PRIMARY KEY (`userID`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
