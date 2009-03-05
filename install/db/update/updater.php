<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup - Update/Convert Progress.
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
}
//if (isset($_GET['install'])) {
  if (getConfigRevision()===false || getConfigRevision()<$setupversion) {
    UpdateDB();
  } else {
    // Create the Required Databases
    createTables("util");
    createTables("server");
    if (isset($config['db_account']) && $config['db_account'] > 0) {
      // Create the account Databases
      createTables("account");
    };
    if (isset($config['db_char']) && $config['db_char'] > 0) {
      // Create the char Databases
      createTables("char");
    };
    if (isset($config['db_corp']) && $config['db_corp'] > 0) {
      // Create the corp Databases
      createTables("corp");
    };
    if (isset($config['db_eve']) && $config['db_eve'] > 0) {
      // Create the eve Databases
      createTables("eve");
    };
    if (isset($config['db_map']) && $config['db_map'] > 0) {
      // Create the map Databases
      createTables("map");
    };
  };
//} else {

//};
?>
