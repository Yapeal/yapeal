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
 * @copyright  Copyright (c) 2008-2013, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Caching;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Yapeal\Database\DatabaseConnection;
use Yapeal\Database\QueryBuilder;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Util\CachedInterval;
use Yapeal\Util\CachedUntil;

/**
 * Class used to manage caching of XML from Eve APIs.
 *
 * @package    Yapeal
 */
class EveApiCache implements LoggerAwareInterface
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
        $this->calculateHash($api, $section, $owner, $postParams);
        $this->setApi($api);
        $this->setDefaultCacheInterval($ci);
        $this->setLogger($logger);
        $this->setOwnerId($owner);
        $this->setPostParams($postParams);
        $this->setSection($section);
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
        if (false === $this->validateXml($xml, $xr)) {
            $mess =
                'Caching invalid API XML for '
                . $this->section
                . DS
                . $this->api;
            $this->logger->log(LogLevel::NOTICE, $mess);
        }
        $apiError = $this->gotXmlError($xml, $xr);
        // Throw exception for any API errors.
        if ($apiError !== false) {
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
        switch (self::$cacheOutput) {
            case 'both':
                $this->cacheXmlToDatabase($xml);
                $this->cacheXmlFile($xml);
                break;
            case 'database':
                $this->cacheXmlToDatabase($xml);
                break;
            case 'file':
                $this->cacheXmlFile($xml);
                break;
            case 'none':
                break;
            default:
                $mess =
                    'Invalid value of "'
                    . self::$cacheOutput
                    . '" for cache_output. Check that the setting in'
                    . ' yapeal.(ini, json) is correct.';
                $this->logger->log(LogLevel::ERROR, $mess);
                return false;
        }
        return true;
    }
    /**
     * Used to delete any cached XML.
     *
     * @param \ADOConnection|null $con
     *
     * @return self Returns self to allow fluid interface.
     */
    public function deleteCachedApi(\ADOConnection $con = null)
    {
        switch (self::$cacheOutput) {
            case 'both':
                $this->deleteCachedXmlFromDatabase($con);
                $this->deleteCachedXmlFile();
                break;
            case 'database':
                $this->deleteCachedXmlFromDatabase($con);
                break;
            case 'file':
                $this->deleteCachedXmlFile();
                break;
            default:
                $mess =
                    'Invalid value of "'
                    . self::$cacheOutput
                    . '" for cache_output. Check that the setting in'
                    . ' yapeal.(ini, json) is correct.';
                $this->logger->log(LogLevel::ERROR, $mess);
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
    public function getCachedApi(\XMLReader $xr = null)
    {
        switch (self::$cacheOutput) {
            case 'both':
                $xml = $this->getCachedDatabase();
                // If not cached in DB try file.
                if (false === $xml) {
                    $xml = $this->getCachedFile();
                    // If XML was cached to file but not to database add it to database.
                    if ($xml !== false) {
                        $this->cacheXmlToDatabase($xml);
                    }
                }
                break;
            case 'database':
                $xml = $this->getCachedDatabase();
                break;
            case 'file':
                $xml = $this->getCachedFile();
                break;
            case 'none':
                return false;
            default:
                $mess =
                    'Invalid value of "'
                    . self::$cacheOutput
                    . '" for cache_output. Check that the setting in'
                    . ' yapeal.(ini, json) is correct.';
                $this->logger->log(LogLevel::ERROR, $mess);
                return false;
        }
        if ($xml === false) {
            return false;
        }
        if (is_null($xr)) {
            $xr = new \XMLReader();
        }
        if (false === $this->validateXml($xml, $xr)) {
            return false;
        }
        $currentTimeXml = $this->getXmlCurrentTime($xml, $xr);
        if ($currentTimeXml === false) {
            return false;
        }
        $currentTimeXml =
            strtotime($currentTimeXml . ' +0000') + $this->cacheInterval;
        // If already past cachedUntil datetime need to get XML again.
        if (time() > $currentTimeXml) {
            return false;
        }
        return $xml;
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
     * @param string $cacheInterval
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setCacheInterval($cacheInterval)
    {
        $this->cacheInterval = $cacheInterval;
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
    /**
     * Used to validate structure of Eve API XML file.
     *
     * @param string          $xml        XML to be validated.
     * @param \XMLReader|null $xr
     * @param string          $xsdBaseDir Base directory where the per api XSD files can be found.
     *
     * @return bool Return TRUE if the XML validates.
     */
    public function validateXml(
        $xml,
        \XMLReader $xr = null,
        $xsdBaseDir = __DIR__
    ) {
        if (!is_dir($xsdBaseDir)) {
            $mess = 'Could NOT find or was not a directory ' . $xsdBaseDir;
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        }
        if (is_null($xr)) {
            $xr = new \XMLReader();
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
     * Used to save API XML into file.
     *
     * @param string $xml The Eve API XML to be cached.
     */
    private function cacheXmlFile($xml)
    {
        $cachePath = $this->getCacheDirPath();
        if (false === $cachePath) {
            return;
        }
        $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
        $ret = file_put_contents($cacheFile, $xml);
        if (false == $ret || $ret == -1) {
            $mess = 'Could not cache XML to ' . $cacheFile;
            $this->logger->log(LogLevel::ERROR, $mess);
        }
    }
    /**
     * Function used to save API XML into database table.
     *
     * @param string $xml The Eve API XML to be cached.
     */
    private function cacheXmlToDatabase($xml)
    {
        try {
            // Get a new query instance.
            $qb = new QueryBuilder(YAPEAL_TABLE_PREFIX
                . 'utilXmlCache', YAPEAL_DSN, false);
            $row = array(
                'api' => $this->api,
                'hash' => $this->hash,
                'section' => $this->section,
                'xml' => $xml
            );
            $qb->addRow($row);
            $qb->store();
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::WARNING, $e->getMessage());
            $mess = 'Could NOT cache XML to database';
            $this->logger->log(LogLevel::ERROR, $mess);
        }
    }
    /**
     * @param $api
     * @param $section
     * @param $owner
     * @param $postParams
     */
    private function calculateHash($api, $section, $owner, $postParams)
    {
        $params = $section . $api . $owner;
        if (!empty($postParams)) {
            foreach ($postParams as $k => $v) {
                $params .= $k . '=' . $v;
            }
        }
        $this->hash = hash('sha1', $params);
    }
    /**
     * Used to delete any cached XML from file.
     *
     * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
     */
    private function deleteCachedXmlFile()
    {
        $cachePath = $this->getCacheDirPath();
        if (false === $cachePath) {
            return false;
        }
        $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
        if (!file_exists($cacheFile) || !is_file($cacheFile)) {
            return false;
        }
        return @unlink($cacheFile);
    }
    /**
     * Used to delete any cached XML from database.
     *
     * @param \ADOConnection|null $con
     *
     * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
     */
    private function deleteCachedXmlFromDatabase(\ADOConnection $con = null)
    {
        try {
            if (is_null($con)) {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            }
            $sql = 'delete from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
            $sql .= ' where';
            $sql .= ' `hash`=' . $con->qstr($this->hash);
            $con->Execute($sql);
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());
            $mess = 'Could NOT delete cached XML from database';
            $this->logger->log(LogLevel::DEBUG, $mess);
            return false;
        }
        return true;
    }
    /**
     * Used to fetch API XML from database table.
     *
     * @param \ADOConnection|null $con
     *
     * @return string|false Returns XML if data is available, else returns FALSE.
     */
    private function getCachedDatabase(\ADOConnection $con = null)
    {
        try {
            if (is_null($con)) {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            }
            $sql = 'select sql_no_cache `xml`';
            $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
            $sql .= ' where';
            $sql .= ' `hash`=' . $con->qstr($this->hash);
            $result = (string)$con->GetOne($sql);
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::ERROR, $e->getMessage());
            $mess = 'Could NOT retrieve cached XML from database';
            $this->logger->log(LogLevel::DEBUG, $mess);
            return false;
        }
        if (empty($result)) {
            $mess = 'Retrieved cached XML from database was empty';
            $this->logger->log(LogLevel::WARNING, $mess);
            return false;
        }
        return $result;
    }
    /**
     * Function used to fetch API XML from file.
     *
     * @return string|false Returns XML if file is available, else returns FALSE.
     */
    private function getCachedFile()
    {
        // Build cache file path
        $cachePath = YAPEAL_CACHE . $this->section . DS;
        if (!is_dir($cachePath)) {
            $mess =
                'XML cache '
                . $cachePath
                . ' is not a directory or does not exist';
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        };
        $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
        $result = @file_get_contents($cacheFile);
        if (false === $result || empty($result)) {
            $mess = 'Could NOT retrieve cached XML file ' . $cacheFile;
            $this->logger->log(LogLevel::DEBUG, $mess);
            return false;
        }
        return $result;
    }
    /**
     * @param string     $xml
     * @param \XMLReader $xr
     *
     * @return string|false
     */
    private function getXmlCurrentTime($xml, \XMLReader $xr)
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
    private function gotXmlError($xml, \XMLReader $xr)
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
     * @param CachedInterval|null $ci
     */
    private function setDefaultCacheInterval(CachedInterval $ci = null)
    {
        if (is_null($ci)) {
            $ci = new CachedInterval();
        }
        $this->cacheInterval =
            (int)$ci->getInterval($this->api, $this->section);
    }
    /**
     * @return bool|string
     */
    private function getCacheDirPath(){
        // Build cache file path
        $cachePath = YAPEAL_CACHE . $this->section . DS;
        if (!is_dir($cachePath)) {
            $mess =
                'XML cache '
                . $cachePath
                . ' is not a directory or is not accessible';
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        }
        if (!is_writable($cachePath)) {
            $mess = 'XML cache directory ' . $cachePath . ' is not writable';
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        }
        return $cachePath;
    }
}
