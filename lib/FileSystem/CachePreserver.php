<?php
/**
 * Contains CachePreserver class.
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
use Exception;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use LogicException;
use Yapeal\Event\EveApiEventEmitterTrait;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class CachePreserver
 */
class CachePreserver
{
    use CommonFileHandlingTrait, EveApiEventEmitterTrait, FilePathNormalizerTrait;
    /**
     * @param string|null $cachePath
     *
     * @throws InvalidArgumentException
     * @throws DomainException
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
    public function preserveEveApi(
        EveApiEventInterface $event,
        $eventName,
        EventMediatorInterface $yem
    ) {
        $data = $event->getData();
        $this->setYem($yem);
        $yem->triggerLogEvent(
            'Yapeal.Log.log',
            Logger::DEBUG,
            $this->getReceivedEventMessage($data, $eventName, __CLASS__)
        );
        $cachePath = $this->getSectionCachePath($data->getEveApiSectionName());
        if (false === $cachePath || false === $this->isUsableCachePath($cachePath)) {
            return $event;
        }
        // Insures retriever never see partly written file by deleting old file and using temp file for writing.
        $cacheBase = $cachePath . $data->getEveApiName() . $data->getHash();
        $cacheFile = $cacheBase . '.xml';
        $cacheTemp = $cacheBase . '.tmp';
        if (false === $this->deleteWithRetry($cacheFile, $yem)) {
            return $event;
        }
        $this->writeXmlData($data->getEveApiXml(), $cacheTemp);
        if (false === rename($cacheTemp, $cacheFile)) {
            $mess = sprintf('Could NOT rename %1$s to %2$s', $cacheTemp, $cacheFile);
            $yem->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return $event;
        }
        return $event->eventHandled();
    }
    /**
     * @param string|null $value
     *
     * @return self Fluent interface.
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    public function setCachePath($value = null)
    {
        if ($value === null) {
            $value = dirname(dirname(__DIR__)) . '/cache/';
        }
        if (!is_string($value)) {
            $mess = 'Cache path MUST be string but was given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        if ('' === $this->cachePath) {
            $mess = 'Cache path can NOT be empty';
            throw new DomainException($mess);
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
        return $this->cachePath;
    }
    /**
     * @param string $sectionName
     *
     * @return false|string
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function getSectionCachePath($sectionName)
    {
        try {
            return $this->getFpn()
                        ->normalizePath($this->getCachePath() . strtolower($sectionName));
        } catch (Exception $exc) {
            $mess = 'Could NOT get cache path';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess, ['exception' => $exc]);
            return false;
        }
    }
    /**
     * @param string $cachePath
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
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
        if (!is_writable($cachePath)) {
            $mess = 'Cache path is NOT writable was given ' . $cachePath;
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param string $xml
     * @param string $fileName
     *
     * @return bool
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function writeXmlData($xml, $fileName)
    {
        $handle = $this->acquireLockedHandle($fileName, $this->getYem());
        if (false === $handle) {
            return false;
        }
        $tries = 0;
        //Give 10 seconds to try writing file.
        $timeout = time() + 10;
        while (strlen($xml)) {
            if (++$tries > 10 || time() > $timeout) {
                $mess = sprintf('Giving up could NOT finish writing data to %1$s', $fileName);
                $this->getYem()
                     ->triggerLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
                $this->releaseHandle($handle)
                     ->deleteWithRetry($fileName, $this->getYem());
                return false;
            }
            $written = fwrite($handle, $xml);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ($written > 0 && $tries > 0) {
                --$tries;
            }
            $xml = substr($xml, $written);
        }
        $this->releaseHandle($handle);
        return true;
    }
    /**
     * @type string $cachePath
     */
    protected $cachePath;
}
