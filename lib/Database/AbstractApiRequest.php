<?php
/**
 * Contains abstract ApiRequest class.
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

use Psr\Log\LoggerInterface;
use XMLReader;
use Yapeal\Caching\EveApiXmlCache;
use Yapeal\Dependency\DependenceInterface;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Network\NetworkConnection;
use Yapeal\Network\NetworkInterface;

/**
 * Abstract class to hold common methods for API classes.
 */
abstract class AbstractApiRequest
{
    /**
     * Constructor
     *
     * @param DependenceInterface|null     $dependence
     * @param EveApiXmlCache|string|null   $cache
     * @param LoggerInterface|string|null  $logger
     * @param NetworkInterface|string|null $network
     * @param XMLReader|string|null        $reader
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        DependenceInterface $dependence = null,
        EveApiXmlCache $cache = null,
        LoggerInterface $logger = null,
        NetworkInterface $network = null,
        XMLReader $reader = null
    ) {
        $this->setDependence($dependence);
        $this->setLogger($logger);
        $this->setCache($cache);
        $this->setNetwork($network);
        $this->setReader($reader);
    }
    /**
     * Used to store XML to MySQL table(s).
     *
     * @throws \LogicException
     * @return Bool Return TRUE if store was successful.
     */
    public function apiStore()
    {
        try {
            // Get valid cached copy if there is one.
            $result = $cache->getCachedApi();
            // If XML is not cached need to try to get it from API server or proxy.
            if (false === $result) {
                $proxy = $this->getProxy();
                $con = new NetworkConnection(null, null, $this->logger);
                $result = $con->retrieveEveApiXml(
                    $this->api,
                    $this->section,
                    $this->params
                );
                // FALSE means there was an error and it has already been report just
                // need to return to caller.
                if (false === $result) {
                    return false;
                }
                // Cache the received result.
                $cache->cacheXml($result);
            }
            if ($this->prepareTables() !== true) {
                $mess = 'Could not prepare ' . $this->section . $this->api;
                $mess .= ' API tables to accept new data for '
                    . $this->ownerID;
                \Logger::getLogger('yapeal')
                       ->warn($mess);
            }
            // Create XMLReader.
            $this->reader = new XMLReader();
            // Pass XML to reader.
            $this->reader->XML($result);
            // Outer structure of XML is processed here.
            while ($this->reader->read()) {
                if ($this->reader->nodeType == XMLReader::ELEMENT
                    && $this->reader->localName == 'result'
                ) {
                    $result = $this->parserAPI();
                }
            }
            return $result;
        } catch (YapealApiErrorException $e) {
            // Any API errors that need to be handled in some way are handled in this
            // function.
            $this->handleApiError($e);
            return false;
        } catch (\ADODB_Exception $e) {
            // Catch any uncaught ADOdb exceptions here.
            $mess = 'Uncaught ADOdb exception' . PHP_EOL;
            \Logger::getLogger('yapeal')
                   ->warn($mess);
            return false;
        }
    }
    /**
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @return EveApiXmlCache
     */
    public function getCache()
    {
        if (empty($this->cache)) {
            $mess = 'Tried to use $cache when it was NOT set';
            throw new \LogicException($mess);
        } elseif (is_string($this->cache)) {
            $dependence = $this->getDependence();
            $this->setCache($dependence[(string)$this->cache]);
        }
        if (!$this->cache instanceof EveApiXmlCache) {
            $mess = '$cache could NOT be resolved to instance of'
                . ' EveApiXmlCache is instead ' . gettype($this->cache);
            throw new \InvalidArgumentException($mess);
        }
        return $this->cache;
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
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @return NetworkInterface
     */
    public function getNetwork()
    {
        if (empty($this->network)) {
            $mess = 'Tried to use $network when it was NOT set';
            throw new \LogicException($mess);
        } elseif (is_string($this->network)) {
            $dependence = $this->getDependence();
            $this->setCache($dependence[(string)$this->network]);
        }
        if (!$this->network instanceof EveApiXmlCache) {
            $mess = '$network could NOT be resolved to instance of'
                . ' NetworkInterface is instead ' . gettype($this->network);
            throw new \InvalidArgumentException($mess);
        }
        return $this->network;
    }
    /**
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @return XMLReader
     */
    public function getReader()
    {
        if (empty($this->reader)) {
            $mess = 'Tried to use $reader when it was NOT set';
            throw new \LogicException($mess);
        } elseif (is_string($this->reader)) {
            $dependence = $this->getDependence();
            $this->setCache($dependence[(string)$this->reader]);
        }
        if (!$this->reader instanceof EveApiXmlCache) {
            $mess = '$reader could NOT be resolved to instance of'
                . ' XMLReader is instead ' . gettype($this->reader);
            throw new \InvalidArgumentException($mess);
        }
        return $this->reader;
    }
    /**
     * @param EveApiXmlCache $value
     *
     * @return self
     */
    public function setCache(EveApiXmlCache $value = null)
    {
        if (is_string($value)) {
            $dependence = $this->getDependence();
            if (empty($dependence[$value])) {
                $mess = 'Dependence container does NOT contain ' . $value;
                throw new \DomainException($mess);
            }
        }
        $this->cache = $value;
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
     * @param NetworkInterface $value
     *
     * @return self
     */
    public function setNetwork(NetworkInterface $value = null)
    {
        if (is_string($value)) {
            $dependence = $this->getDependence();
            if (empty($dependence[$value])) {
                $mess = 'Dependence container does NOT contain ' . $value;
                throw new \DomainException($mess);
            }
        }
        $this->network = $value;
        return $this;
    }
    /**
     * @param XMLReader $value
     *
     * @return self
     */
    public function setReader(XMLReader $value = null)
    {
        if (is_string($value)) {
            $dependence = $this->getDependence();
            if (empty($dependence[$value])) {
                $mess = 'Dependence container does NOT contain ' . $value;
                throw new \DomainException($mess);
            }
        }
        $this->reader = $value;
        return $this;
    }
    /**
     * @var string Holds the name of the API. Normally set in constructor of the
     * final derived instance class.
     */
    protected $api;
    /**
     * @var EveApiXmlCache Holds API connection.
     */
    protected $cache;
    /**
     * @var DependenceInterface
     */
    protected $dependence;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var NetworkInterface
     */
    protected $network;
    /**
     * @var string Holds the ownerID to be used when updating cachedUntil table.
     */
    protected $ownerID = 0;
    /**
     * @var array Holds the required parameters like keyID, vCode, etc used in
     * HTML POST parameters to API servers which varies depending on API 'section'
     * being requested.
     */
    protected $params;
    /**
     * @var XMLReader Holds instance of XMLReader.
     */
    protected $reader;
    /**
     * @var string Holds the API section name. Normally set in constructor of the
     * final derived instance class.
     */
    protected $section;
    /**
     * Version of sprintf for cases where named arguments are desired (php syntax)
     *
     * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
     *
     * with sprintfn: sprintfn('second: %second$s ; first: %first$s', array(
     *  'first' => '1st',
     *  'second'=> '2nd'
     * ));
     * Original idea taken from post by nate at frickenate dot com which can be
     * found in
     * {@link http://us.php.net/manual/en/function.sprintf.php#94608 sprinf description}
     *
     * @param string $format sprintf format string, with any number of named
     *                       arguments.
     * @param array  $args   array of [ 'arg_name' => 'arg value', ... ] replacements
     *                       to be made.
     *
     * @return mixed Returns result of sprintf call, or FALSE on error.
     */
    protected static function sprintfn($format, array $args = array())
    {
        // Mapping of argument names to their corresponding sprintf numeric argument
        // value.
        $arg_nums =
            array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);
        // Find the next named argument. Each search starts at the end of the
        // previous replacement.
        for ($pos = 0;
            preg_match(
                '/(?<=%)([a-zA-Z_]\w*)(?=\$)/',
                $format,
                $match,
                PREG_OFFSET_CAPTURE,
                $pos
            );
            $pos = $arg_pos + strlen($replace)) {
            $arg_pos = $match[0][1];
            $arg_len = strlen($match[0][0]);
            $arg_key = $match[1][0];
            // Programmer did not supply a value for the named argument found in the
            // format string.
            if (!array_key_exists($arg_key, $arg_nums)) {
                $mess = 'Missing argument "' . $arg_key . '"' . PHP_EOL;
                \Logger::getLogger('yapeal')
                       ->warn($mess);
                return false;
            }
            // Replace the named argument with the corresponding numeric one.
            $replace = $arg_nums[$arg_key];
            $format = substr_replace($format, $replace, $arg_pos, $arg_len);
            // Skip to end of replacement for next iteration.
            // Moved this into for loop increment where it belonged.
            //$pos = $arg_pos + strlen($replace);
        }
        return vsprintf($format, array_values($args));
    }
    /**
     * Method used to get network connection.
     *
     * @return NetworkConnection
     */
    abstract protected function getNetworkConnection();
    /**
     * Abstract per API section function that returns API proxy.
     *
     * @return mixed Returns the URL for proxy as string if found else FALSE.
     */
    abstract protected function getProxy();
    /**
     * Abstract method to handles some Eve API error codes in special ways.
     *
     * Normally implemented in abstract section class that extends this class.
     *
     * @param object $e Eve API exception returned.
     *
     * @return bool Returns TRUE if handled the error else FALSE.
     */
    abstract protected function handleApiError($e);
    /**
     * Method used to determine if Need to use upsert or insert for API.
     *
     * @return bool
     */
    protected function needsUpsert()
    {
        return true;
    }
    /**
     * Simple <rowset> per API parser for XML.
     *
     * Most common API style is a simple <rowset>. This implementation allows most
     * API classes to be empty except for a constructor which sets $this->api and
     * calls their parent constructor.
     *
     * @throws \LogicException
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert($this->needsUpsert());
        if ($this->ownerID != 0) {
            $qb->setDefault('ownerID', $this->ownerID);
        }
        try {
            while ($this->reader->read()) {
                switch ($this->reader->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->reader->localName) {
                            case 'row':
                                $row = array();
                                // Walk through attributes and add them to row.
                                while ($this->reader->moveToNextAttribute()) {
                                    $row[$this->reader->name] =
                                        $this->reader->value;
                                }
                                $qb->addRow($row);
                                break;
                        };
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->reader->localName == 'result') {
                            // Insert any leftovers.
                            if (count($qb) > 0) {
                                $qb->store();
                            }
                            $qb = null;
                            return true;
                        }
                        break;
                }
            }
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                   ->warn($e);
            return false;
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
    /**
     * Method used to prepare database table(s) before parsing API XML data.
     *
     * If there is any need to delete records or empty tables before parsing XML
     * and adding the new data this method should be used to do so by overriding
     * it in extending class.
     *
     * @return bool Will return TRUE if table(s) were prepared correctly.
     */
    protected function prepareTables()
    {
        return true;
    }
}

