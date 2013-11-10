<?php
/**
 * Contains YapealAutoLoad class.
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
 * @copyright  Copyright (c) 2008-2013, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Autoload;

use LogicException;

/**
 * Class used to manage auto loading of other classes/interfaces.
 *
 * @package    Yapeal\Autoload
 *
 * @deprecated With move to PHP 5.3 namespaces will be moving to PSR-0 type auto
 *             loader and will stop using this class soon!
 *
 * @uses       YAPEAL_CLASS Constant with path to class directory.
 * @uses       YAPEAL_INTERFACE Constant with path to interface directory.
 * @uses       YAPEAL_EXT Constant with path to external packages directory.
 */
class YapealAutoLoad
{
    /**
     * @var object
     */
    protected static $instance;
    /**
     * @var array
     */
    private static $dirList = array(YAPEAL_CLASS, YAPEAL_EXT, YAPEAL_INTERFACE);
    /**
     * @var array
     */
    private static $suffixList = array(
        '.php',
        '.inc.php',
        '.class.php',
        '.class',
        '.inc'
    );
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
    // function __construct
    /**
     * Used to activate autoloading.
     */
    public static function activateAutoLoad()
    {
        if (false == spl_autoload_functions()) {
            spl_autoload_register(
                array('Yapeal\Autoload\YapealAutoLoad', 'autoLoad')
            );
            if (function_exists('__autoload')) {
                spl_autoload_register('__autoload', false);
            };
        } else {
            // Prepend if other autoloaders already exist.
            spl_autoload_register(
                array('Yapeal\Autoload\YapealAutoLoad', 'autoLoad'),
                true,
                true
            );
        }; // else FALSE == spl_autoload_functions() ...
    }
    // function __clone
    /**
     * Add an extension to the list used for class/interface names.
     *
     * @param string $ext The extension to be added to list.
     *
     * @return bool TRUE if extension was already in the list.
     */
    public static function addExtension($ext)
    {
        if (!in_array($ext, self::$suffixList)) {
            self::$suffixList[] = $ext;
            return false;
        };
        return true;
    }
    // function activateAutoLoad
    /**
     * Add a directory to the list to be searched in for class/interface files.
     *
     * @param string $dir The directory to be added to list.
     *
     * @return bool TRUE if directory was already in the list.
     */
    public static function addPath($dir)
    {
        if (!in_array($dir, self::$dirList)) {
            self::$dirList[] = $dir;
            return false;
        };
        return true;
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
        foreach (self::$dirList as $dir) {
            $files =
                new FilterFileFinder($dir, $className, FilterFileFinder::CONTAINS);
            foreach ($files as $name => $object) {
                $bn = basename($name);
                foreach (self::$suffixList as $suffix) {
                    if ($bn == $className . $suffix
                        || $bn == strtolower($className) . $suffix
                        || $bn == 'I' . $className . $suffix
                        || $bn == 'I' . strtolower($className) . $suffix
                    ) {
                        @include_once($name);
                        // Does the class/interface requested actually exist now?
                        if (class_exists($className, false)
                            || interface_exists(
                                $className,
                                false
                            )
                        ) {
                            // Yes, we're done.
                            return true;
                        }; // if class_exists...
                    }; // if basename...
                }; // foreach self::$suffixList ...
            }; // foreach $files ...
        }; // foreach self::$dirList ...
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

