<?php
/**
 * Contains Yapeal's custom error handler.
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
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 * @link       http://logging.apache.org/log4php/
 */
/**
 * Yapeal's custom error handler.
 *
 * @package    Yapeal
 * @subpackage Error
 */
class YapealErrorHandler
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
     * Method that PHP will call to handle errors.
     *
     * @param integer $errno    Holds one of E_USER_ERROR, E_USER_WARNING, etc.
     * @param string  $errmsg   Text of error message.
     * @param string  $filename Name of file where error happened.
     * @param integer $line     Line number where error happened.
     * @param array   $vars     Array containing all the defined variables and constants.
     *
     * @return bool Returns TRUE if error is handled by this function else FALSE.
     */
    public static function handle($errno, $errmsg, $filename, $line, $vars)
    {
        // obey @ protocol
        if (error_reporting() == 0) {
            return false;
        }
        /*
         * Do not add any of the E_USER_* constants to switch except if you believe
         * in free energy and like infinite loops.
         */
        switch ($errno) {
            case E_ERROR:
            case E_RECOVERABLE_ERROR:
                $body = $errmsg . PHP_EOL;
                $body .= '   Trace:' . PHP_EOL;
                ob_start();
                debug_print_backtrace();
                $backtrace = ob_get_flush();
                $body .= $backtrace . PHP_EOL;
                $body .= str_pad(' END TRACE ', 30, '-', STR_PAD_BOTH);
                Logger::getLogger('yapeal_capture')
                      ->error($body);
                return true;
            case E_NOTICE:
            case E_STRICT:
                Logger::getLogger('yapeal_capture')
                      ->info($errmsg);
                return true;
            case E_WARNING:
                Logger::getLogger('yapeal_capture')
                      ->warn($errmsg);
                return true;
            case E_DEPRECATED:
                Logger::getLogger('yapeal_capture')
                      ->info($errmsg);
                return true;
        }
        return false;
    }
    /**
     * Function used to set constants from [Logging] section of the configuration
     * file.
     *
     * @param array  $section A list of settings for this section of configuration.
     * @param string $file    Path and name of the config file to use with log4php.
     */
    public static function setLoggingSectionProperties(
        array $section,
        $file = null
    ) {
        $config = dirname(__DIR__) . '/config/';
        // Check if given custom configuration file.
        if (empty($file) || !is_string($file)) {
            if (!empty($section['log_config'])) {
                $file = $section['log_config'];
                // Change so do NOT need YAPEAL_CONFIG constant.
                $file = str_replace('{config}', $config, $file);
            } else {
                $file = @getenv('YAPEAL_LOGGER');
                if ($file === false) {
                    $file = $config . 'logger.xml';
                }
            }
        }
        if (!(is_readable($file) && is_file($file))) {
            $mess =
                'The ' . $file . ' configuration file is missing!' . PHP_EOL;
            fwrite(STDERR, $mess);
            exit(1);
        }
        self::$logConfig = $file;
        if (!empty($section['trace_enabled'])) {
            if (!defined('YAPEAL_TRACE_ENABLED')) {
                define('YAPEAL_TRACE_ENABLED', $section['trace_enabled']);
            }
        } else {
            define('YAPEAL_TRACE_ENABLED', false);
        }
    }
    /**
     * Function used to setup error and exception logging.
     *
     */
    public static function setupCustomErrorAndExceptionSettings()
    {
        Logger::configure(self::$logConfig);
        // Start using custom error handler.
        set_error_handler(array('YapealErrorHandler', 'handle'));
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
    /**
     * @var int Holds path and name of the config file to use with log4php.
     */
    private static $logConfig = '';
}

