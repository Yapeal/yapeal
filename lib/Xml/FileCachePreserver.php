<?php
/**
 * Contains FileCachePreserver class.
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
namespace Yapeal\Xml;

use Psr\Log\LoggerInterface;
use Yapeal\Exception\YapealPreserverException;
use Yapeal\Exception\YapealPreserverFileException;
use Yapeal\Exception\YapealPreserverPathException;

/**
 * Class FileCachePreserver
 */
class FileCachePreserver implements EveApiPreserverInterface
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
     * @param EveApiReadInterface $data
     *
     * @throws YapealPreserverPathException
     * @throws \LogicException
     * @throws YapealPreserverFileException
     * @return self
     */
    public function preserveEveApi(EveApiReadInterface $data)
    {
        try {
            $cachePath =
                $this->getNormalizedCachePath($data->getEveApiSectionName());
            $this->checkUsableCachePath($cachePath);
            $hash = $data->getHash();
            // Insures retriever never see partly written file by using temp file.
            $cacheTemp = $cachePath . $data->getEveApiName() . $hash . '.tmp';
            $this->prepareConnection($cacheTemp)
                 ->writeXmlData($data->getEveApiXml(), $cacheTemp)
                 ->__destruct();
        } catch (YapealPreserverException $exp) {
            $mess = 'Could NOT get XML data';
            $this->getLogger()
                 ->info($mess, array('exception' => $exp));
            return $data;
        }
        $cacheFile = $cachePath . $data->getEveApiName() . $hash . '.xml';
        rename($cacheTemp, $cacheFile);
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
     * @throws YapealPreserverPathException
     * @return self
     */
    protected function checkUsableCachePath($cachePath)
    {
        if (!is_readable($cachePath)) {
            $mess = 'Cache path is NOT readable or does NOT exist was given '
                . $cachePath;
            throw new YapealPreserverPathException($mess, 1);
        }
        if (!is_dir($cachePath)) {
            $mess = 'Cache path is NOT a directory was given '
                . $cachePath;
            throw new YapealPreserverPathException($mess, 2);
        }
        if (!is_writable($cachePath)) {
            $mess = 'Cache path is NOT writable was given ' . $cachePath;
            throw new YapealPreserverPathException($mess, 3);
        }
        return $this;
    }
    /**
     * @param string $path
     *
     * @return string[]
     * @throws YapealPreserverPathException
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
                    throw new YapealPreserverPathException($mess, 1);
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
     * @param string $sectionName
     *
     * @throws \LogicException
     * @throws YapealPreserverPathException
     * @return string
     */
    protected function getNormalizedCachePath($sectionName)
    {
        if (empty($this->cachePath)) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new YapealPreserverPathException($mess);
        }
        $absoluteRequired = true;
        $cachePath = $this->cachePath . '/' . $sectionName;
        $path = str_replace('\\', '/', $cachePath);
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
            throw new YapealPreserverPathException($mess);
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
     * @throws YapealPreserverFileException
     * @return self
     */
    protected function prepareConnection($cacheFile)
    {
        $this->handle = fopen($cacheFile, 'cb');
        $tries = 0;
        //Give a minute to try getting lock.
        $timeout = time() + 10;
        while (!flock($this->getHandle(), LOCK_EX | LOCK_NB)) {
            if (++$tries > 10 || time() > $timeout) {
                $this->__destruct();
                $mess = 'Giving up could NOT get flock on ' . $cacheFile;
                throw new YapealPreserverFileException($mess);
            }
            // Wait 0.1 to 0.5 seconds before trying again.
            usleep(rand(100000, 500000));
        }
        @ftruncate($this->getHandle(), 0);
        return $this;
    }
    /**
     * @param string $xml
     * @param string $cacheFile
     *
     * @throws YapealPreserverFileException
     * @return self
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
                throw new YapealPreserverFileException($mess);
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
}
