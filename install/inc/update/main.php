<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup - Config page.
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
// Get config info
$ini_yapeal = parse_ini_file('..'.$DS.'config'.$DS.'yapeal.ini', true);
if (conRev($ini_yapeal['version'])<$setupversion) {
  require('inc'.$DS.'update'.$DS.'update.php');
  exit;
};// if (conRev($ini_yapeal['version'])<=471)
$db = new mysqli($ini_yapeal['Database']['host'],$ini_yapeal['Database']['username'],$ini_yapeal['Database']['password']);
$query = "SELECT * FROM `".$ini_yapeal['Database']['database']."`.`".$ini_yapeal['Database']['table_prefix']."utilConfig`";
$result = $db->query($query);
/**
 * Check if the database setup is correct or if the table utilConfig is missing
 */
if (!$result) {
  OpenSite(ED_ERROR_NO_DB_SETUP);
  echo  '<h3>'.ED_ERROR_NO_DB_SETUP.'</h3><br />' . PHP_EOL
      .ED_ERROR_NO_DB_SETUP_DES.$db->error.ED_ERROR_NO_DB_SETUP_DES2 . PHP_EOL
      .ED_ERROR_NO_DB_SETUP_SOLUTION . PHP_EOL;
  CloseSite;
  exit;
};
while ($row = $result->fetch_assoc()) {
  $conf[$row['Name']] = $row['Value'];
}
$result->close();
$db->close();
/**
 * Set logging type
 */
$logtype = 'Config';
/**
 * Link handler
 */
// Get login info
require_once('inc'.$DS.'update'.$DS.'login.php');
if (isset($_GET['edit']) && $_GET['edit'] == "select") {
  // Main edit site
  require_once('inc'.$DS.'update'.$DS.'char_select.php');
} elseif (isset($_GET['edit']) && $_GET['edit'] == "go") {
  // Main edit site
  require_once('inc'.$DS.'update'.$DS.'go.php');
} elseif (isset($_GET['edit']) && $_GET['edit'] == "config") {
  // Main edit site
  require_once('inc'.$DS.'update'.$DS.'config.php');
} else {
  header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?edit=config');
};
?>
