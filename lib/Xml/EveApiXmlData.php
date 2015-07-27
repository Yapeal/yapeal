<?php
/**
 * Contains EveApiXmlData class.
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
use InvalidArgumentException;
use LogicException;

/**
 * Class EveApiXmlData
 */
class EveApiXmlData implements EveApiReadWriteInterface
{
    /**
     * Used to add item to arguments list.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws InvalidArgumentException
     * @return self Fluent interface.
     */
    public function addEveApiArgument($name, $value)
    {
        if (!is_string($name)) {
            $mess = 'Name MUST be string but given ' . gettype($name);
            throw new InvalidArgumentException($mess);
        }
        $this->eveApiArguments[$name] = (string)$value;
        return $this;
    }
    /**
     * Getter for cache interval.
     *
     * @return int
     * @throws \LogicException
     */
    public function getCacheInterval()
    {
        if (!is_int($this->cacheInterval)) {
            $mess = 'Tried to access cache interval before it was set';
            throw new LogicException($mess);
        }
        return $this->cacheInterval;
    }
    /**
     * Getter for an existing Eve API argument.
     *
     * @param string $name
     *
     * @return null|string
     * @throws DomainException
     */
    public function getEveApiArgument($name)
    {
        $name = (string)$name;
        if (!array_key_exists($name, $this->eveApiArguments)) {
            $mess = 'Unknown argument ' . $name;
            throw new DomainException($mess);
        }
        return $this->eveApiArguments[$name];
    }
    /**
     * Getter for Eve API argument list.
     *
     * @return string[]
     */
    public function getEveApiArguments()
    {
        return $this->eveApiArguments;
    }
    /**
     * Getter for name of Eve API.
     *
     * @return string
     * @throws LogicException Throws exception if accessed before being set.
     */
    public function getEveApiName()
    {
        if ('' === $this->eveApiName) {
            $mess = 'Tried to access Eve Api name before it was set';
            throw new LogicException($mess);
        }
        return $this->eveApiName;
    }
    /**
     * Getter for name of Eve API section.
     *
     * @return string
     * @throws LogicException Throws exception if accessed before being set.
     */
    public function getEveApiSectionName()
    {
        if ('' === $this->eveApiSectionName) {
            $mess = 'Tried to access Eve Api section name before it was set';
            throw new LogicException($mess);
        }
        return $this->eveApiSectionName;
    }
    /**
     * Getter for the actual Eve API XML received.
     *
     * @return string|false Returns false if XML is a empty string.
     */
    public function getEveApiXml()
    {
        if ('' === $this->eveApiXml) {
            return false;
        }
        return $this->eveApiXml;
    }
    /**
     * Used to get a repeatable unique hash for any combination API name, section, and arguments.
     *
     * @return string
     * @throws LogicException
     */
    public function getHash()
    {
        $hash = $this->getEveApiName() . $this->getEveApiSectionName();
        foreach ($this->getEveApiArguments() as $key => $value) {
            $hash .= $key . $value;
        }
        return hash('md5', $hash);
    }
    /**
     * Used to check if an argument exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasEveApiArgument($name)
    {
        return array_key_exists($name, $this->eveApiArguments);
    }
    /**
     * Cache interval setter.
     *
     * @param int $value
     *
     * @return self Fluent interface.
     */
    public function setCacheInterval($value)
    {
        $this->cacheInterval = (int)$value;
        return $this;
    }
    /**
     * Used to set a list of arguments used when forming request to Eve Api
     * server.
     *
     * Things like KeyID, vCode etc that are either required or optional for the
     * Eve API. See adder for example.
     *
     * Example:
     * <code>
     * <?php
     * $args = array( 'KeyID' => '1156', 'vCode' => 'abc123');
     * $api->setEveApiArguments($args);
     * ...
     * </code>
     *
     * @param string[] $values
     *
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     * @uses EveApiXmlData::addEveApiArgument()
     */
    public function setEveApiArguments(array $values)
    {
        $this->eveApiArguments = [];
        if (0 === count($values)) {
            return $this;
        }
        foreach ($values as $name => $value) {
            $this->addEveApiArgument($name, $value);
        }
        return $this;
    }
    /**
     * Eve API name setter.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     */
    public function setEveApiName($value)
    {
        if (!is_string($value)) {
            $mess = 'Name MUST be string but was given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        $this->eveApiName = $value;
        return $this;
    }
    /**
     * Eve API section name setter.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     */
    public function setEveApiSectionName($value)
    {
        if (!is_string($value)) {
            $mess = 'Section name MUST be string but was given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        $this->eveApiSectionName = $value;
        return $this;
    }
    /**
     * Sets the actual Eve API XML data received.
     *
     * @param string|bool $xml Only allows string or false NOT true.
     *
     * @return self Fluent interface.
     * @throws InvalidArgumentException
     */
    public function setEveApiXml($xml = false)
    {
        if (false === $xml) {
            $xml = '';
        }
        if (!is_string($xml)) {
            $mess = 'Xml MUST be string but was given ' . gettype($xml);
            throw new InvalidArgumentException($mess);
        }
        $this->eveApiXml = $xml;
        return $this;
    }
    /**
     * Holds expected/calculated cache interval for the current API.
     *
     * @type int $cacheInterval
     */
    protected $cacheInterval = 300;
    /**
     * List of API arguments.
     *
     * @type string[] $eveApiArguments
     */
    protected $eveApiArguments = [];
    /**
     * Holds Eve API name.
     *
     * @type string $eveApiName
     */
    protected $eveApiName;
    /**
     * Holds Eve API section name.
     *
     * @type string $eveApiSectionName
     */
    protected $eveApiSectionName;
    /**
     * Holds the actual Eve API XML data.
     *
     * @type string $eveApiXml
     */
    protected $eveApiXml = '';
}
