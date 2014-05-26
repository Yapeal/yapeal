<?php
/**
 * Contains EveApiXmlFileCacheRetriever class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
 * Copyright (C) 2014 Michael Cummings
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
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Caching;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Yapeal\Exception\YapealRetrieverException;
use Yapeal\Exception\YapealRetrieverFileException;
use Yapeal\Exception\YapealRetrieverPathException;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlDataInterface;

/**
 * Class EveApiXmlFileCacheRetriever
 */
class EveApiXmlFileCacheRetriever implements EveApiRetrieverInterface,
    LoggerAwareInterface
{
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
     * @param EveApiXmlDataInterface $data
     *
     * @return EveApiXmlDataInterface
     */
    public function retrieveEveApi(EveApiXmlDataInterface $data)
    {
        try {
            $cachePath =
                $this->getNormalizedCachePath() . $data->getEveApiSectionName();
            $this->checkUsableCachePath($cachePath);
            $hash = $data->getEveApiName() . $data->getEveApiSectionName();
            foreach ($data->getEveApiArguments() as $key => $value) {
                $hash .= $key . $value;
            }
            $hash = hash('md5', $hash);
            $cacheFile =
                $cachePath . '/' . $data->getEveApiName() . $hash . '.xml';
            if (!is_readable($cacheFile) || !is_file($cacheFile)) {
                $mess =
                    'Could NOT find accessible cache file was given '
                    . $cacheFile;
                $this->getLogger()
                     ->notice($mess);
                return $data;
            }
            $this->prepareConnection($cacheFile);
            $result = $this->readXmlData($cacheFile);
            $this->__destruct();
        } catch (YapealRetrieverException $exp) {
            $mess = 'Could NOT get XML data';
            $this->getLogger()
                 ->info($mess, array('exception' => $exp));
            return $data;
        }
        return $data->setEveApiXml($result);
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
     * @var string
     */
    protected $cachePath;
    /**
     * @var resource|false File handle
     */
    protected $handle;
    /**
     * @type LoggerInterface
     */
    protected $logger;
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
     * @param string $path
     *
     * @return string[]
     * @throws YapealRetrieverPathException
     */
    protected function cleanPartsPath($path)
    {
        // Drop all leading and trailing "/"s.
        $path = trim($path, '/');
        // Drop pointless consecutive "/"s.
        while (false !== strpos($path, '//')) {
            $path = str_replace('//', '/', $path);
        }
        $parts = array();
        foreach (explode('/', $path) as $part) {
            if ('.' == $part || '' == $part) {
                continue;
            }
            if ('..' == $part) {
                if (count($parts) < 1) {
                    $mess = 'Can NOT go above root path but given ' . $path;
                    throw new YapealRetrieverPathException($mess);
                }
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }
        return $parts;
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
     * @throws YapealRetrieverPathException
     * @return string
     */
    protected function getNormalizedCachePath()
    {
        if (empty($this->cachePath)) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new YapealRetrieverPathException($mess);
        }
        $absoluteRequired = true;
        $path = str_replace('\\', '/', $this->cachePath);
        // Optional wrapper(s).
        $regExp = '%^(?<wrappers>(?:[[:alpha:]][[:alnum:]]+://)*)';
        // Optional root prefix.
        $regExp .= '(?<root>(?:[[:alpha:]]:/|/)?)';
        // Actual path.
        $regExp .= '(?<path>(?:[[:print:]]*))$%';
        $parts = array();
        preg_match($regExp, $path, $parts);
        $wrappers = $parts['wrappers'];
        // vfsStream does NOT allow absolute path.
        if ('vfs://' == substr($wrappers, -6)) {
            $absoluteRequired = false;
        }
        if ($absoluteRequired && empty($parts['root'])) {
            $mess =
                'Path NOT absolute missing drive or root was given ' . $path;
            throw new YapealRetrieverPathException($mess, 1);
        }
        $root = $parts['root'];
        $parts = $this->cleanPartsPath($parts['path']);
        $path = $wrappers . $root . implode('/', $parts);
        if ('/' != substr($path, -1)) {
            $path .= '/';
        }
        return $path;
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
}
