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
/**
 * Log where in the setup progress we are
 */
$logtime = $_POST['logtime'];
$logtimenow = date('H:i:s',time());
$logfile = basename(__FILE__);
$log = <<<LOGTEXT
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
Time: [$logtimenow]
Page: {$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}
File: $logfile
--------------------------------------------------------------------------------
[$logtimenow] Check if post['c_action'] is  not = 0.
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Check for c_action
check_c_action();
/**
 * Run the script if check_c_action(); didn't exit the script
 */
$config = $_POST['config'];
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Get Character infoes
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Get Character infoes
$charinfo = explode("^-_-^",$config['api_char_info']);
$config['api_char_name'] = $charinfo[0];
$config['api_char_id'] = $charinfo[1];
$config['api_corp_name'] = $charinfo[2];
$config['api_corp_id'] = $charinfo[3];
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Build Selected Char APIs
LOGTEXT;
c_logging($log,$logtime,$logtype);
/*
 * Build Selected Char APIs
 */
$charSelAPIs = '';
$count = 0;
if (isset($config['charAPIs']) && !empty($config['charAPIs'])) {
  foreach ($config['charAPIs'] as $data) {
    if (!empty($data)) {
      if ($count==0) {
        $charSelAPIs .= $data;
        $count++;
      } else {
        $charSelAPIs .= ' '.$data;
      };
    };
  }
};
$config['charAPIs'] = $charSelAPIs;
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Build Selected Corp APIs
LOGTEXT;
c_logging($log,$logtime,$logtype);
/*
 * Build Selected Corp APIs
 */
$corpSelAPIs = '';
$count = 0;
if (isset($config['corpAPIs']) && !empty($config['corpAPIs'])) {
  foreach ($config['corpAPIs'] as $data) {
    if (!empty($data)) {
      if ($count==0) {
        $corpSelAPIs .= $data;
        $count++;
      } else {
        $corpSelAPIs .= ' '.$data;
      };
    };
  }
};
$config['corpAPIs'] = $corpSelAPIs;
// Stopper
$stop = 0;
$output = "";
$db_main_error = "";
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Setup Database Connection on Main Database
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Check connection to DB
//Setup Database Connection on Main Database
$link = @mysqli_connect($config['DB_Host'],$config['DB_Username'],$config['DB_Password']);
if (DBHandler($config['DB_Host'], "CON")) {
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] Check DB Select
LOGTEXT;
  c_logging($log,$logtime,$logtype);
  // Check DB Select
  if(DBHandler($config['DB_Database'], "DS", $config['DB_Database'])) {
    if ($_POST['c_action']==1) {
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] post['c_action'] is 1. = Clean Setup Selected
LOGTEXT;
      c_logging($log,$logtime,$logtype);
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
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] post['c_action'] is 2. = Update Selected
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      include('db/update/updater.php');
    };
  };
};
// Close the Database Connection
DBHandler($config['DB_Host'], "CLOSE");
//Creating yapeal.ini file
if($stop == 0) {
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] Creating yapeal.ini file
LOGTEXT;
  c_logging($log,$logtime,$logtype);
  // Include the file that create yapeal.ini
  require_once('inc'.$DS.'ini_creator.php');
} else {
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] There was errors in the DB queries. setup failed
LOGTEXT;
  c_logging($log,$logtime,$logtype);
};
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Generate Page
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Show the Progress report
OpenSite(INSTALLER_PROGRESS);
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
      .'<div id="Go_Back"></div>' . PHP_EOL
      .'<script type="text/javascript"> Go_Back(); </script>' . PHP_EOL;
};
CloseSite();
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Generate Page Done
LOGTEXT;
c_logging($log,$logtime,$logtype);
?>
