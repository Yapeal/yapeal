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
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Data `charAccountBalance`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charAssetList`
-- -----------------------------------------------------
INSERT INTO `%prefix%charAssetList` (`flag`,`itemID`,`lft`,
  `locationID`,`ownerID`,`quantity`,
  `rgt`,`singleton`,`typeID`)
SELECT A.`flag`,
  A.`itemID`, A.`lft`,
  A.`locationID`, A.`ownerID`,
  A.`quantity`, A.`rgt`,
  A.`singleton`, A.`typeID`
FROM `AssetList` A,`RegisteredCharacter` R
WHERE A.`ownerID` = R.`characterID`;

-- -----------------------------------------------------
-- Data `charAttributes`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charAttributeEnhancers`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charcertificates`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charCharacterSheet`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charCorporationRoles`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charCorporationRolesAtBase`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charCorporationRolesAtHQ`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charCorporationRolesAtOther`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charCorporationTitles`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charIndustryJobs`
-- -----------------------------------------------------
INSERT INTO `%prefix%charIndustryJobs` (`activityID`,`assemblyLineID`,
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
SELECT I.`activityID`,I.`assemblyLineID`,
  I.`beginProductionTime`,I.`charMaterialMultiplier`,
  I.`charTimeMultiplier`,I.`completed`,
  I.`completedStatus`,I.`completedSuccessfully`,
  I.`containerID`,I.`containerLocationID`,
  I.`containerTypeID`,I.`endProductionTime`,
  I.`installedInSolarSystemID`,I.`installedItemCopy`,
  I.`installedItemFlag`,I.`installedItemID`,
  I.`installedItemLicensedProductionRunsRemaining`,
  I.`installedItemLocationID`,
  I.`installedItemMaterialLevel`,
  I.`installedItemProductivityLevel`,
  I.`installedItemQuantity`,I.`installedItemTypeID`,
  I.`installerID`,I.`installTime`,
  I.`jobID`,I.`licensedProductionRuns`,
  I.`materialMultiplier`,I.`outputFlag`,
  I.`outputLocationID`,I.`outputTypeID`,
  I.`ownerID`,I.`pauseProductionTime`,
  I.`runs`,I.`timeMultiplier`
FROM `IndustryJobs` I,`RegisteredCharacter` R
WHERE I.`ownerID` = R.`characterID`
ON DUPLICATE KEY UPDATE `activityID`=VALUES(`activityID`),`assemblyLineID`=VALUES(`assemblyLineID`),
  `beginProductionTime`=VALUES(`beginProductionTime`),`charMaterialMultiplier`=VALUES(`charMaterialMultiplier`),
  `charTimeMultiplier`=VALUES(`charTimeMultiplier`),`completed`=VALUES(`completed`),
  `completedStatus`=VALUES(`completedStatus`),`completedSuccessfully`=VALUES(`completedSuccessfully`),
  `containerID`=VALUES(`containerID`),`containerLocationID`=VALUES(`containerLocationID`),
  `containerTypeID`=VALUES(`containerTypeID`),`endProductionTime`=VALUES(`endProductionTime`),
  `installedInSolarSystemID`=VALUES(`installedInSolarSystemID`),`installedItemCopy`=VALUES(`installedItemCopy`),
  `installedItemFlag`=VALUES(`installedItemFlag`),`installedItemID`=VALUES(`installedItemID`),
  `installedItemLicensedProductionRunsRemaining`=VALUES(`installedItemLicensedProductionRunsRemaining`),
  `installedItemLocationID`=VALUES(`installedItemLocationID`),
  `installedItemMaterialLevel`=VALUES(`installedItemMaterialLevel`),
  `installedItemProductivityLevel`=VALUES(`installedItemProductivityLevel`),
  `installedItemQuantity`=VALUES(`installedItemQuantity`),`installedItemTypeID`=VALUES(`installedItemTypeID`),
  `installerID`=VALUES(`installerID`),`installTime`=VALUES(`installTime`),
  `jobID`=VALUES(`jobID`),`licensedProductionRuns`=VALUES(`licensedProductionRuns`),
  `materialMultiplier`=VALUES(`materialMultiplier`),`outputFlag`=VALUES(`outputFlag`),
  `outputLocationID`=VALUES(`outputLocationID`),`outputTypeID`=VALUES(`outputTypeID`),
  `ownerID`=VALUES(`ownerID`),`pauseProductionTime`=VALUES(`pauseProductionTime`),
  `runs`=VALUES(`runs`),`timeMultiplier`=VALUES(`timeMultiplier`);

-- -----------------------------------------------------
-- Data `charMarketOrders`
-- -----------------------------------------------------
INSERT INTO `%prefix%charMarketOrders` (`accountKey`,`bid`,`changed`,`charID`,`duration`,
  `escrow`,`issued`,`minVolume`,`orderID`,`orderState`,`ownerID`,`price`,`range`,
  `stationID`,`typeID`,`volEntered`,`volRemaining`)
SELECT M.`accountKey`,M.`bid`,M.`changed`,
  M.`charID`,M.`duration`,M.`escrow`,
  M.`issued`,M.`minVolume`,M.`orderID`,
  M.`orderState`,M.`ownerID`,M.`price`,
  M.`range`,M.`stationID`,M.`typeID`,
  M.`volEntered`,M.`volRemaining`
FROM `MarketOrders` M,`RegisteredCharacter` R
WHERE M.`ownerID` = R.`characterID`
ON DUPLICATE KEY UPDATE `accountKey`=VALUES(`accountKey`),`bid`=VALUES(`bid`),
  `changed`=VALUES(`changed`),`charID`=VALUES(`charID`),`duration`=VALUES(`duration`),
  `escrow`=VALUES(`escrow`),`issued`=VALUES(`issued`),`minVolume`=VALUES(`minVolume`),
  `orderID`=VALUES(`orderID`),`orderState`=VALUES(`orderState`),`ownerID`=VALUES(`ownerID`),
  `price`=VALUES(`price`),`range`=VALUES(`range`),`stationID`=VALUES(`stationID`),
  `typeID`=VALUES(`typeID`),`volEntered`=VALUES(`volEntered`),`volRemaining`=VALUES(`volRemaining`);

-- -----------------------------------------------------
-- Data `charskills`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `charWalletJournal`
-- -----------------------------------------------------
INSERT INTO `%prefix%charWalletJournal` (`accountKey`,`amount`,`argID1`,`argName1`,
  `balance`,`date`,`ownerID`,`ownerID1`,`ownerID2`,`ownerName1`,`ownerName2`,
  `reason`,`refID`,`refTypeID`)
SELECT W.`accountKey`,W.`amount`,
  W.`argID1`,W.`argName1`,W.`balance`,
  W.`date`,W.`ownerID`,W.`ownerID1`,
  W.`ownerID2`,W.`ownerName1`,
  W.`ownerName2`,W.`reason`,W.`refID`,
  W.`refTypeID`
FROM `WalletJournal` W,`RegisteredCharacter` R
WHERE W.`ownerID` = R.`characterID`
ON DUPLICATE KEY UPDATE `accountKey`=VALUES(`accountKey`),`amount`=VALUES(`amount`),
  `argID1`=VALUES(`argID1`),`argName1`=VALUES(`argName1`),`balance`=VALUES(`balance`),
  `date`=VALUES(`date`),`ownerID`=VALUES(`ownerID`),`ownerID1`=VALUES(`ownerID1`),
  `ownerID2`=VALUES(`ownerID2`),`ownerName1`=VALUES(`ownerName1`),`ownerName2`=VALUES(`ownerName2`),
  `reason`=VALUES(`reason`),`refID`=VALUES(`refID`),`refTypeID`=VALUES(`refTypeID`);

-- -----------------------------------------------------
-- Data `charWalletTransactions`
-- -----------------------------------------------------
INSERT INTO `%prefix%charWalletTransactions` (`accountKey`,`characterID`,`characterName`,
  `clientID`,`clientName`,`ownerID`,`price`,`quantity`,`stationID`,`stationName`,
  `transactionDateTime`,`transactionFor`,`transactionID`,`transactionType`,
  `typeID`,`typeName`)
SELECT W.`accountKey`,W.`characterID`,
  W.`characterName`,W.`clientID`,
  W.`clientName`,W.`ownerID`,
  W.`price`,W.`quantity`,
  W.`stationID`,W.`stationName`,
  W.`transactionDateTime`,
  W.`transactionFor`,W.`transactionID`,
  W.`transactionType`,W.`typeID`,
  W.`typeName`
FROM `WalletTransactions` W,`RegisteredCharacter` R
WHERE W.`ownerID` = R.`characterID`
ON DUPLICATE KEY UPDATE `accountKey`=VALUES(`accountKey`),`characterID`=VALUES(`characterID`),
  `characterName`=VALUES(`characterName`),`clientID`=VALUES(`clientID`),`clientName`=VALUES(`clientName`),
  `ownerID`=VALUES(`ownerID`),`price`=VALUES(`price`),`quantity`=VALUES(`quantity`),
  `stationID`=VALUES(`stationID`),`stationName`=VALUES(`stationName`),`transactionDateTime`=VALUES(`transactionDateTime`),
  `transactionFor`=VALUES(`transactionFor`),`transactionID`=VALUES(`transactionID`),`transactionType`=VALUES(`transactionType`),
  `typeID`=VALUES(`typeID`),`typeName`=VALUES(`typeName`);

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
