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
// Insure minimum version of PHP we need to run.
if (version_compare(PHP_VERSION, '5.2.4', '<')) {
  $mess = 'Need minimum of PHP 5.2.4 to use this software!';
  fwrite(STDERR, $mess);
};
// Check for some required extensions
$required = array('curl', 'date', 'hash', 'mysqli', 'SimpleXML', 'SPL', 'xmlreader');
$exts = get_loaded_extensions();
$missing = array_diff($required, $exts);
if (count($missing) > 0) {
  $mess = 'The required PHP extensions: ';
  $mess .= implode(', ', $missing) . ' are missing!';
  fwrite(STDERR, $mess);
};
define('DS', '/');
// Used to over come path issues caused by how script is ran on server.
$baseDir = str_replace('\\', '/', realpath('..' . DS . dirname(__FILE__)));
// Get path constants so they can be used.
require_once $baseDir . DS . 'inc' . DS . 'common_paths.php';
$iniFile = YAPEAL_CONFIG . 'yapeal.ini';
if (!(is_readable($iniFile) && is_file($iniFile))) {
  $mess = 'The required ' . $iniFile . ' configuration file is missing';
  fwrite(STDERR, $mess);
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
// Check writable paths
if (!is_writable(YAPEAL_LOG)) {
  $mess = YAPEAL_LOG . ' is not writeable';
  fwrite(STDERR, $mess);
};
if (!isset($iniVars['Cache']['cache_output'])) {
  $mess = 'Missing required setting "cache_output" in ' . $iniFile;
  $mess .= ' for section [Cache].';
  fwrite(STDERR, $mess);
} else {
  // Check if cache directories exist and are writeable.
  if ($iniVars['Cache']['cache_output'] == 'file' ||
    $iniVars['Cache']['cache_output'] == 'both') {
    if (!is_writable(YAPEAL_CACHE)) {
      $mess = YAPEAL_CACHE . ' is not writeable';
      fwrite(STDERR, $mess);
    };
    $sections = array('account', 'char', 'corp', 'eve', 'map', 'server');
    foreach ($sections as $section) {
      if (!is_dir(YAPEAL_CACHE . $section)) {
        $mess = 'Missing required directory ' . YAPEAL_CACHE . $section;
        fwrite(STDERR, $mess);
      };
      if (!is_writable(YAPEAL_CACHE . $section)) {
        $mess = YAPEAL_CACHE . $section . ' is not writeable';
        fwrite(STDERR, $mess);
      };
    };// foreach $sections ...
  };// if $iniVars['Cache']['cache_output'] == 'file' || ...
};// else !isset $iniVars['Cache']['cache_output'] ...
?>
