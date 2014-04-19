<?php
/**
 * Contains DBConnection class.
 *
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * as Yapeal.
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
 */
namespace Yapeal\Database;

/**
 * A factory to manage ADOdb connections to databases.
 */
class DBConnection
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
     * Use to get a ADOdb connection object.
     *
     * This method will create a new ADOdb connection for each DSN it is passed and
     * return the same connection for any other requests for the same DSN. It was
     * developed to get around some problems with how ADOdb handles connections
     * that was not compatible with what I needed.
     *
     * @param string $dsn An ADOdb compatible connection string.
     *
     * @return \ADODB_mysqli Returns ADOdb connection object.
     *
     * @throws \InvalidArgumentException if $dsn is empty or if $dsn isn't a string
     * it will throw InvalidArgumentException.
     * @throws \ADODB_Exception Passes through any problem with actual connection
     * from ADOdb.
     */
    public static function connect($dsn)
    {
        if (empty($dsn) || !is_string($dsn)) {
            throw new \InvalidArgumentException('Bad value passed for $dsn');
        }
        global $ADODB_COUNTRECS, $ADODB_CACHE_DIR;
        $ADODB_COUNTRECS = false;
        $ADODB_CACHE_DIR =
            dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . 'ADOdb';
        if (empty(self::$connections)) {
            $adoDir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'ext'
                . DIRECTORY_SEPARATOR . 'ADOdb' . DIRECTORY_SEPARATOR;
            require_once $adoDir . 'adodb-exceptions.inc.php';
            require_once $adoDir . 'adodb.inc.php';
        }
        $hash = hash('sha1', $dsn);
        if (!array_key_exists($hash, self::$connections)) {
            /** @var \ADODB_mysqli $ado */
            $ado = \NewADOConnection($dsn);
            $ado->debug = false;
            $ado->SetFetchMode(ADODB_FETCH_ASSOC);
            $ado->Execute('set names utf8');
            $ado->Execute('set time_zone="+0:00"');
            self::$connections[$hash] = $ado;
        }
        return self::$connections[$hash];
    }
    /**
     * Function to close and release all existing ADOdb connections.
     *
     * @throws \ADODB_Exception Passes through any problem with actual connection
     * from ADOdb.
     */
    public static function releaseAll()
    {
        if (!empty(self::$connections)) {
            foreach (self::$connections as $k => $v) {
                self::$connections[$k]->Close();
                self::$connections[$k] = null;
                unset(self::$connections[$k]);
            }
        }
    }
    /**
     * Function used to set constants from [Database] section of the configuration
     * file.
     *
     * @param array $section A list of settings for this section of configuration.
     */
    public static function setDatabaseSectionConstants(array $section)
    {
        if (!defined('YAPEAL_DSN')) {
            // Put all the pieces of the ADOdb DSN together.
            $dsn = $section['driver'] . $section['username'] . ':';
            $dsn .= $section['password'] . '@' . $section['host'];
            $dsn .= '/' . $section['database'] . $section['suffix'];
            /**
             * Defines the DSN used for ADOdb connection.
             */
            define('YAPEAL_DSN', $dsn);
        }
        if (!defined('YAPEAL_TABLE_PREFIX')) {
            /**
             * Defines the table prefix used for all Yapeal tables.
             */
            define('YAPEAL_TABLE_PREFIX', $section['table_prefix']);
        }
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
     * @var \ADODB_mysqli[] List of ADOdb connection resources.
     */
    private static $connections = array();
}

