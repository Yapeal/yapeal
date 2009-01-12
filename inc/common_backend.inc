<?php
/**
 * Common include file used to setup environment for Yapeal
 *
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
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008, 2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/* Any errors that are trigger in this file are reported to the system default
 * logging location until we're done setting up some of the required vars and
 * we can start our own logging.
*/
// Used to over come path issues caused by how script is ran on server.
$incDir = realpath(dirname(__FILE__));
chdir($incDir);
$ds = DIRECTORY_SEPARATOR;
// Since this file has to be in the 'inc' directory we can set that path now and
// all the other paths are relative to it.
define('YAPEAL_INC', $incDir . $ds);
// Set constant for how we're being run.
// Mostly being used to detect cli vs cgi or apache for now where needed.
defined('YAPEAL_SCRIPT_MODE') || define('YAPEAL_SCRIPT_MODE', php_sapi_name());
// Set some basic common settings so we know we'll get to see any errors etc.
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('log_errors', TRUE);
ini_set('ignore_repeated_errors', FALSE);
ini_set('ignore_repeated_source', FALSE);
ini_set('track_errors', TRUE);
ini_set('html_errors', FALSE);
ini_set('error_log', NULL);
// Bring in function so we can 'print_on_command'
require_once YAPEAL_INC . 'elog.inc';
// Insure minimum version of PHP 5 we need to run.
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  $mess = 'Need minimum of PHP 5.2.1 to use this software!';
  print_on_command($mess);
  trigger_error($mess, E_USER_ERROR);
  exit(1);
};
// Check for some required extensions
if (!extension_loaded('curl')) {
  $mess = 'The required PHP extension cURL is missing!';
  print_on_command($mess);
  trigger_error($mess, E_USER_ERROR);
  exit(2);
};
if (!extension_loaded('mysqli')) {
  $mess = 'The required PHP extension mysqli is missing!';
  print_on_command($mess);
  trigger_error($mess, E_USER_ERROR);
  exit(2);
};
if (!extension_loaded('simplexml')) {
  $mess = 'The required PHP extension simplexml is missing!';
  print_on_command($mess);
  trigger_error($mess, E_USER_ERROR);
  exit(2);
};
/* **************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * **************************************************************************/
// Set a constant for location of configuration file.
if (!isset($iniFile)) {
  // Default assumes that this file and yapeal.ini file are in 'neighboring'
  // directories.
  $iniFile = realpath(YAPEAL_INC . $ds . '..' . $ds . 'config' . $ds . 'yapeal.ini');
}
if (!($iniFile && is_readable($iniFile) && is_file($iniFile))) {
  $mess = 'The required ' . $iniFile . ' configuration file is missing';
  print_on_command($mess);
  trigger_error($mess, E_USER_ERROR);
};
/* **************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * **************************************************************************/
// Grab the info from ini file.
$iniVars = parse_ini_file($iniFile, TRUE);
// Abort if required sections aren't defined
$requiredSections = array('Api', 'Logging', 'Paths', 'Tracing');
$mess = '';
foreach ($requiredSections as $section) {
  if (!isset($iniVars[$section])) {
    $mess .= 'Required section [' . $section;
    $mess .= '] is missing from ' . $iniFile . PHP_EOL;
  }; // if isset ...
  if (!empty($mess)) {
    print_on_command($mess);
    trigger_error($mess, E_USER_WARNING);
    exit(3);
  };
};
// Set vars use in error messages.
$req1 = 'Required variable ';
$req2 = ' in ' . $iniFile . " file isn't defined";
$nonexist = 'None existent path defined for ';
/* **************************************************************************
 * Paths
 * **************************************************************************/
// Make sure required setting vars aren't already set
unset($adodb_dir, $base_dir, $backend_dir, $cache_dir, $class_dir, $config_dir, $inc_dir, $log_dir);
// Pull out Paths settings
extract($iniVars['Paths']);
// Triggers error if a required setting is missing.
isset($adodb_dir) || trigger_error($req1 . 'adodb_dir' . $req2, E_USER_ERROR);
isset($base_dir) || trigger_error($req1 . 'base_dir' . $req2, E_USER_ERROR);
isset($backend_dir) || trigger_error($req1 . 'backend_dir' . $req2, E_USER_ERROR);
isset($cache_dir) || trigger_error($req1 . 'cache_dir' . $req2, E_USER_ERROR);
isset($class_dir) || trigger_error($req1 . 'class_dir' . $req2, E_USER_ERROR);
isset($config_dir) || trigger_error($req1 . 'config_dir' . $req2, E_USER_ERROR);
isset($log_dir) || trigger_error($req1 . 'log_dir' . $req2, E_USER_ERROR);
// Define some constants from vars
if ($realpath = realpath($dir . $ds . $adodb_dir)) {
  define('YAPEAL_ADODB', $realpath . $ds);
} else {
  trigger_error($nonexist . 'adodb_dir', E_USER_ERROR);
};
if ($realpath = realpath($dir . $ds . $base_dir)) {
  define('YAPEAL_BASE', $realpath . $ds);
} else {
  trigger_error($nonexist . 'base_dir', E_USER_ERROR);
};
if ($realpath = realpath($dir . $ds . $backend_dir)) {
  define('YAPEAL_BACKEND', $realpath . $ds);
} else {
  trigger_error($nonexist . 'backend_dir', E_USER_ERROR);
};
if ($realpath = realpath($dir . $ds . $cache_dir)) {
  if (is_writable($realpath)) {
    define('YAPEAL_CACHE', $realpath . $ds);
  } else {
    trigger_error($realpath . ' is not writeable', E_USER_ERROR);
  };
} else {
  trigger_error($nonexist . 'cache_dir', E_USER_ERROR);
};
if ($realpath = realpath($dir . $ds . $class_dir)) {
  define('YAPEAL_CLASS', $realpath . $ds);
} else {
  trigger_error($nonexist . 'class_dir', E_USER_ERROR);
};
// This is relative to YAPEAL_CACHE
if ($realpath = realpath(YAPEAL_CACHE . $log_dir)) {
  if (is_writable($realpath)) {
    define('YAPEAL_LOG', $realpath . $ds);
  } else {
    trigger_error($realpath . ' is not writeable', E_USER_ERROR);
  };
} else {
  trigger_error($nonexist . 'log_dir', E_USER_ERROR);
};
/* **************************************************************************
 * Logging
 * **************************************************************************/
// Make sure required setting vars aren't already set
unset($error_log, $level, $notice_log, $trace_log, $warning_log);
// Pull out Logging settings
extract($iniVars['Logging']);
// Triggers error if a required setting is missing.
isset($error_log) || trigger_error($req1 . 'error_log' . $req2, E_USER_ERROR);
isset($level) || trigger_error($req1 . 'level' . $req2, E_USER_ERROR);
isset($notice_log) || trigger_error($req1 . 'notice_log' . $req2, E_USER_ERROR);
isset($trace_log) || trigger_error($req1 . 'trace_log' . $req2, E_USER_ERROR);
isset($warning_log) || trigger_error($req1 . 'warning_log' . $req2, E_USER_ERROR);
// Define some constants from vars
define('YAPEAL_ERROR_LOG', YAPEAL_LOG . $error_log);
define('YAPEAL_LOG_LEVEL', $level);
define('YAPEAL_NOTICE_LOG', YAPEAL_LOG . $notice_log);
define('YAPEAL_TRACE_LOG', YAPEAL_LOG . $trace_log);
define('YAPEAL_WARNING_LOG', YAPEAL_LOG . $warning_log);
/* **************************************************************************
 * Change over to our custom error and exception code
 * **************************************************************************/
// Change some error logging settings
error_reporting(YAPEAL_LOG_LEVEL);
ini_set('error_log', YAPEAL_ERROR_LOG);
require_once YAPEAL_CLASS . 'YapealErrorHandler.class.php';
// Start using custom error handler.
set_error_handler(array('YapealErrorHandler', 'handle'));
// Log Yapeal version.
$mess = 'Yapeal version ' . YAPEAL_VERSION;
trigger_error($mess, E_USER_NOTICE);
// Setup custom exception handlers
require_once YAPEAL_CLASS . 'YapealApiException.class.php';
require_once YAPEAL_CLASS . 'ADODB_Exception.class.php';
require_once YAPEAL_CLASS . 'LoggingExceptionObserver.class.php';
require_once YAPEAL_CLASS . 'PrintingExceptionObserver.class.php';
// Setup exception observers.
$logObserver = new LoggingExceptionObserver(YAPEAL_WARNING_LOG);
$printObserver = new PrintingExceptionObserver();
// Attach (start) our custom printing and logging of exceptions.
YapealApiException::attach($logObserver);
YapealApiException::attach($printObserver);
ADODB_Exception::attach($logObserver);
ADODB_Exception::attach($printObserver);
/* **************************************************************************
 * Api
 * **************************************************************************/
// Make sure required setting vars aren't already set
unset($cache_xml, $file_suffix, $url_base);
// Pull out API settings
extract($iniVars['Api']);
// Triggers error if a required setting is missing.
isset($cache_xml) || trigger_error($req1 . 'cache_xml' . $req2, E_USER_ERROR);
isset($file_suffix) || trigger_error($req1 . 'file_suffix' . $req2, E_USER_ERROR);
isset($url_base) || trigger_error($req1 . 'url_base' . $req2, E_USER_ERROR);
// Define some constants from vars
define('API_URL_BASE', $url_base);
define('API_SUFFIX', $file_suffix);
define('YAPEAL_CACHE_XML', $cache_xml);
// Define some addtional constants that are mostly used by pull scripts
define('YAPEAL_API_ACCOUNT', 0);
define('YAPEAL_API_CHAR', 1);
define('YAPEAL_API_CORP', 2);
define('YAPEAL_API_EVE', 3);
define('YAPEAL_API_MAP', 4);
define('YAPEAL_API_SERVER', 5);
/* **************************************************************************
 * Trace
 * **************************************************************************/
// Define some constants used with trace_section setting.
// They need to be set here so they exist before re-reading ini file.
// Lower 16 bits used for API.
define('YAPEAL_TRACE_ALL', 2147483647);
define('YAPEAL_TRACE_NONE', 0);
define('YAPEAL_TRACE_ACCOUNT', 1);
define('YAPEAL_TRACE_CHAR', 2);
define('YAPEAL_TRACE_CORP', 4);
define('YAPEAL_TRACE_EVE', 8);
define('YAPEAL_TRACE_MAP', 16);
define('YAPEAL_TRACE_SERVER', 32);
// Upper bits used for Yapeal.
define('YAPEAL_TRACE_API', 65536);
define('YAPEAL_TRACE_CACHE', 131072);
define('YAPEAL_TRACE_CURL', 262144);
define('YAPEAL_TRACE_DATABASE', 524288);
define('YAPEAL_TRACE_FILES', 1048576);
define('YAPEAL_TRACE_REQUEST', 2097152);
// Grab the info from ini file again now that our constants are defined.
$iniVars = parse_ini_file($iniFile, TRUE);
// Make sure required setting vars aren't already set
unset($trace, $trace_level, $trace_output, $trace_section);
// Pull out API settings
extract($iniVars['Tracing']);
// Triggers error if a required setting is missing.
isset($trace) || trigger_error($req1 . 'trace' . $req2, E_USER_ERROR);
isset($trace_level) || trigger_error($req1 . 'trace_level' . $req2, E_USER_ERROR);
isset($trace_output) || trigger_error($req1 . 'trace_output' . $req2, E_USER_ERROR);
isset($trace_section) || trigger_error($req1 . 'trace_section' . $req2, E_USER_ERROR);
// Define some constants from vars
define('YAPEAL_TRACE', $trace);
define('YAPEAL_TRACE_LEVEL', $trace_level);
define('YAPEAL_TRACE_OUTPUT', $trace_output);
define('YAPEAL_TRACE_SECTION', $trace_section);
// Load tracing code here and set it up.
require_once YAPEAL_CLASS . 'YapealTracing.class.php';
$tracing = new YapealTracing();
/* **************************************************************************
 * Datebase-xxx sections
 * **************************************************************************/
$dbs = array('account', 'char', 'corp', 'eve', 'map', 'server', 'util');
$requireds = array('database', 'driver', 'host', 'suffix', 'writer');
foreach($dbs as $db) {
  $data = array();
  // Overwrite defaults with ones given in Database-xxx section
  if (isset($iniVars['Database-' . $db]) &&
    isset($iniVars['Database-' . $db]['active']) &&
    $iniVars['Database-' . $db]['active'] == TRUE) {
    foreach ($requireds as $required) {
      // Set to default if we have one.
      if (isset($iniVars['Database']) && isset($iniVars['Database'][$required])) {
        $data[$required] = $iniVars['Database'][$required];
      };
      // Set to section value if it exists which will override default.
      if (isset($iniVars['Database-' . $db][$required])) {
        $data[$required] = $iniVars['Database-' . $db][$required];
      };
      // If it's not set by now it's missing trigger the error.
      if (empty($data[$required])) {
        trigger_error($req1 . $required . $req2, E_USER_ERROR);
      };
    };
    // All constant names are in uppercase only.
    $db = strtoupper($db);
    // Extract the required fields from array.
    extract($data);
    // Mark as active.
    define('YAPEAL_' . $db . '_ACTIVE', TRUE);
    // Define some constants from vars.
    define('DB_' . $db, $database);
    define('DSN_' . $db . '_WRITER', $driver . $writer . '@' . $host . '/' . $database . $suffix);
  } elseif ($db == 'util') {
    $mess = 'Section [util] is required in ' . $iniFile;
    trigger_error($mess, E_USER_ERROR);
  } elseif (!isset($iniVars['Database-' . $db])) {
    // Mark as inactive if section is missing.
    define('YAPEAL_' . strtoupper($db) . '_ACTIVE', FALSE);
    $mess = 'Optional section [Datebase-' . $db . '] is missing';
    trigger_error($mess, E_USER_NOTICE);
  } else {
    // Mark section as deactived.
    define('YAPEAL_' . strtoupper($db) . '_ACTIVE', FALSE);
    $mess = 'Optional section [Datebase-' . $db . '] is deactived';
    trigger_error($mess, E_USER_NOTICE);
  };
}; // foreach $dbs...
?>
