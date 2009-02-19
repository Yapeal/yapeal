<?php
/**
 * Class contenting some static api functions.
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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Class to handle all API connections.
 *
 * A holder for a group of static methods and varables used to access the Eve APIs.
 *
 * @package Yapeal
 */
class YapealApiRequests {
  /**
   * @var array Holds the map from constants to API sections.
   */
  static $apiSections = array(
    YAPEAL_API_ACCOUNT => '/account/',
    YAPEAL_API_CHAR => '/char/',
    YAPEAL_API_CORP => '/corp/',
    YAPEAL_API_EVE => '/eve/',
    YAPEAL_API_MAP => '/map/',
    YAPEAL_API_SERVER => '/server/'
  );
  /**
   * Function used to get info from Eve API.
   *
   * @param string $api Needs to be set to base part of name for example:
   * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
   * @param integer $postType See class constants for allowed values.
   * @param array $postData Is an array of data ready to be used in
   * http_build_query.
   *
   * @return mixed Returns SimpleXML object or FALSE
   *
   * @throws YapealApiFileException for API file errors
   * @throws YapealApiErrorException for API errors
   */
  static function getAPIinfo($api, $postType, $postData = array()) {
    global $tracing;
    require_once YAPEAL_CLASS . 'CurlRequest.class.php';
    if (!array_key_exists($postType, self::$apiSections)) {
      $mess = '$postType param was not equal to one of the allowed values';
      $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
    };
    $result = array();
    $xml = NULL;
    // Build http parameter.
    $http = array('timeout' => 60,
      'url' => YAPEAL_URL_BASE . self::$apiSections[$postType] . $api . YAPEAL_FILE_SUFFIX
    );
    if ($postType == YAPEAL_API_EVE || $postType == YAPEAL_API_MAP ||
      $postType == YAPEAL_API_SERVER) {
      // Global APIs like eve, map, and server don't use POST data.
      $http['method'] = 'GET';
    } else {
      // Setup for POST query.
      $http['method'] = 'POST';
      $http['content'] = http_build_query($postData, NULL, '&');
    }; // if $postType=YAPEAL_API_EVE||...
    $mess = 'Setup cURL connection in ' . __FILE__;
    $tracing->activeTrace(YAPEAL_TRACE_CURL, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CURL, $mess);
    // Setup new cURL connection with options.
    $sh = new CurlRequest($http);
    $mess = 'cURL connect to Eve API in ' . __FILE__;
    $tracing->activeTrace(YAPEAL_TRACE_CURL, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_CURL, $mess);
    // Try to get XML.
    $result = $sh->exec();
    // Now check for errors.
    if ($result['curl_error']) {
      $mess = 'cURL error for ' . $http['url'] . PHP_EOL;
      if (isset($http['content'])) {
        $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
      };
      $mess .= 'Error code: ' . $result['curl_errno'];
      $mess .= 'Error message: ' . $result['curl_error'];
      // Throw exception
      require_once YAPEAL_CLASS . 'YapealApiFileException.class.php';
      throw new YapealApiFileException($mess, 1);
    };
    if (200 != $result['http_code']) {
      $mess = 'HTTP error for ' . $http['url'] . PHP_EOL;
      if (isset($http['content'])) {
        $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
      };
      $mess .= 'Error code: ' . $result['http_code'] . PHP_EOL;
      // Throw exception
      require_once YAPEAL_CLASS . 'YapealApiFileException.class.php';
      throw new YapealApiFileException($mess, 2);
    };
    if (!$result['body']) {
      $mess = 'API data empty for ' . $http['url'] . PHP_EOL;
      if (isset($http['content'])) {
        $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
      };
      // Throw exception
      require_once YAPEAL_CLASS . 'YapealApiFileException.class.php';
      throw new YapealApiFileException($mess, 3);
    };
    if (!strpos($result['body'], '<eveapi version="')) {
      $mess = 'API data error for ' . $http['url'] . PHP_EOL;
      if (isset($http['content'])) {
        $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
      };
      $mess .= 'No XML returned' . PHP_EOL;
      // Throw exception
      require_once YAPEAL_CLASS . 'YapealApiFileException.class.php';
      throw new YapealApiFileException($mess, 4);
    };
    $mess = 'Before simplexml_load_string';
    $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
    $xml = simplexml_load_string($result['body']);
    if (isset($xml->error[0])) {
      $mess = 'Eve API error for ' . $http['url'] . PHP_EOL;
      if (isset($http['content'])) {
        $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
      };
      $mess .= 'Error code: ' . (int)$xml->error[0]['code'] . PHP_EOL;
      $mess .= 'Error message: ' . (string)$xml->error[0] . PHP_EOL;
      if (YAPEAL_CACHE_XML) {
        // Build base part of error cache name.
        $cacheName = 'error_' . $api;
        // Hash the parameters to protect userID, characterID, and ApiKey while
        // still having unique names.
        if (!empty($postData)) {
         $cacheName .= sha1(http_build_query($postData, NULL, '&'));
        };
        $cacheName .= '.xml';
        self::cacheXml($result['body'], $cacheName, $postType);
      };// if YAPEAL_CACHE_XML
      // Throw exception
      require_once YAPEAL_CLASS . 'YapealApiErrorException.class.php';
      // Have to use API error code for special API error handling to work.
      throw new YapealApiErrorException($mess, (int)$xml->error[0]['code']);
    };
    return $xml;
  }// function getAPIinfo
  /**
   * Function used to fetch API XML from file.
   *
   * @param string $cacheName Name of file to write to.
   * @param integer $postType See class constants for allowed values.
   *
   * @return mixed Returns XML if file is available and not expired, FALSE otherwise.
   */
  static function getCachedXml($cacheName, $postType) {
    global $tracing;
    if (!array_key_exists($postType, self::$apiSections)) {
      $mess = '$postType param was not equal to one of the allowed values';
      $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
    };
    // If using cache file check for it first.
    if (YAPEAL_CACHE_XML) {
      // Build cache file path
      $cachePath = realpath(YAPEAL_CACHE . self::$apiSections[$postType]);
      $cachePath .= DIRECTORY_SEPARATOR;
      if (!is_dir($cachePath)) {
        $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
        trigger_error($mess, E_USER_WARNING);
        return FALSE;
      };
      $cacheFile = $cachePath . $cacheName;
      if (file_exists($cacheFile) && is_readable($cacheFile)) {
        $mess = 'Loading ' . $cacheFile . ' in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
        $file = file_get_contents($cacheFile);
        if (!strpos($file, '<eveapi version="')) {
          $mess = $cacheFile . ' is not an Eve API XML file';
          trigger_error($mess, E_USER_WARNING);
          return FALSE;
        };
        $xml = simplexml_load_string($file);
        $cuntil = strtotime((string)$xml->cachedUntil[0] . ' +0000');
        $ctime = time();
        if ($ctime <= $cuntil) {
          $mess = 'Returning ' . $cacheFile . ' in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
          return $xml;
        };// if $ctime ...
      };// if file_exists $cacheFile ...
    };
    return FALSE;
  }// function getCachedXml
  /**
   * Function used to fetch API XML from file.
   *
   * @param string $xml The Eve API XML to be cached.
   * @param string $cacheName Name of cache item.
   * @param integer $postType See class constants for allowed values.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  static function cacheXml($xml, $cacheName, $postType) {
    global $tracing;
    if (!array_key_exists($postType, self::$apiSections)) {
      $mess = '$postType param was not equal to one of the allowed values';
      $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
    };
    // If using cache file check for it first.
    if (YAPEAL_CACHE_XML) {
      // Build cache file path
      $cachePath = realpath(YAPEAL_CACHE . self::$apiSections[$postType]);
      $cachePath .= DIRECTORY_SEPARATOR;
      if (!is_dir($cachePath)) {
        $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
        trigger_error($mess, YAPEAL_WARNING_LOG);
        $result = FALSE;
      };
      if (!is_writable($cachePath)) {
        $mess = 'XML cache directory ' . $cachePath . ' is not writable';
        trigger_error($mess, E_USER_WARNING);
      };
      $cacheFile = $cachePath . $cacheName;
      if (is_dir($cachePath) && is_writeable($cachePath)) {
        file_put_contents($cacheFile, $xml);
        return TRUE;
      };
    };
    return FALSE;
  }// function cacheXml
  /**
   * Private constructor no class instances needed.
   */
  private function __construct() {}
  /**
   * Private clone no class instances needed.
   */
  private function __clone() {}
  /**
   * Private destructor no class instances needed.
   */
  private function __destruct() {}
}
/**
 * Account APIs
 */
define('YAPEAL_API_ACCOUNT', 0);
/**
 * Char APIs
 */
define('YAPEAL_API_CHAR',  1);
/**
 * Corp APIs
 */
define('YAPEAL_API_CORP', 2);
/**
 * Eve APIs
 */
define('YAPEAL_API_EVE', 3);
/**
 * Map APIs
 */
define('YAPEAL_API_MAP', 4);
/**
 * Server APIs
 */
define('YAPEAL_API_SERVER', 5);
/**
 * Reserved
 */
define('YAPEAL_API_UTIL', 65535);
?>
