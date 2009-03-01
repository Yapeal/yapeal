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
-- Data `eveAllianceList`
-- -----------------------------------------------------
-- There is no data to move.
-- This file is only so the update code don't break.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `eveMemberCorporations`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `eveConquerableStationList`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `eveErrorList`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Data `eveRefTypes`
-- -----------------------------------------------------
-- There is no data to move.
-- -----------------------------------------------------

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
