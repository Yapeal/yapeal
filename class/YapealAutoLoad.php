<?php
/**
 * Contains YapealAutoLoad class.
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
// Need to require one last class before autoloader can take over.
require_once __DIR__ . '/FilterFileFinder.php';
/**
 * Class used to manage auto loading of other classes/interfaces.
 *
 * @uses       YAPEAL_CLASS Constant with path to class directory.
 * @uses       YAPEAL_INTERFACE Constant with path to interface directory.
 * @uses       YAPEAL_EXT Constant with path to external packages directory.
 */
class YapealAutoLoad
{
    /**
     * Static only class.
     *
     * @throws LogicException Throws LogicException if new is used.
     */
    final public function __construct()
    {
        $mess = 'Illegally attempted to make instance of ' . __CLASS__;
        throw new LogicException($mess);
    }
    /**
     * Used to activate autoloading.
     */
    public static function activateAutoLoad()
    {
        if (false == spl_autoload_functions()) {
            spl_autoload_register(array('YapealAutoLoad', 'autoLoad'));
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload', false);
            }
        } else {
            // Prepend if other autoloaders already exist.
            spl_autoload_register(
                array('YapealAutoLoad', 'autoLoad'),
                false,
                true
            );
        }
    }
    /**
     * Searches through the common class directory locations for the file
     * containing the class/interface we need.
     *
     * @param string $className Class name to be loaded.
     *
     * @return bool TRUE if class/interface is found.
     */
    public static function autoLoad($className)
    {
        $ext = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ext'
            . DIRECTORY_SEPARATOR;
        $dirList = array(__DIR__ . DIRECTORY_SEPARATOR, $ext);
        $suffixList = array(
            '.php',
            '.inc.php',
            '.class.php',
            '.class',
            '.inc'
        );
        foreach ($dirList as $dir) {
            $files = new FilterFileFinder(
                $dir,
                $className,
                FilterFileFinder::CONTAINS
            );
            foreach ($files as $name => $object) {
                $bn = basename($name);
                foreach ($suffixList as $suffix) {
                    if ($bn == $className . $suffix
                        || $bn == strtolower($className) . $suffix
                        || $bn == 'I' . $className . $suffix
                        || $bn == 'I' . strtolower($className) . $suffix
                    ) {
                        include_once $name;
                        // Does the class/interface requested actually exist now?
                        if (class_exists($className, false)
                            || interface_exists(
                                $className,
                                false
                            )
                        ) {
                            // Yes, we're done.
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    /**
     * No backdoor through cloning either.
     *
     * @throws LogicException Throws LogicException if cloning of class is tried.
     */
    final public function __clone()
    {
        $mess = 'Illegally attempted to clone ' . __CLASS__;
        throw new LogicException($mess);
    }
}

