<?php
/**
 * Contains NetworkConnection class.
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
namespace Yapeal\Network;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Yapeal\Dependency\DependenceInterface;

/**
 * Wrapper for API network connection.
 */
class NetworkConnection implements NetworkInterface
{
    /**
     * Constructor
     *
     * @param DependenceInterface|null    $dependence
     * @param ClientInterface|string|null $client
     * @param LoggerInterface|string|null $logger
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        DependenceInterface $dependence = null,
        ClientInterface $client = null,
        LoggerInterface $logger = null
    ) {
        $this->setDependence($dependence);
        $this->setLogger($logger);
        $this->setClient($client);
    }
    /**
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @return ClientInterface
     */
    public function getClient()
    {
        if (empty($this->client)) {
            $mess = 'Tried to use $client when it was NOT set';
            throw new \LogicException($mess);
        } elseif (is_string($this->client)) {
            $dependence = $this->getDependence();
            $this->setClient($dependence[(string)$this->client]);
        }
        if (!$this->client instanceof ClientInterface) {
            $mess = '$client could NOT be resolved to instance of'
                . ' ClientInterface is instead ' . gettype($this->client);
            throw new \InvalidArgumentException($mess);
        }
        return $this->client;
    }
    /**
     * @throws \LogicException
     * @return DependenceInterface
     */
    public function getDependence()
    {
        if (empty($this->dependence)) {
            $mess = 'Tried to use $dependence when it was NOT set';
            throw new \LogicException($mess);
        }
        return $this->dependence;
    }
    /**
     * @throws \DomainException
     * @throws \LogicException
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (empty($this->logger)) {
            $mess = 'Tried to use $logger when it was NOT set';
            throw new \LogicException($mess);
        } elseif (is_string($this->logger)) {
            $dependence = $this->getDependence();
            $this->setLogger($dependence[(string)$this->logger]);
        }
        if (!$this->logger instanceof LoggerInterface) {
            $mess = '$logger could NOT be resolved to instance of'
                . ' LoggerInterface is instead ' . gettype($this->logger);
            throw new \InvalidArgumentException($mess);
        }
        return $this->logger;
    }
    /**
     * Will retrieve the XML from API server.
     *
     * @param string $api      API needed.
     * @param string $section  Section API belongs to.
     * @param array  $postList A list of data that will be passed to the API
     *                         server. Example:
     *                         array(UserID => '123', apiKey => 'abc123', ...)
     *
     * @return string|false Returns XML data from API or FALSE for any
     * connection error.
     */
    public function retrieveEveApiXml($api, $section, $postList)
    {
        $uri = array(
            '/{section}/{api}.xml.aspx',
            array(
                'section' => $section,
                'api' => $api
            )
        );
        $request = $this->getClient()
                        ->setConfig(
                            array('defaults' => $this->getXmlClientDefaults())
                        )
                        ->setBaseUrl('https://api.eveonline.com')
                        ->post($uri, null, $postList);
        try {
            $response = $request->send();
        } catch (RequestException $exc) {
            $mess =
                $exc->getMessage() . ' for API /' . $section . '/' . $api
                . '.xml.aspx';
            $this->getLogger()
                 ->notice($mess);
            return false;
        }
        return $response->getBody(true);
    }
    /**
     * @param ClientInterface $value
     *
     * @return self
     */
    public function setClient(ClientInterface $value = null)
    {
        if (is_string($value)) {
            $dependence = $this->getDependence();
            if (empty($dependence[$value])) {
                $mess = 'Dependence container does NOT contain ' . $value;
                throw new \DomainException($mess);
            }
        }
        $this->client = $value;
        return $this;
    }
    /**
     * @param DependenceInterface|null $value
     *
     * @return self
     */
    public function setDependence(DependenceInterface $value = null)
    {
        $this->dependence = $value;
        return $this;
    }
    /**
     * @param LoggerInterface $value
     *
     * @return self
     */
    public function setLogger(LoggerInterface $value = null)
    {
        if (is_string($value)) {
            $dependence = $this->getDependence();
            if (empty($dependence[$value])) {
                $mess = 'Dependence container does NOT contain ' . $value;
                throw new \DomainException($mess);
            }
        }
        $this->logger = $value;
        return $this;
    }
    /**
     * @var ClientInterface Holds API connection.
     */
    protected $client;
    /**
     * @var DependenceInterface
     */
    protected $dependence;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @return array
     */
    protected function getXmlClientDefaults()
    {
        $headers = array(
            'Accept' => 'text/xml,application/xml,application/xhtml+xml;'
                . 'q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;'
                . 'q=0.5',
            'Accept-Charset' => 'utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6',
            'Accept-Encoding' => 'gzip',
            'Accept-Language' => 'en-us;q=0.9,en;q=0.8,*;q=0.7',
            'Connection' => 'Keep-Alive',
            'Keep-Alive' => '300',
            'User-Agent' => YAPEAL_APPLICATION_AGENT
        );
        $defaults = array(
            'headers' => $headers,
            'timeout' => 10,
            'connect_timeout' => 30,
            'verify' =>
                dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'eveonline.crt',
        );
        return $defaults;
    }
}

