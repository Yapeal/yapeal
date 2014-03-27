<?php
/**
 * Contains abstract class for server section.
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
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Api;

use LengthException;
use Logger;
use Yapeal\Database\DatabaseConnection;
use Yapeal\Util\CachedUntil;

/**
 * Abstract class for Server APIs.
 *
 * @package Yapeal\Api
 */
abstract class AServer extends AApiRequest
{
    /**
     * Constructor
     *
     * @param array $params Holds the required parameters like keyID, vCode, etc
     *                      used in HTML POST parameters to API servers which varies depending on API
     *                      'section' being requested.
     *
     * @throws LengthException for any missing required $params.
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }
    // function __construct
    /**
     * Per API section function that returns API proxy.
     *
     * For a description of how to design a format string look at the description
     * from {@link AApiRequest::sprintfn sprintfn}. The 'section' and 'api' will
     * be available as well as anything included in $params for __construct().
     *
     * @return mixed Returns the URL for proxy as string if found else it will
     * return the default string needed to use API server directly.
     */
    protected function getProxy()
    {
        $default = 'https://api.eveonline.com/' . $this->section;
        $default .= '/' . $this->api . '.xml.aspx';
        try {
            $con = DatabaseConnection::connect(YAPEAL_DSN);
            $sql = 'select `proxy`';
            $sql .= ' from ';
            $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilSections`';
            $sql .= ' where';
            $sql .= ' `section`=' . $con->qstr($this->section);
            $result = $con->GetOne($sql);
            if (empty($result)) {
                return $default;
            }; // if empty $result ...
            // Need to make substitution array by adding api, section, and params.
            $subs = array('api' => $this->api, 'section' => $this->section);
            $subs = array_merge($subs, $this->params);
            $proxy = self::sprintfn($result, $subs);
            if (false === $proxy) {
                return $default;
            };
            return $proxy;
        } catch (\ADODB_Exception $e) {
            return $default;
        }
    }
    // function getProxy
    /**
     * Handles some Eve API error codes in special ways.
     *
     * @param object $e Eve API exception returned.
     *
     * @return bool Returns TRUE if handled the error else FALSE.
     */
    protected function handleApiError($e)
    {
        try {
            switch ($e->getCode()) {
                case 901: // Web site database temporarily disabled.
                case 902: // EVE backend database temporarily disabled.
                    $cuntil = gmdate('Y-m-d H:i:s', strtotime('6 hours'));
                    $data = array(
                        'api' => $this->api,
                        'cachedUntil' => $cuntil,
                        'ownerID' => 0,
                        'section' => $this->section
                    );
                    $cu = new CachedUntil($data);
                    $cu->store();
                    break;
                default:
                    return false;
                    break;
            }; // switch $code ...
        } catch (\ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                ->error($e);
            return false;
        }
        return true;
    }
    // function handleApiError
}

