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
// Set some basic common settings so we know we'll get to see any errors etc.
error_reporting(E_ALL);
ini_set('ignore_repeated_errors', 0);
ini_set('ignore_repeated_source', 0);
ini_set('html_errors', 0);
ini_set('display_errors', 1);
ini_set('error_log', NULL);
ini_set('log_errors', 0);
// Special debugging command-line override.
if (defined('YAPEAL_DEBUG')) {
  ob_start();
  trigger_error(str_pad(' Pre-custom ', 75, '-', STR_PAD_BOTH), E_USER_NOTICE);
  ini_set('track_errors', 1);
} else {
  ini_set('track_errors', 0);
};// else defined YAPEAL_DEBUG ...
// Log Yapeal version information.
$mess = 'Yapeal version ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
$mess .= YAPEAL_DATE;
trigger_error($mess, E_USER_NOTICE);
// Insure minimum version of PHP 5 we need to run.
if (version_compare(PHP_VERSION, '5.2.1', '<')) {
  $mess = 'Need minimum of PHP 5.2.1 to use this software!';
  trigger_error($mess, E_USER_ERROR);
  exit(1);
};
// Check for some required extensions
$required = array('curl', 'mysqli', 'SimpleXML');
$exts = get_loaded_extensions();
foreach ($required as $ext) {
  if (!in_array($ext, $exts)) {
    $mess = 'The required PHP extension ' . $ext . ' is missing!';
    trigger_error($mess, E_USER_ERROR);
    exit(2);
  };
};// foreach $exts ...
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
  trigger_error($mess, E_USER_ERROR);
};
/* **************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * **************************************************************************/
// Grab the info from ini file.
$iniVars = parse_ini_file($iniFile, TRUE);
// Abort if required sections aren't defined
$sections = array('Api', 'Logging', 'Paths', 'Tracing');
$mess = '';
foreach ($sections as $section) {
  if (!isset($iniVars[$section])) {
    $mess .= 'Required section [' . $section;
    $mess .= '] is missing from ' . $iniFile . PHP_EOL;
  }; // if isset ...
};
if (!empty($mess)) {
  trigger_error($mess, E_USER_WARNING);
  exit(3);
};
// Set vars use in error messages.
$req1 = 'Required variable ';
$req2 = ' in ' . $iniFile . '  is undefined';
$nonexist = 'Nonexistent directory defined for ';
/* **************************************************************************
 * Paths
 * **************************************************************************/
$settings = array('adodb', 'base', 'backend', 'cache','class', 'config');
foreach ($settings as $setting) {
  // Set to section value if it exists.
  if (isset($iniVars['Paths'][$setting])) {
    $realpath = realpath($incDir . $ds . $iniVars['Paths'][$setting]);
    if ($realpath && is_dir($realpath)) {
      define('YAPEAL_' . strtoupper($setting), $realpath . $ds);
    } else {
      trigger_error($nonexist . $setting, E_USER_ERROR);
    };
  } else {
    trigger_error($req1 . $setting . $req2, E_USER_ERROR);
  };// else isset $iniVars...
};// foreach $settings ...
// Check writable paths
if (!is_writable(YAPEAL_CACHE)) {
  trigger_error($realpath . ' is not writeable', E_USER_ERROR);
};
$sections = array('account', 'char', 'corp', 'eve', 'map', 'server');
foreach ($sections as $section) {
  $realpath = realpath(YAPEAL_CACHE . $section);
  if (!$realpath || !is_dir($realpath)) {
    $mess = 'Missing required directory ' . YAPEAL_CACHE . $section;
    trigger_error($mess, E_USER_ERROR);
  };
  if (!is_writable($realpath)) {
    trigger_error(YAPEAL_CACHE . $section . ' is not writeable', E_USER_ERROR);
  };
};// foreach $sections ...
// log_dir is relative to YAPEAL_CACHE
if (isset($iniVars['Paths']['log'])) {
  $realpath = realpath(YAPEAL_CACHE . $iniVars['Paths']['log']);
  if ($realpath && is_dir($realpath)) {
    define('YAPEAL_LOG', $realpath . $ds);
  } else {
    trigger_error($nonexist . 'log', E_USER_ERROR);
  };
  if (!is_writable($realpath)) {
    trigger_error($realpath . ' is not writeable', E_USER_ERROR);
  };
} else {
  trigger_error($req1 . 'log' . $req2, E_USER_ERROR);
};
/* **************************************************************************
 * Logging
 * **************************************************************************/
// Special debugging command-line override.
if (defined('YAPEAL_DEBUG')) {
  error_reporting(E_ALL);
  define('YAPEAL_ERROR_LOG', YAPEAL_DEBUG);
  define('YAPEAL_NOTICE_LOG', YAPEAL_DEBUG);
  define('YAPEAL_TRACE_LOG', YAPEAL_DEBUG);
  define('YAPEAL_WARNING_LOG', YAPEAL_DEBUG);
} else {
  $settings = array('error_log', 'notice_log', 'trace_log', 'warning_log');
  foreach ($settings as $setting) {
    // Set to section value if it exists.
    if (isset($iniVars['Logging'][$setting])) {
      define('YAPEAL_' . strtoupper($setting),
        YAPEAL_LOG . $iniVars['Logging'][$setting]);
    } else {
      trigger_error($req1 . $setting . $req2, E_USER_ERROR);
    };// else isset $iniVars...
  };// foreach $settings ...
  // Set error reporting level.
  if (isset($iniVars['Logging']['log_level'])) {
    error_reporting($iniVars['Logging']['log_level']);
  } else {
    trigger_error($req1 . 'log_level' . $req2, E_USER_ERROR);
  };
};// else defined YAPEAL_DEBUG ...
/* **************************************************************************
 * Change over to our custom error and exception code
 * **************************************************************************/
// Change some error logging settings.
ini_set('error_log', YAPEAL_ERROR_LOG);
ini_set('log_errors', 1);
require_once YAPEAL_INC . 'elog.inc';
require_once YAPEAL_CLASS . 'YapealErrorHandler.class.php';
// Start using custom error handler.
set_error_handler(array('YapealErrorHandler', 'handle'));
if (defined('YAPEAL_DEBUG')) {
  $mess = ob_get_flush() . PHP_EOL;
  file_put_contents(YAPEAL_DEBUG, $mess);
};
// Printing for CLI will be handled in our custom handler from now on.
//if (PHP_SAPI == 'cli') {
//  ini_set('display_errors', FALSE);
//};
$mess = str_pad(' Custom handler started ', 75, '-', STR_PAD_BOTH);
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
 * Logging init file if debugging.
 * **************************************************************************/
if (defined('YAPEAL_DEBUG')) {
  $mess = str_pad(' '. $iniFile .' ', 75, '-', STR_PAD_BOTH) . PHP_EOL;
  $settings = array('version', 'stability', 'date');
  foreach ($settings as $setting) {
    if (isset($iniVars[$setting])) {
      $mess .= $setting . ' = ' . $iniVars[$setting] . PHP_EOL;
    };
  };
  $sections = array('Api', 'Database', 'Database-account', 'Database-char',
    'Database-corp', 'Database-eve', 'Database-map', 'Database-server',
    'Database-util', 'Logging', 'Paths', 'Tracing'
  );
  $hidden = array('writer', 'username', 'password');
  foreach ($sections as $section) {
    if (isset($iniVars[$section])) {
      $mess .= '[' . $section . ']' . PHP_EOL;
      $settings = array_keys($iniVars[$section]);
      foreach ($settings as $setting) {
        // Hide any usernames or password fields.
        if (in_array($setting, $hidden)) {
          $value = str_repeat('*', strlen($iniVars[$section][$setting]));
          $mess .= $setting . ' = ' . $value . PHP_EOL;
        } else {
          $mess .= $setting . ' = ' . $iniVars[$section][$setting] . PHP_EOL;
        };// else in_array $setting ...
      };// foreach $settings ...
    };// if isset $iniVars...
  };//foreach $sections ...
  $mess .= str_pad(' End config file ', 75, '-', STR_PAD_BOTH);
  trigger_error($mess, E_USER_NOTICE);
};// if defined YAPEAL_DEBUG ...
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
define('YAPEAL_API_UTIL', 65535);
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
// Special debugging command-line override.
if (defined('YAPEAL_DEBUG')) {
  define('YAPEAL_TRACE', TRUE);
  define('YAPEAL_TRACE_LEVEL', 2);
  define('YAPEAL_TRACE_OUTPUT', 'file');
  define('YAPEAL_TRACE_SECTION', YAPEAL_TRACE_ALL);
} else {
  $settings = array('trace', 'trace_level', 'trace_output', 'trace_section');
  foreach ($settings as $setting) {
    // Set to section value if it exists.
    if (isset($iniVars['Tracing'][$setting])) {
      define('YAPEAL_' . strtoupper($setting), $iniVars['Tracing'][$setting]);
    } else {
      trigger_error($req1 . $setting . $req2, E_USER_ERROR);
    };// if isset $iniVars...
  };// foreach $settings ...
};// else defined YAPEAL_DEBUG ...
// Load tracing code here and set it up.
require_once YAPEAL_CLASS . 'YapealTracing.class.php';
$tracing = new YapealTracing();
/* **************************************************************************
 * Datebase-xxx sections
 * **************************************************************************/
$sections = array('account', 'char', 'corp', 'eve', 'map', 'server', 'util');
$settings = array('database', 'driver', 'host', 'suffix', 'writer');
foreach ($sections as $section) {
  $data = array();
  // Overwrite defaults with ones given in Database-xxx section
  if (isset($iniVars['Database-' . $section]) &&
    isset($iniVars['Database-' . $section]['active']) &&
    $iniVars['Database-' . $section]['active'] == TRUE) {
    foreach ($settings as $setting) {
      // Set to default if we have one.
      if (isset($iniVars['Database']) && isset($iniVars['Database'][$setting])) {
        $data[$setting] = $iniVars['Database'][$setting];
      };
      // Set to section value if it exists which will override default.
      if (isset($iniVars['Database-' . $section][$setting])) {
        $data[$setting] = $iniVars['Database-' . $section][$setting];
      };
      // If it's not set by now it's missing trigger the error.
      if (empty($data[$setting])) {
        trigger_error($req1 . $setting . $req2, E_USER_ERROR);
      };
    };
    // All constant names are in uppercase only.
    $section = strtoupper($section);
    // Extract the required fields from array.
    extract($data);
    // Mark as active.
    define('YAPEAL_' . $section . '_ACTIVE', TRUE);
    // Define some constants from vars.
    define('DB_' . $section, $database);
    define('DSN_' . $section . '_WRITER',
      $driver . $writer . '@' . $host . '/' . $database . $suffix);
  } elseif ($section == 'util') {
    $mess = 'Section [util] is required in ' . $iniFile;
    trigger_error($mess, E_USER_ERROR);
  } elseif (!isset($iniVars['Database-' . $section])) {
    // Mark as inactive if section is missing.
    define('YAPEAL_' . strtoupper($section) . '_ACTIVE', FALSE);
    $mess = 'Optional section [Datebase-' . $section . '] is missing';
    trigger_error($mess, E_USER_NOTICE);
  } else {
    // Mark section as deactived.
    define('YAPEAL_' . strtoupper($section) . '_ACTIVE', FALSE);
    $mess = 'Optional section [Datebase-' . $section . '] is deactived';
    trigger_error($mess, E_USER_NOTICE);
  };
}; // foreach $dbs...
?>
