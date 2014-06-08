<?php
/**
 * Contains EveApiXmlData class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x
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

/**
 * Class EveApiXmlData
 */
class EveApiXmlData implements EveApiReadWriteInterface,
    EveApiXmlModifyInterface
{
    /**
     * @param string      $eveApiName
     * @param string      $eveApiSectionName
     * @param string[]    $eveApiArguments
     * @param bool|string $eveApiXml Only allows string or false NOT true.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        $eveApiName = '',
        $eveApiSectionName = '',
        array $eveApiArguments = array(),
        $eveApiXml = false
    ) {
        $this->setEveApiName($eveApiName);
        $this->setEveApiSectionName($eveApiSectionName);
        $this->setEveApiArguments($eveApiArguments);
        $this->setEveApiXml($eveApiXml);
    }
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->eveApiXml;
    }
    /**
     * Used to add item to arguments list.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function addEveApiArgument($name, $value)
    {
        if (!is_string($name)) {
            $mess = 'Name MUST be string but given ' . gettype($name);
            throw new \InvalidArgumentException($mess);
        }
        $this->eveApiArguments[$name] = (string)$value;
        return $this;
    }
    /**
     * @return string[]
     */
    public function getEveApiArguments()
    {
        if (empty($this->eveApiArguments)) {
            return array();
        }
        return $this->eveApiArguments;
    }
    /**
     * @throws \LogicException
     * @return string
     */
    public function getEveApiName()
    {
        if (empty($this->eveApiName)) {
            $mess = 'Tried to access Eve Api name before it was set';
            throw new \LogicException($mess);
        }
        return $this->eveApiName;
    }
    /**
     * @throws \LogicException
     * @return string
     */
    public function getEveApiSectionName()
    {
        if (empty($this->eveApiSectionName)) {
            $mess = 'Tried to access Eve Api section name before it was set';
            throw new \LogicException($mess);
        }
        return $this->eveApiSectionName;
    }
    /**
     * @return string|false
     */
    public function getEveApiXml()
    {
        if (empty($this->eveApiXml)) {
            return false;
        }
        return $this->eveApiXml;
    }
    /**
     * @throws \LogicException
     * @return bool
     */
    public function hasXmlRowSet()
    {
        if (false !== strpos($this->getEveApiXml(), '<rowset')) {
            return true;
        }
        return false;
    }
    /**
     * Used to set a list of arguments used when forming request to Eve Api
     * server.
     *
     * Things like KeyID, vCode etc that are either required or optional for the
     * Eve API. See setter for example.
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
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setEveApiArguments(array $values)
    {
        if (empty($values)) {
            $this->eveApiArguments = array();
            return $this;
        }
        foreach ($values as $name => $value) {
            $this->addEveApiArgument($name, $value);
        }
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setEveApiName($value)
    {
        if (!is_string($value)) {
            $mess = 'Name MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->eveApiName = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setEveApiSectionName($value)
    {
        if (!is_string($value)) {
            $mess = 'Section name MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->eveApiSectionName = $value;
        return $this;
    }
    /**
     * @param string|bool $xml Only allows string or false NOT true.
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setEveApiXml($xml = false)
    {
        if ($xml === false) {
            $xml = '';
        }
        if (!is_string($xml)) {
            $mess = 'Xml MUST be string but given ' . gettype($xml);
            throw new \InvalidArgumentException($mess);
        }
        $this->eveApiXml = $xml;
        return $this;
    }
    /**
     * @type string[]
     */
    protected $eveApiArguments;
    /**
     * @var string
     */
    protected $eveApiName;
    /**
     * @var string
     */
    protected $eveApiSectionName;
    /**
     * @var string
     */
    protected $eveApiXml;
}
