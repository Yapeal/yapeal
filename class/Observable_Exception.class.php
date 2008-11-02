<?php
/**
 * Observable_Exception class.
 *
 * LICENSE: This file is part of Yapeal.
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
 * @copyright Copyright (c) 2008, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename( __FILE__ )==basename($_SERVER['PHP_SELF'])) {
  exit();
};

/* *************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * *************************************************************************/

/**
 * Find path for includes
 */
// Move up and over to 'inc' directory to read common_backend.inc
$path=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
$path.='..'.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'common_backend.inc';
require_once realpath($path);

/* *************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * *************************************************************************/

/**
 * Observable_Exception class.
 *
 * @package Yapeal
 */
class Observable_Exception extends Exception {
  public static $_observers=array();
  public static function attach(Exception_Observer $observer) {
    self::$_observers[]=$observer;
  }
  public function __construct($message=null,$code=0) {
    parent::__construct($message,$code);
    $this->notify();
  }
  public function notify() {
    foreach (self::$_observers as $observer) {
      $observer->update($this);
    };
  }
}
?>