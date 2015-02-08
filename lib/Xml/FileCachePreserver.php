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
namespace Yapeal\Xml;

use DomainException;
use FilePathNormalizer\FilePathNormalizerTrait;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Yapeal\Exception\YapealPreserverException;
use Yapeal\Exception\YapealPreserverFileException;
use Yapeal\Exception\YapealPreserverPathException;

/**
 * Class FileCachePreserver
 */
class FileCachePreserver implements EveApiPreserverInterface
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
        if (is_resource($this->handle)) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
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
            $cachePath
                = $this->getSectionCachePath($data->getEveApiSectionName());
            $this->checkUsableCachePath($cachePath);
            $hash = $data->getHash();
            // Insures retriever never see partly written file by using temp file.
            $cacheTemp = $cachePath . $data->getEveApiName() . $hash . '.tmp';
            $this->prepareConnection($cacheTemp)
                 ->writeXmlData($data->getEveApiXml(), $cacheTemp)
                 ->__destruct();
        } catch (YapealPreserverException $exc) {
            $mess = 'Could NOT get XML data';
            $this->getLogger()
                ->info($mess, ['exception' => $exc]);
            return $this;
        }
        $cacheFile = $cachePath . $data->getEveApiName() . $hash . '.xml';
        rename($cacheTemp, $cacheFile);
        return $this;
    }
    /**
     * @param string|null $value
     *
     * @throws InvalidArgumentException
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
     * @throws YapealPreserverPathException
     * @return string
     */
    protected function getCachePath()
    {
        if (null === $this->cachePath) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new YapealPreserverPathException($mess);
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
     * @throws \LogicException
     * @throws YapealPreserverPathException
     * @return string
     */
    protected function getSectionCachePath($sectionName)
    {
        try {
            return $this->getFpn()
                        ->normalizePath($this->getCachePath() . $sectionName);
        } catch (DomainException $exc) {
            throw new YapealPreserverPathException(
                $exc->getMessage(),
                null,
                $exc
            );
        }
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
        //Give 10 secs to try getting lock.
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
