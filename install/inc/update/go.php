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
if (conRev($ini_yapeal['version'])<=471) {
  $ini_yapeal['Database']['host'] = $config['DB_Host'];
  $ini_yapeal['Database']['database'] = $config['DB_Database'];
  $userpass = explode(":",$ini_yapeal['Database']['writer']);
  $ini_yapeal['Database']['username'] = $userpass[0];
  $ini_yapeal['Database']['password'] = $userpass[1];
  $ini_yapeal['Database']['table_prefix'] = $config['DB_Prefix'];
} // Check connection to DB
//Setup Database Connection on Main Database
$link = @mysqli_connect($ini_yapeal['Database']['host'],$ini_yapeal['Database']['username'],$ini_yapeal['Database']['password']);
if (DBHandler($ini_yapeal['Database']['host'], "CON")) {
  // Check DB Select
  if(DBHandler($ini_yapeal['Database']['database'], "DS", $ini_yapeal['Database']['database'])) {
    if (!isset($_POST['c_action'])) {
      if ($config['db_action'] == 2) {
        dropTables("util",true);
        dropTables("server",true);
        dropTables("account",true);
        dropTables("char",true);
        dropTables("corp",true);
        dropTables("eve",true);
        dropTables("map",true);
        createTables("util");
        createTables("server");
        if (isset($config['db_account']) && $config['db_account'] != 2) {
          // Create the account Databases
          createTables("account");
        };
        if (isset($config['db_char']) && $config['db_char'] != 2) {
          // Create the char Databases
          createTables("char");
        };
        if (isset($config['db_corp']) && $config['db_corp'] != 2) {
          // Create the corp Databases
          createTables("corp");
        };
        if (isset($config['db_eve']) && $config['db_eve'] != 2) {
          // Create the eve Databases
          createTables("eve");
        };
        if (isset($config['db_map']) && $config['db_map'] != 2) {
          // Create the map Databases
          createTables("map");
        };
      } else {
        /**
         * Edit accountData
         */
        editData('account');
        /**
         * Edit charData
         */
        editData('char');
        /**
         * Edit corpData
        */
        editData('corp');
        /**
         * Edit eveData
         */
        editData('eve');
        /**
         * Edit mapData
         */
        editData('map');
        /**
         * Update extra utilConfig DB
         */
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_user_id']."' WHERE `Name` = 'creatorAPIuserID'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorAPIuserID", "DUC", $query);
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_limit_key']."' WHERE `Name` = 'creatorAPIlimitedApiKey'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorAPIlimitedApiKey", "DUC", $query);
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_full_key']."' WHERE `Name` = 'creatorAPIfullApiKey'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorAPIfullApiKey", "DUC", $query);
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_char_id']."' WHERE `Name` = 'creatorCharacterID'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorCharacterID", "DUC", $query);
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_corp_id']."' WHERE `Name` = 'creatorCorporationID'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorCorporationID", "DUC", $query);
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_corp_name']."' WHERE `Name` = 'creatorCorporationName'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorCorporationName", "DUC", $query);
        $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".$config['api_char_name']."' WHERE `Name` = 'creatorName'";
        DBHandler($config['DB_Prefix']."utilconfig => creatorName", "DUC", $query);
        $query = "INSERT INTO `".$config['DB_Prefix']."utilregistereduser` (`userID`,`fullApiKey`,`limitedApiKey`) 
                  VALUES
                  ('".$config['api_user_id']."','".$config['api_full_key']."','".$config['api_limit_key']."') 
                  ON DUPLICATE KEY UPDATE `fullApiKey`=VALUES(`fullApiKey`),`limitedApiKey`=VALUES(`limitedApiKey`)";
        DBHandler($config['DB_Prefix']."utilregistereduser", "DII", $query);
        if ($config['db_char']==1) { $charisactive = '1'; } else { $charisactive = '0'; };
        $query = "INSERT INTO `".$config['DB_Prefix']."utilRegisteredCharacter` (`characterID`,`userID`,`name`,`corporationID`,`corporationName`,`isActive`) 
                  VALUES
                  ('".$config['api_char_id']."', '".$config['api_user_id']."', '".$config['api_char_name']."', '".$config['api_corp_id']."', '".$config['api_corp_name']."', '".$charisactive."')
                  ON DUPLICATE KEY UPDATE `userID`=VALUES(`userID`),`name`=VALUES(`name`),`corporationID`=VALUES(`corporationID`),`corporationName`=VALUES(`corporationName`),`isActive`=VALUES(`isActive`)";
        DBHandler($config['DB_Prefix']."utilregistereduser", "DII", $query);
        if ($config['db_corp']==1) { $coprisactive = '1'; } else { $coprisactive = '0'; };
        $query = "INSERT INTO `".$config['DB_Prefix']."utilregisteredcorporation` (`corporationID`,`characterID`,`isActive`) 
                  VALUES
                  ('".$config['api_corp_id']."', '".$config['api_char_id']."', '".$coprisactive."')
                  ON DUPLICATE KEY UPDATE `characterID`=VALUES(`characterID`),`isActive`=VALUES(`isActive`)";
        DBHandler($config['DB_Prefix']."utilregisteredcorporation", "DII", $query);
        if (!empty($config['config_pass']) && $config['config_pass']!="" && md5($config['config_pass'])!==$conf['password']) {
          $query = "UPDATE `".$config['DB_Prefix']."utilconfig` SET `Value` = '".md5($config['config_pass'])."' WHERE `Name` = 'password'";
          DBHandler($config['DB_Prefix']."utilconfig => password", "DUC", $query);
        }; // if !(empty($config['db_map']) && $config['db_map']=="" && md5($config['db_map'])===$conf['password'])
      }; // if $config['db_action'] == 2
    } else {
      include('db/update/updater.php');
    }; // if (isset($_POST['c_action']) && $_POST['c_action']==2)
  };
};
// Close the Database Connection
DBHandler($ini_yapeal['Database']['host'], "CLOSE");
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
      .ED_UPDATING_DONE . PHP_EOL;
} else {
  echo '<hr />' . PHP_EOL
      .ED_UPDATING_FAILED . PHP_EOL;
};
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?lang='.$_GET['lang'].'&amp;edit=setup" method="post">' . PHP_EOL
    .'<input type="submit" value="'.ED_GO_TO_CONFIG.'" />' . PHP_EOL
    .'</form>' . PHP_EOL;
CloseSite();
?>
