<?php
/**
 * Contains ConsoleWiring class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Configuration;

use Guzzle\Http\Client;
use Yapeal\Xml\FileCachePreserver;
use Yapeal\Xml\GuzzleNetworkRetriever;

/**
 * Class ConsoleWiring
 */
class ConsoleWiring extends Wiring
{
    /**
     * @return self
     */
    public function wirePreserver()
    {
        if (isset($this->dic['Yapeal.Xml.Preserver'])) {
            return $this;
        }
        $this->dic['Yapeal.Xml.Preserver'] = function ($dic) {
            return new FileCachePreserver(
                $dic['Yapeal.Log.Logger'],
                $dic['Yapeal.Cache.cacheDir']
            );
        };
        return $this;
    }
    /**
     * @return self
     */
    public function wireRetriever()
    {
        if (isset($this->dic['Yapeal.Xml.Retriever'])) {
            return $this;
        }
        $this->dic['Yapeal.Xml.Retriever'] = function ($dic) {
            $appComment = $dic['Yapeal.Network.appComment'];
            $appName = $dic['Yapeal.Network.appName'];
            $appVersion = $dic['Yapeal.Network.appVersion'];
            if (empty($appName)) {
                $appComment = '';
                $appVersion = '';
            }
            $userAgent = trim(
                str_replace(
                    [
                        '{machineType}',
                        '{osName}',
                        '{osRelease}',
                        '{phpVersion}',
                        '{Yapeal.Network.appComment}',
                        '{Yapeal.Network.appName}',
                        '{Yapeal.Network.appVersion}'
                    ],
                    [
                        php_uname('m'),
                        php_uname('s'),
                        php_uname('r'),
                        PHP_VERSION,
                        $appComment,
                        $appName,
                        $appVersion
                    ],
                    $dic['Yapeal.Network.userAgent']
                )
            );
            $userAgent = ltrim($userAgent, '/ ');
            $headers = [
                'Accept' => 'text/xml,application/xml,application/xhtml+xml;'
                            . 'q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;'
                            . 'q=0.6,*/*;q=0.5',
                'Accept-Charset' => 'utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
                'Accept-Encoding' => 'gzip',
                'Accept-Language' => 'en-us;q=0.9,en;q=0.8,*;q=0.7',
                'Connection' => 'Keep-Alive',
                'Keep-Alive' => '300'
            ];
            if (!empty($userAgent)) {
                $headers['User-Agent'] = $userAgent;
            }
            $defaults = [
                'headers' => $headers,
                'timeout' => 10,
                'connect_timeout' => 30,
                'verify' => $dic['Yapeal.baseDir'] . 'config/eveonline.crt',
            ];
            return new GuzzleNetworkRetriever(
                $dic['Yapeal.Log.Logger'],
                new Client(
                    $dic['Yapeal.Network.baseUrl'],
                    ['defaults' => $defaults]
                )
            );
        };
        return $this;
    }
}
