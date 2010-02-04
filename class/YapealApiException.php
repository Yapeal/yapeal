<?php
/**
 * Contains Custom Yapeal API exception class.
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
/**
 * Base class used for all API type exception.
 *
 * @package Yapeal
 * @subpackage Exceptions
 * @uses IYapealSubject
 */
class YapealApiException extends Exception implements IYapealSubject {
  /**
   * @var array Hold the references to our observers.
   */
  protected static $observers = array();
  /**
   * Constructor
   *
   * @param string $message Optional text message of the exception.
   * @param integer $code Optional code for exception.
   */
  public function __construct($message = NULL, $code = 0) {
    parent::__construct($message, $code);
    $this->notify();
  }
  /**
   * Used by observers to register so they can be notified.
   *
   * @param IYapealObserver $observer The observer being added.
   */
  public static function attach(IYapealObserver $observer) {
    $idx = spl_object_hash($observer);
    self::$observers[$idx] = $observer;
  }
  /**
   * Used by observers to unregister from being notified.
   *
   * @param IYapealObserver $observer The observer being removed.
   */
  public static function detach(IYapealObserver $observer) {
    $idx = spl_object_hash($observer);
    if (array_key_exists($idx, self::$observers)) {
      unset(self::$observers[$idx]);
    };
  }
  /**
   * Used to notify all the observers.
   */
  public function notify() {
    foreach (self::$observers as $observer) {
      $observer->YapealUpdate($this);
    };
  }
}
?>
