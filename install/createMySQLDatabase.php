#!/usr/bin/php -Cq
<?php
/**
 * Contains code used to test if user has privileges on a MySQL database.
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
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2011, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 * @subpackage Install
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
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error' . PHP_EOL);
  exit(1);
};
/**
 * Define short name for directory separator which always uses unix '/'.
 * @ignore
 */
define('DS', '/');
// Used to over come path issues caused by how script is ran on server.
$baseDir = str_replace('\\', DS, realpath(dirname(__FILE__) . DS. '..')) . DS;
// Pull in Yapeal revision constants.
require_once $baseDir . 'revision.php';
// Get path constants so they can be used.
require_once $baseDir . 'inc' . DS . 'common_paths.php';
// If function getopts available get any command line parameters.
if (function_exists('getopt')) {
  $options = parseCommandLineOptions($argv);
};// if function_exists getopt ...
if (!empty($options['config'])) {
  $section = getSettingsFromIniFileForDatabaseSection($options['config']);
  unset($options['config']);
} else {
  $section = getSettingsFromIniFileForDatabaseSection();
};
// Merge the configuration file settings with ones from command line.
// Settings from command line will override any from file.
$options = array_merge($section, $options);
$required = array('database', 'host', 'password', 'username');
$mess = '';
foreach ($required as $setting) {
  if (empty($options[$setting])) {
    $mess .= 'Missing required setting ' . $setting;
    $mess .= ' in section [Database].' . PHP_EOL;
  };
};// foreach $required ...
if (!empty($mess)) {
  fwrite(STDERR, $mess);
  exit(2);
};
$mysqli = @new mysqli($options['host'], $options['username'], $options['password']);
if ($mysqli->connect_error || mysqli_connect_error()) {
  $mess = 'Connection error (' . mysqli_connect_errno() . ') ' .
    mysqli_connect_error() . PHP_EOL;
  fwrite(STDERR, $mess);
  exit(2);
};
$sql = 'create database `' . $mysqli->real_escape_string($options['database']);
$sql .= '` collate = utf8_unicode_ci';
if ($mysqli->query($sql) === FALSE) {
  $mess = 'Database ' . $options['database'] . ' could not be created.' . PHP_EOL;
  fwrite(STDERR, $mess);
  exit(2);
};
$mess = 'Database ' . $options['database'] . ' created successfully!' . PHP_EOL;
fwrite(STDOUT, $mess);
exit(0);
/**
 * Function used to get 'ini' configuration file.
 *
 * @param string $file Path and name of the ini file to get.
 *
 * @return array Returns list of settings from file.
 */
function getSettingsFromIniFileForDatabaseSection($file = NULL) {
  // Check if given custom configuration file.
  if (empty($file) || !is_string($file)) {
    // Default assumes that this file and yapeal.ini file are in 'neighboring'
    // directories.
    $file = YAPEAL_CONFIG . 'yapeal.ini';
  } else {
    $mess = 'Using custom configuration file ' . $file . PHP_EOL;
    fwrite(STDOUT, $mess);
  };
  if (!(is_readable($file) && is_file($file))) {
    $mess = 'The ' . $file . ' configuration file is missing!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  // Grab the info from ini file.
  $settings = parse_ini_file($file, TRUE);
  if (empty($settings)) {
    $mess = 'The ' . $file . ' configuration file contains no settings!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  if (empty($settings['Database'])) {
    $mess = 'No settings for [Database] section found in configuration file!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  return $settings['Database'];
}// function getSettingsFromIniFileForDatabaseSection
/**
 * Function used to parser command line options.
 *
 * @param array $argv Array of arguments passed to script.
 *
 * @return mixed Returns settings list.
 */
function parseCommandLineOptions($argv) {
  $shortOpts = 'c:d:hp:s:u:V';
  if (version_compare(PHP_VERSION, '5.3.0', '>=')
    || strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
    $longOpts = array('config:', 'database:', 'help', 'password:',
      'server:', 'username:', 'version');
    $options = getopt($shortOpts, $longOpts);
  } else {
    $options = getopt($shortOpts);
  };
  $settings = array();
  if (empty($options)) {
    return $settings;
  };
  $exit = FALSE;
  foreach ($options as $opt => $value) {
    switch ($opt) {
      case 'c':
      case 'config':
        if (is_array($value)) {
          // If option is used multiple times use the last value.
          $value = $value[count($value) - 1];
        };
        $settings['config'] = (string)$value;
        break;
      case 'd':
      case 'database':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['database'] = (string)$value;
        break;
      case 'p':
      case 'password':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['password'] = (string)$value;
        break;
      case 's':
      case 'server':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['host'] = (string)$value;
        break;
      case 'u':
      case 'username':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['username'] = (string)$value;
        break;
      case 'h':
      case 'help':
        usage($argv);
        // Fall through is intentional.
      case 'V':
      case 'version':
        $mess = basename($argv[0]);
        if (YAPEAL_VERSION != 'svnversion') {
          $mess .= ' ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
          $mess .= YAPEAL_DATE . PHP_EOL . PHP_EOL;
        } else {
          $rev = str_replace(array('$', 'Rev:'), '', '$Rev$');
          $date = str_replace(array('$', 'Date:'), '', '$Date$');
          $mess .= $rev . '(svn)' . $date . PHP_EOL;
        };
        $mess .= 'Copyright (c) 2008-2011, Michael Cummings.' . PHP_EOL;
        $mess .= 'License LGPLv3+: GNU LGPL version 3 or later' . PHP_EOL;
        $mess .= ' <http://www.gnu.org/copyleft/lesser.html>.' . PHP_EOL;
        $mess .= 'See COPYING and COPYING-LESSER for more details.' . PHP_EOL;
        $mess .= 'This program comes with ABSOLUTELY NO WARRANTY.' . PHP_EOL . PHP_EOL;
        fwrite(STDOUT, $mess);
        $exit = TRUE;
        break;
    };// switch $opt
  };// foreach $options...
  if ($exit == TRUE) {
    exit;
  };
  return $settings;
};// function parseCommandLineOptions
/**
 * Function use to show the usage message on command line.
 *
 * @param array $argv Array of arguments passed to script.
 */
function usage($argv) {
  $ragLine = 76;
  $cutLine = 80;
  $mess = PHP_EOL . 'Usage: ' . basename($argv[0]);
  $mess .= ' [OPTION]...' . PHP_EOL . PHP_EOL;
  $desc = 'The script reads database settings from [Database] section of the';
  $desc .= ' configuration file, either the default one in';
  $desc .= ' /Where/Installed/Yapeal/config/yapeal.ini or the custom one from';
  $desc .= ' -c OPTION. The other OPTIONs -d, -p, -s, -u can be used to';
  $desc .= ' override any settings found in the configuration file. If no';
  $desc .= ' configuration file is found, either default or custom, or some';
  $desc .= ' of the settings are missing from it the OPTIONs -d, -p, -s, -u';
  $desc .= ' become required. For example the configuration file has all but';
  $desc .= ' the "password" setting. The -p option will be required on the';
  $desc .= ' command line.';
  // Make text ragged right with forced word wrap at 80 characters.
  $desc = wordwrap($desc, $ragLine, PHP_EOL);
  $desc = wordwrap($desc, $cutLine, PHP_EOL, TRUE);
  $mess .= $desc . PHP_EOL . PHP_EOL;
  $desc = 'Short version of OPTIONs have the same value requirements of the';
  $desc .= ' corresponding long ones. For all OPTIONs if they are';
  $desc .= ' used more than once only the last value will be used.';
  $desc = wordwrap($desc, $ragLine, PHP_EOL);
  $desc = wordwrap($desc, $cutLine, PHP_EOL, TRUE);
  $mess .= $desc . PHP_EOL . PHP_EOL;
  $mess .= 'OPTIONs:' . PHP_EOL;
  $options = array();
  $options[] = array('pp' => '-c, --config=FILE', 'desc' =>
    'Read configuration from FILE. This is an optional setting to allow using a'
    . ' custom configuration file. File must be in "ini" format. Defaults to'
    . ' /Where/Installed/Yapeal/config/yapeal.ini.');
  $options[] = array('pp' => '-d, --database=DB', 'desc' =>
    'DB is the name of the database to create.');
  $options[] = array('pp' => '-h, --help', 'desc' => 'Show this help.');
  $options[] = array('pp' => '-p, password=SECRET', 'desc' =>
    'SECRET is the password for the database server.');
  $options[] = array('pp' => '-s, --server=LOCALHOST', 'desc' =>
    'LOCALHOST is the database server name to use.');
  $options[] = array('pp' => '-u, --username=USER', 'desc' =>
    'USER is the user name for the database server.');
  $options[] = array('pp' => '-V, --version', 'desc' =>
    'Show version and licensing information.');
  $width = 0;
  foreach ($options as $option) {
    // find widest parameter in list.
    if (strlen($option['pp']) > $width) {
      $width = strlen($option['pp']);
    };
  };// foreach $options ...
  // Give another six spaces for padding.
  $width += 6;
  foreach ($options as $option) {
    $desc = str_pad('  ' . $option['pp'], $width);
    $desc .= $option['desc'];
    // Make text ragged right with forced word wrap at 80 characters.
    $desc = wordwrap($desc, $ragLine, PHP_EOL);
    $desc = wordwrap($desc, $cutLine, PHP_EOL, TRUE);
    $mess .= $desc . PHP_EOL . PHP_EOL;
  };// foreach $options ...
  fwrite(STDOUT, $mess);
};// function usage
?>
