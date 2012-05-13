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
  $mess = basename(__FILE__)
    . ' must be included it can not be ran directly.' . PHP_EOL;
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  };
  fwrite(STDERR, $mess);
  exit(1);
};
// Define short name for directory separator which always uses '/'.
if (!defined('DS')) {
  /**
   * Define short name for directory separator which always uses unix '/'.
   * @ignore
   */
  define('DS', '/');
};
if (!defined('YAPEAL_INC')) {
  // Used to over come path issues caused by how script is ran on server.
  $dir = str_replace('\\', DS, realpath(dirname(__FILE__)));
  /**
   * Since this file has to be in the 'inc' directory we can set that path now.
   */
  define('YAPEAL_INC', $dir . DS);
};
if (!defined('YAPEAL_BASE')) {
  // Check if the base path for Yapeal has been set in the environment.
  $dir = @getenv('YAPEAL_BASE');
  if ($dir === FALSE) {
    $dir = str_replace('\\', DS, realpath(YAPEAL_INC . '..'));
  };
  /**
   * We know the 'base' directory should be just above 'inc' by default.
   */
  define('YAPEAL_BASE', $dir . DS);
};
if (!defined('YAPEAL_CACHE')) {
  /**
   * The 'cache' directory is normally just above base but can be moved in some
   * configurations.
   */
  define('YAPEAL_CACHE', YAPEAL_BASE . 'cache' . DS);
};
if (!defined('YAPEAL_CLASS')) {
  /**
   * The 'class' directory is normally just above base but can be moved in some
   * configurations.
   */
  define('YAPEAL_CLASS', YAPEAL_BASE . 'class' . DS);
};
if (!defined('YAPEAL_CONFIG')) {
  /**
   * The 'config' directory is normally just above base but can be moved in some
   * configurations.
   */
  define('YAPEAL_CONFIG', YAPEAL_BASE . 'config' . DS);
};
if (!defined('YAPEAL_EXT')) {
  /**
   * The 'ext' directory is normally just above base but can be moved in some
   * configurations.
   */
  define('YAPEAL_EXT', YAPEAL_BASE . 'ext' . DS);
};
if (!defined('YAPEAL_INTERFACE')) {
  /**
   * The 'interface' directory is normally just above base but can be moved in
   * some configurations.
   */
  define('YAPEAL_INTERFACE', YAPEAL_BASE . 'interface' . DS);
};
if (!defined('YAPEAL_LOG')) {
  /**
   * The 'log' directory is normally just above base but can be moved in some
   * configurations.
   */
  define('YAPEAL_LOG', YAPEAL_BASE . 'log' . DS);
};

