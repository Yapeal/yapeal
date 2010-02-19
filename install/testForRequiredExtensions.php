#!/usr/bin/php
<?php
/**
 * Contains code used to test for required PHP extensions.
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
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 * @since      revision ###
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
// Strip any quotes
$replace = array("'", '"');
for ($i = 1; $i < $argc; ++$i) {
  $argv[$i] = str_replace($replace, '', $argv[$i]);
};
if ($argc == 2) {
  $required = explode(' ', $argv[1]);
} else {
  $required = array('curl', 'date', 'mysqli', 'SimpleXML', 'SPL');
};
$exts = get_loaded_extensions();
$ret = 'false';
// Check for some required extensions
$missing = array_diff($required, $exts);
if (count($missing) > 0) {
  $mess = implode(', ', $missing) . PHP_EOL;
  fwrite(STDERR, $mess);
  $ret = 'false';
} else {
  $mess = 'All required PHP extensions were found';
  fwrite(STDERR, $mess);
  $ret = 'true';
};
fwrite(STDOUT, $ret);
exit(0);
?>
