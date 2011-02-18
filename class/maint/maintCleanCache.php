<?php
/**
 * Contains Maintenance Clean Cache class.
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
/**
 * Class used to clean out old unused cached API XML files from cache directory.
 *
 * @package Yapeal
 * @subpackage maintenance
 */
class maintCleanCache {
  /**
   * Constructor
   */
  public function __construct() {
    $this->sections = array('account', 'char', 'corp', 'eve', 'map', 'server');
  }// function __construct()
    /**
     * This function finds and deletes any XML files in cache/ that haven't been
     * modified for seven days or more.
     *
     * By default all the APIs are setup to refresh in a day or less in
     * utilCachedInterval so any XML file older then that aren't being used and
     * are just taking up space since Yapeal will have to grab them again
     * anyway.
     */
    public function doWork() {
      $limit = strtotime('7 days ago');
      foreach ($this->sections as $section) {
        $path = YAPEAL_CACHE . $section . DS;
        $files = new DirectoryIterator($path);
        foreach ($files as $item) {
          $name = $item->getFileName();
          // Only need to be concerned with expired XML Files.
          if ($item->isFile() && $item->isWritable()
            && substr($name, -3) == 'xml' && $item->getMTime() < $limit) {
            $result = @unlink($name);
            if ($result === FALSE) {
              $mess = 'Could not delete ' . $name;
              trigger_error($mess, E_USER_WARNING);
            };// if $result...
          };// if $item->isFile() ...
        };// foreach $files ...
      };// foreach $sections ...
      return TRUE;
    }// function doWork()
}
?>
