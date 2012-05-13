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
 * @copyright Copyright (c) 2008-2012, Michael Cummings
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
  $mess = basename(__FILE__) . ' only works with CLI version of PHP but tried'
    . ' to run it using ' . PHP_SAPI . ' instead.' . PHP_EOL;
  die($mess);
};
/**
 * @internal Only let this code be ran directly.
 */
$included = get_included_files();
if (count($included) > 1 || $included[0] != __FILE__) {
  $mess = basename(__FILE__)
    . ' must be called directly and can not be included.' . PHP_EOL;
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Define short name for directory separator which always uses unix '/'.
 * @ignore
 */
define('DS', '/');
// Check if the base path for Yapeal has been set in the environment.
$dir = @getenv('YAPEAL_BASE');
if ($dir === FALSE) {
  // Used to overcome path issues caused by how script is ran.
  $dir = str_replace('\\', DS, realpath(dirname(__FILE__) . DS. '..')) . DS;
};
// Get path constants so they can be used.
require_once $dir . 'inc' . DS . 'common_paths.php';
require_once YAPEAL_BASE . 'revision.php';
require_once YAPEAL_INC . 'parseCommandLineOptions.php';
require_once YAPEAL_INC . 'getSettingsFromIniFile.php';
require_once YAPEAL_INC . 'usage.php';
require_once YAPEAL_INC . 'showVersion.php';
$shortOpts = array('c:', 'd:', 'p:', 's:', 'u:');
$longOpts = array('config:', 'database:', 'password:', 'privileges:',
  'server:', 'username:');
$options = parseCommandLineOptions($shortOpts, $longOpts);
$exit = FALSE;
if (isset($options['help'])) {
  usage(__FILE__, $shortOpts, $longOpts);
  exit(0);
};
if (isset($options['version'])) {
  showVersion(__FILE__);
  exit(0);
};
if (!empty($options['config'])) {
  $section = getSettingsFromIniFile($options['config'], 'Database');
  unset($options['config']);
} else {
  $section = getSettingsFromIniFile(NULL, 'Database');
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
if (empty($options['privileges'])) {
  $privs = array('alter', 'create', 'delete', 'drop', 'index', 'insert',
    'select', 'update');
} else {
  $privs = explode(' ', $options['privileges']);
};
$split = array();
// May need to do a little escaping on database name.
$database = str_replace('_', '\\_', $options['database']);
$sql = 'show grants';
$result = $mysqli->query($sql);
if ($result) {
  while ($row = $result->fetch_row()) {
    $dbPos = strpos($row[0], '`' . $database . '`');
    // If not the right table continue.
    if (FALSE !== $dbPos) {
      // Trim grant off the front.
      $split = str_replace('GRANT ', '', $row[0]);
      // Find the end part and strip it off.
      $end = substr($split, strpos($split, ' ON '));
      $split = str_replace($end, '', $split);
      // Delete the spaces.
      $split = str_replace(' ', '', $split);
      // If $split isn't empty there are privs.
      if (!empty($split)) {
        $split = explode(',' , strtolower($split));
      };// if !empty $split ...
    } else {
      continue;
    };// else FALSE !== $dbPos ...
  };// while $row ...
  $result->free();
  $mysqli->close();
  $missing = array_diff($privs, $split);
  if (count($missing) > 0 && FALSE === array_search('allprivileges', $split)) {
    $mess = $options['username'] . ' lacks the needed privileges: ';
    $mess .= implode(', ', $missing) . ' on the ' . $options['database'];
    $mess .= ' database.' . PHP_EOL;
    fwrite(STDERR, $mess);
    exit(2);
  };
};
$mess = $options['username'] . ' has the needed privileges on the ';
$mess .= $options['database'] . ' database.' . PHP_EOL;
fwrite(STDOUT, $mess);
exit(0);

