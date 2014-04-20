<?php
/**
 * Contains abstract class for account section.
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
namespace Yapeal\Database;

use CachedUntil;
use RegisteredKey;

/**
 * Abstract class for Account APIs.
 */
abstract class AAccount extends AApiRequest
{
    /**
     * Constructor
     *
     * @param array $params Holds the required parameters like keyID, vCode, etc
     *                      used in POST parameters to API servers which varies depending on API
     *                      'section' being requested.
     *
     * @throws \LengthException for any missing required $params.
     */
    public function __construct(array $params)
    {
        $required = array('keyID' => 'I', 'vCode' => 'C');
        foreach ($required as $k => $v) {
            if (!isset($params[$k])) {
                $mess = 'Missing required parameter $params["' . $k . '"]';
                $mess .= ' to constructor for ' . $this->api;
                $mess .= ' in ' . __CLASS__;
                throw new \LengthException($mess, 1);
            }
            switch ($v) {
                case 'C':
                case 'X':
                    if (!is_string($params[$k])) {
                        $mess = '$params["' . $k . '"] must be a string for '
                            . $this->api;
                        $mess .= ' in ' . __CLASS__;
                        throw new \LengthException($mess, 2);
                    }
                    break;
                case 'I':
                    if (0 != strlen(
                            str_replace(range(0, 9), '', $params[$k])
                        )
                    ) {
                        $mess = '$params["' . $k . '"] must be an integer for '
                            . $this->api;
                        $mess .= ' in ' . __CLASS__;
                        throw new \LengthException($mess, 3);
                    }
                    break;
            }
        }
        $this->ownerID = $params['keyID'];
        $this->params = $params;
    }
    /**
     * Per API section function that returns API proxy.
     *
     * For a description of how to design a format string look at the description
     * from {@link Yapeal\Database\AApiRequest::sprintfn sprintfn}. The 'section' and 'api' will
     * be available as well as anything included in $params for __construct().
     *
     * @throws \InvalidArgumentException
     * @return string Returns the URL for proxy as string if found else it will
     * return the default string needed to use API server directly.
     */
    protected function getProxy()
    {
        $default = 'https://api.eveonline.com/' . $this->section;
        $default .= '/' . $this->api . '.xml.aspx';
        $sql = 'SELECT proxy FROM ';
        try {
            $con = DBConnection::connect(YAPEAL_DSN);
            $tables = array();
            $tables[] = '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredKey`'
                . ' where `keyID`=' . $this->params['keyID'];
            $tables[] = '`' . YAPEAL_TABLE_PREFIX . 'utilSections`'
                . ' where `section`=' . $con->qstr($this->section);
            // Look for a set proxy in each table.
            foreach ($tables as $table) {
                $result = $con->GetOne($sql . $table);
                // 4 is random and not magic. It just sounded good and is shorter than
                // any legal URL.
                if (strlen($result) > 4) {
                    break;
                }
            }
            if (empty($result)) {
                return $default;
            }
            // Need to make substitution array by adding api, section, and params.
            $subs = array('api' => $this->api, 'section' => $this->section);
            $subs = array_merge($subs, $this->params);
            $proxy = self::sprintfn($result, $subs);
            if (false === $proxy) {
                return $default;
            }
            return $proxy;
        } catch (\ADODB_Exception $e) {
            return $default;
        }
    }
    /**
     * Handles some Eve API error codes in special ways.
     *
     * @param object $e Eve API exception returned.
     *
     * @throws \InvalidArgumentException
     * @return bool Returns TRUE if handled the error else FALSE.
     */
    protected function handleApiError($e)
    {
        try {
            switch ($e->getCode()) {
                case 202: // API key authentication failure.
                case 203: // Authentication failure.
                case 204: // Authentication failure.
                case 205: // Authentication failure (final pass).
                case 210: // Authentication failure.
                case 212: // Authentication failure (final pass).
                    $mess = 'Deactivating keyID: ' . $this->params['keyID'];
                    $mess .= ' as the Eve key information is incorrect';
                \Logger::getLogger('yapeal')
                          ->warn($mess);
                    $key = new RegisteredKey($this->params['keyID'], false);
                    $key->isActive = 0;
                    if (false === $key->store()) {
                        $mess = 'Could not deactivate keyID: '
                            . $this->params['keyID'];
                        \Logger::getLogger('yapeal')
                              ->warn($mess);
                    }
                    break;
                case 211: // Login denied by account status.
                    // The account isn't active deactivate key.
                    $mess = 'Deactivating keyID: ' . $this->params['keyID'];
                    $mess .= ' as the Eve account is currently suspended';
                    \Logger::getLogger('yapeal')
                          ->warn($mess);
                    $key = new RegisteredKey($this->params['keyID'], false);
                    $key->isActive = 0;
                    if (false === $key->store()) {
                        $mess = 'Could not deactivate keyID: '
                            . $this->params['keyID'];
                        \Logger::getLogger('yapeal')
                              ->warn($mess);
                    }
                    break;
                case 222: //Key has expired. Contact key owner for access renewal.
                    $mess = 'Deactivating keyID: ' . $this->params['keyID'];
                    $mess .= ' as it needs to be renewed by owner';
                    \Logger::getLogger('yapeal')
                          ->warn($mess);
                    // Deactivate for char and corp sections by expiring the key.
                    $sql =
                        'update `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo`';
                    $sql .= ' set `expires` = "' . gmdate('Y-m-d H:i:s') . '"';
                    $sql .= ' where `keyID` = ' . $this->params['keyID'];
                    // Get a database connection.
                    $con = DBConnection::connect(YAPEAL_DSN);
                    $con->Execute($sql);
                    // Deactivate for account section.
                    $key = new RegisteredKey($this->params['keyID'], false);
                    $key->isActive = 0;
                    if (false === $key->store()) {
                        $mess = 'Could not deactivate keyID: '
                            . $this->params['keyID'];
                        \Logger::getLogger('yapeal')
                              ->warn($mess);
                    }
                    break;
                case 901: // Web site database temporarily disabled.
                case 902: // EVE backend database temporarily disabled.
                    $cuntil = gmdate('Y-m-d H:i:s', strtotime('6 hours'));
                    $data = array(
                        'api' => $this->api,
                        'cachedUntil' => $cuntil,
                        'ownerID' => $this->params['keyID'],
                        'section' => $this->section
                    );
                    $cu = new CachedUntil($data);
                    $cu->store();
                    break;
                default:
                    return false;
                    break;
            }
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                  ->warn($e);
            return false;
        }
        return true;
    }
}

