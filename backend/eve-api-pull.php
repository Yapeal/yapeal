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
 * @copyright Copyright (c) 2008, 2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
// Track versioning of script.
define('YAPEAL_STABILITY', 'beta');
define('YAPEAL_VERSION',
  (int)trim(str_replace(array('$', 'Revision:'), '', '$Revision$')));
define('YAPEAL_DATE', trim(str_replace(
  array('$', '#', 'Date::') , '', '$Date::                      $')));
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// If being run from command-line look for options there if function available.
if (PHP_SAPI == 'cli' && function_exists('getopt')) {
  $options = getopt('hVc:d:');
  foreach($options as $opt => $value) {
    switch ($opt) {
      case 'c':
        $iniFile = realpath($value);
        break;
      case 'd':
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
};// if PHP_SAPI == 'cli' && ...
/* **************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* **************************************************************************/
/* This would need to be changed if this file isn't in another path at same
* level as 'inc' directory where common_emt.inc is.
*/
// Move up and over to 'inc' directory to read common_backend.inc
$ds = DIRECTORY_SEPARATOR;
$path = realpath(dirname(__FILE__)) . $ds . '..' . $ds;
$path .= 'inc' . $ds . 'common_backend.inc';
require_once realpath($path);
/* **************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* **************************************************************************/
require_once YAPEAL_INC . 'elog.inc';
require_once YAPEAL_INC . 'common_db.inc';
require_once YAPEAL_INC . 'common_api.inc';
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
    define('YAPEAL_START_TIME',gmdate('Y-m-d H:i:s', strtotime('5 minutes')));
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => YAPEAL_START_TIME);
    $mess = 'Before upsert for ' . $api . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
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
    $mess = 'Character section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    try {
      $charList = getRegisteredCharacters();
      // Ok now that we have a list of characters that need updated
      // we can check API for updates to their infomation.
      foreach ($charList as $char) {
        extract($char);
        $ownerid = $charid;
        /* **********************************************************************
        * Per character API pulls
        * **********************************************************************/
        $mess = 'Before require pulls_char.inc for character ' . $ownerid;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        require YAPEAL_INC . 'pulls_char.inc';
      }; // foreach $charList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
  }; // if YAPEAL_CHAR_ACTIVE...
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
        $ownerid = $corpid;
        /* ********************************************************************
        * Per corp API pulls
        * ********************************************************************/
        $mess = 'Before require pulls_corp.inc for corporation ' . $ownerid;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        require YAPEAL_INC . 'pulls_corp.inc';
      }; // foreach $corpList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
  }; // if YAPEAL_CORP_ACTIVE...
  /* ************************************************************************
  * /eve/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_EVE_ACTIVE) {
    $mess = 'Eve section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_EVE, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
    $mess = 'Connect for eve section in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
    $con = connect(DSN_EVE_WRITER, 'eve');
    $mess = 'Before require pulls_eve.inc';
    $tracing->activeTrace(YAPEAL_TRACE_EVE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
    require YAPEAL_INC . 'pulls_eve.inc';
  }; // if YAPEAL_EVE_ACTIVE...
  /* ************************************************************************
  * /server/ API pulls
  * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_SERVER_ACTIVE) {
    $mess = 'Server section active in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    $mess = 'Connect for server section in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    $con = connect(DSN_SERVER_WRITER, 'server');
    $mess = 'Before require pulls_server.inc';
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    require YAPEAL_INC . 'pulls_server.inc';
  }; // if YAPEAL_EVE_ACTIVE...
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
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
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
?>
