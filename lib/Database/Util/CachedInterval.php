<?php
/**
 * Contains CachedInterval class.
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
namespace Yapeal\Database\Util;

use Yapeal\Database\DBConnection;

/**
 * Wrapper class for utilCachedInterval table.
 *
 * Unlike the other wrapper classes this one is read only.
 */
class CachedInterval
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // If list is empty grab it from database.
        if (empty(self::$intervalList)) {
            self::resetAll();
        }
    }
    /**
     * Used to reset intervals back to database defaults.
     *
     * @throws \RuntimeException Throws a RuntimeException if connection to
     * database fails or can't get data from table.
     */
    public static function resetAll()
    {
        try {
            // Get a database connection.
            $con = DBConnection::connect(YAPEAL_DSN);
        } catch (\ADODB_Exception $e) {
            $mess =
                'Failed to get database connection in ' . basename(__CLASS__);
            throw new \RuntimeException($mess);
        }
        $sql = 'select `api`,`interval`,`section`';
        $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'util' . basename(__CLASS__)
            . '`';
        try {
            self::$intervalList = $con->GetAll($sql);
        } catch (\ADODB_Exception $e) {
            $mess = 'Failed to get data from table in ' . basename(__CLASS__);
            throw new \RuntimeException($mess);
        }
        // If the table is empty add a default for APIKeyInfo interval only.
        if (empty(self::$intervalList)) {
            self::$intervalList = array(
                array(
                    'api' => 'APIKeyInfo',
                    'interval' => 300,
                    'section' => 'account'
                )
            );
        }
    }
    /**
     * Used to temporarily change interval for an API.
     *
     * @param string $api      Name of API interval being add or changed.
     * @param string $section  Name of the section the API belongs to.
     * @param int    $interval New value for API interval.
     *
     * @return bool Returns TRUE if row already existed.
     *
     * @throws \InvalidArgumentException If $api or $section isn't a string will
     * throw an InvalidArgumentException.
     */
    public function changeInterval($api, $section, $interval)
    {
        if (!is_string($api) || !is_string($section)) {
            $mess = '$api and $section must be strings';
            throw new \InvalidArgumentException($mess);
        }
        $found = false;
        for ($i = 0, $cnt = count(self::$intervalList);$i < $cnt;++$i) {
            if (self::$intervalList[$i]['section'] == $section
                && self::$intervalList[$i]['api'] == $api
            ) {
                self::$intervalList[$i]['interval'] = $interval;
                $found = true;
            }
        }
        // No existing interval found for API temporarily add it.
        if ($found === false) {
            self::$intervalList[] = array(
                'api' => $api,
                'interval' => $interval,
                'section' => $section
            );
        }
        return $found;
    }
    /**
     * Used to get interval for an API.
     *
     * @param string $api     Name of API interval is needed for.
     * @param string $section Name of the section the API belongs to.
     *
     * @return int Returns the interval for the API. If the API can't be found an
     * error is triggered and the default hour interval is returned.
     *
     * @throws \InvalidArgumentException If $api or $section isn't a string will
     * throw an InvalidArgumentException.
     */
    public function getInterval($api, $section)
    {
        if (!is_string($api) || !is_string($section)) {
            $mess = '$api and $section must be strings';
            throw new \InvalidArgumentException($mess);
        }
        $found = false;
        $interval = 3600; // Use an hour as default.
        foreach (self::$intervalList as $row) {
            if ($row['section'] == $section && $row['api'] == $api) {
                $interval = $row['interval'];
                $found = true;
            }
        }
        if ($found === false) {
            if (\Logger::getLogger('yapeal')
                       ->isInfoEnabled()
            ) {
                $mess = $api . ' is an unknown API for section ' . $section;
                \Logger::getLogger('yapeal')
                       ->info($mess);
            }
        }
        return $interval;
    }
    /**
     * List of all CachedIntervals
     *
     * @var array
     */
    private static $intervalList;
}

