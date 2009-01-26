<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Setup Progress.
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
// Get Character infoes
$charinfo = explode("^-_-^",$_POST['api_char_info']);
// Stopper
$stop = 0;
$output = "";
$db_main_error = "";
// Check connection to DB
//Setup Database Connection on Main Database
$masterlink = @mysqli_connect($_POST['DB_Host_Main'],$_POST['DB_Username_Main'],$_POST['DB_Password_Main']);
$link = $masterlink;
if (DBHandler($_POST['DB_Host_Main'], "CON")) {
  // Check DB Select
  if(DBHandler($_POST['DB_Database_Main'], "DS", $_POST['DB_Database_Main'])) {
    // Create the Required Databases
    require('db/db_util.php');
    //require_once('db/db_server.php');
    if (isset($_POST['db_account']) && $_POST['db_account'] > 0) {
      // Create the account Databases
      require('db/db_account.php');
    };
    if (isset($_POST['db_char']) && $_POST['db_char'] > 0) {
      // Create the char Databases
      require('db/db_char.php');
    };
    if (isset($_POST['db_corp']) && $_POST['db_corp'] > 0) {
      // Create the corp Databases
      require('db/db_corp.php');
    };
    if (isset($_POST['db_eve']) && $_POST['db_eve'] > 0) {
      // Create the eve Databases
      require('db/db_eve.php');
    };
    if (isset($_POST['db_map']) && $_POST['db_map'] > 0) {
      // Create the map Databases
      require('db/db_map.php');
    };
  };
};
// Close the Database Connection
DBHandler($_POST['DB_Host_Main'], "CLOSE");
//Creating yapeal.ini file
if($stop == 0) {
  // Create the map Databases
  require_once('inc/ini_creator.php');
};
// Show the Progress report
OpenSite('Progress',true);
echo '<table>'
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">Progress</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    . $output . PHP_EOL
    .'</table>' . PHP_EOL;
if ($stop == 0) {
  echo '<hr />' . PHP_EOL
      .'<h2>The setup is done.</h2>' . PHP_EOL
      .'<br />' . PHP_EOL
      .'You can now setup a Cronjob on backend/eve-api-pull.php to cache all the data.<br>' . PHP_EOL;
      .'<h3>NOTIS: backend/eve-api-pull.php can\'t run in a webbrowser.</h3>' . PHP_EOL;
} else {
  echo '<hr />' . PHP_EOL
      .'<h2>The setup was not complete</h2>' . PHP_EOL
      .'<br />' . PHP_EOL
      .'You might have mistyped some info.<br />' . PHP_EOL
      .'<div id="Go_Back></div>' . PHP_EOL
      .'<script> Go_Back(); </script>' . PHP_EOL;
};
CloseSite();
?>
