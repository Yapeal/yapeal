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

/* account section */
DROP TABLE IF EXISTS `accountCharacters` ;

/* char section */
DROP TABLE IF EXISTS `charAccountBalance` ;

DROP TABLE IF EXISTS `charAssetList` ;

DROP TABLE IF EXISTS `charAttributes` ;

DROP TABLE IF EXISTS `charAttributeEnhancers` ;

DROP TABLE IF EXISTS `charCertificates` ;

DROP TABLE IF EXISTS `charCharacterSheet` ;

DROP TABLE IF NOT EXISTS `charCorporationRoles` ;

DROP TABLE IF NOT EXISTS `charCorporationRolesAtBase` ;

DROP TABLE IF NOT EXISTS `charCorporationRolesAtHQ` ;

DROP TABLE IF NOT EXISTS `charCorporationRolesAtOther` ;

DROP TABLE IF NOT EXISTS `charCorporationTitles` ;

DROP TABLE IF EXISTS `charIndustryJobs` ;

DROP TABLE IF EXISTS `charMarketOrders` ;

DROP TABLE IF EXISTS `charSkills` ;

DROP TABLE IF EXISTS `charWalletJournal` ;

DROP TABLE IF EXISTS `charWalletTransactions` ;

/* corp section */
DROP TABLE IF EXISTS `corpAccountBalance` ;

DROP TABLE IF EXISTS `corpAssetList` ;

DROP TABLE IF EXISTS `corpContainerLog` ;

DROP TABLE IF EXISTS `corpCorporationSheet` ;

DROP TABLE IF EXISTS `corpDivisions` ;

DROP TABLE IF EXISTS `corpIndustryJobs` ;

DROP TABLE IF EXISTS `corpLogo` ;

DROP TABLE IF EXISTS `corpMarketOrders` ;

DROP TABLE IF EXISTS `corpMemberTracking` ;

DROP TABLE IF EXISTS `corpStarbaseList` ;

DROP TABLE IF EXISTS `corpWalletDivisions` ;

DROP TABLE IF EXISTS `corpWalletJournal` ;

DROP TABLE IF EXISTS `corpWalletTransactions` ;

/* eve section */
DROP TABLE IF EXISTS `eveAllianceList` ;

DROP TABLE IF EXISTS `eveConquerableStationList` ;

DROP TABLE IF EXISTS `eveErrorList` ;

DROP TABLE IF EXISTS `eveMemberCorporations` ;

DROP TABLE IF EXISTS `eveRefTypes` ;

/* map section */
DROP TABLE IF EXISTS `mapJumps` ;

DROP TABLE IF EXISTS `mapKills` ;

DROP TABLE IF EXISTS `mapSovereignty` ;

/* server section */
DROP TABLE IF EXISTS `serverServerStatus` ;

/* util section */
DROP TABLE IF EXISTS `utilCachedUntil` ;

DROP TABLE IF EXISTS `utilRegisteredUser` ;

DROP TABLE IF EXISTS `utilRegisteredCharacter` ;

DROP TABLE IF EXISTS `utilRegisteredCorporation` ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
