<?php
/**
 * Contents IYapealObserver Interface.
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
 * @copyright Copyright (c) 2009, Michael Cummings
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
 * Interface classes need to implement to observable exceptions.
 *
 * @package Yapeal
 * @subpackage Observer
 */
interface YapealSubject {
  /**
   * Used by observers to register so they can be notified.
   *
   * @param SplObserver $observer The observer being added.
   */
  public static function attach(YapealObserver $observer);
  /**
   * Used by observers to unregister from being notified.
   *
   * @param SplObserver $observer The observer being removed.
   */
  public static function detach(YapealObserver $observer);
  /**
   * Used to notify all the observers.
   */
  public function notify();
}
