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
 * Since this file has to be in the 'inc' directory we can set that path now and
 * all the other paths are relative to it.
 *
 */
define('YAPEAL_INC', $incDir . DS);
/* **************************************************************************
* Paths
* **************************************************************************/
$settings = array(
  'BASE' => '..' . DS,
  'CACHE' => '..' . DS . 'cache' . DS,
  'CLASS' => '..' . DS . 'class' . DS,
  'CONFIG' => '..' . DS . 'config' . DS,
  'EXT' => '..' . DS . 'ext' . DS,
  'INSTALL' => '..' . DS . 'install' . DS
);
foreach ($settings as $k => $v) {
  $realpath = realpath($incDir . DS . $v);
  if ($realpath && is_dir($realpath)) {
    define('YAPEAL_' . $k, $realpath . DS);
  } else {
    $mess = 'Nonexistent directory defined for YAPEAL_' . $k . ' constant';
    trigger_error($mess, E_USER_ERROR);
  };
};// foreach $settings ...
/* **************************************************************************
* Specific Extension Library Paths
* **************************************************************************/
$exts = new DirectoryIterator(YAPEAL_EXT);
foreach ($exts as $ext) {
  if ($ext->isDir()) {
    define('YAPEAL_' . strtoupper($ext), $ext->getPathname() . DS);
  };
};// foreach $exts ...
?>
