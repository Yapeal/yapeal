<?php
/**
 * Yet Another Php Eve Api library
 *
 * YapealAutoLoad.php - Contents YapealAutoLoad class.
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
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
  exit();
};
/**
 * Class used to manage auto loading of other classes/interfaces.
 *
 * @package Yapeal
 * @subpackage Autoload
 */
class YapealAutoLoad {
  /**
   * @var object
   */
  protected static $instance;
  /**
   * @var array
   */
  private static $dirList;
  /**
   * @var array
   */
  private static $suffixList;
  /**
   * Only way to make instance is through {@link getInstance() getInstance()}.
   */
  private function __construct() {
    self::$dirList = array(YAPEAL_CLASS, YAPEAL_ADODB);
    self::$suffixList = array('.php', '.class.php', '.inc.php', '.class', '.inc');
  }
  /**
   * No backdoor through cloning either.
   */
  private function __clone() {}
  /**
   * Used to get an instance of the class.
   *
   * @return YapealAutoLoad Returns an instance of the class.
   */
  public static function getInstance() {
    if (!(self::$instance instanceof self)) {
      self::$instance = new self();
    };
    return self::$instance;
  }
  /**
   * Searches through the common class directory locations for the file
   * containing the class/interface we need.
   *
   * @param string $className Class name to be loaded.
   *
   * @return bool TRUE if class/interface is found.
   */
  public static function autoLoad($className) {
    self::getInstance();
    foreach (self::$dirList as $dir) {
      $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
      foreach ($files as $name => $object) {
        $bn = basename($name);
        foreach (self::$suffixList as $suffix) {
          if ($bn == $className . $suffix ||
            $bn == strtolower($className) . $suffix ||
            $bn == 'I' . $className . $suffix ||
            $bn == 'I' . strtolower($className) . $suffix) {
            include_once($name);
            // does the class/interface requested actually exist now?
            if (class_exists($className, FALSE) ||
              interface_exists($className, FALSE)) {
              // yes, we're done
              return TRUE;
            };// if class_exists...
          };// if basename...
        };// foreach self::$suffixList ...
      };// foreach $files ...
    };// foreach self::$dirList ...
    return FALSE;
  }
  /**
   * Add an extension to the list used for class/interface names.
   *
   * @param string $ext The extension to be added to list.
   *
   * @return bool TRUE if extension was already in the list.
   */
  static public function addExtension($ext) {
    self::getInstance();
    if (!in_array($ext, self::$suffixList)) {
      self::$suffixList[] = $ext;
      return FALSE;
    };
    return TRUE;
  }
  /**
   * Add a directory to the list to be searched in for class/interface files.
   *
   * @param string $dir The directory to be added to list.
   *
   * @return bool TRUE if directory was already in the list.
   */
  static public function addPath($dir) {
    self::getInstance();
    if (!in_array($dir, self::$dirList)) {
      self::$dirList[] = $dir;
      return FALSE;
    };
    return TRUE;
  }
}
// Now activate the EMPAAutoLoad autoloader.
if (FALSE == spl_autoload_functions()) {
  spl_autoload_register(array('YapealAutoLoad', 'autoLoad'));
  if (function_exists('__autoload')) {
    spl_autoload_register('__autoload', FALSE);
  };
}
?>
