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
-- Data `utilconfig`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `utilCachedUntil`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `utilRegisteredCharacter`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilRegisteredCharacter` (`characterID`,`userID`,`name`,
  `corporationID`,`corporationName`,`isActive`,`graphic`,`graphicType`)
SELECT R.`characterID`,R.`userID`,
  R.`name`,R.`corporationID`,
  R.`corporationName`,R.`isActive`,
  R.`graphic`,R.`graphicType`
FROM `RegisteredCharacter` R
ON DUPLICATE KEY UPDATE `userID`=VALUES(`userID`),`name`=VALUES(`name`),
  `corporationID`=VALUES(`corporationID`),`corporationName`=VALUES(`corporationName`),
  `isActive`=VALUES(`isActive`),`graphic`=VALUES(`graphic`),
  `graphicType`=VALUES(`graphicType`);

-- -----------------------------------------------------
-- Data `utilRegisteredCorporation`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilRegisteredCorporation` (`characterID`,`corporationID`,`graphic`,
  `graphicType`,`isActive`)
SELECT R.`characterID`,
  R.`corporationID`,R.`graphic`,
  R.`graphicType`,R.`isActive`
FROM `RegisteredCorporation` R
ON DUPLICATE KEY UPDATE `characterID`=VALUES(`characterID`),`graphic`=VALUES(`graphic`),
  `graphicType`=VALUES(`graphicType`),`isActive`=VALUES(`isActive`);

-- -----------------------------------------------------
-- Data `utilRegisteredUser`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilRegisteredUser` (`fullApiKey`,`limitedApiKey`,`userID`)
SELECT R.`fullApiKey`,
  R.`limitedApiKey`,
  R.`userID`
FROM `RegisteredUser` R
ON DUPLICATE KEY UPDATE `fullApiKey`=VALUES(`fullApiKey`),`limitedApiKey`=VALUES(`limitedApiKey`);

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
