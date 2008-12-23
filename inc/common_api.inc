<?php
/**
 * Group of common api functions.
 *
 * LICENSE: This file is part of Yapeal.
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
 * @copyright Copyright (c) 2008, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/* **************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* **************************************************************************/
/**
 * Find path for includes
 */
// Assumes this file is in same directory as common_backend.inc
require_once realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'common_backend.inc';
/* **************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* **************************************************************************/
require_once YAPEAL_INC . 'elog.inc';
require_once YAPEAL_CLASS . 'CurlRequest.class.php';
/**
 * Function used to get info from Eve API.
 *
 * GetAPIinfo has been updated and now only throws Exception.
 * It will no longer trigger errors.
 *
 * @param string $api Needs to be set to base part of name for example:
 * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
 * @param integer $posttype 0=eve, 1=char, 2=corp, 3=map, 4=account See constants
 * in $prefixes below for allowed values.
 * @param array $postdata Is an array of data ready to be used in
 * http_build_query.
 *
 * @return mixed Returns SimpleXML object or FALSE
 *
 * @throws EVE_API_Exception for API file errors
 */
function getAPIinfo($api, $posttype, $postdata = array()) {
  global $tracing;
  $prefixes = array(
    YAPEAL_API_ACCOUNT => '/account/',
    YAPEAL_API_CHAR => '/char/',
    YAPEAL_API_CORP => '/corp/',
    YAPEAL_API_EVE => '/eve/',
    YAPEAL_API_MAP => '/map/',
    YAPEAL_API_SERVER => '/server/'
  );
  $result = array();
  $xml = NULL;
  // If using cache file check for it first.
  if (YAPEAL_CACHE_XML) {
    // Build cache file path
    $cachePath = realpath(YAPEAL_CACHE . $prefixes[$posttype]) . DIRECTORY_SEPARATOR;
    if (!(is_dir($cachePath) && is_writable($cachePath)) &&
      (YAPEAL_LOG_LEVEL & E_USER_WARNING) == E_USER_WARNING) {
      $mess = 'XML cache directory ' . $cachePath . ' is missing or not writable';
      print_on_command($mess);
      elog($mess, YAPEAL_WARNING_LOG);
    };
    // Build base part of cache file name
    $cacheFileName = $api;
    // Hash the parameters to protect userID, characterID, and ApiKey while
    // still having unique names
    if (!empty($postdata)) {
     $cacheFileName.= sha1(http_build_query($postdata, NULL, '&'));
    };
    $cacheFileName.= '.xml';
    $cacheFile = $cachePath . $cacheFileName;
    if (file_exists($cacheFile) && is_readable($cacheFile)) {
      $mess = 'Loading ' . $cacheFileName . ' in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
      $xml = simplexml_load_file($cacheFile);
      $cuntil = strtotime((string)$xml->cachedUntil[0] . ' +0000');
      $ctime = time();
      if ($ctime <= $cuntil) {
        $mess = 'Using ' . $cacheFileName . ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
        return $xml;
      };// if $ctime ...
    };
  };
  // Build http parameter.
  $http = array(
    'timeout' => 60,
    'url' => API_URL_BASE . $prefixes[$posttype] . $api . API_SUFFIX
  );
  if ($posttype == YAPEAL_API_EVE || $posttype == YAPEAL_API_MAP ||
    $posttype == YAPEAL_API_SERVER) {
    // Global APIs like eve, map, and server don't use POST data.
    $http['method'] = 'GET';
  } else {
    // Setup for POST query.
    $http['method'] = 'POST';
    $http['content'] = http_build_query($postdata, NULL, '&');
  }; // if $posttype=YAPEAL_API_EVE||...
  $mess = 'Setup cURL connection in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_CURL, 1) &&
  $tracing->logTrace(YAPEAL_TRACE_CURL, $mess);
  // Setup new cURL connection with options.
  $sh = new CurlRequest($http);
  $mess = 'cURL connect to Eve API in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_CURL, 1) &&
  $tracing->logTrace(YAPEAL_TRACE_CURL, $mess);
  // Try to get XML.
  $result = $sh->exec();
  // Now check for errors.
  if ($result['curl_error']) {
    $mess = 'cURL error' . PHP_EOL;
    if ($http['content']) {
      $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
    };
    $mess .= 'Error code: ' . $result['curl_errno'] . ' for ';
    $mess .= $http['url'] . PHP_EOL;
    $mess .= 'Error message: ' . $result['curl_error'];
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS . 'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess, 1);
  };
  if (200 != $result['http_code']) {
    $mess = 'HTTP error' . PHP_EOL;
    if ($http['content']) {
      $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
    };
    $mess .= 'Error code: ' . $result['http_code'] . ' for ';
    $mess .= $http['url'] . PHP_EOL;
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS . 'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess, 2);
  };
  if (!$result['body']) {
    $mess = 'API data error' . PHP_EOL;
    if ($http['content']) {
      $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
    };
    $mess .= 'Data empty for ' . $http['url'] . PHP_EOL;
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS . 'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess, 3);
  };
  if (!mb_strpos($result['body'], '<eveapi version="')) {
    $result = FALSE;
    $mess = 'API data error' . PHP_EOL;
    if ($http['content']) {
      $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
    };
    $mess .= 'No XML returned for ' . $http['url'] . PHP_EOL;
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS . 'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess, 4);
  };
  $mess = 'Before simplexml_load_string';
  $tracing->activeTrace(YAPEAL_TRACE_REQUEST, 0) &&
  $tracing->logTrace(YAPEAL_TRACE_REQUEST, $mess);
  $xml = simplexml_load_string($result['body']);
  if (isset($xml->error[0])) {
    $mess = 'Eve API error' . PHP_EOL;
    if ($http['content']) {
      $mess .= 'Post parameters: ' . $http['content'] . PHP_EOL;
    };
    $mess .= 'Error code: ' . (int)$xml->error[0]['code'] . ' for ';
    $mess .= $http['url'] . PHP_EOL;
    $mess .= 'Error message: ' . (string)$xml->error[0];
    if (YAPEAL_CACHE_XML) {
      $cacheFile = $cachePath . 'error_' . $cacheFileName;
      if (is_dir($cachePath) && is_writeable($cachePath)) {
        file_put_contents($cacheFile, $xml->asXML());
      };
    }; // if YAPEAL_CACHE_XML
    // Throw exception
    require_once YAPEAL_CLASS . 'EVE_API_Exception.class.php';
    // Have to use API error code for special API error handling to work.
    throw new EVE_API_Error_Exception($mess, (int)$xml->error[0]['code']);
  };
  if (YAPEAL_CACHE_XML) {
    $cacheFile = $cachePath . 'error_' . $cacheFileName;
    // Delete old XML error file if it exists
    if (file_exists($cacheFile) && is_writeable($cacheFile)) {
      unlink($cacheFile);
    };
    $cacheFile = $cachePath . $cacheFileName;
    if (is_dir($cachePath) && is_writeable($cachePath)) {
      file_put_contents($cacheFile, $xml->asXML());
    };
  }; // if YAPEAL_CACHE_XML
  return $xml;
}; // function getAPIinfo
?>
