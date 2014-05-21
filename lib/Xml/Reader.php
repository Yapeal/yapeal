<?php
/**
 * Contains Reader class.
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

use XMLReader;

/**
 * Class Reader is a simple wrapper around XMLReader to allow use of interface
 * and easier unit testing.
 */
class Reader extends XMLReader implements ReaderInterface
{
    /**
     * @return string
     */
    public function getLocalName()
    {
        return (string)$this->localName;
    }
    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }
    /**
     * @return int
     */
    public function getNodeType()
    {
        return (int)$this->nodeType;
    }
    /**
     * @return string
     */
    public function getValue()
    {
        return (string)$this->value;
    }
    /**
     * @return bool
     */
    public function hasAttributes()
    {
        return (bool)$this->hasAttributes;
    }
    /**
     * @return bool
     */
    public function isEmptyElement()
    {
        return (bool)$this->isEmptyElement;
    }
    /**
     * Set the data containing the XML to parse.
     *
     * @param string $source   String containing the XML to be parsed.
     * @param string $encoding [optional] The document encoding or __NULL__.
     * @param int    $options  [optional] A bitmask of the LIBXML_* constants.
     *
     * @return bool __TRUE__ on success or __FALSE__ on failure. If called
     * statically, returns an __XMLReader__ or __FALSE__ on failure.
     * @link http://php.net/manual/en/xmlreader.xml.php
     */
    public function setXml($source, $encoding = null, $options = 0)
    {
        parent::XML($source, $encoding, $options);
    }
}
