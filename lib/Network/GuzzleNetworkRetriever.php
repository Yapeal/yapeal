<?php
/**
 * Contains GuzzleNetworkRetriever class.
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
namespace Yapeal\Network;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Yapeal\Container\ContainerInterface;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class GuzzleNetworkRetriever
 *
 * @author Stephen Gulick <stephenmg12@gmail.com>
 */
class GuzzleNetworkRetriever
{
    /**
     * @param ClientInterface|null $client
     */
    public function __construct(ClientInterface $client = null)
    {
        $this->setClient($client);
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'Yapeal.EveApi.retrieve' => [
                'eveApiRetrieve',
                -PHP_INT_MAX
            ]
        ];
        return $events;
    }
    /**
     * @inheritdoc
     */
    public static function injectCallable(ContainerInterface $dic)
    {
        $class = __CLASS__;
        $serviceName = str_replace('\\', '.', $class);
        $dic[$serviceName] = function () use ($dic, $class) {
            /**
             * @type GuzzleNetworkRetriever $callable
             */
            $callable = new $class($dic['Yapeal.Network.Client']);
            return $callable;
        };
        return $serviceName;
    }
    /**
     *
     */
    public function __destruct()
    {
    }
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws \LogicException
     */
    public function eveApiRetrieve(
        EveApiEventInterface $event,
        $eventName,
        EventMediatorInterface $yem
    ) {
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s in %4$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            __CLASS__
        );
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $mess = sprintf(
            'Started network retrieve for %1$s/%2$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $uri = [
            '/{EveApiSectionName}/{EveApiName}.xml.aspx',
            [
                'EveApiSectionName' => strtolower($data->getEveApiSectionName()),
                'EveApiName'        => $data->getEveApiName()
            ]
        ];
        try {
            $response = $this->getClient()
                             ->post($uri, ['body' => $data->getEveApiArguments()]);
        } catch (RequestException $exc) {
            $mess = sprintf(
                'Could NOT get XML data for %1$s/%2$s',
                $data->getEveApiSectionName(),
                $data->getEveApiName()
            );
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess, ['exception' => $exc]);
            return $event;
        }
        $body = (string)$response->getBody();
        if ('' === $body) {
            $mess = sprintf(
                'Body empty for %1$s/%2$s',
                $data->getEveApiSectionName(),
                $data->getEveApiName()
            );
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        }
        $data->setEveApiXml(
            $this->addYapealProcessingInstructionToXml(
                $body,
                $data->getEveApiArguments()
            )
        );
        $this->__destruct();
        $mess = sprintf(
            'Finished %1$s event for %2$s/%3$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $yem->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        return $event->setData($data)
                     ->eventHandled();
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
     * @param string   $xml
     * @param string[] $arguments
     *
     * @return string
     */
    protected function addYapealProcessingInstructionToXml(
        $xml,
        array $arguments
    ) {
        if ($xml === false) {
            return $xml;
        }
        if (!empty($arguments['vCode'])) {
            $arguments['vCode'] = substr($arguments['vCode'], 0, 8) . '...';
        }
        $json = json_encode($arguments);
        return str_replace(
            ["encoding='UTF-8'?>\r\n", "encoding='UTF-8'?>\n"],
            [
                "encoding='UTF-8'?>\r\n<?yapeal.parameters.json " . $json . "?>\r\n",
                "encoding='UTF-8'?>\n<?yapeal.parameters.json " . $json . "?>\n"
            ],
            $xml
        );
    }
    /**
     * @return ClientInterface
     */
    protected function getClient()
    {
        return $this->client;
    }
    /**
     * @type ClientInterface $client
     */
    protected $client;
}
