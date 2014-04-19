<?php
/**
 * Contains Yapeal\Singleton class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal;

/**
 * Class used to treat any other class like a singleton.
 *
 * Idea for this class is taken from many places but much of it was from the
 * notes found at
 *
 * @link       http://www.php.net/manual/en/reflectionclass.newinstanceargs.php NewInstanceArgs
 *             and from Sing class by David Hopkins (semlabs.co.uk)

 */
class Singleton
{
    /**
     * Static only class.
     *
     * @throws \LogicException Throws LogicException if new is used.
     */
    final public function __construct()
    {
        $mess = 'Illegally attempted to make instance of ' . __CLASS__;
        throw new \LogicException($mess);
    }
    /**
     * Used to get instance of a class.
     *
     * If an instance of the class doesn't exist it will be created. If there is
     * already an instance will return it.
     *
     * @param string $name Name of the class an instance is needed for.
     * @param array  $args Optional list of parameters to be passed to constructor
     *                     of new instance.
     *
     * @return mixed Will return an instance of the class or FALSE if getting an
     * instance is not possible.
     */
    public static function &get($name, $args = array())
    {
        if (isset(self::$instance[$name])) {
            if (!empty($args)) {
                if (\Logger::getLogger('yapeal')
                           ->isInfoEnabled()
                ) {
                    $mess = '$args ignored as an instance of ' . $name
                        . ' already exists';
                    \Logger::getLogger('yapeal')
                           ->info($mess);
                }
            }
            return self::$instance[$name];
        }
        $reflectionClass = new \ReflectionClass($name);
        if (false === $reflectionClass->isInstantiable()) {
            $mess = $name . ' class is not instantiable';
            \Logger::getLogger('yapeal')
                   ->warn($mess);
            return false;
        }
        if (true === $reflectionClass->hasMethod('__construct')) {
            $refMethod = new \ReflectionMethod($name, '__construct');
            $numParams = $refMethod->getNumberOfParameters();
            if ($numParams == 0) {
                if (!empty($args)) {
                    $mess = $name
                        . '::__construct() does not accept any parameters.';
                    $mess .= ' $args will be ignored.';
                    \Logger::getLogger('yapeal')
                           ->warn($mess);
                }
                self::$instance[$name] = $reflectionClass->newInstance();
            } else {
                $params = $refMethod->getParameters();
                $missing = array();
                $re_args = array();
                foreach ($params as $key => $param) {
                    if (!isset($args[$key])) {
                        if (!$param->isOptional()) {
                            $missing[] = $param->getName();
                        }
                        continue;
                    }
                    if ($param->isPassedByReference()) {
                        $re_args[$key] = & $args[$key];
                    } else {
                        $re_args[$key] = $args[$key];
                    }
                }
                if (!empty($missing)) {
                    $mess =
                        'The following required parameters for constructor were missing: ';
                    $mess .= implode(', ', $missing);
                    $mess .= ' when calling instance of ' . $name;
                    \Logger::getLogger('yapeal')
                           ->warn($mess);
                    return false;
                }
                self::$instance[$name] =
                    $reflectionClass->newInstanceArgs((array)$re_args);
            }
        }
        return self::$instance[$name];
    }
    /**
     * Will return a comma separated list of singletons. Used for debugging.
     *
     * @return string Returns a comma separated list of all the singletons.
     */
    public static function getAll()
    {
        $list = array_keys(self::$instance);
        return implode(',', $list);
    }
    /**
     * Used to release all singletons.
     *
     * @return void
     */
    static public function releaseAll()
    {
        self::$instance = array();
    }
    /**
     * No backdoor through cloning either.
     *
     * @throws \LogicException Throws LogicException if cloning of class is tried.
     */
    final public function __clone()
    {
        $mess = 'Illegally attempted to clone ' . __CLASS__;
        throw new \LogicException($mess);
    }
    /**
     * @var array Holds the list of instances.
     */
    static private $instance = array();
}

