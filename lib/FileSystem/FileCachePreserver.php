<?php
/**
 * Contains FileCachePreserver class.
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
namespace Yapeal\FileSystem;

use Exception;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use LogicException;
use Yapeal\Container\ContainerInterface;
use Yapeal\Container\ServiceCallableInterface;
use Yapeal\Event\ContainerAwareEventDispatcherInterface;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventSubscriberInterface;
use Yapeal\Exception\YapealException;
use Yapeal\Log\Logger;

/**
 * Class FileCachePreserver
 */
class FileCachePreserver implements EventSubscriberInterface, ServiceCallableInterface
{
    use FilePathNormalizerTrait;
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
     * @inheritdoc
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        $events = [
            'Yapeal.EveApi.preserve' => [
                'eveApiPreserve',
                -PHP_INT_MAX
            ]
        ];
        return $events;
    }
    /**
     * @inheritdoc
     */
    public static function injectCallable(ContainerInterface &$dic)
    {
        if ('none' !== $dic['Yapeal.Cache.fileSystemMode']) {
            $class = __CLASS__;
            $serviceName = str_replace('\\', '.', $class);
            $dic[$serviceName] = function () use ($dic, $class) {
                return new $class($dic['Yapeal.Cache.cacheDir']);
            };
            return $serviceName;
        }
        return false;
    }
    /**
     *
     */
    public function __destruct()
    {
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
    }
    /**
     * @param EveApiEventInterface                   $event
     * @param string                                 $eventName
     * @param ContainerAwareEventDispatcherInterface $yed
     *
     * @return EveApiEventInterface
     */
    public function eveApiPreserve(
        EveApiEventInterface $event,
        $eventName,
        ContainerAwareEventDispatcherInterface $yed
    ) {
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s in %4$s',
            $eventName,
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            __CLASS__
        );
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $mess = sprintf(
            'Started filesystem preserve for %1$s/%2$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName()
        );
        $yed->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $cachePath
            = $this->getSectionCachePath($data->getEveApiSectionName(), $yed);
        if (false === $cachePath
            || false === $this->isUsableCachePath($cachePath, $yed)
        ) {
            return $event;
        }
        // Insures retriever never see partly written file by using temp file.
        $cacheBase = $cachePath . $data->getEveApiName() . $data->getHash();
        $cacheTemp = $cacheBase . '.tmp';
        $cacheFile = $cacheBase . '.xml';
        try {
            $this->prepareConnection($cacheTemp)
                 ->writeXmlData($data->getEveApiXml(), $cacheTemp)
                 ->__destruct();
        } catch (Exception $exc) {
            $mess = 'Could NOT preserve XML data';
            $yed->dispatchLogEvent(
                'Yapeal.Log.log',
                Logger::NOTICE,
                $mess,
                ['exception' => $exc]
            );
            return $event;
        }
        rename($cacheTemp, $cacheFile);
        return $event;
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
        return $this->cachePath;
    }
    /**
     * @return false|resource
     */
    protected function getHandle()
    {
        return $this->handle;
    }
    /**
     * @param string                                 $sectionName
     * @param ContainerAwareEventDispatcherInterface $yed
     *
     * @return false|string
     */
    protected function getSectionCachePath(
        $sectionName,
        ContainerAwareEventDispatcherInterface $yed
    ) {
        try {
            return $this->getFpn()
                ->normalizePath(
                    $this->getCachePath() . $sectionName
                );
        } catch (Exception $exc) {
            $mess = 'Could NOT get cache path';
            $yed->dispatchLogEvent(
                'Yapeal.Log.log',
                Logger::NOTICE,
                $mess,
                ['exception' => $exc]
            );
            return false;
        }
    }
    /**
     * @param string                                 $cachePath
     * @param ContainerAwareEventDispatcherInterface $yed
     *
     * @return bool
     */
    protected function isUsableCachePath(
        $cachePath,
        ContainerAwareEventDispatcherInterface $yed
    ) {
        if (!is_readable($cachePath)) {
            $mess =
                'Cache path is NOT readable or does NOT exist was given '
                . $cachePath;
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        if (!is_dir($cachePath)) {
            $mess = 'Cache path is NOT a directory was given ' . $cachePath;
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        if (!is_writable($cachePath)) {
            $mess = 'Cache path is NOT writable was given ' . $cachePath;
            $yed->dispatchLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param $cacheFile
     *
     * @return self
     * @throws YapealException
     */
    protected function prepareConnection($cacheFile)
    {
        $this->handle = fopen($cacheFile, 'cb');
        $tries = 0;
        //Give 10 seconds to try getting lock.
        $timeout = time() + 10;
        while (!flock($this->getHandle(), LOCK_EX | LOCK_NB)) {
            if (++$tries > 10 || time() > $timeout) {
                $this->__destruct();
                $mess = 'Giving up could NOT get flock on ' . $cacheFile;
                throw new YapealException($mess);
            }
            // Wait 0.1 to 0.5 seconds before trying again.
            usleep(rand(100000, 500000));
        }
        ftruncate($this->getHandle(), 0);
        return $this;
    }
    /**
     * @param string $xml
     * @param string $cacheFile
     *
     * @return self
     * @throws YapealException
     */
    protected function writeXmlData($xml, $cacheFile)
    {
        $tries = 0;
        //Give a minute to try writing file.
        $timeout = time() + 60;
        while (strlen($xml)) {
            if (++$tries > 10 || time() > $timeout) {
                $this->__destruct();
                $mess = 'Giving up could NOT finish writing  ' . $cacheFile;
                throw new YapealException($mess);
            }
            $written = fwrite($this->getHandle(), $xml);
            // Decrease $tries while making progress but NEVER $tries < 1.
            if ($written > 0 && $tries > 0) {
                --$tries;
            }
            $xml = substr($xml, $written);
        }
        return $this;
    }
    /**
     * @type string $cachePath
     */
    protected $cachePath;
    /**
     * @type resource|false $handle File handle
     */
    protected $handle;
}
