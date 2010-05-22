#!/usr/bin/php -Cq
<?php
/**
 * Contains code used to test if MySQL is installed.
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
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
// Make CGI work like CLI.
if (PHP_SAPI != 'cli') {
  ini_set('implicit_flush', '1');
  ini_set('register_argc_argv', '1');
  defined('STDIN') || define('STDIN', fopen('php://stdin', 'r'));
  defined('STDOUT') || define('STDOUT', fopen('php://stdout', 'w'));
  defined('STDERR') || define('STDERR', fopen('php://stderr', 'w'));
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
if ($argc < 4) {
  $mess = 'Host, Username, Password required in ' . $argv[0] . PHP_EOL;
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
$mysqli->close();
$ret = 'true';
fwrite(STDOUT, $ret);
exit(0);
?>
