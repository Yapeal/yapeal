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
 * @package    Yapeal
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
     * Returns API error.
     *
     * @return array Returns API error from XML as assoc array.
     */
    public function getApiError()
    {
        return array(
            'code' => $this->errorCode,
            'message' => $this->errorMessage
        );
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
     * Returns if current XML is API error.
     *
     * @return bool Return TRUE if current XML is API error.
     */
    public function isApiError()
    {
        return $this->apiError;
    }
    /**
     * Returns if current cached XML is valid.
     *
     * @return bool Return TRUE if current XML was Validated and valid.
     */
    public function isValid()
    {
        return $this->validXML;
    }
    /**
     * Used to validate structure of Eve API XML file.
     *
     * @return bool Return TRUE if the XML validates.
     */
    public function validateXML()
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
            return false;
        }
        // Pass XML data to XMLReader so it can be checked.
        $xr->XML($this->xml);
        // Need to get for XSD Schema for this API to use while validating.
        // Build cache file path.
        $cachePath = __DIR__ . DIRECTORY_SEPARATOR . $this->section
            . DIRECTORY_SEPARATOR;
        $cacheFile = $cachePath . $this->api . '.xsd';
        if (!is_readable($cacheFile) || !is_file($cacheFile)) {
            if (\Logger::getLogger('yapeal')
                       ->isInfoEnabled()
            ) {
                $mess = 'Could NOT access XSD schema file: ' . $cacheFile;
                \Logger::getLogger('yapeal')
                       ->info($mess);
            }
            $cacheFile = $cachePath . 'unknown.xsd';
        }
        // Have to have a good schema.
        if (!$xr->setSchema($cacheFile)) {
            if (\Logger::getLogger('yapeal')
                       ->isInfoEnabled()
            ) {
                $mess = 'Could NOT load schema file ' . $cacheFile;
                \Logger::getLogger('yapeal')
                       ->info($mess);
            }
            return false;
        }
        // Now ready to start going through API XML.
        while (@$xr->read()) {
            if ($xr->nodeType == \XMLReader::ELEMENT) {
                switch ($xr->localName) {
                    case 'cachedUntil':
                        // Grab cachedUntil from API.
                        $xr->read();
                        $this->cachedUntil = $xr->value;
                        break;
                    case 'currentTime':
                        // Grab current time from API.
                        $xr->read();
                        $this->currentTime = $xr->value;
                        break;
                    case 'error':
                        // API error returned.
                        // See if error code attribute is available.
                        if (true == $xr->hasAttributes) {
                            $this->errorCode = (int)$xr->getAttribute('code');
                        }
                        // Move to message text.
                        $xr->read();
                        $this->errorMessage = $xr->value;
                        $this->apiError = true;
                        break;
                }
            }
        }
        $this->validXML = (boolean)$xr->isValid();
        $xr->close();
        return true;
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
     * @var boolean Used to track if XML contains Eve API error.
     */
    private $apiError = false;
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
    private $validXML = false;
}

