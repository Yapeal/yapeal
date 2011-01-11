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
 * @copyright Copyright (c) 2008-2009, Michael Cummings
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
// Only CLI.
if (PHP_SAPI != 'cli') {
  $mess = 'This script will only work with CLI version of PHP';
  die($mess);
};
/**
 * @internal Only let this code be ran directly.
 */
if (basename(__FILE__) != basename($_SERVER['PHP_SELF'])) {
  $mess = 'Including of ' . $argv[0] . ' is not allowed' . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(1);
};
$ret = 'error';
if ($argc < 5) {
  $mess = 'Hostname Username Password Database are required in ' .$argv[0] . PHP_EOL;
  $mess .= 'Privilege(s) is optional' . PHP_EOL;
  $mess .= 'If Privilege(s) is a list it needs to be inside quotes' . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, $ret);
  exit(1);
};
if ($argc = 5) {
  $privs = array('alter', 'create', 'delete', 'drop', 'index', 'insert',
    'select', 'update');
} else {
  $privs = explode(' ', $argv[5]);
};
// Strip any quotes
$replace = array("'", '"');
for ($i = 1; $i < $argc; ++$i) {
  $argv[$i] = str_replace($replace, '', $argv[$i]);
};
$mysqli = @new mysqli($argv[1], $argv[2], $argv[3]);
if ($mysqli->connect_error || mysqli_connect_error()) {
  $mess = 'Connection error (' . mysqli_connect_errno() . ') ' .
    mysqli_connect_error() . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, $ret);
  exit(3);
};
$split = array();
$sql = 'show grants';
if ($result = $mysqli->query($sql)) {
  while ($row = $result->fetch_row()) {
    $dbPos = strpos($row[0], '`' . $argv[4] . '`');
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
  $missing = array_diff($privs, $split);
  if (count($missing) > 0 && FALSE === array_search('allprivileges', $split)) {
    $mess = $argv[2] . ' lacks the needed privileges: ' .
      implode(', ', $missing) . ' on the ' . $argv[4] . ' database';
    fwrite(STDERR, $mess);
    $ret = 'false';
  } else {
    $ret = 'true';
  };
  $result->free();
};
$mysqli->close();
fwrite(STDOUT, $ret);
exit(0);
?>
