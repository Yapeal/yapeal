<?php
/**
 * Common include file used to setup environment for Yapeal.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
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
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/** Any errors that are trigger in this file are reported to the system default
 * logging location until we're done setting up some of the required vars and
 * we can start our own logging.
 */
// Used to over come path issues caused by how script is ran on server.
$incDir = realpath(dirname(__FILE__));
chdir($incDir);
// Define shortened name for DIRECTORY_SEPARATOR
if (!defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
};
// Set max SQL insert size. This is a trade off of memory use and number of
// inserts needed for larger APIs.
define('YAPEAL_MAX_UPSERT', 1000);
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
  trigger_error(str_pad(' Pre-custom ', 65, '-', STR_PAD_BOTH), E_USER_NOTICE);
  ini_set('track_errors', 1);
} else {
  ini_set('track_errors', 0);
};// else defined YAPEAL_DEBUG ...
// Get Yapeal version if not already defined.
if (!defined('YAPEAL_VERSION')) {
  $path = $incDir . DS . '..' . DS . 'revision.php';
  require_once realpath($path);
}
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
$required = array('curl', 'date', 'mysqli', 'SimpleXML', 'SPL');
$exts = get_loaded_extensions();
$missing = array_diff($required, $exts);
if (count($missing) > 0) {
  $mess = 'The required PHP extensions: ';
  $mess .= implode(', ', $missing) . ' are missing!';
  trigger_error($mess, E_USER_ERROR);
  exit(2);
};
// Set the default timezone for GMT.
date_default_timezone_set('GMT');
// Get path constants so they can be used.
require_once $incDir . DS . 'common_paths.php';
// Set a constant for location of configuration file.
if (!isset($iniFile)) {
  // Default assumes that this file and yapeal.ini file are in 'neighboring'
  // directories.
  $iniFile = realpath(YAPEAL_CONFIG . 'yapeal.ini');
}
if (!($iniFile && is_readable($iniFile) && is_file($iniFile))) {
  $mess = 'The required ' . $iniFile . ' configuration file is missing';
  trigger_error($mess, E_USER_ERROR);
};
// Grab the info from ini file.
$iniVars = parse_ini_file($iniFile, TRUE);
// Abort if required sections aren't defined
$sections = array('Cache', 'Database', 'Logging');
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
$req1 = 'Missing required setting ';
$req2 = ' in ' . $iniFile;
$nonexist = 'Nonexistent directory defined for ';
/* **************************************************************************
 * Class autoloader section
 * **************************************************************************/
require_once YAPEAL_CLASS . 'YapealAutoLoad.php';
/* **************************************************************************
 * Logging section
 * **************************************************************************/
// Check writable paths
if (!is_writable(YAPEAL_LOG)) {
  trigger_error(YAPEAL_LOG . ' is not writeable', E_USER_ERROR);
};
// Grab the info from ini file again now that our constants are defined.
$iniVars = parse_ini_file($iniFile, TRUE);
// Special debugging command-line override.
if (defined('YAPEAL_DEBUG')) {
  error_reporting(E_ALL);
  define('YAPEAL_ERROR_LOG', YAPEAL_DEBUG);
  define('YAPEAL_NOTICE_LOG', YAPEAL_DEBUG);
  define('YAPEAL_STRICT_LOG', YAPEAL_DEBUG);
  //define('YAPEAL_TRACE_ACTIVE', TRUE);
  //define('YAPEAL_TRACE_LEVEL', 2);
  //define('YAPEAL_TRACE_LOG', YAPEAL_DEBUG);
  //define('YAPEAL_TRACE_OUTPUT', 'file');
  //define('YAPEAL_TRACE_SECTIONS', YAPEAL_TRACE_ALL);
  define('YAPEAL_WARNING_LOG', YAPEAL_DEBUG);
} else {
  $settings = array('error_log', 'notice_log', 'strict_log', 'trace_log', 'warning_log');
  foreach ($settings as $setting) {
    if (isset($iniVars['Logging'][$setting])) {
      $constant = 'YAPEAL_' . strtoupper($setting);
      define($constant, YAPEAL_LOG . $iniVars['Logging'][$setting]);
    } else {
      trigger_error($req1 . $setting . $req2, E_USER_ERROR);
    };// else isset $iniVars...
  };// foreach $settings ...
  $settings = array('log_level', 'trace_active', 'trace_level', 'trace_output',
    'trace_sections');
  foreach ($settings as $setting) {
    if (isset($iniVars['Logging'][$setting])) {
      $constant = 'YAPEAL_' . strtoupper($setting);
      define($constant, $iniVars['Logging'][$setting]);
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
// Start using custom error handler.
set_error_handler(array('YapealErrorHandler', 'handle'));
if (defined('YAPEAL_DEBUG')) {
  $mess = ob_get_flush() . PHP_EOL;
  file_put_contents(YAPEAL_DEBUG, $mess);
};
// Printing for CLI will be handled in our custom handler from now on.
$mess = str_pad(' Custom handler started ', 65, '-', STR_PAD_BOTH);
trigger_error($mess, E_USER_NOTICE);
$mess = 'Yapeal version ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
$mess .= YAPEAL_DATE;
trigger_error($mess, E_USER_NOTICE);
// Setup exception observers.
$logObserver = new LoggingExceptionObserver(YAPEAL_WARNING_LOG);
$printObserver = new PrintingExceptionObserver();
// Attach (start) our custom printing and logging of exceptions.
YapealApiException::attach($logObserver);
YapealApiException::attach($printObserver);
ADODB_Exception::attach($logObserver);
ADODB_Exception::attach($printObserver);
unset($logObserver, $printObserver);
/* **************************************************************************
 * Logging init file if debugging.
 * **************************************************************************/
if (defined('YAPEAL_DEBUG')) {
  $mess = str_pad(' '. $iniFile .' ', 65, '-', STR_PAD_BOTH) . PHP_EOL;
  $settings = array('version', 'stability', 'date');
  foreach ($settings as $setting) {
    if (isset($iniVars[$setting])) {
      $mess .= $setting . ' = ' . $iniVars[$setting] . PHP_EOL;
    };
  };
  $sections = array('Cache', 'Database', 'Logging');
  $hidden = array('username', 'password');
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
  $mess .= str_pad(' End config file ', 65, '-', STR_PAD_BOTH);
  trigger_error($mess, E_USER_NOTICE);
};// if defined YAPEAL_DEBUG ...
/* **************************************************************************
 * Cache section
 * **************************************************************************/
$settings = array('cache_output', 'cache_xml');
foreach ($settings as $setting) {
  // Set to section value if it exists.
  if (isset($iniVars['Cache'][$setting])) {
    $constant = 'YAPEAL_' . strtoupper($setting);
    define($constant, $iniVars['Cache'][$setting]);
  } else {
    trigger_error($req1 . $setting . $req2, E_USER_ERROR);
  };// else isset $iniVars...
};// foreach $settings ...
if (YAPEAL_CACHE_XML == TRUE &&
  (YAPEAL_CACHE_OUTPUT == 'file' || YAPEAL_CACHE_OUTPUT == 'both')) {
  if (!is_writable(YAPEAL_CACHE)) {
    trigger_error(YAPEAL_CACHE . ' is not writeable', E_USER_ERROR);
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
};// if YAPEAL_CACHE_XML == TRUE && ...
/* **************************************************************************
 * Datebase section
 * **************************************************************************/
$settings = array('database', 'driver', 'host', 'suffix', 'table_prefix',
  'username', 'password');
$data = array();
foreach ($settings as $setting) {
  if (isset($iniVars['Database'][$setting])) {
    $data[$setting] = $iniVars['Database'][$setting];
  } else {
    $mess = $req1 . $setting . $req2 . ' section [Database].';
    trigger_error($mess, E_USER_ERROR);
  };
};// foreach $settings ...
// Extract the required fields from array.
extract($data);
// Define some constants from vars.
/**
 * Defines the database Name.
 */
define('YAPEAL_DB', $database);
/**
 * Defines the DSN used for ADOdb connection.
 */
define('YAPEAL_DSN',
  $driver . $username . ':' . $password . '@' . $host . '/' . $database . $suffix);
/**
 * Defines the table prefix used for all Yapeal tables.
 */
define('YAPEAL_TABLE_PREFIX', $table_prefix);
?>
