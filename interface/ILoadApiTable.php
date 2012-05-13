<?php
/**
 * Interface for Api database tables.
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

/**
 * Interface for loading (selecting) Eve API from a database table.
 *
 * @package Yapeal
 * @subpackage Api
 */
interface ILoadApiTable {
  /**
   * Used to load an item by ID from database.
   *
   * @param mixed $item ID of an item to load. Can be an integer for normal IDs
   * or string for big integer IDs
   * @param string $field column name to use in where clause.
   *
   * @return array Returns an array containing item or NULL if item not found.
   */
  function apiLoadByID($item = NULL, $field = NULL);
  /**
   * Used to load a named item from database.
   *
   * @param string $item Name of an item to load.
   * @param string $field column name to use in where clause.
   *
   * @return array Returns an array containing item or NULL if item not found.
   */
  function apiLoadByName($item = NULL, $field = NULL);
}

