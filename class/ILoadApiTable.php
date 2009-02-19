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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
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
?>
