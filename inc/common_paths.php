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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
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
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
// Define short name for directory separator which always uses '/'.
if (!defined('DS')) {
  /**
   * Define short name for directory separator which always uses unix '/'.
   * @ignore
   */
  define('DS', '/');
};
// Used to over come path issues caused by how script is ran on server.
$incDir = str_replace('\\', DS, realpath(dirname(__FILE__)));
/**
 * Since this file has to be in the 'inc' directory we can set that path now.
 */
if (!defined('YAPEAL_INC')) {
  define('YAPEAL_INC', $incDir . DS);
};
/**
 * We know the 'base' directory has to be just above 'inc'.
 */
if (!defined('YAPEAL_BASE')) {
  $dir = str_replace('\\', DS, realpath(YAPEAL_INC . '..'));
  define('YAPEAL_BASE', $dir . DS);
};
/**
 * The 'cache' directory is normally a neighbor to 'inc' but can be moved in some
 * configurations.
 */
if (!defined('YAPEAL_CACHE')) {
  define('YAPEAL_CACHE', YAPEAL_BASE . 'cache' . DS);
};
/**
 * The 'class' directory is a neighbor to us.
 */
if (!defined('YAPEAL_CLASS')) {
  define('YAPEAL_CLASS', YAPEAL_BASE . 'class' . DS);
};
/**
 * The 'config' directory is normally a neighbor to 'inc' but can be moved in some
 * configurations.
 */
if (!defined('YAPEAL_CONFIG')) {
  define('YAPEAL_CONFIG', YAPEAL_BASE . 'config' . DS);
};
/**
 * The 'ext' directory is normally a neighbor to 'inc' but can be moved in some
 * configurations.
 */
if (!defined('YAPEAL_EXT')) {
  define('YAPEAL_EXT', YAPEAL_BASE . 'ext' . DS);
};
/**
 * The 'install' directory is a neighbor to 'inc'.
 */
if (!defined('YAPEAL_INSTALL')) {
  define('YAPEAL_INSTALL', YAPEAL_BASE . 'install' . DS);
};
/**
 * The 'log' directory is normally a neighbor to 'inc' but can be moved in
 * some configurations.
 */
if (!defined('YAPEAL_LOG')) {
  define('YAPEAL_LOG', YAPEAL_BASE . 'log' . DS);
};
/**
 * The 'pics' directory is normally a neighbor to 'inc'.
 */
if (!defined('YAPEAL_PICS')) {
  define('YAPEAL_PICS', YAPEAL_BASE . 'pics' . DS);
};
/* **************************************************************************
 * Specific Extension Library Paths
 * **************************************************************************/
$exts = new DirectoryIterator(YAPEAL_EXT);
foreach ($exts as $ext) {
  if ($ext->isDir()) {
    $constant = 'YAPEAL_' . strtoupper($ext);
    if (!defined($constant)) {
      $path = str_replace('\\', DS, realpath($ext->getPathname()));
     define($constant, $path . DS);
    };// if !defined...
  };// if $ext->isDir() ...
};// foreach $exts ...
?>
