<?php
/**
 * Contains FileCacheRetriever class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014 Michael Cummings
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
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Xml;

use FilePathNormalizer\FilePathNormalizerTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Yapeal\Exception\YapealRetrieverException;
use Yapeal\Exception\YapealRetrieverFileException;
use Yapeal\Exception\YapealRetrieverPathException;

/**
 * Class FileCacheRetriever
 */
class FileCacheRetriever implements EveApiRetrieverInterface,
    LoggerAwareInterface
{
    use FilePathNormalizerTrait;
    /**
     * @param LoggerInterface $logger
     * @param string|null     $cachePath
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(LoggerInterface $logger, $cachePath = null)
    {
        $this->setLogger($logger);
        $this->setCachePath($cachePath);
    }
    /**
     *
     */
    public function __destruct()
    {
        if ($this->handle) {
            @flock($this->handle, LOCK_UN);
            @fclose($this->handle);
        }
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @return self
     */
    public function retrieveEveApi(EveApiReadWriteInterface &$data)
    {
        $mess = sprintf(
            'Started filesystem retrieve for %1$s/%2$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $this->getLogger()
             ->debug($mess);
        try {
            $cachePath
                = $this->getSectionCachePath($data->getEveApiSectionName());
            $this->checkUsableCachePath($cachePath);
            $cacheFile
                =
                $cachePath . $data->getEveApiName() . $data->getHash() . '.xml';
            $this->checkUsableCacheFile($cacheFile);
            $this->prepareConnection($cacheFile);
            $result = $this->readXmlData($cacheFile);
            $this->__destruct();
            if ($this->isExpired($result)) {
                $mess = sprintf('Deleting expired cache file %1$s', $cacheFile);
                $this->getLogger()
                     ->debug($mess);
                @unlink($cacheFile);
                return $this;
            }
        } catch (YapealRetrieverException $exc) {
            $mess = 'Could NOT get XML data';
            $this->getLogger()
                 ->debug($mess, ['exception' => $exc]);
            return $this;
        }
        //$this->getLogger()->debug($result);
        $data->setEveApiXml($result);
        return $this;
    }
    /**
     * @param string|null $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setCachePath($value = null)
    {
        if ($value === null) {
            $value = dirname(dirname(__DIR__)) . '/cache/';
        }
        if (!is_string($value)) {
            $mess = 'Cache path MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->cachePath = $value;
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
     * @param $cacheFile
     *
     * @throws YapealRetrieverFileException
     * @return self
     */
    protected function checkUsableCacheFile($cacheFile)
    {
        if (!is_readable($cacheFile) || !is_file($cacheFile)) {
            $mess
                = 'Could NOT find accessible cache file was given '
                  . $cacheFile;
            throw new YapealRetrieverFileException($mess);
        }
        return $this;
    }
    /**
     * @param $cachePath
     *
     * @throws YapealRetrieverPathException
     * @return self
     */
    protected function checkUsableCachePath($cachePath)
    {
        if (!is_readable($cachePath)) {
            $mess = 'Cache path is NOT readable or does NOT exist was given '
                    . $cachePath;
            throw new YapealRetrieverPathException($mess);
        }
        if (!is_dir($cachePath)) {
            $mess = 'Cache path is NOT a directory was given '
                    . $cachePath;
            throw new YapealRetrieverPathException($mess);
        }
        return $this;
    }
    /**
     * @throws YapealRetrieverPathException
     * @return string
     */
    protected function getCachePath()
    {
        if (empty($this->cachePath)) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new YapealRetrieverPathException($mess);
        }
        return $this->getFpn()
                    ->normalizePath($this->cachePath);
    }
    /**
     * @return false|resource
     */
    protected function getHandle()
    {
        return $this->handle;
    }
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    /**
     * @param string $sectionName
     *
     * @throws YapealRetrieverPathException
     * @return string
     */
    protected function getSectionCachePath($sectionName)
    {
        return $this->getFpn()
                    ->normalizePath($this->getCachePath() . $sectionName);
    }
    /**
     * @param string $xml
     *
     * @return bool
     */
    protected function isExpired($xml)
    {
        $simple = new SimpleXMLElement($xml);
        if (!isset($simple->currentTime)) {
            $mess = 'Xml file missing required currentTime element';
            $this->getLogger()
                 ->notice($mess);
            return true;
        }
        if (!isset($simple->cachedUntil)) {
            $mess = 'Xml file missing required cachedUntil element';
            $this->getLogger()
                 ->notice($mess);
            return true;
        }
        $now = time();
        $current = strtotime($simple->currentTime . '+00:00');
        $until = strtotime($simple->cachedUntil . '+00:00');
        // At minimum use cached XML for 5 minutes (300 secs).
        if (($now - $current) <= 300) {
            return false;
        }
        // Catch and log APIs with bad CachedUntil times so CCP can be told and
        // get them fixed.
        if ($until <= $current) {
            $mess = sprintf(
                'CachedUntil is invalid was given %1$s and currentTime is %2$s',
                $simple->cachedUntil,
                $simple->currentTime
            );
            $this->getLogger()
                 ->warning($mess);
            return true;
        }
        // Now plus a day.
        if ($until > ($now + 86400)) {
            $mess = sprintf(
                'CachedUntil is excessively long was given %1$s and currentTime is %2$s',
                $simple->cachedUntil,
                $simple->currentTime
            );
            $this->getLogger()
                 ->notice($mess);
            return true;
        }
        if ($until <= $now) {
            return true;
        }
        return false;
    }
    /**
     * @param $cacheFile
     *
     * @throws YapealRetrieverFileException
     * @return self
     */
    protected function prepareConnection($cacheFile)
    {
        $this->handle = fopen($cacheFile, 'rb+');
        $tries = 0;
        //Give a minute to try getting lock.
        $timeout = time() + 10;
        while (!flock($this->getHandle(), LOCK_SH | LOCK_NB)) {
            if (++$tries > 10 || time() > $timeout) {
                $this->__destruct();
                $mess = 'Giving up could NOT get flock on ' . $cacheFile;
                throw new YapealRetrieverFileException($mess);
            }
            // Wait 0.1 to 0.5 seconds before trying again.
            usleep(rand(100000, 500000));
        }
        return $this;
    }
    /**
     * @param string $cacheFile
     *
     * @throws YapealRetrieverFileException
     * @return string|false
     */
    protected function readXmlData($cacheFile)
    {
        $xml = '';
        $tries = 0;
        //Give a minute to try reading file.
        $timeout = time() + 60;
        while (!feof($this->getHandle())) {
            if (++$tries > 10 || time() > $timeout) {
                $this->__destruct();
                $mess = 'Giving up could NOT finish reading  ' . $cacheFile;
                throw new YapealRetrieverFileException($mess);
            }
            $read = fread($this->getHandle(), 16384);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if (strlen($read) > 0 && $tries > 0) {
                --$tries;
            }
            $xml .= $read;
        }
        return $xml;
    }
    /**
     * @type string $cachePath
     */
    protected $cachePath;
    /**
     * @type resource|false $handle File handle
     */
    protected $handle;
    /**
     * @type LoggerInterface $logger
     */
    protected $logger;
}
