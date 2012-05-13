<?php
/**
 * Contains Singleton class.
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
 * Class used to treat any other class like a singleton.
 *
 * Idea for this class is taken from many places but much of it was from the
 * notes found at
 * @link http://www.php.net/manual/en/reflectionclass.newinstanceargs.php NewInstanceArgs
 * and from Sing class by David Hopkins (semlabs.co.uk)
 * @package    Yapeal
 */
class Singleton {
  /**
   * @var array Holds the list of instances.
   */
  static private $instance = array();
  /**
   * Static only class.
   *
   * @throws LogicException Throws LogicException if new is used.
   */
  final public function __construct() {
    $mess = 'Illegally attempted to make instance of ' . __CLASS__;
    throw new LogicException($mess);
  }// function __construct
  /**
   * No backdoor through cloning either.
   *
   * @throws LogicException Throws LogicException if cloning of class is tried.
   */
  final public function __clone() {
    $mess = 'Illegally attempted to clone ' . __CLASS__;
    throw new LogicException($mess);
  }// function __clone
  /**
   * Used to get instance of a class.
   *
   * If an instance of the class doesn't exist it will be created. If there is
   * already an instance will return it.
   *
   * @param string $name Name of the class an instance is needed for.
   * @param array $args Optional list of parameters to be passed to constructor
   * of new instance.
   *
   * @return mixed Will return an instance of the class or FALSE if getting an
   * instance is not possible.
   */
	public static function &get($name, $args = array())	{
		if (isset(self::$instance[$name])) {
      if (!empty($args)) {
        if (Logger::getLogger('yapeal')->isInfoEnabled()) {
          $mess = '$args ignored as an instance of ' . $name . ' already exists';
          Logger::getLogger('yapeal')->info($mess);
        };
      };
      return self::$instance[$name];
    };
    $reflectionClass = new ReflectionClass($name);
    if (FALSE === $reflectionClass->isInstantiable()) {
      $mess = $name . ' class is not instantiable';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    if (TRUE === $reflectionClass->hasMethod('__construct')) {
      $refMethod = new ReflectionMethod($name,  '__construct');
      $numParams = $refMethod->getNumberOfParameters();
      if ($numParams == 0) {
        if (!empty($args)) {
          $mess = $name . '::__construct() does not accept any parameters.';
          $mess .= ' $args will be ignored.';
          Logger::getLogger('yapeal')->warn($mess);
        };
        self::$instance[$name] = $reflectionClass->newInstance();
      } else {
        $params = $refMethod->getParameters();
        $missing = array();
        $re_args = array();
        foreach ($params as $key => $param) {
          if (!isset($args[$key])) {
            if (!$param->isOptional()) {
              $missing[] = $param->getName();
            };
            continue;
          };
          if ($param->isPassedByReference()) {
            $re_args[$key] = &$args[$key];
          } else {
            $re_args[$key] = $args[$key];
          };
        };
        if (!empty($missing)) {
          $mess = 'The following required parameters for constructor were missing: ';
          $mess .= implode(', ', $missing);
          $mess .= ' when calling instance of ' . $name;
          Logger::getLogger('yapeal')->warn($mess);
          return FALSE;
        };
        self::$instance[$name] = $reflectionClass->newInstanceArgs((array)$re_args);
      };
    };
		return self::$instance[$name];
	}// function get
  /**
   * Will return a comma separated list of singletons. Used for debugging.
   *
   * @return string Returns a comma separated list of all the singletons.
   */
  public static function getAll() {
    $list = array_keys(self::$instance);
    return implode(',', $list);
  }// function getAll
  /**
   * Used to release all singletons.
   *
   * @return void
   */
  static public function releaseAll() {
    self::$instance = array();
  }// function releaseAll
}

