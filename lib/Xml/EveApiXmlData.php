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
class EveApiXmlData implements EveApiXmlDataInterface
{
    /**
     * Used to add item to arguments list.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function addEveApiArgument($name, $value)
    {
        // TODO: Implement addEveApiArgument() method.
    }
    /**
     * @return string[]
     */
    public function getEveApiArguments()
    {
        // TODO: Implement getEveApiArguments() method.
    }
    /**
     * @return string
     */
    public function getEveApiName()
    {
        // TODO: Implement getEveApiName() method.
    }
    /**
     * @return string
     */
    public function getEveApiSectionName()
    {
        // TODO: Implement getEveApiSectionName() method.
    }
    /**
     * @return string
     */
    public function getEveApiXml()
    {
        // TODO: Implement getEveApiXml() method.
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
     * @return self
     */
    public function setEveApiArguments(array $values)
    {
        // TODO: Implement setEveApiArguments() method.
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setEveApiName($value)
    {
        // TODO: Implement setEveApiName() method.
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setEveApiSectionName($value)
    {
        // TODO: Implement setEveApiSectionName() method.
    }
    /**
     * @param string $xml
     *
     * @return self
     */
    public function setEveApiXml($xml)
    {
        // TODO: Implement setEveApiXml() method.
    }
    /**
     * @var string
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
