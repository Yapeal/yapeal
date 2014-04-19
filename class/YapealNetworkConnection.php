<?php
/**
 * Contains YapealNetworkConnection class.
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
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
use Yapeal\Exception\YapealApiException;
use Yapeal\Singleton;

/**
 * Wrapper for API network connection.
 */
class YapealNetworkConnection
{
    /**
     * Constructor
     *
     * @throws YapealApiException Throws YapealApiException on missing database
     * connection.
     */
    public function __construct()
    {
        $accept = 'text/xml,application/xml,application/xhtml+xml;q=0.9';
        $accept .= ',text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;q=0.5';
        $headers = array(
            'Accept: ' . $accept,
            'Accept-Language: en-us;q=0.9,en;q=0.8,*;q=0.7',
            'Accept-Charset: utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
            'Connection: Keep-Alive',
            'Keep-Alive: 300'
        );
        $file =
            dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ext' . DIRECTORY_SEPARATOR
            . 'eac_httprequest' . DIRECTORY_SEPARATOR
            . 'eac_httprequest.class.php';
        require_once $file;
        $this->con = Singleton::get('httpRequest');
        if (false === $this->con) {
            $mess = 'Could not get a connection to use for APIs';
            throw new YapealApiException($mess, 1);
        }
        $this->con->setOptions();
        foreach ($headers as $header) {
            $this->con->header($header, true);
        }
    }
    /**
     * Will retrieve the XML from API server.
     *
     * @param string $url      URL of API needed.
     * @param array  $postList A list of data that will be passed to the API server.
     *                         example: array(UserID => '123', apiKey => 'abc123', ...)
     *
     * @return string|false Returns XML data from API or FALSE for any connection error.
     */
    public function retrieveXml($url, $postList)
    {
        $result = $this->con->post($url, $postList);
        if (!$this->con->success) {
            if (Logger::getLogger('yapeal')
                      ->isInfoEnabled()
            ) {
                $mess = $this->con->error . ' for API ' . $url;
                Logger::getLogger('yapeal')
                      ->info($mess);
            }
            return false;
        }
        return $result;
    }
    /**
     * @var curlRequest Holds API connection object.
     */
    private $con;
}

