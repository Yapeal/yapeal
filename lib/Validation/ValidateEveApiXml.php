<?php
/**
 * Contains ValidateEveApiXml class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Validation;

/**
 * Class used to validate XML from Eve APIs.
 */
class ValidateEveApiXml
{
    /**
     * @var string Hold the XML.
     */
    public $xml = '';
    /**
     * Constructor
     *
     * @param string $api     Name of the Eve API being cached.
     * @param string $section The api section that $api belongs to. For Eve APIs
     *                        will be one of account, char, corp, eve, map, or
     *                        server.
     */
    public function __construct($api, $section)
    {
        $this->api = $api;
        $this->section = $section;
    }
    /**
     * Returns cachedUntil date time.
     *
     * @return string Returns cachedUntil date time from XML.
     */
    public function getCachedUntil()
    {
        return $this->cachedUntil;
    }
    /**
     * Returns currentTime date time.
     *
     * @return string Returns currentTime date time from XML.
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }
    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    /**
     * Returns if current XML is API error.
     *
     * @return bool Return TRUE if current XML is API error.
     */
    public function isApiError()
    {
        if ($this->errorCode != 0 || $this->errorMessage != '') {
            return true;
        }
        return false;
    }
    /**
     * Returns if current cached XML is valid.
     *
     * @return bool Return TRUE if current XML was Validated and valid.
     */
    public function isValidXml()
    {
        return $this->validXml;
    }
    /**
     * Used to validate structure of Eve API XML file.
     *
     * @return self
     */
    public function scanXml()
    {
        // Get a XMLReader instance.
        $xr = new \XMLReader();
        // Check for HTML errors.
        if (false !== strpos($this->xml, '<!DOCTYPE html')) {
            $mess = 'API returned HTML error page.';
            $mess .= ' Check to make sure API ';
            $mess .= $this->section . DS . $this->api;
            $mess .= ' is a valid API.';
            \Logger::getLogger('yapeal')
                   ->warn($mess);
            return $this;
        }
        // Pass XML data to XMLReader so it can be checked.
        $xr->XML($this->xml);
        // Need to get for XSD Schema for this API to use while validating.
        // Build cache file path.
        $xsdPath =
            __DIR__ . DIRECTORY_SEPARATOR . 'xsd' . DIRECTORY_SEPARATOR;
        $xsdFile =
            $xsdPath . $this->section . DIRECTORY_SEPARATOR . $this->api
            . '.xsd';
        if (!is_readable($xsdFile) || !is_file($xsdFile)) {
            if (\Logger::getLogger('yapeal')
                       ->isInfoEnabled()
            ) {
                $mess = 'Could NOT access XSD schema file: ' . $xsdFile;
                \Logger::getLogger('yapeal')
                       ->info($mess);
            }
            $xsdFile = $xsdPath . 'unknown.xsd';
        }
        // Have to have a good schema.
        if (!$xr->setSchema($xsdFile)) {
            if (\Logger::getLogger('yapeal')
                       ->isInfoEnabled()
            ) {
                $mess = 'Could NOT load schema file ' . $xsdFile;
                \Logger::getLogger('yapeal')
                       ->info($mess);
            }
            return $this;
        }
        // Now ready to start going through API XML.
        while (@$xr->read()) {
            if ($xr->nodeType == \XMLReader::ELEMENT) {
                switch ($xr->localName) {
                    case 'cachedUntil':
                        // Grab cachedUntil from XML.
                        $xr->read();
                        $this->cachedUntil = $xr->value;
                        break;
                    case 'currentTime':
                        // Grab current time from XML.
                        $xr->read();
                        $this->currentTime = $xr->value;
                        break;
                    case 'error':
                        // API XML error returned.
                        // See if error code attribute is available.
                        if (true == $xr->hasAttributes) {
                            $this->errorCode = (int)$xr->getAttribute('code');
                        }
                        // Move to message text.
                        $xr->read();
                        $this->errorMessage = $xr->value;
                        break;
                }
            }
        }
        $this->validXml = (boolean)$xr->isValid();
        $xr->close();
        return $this;
    }
    /**
     * @var string Name of the Eve API being cached.
     */
    protected $api;
    /**
     * @var string The api section that $api belongs to.
     */
    protected $section;
    /**
     * @var string Holds the cachedUntil date time from API.
     */
    private $cachedUntil = '1970-01-01 00:00:02';
    /**
     * @var string Holds the currentTime date time from API.
     */
    private $currentTime = '1970-01-01 00:00:01';
    /**
     * @var integer Holds the API error code if there is one.
     */
    private $errorCode = 0;
    /**
     * @var string Holds the API error message if there is one.
     */
    private $errorMessage = '';
    /**
     * @var boolean Used to track if XML is valid.
     */
    private $validXml = false;
}

