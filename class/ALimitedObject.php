<?php
/**
 * Contains ALimitedObject class.
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
/**
 * Abstract class for basic object with properties
 *
 * @package    Yapeal
 */
abstract class ALimitedObject {
  /**
   * List of column names and their ADOdb generic types.
   * @var array
   */
  protected $types;
  /**
   * Holds the current properties.
   * @var array
   */
  protected $properties;
  /**
   * Magic getter for fields in $properties array.
   *
   * @param string $name Name of field/column user wants.
   *
   * @return mixed Value of $name from $properties if it exists or NULL if not.
   *
   * @throws DomainException If $name not in $this->types throws DomainException.
   */
  public function __get($name) {
    if (!in_array($name, $this->types)) {
      $mess = 'Unknown field: ' . $name;
      throw new DomainException($mess, 1);
    };// if !in_array...
    if(isset($this->properties[$name])) {
      return $this->properties[$name];
    } else {
      return NULL;
    };// if isset...
  }// function __get
  /**
   * Magic isset for fields in properties array.
   * @param string $name Name of field being checked for.
   *
   * @return bool TRUE if is set and not empty else FALSE.
   */
  public function __isset($name) {
    return isset($this->properties[$name]);
  }// function __isset
  /**
   * Magic setter for fields in $properties array or if $value is an array adds
   * a new public property $name to class and assign $value to it.
   *
   * @param string $name Name of field or property being set or added.
   * @param mixed $value Value to be assigned.
   *
   * @return bool TRUE if $name already existed.
   *
   * @throws DomainException If $name not in $this->types throws DomainException.
   */
  public function __set($name, $value) {
    $ret = FALSE;
    // This will only happen if a new property that doesn't exist is being made.
    if (is_array($value)) {
      $this->$name = $value;
    };
    if (!in_array($name, $this->types)) {
      $mess = 'Unknown field: ' . $name;
      throw new DomainException($mess, 1);
    };// if !in_array...
    if(isset($this->properties[$name])) {
      $ret = TRUE;
    };// if isset...
    $this->properties[$name] = $value;
    return $ret;
  }// function __set
  /**
   * Used to unset fields of $properties array.
   *
   * @param string $name Name of field being unset.
   */
  public function __unset($name) {
    unset($this->properties[$name]);
  }// function __unset
}
?>
