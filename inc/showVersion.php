<?php
/**
 * Contains showVersion function.
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
 * @copyright  Copyright (c) 2008-2012, Michael Cummings
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
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
  $mess = basename(__FILE__) . ' must be included it can not be ran directly';
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  };
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Function to show version information of script.
 *
 * @param string $file Name of script file.
 */
function showVersion($file) {
  $mess = basename($file) . ' ';
  $mess .= YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ')' . PHP_EOL . PHP_EOL;
  $mess .= 'Copyright (c) 2008-2012, Michael Cummings.' . PHP_EOL;
  $mess .= 'License LGPLv3+: GNU LGPL version 3 or later' . PHP_EOL;
  $mess .= ' <http://www.gnu.org/copyleft/lesser.html>.' . PHP_EOL;
  $mess .= 'See COPYING.md and COPYING-LESSER.md for more details.' . PHP_EOL;
  $mess .= 'This program comes with ABSOLUTELY NO WARRANTY.' . PHP_EOL . PHP_EOL;
  fwrite(STDOUT, $mess);
}// function showVersion

