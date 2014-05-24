<?php
/**
 * Contains GuzzleNetworkRetriever class.
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

use Guzzle\Http\Client;
use Guzzle\Http\Message\Request;

/**
 * Class GuzzleNetworkRetriever
 */
class GuzzleNetworkRetriever extends NetworkRetrieverAbstract implements
    NetworkRetrieverInterface
{
    /**
     * @return Client|ClientInterface
     */
    public function getClient()
    {
        /** Check if we already have a client set, else we set one */
        if (isset($this->client)) {
            return $this->client;
        } else {
            $this->client = new Client();
        }
        /** Set user agent on client */
        $this->client->setUserAgent($this->userAgent);
        return $this->client;
    }
    /**
     * @param $plugin
     *
     * @return $this
     */
    public function addSubscriber($plugin = null)
    {
        if (isset($plugin)) {
            $this->client->addSubscriber($plugin);
        }
        return $this;
    }
    /**
     * @param $request
     *
     * @return response
     */
    public function sendRequest(request $request) //move
    {
        return $response = $request->send();
    }
    /**
     * @param Client|ClientInterface $client
     *
     * @return $this
     */
    public function setClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }
    /**
     * @param string $urlTemplate
     * @param array  $urlTemplateOptions
     * @param array  $postData
     *
     * @return request
     */
    public function sendPost($urlTemplate, $urlTemplateOptions, $postData)
    {
        $request = $this->client->post(
                                array(
                                    $urlTemplate,
                                    $urlTemplateOptions
                                ),
                                    $this->getHeaders(),
                                    $postData,
                                    $this->getOptions()
        );
        return $request;
    }
    /**
     * @return array
     */
    public function getOptions()
    {
        if (isset($this->options)) {
            return $this->options;
        } else {
            return $options = array(
                'timeout' => 10,
                'connect_timeout' => 30,
                'verify' =>
                    dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config'
                    . DIRECTORY_SEPARATOR . 'eveonline.crt',
            );
        }
    }
}
