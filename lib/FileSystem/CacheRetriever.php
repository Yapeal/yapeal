<?php
/**
 * Contains CacheRetriever class.
 *
 * PHP version 5.5
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
namespace Yapeal\FileSystem;

use DomainException;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use LogicException;
use SimpleXMLElement;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class CacheRetriever
 */
class CacheRetriever implements EveApiRetrieverInterface
{
    use CommonFileHandlingTrait, EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * @param string|null $cachePath
     *
     * @throws InvalidArgumentException
     */
    public function __construct($cachePath = null)
    {
        $this->setCachePath($cachePath);
    }
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws LogicException
     */
    public function retrieveEveApi(
        EveApiEventInterface $event,
        $eventName,
        EventMediatorInterface $yem
    ) {
        $this->setYem($yem);
        $data = $event->getData();
        $yem->triggerLogEvent(
            'Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__)
        );
        $cachePath = $this->getSectionCachePath($data->getEveApiSectionName());
        if (false === $cachePath || false === $this->isUsableCachePath($cachePath)) {
            return $event;
        }
        $cacheFile = $cachePath . $data->getEveApiName() . $data->getHash() . '.xml';
        if (false === $this->isUsableCacheFile($cacheFile)) {
            return $event;
        }
        $result = $this->readXmlData($cacheFile);
        if (false === $result) {
            return $event;
        }
        if ($this->isExpired($result)) {
            $this->deleteWithRetry($cacheFile, $this->getYem());
            return $event;
        }
        $mess = sprintf('Found usable cache file %1$s', $cacheFile);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        return $event->setData($data->setEveApiXml($result))
                      ->eventHandled();
    }
    /**
     * @param string|null $value
     *
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setCachePath($value = null)
    {
        if ($value === null) {
            $value = dirname(dirname(__DIR__)) . '/cache/';
        }
        if (!is_string($value)) {
            $mess = 'Cache path MUST be string but given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        $this->cachePath = $value;
        return $this;
    }
    /**
     * @return string
     * @throws LogicException
     */
    protected function getCachePath()
    {
        if ('' === $this->cachePath) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new LogicException($mess);
        }
        return $this->getFpn()
                    ->normalizePath($this->cachePath);
    }
    /**
     * @param string $sectionName
     *
     * @return string|false
     * @throws LogicException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    protected function getSectionCachePath($sectionName)
    {
        try {
            return $this->getFpn()
                        ->normalizePath($this->getCachePath() . strtolower($sectionName));
        } catch (DomainException $exc) {
            $mess = 'Could NOT get cache path';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess, ['exception' => $exc]);
            return false;
        }
    }
    /**
     * @param string $xml
     *
     * @return bool
     * @throws \DomainException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function isExpired($xml)
    {
        $simple = new SimpleXMLElement($xml);
        if (null === $simple->currentTime[0]) {
            $mess = 'Xml file missing required currentTime element';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return true;
        }
        if (null === $simple->cachedUntil[0]) {
            $mess = 'Xml file missing required cachedUntil element';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return true;
        }
        $now = time();
        $current = strtotime($simple->currentTime[0] . '+00:00');
        $until = strtotime($simple->cachedUntil[0] . '+00:00');
        // At minimum use cached XML for 5 minutes (300 secs).
        if (($now - $current) <= 300) {
            return false;
        }
        // Catch and log APIs with bad CachedUntil times so CCP can be told and get them fixed.
        if ($until <= $current) {
            $mess = sprintf('CachedUntil is invalid was given %1$s and currentTime is %2$s', $until, $current);
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return true;
        }
        // Now plus a day.
        if ($until > ($now + 86400)) {
            $mess = sprintf('CachedUntil is excessively long was given %1$s and currentTime is %2$s', $until, $current);
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return true;
        }
        return ($until <= $now);
    }
    /**
     * @param $cacheFile
     *
     * @return bool
     * @throws \DomainException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function isUsableCacheFile($cacheFile)
    {
        if (!is_readable($cacheFile) || !is_file($cacheFile)) {
            $mess = 'Could NOT find accessible cache file was given ' . $cacheFile;
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param $cachePath
     *
     * @return bool
     * @throws \DomainException
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function isUsableCachePath($cachePath)
    {
        if (!is_readable($cachePath)) {
            $mess = 'Cache path is NOT readable or does NOT exist was given ' . $cachePath;
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        if (!is_dir($cachePath)) {
            $mess = 'Cache path is NOT a directory was given ' . $cachePath;
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param string $fileName
     *
     * @return string|false
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function readXmlData($fileName)
    {
        $handle = $this->acquireLockedHandle($fileName, $this->getYem(), 'rb+');
        if (false === $handle) {
            return false;
        }
        rewind($handle);
        $xml = '';
        $tries = 0;
        //Give 10 seconds to try reading file.
        $timeout = time() + 10;
        while (!feof($handle)) {
            if (++$tries > 10 || time() > $timeout) {
                $mess = sprintf('Giving up could NOT finish reading data from %1$s', $fileName);
                $this->getYem()
                     ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
                $this->releaseHandle($handle);
                return false;
            }
            $read = fread($handle, 16384);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ('' !== $read && $tries > 0) {
                --$tries;
            }
            $xml .= $read;
        }
        $this->releaseHandle($handle);
        return $xml;
    }
    /**
     * @type string $cachePath
     */
    protected $cachePath;
}
