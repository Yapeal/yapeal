<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer php functions.
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

//////////////////////////////////
// Functions
//////////////////////////////////

// Get language
function GetLang($lang){
  if (file_exists("inc".DIRECTORY_SEPARATOR."language".DIRECTORY_SEPARATOR."lang_".$lang.".php")) {
    include("inc".DIRECTORY_SEPARATOR."language".DIRECTORY_SEPARATOR."lang_".$lang.".php");
  } else {
    include("inc".DIRECTORY_SEPARATOR."language".DIRECTORY_SEPARATOR."lang_en.php");
  }
}

// Select browser language
function GetBrowserLang() {
$langs = array();
  if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    // break up string into pieces (languages and q factors)
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
    if (count($lang_parse[1])) {
      // create a list like "en" => 0.8
      $langs = array_combine($lang_parse[1], $lang_parse[4]);
      // set default to 1 for any without q factor
      foreach ($langs as $lang => $val) {
        if ($val === '') $langs[$lang] = 1;
      }
      // sort list based on value	
      arsort($langs, SORT_NUMERIC);
    }; // if count $lang_parse[1]
  }; // if isset $_SERVER['HTTP_ACCEPT_LANGUAGE']
  foreach ($langs as $lang => $val) {
    if (strpos($lang, 'da') === 0) {
      // show Danish site
      return "da";
    } else if (strpos($lang, 'ru') === 0) {
      // show English site
      return "ru";
    } else if (strpos($lang, 'en') === 0) {
      // show English site
      return "en";
    }; 
  }
  return "en";
}

// Input standard Site header
function OpenSite($subtitle = "") {
  if ($subtitle != "") {
    $subtitle = ' - '.$subtitle;
  };
  /*
   * Language selector
   */
  $count = 0;
  /*
   * Make url query
   */
  foreach ($_GET as $getName=>$getValue) {
    if ($count==0) {
      $getParse = '?'.$getName.'='.$getValue;
      $count++;
    } else {
      $getParse .= '&amp;'.$getName.'='.$getValue;
    }; // if ($count==0)
  }
  /*
   * Setup the language selector bar
   */
  $languageselector = '<form action="'.$_SERVER['SCRIPT_NAME'].$getParse.'" method="post">' . PHP_EOL
                     .'<select name="lang" onchange="submit();">' . PHP_EOL
                     .'  <option value="en"'; if ($_POST['lang'] == 'en') { $languageselector .= ' selected="selected"'; } $languageselector .= '>English</option>' . PHP_EOL
                     .'  <option value="da"'; if ($_POST['lang'] == 'da') { $languageselector .= ' selected="selected"'; } $languageselector .= '>Danish</option>' . PHP_EOL
                     .'  <option value="ru"'; if ($_POST['lang'] == 'ru') { $languageselector .= ' selected="selected"'; } $languageselector .= '>Russian</option>' . PHP_EOL
                     .'</select>' . PHP_EOL;
  /*
   * Make post query
   */
  foreach ($_POST as $postName=>$postValue) {
    if ($postName=='lang') { continue; };
    /*
     * check if the value is a array
     */
    if (is_array($postValue)) {
      foreach ($postValue as $postName1=>$postValue1) {
        /*
         * check if the value is a array
         */
        if (is_array($postValue1)) {
          foreach ($postValue1 as $postName2=>$postValue2) {
            /*
             * check if the value is a array
             */
            if (is_array($postValue2)) {
              foreach ($postValue2 as $postName3=>$postValue3) {
                $languageselector .= '<input type="hidden" name="'.$postName.'['.$postName1.']['.$postName2.']['.$postName3.']" value="'.$postValue3.'" />' . PHP_EOL;
              }
            } else {
             $languageselector .= '<input type="hidden" name="'.$postName.'['.$postName1.']['.$postName2.']" value="'.$postValue2.'" />' . PHP_EOL;
            }; // if is_array($postValue2)
          }
        } else {
          $languageselector .= '<input type="hidden" name="'.$postName.'['.$postName1.']" value="'.$postValue1.'" />' . PHP_EOL;
        }; // if is_array($postValue1)
      }
    } else {
      $languageselector .= '<input type="hidden" name="'.$postName.'" value="'.$postValue.'" />' . PHP_EOL;
    }; // if is_array($postValue)
  }
  $languageselector .='<input type="submit" value="'.CHOSELANGUAGE.'" />' . PHP_EOL
                     .'</form>' . PHP_EOL;
  /*
   * Set page title
   */
  if (isset($_GET['install'])) {
    $pagetitle = '<h1>'.SETUP.'</h1>';
  } elseif (isset($_GET['edit'])) {
    $pagetitle = '<h1>'.CONFIG.'</h1>';
  } else {
    $pagetitle = '';
  }; // if / else
  echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' . PHP_EOL
      .'<html xmlns="http://www.w3.org/1999/xhtml">' . PHP_EOL
      .'<head>' . PHP_EOL
      .'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL
      .'<title>Yapeal Setup' . $subtitle . '</title>' . PHP_EOL
      .'<link href="inc/style.css" rel="stylesheet" type="text/css" />' . PHP_EOL
      .'</head>' . PHP_EOL
      .'<body>' . PHP_EOL
      .$languageselector
      .'<div id="wrapper">' . PHP_EOL
      .'<img src="../pics/yapealblue.png" width="150" height="50" alt="Yapeal logo" /><br />' . PHP_EOL
      .$pagetitle . PHP_EOL;
}

// Input standard Site footer
function CloseSite() {
  echo '</div>' . PHP_EOL
      .'</body>' . PHP_EOL
      .'</html>' . PHP_EOL;
}

// DB Handler
function DBHandler($info, $dbtype = "CON", $checker = "") {
  global $link, $output, $stop, $config, $logtime, $logtype;
  $select = false;
  if ($dbtype==="CON") {
    // Check Connection
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_CONNECT_TO;
    $request_type_log = 'Database: Connect to';
    $okay = CONNECTED;
    $okay_log = 'Connected';
    $select = $link;
    if ($link) { @mysqli_query($link,"SET NAMES utf8"); };
    $errortext = @mysqli_connect_error();
  } else if ($dbtype==="DS") {
    // Check Selected DB
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_SELECT_DB;
    $request_type_log = 'Database: Select Database';
    $okay = SELECTED;
    $okay_log = 'Selected';
    $select = @mysqli_select_db($link,$checker);
    $errortext = DATABASE.' '.$info.' '.INSTALLER_WAS_NOT_FOUND;
  } else if ($dbtype==="DCT") {
    // Check Table Create
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_CREATE_TABLE;
    $request_type_log = 'Database: Create Table';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="DII") {
    // Check Insert Into
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_INSERT_INTO;
    $request_type_log = 'Database: Insert Into';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="DMOD") {
    // Move old data
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_MOVE_OLD_DATA;
    $request_type_log = 'Database: Move Old Data To';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="DDOT") {
    // Remove old data
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_REMOVE_OLD_TABLES;
    $request_type_log = 'Database: Remove Old Tables';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="DAT") {
    // Remove old data
    $errorval = 1;
    $request_type = DATABASE.": ".UPD_ALTER_TABLE;
    $request_type_log = 'Database: Alter Table';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="SUR") {
    // Remove old data
    $errorval = 0;
    $request_type = UPD_START_UPDATE;
    $request_type_log = 'Database: Start Update';
    $okay = $checker;
    $okay_log = $checker;
    $select = true;
  } else if ($dbtype==="EUR") {
    // Add revision start
    $errorval = 0;
    $request_type = UPD_END_UPDATE;
    $request_type_log = 'Database: End Update';
    $okay = $checker;
    $okay_log = $checker;
    $select = true;
  } else if ($dbtype==="DU") {
    // Update data
    $errorval = 1;
    $request_type = DATABASE.": ".UPDATE;
    $request_type_log = 'Database: Update';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="DDT") {
    // Check Drop Table
    $errorval = 1;
    $request_type = DATABASE.": ".INSTALLER_DROP_TABLE;
    $request_type_log = 'Database: Drop Table';
    $okay = DONE;
    $okay_log = 'Done';
    $select = @mysqli_query($link,$checker);
    $errortext = @mysqli_error($link);
  } else if ($dbtype==="CHKTABLE") {
    // Check If Table Exists
    $query = "SELECT count(*) FROM information_schema.tables
              WHERE table_schema = '".$config['DB_Database']."'
              AND table_name = '".$info."'";
    $resid = mysqli_query($link,$query);
    $result = mysqli_fetch_array($resid);
    if ($result[0]>0) {
      return true;
    } else {
      return false;
    }; // if ($result[0]>0)
  } else if ($dbtype==="CLOSE") {
    // Check Close DB
    if (!$link) { return false; };
    $errorval = 0;
    $request_type = DATABASE.": ".INSTALLER_CLOSE_CONNECTION;
    $request_type_log = 'Database: Close Connection';
    $okay = CLOSED;
    $okay_log = 'Closed';
    $select = @mysqli_close($link);
  } else {
    // Return false.
    return false;
  };
  if (!$select) {
    /**
     * Log where in the setup progress we are
     */
    $logtimenow = date('H:i:s',time());
    $log = <<<LOGTEXT
[$logtimenow] $request_type_log => $info => $errortext
LOGTEXT;
    c_logging($log,$logtime,$logtype);
    /**
     * Output error
     */
    $stop += $errorval;
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.$request_type.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$info.'</td>' . PHP_EOL;
    $output .= '    <td class="warning">'.$errortext.'</td>' . PHP_EOL;
    $output .= '  </tr>' . PHP_EOL;
    return false;
  } else {
    /**
     * Log where in the setup progress we are
     */
    $logtimenow = date('H:i:s',time());
    $log = <<<LOGTEXT
[$logtimenow] $request_type_log => $info => $okay_log
LOGTEXT;
    c_logging($log,$logtime,$logtype);
    /**
     * Output success
     */
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.$request_type.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$info.'</td>' . PHP_EOL;
    $output .= '    <td class="good">'.$okay.'</td>' . PHP_EOL;
    $output .= '  </tr>' . PHP_EOL;
    return true;
  };
}

// Dir and File writ anabled checker
function WritChecker($path) {
  global $content2, $chmodcheck;
  if (is_file('..'.DIRECTORY_SEPARATOR.$path)) {
    $type = '('.INSTALLER_FILE.')';
    $cmod = INSTALLER_FILE_TO.' 666';
  } else {
    $type = '('.INSTALLER_DIR.')';
    $cmod = INSTALLER_DIR_TO.' 777';
  }
  $content2 .= '  <tr>' . PHP_EOL;
  $content2 .= '    <td width="220">'.$type.' '.$path.'</td>' . PHP_EOL;
  if (is_writable('..'.DIRECTORY_SEPARATOR.$path)) {
    $content2 .= '    <td class="good">'.YES.'</td>' . PHP_EOL;
  } else {
    $content2 .= '    <td class="warning">'.NO.' - Chmod '.$cmod.'</td>' . PHP_EOL;
    $chmodcheck++;
  };
  $content2 .= '  </tr>' . PHP_EOL;
}

// Check disabled form values
function DisableChecker($value) {
  if ($value == "") {
    return '0';
  } else {
    return $value;
  };
}

// Table Creator
function createTables($type) {
  // Load sql file
  global $link, $config, $conf;
  if (isset($_GET['install'])) {
    if ($config['db_account']==0) { $accountData = 2; } else { $accountData = $config['db_account']; };
    if ($config['db_char']==0) { $charData = 2; } else { $charData = $config['db_char']; };
    if ($config['db_corp']==0) { $corpData = 2; } else { $corpData = $config['db_corp']; };
    if ($config['db_eve']==0) { $eveData = 2; } else { $eveData = $config['db_eve']; };
    if ($config['db_map']==0) { $mapData = 2; } else { $mapData = $config['db_map']; };
  } else {
    $accountData = $config['db_account'];
    $charData = $config['db_char'];
    $corpData = $config['db_corp'];
    $eveData = $config['db_eve'];
    $mapData = $config['db_map'];
  }
  if (isset($config['db_action']) && $config['db_action']==2) { $replacepass = $conf['password']; } else { $replacepass = md5($config['config_pass']); }
  $replace = array('prefix'           => $config['DB_Prefix'], 
                   'accountData'      => $accountData, 
                   'charData'         => $charData, 
                   'corpData'         => $corpData, 
                   'eveData'          => $eveData, 
                   'mapData'          => $mapData, 
                   'fullApiKey'       => $config['api_full_key'], 
                   'limitedApiKey'    => '',
                   'userID'           => $config['api_user_id'], 
                   'characterID'      => $config['api_char_id'], 
                   'corporationID'    => $config['api_corp_id'],
                   'corporationName'  => $config['api_corp_name'], 
                   'name'             => $config['api_char_name'], 
                   'charisactive'     => $config['db_char'],
                   'corpisactive'     => $config['db_corp'], 
                   'activeCharAPI'    => $config['charAPIs'], 
                   'activeCorpAPI'    => $config['corpAPIs'], 
                   'password'         => $replacepass
                   );
  $sql = subsFile($replace,'install_'.$type.'.sql');
  if ($sql===false) { return false; };
  // Remove comments in sql file
  $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
  $sql = preg_replace('/-- .*/', '', $sql);
  // Spliting up the sql file into query's
  $sql = explode(';', $sql);
  // Execute query's
  $insint = 0;
  foreach ($sql as $query) {
    $query = trim($query);
    if (empty($query)) { continue; };
    // Create table or Inset table
    if (eregi('CREATE TABLE',$query)) {
      $start = strpos($query,'`',(strpos($query,'CREATE TABLE') + 12)) + 1;
      $end = strpos($query,'`',($start)) - $start;
      $tablename = substr($query, $start, $end);
      DBHandler($tablename, $dbtype = "DCT", $query);
      $insint = 0;
    } else if (eregi('INSERT INTO',$query)) {
      if ($insint==0) {
        $start = strpos($query,'`',(strpos($query,'INSERT INTO') + 11)) + 1;
        $end = strpos($query,'`',($start)) - $start;
        $tablename = substr($query, $start, $end);
        DBHandler($tablename, $dbtype = "DII", $query);
        $insint = 1;
      } else {
        @mysqli_query($link,$query);
      };
    } else {
      @mysqli_query($link,$query);
      $insint = 0;
    };
  }
  return true;
}
// Table Drop
function dropTables($type, $dropold=false) {
  global $link, $config, $conf;
  // Load sql file
  if ($dropold) { $dropprefix = $conf['dbPrefix']; } else { $dropprefix = $config['DB_Prefix']; };
  $replace = array('prefix' => $dropprefix);
  $sql = subsFile($replace,'drop_'.$type.'.sql');
  if ($sql===false) { return false; };
  // Remove comments in sql file
  $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
  $sql = preg_replace('/-- .*/', '', $sql);
  // Spliting up the sql file into query's
  $sql = explode(';', $sql);
  // Execute query's
  foreach ($sql as $query) {
    $query = trim($query);
    if (empty($query)) { continue; };
    //
    if (eregi('DROP TABLE',$query)) {
      $start = strpos($query,'`',(strpos($query,'DROP TABLE') + 10)) + 1;
      $end = strpos($query,'`',($start)) - $start;
      $tablename = substr($query, $start, $end);
      DBHandler($tablename, $dbtype = "DDT", $query);
    };
  }
  return true;
}
/**
 * Function to do string substitution in file.
 *
 * @param array $subs Keys are the string to be replaced and value the new values.
 * @param string $fileName Name of file to do substitutions in.
 *
 * @return string Returns the file as string with all substitions done.
 */
function subsFile($subs,$fileName) {
  $file = 'db' . DIRECTORY_SEPARATOR . $fileName;
  if (file_exists($file) && is_readable($file)) {
    $template=file_get_contents($file);
    if ($template===false) {
      return false;
    };// if $template===false
    foreach ($subs as $k=>$v) {
      $template=str_replace('%' . $k . '%', $v, $template);
    };
  } else {
    return false;
  };
  return $template;
}
// Edit data in DB
function editData($type) {
  global $link,$config,$conf;
  if ($conf[$type.'Data'] == 0) {
    if ($config['db_'.$type] == 1) {
      $query = "UPDATE `".$config['DB_Prefix']."utilConfig` SET `Value` = 1 WHERE `Name` = '".$type."Data'";
      DBHandler($config['DB_Prefix']."utilConfig => ".$type."Data", "DU", $query);
    } elseif ($config['db_'.$type] == 2) {
      $query = "UPDATE `".$config['DB_Prefix']."utilConfig` SET `Value` = 2 WHERE `Name` = '".$type."Data'";
      DBHandler($config['DB_Prefix']."utilConfig => ".$type."Data", "DU", $query);
      dropTables($type);
    };
  } else if ($conf[$type.'Data'] == 1) {
    if ($config['db_'.$type] == 0) {
      $query = "UPDATE `".$config['DB_Prefix']."utilConfig` SET `Value` = 0 WHERE `Name` = '".$type."Data'";
      DBHandler($config['DB_Prefix']."utilConfig => ".$type."Data", "DU", $query);
    } elseif ($config['db_'.$type] == 2) {
      $query = "UPDATE `".$config['DB_Prefix']."utilConfig` SET `Value` = 2 WHERE `Name` = '".$type."Data'";
      DBHandler($config['DB_Prefix']."utilConfig => ".$type."Data", "DU", $query);
      dropTables($type);
    };
  } else if ($conf[$type.'Data'] == 2) {
    if ($config['db_'.$type] == 0) {
      $query = "UPDATE `".$config['DB_Prefix']."utilConfig` SET `Value` = 0 WHERE `Name` = '".$type."Data'";
      DBHandler($config['DB_Prefix']."utilConfig => ".$type."Data", "DU", $query);
      createTables($type);
    } elseif ($config['db_'.$type] == 1) {
      $query = "UPDATE `".$config['DB_Prefix']."utilConfig` SET `Value` = 1 WHERE `Name` = '".$type."Data'";
      DBHandler($config['DB_Prefix']."utilConfig => ".$type."Data", "DU", $query);
      createTables($type);
    };
  };
}

// Update Checker
function check_c_action() {
  global $logtime;
	if (isset($_POST['c_action']) && $_POST['c_action']==0) {
    /**
     * Log where in the setup progress we are
     */
    $logtimenow = date('H:i:s',time());
    $log = <<<LOGTEXT
[$logtimenow] post['c_action'] was set to 0.
--------------------------------------------------------------------------------
Log Setup_$logtime.log End
LOGTEXT;
    c_logging($log,$logtime,'Setup');
    OpenSite(INSTALLER_NO_C_ACTION);
    echo  INSTALLER_NO_C_ACTION_DES . PHP_EOL
         .'<form action="' . $_SERVER['SCRIPT_NAME'] . '?lang=' . $_GET['lang'] . '&amp;install=welcome" method="post">' . PHP_EOL
         .'  <input type="submit" value="'.BACK.'" />' . PHP_EOL
         .'</form>' . PHP_EOL;
    CloseSite();
    exit();
  }; // if isset $_POST['c_action'] & $_POST['c_action']==0
}
// Update database to the newest revision
function UpdateDB() {
  global $link, $config, $conf;
  $types = array('util','server','account','char','corp','eve','map');
  $updateversions = array('471','616','643');
	/*
   * Set some replacement values that is needed for the sql files
   */
  if (isset($_GET['install'])) {
    if ($config['db_account']==0) { $accountData = 2; } else { $accountData = $config['db_account']; };
    if ($config['db_char']==0) { $charData = 2; } else { $charData = $config['db_char']; };
    if ($config['db_corp']==0) { $corpData = 2; } else { $corpData = $config['db_corp']; };
    if ($config['db_eve']==0) { $eveData = 2; } else { $eveData = $config['db_eve']; };
    if ($config['db_map']==0) { $mapData = 2; } else { $mapData = $config['db_map']; };
  } else {
    $accountData = $config['db_account'];
    $charData = $config['db_char'];
    $corpData = $config['db_corp'];
    $eveData = $config['db_eve'];
    $mapData = $config['db_map'];
  }
  if (isset($config['db_action']) && $config['db_action']==2) { $replacepass = $conf['password']; } else { $replacepass = md5($config['config_pass']); }
  $replace = array('prefix'           => $config['DB_Prefix'], 
                   'accountData'      => $accountData, 
                   'charData'         => $charData, 
                   'corpData'         => $corpData, 
                   'eveData'          => $eveData, 
                   'mapData'          => $mapData, 
                   'fullApiKey'       => $config['api_full_key'], 
                   'limitedApiKey'    => '',
                   'userID'           => $config['api_user_id'], 
                   'characterID'      => $config['api_char_id'], 
                   'corporationID'    => $config['api_corp_id'],
                   'corporationName'  => $config['api_corp_name'], 
                   'name'             => $config['api_char_name'], 
                   'charisactive'     => $config['db_char'],
                   'corpisactive'     => $config['db_corp'], 
                   'activeCharAPI'    => $config['charAPIs'], 
                   'activeCorpAPI'    => $config['corpAPIs'], 
                   'password'         => $replacepass,
                   );
  /*
   * Loop throu the versions and run them if curent version number is out of date
   */
  foreach ($updateversions as $version) {
    if (getConfigRevision()==false) {
      $runversion = true;
    } elseif (getConfigRevision()<$version) {
      $runversion = true;
    } else {
      $runversion = false;
    }; // if getConfigRevision()
    if ($runversion) {
      /*
       * Show Revision Update Start
       */
      DBHandler(UPD_REVISION, "SUR", $version);
      /*
       * Loop throu the types
       */
      foreach ($types as $type) {
        /*
         * Check if the type should be runned
         */
        if ($type=='util') {
          $run = true;
        } elseif ($type=='server') {
          $run = true;
        } elseif ($type=='account' && $config['db_account']==1) {
          $run = true;
        } elseif ($type=='char' && $config['db_char']==1) {
          $run = true;
        } elseif ($type=='corp' && $config['db_corp']==1) {
          $run = true;
        } elseif ($type=='eve' && $config['db_eve']==1) {
          $run = true;
        } elseif ($type=='map' && $config['db_map']==1) {
          $run = true;
        } else {
          $run = false;
        }; // if ($type=='xxx')
				/*
         * Run the update if $run is set to true
         */
        if ($run) {
          /*
           * Load xxx_new_tables.sql file with replaced values
           */
          $sql = subsFile($replace,'update'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$type.'_new_tables.sql');
          if ($sql) {
            /*
             * Remove comments in sql file
             */
            $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
            $sql = preg_replace('/-- .*/', '', $sql);
            /*
             * Spliting up the sql file into query's
             */
            $sql = explode(';', $sql);
            /*
             * Execute query's
             */
            $insint = 0;
            foreach ($sql as $query) {
              /*
              * Trim the query for not needed spaces
              */
              $query = trim($query);
              /*
              * If query is empty continue to the next query
              */
              if (empty($query)) { continue; };
              /*
               * Create table or Inset Into table
               */
              if (eregi('CREATE TABLE',$query)) {
                $start = strpos($query,'`',(strpos($query,'CREATE TABLE') + 12)) + 1;
                $end = strpos($query,'`',($start)) - $start;
                $tablename = substr($query, $start, $end);
                DBHandler($tablename, "DCT", $query);
                $insint = 0;
              /*
               * Inset Into table
               */
              } elseif (eregi('INSERT INTO',$query)) {
                if ($insint==0) {
                  $start = strpos($query,'`',(strpos($query,'INSERT INTO') + 11)) + 1;
                  $end = strpos($query,'`',($start)) - $start;
                  $tablename = substr($query, $start, $end);
                  DBHandler($tablename, "DII", $query);
                  $insint = 1;
                } else {
                  @mysqli_query($link,$query);
                };
              /*
               * Execute query with no message
               */
              } else {
                @mysqli_query($link,$query);
                $insint = 0;
              };
            }
          }; // if ($sql)
          /*
           * Load xxx_alter_tables.sql file with replaced values
           */
          $sql = subsFile($replace,'update'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$type.'_alter_tables.sql');
          if ($sql) {
            /*
             * Remove comments in sql file
             */
            $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
            $sql = preg_replace('/-- .*/', '', $sql);
            /*
             * Spliting up the sql file into query's
             */
            $sql = explode(';', $sql);
            /*
             * Execute query's
             */
            $insint = 0;
            foreach ($sql as $query) {
              /*
              * Trim the query for not needed spaces
              */
              $query = trim($query);
              /*
              * If query is empty continue to the next query
              */
              if (empty($query)) { continue; };
              /*
               * Alter Table
               */
              if (eregi('ALTER TABLE',$query)) {
                $start = strpos($query,'`',(strpos($query,'ALTER TABLE') + 11)) + 1;
                $end = strpos($query,'`',($start)) - $start;
                $tablename = substr($query, $start, $end);
                DBHandler($tablename, "DAT", $query);
                $insint = 0;
              /*
               * Update
               */
              } elseif (eregi('UPDATE',$query)) {
                $start = strpos($query,'`',(strpos($query,'UPDATE') + 6)) + 1;
                $end = strpos($query,'`',($start)) - $start;
                $tablename = substr($query, $start, $end);
                DBHandler($tablename, "DU", $query);
              /*
               * Inset Into table
               */
              } elseif (eregi('INSERT INTO',$query)) {
                if ($insint==0) {
                  $start = strpos($query,'`',(strpos($query,'INSERT INTO') + 11)) + 1;
                  $end = strpos($query,'`',($start)) - $start;
                  $tablename = substr($query, $start, $end);
                  DBHandler($tablename, "DII", $query);
                  $insint = 1;
                } else {
                  @mysqli_query($link,$query);
                };
              /*
               * Execute query with no message
               */
              } else {
                @mysqli_query($link,$query);
                $insint = 0;
              };
            }
          }; // if ($sql)
          /*
           * Load xxx_datamove.sql file with replaced values
           */
          $sql = subsFile($replace,'update'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$type.'_datamove.sql');
          if ($sql) {
            /*
             * Remove comments in sql file
             */
            $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
            $sql = preg_replace('/-- .*/', '', $sql);
            /*
             * Spliting up the sql file into query's
             */
            $sql = explode(';', $sql);
            /*
             * Execute query's
             */
            foreach ($sql as $query) {
              /*
              * Trim the query for not needed spaces
              */
              $query = trim($query);
              /*
              * If query is empty continue to the next query
              */
              if (empty($query)) { continue; };
              /*
               * Move old data to new table
               */
              if (eregi('INSERT INTO',$query)) {
                $start = strpos($query,'`',(strpos($query,'FROM') + 4)) + 1;
                $end = strpos($query,'`',($start)) - $start;
                $tablename = substr($query, $start, $end);
                if (DBHandler($tablename, "CHKTABLE")) {
                  $start = strpos($query,'`',(strpos($query,'INSERT INTO') + 11)) + 1;
                  $end = strpos($query,'`',($start)) - $start;
                  $tablename = substr($query, $start, $end);
                  DBHandler($tablename, "DMOD", $query);
                } else { continue; };
              /*
               * Execute query with no message
               */
              } else {
                @mysqli_query($link,$query);
              };
            }
          }; // if ($sql)
          /*
           * Load xxx_setversion.sql file with replaced values
           */
          $sql = subsFile($replace,'update'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.$type.'_setversion.sql');
          if ($sql) {
            /*
             * Remove comments in sql file
             */
            $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
            $sql = preg_replace('/-- .*/', '', $sql);
            /*
             * Spliting up the sql file into query's
             */
            $sql = explode(';', $sql);
            /*
             * Execute query's
             */
            foreach ($sql as $query) {
              /*
              * Trim the query for not needed spaces
              */
              $query = trim($query);
              /*
              * If query is empty continue to the next query
              */
              if (empty($query)) { continue; };
              /*
               * Update to new revision number
               */
              if (eregi('UPDATE',$query)) {
                $start = strpos($query,'`',(strpos($query,'UPDATE') + 6)) + 1;
                $end = strpos($query,'`',($start)) - $start;
                $tablename = substr($query, $start, $end);
                DBHandler($tablename." => version", "DU", $query);
							/*
               * Execute query with no message
               */
              } else {
                @mysqli_query($link,$query);
              };
            }
          }; // if ($sql)
        }; // if ($run)
      }; // if (getConfigRevision()===false || getConfigRevision()<=$version)
      unset($type);
      /*
       * Load drop_old_tables.sql file with replaced values
       */
      $sql = subsFile($replace,'update'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.'drop_old_tables.sql');
      /*
       * Run the sql file if there is one
       */
      if ($sql) {
        /*
         * Remove comments in sql file
         */
        $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
        $sql = preg_replace('/-- .*/', '', $sql);
        /*
         * Spliting up the sql file into query's
         */
        $sql = explode(';', $sql);
        /*
         * Execute query's
         */
        foreach ($sql as $query) {
          /*
           * Trim the query for not needed spaces
           */
          $query = trim($query);
          /*
           * If query is empty continue to the next query
           */
          if (empty($query)) { continue; };
          /*
           * Drop Tables
           */
          if (eregi('DROP TABLE',$query)) {
            $start = strpos($query,'`',(strpos($query,'DROP TABLE') + 10)) + 1;
            $end = strpos($query,'`',($start)) - $start;
            $tablename = substr($query, $start, $end);
            DBHandler(INSTALLER_OLD_TABLE.$tablename, "DDOT", $query);
          /*
           * Execute query with no message
           */
          } else {
            @mysqli_query($link,$query);
          }; // if (eregi('DROP TABLE',$query)
        }
      }; // if ($sql)
      /*
       * Show Revision Update Start
       */
      DBHandler(UPD_REVISION, "EUR", $version);
    }
  }
  unset($version);
  return true;
}
/*
 * Get revision to a integer value
 */
function conRev($rev) {
  $rev = preg_replace('/\$Revision: /', '', $rev);
  $rev = preg_replace('/ \$/', '', $rev);
  return (int)$rev;
}
/*
 * Get revision from utilConfig
 */
function getConfigRevision() {
  global $link,$config;
  // Check If Table Exists
  $query = "SELECT count(*) FROM information_schema.tables
            WHERE table_schema = '".$config['DB_Database']."'
            AND table_name = '".$config['DB_Prefix']."utilConfig'";
  $resid = mysqli_query($link,$query);
  $result = mysqli_fetch_array($resid);
  if ($result[0]>0) {
    $query = "SELECT `Value` FROM `".$config['DB_Prefix']."utilConfig`
            WHERE `Name` = 'version'";
    $resid = mysqli_query($link,$query);
    $result = mysqli_fetch_array($resid);
    return conRev($result[0]);
  } else {
    return false;
  }; // if ($result[0]>0)
}
// Drop Old Tables
function dropOldTables($version) {
  global $link, $config, $conf;
  // Load sql file
  if ($dropold) { $dropprefix = $conf['dbPrefix']; } else { $dropprefix = $config['DB_Prefix']; };
  $replace = array('prefix' => $dropprefix);
  $sql = subsFile($replace,'update'.DIRECTORY_SEPARATOR.$version.DIRECTORY_SEPARATOR.'drop_old_tables.sql');
  if ($sql===false) { return false; };
  // Remove comments in sql file
  $sql = preg_replace('/\/\*(.|[\r\n])*?\*\//', '', $sql);
  $sql = preg_replace('/-- .*/', '', $sql);
  // Spliting up the sql file into query's
  $sql = explode(';', $sql);
  // Execute query's
  foreach ($sql as $query) {
    $query = trim($query);
    if (empty($query)) { continue; };
    //
    if (eregi('DROP TABLE',$query)) {
      $tablename = INSTALLER_FROM_REVISION.$version;
      DBHandler($tablename, $dbtype = "DDOT", $query);
    };
  }
  return true;
}
/*
 * Get revision from utilConfig
 */
function c_logging($text,$timestamp,$type) {
  if (is_writable('..'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'log')) {
    $fp = fopen('..'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR.$type.'_'.$timestamp.'.log', 'a');
    fwrite($fp, $text.PHP_EOL);
    fclose($fp);
  }
}
?>
