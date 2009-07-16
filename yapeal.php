#!/usr/bin/php
<?php
/**
 * Used to get information from Eve-online API and store in database.
 *
 * This script expects to be ran from a command line or from a crontab job that
 * can optionally pass a config file name with -c option.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal.
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
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 * @since revision 561
 */
/**
 * Track stability of script.
 */
define('YAPEAL_STABILITY', 'beta');
/**
 * Track version of script.
 */
define('YAPEAL_VERSION',
  (int)trim(str_replace(array('$', 'Revision:'), '', '$Revision$')));
/**
 * Track date of script.
 */
define('YAPEAL_DATE', trim(str_replace(
  array('$', '#', 'Date::') , '', '$Date::                      $')));
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// If being run from command-line look for options there if function available.
if (PHP_SAPI == 'cli') {
  if (function_exists('getopt')) {
    $options = getopt('hVc:d:');
    foreach ($options as $opt => $value) {
      switch ($opt) {
        case 'c':
          $iniFile = realpath($value);
          break;
        case 'd':
          /**
           * Used to turn on special debug logging.
           */
          define('YAPEAL_DEBUG', $value);
          break;
        case 'h':
          usage();
          exit;
        case 'V':
          $mess = $argv[0] . ' ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
          $mess .= YAPEAL_DATE . PHP_EOL;
          $mess .= "Copyright (C) 2008, 2009, Michael Cummings" . PHP_EOL;
          $mess .= "This program comes with ABSOLUTELY NO WARRANTY." . PHP_EOL;
          $mess .= 'Licensed under the GNU LPGL 3.0 License.' . PHP_EOL;
          $mess .= 'See COPYING and COPYING-LESSER for more details.' . PHP_EOL;
          fwrite(STDOUT, $mess);
          exit;
        default:
          $mess = 'Unknown option ' . $opt . PHP_EOL;
          usage();
          exit(1);
      };// switch $opt
    };// foreach $options...
  };// if function_exists getopt ...
} else {
  notAWebPage();
};// else PHP_SAPI == 'cli' ...
/* **************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* **************************************************************************/
// Move down to 'inc' directory to read common_backend.php
$ds = DIRECTORY_SEPARATOR;
$path = $dir . $ds . 'inc' . $ds . 'common_backend.php';
require_once realpath($path);
/* **************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* **************************************************************************/
require_once YAPEAL_INC . 'common_db.php';
$cachetypes = array('tableName' => 'C', 'ownerID' => 'I', 'cachedUntil' => 'T');
try {
  $api = 'eve-api-pull';
  $mess = 'Before dontWait for ' . $api . ' in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_API, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_API, $mess);
  // Mutex to keep from having more than one pull going at once most the time.
  // Turned logging off here since this runs every minute.
  if (dontWait($api, 0, FALSE)) {
    // Give ourself up to 5 minutes to finish.
    /**
     * Used to have the same time on all script that error out and need to be
     * ran again.
     */
    define('YAPEAL_START_TIME',gmdate('Y-m-d H:i:s', strtotime('5 minutes')));
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => YAPEAL_START_TIME);
    $mess = 'Before upsert for ' . $api . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
      YAPEAL_DSN);
  } else {
    // Someone else has set timer need to wait it out.
    exit;
  };// else dontwait $api ...
  /* ************************************************************************
  * Generate user list
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_ACCOUNT_ACTIVE) {
    $mess = 'Account section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_ACCOUNT, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_ACCOUNT, $mess);
    try {
      $userList = getRegisteredUsers();
      // Ok now that we have a list of users that need updated
      // we can check API for updates to their infomation.
      foreach ($userList as $user) {
        extract($user);
        /* **********************************************************************
        * Per user API pulls
        * **********************************************************************/
        $mess = 'Before require pulls_account.php for user ' . $userID;
        $tracing->activeTrace(YAPEAL_TRACE_ACCOUNT, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_ACCOUNT, $mess);
        require YAPEAL_INC . 'pulls_account.php';
      }; // foreach $userList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
    $tracing->flushTrace();
  };// if YAPEAL_ACCOUNT_ACTIVE...
  /* ************************************************************************
  * Generate character list
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_CHAR_ACTIVE) {
    $mess = 'Character section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    try {
      $charList = getRegisteredCharacters();
      // Ok now that we have a list of characters that need updated
      // we can check API for updates to their infomation.
      foreach ($charList as $char) {
        extract($char);
        /* **********************************************************************
        * Per character API pulls
        * **********************************************************************/
        $mess = 'Before require pulls_char.php for character ' . $charID;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        require YAPEAL_INC . 'pulls_char.php';
      }; // foreach $charList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
    $tracing->flushTrace();
  };// if YAPEAL_CHAR_ACTIVE...
  /* ************************************************************************
  * Generate corp list
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_CORP_ACTIVE) {
    $mess = 'Corporation section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_CORP, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
    try {
      $corpList = getRegisteredCorporations();
      // Ok now that we have a list of corporations that need updated
      // we can check API for updates to their infomation.
      foreach ($corpList as $corp) {
        extract($corp);
        /* ********************************************************************
        * Per corp API pulls
        * ********************************************************************/
        $mess = 'Before require pulls_corp.php for corporation ' . $corpID;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        require YAPEAL_INC . 'pulls_corp.php';
      }; // foreach $corpList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
    $tracing->flushTrace();
  };// if YAPEAL_CORP_ACTIVE...
  /* ************************************************************************
  * /eve/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_EVE_ACTIVE) {
    $mess = 'Eve section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_EVE, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
    $mess = 'Before require pulls_eve.php';
    $tracing->activeTrace(YAPEAL_TRACE_EVE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
    require YAPEAL_INC . 'pulls_eve.php';
    $tracing->flushTrace();
  }; // if YAPEAL_EVE_ACTIVE...
  /* ************************************************************************
  * /map/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_MAP_ACTIVE) {
    $mess = 'Eve section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_MAP, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_MAP, $mess);
    $mess = 'Before require pulls_map.php';
    $tracing->activeTrace(YAPEAL_TRACE_MAP, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_MAP, $mess);
    require YAPEAL_INC . 'pulls_map.php';
    $tracing->flushTrace();
  };// if YAPEAL_EVE_ACTIVE...
  /* ************************************************************************
  * /server/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_SERVER_ACTIVE) {
    $mess = 'Server section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    $mess = 'Before require pulls_server.php';
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    require YAPEAL_INC . 'pulls_server.php';
    $tracing->flushTrace();
  };// if YAPEAL_EVE_ACTIVE...
  /* ************************************************************************
  * Final admin stuff
  * ************************************************************************/
  $api = 'eve-api-pull';
  // Reset Mutex if we still own it.
  $ctime2 = getCachedUntil($api, 0);
  if (YAPEAL_START_TIME == $ctime2) {
    $cuntil = gmdate('Y-m-d H:i:s');
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => $cuntil);
    upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
      YAPEAL_DSN);
  } else {
    // Lost Mutex we should log that as warning.
    $mess = $api . ' ' . YAPEAL_START_TIME . ' ran long';
    trigger_error($mess, E_USER_WARNING);
  }; // else $timer==get_cacheduntil $api ...
}
catch (Exception $e) {
  elog('Uncaught exception in ' . basename(__FILE__), YAPEAL_ERROR_LOG);
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
trigger_error('Peak memory used:' . memory_get_peak_usage(TRUE), E_USER_NOTICE);
exit;
/**
 * Function use to show the usage message on command line.
 */
function usage() {
  $progname = basename($GLOBALS['argv'][0]);
  $scriptversion = YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
  $scriptversion .= YAPEAL_DATE . PHP_EOL;
  $use = <<<USAGE_MESSAGE
Usage: $progname [-V | [-h] | [-c <config.ini>] [-d <logfile.log>]]
Options:
  -c config.ini                        Read configation from 'config.ini'.
  -d logfile.log                       Save debugging log to 'logfile.log'.
  -h                                   Show this help.
  -V                                   Show $progname version and license.

Version $scriptversion
USAGE_MESSAGE;
  fwrite(STDOUT, $use);
};
/**
 * Function use to show an error web page if run from web browser.
 */
function notAWebPage () {
  $page = <<<WEBPAGE
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http:www.w3.org/TR/xhtml1" xml:lang="en" lang="en">
  <head>
    <title>Yapeal is not a web application</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  </head>
  <body>
    <h1 style="font-size: xx-large;color: #ff1010;">USER ERROR USER ERROR USER ERROR</h1>
    <p>
    If you are seeing this you have tried to run Yapeal as a web page which is
    incorrect.
    Yapeal is made to <b>ONLY</b> ran from the command line.
    See the <a href="http://code.google.com/p/yapeal/w/list">Yapeal Wiki</a> for
    more information on using it.
    </p>
  </body>
</html>
WEBPAGE;
print $page . PHP_EOL;
exit(128);
}
?>
