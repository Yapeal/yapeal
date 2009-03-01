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
// Check for c_action
check_c_action();
/**
 * Run the script if check_c_action(); didn't exit the script
 */
$config = $_POST['config'];
// Get Character infoes
$charinfo = explode("^-_-^",$config['api_char_info']);
$config['api_char_name'] = $charinfo[0];
$config['api_char_id'] = $charinfo[1];
$config['api_corp_name'] = $charinfo[2];
$config['api_corp_id'] = $charinfo[3];
// Stopper
$stop = 0;
$output = "";
$db_main_error = "";
// Check connection to DB
//Setup Database Connection on Main Database
$link = @mysqli_connect($config['DB_Host'],$config['DB_Username'],$config['DB_Password']);
if (DBHandler($config['DB_Host'], "CON")) {
  // Check DB Select
  if(DBHandler($config['DB_Database'], "DS", $config['DB_Database'])) {
    if ($_POST['c_action']==1) {
      // Create the Required Databases
      dropTables("util");
      createTables("util");
      dropTables("server");
      createTables("server");
      if (isset($config['db_account']) && $config['db_account'] > 0) {
        // Create the account Databases
        dropTables("account");
        createTables("account");
      };
      if (isset($config['db_char']) && $config['db_char'] > 0) {
        // Create the char Databases
        dropTables("char");
        createTables("char");
      };
      if (isset($config['db_corp']) && $config['db_corp'] > 0) {
        // Create the corp Databases
        dropTables("corp");
        createTables("corp");
      };
      if (isset($config['db_eve']) && $config['db_eve'] > 0) {
        // Create the eve Databases
        dropTables("eve");
        createTables("eve");
      };
      if (isset($config['db_map']) && $config['db_map'] > 0) {
        // Create the map Databases
        dropTables("map");
        createTables("map");
      };
    } elseif ($_POST['c_action']==2) {
      include('db/update/updater.php');
    };
  };
};
// Close the Database Connection
DBHandler($config['DB_Host'], "CLOSE");
//Creating yapeal.ini file
if($stop == 0) {
  // Create the map Databases
  require_once('inc'.$DS.'ini_creator.php');
};
// Show the Progress report
OpenSite(INSTALLER_PROGRESS,true,false);
echo '<table>'
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">'.INSTALLER_PROGRESS.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    . $output . PHP_EOL
    .'</table>' . PHP_EOL;
if ($stop == 0) {
  echo '<hr />' . PHP_EOL
      .INSTALLER_SETUP_DONE;
} else {
  echo '<hr />' . PHP_EOL
      .INSTALLER_SETUP_FAILED
      .'<div id="Go_Back></div>' . PHP_EOL
      .'<script> Go_Back(); </script>' . PHP_EOL;
};
CloseSite();
?>
