<?php
/**
 * Contains EveApiReadWriteInterface Interface.
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

/**
 * Interface EveApiReadWriteInterface
 */
interface EveApiReadWriteInterface
{
    /**
     * Used to add item to arguments list.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return self Fluent interface.
     */
    public function addEveApiArgument($name, $value);
    /**
     * Getter for cache interval.
     *
     * @return int
     * @throws \LogicException
     */
    public function getCacheInterval();
    /**
     * Getter for an existing Eve API argument.
     *
     * @param string $name
     *
     * @return string
     * @throws \DomainException Throws exception for unknown arguments.
     */
    public function getEveApiArgument($name);
    /**
     * Getter for Eve API argument list.
     *
     * @return string[]
     */
    public function getEveApiArguments();
    /**
     * Getter for name of Eve API.
     *
     * @return string
     * @throws \LogicException Throws exception if accessed before being set.
     */
    public function getEveApiName();
    /**
     * Getter for name of Eve API section.
     *
     * @return string
     * @throws \LogicException Throws exception if accessed before being set.
     */
    public function getEveApiSectionName();
    /**
     * Getter for the actual Eve API XML received.
     *
     * @return string|false Returns false if XML is a empty string.
     */
    public function getEveApiXml();
    /**
     * Used to get a repeatable unique hash for any combination API name, section, and arguments.
     *
     * @return string
     */
    public function getHash();
    /**
     * Used to check if an argument exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasEveApiArgument($name);
    /**
     * Cache interval setter.
     *
     * @param int $value Caching interval.
     *
     * @return self Fluent interface.
     */
    public function setCacheInterval($value);
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
     * @throws \InvalidArgumentException
     * @uses EveApiXmlData::addEveApiArgument()
     */
    public function setEveApiArguments(array $values);
    /**
     * Eve API name setter.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setEveApiName($value);
    /**
     * Eve API section name setter.
     *
     * @param string $value
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setEveApiSectionName($value);
    /**
     * Sets the actual Eve API XML data received.
     *
     * @param string|bool $xml Only allows string or false NOT true.
     *
     * @return self Fluent interface.
     * @throws \InvalidArgumentException
     */
    public function setEveApiXml($xml = false);
}
