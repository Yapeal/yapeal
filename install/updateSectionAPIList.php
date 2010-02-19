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
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @subpackage Setup
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
 * @internal Only let this code be ran directly.
 */
if (basename(__FILE__) != basename($_SERVER['PHP_SELF'])) {
  exit();
};
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// Define shortened name for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);
// Pull in Yapeal revision constants.
$path = $dir . DS . '..' . DS . 'revision.php';
require_once realpath($path);
// Move down and over to 'inc' directory to read common_backend.php
$path = $dir . DS . '..' . DS . 'inc' . DS . 'common_backend.php';
require_once realpath($path);
if ($argc < 3) {
  $mess = 'Section, APIList are required in ';
  $mess .= $argv[0] . PHP_EOL;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error');
  exit(1);
};
// Strip any quotes
$replace = array("'", '"');
for ($i = 1; $i < $argc; ++$i) {
  $argv[$i] = str_replace($replace, '', $argv[$i]);
};
try {
  $section = new Sections($argv[1]);
  $section->activeAPI = $argv[2];
  if (TRUE == $section->store()) {
    fwrite(STDOUT, 'true');
  } else {
    fwrite(STDOUT, 'false');
  };
  exit(0);
} catch (Exception $e) {
  $mess = <<<MESS
EXCEPTION:
     Code: {$e->getCode()}
  Message: {$e->getMessage()}
     File: {$e->getFile()}
     Line: {$e->getLine()}
Backtrace:
{$e->getTraceAsString()}
MESS;
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'false');
  exit(2);
}
?>
