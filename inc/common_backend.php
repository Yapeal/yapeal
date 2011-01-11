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
// Used to over come path issues caused by how script is ran.
$incDir = str_replace('\\', '/', realpath(dirname(__FILE__)));
chdir($incDir);
// Set the default timezone to GMT.
date_default_timezone_set('GMT');
// Define short name for directory separator which always uses '/'.
if (!defined('DS')) {
  /**
   * @ignore
   */
  define('DS', '/');
};
// Set some basic common settings so we know we'll get to see any errors etc.
error_reporting(E_ALL);
ini_set('ignore_repeated_errors', 0);
ini_set('ignore_repeated_source', 0);
ini_set('html_errors', 0);
ini_set('display_errors', 1);
ini_set('error_log', NULL);
ini_set('log_errors', 0);
ini_set('track_errors', 0);
// Grab revision settings
$path = str_replace('\\', '/', realpath($incDir . DS . '..' . DS . 'revision.php'));
require_once $path;
// Get path constants so they can be used.
require_once $incDir . DS . 'common_paths.php';
// Set a constant for location of configuration file.
if (!isset($iniFile)) {
  // Default assumes that this file and yapeal.ini file are in 'neighboring'
  // directories.
  $iniFile = YAPEAL_CONFIG . 'yapeal.ini';
};
if (!($iniFile && is_readable($iniFile) && is_file($iniFile))) {
  $mess = 'The required ' . $iniFile . ' configuration file is missing';
  trigger_error($mess, E_USER_ERROR);
  exit(1);
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
// Grab the info from ini file again now that our constants are defined.
$iniVars = parse_ini_file($iniFile, TRUE);
$settings = array('error_log', 'log_level', 'notice_log', 'strict_log',
  'warning_log');
foreach ($settings as $setting) {
  if (!isset($iniVars['Logging'][$setting])) {
    trigger_error($req1 . $setting . $req2, E_USER_ERROR);
  };// else isset $iniVars...
};// foreach $settings ...
if (!defined('YAPEAL_LOG_LEVEL')) {
  define('YAPEAL_LOG_LEVEL', $iniVars['Logging']['log_level']);
};
if (!defined('YAPEAL_ERROR_LOG')) {
  define('YAPEAL_ERROR_LOG', YAPEAL_LOG . $iniVars['Logging']['error_log']);
};
if (!defined('YAPEAL_NOTICE_LOG')) {
  define('YAPEAL_NOTICE_LOG', YAPEAL_LOG . $iniVars['Logging']['notice_log']);
};
if (!defined('YAPEAL_STRICT_LOG')) {
  define('YAPEAL_STRICT_LOG', YAPEAL_LOG . $iniVars['Logging']['strict_log']);
};
if (!defined('YAPEAL_WARNING_LOG')) {
  define('YAPEAL_WARNING_LOG', YAPEAL_LOG . $iniVars['Logging']['warning_log']);
};
/* **************************************************************************
 * Change over to our custom error and exception code
 * **************************************************************************/
// Change some error logging settings.
ini_set('error_log', YAPEAL_ERROR_LOG);
ini_set('log_errors', 1);
// Start using custom error handler.
set_error_handler(array('YapealErrorHandler', 'handle'));
error_reporting(YAPEAL_LOG_LEVEL);
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
 * Cache section
 * **************************************************************************/
$settings = array('cache_output');
foreach ($settings as $setting) {
  if (!isset($iniVars['Cache'][$setting])) {
    $mess = $req1 . $setting . $req2 . ' section [Cache].';
    trigger_error($mess, E_USER_ERROR);
  };
};// foreach $settings ...
if (!defined('YAPEAL_CACHE_OUTPUT')) {
  /**
   * Used to decide how API XML should be cached.
   */
  define('YAPEAL_CACHE_OUTPUT', $iniVars['Cache']['cache_output']);
};
/* **************************************************************************
 * Curl section
 * **************************************************************************/
if (!defined('YAPEAL_CURL_TIMEOUT')) {
  define('YAPEAL_CURL_TIMEOUT', 45);
};
/* **************************************************************************
 * Database section
 * **************************************************************************/
$settings = array('database', 'driver', 'host', 'suffix', 'table_prefix',
  'username', 'password');
$data = array();
foreach ($settings as $setting) {
  if (!isset($iniVars['Database'][$setting])) {
    $mess = $req1 . $setting . $req2 . ' section [Database].';
    trigger_error($mess, E_USER_ERROR);
  };
};// foreach $settings ...
if (!defined('YAPEAL_DSN')) {
  // Put all the pieces of the ADOdb DSN together.
  $dsn = $iniVars['Database']['driver'] . $iniVars['Database']['username'] . ':';
  $dsn .= $iniVars['Database']['password'] . '@' . $iniVars['Database']['host'];
  $dsn .= '/' . $iniVars['Database']['database'] . $iniVars['Database']['suffix'];
  /**
   * Defines the DSN used for ADOdb connection.
   */
  define('YAPEAL_DSN', $dsn);
};
if (!defined('YAPEAL_TABLE_PREFIX')) {
  /**
   * Defines the table prefix used for all Yapeal tables.
   */
  define('YAPEAL_TABLE_PREFIX', $iniVars['Database']['table_prefix']);
};
/* **************************************************************************
 * General section
 * **************************************************************************/
if (!isset($iniVars['application_agent'])) {
  $mess = $iniFile . ' is outdated and "application_agent" is not set';
  trigger_error($mess, E_USER_NOTICE);
  $iniVars['application_agent'] = '';
};// if isset $iniVars['application_agent'] ...
if (!defined('YAPEAL_APPLICATION_AGENT')) {
  $curl = curl_version();
  $user_agent = $iniVars['application_agent'];
  $user_agent .= ' Yapeal/'. YAPEAL_VERSION . ' ' . YAPEAL_STABILITY;
  $user_agent .= ' (' . PHP_OS . ' ' . php_uname('m') . ')';
  $user_agent .= ' libcurl/' . $curl['version'];
  $user_agent = trim($user_agent);
  /**
   * Used as default user agent in network connections.
   */
  define('YAPEAL_APPLICATION_AGENT', $user_agent);
};
?>
