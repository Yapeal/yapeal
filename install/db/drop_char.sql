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
-- Table `charAccountBalance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charAccountBalance`;

-- -----------------------------------------------------
-- Table `charAssetList`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charAssetList`;

-- -----------------------------------------------------
-- Table `charAttributes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charAttributes`;

-- -----------------------------------------------------
-- Table `charAttributeEnhancers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charAttributeEnhancers`;

-- -----------------------------------------------------
-- Table `charCertificates`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCertificates`;

-- -----------------------------------------------------
-- Table `charCharacterSheet`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCharacterSheet`;

-- -----------------------------------------------------
-- Table `charCorporationRoles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCorporationRoles`;

-- -----------------------------------------------------
-- Table `charCorporationRolesAtBase`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCorporationRolesAtBase`;

-- -----------------------------------------------------
-- Table `charCorporationRolesAtHQ`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCorporationRolesAtHQ`;

-- -----------------------------------------------------
-- Table `charCorporationRolesAtOther`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCorporationRolesAtOther`;

-- -----------------------------------------------------
-- Table `charCorporationTitles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charCorporationTitles`;

-- -----------------------------------------------------
-- Table `charIndustryJobs`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charIndustryJobs`;

-- -----------------------------------------------------
-- Table `charMarketOrders`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charMarketOrders`;

-- -----------------------------------------------------
-- Table `charskills`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charskills`;

-- -----------------------------------------------------
-- Table `charWalletJournal`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charWalletJournal`;

-- -----------------------------------------------------
-- Table `charWalletTransactions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `%prefix%charWalletTransactions`;
-- -----------------------------------------------------

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
