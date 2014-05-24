<?php
/**
 * Contains NetworkRetrieverAbstract class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of yapeal
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Network;


/**
 * Class NetworkRetrieverAbstract
 */
abstract class NetworkRetrieverAbstract
{
    /**
     * @var string
     */
    protected $userAgent = 'Yapeal/1.2 (+https://github.com/Dragonrun1/yapeal/wiki)';
    /**
     * @var mixed
     */
    protected $client;
    /**
     * @var string
     */
    /**
     * @var array
     */
    protected $options;
    /**
     * @var array
     */
    protected $headers = array( //move
        'Accept' => 'text/xml,application/xml,application/xhtml+xml;q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;q=0.5',
        'Accept-Charset' => 'utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
        'Accept-Encoding' => 'gzip',
        'Accept-Language' => 'en-us;q=0.9,en;q=0.8,*;q=0.7',
        'Connection' => 'Keep-Alive',
        'Keep-Alive' => '300'
    );
    /**
     * @param array $value
     *
     * @return self
     */
    public function setHeaders($value)
    {
        $this->headers = $value;
        return $this;
    }
    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    /**
     * @param array $value
     *
     * @return self
     */
    public function setOptions($value) //move
    {
        $this->options = $value;
        return $this;
    }
    /**
     * @param string $userAgent
     */
    public function setUserAgent($userAgent) //move
    {
        $this->userAgent = $userAgent;
    }
    /**
     * @param $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }
}
