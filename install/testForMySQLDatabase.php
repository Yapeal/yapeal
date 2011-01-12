#!/usr/bin/php -Cq
<?php
/**
 * Contains code used to test if MySQL database exists.
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
$ret = 'false';
if ($argc != 5) {
  $mess = 'Hostname Username Password Database are required in ' . $argv[0] . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, $ret);
  exit(1);
};
// Strip any quotes
$replace = array("'", '"');
for ($i = 1; $i < $argc; ++$i) {
  $argv[$i] = str_replace($replace, '', $argv[$i]);
};
$mysqli = @new mysqli($argv[1], $argv[2], $argv[3], 'information_schema');
if ($mysqli->connect_error || mysqli_connect_error()) {
  $mess = 'Connection error (' . mysqli_connect_errno() . ') ' .
    mysqli_connect_error() . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, $ret);
  exit(2);
};
$sql = 'show databases';
if ($result = $mysqli->query($sql)) {
  if ($mysqli->connect_error || mysqli_connect_error()) {
    $mess = 'Connection error (' . mysqli_connect_errno() . ') ' .
      mysqli_connect_error() . PHP_EOL;
    fwrite(STDERR, $mess);
    fwrite(STDOUT, $ret);
    exit(3);
  };
  $mess = $argv[4] . ' database does not exist' . PHP_EOL;
  while ($row = $result->fetch_row()) {
    if ((string)$row[0] == (string)$argv[4]) {
      $ret = 'true';
      $mess = $argv[4] . ' database does exist' . PHP_EOL;
    };
  };
  $result->free();
};
$mysqli->close();
fwrite(STDERR, $mess);
fwrite(STDOUT, $ret);
exit(0);
?>
