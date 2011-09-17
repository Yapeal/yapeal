<?php
/**
 * Used to check installation meets basic requirements.
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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be ran in CLI.
 */
if (PHP_SAPI != 'cli') {
  header('HTTP/1.0 403 Forbidden', TRUE, 403);
  $mess = basename(__FILE__) . ' only works with CLI version of PHP but tried';
  $mess = ' to run it using ' . PHP_SAPI . ' instead';
  die($mess);
};
/**
 * @internal Only let this code be ran directly.
 */
$included = get_included_files();
if (count($included) > 1 || $included[0] != __FILE__) {
  $mess = basename(__FILE__) . ' must be called directly and can not be included';
  fwrite(STDERR, $mess . PHP_EOL);
  fwrite(STDOUT, 'error' . PHP_EOL);
  exit(1);
};
// Insure minimum version of PHP we need to run.
if (version_compare(PHP_VERSION, '5.2.4', '<')) {
  $mess = 'Need minimum of PHP 5.2.4 to use this software!' . PHP_EOL;
  fwrite(STDERR, $mess);
};
// Check for some required extensions
$required = array('curl', 'date', 'hash', 'mysqli', 'SimpleXML', 'SPL', 'xmlreader');
$exts = get_loaded_extensions();
$missing = array_diff($required, $exts);
if (count($missing) > 0) {
  $mess = 'The required PHP extensions: ';
  $mess .= implode(', ', $missing) . ' are missing!' . PHP_EOL;
  fwrite(STDERR, $mess);
};
// Check on cURL version and features.
$cv = curl_version();
if (version_compare($cv['version'], '7.15.0', '<')) {
  $mess = 'Need minimum of cURL 7.15.0 to use this software!' . PHP_EOL;
  fwrite(STDERR, $mess);
};
if (($cv['features'] & CURL_VERSION_SSL) != CURL_VERSION_SSL) {
  $mess = 'cURL was built without SSL please check it.';
  fwrite(STDERR, $mess);
};
// Check for minimum MySQL client.
if (mysqli_get_client_version() < 50000) {
  $mess = 'MySQL client version is older than 5.0.' . PHP_EOL;
  fwrite(STDERR, $mess);
};
/**
 * Define short name for directory separator which always uses unix '/'.
 * @ignore
 */
define('DS', '/');
// Used to over come path issues caused by how script is ran on server.
$baseDir = str_replace('\\', DS, realpath(dirname(__FILE__) . DS. '..') . DS);
// Get path constants so they can be used.
require_once $baseDir . DS . 'inc' . DS . 'common_paths.php';
$iniFile = YAPEAL_CONFIG . 'yapeal.ini';
if (!(is_readable($iniFile) && is_file($iniFile))) {
  $mess = 'The required ' . $iniFile . ' configuration file is missing!' . PHP_EOL;
  fwrite(STDERR, $mess);
  exit(2);
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
  fwrite(STDERR, $mess);
};
// Check for required error logging settings.
$settings = array('error_log', 'log_level', 'notice_log', 'strict_log',
  'warning_log');
$mess = '';
foreach ($settings as $setting) {
  if (!isset($iniVars['Logging'][$setting])) {
    $mess .= 'Missing required setting ' . $setting . ' in ' . $iniFile . PHP_EOL;
  };// if isset $iniVars...
};// foreach $settings ...
if (!empty($mess)) {
  fwrite(STDERR, $mess);
};
// Check writable paths
if (!is_writable(YAPEAL_LOG)) {
  $mess = YAPEAL_LOG . ' is not writeable.' . PHP_EOL;
  fwrite(STDERR, $mess);
};
if (!isset($iniVars['Cache']['cache_output'])) {
  $mess = 'Missing required setting "cache_output" in ' . $iniFile;
  $mess .= ' for section [Cache].' . PHP_EOL;
  fwrite(STDERR, $mess);
} else {
  // Check if cache directories exist and are writeable.
  if ($iniVars['Cache']['cache_output'] == 'file' ||
    $iniVars['Cache']['cache_output'] == 'both') {
    if (!is_writable(YAPEAL_CACHE)) {
      $mess = YAPEAL_CACHE . ' is not writeable.' . PHP_EOL;
      fwrite(STDERR, $mess);
    };
    $sections = array('account', 'char', 'corp', 'eve', 'map', 'server');
    foreach ($sections as $section) {
      if (!is_dir(YAPEAL_CACHE . $section)) {
        $mess = 'Missing required directory ' . YAPEAL_CACHE . $section . PHP_EOL;
        fwrite(STDERR, $mess);
      };
      if (!is_writable(YAPEAL_CACHE . $section)) {
        $mess = YAPEAL_CACHE . $section . ' is not writeable.' . PHP_EOL;
        fwrite(STDERR, $mess);
      };
    };// foreach $sections ...
  };// if $iniVars['Cache']['cache_output'] == 'file' || ...
};// else !isset $iniVars['Cache']['cache_output'] ...
// Check for required database settings.
$settings = array('database', 'driver', 'host', 'suffix', 'table_prefix',
  'username', 'password');
$mess = '';
foreach ($settings as $setting) {
  if (!isset($iniVars['Database'][$setting])) {
    $mess .= 'Missing required setting ' . $setting . ' in ' . $iniFile . PHP_EOL;
  };
};// foreach $settings ...
if (!empty($mess)) {
  fwrite(STDERR, $mess);
};
if (!isset($iniVars['application_agent'])) {
  $mess = $iniFile . ' is outdated and "application_agent" is not set';
  fwrite(STDERR, $mess);
};// if isset $iniVars['application_agent'] ...
if (isset($iniVars['registered_mode'])) {
  $mode = $iniVars['registered_mode'];
  if (!in_array($mode, array('ignored','optional','required'))) {
    $mess = $iniFile . ' had unknown value ' . $mode . ' for "registered_mode"';
    fwrite(STDERR, $mess);
  };
} else {
  $mess = $iniFile . ' is outdated and "registered_mode" is not set';
  fwrite(STDERR, $mess);
};
?>
