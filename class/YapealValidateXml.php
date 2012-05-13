<?php
/**
 * Contains YapealValidateXml class.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2012, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
  $mess = basename(__FILE__)
    . ' must be included it can not be ran directly.' . PHP_EOL;
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  };
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Class used to validate XML from Eve APIs.
 *
 * @package    Yapeal
 * @subpackage YapealValidateXml
 */
class YapealValidateXml {
  /**
   * @var string Name of the Eve API being cached.
   */
  protected $api;
  /**
   * @var boolean Used to track if XML contains Eve API error.
   */
  private $apiError = FALSE;
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
  private $validXML = FALSE;
  /**
   * @var string The api section that $api belongs to.
   */
  protected $section;
  /**
   * @var string Hold the XML.
   */
  public $xml = '';
  /**
   * Constructor
   *
   * @param string $api Name of the Eve API being cached.
   * @param string $section The api section that $api belongs to. For Eve
   * APIs will be one of account, char, corp, eve, map, or server.
   */
  public function __construct($api, $section) {
    $this->api = $api;
    $this->section = $section;
  }// function __constructor
  /**
   * Returns cachedUntil date time.
   *
   * @return string Returns cachedUntil date time from XML.
   */
  public function getCachedUntil() {
    return $this->cachedUntil;
  }// function getCachedUntil
  /**
   * Returns currentTime date time.
   *
   * @return string Returns currentTime date time from XML.
   */
  public function getCurrentTime() {
    return $this->currentTime;
  }// function getCurrentTime
  /**
   * Returns API error.
   *
   * @return array Returns API error from XML as assoc array.
   */
  public function getApiError() {
    return array('code' => $this->errorCode, 'message' => $this->errorMessage);
  }// function getApiError
  /**
   * Returns if current XML is API error.
   *
   * @return bool Return TRUE if current XML is API error.
   */
  public function isApiError() {
    return $this->apiError;
  }// function isValid
  /**
   * Returns if current cached XML is valid.
   *
   * @return bool Return TRUE if current XML was Validated and valid.
   */
  public function isValid() {
    return $this->validXML;
  }// function isValid
  /**
   * Used to validate structure of Eve API XML file.
   *
   * @return bool Return TRUE if the XML validates.
   */
  public function validateXML() {
    // Get a XMLReader instance.
    $xr = new XMLReader();
    // Check for HTML errors.
    if (FALSE !== strpos($this->xml, '<!DOCTYPE html')) {
      $mess = 'API returned HTML error page.';
      $mess .= ' Check to make sure API ';
      $mess .= $this->section . DS . $this->api;
      $mess .= ' is a valid API.';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// if FALSE !== strpos <!DOCTYPE html ...
    // Check for XML header.
    //if (FALSE === strpos($xml, "?xml version='1.0'")) {
    //  $mess = 'API server returned unknown type of data.';
    //  Logger::getLogger('yapeal')->warn($mess);
    //  $this->validXML = FALSE;
    //  return FALSE;
    //};// if strpos $xml...
    // Pass XML data to XMLReader so it can be checked.
    $xr->XML($this->xml);
    // Need to look for XSD Schema for this API to use while validating.
    // Build cache file path.
    $cachePath = realpath(YAPEAL_CACHE . $this->section) . DS;
    if (is_dir($cachePath)) {
      // Build W3C Schema file name
      $cacheFile = $cachePath . $this->api . '.xsd';
      // Can not use schema if it is missing.
      if (!is_file($cacheFile)) {
        if (Logger::getLogger('yapeal')->isInfoEnabled()) {
          $mess = 'Missing schema file ' . $cacheFile;
          Logger::getLogger('yapeal')->info($mess);
        };
        $cacheFile = realpath(YAPEAL_CACHE . 'unknown.xsd');
      };// if !is_file ...
      // Have to have a good schema.
      if (!$xr->setSchema($cacheFile)) {
        if (Logger::getLogger('yapeal')->isInfoEnabled()) {
          $mess = 'Could not load schema file ' . $cacheFile;
          Logger::getLogger('yapeal')->info($mess);
        };
        return FALSE;
      };// if !$xr->setSchema ...
    } else {
      $mess = 'Could not access cache directory to get XSD file';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// else is_dir ...
    // Now ready to start going through API XML.
    while (@$xr->read()) {
      if ($xr->nodeType == XMLReader::ELEMENT) {
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
            if (TRUE == $xr->hasAttributes) {
              $this->errorCode = (int)$xr->getAttribute('code');
            };
            // Move to message text.
            $xr->read();
            $this->errorMessage = $xr->value;
            $this->apiError = TRUE;
            break;
        };// switch $xr->localName ...
      };// if $xr->nodeType ...
    };// while $xr->read() ...
    $this->validXML = (boolean)$xr->isValid();
    $xr->close();
    return TRUE;
  }// function validateXML
}

