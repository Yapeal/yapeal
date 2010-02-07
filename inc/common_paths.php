<?php
/**
 * Builds and checks the path constants.
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
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
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
// Used to over come path issues caused by how script is ran on server.
$incDir = realpath(dirname(__FILE__));
// Define shortened name for DIRECTORY_SEPARATOR
if (!defined('DS')) {
  define('DS', DIRECTORY_SEPARATOR);
};
/**
 * Since this file has to be in the 'inc' directory we can set that path now.
 */
define('YAPEAL_INC', $incDir . DS);
/**
 * We know the 'base' directory has to be just above 'inc'.
 */
$dir = realpath(YAPEAL_INC . '..');
define('YAPEAL_BASE', $dir . DS);
/**
 * The 'cache' directory is normally a neighbor to 'inc' but can be moved in some
 * configurations.
 */
define('YAPEAL_CACHE', YAPEAL_BASE . 'cache' . DS);
/**
 * The 'class' directory is a neighbor to us.
 */
define('YAPEAL_CLASS', YAPEAL_BASE . 'class' . DS);
/**
 * The 'config' directory is normally a neighbor to 'inc' but can be moved in some
 * configurations.
 */
define('YAPEAL_CONFIG', YAPEAL_BASE . 'config' . DS);
/**
 * The 'ext' directory is normally a neighbor to 'inc' but can be moved in some
 * configurations.
 */
define('YAPEAL_EXT', YAPEAL_BASE . 'ext' . DS);
/**
 * The 'install' directory is a neighbor to 'inc'.
 */
define('YAPEAL_INSTALL', YAPEAL_BASE . 'install' . DS);
/**
 * The 'log' directory is normally a neighbor to 'inc' but can be moved in
 * some configurations.
 */
define('YAPEAL_LOG', YAPEAL_BASE . 'log' . DS);
/**
 * The 'pics' directory is normally a neighbor to 'inc'.
 */
define('YAPEAL_PICS', YAPEAL_BASE . 'pics' . DS);
/* **************************************************************************
 * Specific Extension Library Paths
 * **************************************************************************/
$exts = new DirectoryIterator(YAPEAL_EXT);
foreach ($exts as $ext) {
  if ($ext->isDir()) {
    $constant = 'YAPEAL_' . strtoupper($ext);
    define($constant, $ext->getPathname() . DS);
  };
};// foreach $exts ...
?>
