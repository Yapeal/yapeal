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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

/* char section */
INSERT INTO `charIndustryJobs` (`activityID`,`assemblyLineID`,
  `beginProductionTime`,`charMaterialMultiplier`,`charTimeMultiplier`,
  `completed`,`completedStatus`,`completedSuccessfully`,`containerID`,
  `containerLocationID`,`containerTypeID`,`endProductionTime`,
  `installedInSolarSystemID`,`installedItemCopy`,`installedItemFlag`,
  `installedItemID`,`installedItemLicensedProductionRunsRemaining`,
  `installedItemLocationID`,`installedItemMaterialLevel`,
  `installedItemProductivityLevel`,`installedItemQuantity`,`installedItemTypeID`,
  `installerID`,`installTime`,`jobID`,`licensedProductionRuns`,
  `materialMultiplier`,`outputFlag`,`outputLocationID`,`outputTypeID`,`ownerID`,
  `pauseProductionTime`,`runs`,`timeMultiplier`)
SELECT `IndustryJobs`.`activityID`,`IndustryJobs`.`assemblyLineID`,
  `IndustryJobs`.`beginProductionTime`,`IndustryJobs`.`charMaterialMultiplier`,
  `IndustryJobs`.`charTimeMultiplier`,`IndustryJobs`.`completed`,
  `IndustryJobs`.`completedStatus`,`IndustryJobs`.`completedSuccessfully`,
  `IndustryJobs`.`containerID`,`IndustryJobs`.`containerLocationID`,
  `IndustryJobs`.`containerTypeID`,`IndustryJobs`.`endProductionTime`,
  `IndustryJobs`.`installedInSolarSystemID`,`IndustryJobs`.`installedItemCopy`,
  `IndustryJobs`.`installedItemFlag`,`IndustryJobs`.`installedItemID`,
  `IndustryJobs`.`installedItemLicensedProductionRunsRemaining`,
  `IndustryJobs`.`installedItemLocationID`,
  `IndustryJobs`.`installedItemMaterialLevel`,
  `IndustryJobs`.`installedItemProductivityLevel`,
  `IndustryJobs`.`installedItemQuantity`,`IndustryJobs`.`installedItemTypeID`,
  `IndustryJobs`.`installerID`,`IndustryJobs`.`installTime`,
  `IndustryJobs`.`jobID`,`IndustryJobs`.`licensedProductionRuns`,
  `IndustryJobs`.`materialMultiplier`,`IndustryJobs`.`outputFlag`,
  `IndustryJobs`.`outputLocationID`,`IndustryJobs`.`outputTypeID`,
  `IndustryJobs`.`ownerID`,`IndustryJobs`.`pauseProductionTime`,
  `IndustryJobs`.`runs`,`IndustryJobs`.`timeMultiplier`
FROM `IndustryJobs`,`RegisteredCharacter`
WHERE `IndustryJobs`.`ownerID` = `RegisteredCharacter`.`characterID`;

INSERT INTO `charMarketOrders` (`accountKey`,`bid`,`changed`,`charID`,`duration`,
  `escrow`,`issued`,`minVolume`,`orderID`,`orderState`,`ownerID`,`price`,`range`,
  `stationID`,`typeID`,`volEntered`,`volRemaining`)
SELECT `MarketOrders`.`accountKey`,`MarketOrders`.`bid`,`MarketOrders`.`changed`,
  `MarketOrders`.`charID`,`MarketOrders`.`duration`,`MarketOrders`.`escrow`,
  `MarketOrders`.`issued`,`MarketOrders`.`minVolume`,`MarketOrders`.`orderID`,
  `MarketOrders`.`orderState`,`MarketOrders`.`ownerID`,`MarketOrders`.`price`,
  `MarketOrders`.`range`,`MarketOrders`.`stationID`,`MarketOrders`.`typeID`,
  `MarketOrders`.`volEntered`,`MarketOrders`.`volRemaining`
FROM `MarketOrders`,`RegisteredCharacter`
WHERE `MarketOrders`.`ownerID` = `RegisteredCharacter`.`characterID`;

INSERT INTO `charWalletJournal` (`accountKey`,`amount`,`argID1`,`argName1`,
  `balance`,`date`,`ownerID`,`ownerID1`,`ownerID2`,`ownerName1`,`ownerName2`,
  `reason`,`refID`,`refTypeID`)
SELECT `WalletJournal`.`accountKey`,`WalletJournal`.`amount`,
  `WalletJournal`.`argID1`,`WalletJournal`.`argName1`,`WalletJournal`.`balance`,
  `WalletJournal`.`date`,`WalletJournal`.`ownerID`,`WalletJournal`.`ownerID1`,
  `WalletJournal`.`ownerID2`,`WalletJournal`.`ownerName1`,
  `WalletJournal`.`ownerName2`,`WalletJournal`.`reason`,`WalletJournal`.`refID`,
  `WalletJournal`.`refTypeID`
FROM `WalletJournal`,`RegisteredCharacter`
WHERE `WalletJournal`.`ownerID` = `RegisteredCharacter`.`characterID`;

INSERT INTO `charWalletTransactions` (`accountKey`,`characterID`,`characterName`,
  `clientID`,`clientName`,`ownerID`,`price`,`quantity`,`stationID`,`stationName`,
  `transactionDateTime`,`transactionFor`,`transactionID`,`transactionType`,
  `typeID`,`typeName`)
SELECT `WalletTransactions`.`accountKey`,`WalletTransactions`.`characterID`,
  `WalletTransactions`.`characterName`,`WalletTransactions`.`clientID`,
  `WalletTransactions`.`clientName`,`WalletTransactions`.`ownerID`,
  `WalletTransactions`.`price`,`WalletTransactions`.`quantity`,
  `WalletTransactions`.`stationID`,`WalletTransactions`.`stationName`,
  `WalletTransactions`.`transactionDateTime`,
  `WalletTransactions`.`transactionFor`,`WalletTransactions`.`transactionID`,
  `WalletTransactions`.`transactionType`,`WalletTransactions`.`typeID`,
  `WalletTransactions`.`typeName`
FROM `WalletTransactions`,`RegisteredCharacter`
WHERE `WalletTransactions`.`ownerID` = `RegisteredCharacter`.`characterID`;

/* corp section */
INSERT INTO `corpIndustryJobs` (`activityID`,`assemblyLineID`,
  `beginProductionTime`,`charMaterialMultiplier`,`charTimeMultiplier`,
  `completed`,`completedStatus`,`completedSuccessfully`,`containerID`,
  `containerLocationID`,`containerTypeID`,`endProductionTime`,
  `installedInSolarSystemID`,`installedItemCopy`,`installedItemFlag`,
  `installedItemID`,`installedItemLicensedProductionRunsRemaining`,
  `installedItemLocationID`,`installedItemMaterialLevel`,
  `installedItemProductivityLevel`,`installedItemQuantity`,`installedItemTypeID`,
  `installerID`,`installTime`,`jobID`,`licensedProductionRuns`,
  `materialMultiplier`,`outputFlag`,`outputLocationID`,`outputTypeID`,`ownerID`,
  `pauseProductionTime`,`runs`,`timeMultiplier`)
SELECT `IndustryJobs`.`activityID`,`IndustryJobs`.`assemblyLineID`,
  `IndustryJobs`.`beginProductionTime`,`IndustryJobs`.`charMaterialMultiplier`,
  `IndustryJobs`.`charTimeMultiplier`,`IndustryJobs`.`completed`,
  `IndustryJobs`.`completedStatus`,`IndustryJobs`.`completedSuccessfully`,
  `IndustryJobs`.`containerID`,`IndustryJobs`.`containerLocationID`,
  `IndustryJobs`.`containerTypeID`,`IndustryJobs`.`endProductionTime`,
  `IndustryJobs`.`installedInSolarSystemID`,`IndustryJobs`.`installedItemCopy`,
  `IndustryJobs`.`installedItemFlag`,`IndustryJobs`.`installedItemID`,
  `IndustryJobs`.`installedItemLicensedProductionRunsRemaining`,
  `IndustryJobs`.`installedItemLocationID`,
  `IndustryJobs`.`installedItemMaterialLevel`,
  `IndustryJobs`.`installedItemProductivityLevel`,
  `IndustryJobs`.`installedItemQuantity`,`IndustryJobs`.`installedItemTypeID`,
  `IndustryJobs`.`installerID`,`IndustryJobs`.`installTime`,
  `IndustryJobs`.`jobID`,`IndustryJobs`.`licensedProductionRuns`,
  `IndustryJobs`.`materialMultiplier`,`IndustryJobs`.`outputFlag`,
  `IndustryJobs`.`outputLocationID`,`IndustryJobs`.`outputTypeID`,
  `IndustryJobs`.`ownerID`,`IndustryJobs`.`pauseProductionTime`,
  `IndustryJobs`.`runs`,`IndustryJobs`.`timeMultiplier`
FROM `IndustryJobs`,`RegisteredCorporation`
WHERE `IndustryJobs`.`ownerID` = `RegisteredCorporation`.`corporationID`;

INSERT INTO `corpMarketOrders` (`accountKey`,`bid`,`changed`,`charID`,`duration`,
  `escrow`,`issued`,`minVolume`,`orderID`,`orderState`,`ownerID`,`price`,`range`,
  `stationID`,`typeID`,`volEntered`,`volRemaining`)
SELECT `MarketOrders`.`accountKey`,`MarketOrders`.`bid`,`MarketOrders`.`changed`,
  `MarketOrders`.`charID`,`MarketOrders`.`duration`,`MarketOrders`.`escrow`,
  `MarketOrders`.`issued`,`MarketOrders`.`minVolume`,`MarketOrders`.`orderID`,
  `MarketOrders`.`orderState`,`MarketOrders`.`ownerID`,`MarketOrders`.`price`,
  `MarketOrders`.`range`,`MarketOrders`.`stationID`,`MarketOrders`.`typeID`,
  `MarketOrders`.`volEntered`,`MarketOrders`.`volRemaining`
FROM `MarketOrders`,`RegisteredCorporation`
WHERE `ownerID` = `corporationID`;

INSERT INTO `corpWalletJournal` (`accountKey`,`amount`,`argID1`,`argName1`,
  `balance`,`date`,`ownerID`,`ownerID1`,`ownerID2`,`ownerName1`,`ownerName2`,
  `reason`,`refID`,`refTypeID`)
SELECT `WalletJournal`.`accountKey`,`WalletJournal`.`amount`,
  `WalletJournal`.`argID1`,`WalletJournal`.`argName1`,`WalletJournal`.`balance`,
  `WalletJournal`.`date`,`WalletJournal`.`ownerID`,`WalletJournal`.`ownerID1`,
  `WalletJournal`.`ownerID2`,`WalletJournal`.`ownerName1`,
  `WalletJournal`.`ownerName2`,`WalletJournal`.`reason`,`WalletJournal`.`refID`,
  `WalletJournal`.`refTypeID`
FROM `WalletJournal`,`RegisteredCorporation`
WHERE `ownerID` = `corporationID`;

INSERT INTO `corpWalletTransactions` (`accountKey`,`characterID`,`characterName`,
  `clientID`,`clientName`,`ownerID`,`price`,`quantity`,`stationID`,`stationName`,
  `transactionDateTime`,`transactionFor`,`transactionID`,`transactionType`,
  `typeID`,`typeName`)
SELECT `WalletTransactions`.`accountKey`,`WalletTransactions`.`characterID`,
  `WalletTransactions`.`characterName`,`WalletTransactions`.`clientID`,
  `WalletTransactions`.`clientName`,`WalletTransactions`.`ownerID`,
  `WalletTransactions`.`price`,`WalletTransactions`.`quantity`,
  `WalletTransactions`.`stationID`,`WalletTransactions`.`stationName`,
  `WalletTransactions`.`transactionDateTime`,
  `WalletTransactions`.`transactionFor`,`WalletTransactions`.`transactionID`,
  `WalletTransactions`.`transactionType`,`WalletTransactions`.`typeID`,
  `WalletTransactions`.`typeName`
FROM `WalletTransactions`,`RegisteredCorporation`
WHERE `WalletTransactions`.`ownerID` = `RegisteredCorporation`.`corporationID`;

/* util section */
INSERT INTO `utilRegisteredCharacter` (`characterID`,`userID`,`name`,
  `corporationID`,`corporationName`,`isActive`,`graphic`,`graphicType`)
SELECT `RegisteredCharacter`.`characterID`,`RegisteredCharacter`.`userID`,
  `RegisteredCharacter`.`name`,`RegisteredCharacter`.`corporationID`,
  `RegisteredCharacter`.`corporationName`,`RegisteredCharacter`.`isActive`,
  `RegisteredCharacter`.`graphic`,`RegisteredCharacter`.`graphicType`
FROM `RegisteredCharacter`;

INSERT INTO `utilRegisteredCorporation` (`characterID`,`corporationID`,`graphic`,
  `graphicType`,`isActive`)
SELECT `RegisteredCorporation`.`characterID`,
  `RegisteredCorporation`.`corporationID`,`RegisteredCorporation`.`graphic`,
  `RegisteredCorporation`.`graphicType`,`RegisteredCorporation`.`isActive`
FROM `RegisteredCorporation`;

INSERT INTO `utilRegisteredUser` (`fullApiKey`,`limitedApiKey`,`userID`)
SELECT `RegisteredUser`.`fullApiKey`,
  `RegisteredUser`.`limitedApiKey`,
  `RegisteredUser`.`userID`
FROM `RegisteredUser`;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
