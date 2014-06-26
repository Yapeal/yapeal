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
namespace Yapeal\Xml;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\RequestException;
use Guzzle\Http\Message\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Class GuzzleNetworkRetriever
 *
 * @author Stephen Gulick <stephenmg12@gmail.com>
 */
class GuzzleNetworkRetriever implements EveApiRetrieverInterface,
    LoggerAwareInterface
{
    /**
     * @param LoggerInterface $logger
     * @param ClientInterface|null $client
     */
    function __construct(
        LoggerInterface $logger,
        ClientInterface $client = null
    )
    {
        $this->setLogger($logger);
        $this->setClient($client);
    }
    /**
     *
     */
    public function __destruct()
    {
    }
    /**
     * @param EveApiXmlModifyInterface $data
     *
     * @return self
     */
    public function retrieveEveApi(EveApiXmlModifyInterface &$data)
    {
        $mess = sprintf(
            'Started network retrieve for %1$s/%2$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $this->getLogger()
             ->debug($mess);
        $result = $this->readXmlData($this->prepareConnection($data));
        //$this->getLogger()->debug($result);
        $data->setEveApiXml($result);
        $this->__destruct();
        return $this;
    }
    /**
     * @param ClientInterface|null $value
     *
     * @return self
     */
    public function setClient(ClientInterface $value = null)
    {
        $this->client = $value;
        return $this;
    }
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
    /**
     * @type ClientInterface
     */
    protected $client;
    /**
     * @type LoggerInterface
     */
    protected $logger;
    /**
     * @return ClientInterface
     */
    protected function getClient()
    {
        return $this->client;
    }
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    /**
     * @param EveApiReadInterface $data
     *
     * @return \Guzzle\Http\Message\EntityEnclosingRequestInterface
     */
    protected function prepareConnection(EveApiReadInterface $data)
    {
        $uri = array(
            '/{EveApiSectionName}/{EveApiName}.xml.aspx',
            array(
                'EveApiSectionName' => $data->getEveApiSectionName(),
                'EveApiName' => $data->getEveApiName()
            )
        );
        $client = $this->getClient();
        return $client->post($uri, null, $data->getEveApiArguments());
    }
    /**
     * @param RequestInterface $request
     *
     * @return string|bool
     */
    protected function readXmlData(RequestInterface $request)
    {
        try {
            $response = $request->send();
        } catch (RequestException $exc) {
            $mess = 'Could NOT get XML data';
            $this->getLogger()
                ->debug($mess, array('exception' => $exc));
            return false;
        }
        return $response->getBody(true);
    }
}
