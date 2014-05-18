<?php
/**
 * Contains EveApiXmlFileCachePreserver class.
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

use Yapeal\Exception\YapealPreserverException;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiXmlDataInterface;

/**
 * Class EveApiXmlFileCachePreserver
 */
class EveApiXmlFileCachePreserver implements EveApiPreserverInterface
{
    /**
     * @param EveApiXmlDataInterface $data
     *
     * @throws \LogicException
     * @throws \Yapeal\Exception\YapealPreserverException
     * @return self
     */
    public function preserveEveApi(EveApiXmlDataInterface $data)
    {
        $cachePath = $this->getCachePath();
        if (!is_readable($cachePath) || !is_dir($cachePath)) {
            $mess = 'Cache path is NOT a directory or does NOT exist was given '
                . $cachePath;
            throw new YapealPreserverException($mess);
        }
        if (!is_writable($cachePath)) {
            $mess = 'Cache path is NOT writable was given ' . $cachePath;
            throw new YapealPreserverException($mess);
        }
        $hash = $this->createHash(
            $data->getEveApiName(),
            $data->getEveApiSectionName(),
            $data->getEveApiArguments()
        );
        $cacheFile = $cachePath . $data->getEveApiName() . $hash . '.xml';
        $result = file_put_contents($cacheFile, $data->getEveApiXml(), LOCK_EX);
        if ($result === false || $result = -1) {
            $mess = 'Writing failed for ' . $cacheFile;
            throw new YapealPreserverException($mess);
        }
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setCachePath($value)
    {
        if (!is_string($value)) {
            $mess = 'Cache path MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->cachePath = $value;
        return $this;
    }
    /**
     * @var string
     */
    protected $cachePath;
    /**
     * @param string   $apiName
     * @param string   $sectionName
     * @param string[] $arguments
     *
     * @return string
     */
    protected function createHash($apiName, $sectionName, array $arguments)
    {
        $hash = $apiName . $sectionName;
        foreach ($arguments as $key => $value) {
            $hash .= $key . $value;
        }
        return hash('md5', $hash);
    }
    /**
     * @throws \LogicException
     * @return string
     */
    protected function getCachePath()
    {
        if (empty($this->cachePath)) {
            $mess = 'Tried to access $cachePath before it was set';
            throw new \LogicException($mess);
        }
        return $this->cachePath;
    }
}
