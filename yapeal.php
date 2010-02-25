#!/usr/bin/php -Cq
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
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 * @since      revision 561
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// Define shortened name for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);
// Pull in Yapeal revision constants.
$path = $dir . DS . 'revision.php';
require_once realpath($path);
# Make CGI work like CLI.
if (PHP_SAPI != 'cli') {
  ini_set('implicit_flush', '1');
  ini_set('register_argc_argv', '1');
};
// If being run from command-line look for options there if function available.
if (function_exists('getopt')) {
  $options = getopt('hVc:d:');
  if (!empty($options)) {
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
          $mess .= "Copyright (C) 2008-2010, Michael Cummings" . PHP_EOL;
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
  };// if !empty $options ...
};// if function_exists getopt ...
// Move down to 'inc' directory to read common_backend.php
$path = $dir . DS . 'inc' . DS . 'common_backend.php';
require_once realpath($path);
try {
  $api = 'eve-api-pull';
  // Mutex to keep from having more than one pull going at once most the time.
  // Turned logging off here since this runs every minute.
  if (YapealDBConnection::dontWait($api, 0, FALSE)) {
    /**
     * Give ourself up to 5 minutes to finish.
     */
    define('YAPEAL_MAX_EXECUTE', strtotime('5 minutes'));
    /**
     * Used to have the same time on all script that error out and need to be
     * ran again.
     */
    define('YAPEAL_START_TIME',gmdate('Y-m-d H:i:s', strtotime('5 minutes')));
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => YAPEAL_START_TIME);
    YapealDBConnection::upsert($data,
      YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
  } else {
    // Someone else has set timer need to wait it out.
    exit;
  };// else dontwait $api ...
  /* ************************************************************************
   * Generate section list
   * ************************************************************************/
  // Build sql to get section list from DB.
  $sql = 'select `activeAPI`,`proxy`,`sectionName`';
  $sql .= ' from ';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilSections`';
  $sql .= ' where isActive=1';
  $sql .= ' order by sectionName asc';
  try {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $sections = $con->GetAll($sql);
  }
  catch (ADODB_Exception $e) {
    // Do nothing use observers to log info
  }
  $sectionList = FilterFileFinder::getStrippedFiles(YAPEAL_CLASS, 'Section');
  // Now take list of sections and call each in turn
  foreach ($sections as $section) {
    extract($section);
    // Skip if section file not found.
    if (!in_array(ucfirst($sectionName), $sectionList)) {
      $mess = 'Class file not found for section ' . $sectionName;
      trigger_error($mess, E_USER_NOTICE);
      continue;
    };// if !in_array(ucfirst($sectionName),...
    $class = 'Section' . ucfirst($sectionName);
    $apis = explode(' ', $activeAPI);
    // Skip if there's no active APIs for this section.
    if (count($apis) == 0) {
      $mess = 'No active APIs listed for section ' . $sectionName;
      trigger_error($mess, E_USER_NOTICE);
      continue;
    };
    try {
      $instance = new $class($proxy, $apis);
      $instance->pullXML();
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
  };// foreach $section ...
  /* ************************************************************************
   * Final admin stuff
   * ************************************************************************/
  $api = 'eve-api-pull';
  // Reset Mutex if we still own it.
  $ctime2 = YapealDBConnection::getCachedUntil($api, 0);
  if (YAPEAL_START_TIME == $ctime2) {
    $cuntil = gmdate('Y-m-d H:i:s');
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => $cuntil);
    YapealDBConnection::upsert($data,
      YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
  } else {
    // Lost Mutex we should log that as warning.
    $mess = $api . ' ' . YAPEAL_START_TIME . ' ran long';
    trigger_error($mess, E_USER_WARNING);
  }; // else $timer==get_cacheduntil $api ...
}
catch (Exception $e) {
  require_once YAPEAL_CLASS . 'YapealErrorHandler.php';
  $mess = 'Uncaught exception in ' . basename(__FILE__);
  YapealErrorHandler::elog($mess, YAPEAL_ERROR_LOG);
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
  YapealErrorHandler::elog($message, YAPEAL_ERROR_LOG);
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
?>
