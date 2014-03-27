<?php
/**
 * Contains EveApiFileSystemCache class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2013-2014  Michael Cummings
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
 * You should be able to find a copy of this license in the LICENSE file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2013-2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link      http://code.google.com/p/yapeal/
 * @link      http://www.eveonline.com/
 */
namespace Yapeal\Caching;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * EveApiFileSystemCache class.
 *
 * @package Yapeal\Caching
 */
class EveApiFileSystemCache
    implements EveApiCacheInterface, LoggerAwareInterface
{
    /**
     * @var string Name of the Eve API being cached.
     */
    private $api;
    /**
     * @var string Holds base directory of cache.
     */
    private $cacheBase;
    /**
     * @var string Holds SHA1 hash of $section, $api, $postParams.
     */
    private $hash = '';
    /**
     * @var LoggerInterface Holds instance of logger.
     */
    private $logger;
    /**
     * @var string The api section that $api belongs to.
     */
    private $section;
    /**
     * @param string          $api
     * @param string          $hash
     * @param string          $section
     * @param string          $cacheBase Base directory for XML cache.
     * @param LoggerInterface $logger
     */
    public function __construct(
        $api,
        $hash,
        $section,
        LoggerInterface $logger = null,
        $cacheBase = YAPEAL_CACHE
    ) {
        $this->setLogger($logger);
        $this->setApi($api);
        $this->setCacheBase($cacheBase);
        $this->setHash($hash);
        $this->setSection($section);
    }
    /**
     * @param string $xml Eve Api XML to be cached.
     *
     * @return self Returns self to allow fluid interface.
     */
    public function cacheXml($xml)
    {
        $cachePath = $this->getCacheDirPath();
        if (false === $cachePath) {
            return $this;
        }
        $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
        $ret = file_put_contents($cacheFile, $xml);
        if (false == $ret || $ret == -1) {
            $mess = 'Could not cache XML to ' . $cacheFile;
            $this->logger->log(LogLevel::ERROR, $mess);
        }
        return $this;
    }
    /**
     * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
     */
    public function deleteCachedXml()
    {
        $cachePath = $this->getCacheDirPath();
        if (false === $cachePath) {
            return false;
        }
        $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
        if (!file_exists($cacheFile) || !is_file($cacheFile)) {
            $mess = 'Could NOT find cached XML file ' . $cacheFile;
            $this->logger->log(LogLevel::DEBUG, $mess);
            return false;
        }
        return @unlink($cacheFile);
    }
    /**
     * @return string|false Returns XML if data is available, else returns FALSE.
     */
    public function getCachedXml()
    {
        $cachePath = $this->getCacheDirPath();
        if (false === $cachePath) {
            return false;
        }
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
     * @param string $api
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setApi($api)
    {
        $this->api = $api;
        return $this;
    }
    /**
     * @param string $cacheBase
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setCacheBase($cacheBase = YAPEAL_CACHE)
    {
        if (!is_dir($cacheBase)) {
            $mess =
                'XML cache '
                . $cacheBase
                . ' is NOT a directory or is NOT accessible';
            $this->logger->log(LogLevel::CRITICAL, $mess);
            return false;
        }
        $this->cacheBase = $cacheBase;
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
     * @return bool|string
     */
    private function getCacheDirPath()
    {
        $cachePath = YAPEAL_CACHE . $this->section . '/';
        if (!is_dir($cachePath)) {
            $mess =
                'XML cache '
                . $cachePath
                . ' is NOT a directory or is NOT accessible';
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
