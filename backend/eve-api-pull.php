#!/usr/bin/php
<?php
/**
 * Used to get information from Eve-online API and store in database.
 *
 * This script expects to be ran from a command line or from a crontab job that
 * can optionally pass a config file name with -c option.
 *
 * LICENSE: This file is part of Yapeal.
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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
// Track version of script.
define('YAPEAL_VERSION', str_replace(
  array('$', '#') , '', '$Revision$ $Date:: 2008-10-24 00:48:59 #$'));
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// If being run from command-line look for options there if function available.
if (php_sapi_name() == 'cli' && function_exists('getopt')) {
  $options = getopt('hVc:');
  foreach($options as $opt => $value) {
    switch ($opt) {
      case 'c':
      case '--config':
        if ($file = realpath($value) && is_file($file) && is_readable($file)) {
          $yapealIniFile = $file;
          break;
        } else {
          $mess = $opt[1] . ' does not exist or is not readable' . PHP_EOL;
          fwrite(STDERR, $mess);
        }; // else realpath $opt[1]&& ...

      case 'h':
      case '--help':
        usage();
        exit;
      case 'V':
      case '--version':
        $mess = $argv[0] . ' ' . YAPEAL_VERSION . PHP_EOL;
        $mess.= "Copyright (C) 2008, Michael Cummings" . PHP_EOL;
        $mess.= "This program comes with ABSOLUTELY NO WARRANTY." . PHP_EOL;
        $mess.= 'Licensed under the GNU LPGL 3.0 License.' . PHP_EOL;
        $mess.= 'See COPYING and COPYING-LESSER for more details.' . PHP_EOL;
        fwrite(STDERR, $mess);
        exit;
    };// switch $opt
  };// foreach $options...
};// if php_sapi_name() == 'cli' && ...
/* **************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* **************************************************************************/
/* This would need to be changed if this file isn't in another path at same
* level as 'inc' directory where common_emt.inc is.
*/
// Move up and over to 'inc' directory to read common_backend.inc
$path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
$path.= '..' . DIRECTORY_SEPARATOR . 'inc';
$path.= DIRECTORY_SEPARATOR . 'common_backend.inc';
require_once realpath($path);
/* **************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* **************************************************************************/
require_once YAPEAL_INC . 'elog.inc';
require_once YAPEAL_CLASS . 'Logging_Exception_Observer.class.php';
require_once YAPEAL_CLASS . 'Printing_Exception_Observer.class.php';
require_once YAPEAL_INC . 'common_db.inc';
//require_once YAPEAL_INC.'eap_functions.inc';
function usage() {
  $progname = basename($GLOBALS['argv'][0]);
  $scriptversion = YAPEAL_VERSION;
  $use = <<<USAGE_MESSAGE
Usage: $progname [-h | -V | -c config.ini]
Options:
  -c config.ini, --config=config.ini   Read configation from 'config.ini'.
  -h, --help                           Show this help.
  -V, --version                        Show $progname version.

Version $scriptversion
USAGE_MESSAGE;
  fwrite(STDERR, $use . PHP_EOL);
};
$cachetypes = array('tableName' => 'C', 'ownerID' => 'I', 'cachedUntil' => 'T');
try {
  $api = 'eve-api-pull';
  $con = connect(DSN_UTIL_WRITER);
  // Mutex to keep from having more than one pull going at once most the time.
  // Turned logging off here since this runs every minute.
  if (dontWait($api, 0, FALSE)) {
    // Give ourself up to 5 minutes to finish.
    define('YAPEAL_START_TIME',gmdate('Y-m-d H:i:s', strtotime('5 minutes')));
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => YAPEAL_START_TIME);
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  } else {
    // Someone else has set timer need to wait it out.
    exit;
  }; // else dontwait $api ...
  /* ************************************************************************
  * Generate character list
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_CHAR_ACTIVE) {
    $api = 'RegisteredCharacter';
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CHAR) == YAPEAL_TRACE_CHAR) {
      $mess = 'CHAR: Connect before section in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    $con = connect(DSN_CHAR_WRITER);
    /* Generate a list of character(s) we need to do updates for */
    $sql = 'select u.userID "userid",u.fullApiKey "apikey",u.limitedApiKey "lapikey",';
    $sql.= 'chr.characterID "charid"';
    $sql.= ' from ';
    $sql.= DB_UTIL . '.RegisteredCharacter as chr,';
    $sql.= DB_UTIL . '.RegisteredUser as u';
    $sql.= ' where chr.isActive=1';
    $sql.= ' and chr.userID=u.userID';
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CHAR) == YAPEAL_TRACE_CHAR) {
      $mess = 'CHAR: Before GetAll $charList in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    $charList = $con->GetAll($sql);
    // Ok now that we have a list of characters that need updated
    // we can check API for updates to their infomation.
    foreach($charList as $char) {
      extract($char);
      $ownerid = $charid;
      /* **********************************************************************
      * Per character API pulls
      * **********************************************************************/
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CHAR) == YAPEAL_TRACE_CHAR) {
        $mess = 'CHAR: Before require pulls_char.inc';
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      require YAPEAL_INC . 'pulls_char.inc';
    }; // foreach $charList

  }; // if YAPEAL_CHAR_ACTIVE...
  /* ************************************************************************
  * Generate corp list
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_CORP_ACTIVE) {
    $api = 'RegisteredCorporation';
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
      $mess = 'CORP: Connect before section in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    $con = connect(DSN_CORP_WRITER);
    // Generate a list of corporation(s) we need to do updates for
    $sql = 'select cp.corporationID "corpid",u.userID "userid",u.fullApiKey "apikey",';
    $sql.= 'u.limitedApiKey "lapikey",cp.characterID "charid"';
    $sql.= ' from ' . DB_UTIL . '.RegisteredCorporation as cp,';
    $sql.= DB_UTIL . '.RegisteredCharacter as chr,';
    $sql.= DB_UTIL . '.RegisteredUser as u';
    $sql.= ' where cp.isActive=1';
    $sql.= ' and cp.characterID=chr.characterID';
    $sql.= ' and chr.userID=u.userID';
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
      $mess = 'CORP: Before GetAll $corpList in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    $corpList = $con->GetAll($sql);
    // Ok now that we have a list of corporations that need updated
    // we can check API for updates to their infomation.
    foreach($corpList as $corp) {
      extract($corp);
      $ownerid = $corpid;
      /* ********************************************************************
      * Per corp API pulls
      * ********************************************************************/
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before require pulls_corp.inc';
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      require YAPEAL_INC . 'pulls_corp.inc';
    }; // foreach $corpList

  }; // if YAPEAL_CORP_ACTIVE...
  /* ************************************************************************
  * /eve/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_EVE_ACTIVE) {
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_EVE) == YAPEAL_TRACE_EVE) {
      $mess = 'EVE: Connect before section in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    $con = connect(DSN_EVE_WRITER);
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_EVE) == YAPEAL_TRACE_EVE) {
      $mess = 'EVE: Before require pulls_eve.inc';
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    require YAPEAL_INC . 'pulls_eve.inc';
  }; // if YAPEAL_EVE_ACTIVE...
  /* ************************************************************************
  * /server/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_SERVER_ACTIVE) {
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_SERVER) == YAPEAL_TRACE_SERVER) {
      $mess = 'SERVER: Connect before section in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    $con = connect(DSN_SERVER_WRITER);
    if (YAPEAL_TRACE &&
      (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_SERVER) == YAPEAL_TRACE_SERVER) {
      $mess = 'SERVER: Before require pulls_server.inc';
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    require YAPEAL_INC . 'pulls_server.inc';
  }; // if YAPEAL_EVE_ACTIVE...
  /* ************************************************************************
  * Final admin stuff
  * ************************************************************************/
  $api = 'eve-api-pull';
  // Reset Mutex if we still own it.
  $ctime2 = getCachedUntil($api, 0, 'CachedUntil', DSN_UTIL_WRITER);
  if (YAPEAL_START_TIME == $ctime2) {
    $cuntil = gmdate('Y-m-d H:i:s');
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  } else {
    // Lost Mutex we should log that as warning.
    if ((YAPEAL_LOG_LEVEL & E_USER_WARNING) == E_USER_WARNING) {
      $mess = $api . ' ' . YAPEAL_START_TIME . ' ran long';
      print_on_command($mess);
      trigger_error($mess, E_USER_NOTICE);
    };
  }; // else $timer==get_cacheduntil $api ...
  if (YAPEAL_TRACE && !empty($yapealTracing)) {
    elog($yapealTracing, YAPEAL_TRACE_LOG);
  }; // if YAPEAL_TRACE&&...
  exit;
}
catch(Exception $e) {
  elog('Uncaught exception in '.basename(__FILE__), YAPEAL_ERROR_LOG);
  $message = <<<MESS
EXCEPTION:
     Code: {$e->getCode() }
  Message: {$e->getMessage() }
     File: {$e->getFile() }
     Line: {$e->getLine() }
Backtrace:
  {$e->getTraceAsString() }
  \t--- END TRACE ---
MESS;
  elog($message, YAPEAL_ERROR_LOG);
}
?>
