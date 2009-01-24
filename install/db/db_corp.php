<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer Database corp tables adder.
 *
 *
 * PHP version 5
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
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
// Creating Table CorporationSheet
$query = "
  CREATE  TABLE IF NOT EXISTS `CorporationSheet` (
    `corporationID` BIGINT UNSIGNED NOT NULL ,
    `corporationName` VARCHAR(255) NOT NULL ,
    `ticker` VARCHAR(255) NOT NULL ,
    `ceoID` BIGINT UNSIGNED NOT NULL ,
    `ceoName` VARCHAR(255) NOT NULL ,
    `stationID` BIGINT UNSIGNED NOT NULL ,
    `stationName` VARCHAR(255) NOT NULL ,
    `description` TEXT NULL DEFAULT NULL ,
    `url` VARCHAR(255) NULL DEFAULT NULL ,
    `allianceId` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `allianceName` VARCHAR(255) NULL DEFAULT NULL ,
    `taxRate` DECIMAL(17,2) UNSIGNED NOT NULL ,
    `memberCount` SMALLINT UNSIGNED NOT NULL ,
    `shares` BIGINT UNSIGNED NOT NULL ,
    `memberLimit` SMALLINT UNSIGNED NOT NULL ,
    PRIMARY KEY (`corporationID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("CorporationSheet", "DCT", $query);

// Creating Table logo
$query = "
  CREATE  TABLE IF NOT EXISTS `logo` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `graphicID` BIGINT UNSIGNED NOT NULL ,
    `shape1` SMALLINT UNSIGNED NOT NULL ,
    `shape2` SMALLINT UNSIGNED NOT NULL ,
    `shape3` SMALLINT UNSIGNED NOT NULL ,
    `color1` BIGINT UNSIGNED NOT NULL ,
    `color2` BIGINT UNSIGNED NOT NULL ,
    `color3` BIGINT UNSIGNED NOT NULL ,
    PRIMARY KEY (`ownerID`, `graphicID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci
  COMMENT = 'Sub-table from CorporationSheet API';
";
DBHandler("logo", "DCT", $query);

// Creating Table walletDivisions
$query = "
  CREATE  TABLE IF NOT EXISTS `walletDivisions` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `accountKey` SMALLINT UNSIGNED NOT NULL ,
    `description` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`ownerID`, `accountKey`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci
  COMMENT = 'This is sub-table from CorporationSheet API';
";
DBHandler("walletDivisions", "DCT", $query);

// Creating Table divisions
$query = "
  CREATE  TABLE IF NOT EXISTS `divisions` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `accountKey` SMALLINT UNSIGNED NOT NULL ,
    `description` VARCHAR(255) NOT NULL ,
    PRIMARY KEY (`ownerID`, `accountKey`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci
  COMMENT = 'Sub-table from CorporationSheet API';
";
DBHandler("divisions", "DCT", $query);

// Creating Table AccountBalance
$query = "
  CREATE  TABLE IF NOT EXISTS `AccountBalance` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `accountID` BIGINT UNSIGNED NOT NULL ,
    `accountKey` SMALLINT UNSIGNED NOT NULL ,
    `balance` DECIMAL(17,2) NOT NULL ,
    PRIMARY KEY (`ownerID`, `accountID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("AccountBalance", "DCT", $query);

// Creating Table IndustryJobs
$query = "
  CREATE  TABLE IF NOT EXISTS `IndustryJobs` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `installTime` DATETIME NOT NULL ,
    `jobID` BIGINT UNSIGNED NOT NULL ,
    `assemblyLineID` BIGINT UNSIGNED NOT NULL ,
    `containerID` BIGINT UNSIGNED NOT NULL ,
    `installedItemID` BIGINT UNSIGNED NOT NULL ,
    `installedItemLocationID` BIGINT UNSIGNED NOT NULL ,
    `installedItemQuantity` BIGINT UNSIGNED NOT NULL ,
    `installedItemProductivityLevel` INT NOT NULL ,
    `installedItemMaterialLevel` INT NOT NULL ,
    `installedItemLicensedProductionRunsRemaining` BIGINT NOT NULL ,
    `outputLocationID` BIGINT UNSIGNED NOT NULL ,
    `installerID` BIGINT UNSIGNED NOT NULL ,
    `runs` BIGINT UNSIGNED NOT NULL ,
    `licensedProductionRuns` BIGINT UNSIGNED NOT NULL ,
    `installedInSolarSystemID` BIGINT UNSIGNED NOT NULL ,
    `containerLocationID` BIGINT UNSIGNED NOT NULL ,
    `materialMultiplier` DECIMAL(17,2) NOT NULL ,
    `charMaterialMultiplier` DECIMAL(17,2) NOT NULL ,
    `timeMultiplier` DECIMAL(17,2) NOT NULL ,
    `charTimeMultiplier` DECIMAL(17,2) NOT NULL ,
    `installedItemTypeID` BIGINT UNSIGNED NOT NULL ,
    `outputTypeID` BIGINT UNSIGNED NOT NULL ,
    `containerTypeID` BIGINT UNSIGNED NOT NULL ,
    `installedItemCopy` BIGINT UNSIGNED NOT NULL ,
    `completed` SMALLINT UNSIGNED NOT NULL ,
    `completedSuccessfully` SMALLINT UNSIGNED NOT NULL ,
    `installedItemFlag` SMALLINT UNSIGNED NOT NULL ,
    `outputFlag` SMALLINT UNSIGNED NOT NULL ,
    `activityID` SMALLINT UNSIGNED NOT NULL ,
    `completedStatus` SMALLINT UNSIGNED NOT NULL ,
    `beginProductionTime` DATETIME NOT NULL ,
    `endProductionTime` DATETIME NOT NULL ,
    `pauseProductionTime` DATETIME NOT NULL ,
    PRIMARY KEY (`ownerID`, `installTime`, `jobID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("IndustryJobs", "DCT", $query);

// Creating Table MarketOrders
$query = "
  CREATE  TABLE IF NOT EXISTS `MarketOrders` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `issued` DATETIME NOT NULL ,
    `orderID` BIGINT UNSIGNED NOT NULL ,
    `accountKey` SMALLINT UNSIGNED NOT NULL ,
    `charID` BIGINT UNSIGNED NOT NULL ,
    `stationID` BIGINT UNSIGNED NOT NULL ,
    `volEntered` BIGINT UNSIGNED NOT NULL ,
    `volRemaining` BIGINT UNSIGNED NOT NULL ,
    `minVolume` BIGINT UNSIGNED NOT NULL ,
    `orderState` TINYINT UNSIGNED NOT NULL ,
    `typeID` BIGINT UNSIGNED NOT NULL ,
    `range` SMALLINT NOT NULL ,
    `duration` SMALLINT UNSIGNED NOT NULL ,
    `escrow` DECIMAL(17,2) NOT NULL ,
    `price` DECIMAL(17,2) NOT NULL ,
    `bid` BOOLEAN NOT NULL ,
    `changed` TIMESTAMP NOT NULL COMMENT 'Added to API to allow tracking of when order was last active. Auto updated by MySQL' ,
    PRIMARY KEY (`ownerID`, `issued`, `orderID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("MarketOrders", "DCT", $query);

// Creating Table MemberTracking
$query = "
  CREATE  TABLE IF NOT EXISTS `MemberTracking` (
    `characterID` BIGINT UNSIGNED NOT NULL ,
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `name` VARCHAR(255) NOT NULL ,
    `startDateTime` DATETIME NOT NULL ,
    `baseID` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `base` VARCHAR(255) NULL DEFAULT NULL ,
    `title` TEXT NULL DEFAULT NULL ,
    `logonDateTime` DATETIME NOT NULL ,
    `logoffDateTime` DATETIME NOT NULL ,
    `locationID` BIGINT UNSIGNED NOT NULL ,
    `location` VARCHAR(255) NOT NULL ,
    `shipTypeID` BIGINT UNSIGNED NOT NULL ,
    `shipType` VARCHAR(255) NOT NULL ,
    `roles` VARCHAR(64) NOT NULL ,
    `grantableRoles` VARCHAR(64) NOT NULL ,
    PRIMARY KEY (`characterID`),
      KEY `index1` (`ownerID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("MemberTracking", "DCT", $query);

// Creating Table StarbaseList
$query = "
  CREATE  TABLE IF NOT EXISTS `StarbaseList` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `itemID` BIGINT UNSIGNED NOT NULL ,
    `typeID` BIGINT UNSIGNED NOT NULL ,
    `locationID` BIGINT UNSIGNED NOT NULL ,
    `moonID` BIGINT UNSIGNED NOT NULL ,
    `state` SMALLINT UNSIGNED NOT NULL ,
    `stateTimestamp` DATETIME NOT NULL ,
    `onlineTimestamp` DATETIME NOT NULL ,
    PRIMARY KEY (`ownerID`, `itemID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("StarbaseList", "DCT", $query);

// Creating Table WalletJournal
$query = "
  CREATE  TABLE IF NOT EXISTS `WalletJournal` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `date` DATETIME NOT NULL ,
    `refID` BIGINT UNSIGNED NOT NULL ,
    `refTypeID` TINYINT UNSIGNED NOT NULL ,
    `ownerName1` VARCHAR(255) NULL ,
    `ownerID1` BIGINT UNSIGNED NULL ,
    `ownerName2` VARCHAR(255) NULL ,
    `ownerID2` BIGINT UNSIGNED NULL ,
    `argName1` VARCHAR(255) NULL ,
    `argID1` BIGINT UNSIGNED NULL ,
    `amount` DECIMAL(17,2) NOT NULL ,
    `balance` DECIMAL(17,2) NOT NULL ,
    `reason` TEXT NULL ,
    `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
    PRIMARY KEY (`ownerID`, `date`, `refID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("WalletJournal", "DCT", $query);

// Creating Table WalletTransactions
$query = "
  CREATE  TABLE IF NOT EXISTS `WalletTransactions` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `transactionDateTime` DATETIME NOT NULL ,
    `transactionID` BIGINT UNSIGNED NOT NULL ,
    `quantity` BIGINT UNSIGNED NOT NULL ,
    `typeName` VARCHAR(255) NOT NULL ,
    `typeID` BIGINT UNSIGNED NOT NULL ,
    `price` DECIMAL(17,2) NOT NULL ,
    `clientID` BIGINT UNSIGNED NULL ,
    `clientName` VARCHAR(255) NULL ,
    `characterID` BIGINT UNSIGNED NULL ,
    `characterName` VARCHAR(255) NULL ,
    `stationID` BIGINT UNSIGNED NULL ,
    `stationName` VARCHAR(255) NULL ,
    `transactionType` VARCHAR(255) NOT NULL DEFAULT 'sell' ,
    `transactionFor` VARCHAR(255) NOT NULL DEFAULT 'corporation' ,
    `accountKey` SMALLINT UNSIGNED NOT NULL COMMENT 'Nothing in XML results IDs which wallet it is for we have to add it. Taken from POST call params.' ,
    PRIMARY KEY (`ownerID`, `transactionDateTime`, `transactionID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("WalletTransactions", "DCT", $query);

// Creating Table AssetList
$query = "
  CREATE  TABLE IF NOT EXISTS `AssetList` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `itemID` BIGINT UNSIGNED NOT NULL ,
    `locationID` BIGINT UNSIGNED NOT NULL ,
    `typeID` BIGINT UNSIGNED NOT NULL ,
    `quantity` BIGINT UNSIGNED NOT NULL ,
    `flag` SMALLINT UNSIGNED NOT NULL ,
    `singleton` BOOLEAN NOT NULL ,
    `lft` BIGINT UNSIGNED NULL DEFAULT NULL ,
    `rgt` BIGINT UNSIGNED NULL DEFAULT NULL ,
    PRIMARY KEY (`ownerID`, `itemID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler("AssetList", "DCT", $query);
?>
