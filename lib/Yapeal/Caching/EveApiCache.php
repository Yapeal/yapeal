<?php
/**
 * Contains EveApiCache class.
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
namespace Yapeal\Caching;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Util\CachedInterval;
use Yapeal\Util\CachedUntil;

/**
 * Class used to manage caching of XML from Eve APIs.
 *
 * @package    Yapeal
 */
class EveApiCache implements EveApiCacheInterface, LoggerAwareInterface
{
    /**
     * @var string Value from [Cache] section for cache_output.
     */
    private static $cacheOutput = 'file';
    /**
     * @var string Name of the Eve API being cached.
     */
    private $api;
    /**
     * @var integer Cache interval for this API.
     */
    private $cacheInterval;
    /**
     * @var EveApiCacheInterface[]
     */
    private $drivers;
    /**
     * @var string Holds SHA1 hash of $section, $api, $postParams.
     */
    private $hash = '';
    /**
     * @var LoggerInterface Holds instance of logger.
     */
    private $logger;
    /**
     * @var string Holds the ownerID to be used when updating cachedUntil table.
     */
    private $ownerId = 0;
    /**
     * @var array The list of any required params used in getting API.
     */
    private $postParams;
    /**
     * @var string The api section that $api belongs to.
     */
    private $section;
    /**
     * Constructor
     *
     * @param string               $api        Name of the Eve API being cached.
     * @param string               $section    The api section that $api belongs to. For Eve
     *                                         APIs will be one of account, char, corp, eve, map, or server.
     * @param string|int           $owner      Owner for current Eve API being cached. This maybe
     *                                         empty for some APIs i.e. eve, map, and server.
     * @param array                $postParams The list of required params used in getting API.
     *                                         This maybe empty for some APIs i.e. eve, map, and server.
     * @param CachedInterval|null  $ci
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        $api,
        $section,
        $owner = 0,
        $postParams = array(),
        CachedInterval $ci = null,
        LoggerInterface $logger = null
    ) {
        $this->setLogger($logger);
        $this->setApi($api);
        $this->setCacheInterval($this->getDefaultCacheInterval($ci));
        $this->setHash(
            $this->getDefaultHash($api, $section, $owner, $postParams)
        );
        $this->setOwnerId($owner);
        $this->setPostParams($postParams);
        $this->setSection($section);
        $this->setDrivers($this->getDefaultDrivers());
    }
    /**
     * function used to set constants from [Cache] section of the configuration file.
     *
     * @param array $section A list of settings for this section of configuration.
     */
    public static function setCacheSectionProperties(array $section)
    {
        self::$cacheOutput = $section['cache_output'];
    }
    /**
     * @param EveApiCacheInterface $driver
     *
     * @return self Returns self to allow fluid interface.
     */
    public function addDriver(EveApiCacheInterface $driver)
    {
        if (!empty($this->drivers) && in_array($driver, $this->drivers, true)) {
            $mess = 'Ignoring duplicate driver ' . get_class($driver);
            $this->logger->log(LogLevel::WARNING, $mess);
        }
        $this->drivers[] = $driver;
        return $this;
    }
    /**
     * Function used to save API XML to cache database table and/or file and
     * update utilCachedUntil table.
     *
     * @param string      $xml The Eve API XML to be cached.
     * @param CachedUntil $cu
     * @param \XMLReader  $xr
     *
     * @throws YapealApiErrorException Throws YapealApiErrorException if xml
     *                                 contains Eve API error.
     * @return bool Returns TRUE if XML was cached, FALSE otherwise.
     *
     */
    public function cacheXml(
        $xml,
        CachedUntil $cu = null,
        \XMLReader $xr = null
    ) {
        if (empty($xml)) {
            $mess = 'XML was empty' . PHP_EOL;
            $this->logger->log(LogLevel::WARNING, $mess);
            return false;
        }
        $data = array(
            'api' => $this->api,
            'ownerID' => $this->ownerId,
            'section' => $this->section
        );
        if (is_null($cu)) {
            $cu = new CachedUntil($data);
        }
        /*
         * Update utilCachedUntil table with default short cache date/time. This
         * is changed when there is an API error that returns a different one or
         * normally is set to the new calculated date/time if there aren't any
         * errors.
         */
        $cu->cachedUntil = YAPEAL_START_TIME;
        $cu->store();
        if (false === $this->isValidXml($xml, $xr)) {
            $mess =
                'Caching invalid API XML for '
                . $this->section
                . DS
                . $this->api;
            $this->logger->log(LogLevel::NOTICE, $mess);
        }
        $apiError = $this->getXmlError($xml, $xr);
        // Throw exception for any API errors.
        if (false !== $apiError) {
            throw new YapealApiErrorException($apiError['message'], $apiError['code']);
        }
        $until = time() + $this->cacheInterval;
        /*
         * Add random number of seconds to cache interval. Randomness is larger
         * the later in the day it is. Between 0 and 1 + 0 .. 23 hours * 10
         * seconds added.
         */
        $until += mt_rand(0, ((1 + (int)gmdate("G")) * 10));
        // Use now + interval + random value to update cachedUntil.
        $cu->cachedUntil = gmdate('Y-m-d H:i:s', $until);
        $cu->store();
        if (!empty($this->drivers)) {
            foreach ($this->drivers as $driver) {
                $driver->cacheXml($xml);
            }
        }
        return true;
    }
    /**
     * Used to delete any cached XML.
     *
     * @return self Returns self to allow fluid interface.
     */
    public function deleteCachedXml()
    {
        if (!empty($this->drivers)) {
            foreach ($this->drivers as $driver) {
                $driver->deleteCachedXml();
            }
        }
        return $this;
    }
    /**
     * Used to fetch API XML from database table and/or file.
     *
     * @param \XMLReader|null $xr
     *
     * @return string|false Returns XML string if cached copy is available and
     * NOT expired, else returns FALSE.
     */
    public function getCachedXml(\XMLReader $xr = null)
    {
        if (is_null($xr)) {
            $xr = new \XMLReader();
        }
        $xml = false;
        if (!empty($this->drivers)) {
            foreach ($this->drivers as $driver) {
                $xml = $driver->getCachedXml();
                if (false === $xml) {
                    continue;
                }
                if (false === $this->isValidXml($xml, $xr)) {
                    $xml = false;
                    continue;
                }
                $currentTimeXml = $this->getXmlCurrentTime($xml, $xr);
                if (false === $currentTimeXml) {
                    $xml = false;
                    continue;
                }
                $currentTimeXml =
                    strtotime($currentTimeXml . ' +0000')
                    + $this->cacheInterval;
                if (time() > $currentTimeXml) {
                    $driver->deleteCachedXml();
                    $xml = false;
                    continue;
                }
            }
        }
        return $xml;
    }
    /**
     * @param CachedInterval|null $ci
     *
     * @return int
     */
    public function getDefaultCacheInterval(CachedInterval $ci = null)
    {
        if (is_null($ci)) {
            $ci = new CachedInterval();
        }
        return $ci->getInterval($this->api, $this->section);
    }
    /**
     * @return array
     */
    public function getDefaultDrivers()
    {
        $drivers = array();
        switch (self::$cacheOutput) {
            case 'both':
                $drivers[] =
                    new EveApiDatabaseCache($this->api, $this->hash, $this->section, $this->logger);
                $drivers[] =
                    new EveApiFileSystemCache($this->api, $this->hash, $this->section, $this->logger);
                break;
            case 'database':
                $drivers[] =
                    new EveApiDatabaseCache($this->api, $this->hash, $this->section, $this->logger);
                break;
            case 'file':
                $drivers[] =
                    new EveApiFileSystemCache($this->api, $this->hash, $this->section, $this->logger);
                break;
            case 'none':
                break;
            default:
                $mess =
                    'Invalid value of "'
                    . self::$cacheOutput
                    . '" for cache_output. Check that the setting in'
                    . ' yapeal.(ini, json) is correct.';
                $this->logger->log(LogLevel::CRITICAL, $mess);
        }
        return $drivers;
    }
    /**
     * @param $api
     * @param $section
     * @param $owner
     * @param $postParams
     *
     * @return string
     */
    public function getDefaultHash($api, $section, $owner, $postParams)
    {
        $params = $section . $api . $owner;
        if (!empty($postParams)) {
            foreach ($postParams as $k => $v) {
                $params .= $k . '=' . $v;
            }
        }
        return hash('sha1', $params);
    }
    /**
     * @param string     $xml
     * @param \XMLReader $xr
     *
     * @return string|false
     */
    public function getXmlCurrentTime($xml, \XMLReader $xr)
    {
        $result = false;
        $xr->XML($xml);
        while (@$xr->read()) {
            if ($xr->nodeType == $xr::ELEMENT
                && $xr->localName == 'currentTime'
            ) {
                $xr->read();
                $result = (string)$xr->value;
                break;
            }
        }
        return $result;
    }
    /**
     * @param string     $xml
     * @param \XMLReader $xr
     *
     * @return array|false
     */
    public function getXmlError($xml, \XMLReader $xr)
    {
        $result = false;
        $xr->XML($xml);
        while (@$xr->read()) {
            if ($xr->nodeType == $xr::ELEMENT && $xr->localName == 'error') {
                $result = array();
                // API error returned.
                // See if error code attribute is available.
                if (true === $xr->hasAttributes) {
                    $result['code'] = (int)$xr->getAttribute('code');
                }
                // Move to message text.
                $xr->read();
                $result['message'] = (string)$xr->value;
                break;
            }
        }
        return $result;
    }
    /**
     * Used to validate structure of Eve API XML file.
     *
     * @param string          $xml        XML to be validated.
     * @param \XMLReader|null $xr
     * @param string          $xsdBaseDir Base directory where the per api XSD files can be found.
     *
     * @return bool Return TRUE if the XML validates.
     */
    public function isValidXml(
        $xml,
        \XMLReader $xr = null,
        $xsdBaseDir = __DIR__
    ) {
        if (!is_dir($xsdBaseDir)) {
            $mess = 'Could NOT find or was not a directory ' . $xsdBaseDir;
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        }
        // Check for HTML errors.
        if (false !== strpos($xml, '<!DOCTYPE html')) {
            $mess =
                'API returned HTML error page. Check to make sure API '
                . $this->section
                . DS
                . $this->api
                . ' is a valid API.';
            $this->logger->log(LogLevel::WARNING, $mess);
            return false;
        }
        if (is_null($xr)) {
            $xr = new \XMLReader();
        }
        // Pass XML data to XMLReader so it can be checked.
        $xr->XML($xml);
        // Need to look for XSD Schema for this API to use while validating.
        // Build W3C Schema file name
        $cacheFile =
            $xsdBaseDir . '/' . $this->section . '/' . $this->api . '.xsd';
        // Can not use schema if it is missing.
        if (!is_file($cacheFile)) {
            $mess = 'Missing schema file ' . $cacheFile;
            $this->logger->log(LogLevel::WARNING, $mess);
            $cacheFile = $xsdBaseDir . '/unknown.xsd';
        }
        // Have to have a good schema.
        if (!$xr->setSchema($cacheFile)) {
            $mess = 'Could not load schema file ' . $cacheFile;
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        }
        // Scan through API XML.
        while (@$xr->read()) {
        }
        $isValid = (boolean)$xr->isValid();
        $xr->close();
        return $isValid;
    }
    /**
     * @param string $api
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setApi($api)
    {
        $this->api = (string)$api;
        return $this;
    }
    /**
     * @param int $cacheInterval
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setCacheInterval($cacheInterval)
    {
        $this->cacheInterval = (int)$cacheInterval;
        return $this;
    }
    /**
     * Used to (re)set all cache drivers at once.
     *
     * @param EveApiCacheInterface[] $drivers
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setDrivers(array $drivers = array())
    {
        $this->drivers = array();
        if (!empty($drivers)) {
            foreach ($drivers as $driver) {
                if ($driver instanceof EveApiCacheInterface) {
                    $this->addDriver($driver);
                } else {
                    $mess =
                        'Class '
                        . get_class($driver)
                        . ' does NOT implement EveApiCacheInterface'
                        . ' and could NOT be added as a driver';
                    $this->logger->log(LogLevel::ERROR, $mess);
                }
            }
        }
        return $this;
    }
    /**
     * @param string $hash
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface|null $logger
     *
     * @return null|void
     */
    public function setLogger(LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;
    }
    /**
     * @param string|int $ownerId
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setOwnerId($ownerId = 0)
    {
        $this->ownerId = (int)$ownerId;
        return $this;
    }
    /**
     * @param string[] $postParams
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setPostParams(array $postParams = array())
    {
        $this->postParams = $postParams;
        return $this;
    }
    /**
     * @param string $section
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }
}
