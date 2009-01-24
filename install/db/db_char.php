<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer Database char tables adder.
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
// Creating Table CharacterSheet
$query = "
  CREATE  TABLE IF NOT EXISTS `CharacterSheet` (
    `characterID` BIGINT UNSIGNED NOT NULL ,
    `name` VARCHAR(255) NOT NULL ,
    `race` VARCHAR(255) NOT NULL ,
    `bloodLine` VARCHAR(255) NOT NULL ,
    `gender` VARCHAR(255) NOT NULL ,
    `corporationName` VARCHAR(255) NOT NULL ,
    `corporationID` BIGINT UNSIGNED NOT NULL ,
    `balance` DECIMAL(17,2) NOT NULL ,
    `charisma` SMALLINT UNSIGNED NOT NULL ,
    `intelligence` SMALLINT UNSIGNED NOT NULL ,
    `memory` SMALLINT UNSIGNED NOT NULL ,
    `perception` SMALLINT UNSIGNED NOT NULL ,
    `willpower` SMALLINT UNSIGNED NOT NULL ,
    `cloneName` VARCHAR(255) NOT NULL ,
    `cloneSkillPoints` BIGINT UNSIGNED NOT NULL ,
    PRIMARY KEY (`characterID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci;
";
DBHandler('CharacterSheet', 'DCT', $query);

// Creating Table certificates
$query = "
  CREATE  TABLE IF NOT EXISTS `certificates` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `certificateID` BIGINT UNSIGNED NOT NULL ,
    PRIMARY KEY (`ownerID`, `certificateID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci
  COMMENT = 'Sub-table from CharacterSheet';
";
DBHandler('certificates', 'DCT', $query);

// Creating Table skills
$query = "
  CREATE  TABLE IF NOT EXISTS `skills` (
    `ownerID` BIGINT UNSIGNED NOT NULL ,
    `typeID` BIGINT UNSIGNED NOT NULL ,
    `level` SMALLINT UNSIGNED NOT NULL ,
    `skillpoints` BIGINT UNSIGNED NOT NULL ,
    `unpublished` BOOLEAN NOT NULL DEFAULT FALSE ,
    PRIMARY KEY (`ownerID`, `typeID`) )
  ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8
  COLLATE = utf8_unicode_ci
  COMMENT = 'Sub-table from CharacterSheet API';
";
DBHandler('skills', 'DCT', $query);

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
DBHandler('AccountBalance', 'DCT', $query);

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
DBHandler('IndustryJobs', 'DCT', $query);

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
DBHandler('MarketOrders', 'DCT', $query);

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
DBHandler('WalletJournal', 'DCT', $query);

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
DBHandler('WalletTransactions', 'DCT', $query);

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
DBHandler('AssetList', 'DCT', $query);
?>
