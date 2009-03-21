/**
 * Yapeal drop corp SQL.
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
-- Table `corpAccountBalance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpAccountBalance`;

-- -----------------------------------------------------
-- Table `corpAssetList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpAssetList`;

-- -----------------------------------------------------
-- Table `corpAttackers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpAttackers`;

-- -----------------------------------------------------
-- Table `corpCorporationSheet`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpCorporationSheet`;

-- -----------------------------------------------------
-- Table `corpContainerLog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpContainerLog`;

-- -----------------------------------------------------
-- Table `corpDivisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpDivisions`;

-- -----------------------------------------------------
-- Table `corpIndustryJobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpIndustryJobs`;

-- -----------------------------------------------------
-- Table `corpItems`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpItems`;

-- -----------------------------------------------------
-- Table `corpKillLog`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpKillLog`;

-- -----------------------------------------------------
-- Table `corpLogo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpLogo`;

-- -----------------------------------------------------
-- Table `corpMarketOrders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpMarketOrders`;

-- -----------------------------------------------------
-- Table `corpMemberTracking`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpMemberTracking`;

-- -----------------------------------------------------
-- Table `corpStandingsFromAgents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStandingsFromAgents`;

-- -----------------------------------------------------
-- Table `corpStandingsFromFactions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStandingsFromFactions`;

-- -----------------------------------------------------
-- Table `corpStandingsFromNPCCorporations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStandingsFromNPCCorporations`;

-- -----------------------------------------------------
-- Table `corpStandingsToAlliances`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStandingsToAlliances`;

-- -----------------------------------------------------
-- Table `corpStandingsToCharacters`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStandingsToCharacters`;

-- -----------------------------------------------------
-- Table `corpStandingsToCorporations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStandingsToCorporations`;

-- -----------------------------------------------------
-- Table `corpStarbaseList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpStarbaseList`;

-- -----------------------------------------------------
-- Table `corpVictim`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpVictim`;

-- -----------------------------------------------------
-- Table `corpWalletDivisions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpWalletDivisions`;

-- -----------------------------------------------------
-- Table `corpWalletJournal`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpWalletJournal`;

-- -----------------------------------------------------
-- Table `corpWalletTransactions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%corpWalletTransactions`;

-- -----------------------------------------------------

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
