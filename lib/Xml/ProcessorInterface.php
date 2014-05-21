<?php
/**
 * Contains ProcessorInterface Interface.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of Yapeal
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

use DOMDocument;
use DOMNode;
use SimpleXMLElement;

/**
 * Interface ProcessorInterface
 */
interface ProcessorInterface
{
    /**
     * Get value of a parameter.
     *
     * @param string $namespaceURI The namespace URI of the XSLT parameter.
     * @param string $localName    The local name of the XSLT parameter.
     *
     * @return string The value of the parameter (as a string), or __FALSE__ if
     * it's NOT set.
     * @link http://php.net/manual/en/xsltprocessor.getparameter.php
     */
    public function getParameter($namespaceURI, $localName);
    /**
     * Import stylesheet
     *
     * @param SimpleXMLElement|DOMDocument $stylesheet The imported style sheet
     *                                                 as a __DOMDocument__ or
     *                                                 __SimpleXMLElement__
     *                                                 object.
     *
     * @link http://php.net/manual/en/xsltprocessor.importstylesheet.php
     */
    public function importStylesheet($stylesheet);
    /**
     * Remove parameter.
     *
     * @param string $namespaceURI The namespace URI of the XSLT parameter.
     * @param string $localName    The local name of the XSLT parameter.
     *
     * @return bool __TRUE__ on success or __FALSE__ on failure.
     * @link http://php.net/manual/en/xsltprocessor.removeparameter.php
     */
    public function removeParameter($namespaceURI, $localName);
    /**
     * Set value for a parameter.
     *
     * @param string $namespace The namespace URI of the XSLT parameter.
     * @param string $name      The local name of the XSLT parameter.
     * @param string $value     The new value of the XSLT parameter.
     *
     * @return bool __TRUE__ on success or __FALSE__ on failure.
     * @link http://php.net/manual/en/xsltprocessor.setparameter.php
     */
    public function setParameter($namespace, $name, $value);
    /**
     * Transform to a DOMDocument.
     *
     * @param DOMNode $doc The node to be transformed.
     *
     * @return DOMDocument The resulting __DOMDocument__ or __FALSE__ on error.
     * @link http://php.net/manual/en/xsltprocessor.transformtodoc.php
     */
    public function transformToDoc(DOMNode $doc);
    /**
     * Transform to XML.
     *
     * Added because docs show only accepts DOMDocument but will also accept
     * SimpleXMLElement which was tested in PHP 5.3, 5.4, and 5.5.
     *
     * @param SimpleXMLElement|DOMDocument $doc The document to be transformed.
     *
     * @return string The result of the transformation as a string or __FALSE__
     * on error.
     * @link http://php.net/manual/en/xsltprocessor.transformtoxml.php
     */
    public function transformToXml($doc);
}
