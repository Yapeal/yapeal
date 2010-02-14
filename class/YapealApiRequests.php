<?php
/**
 * Contains YapealApiRequests class.
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
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
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
   * Function used to get info from API.
   *
   * @param string $api Needs to be set to base part of name for example:
   * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
   * @param string $section The api section that $api belongs to. For Eve APIs
   * will be one of account, char, corp, eve, map, or server.
   * @param string $proxy Allows overriding API server for example to use a
   * different proxy on a per char/corp basis. It should contain a url format
   * string made to used in sprintf() to replace %1$s with $api and %2$s with
   * $section as needed to complete the url. For example:
   * 'http://api.eve-online.com/%2$s/%1$s.xml.aspx' for normal Eve API server.
   * @param array $postData Is an array of data ready to be used in
   * http_build_query.
   *
   * @return mixed Returns SimpleXML object or FALSE
   *
   * @throws YapealApiFileException for API file errors
   * @throws YapealApiErrorException for API errors
   */
  static function getAPIinfo($api, $section, $postData = array(),
    $proxy = NULL) {
    $postParams = '';
    $result = array();
    $xml = NULL;
    // Build http parameter.
    if (empty($proxy)) {
      $proxy = 'http://api.eve-online.com/%2$s/%1$s.xml.aspx';
    };
    $http = array('timeout' => 60, 'url' => sprintf($proxy, $api, $section));
    if ($section == 'eve' || $section == 'map' || $section == 'server') {
      // Global APIs like eve, map, and server don't use POST data.
      $http['method'] = 'GET';
    } else {
      // Setup for POST query.
      $http['method'] = 'POST';
      $http['content'] = http_build_query($postData, NULL, '&');
      $postParams = 'Post parameters: ' . $http['content'] . PHP_EOL;
    };// if $postType=YAPEAL_API_EVE||...
    // Setup new cURL connection with options.
    $sh = new CurlRequest($http);
    // Try to get XML.
    $result = $sh->exec();
    // Now check for errors.
    if ($result['curl_error']) {
      $mess = 'cURL error for ' . $http['url'] . PHP_EOL;
      $mess .= $postParams;
      $mess .= 'Error code: ' . $result['curl_errno'];
      $mess .= 'Error message: ' . $result['curl_error'];
      // Throw exception
      throw new YapealApiFileException($mess, 1);
    };
    if (200 != $result['http_code']) {
      $mess = 'HTTP error for ' . $http['url'] . PHP_EOL;
      $mess .= $postParams;
      $mess .= 'Error code: ' . $result['http_code'] . PHP_EOL;
      // Throw exception
      throw new YapealApiFileException($mess, 2);
    };
    if (!$result['body']) {
      $mess = 'API data empty for ' . $http['url'] . PHP_EOL;
      $mess .= $postParams;
      // Throw exception
      throw new YapealApiFileException($mess, 3);
    };
    if (!strpos($result['body'], '<eveapi version="')) {
      $mess = 'API data error for ' . $http['url'] . PHP_EOL;
      $mess .= $postParams;
      $mess .= 'No XML returned' . PHP_EOL;
      // Throw exception
      throw new YapealApiFileException($mess, 4);
    };
    $xml = simplexml_load_string($result['body'], 'SimpleXMLIterator');
    if (isset($xml->error[0])) {
      $mess = 'Eve API error for ' . $http['url'] . PHP_EOL;
      $mess .= $postParams;
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
        self::cacheXml($result['body'], $cacheName, $section);
      };// if YAPEAL_CACHE_XML
      // Throw exception
      // Have to use API error code for special API error handling to work.
      throw new YapealApiErrorException($mess, (int)$xml->error[0]['code']);
    };
    return $xml;
  }// function getAPIinfo
  /**
   * Function used to fetch API XML from database table and/or file.
   *
   * @param string $cacheName Name of cached item to try to retrieve.
   * @param string $section The api section that $cacheName belongs to. For Eve
   * APIs will be one of account, char, corp, eve, map, or server.
   *
   * @return mixed Returns XML if cached copy is available and not expired, else
   * returns FALSE.
   */
  static function getCachedXml($cacheName, $section) {
    if (TRUE == YAPEAL_CACHE_XML) {
      switch (YAPEAL_CACHE_OUTPUT) {
        case 'both':
          $xml = self::getCachedDatabase($cacheName, $section);
          if (FALSE == $xml) {
            $xml = self::getCachedFile($cacheName, $section);
          };// if FALSE == $xml ...
          return $xml;
        case 'database':
          return self::getCachedDatabase($cacheName, $section);
        case 'file':
          return self::getCachedFile($cacheName, $section);
        default:
          $mess = 'Invalid value of "' . YAPEAL_CACHE_OUTPUT;
          $mess .= '" for YAPEAL_CACHE_OUTPUT.';
          $mess .= ' Check that the setting in your ini file is correct.';
          trigger_error($mess, E_USER_WARNING);
          return FALSE;
      };// switch YAPEAL_CACHE_OUTPUT ...
    };// if TRUE == YAPEAL_CACHE_XML
    return FALSE;
  }// function getCachedXml
  /**
   * Function used to fetch API XML from file.
   *
   * @param string $cacheName Name of file to retrieve from.
   * @param string $section The api section that $cacheName belongs to. For Eve
   * APIs will be one of account, char, corp, eve, map, or server.
   *
   * @return mixed Returns XML if file is available and not expired, else
   * returns FALSE.
   */
  private static function getCachedFile($cacheName, $section) {
    // Build cache file path
    $cachePath = realpath(YAPEAL_CACHE . $section) . DS;
    if (!is_dir($cachePath)) {
      $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };
    $cacheFile = $cachePath . $cacheName . '.xml';
    if (file_exists($cacheFile) && is_readable($cacheFile)) {
      $file = file_get_contents($cacheFile);
      if (!strpos($file, '<eveapi version="')) {
        $mess = $cacheFile . ' is not an Eve API XML file';
        trigger_error($mess, E_USER_WARNING);
        return FALSE;
      };
      $xml = simplexml_load_string($file, 'SimpleXMLIterator');
      $cuntil = strtotime((string)$xml->cachedUntil[0] . ' +0000');
      $ctime = time();
      if ($ctime <= $cuntil) {
        return $xml;
      };// if $ctime ...
    };// if file_exists $cacheFile ...
    return FALSE;
  }// function getCachedFile
  /**
   * Function used to fetch API XML from database table.
   *
   * @param string $cacheName Name of cached record to retrieve.
   * @param string $section The api section that $cacheName belongs to. For Eve
   * APIs will be one of account, char, corp, eve, map, or server.
   *
   * @return mixed Returns XML if record is available and not expired, else
   * returns FALSE.
   */
  private static function getCachedDatabase($cacheName, $section) {
    try {
      $hash = sha1($cacheName . $section);
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'select `xml`';
      $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
      $sql .= ' where `hash`=' . $con->qstr($hash);
      $result = $con->GetOne($sql);
      if (!empty($result)) {
        if (!strpos($result, '<eveapi version="')) {
          $mess = $cacheName . ' is not a valid Eve API XML data';
          trigger_error($mess, E_USER_WARNING);
          return FALSE;
        };
        $xml = simplexml_load_string($result, 'SimpleXMLIterator');
        $cuntil = strtotime((string)$xml->cachedUntil[0] . ' +0000');
        $ctime = time();
        if ($ctime <= $cuntil) {
          return $xml;
        };// if $ctime ...
      };// if !empty $result ...
      return FALSE;
    }
    catch (Exception $e) {
      return FALSE;
    }
  }// function getCachedDatabase
  /**
   * Function used to save API XML to cache database table and/or file.
   *
   * @param string $xml The Eve API XML to be cached.
   * @param string $cacheName Name of cache item.
   * @param istring $section The api section that $api belongs to. For Eve APIs
   * will be one of account, char, corp, eve, map, or server.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  static function cacheXml($xml, $cacheName, $section) {
    if (TRUE == YAPEAL_CACHE_XML) {
      switch (YAPEAL_CACHE_OUTPUT) {
        case 'both':
          self::cacheXmlDatabase($xml, $cacheName, $section);
          self::cacheXmlFile($xml, $cacheName, $section);
          break;
        case 'database':
          self::cacheXmlDatabase($xml, $cacheName, $section);
          break;
        case 'file':
          self::cacheXmlFile($xml, $cacheName, $section);
          break;
        default:
          $mess = 'Invalid value of "' . YAPEAL_CACHE_OUTPUT;
          $mess .= '" for YAPEAL_CACHE_OUTPUT.';
          $mess .= ' Check that the setting in your ini file is correct.';
          trigger_error($mess, E_USER_WARNING);
          return FALSE;
      };// switch YAPEAL_CACHE_OUTPUT ...
      return TRUE;
    };// if TRUE == YAPEAL_CACHE_XML
    return FALSE;
  }// function cacheXml
  /**
   * Function used to save API XML into database table.
   *
   * @param string $xml The Eve API XML to be cached.
   * @param string $cacheName Name of cache item.
   * @param istring $section The api section that $api belongs to. For Eve APIs
   * will be one of account, char, corp, eve, map, or server.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  private static function cacheXmlDatabase($xml, $cacheName, $section) {
    try {
      $hash = sha1($cacheName . $section);
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $data = array('cacheName' => $cacheName, 'hash' => $hash,
        'section' => $section, 'xml' => $xml);
      YapealDBConnection::upsert($data, YAPEAL_TABLE_PREFIX . 'utilXmlCache',
        YAPEAL_DSN);
      $mess = 'Cached XML to database as ' . $cacheName;
      trigger_error($mess, E_USER_NOTICE);
      return TRUE;
    }
    catch (Exception $e) {
      return FALSE;
    }
  }// function cacheXmlDatabase
  /**
   * Function used to save API XML into file.
   *
   * @param string $xml The Eve API XML to be cached.
   * @param string $cacheName Name of cache item.
   * @param istring $section The api section that $api belongs to. For Eve APIs
   * will be one of account, char, corp, eve, map, or server.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  private static function cacheXmlFile($xml, $cacheName, $section) {
    // Build cache file path
    $cachePath = realpath(YAPEAL_CACHE . $section) . DS;
    if (!is_dir($cachePath)) {
      $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if !is_dir $cachePath ...
    if (!is_writable($cachePath)) {
      $mess = 'XML cache directory ' . $cachePath . ' is not writable';
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if !is_writable $cachePath ...
    $cacheFile = $cachePath . $cacheName . '.xml';
    $ret = file_put_contents($cacheFile, $xml);
    if (FALSE == $ret || $ret == -1) {
      $mess = 'Could not cache XML to ' . $cacheFile;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if FALSE == $ret ||...
    $mess = 'Cached XML to ' . $cacheFile;
    trigger_error($mess, E_USER_NOTICE);
    return TRUE;
  }// function cacheXmlFile
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
define('YAPEAL_API_ACCOUNT', 'account');
/**
 * Char APIs
 */
define('YAPEAL_API_CHAR',  'char');
/**
 * Corp APIs
 */
define('YAPEAL_API_CORP', 'corp');
/**
 * Eve APIs
 */
define('YAPEAL_API_EVE', 'eve');
/**
 * Map APIs
 */
define('YAPEAL_API_MAP', 'map');
/**
 * Server APIs
 */
define('YAPEAL_API_SERVER', 'server');
/**
 * Reserved
 */
define('YAPEAL_API_UTIL', 'util');
?>
