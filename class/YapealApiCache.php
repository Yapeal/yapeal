<?php
/**
 * Contains YapealApiCache class.
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
 * Class used to manage caching of XML from Eve APIs.
 *
 * @package    Yapeal
 * @subpackage YapealAPICache
 */
class YapealApiCache {
  /**
   * @var string Name of the Eve API being cached.
   */
  protected $api;
  /**
   * @var string Cache interval for this API.
   */
  private $cacheInterval;
  /**
   * @var string Value from [Cache] section for cache_output.
   */
  private static $cacheOutput = 'file';
  /**
   * @var integer Holds current Unix time to have consistent caching time.
   */
  private $curTime;
  /**
   * @var string Holds SHA1 hash of $section, $api, $postParams.
   */
  private $hash;
  /**
   * @var string Holds the ownerID to be used when updating cachedUntil table.
   */
  protected $ownerID = 0;
  /**
   * @var array The list of any required params used in getting API.
   */
  protected $postParams;
  /**
   * @var string The api section that $api belongs to.
   */
  protected $section;
  /**
   * @var object Holds the validator.
   */
  private $vd;
  /**
   * @var string Hold the XML.
   */
  protected $xml;
  /**
   * Constructor
   *
   * @param string $api Name of the Eve API being cached.
   * @param string $section The api section that $api belongs to. For Eve
   * APIs will be one of account, char, corp, eve, map, or server.
   * @param string|int $owner Owner for current Eve API being cached. This maybe
   * empty for some APIs i.e. eve, map, and server.
   * @param array $postParams The list of required params used in getting API.
   * This maybe empty for some APIs i.e. eve, map, and server.
   */
  public function __construct($api, $section, $owner = 0, $postParams = array()) {
    $params = '';
    if (!empty($postParams)) {
      foreach ($postParams as $k => $v) {
        $params .= $k . '=' . $v;
      };
    };
    $this->api = $api;
    $this->hash = hash('sha1', $section . $api . $owner . $params);
    $this->ownerID = $owner;
    $this->section = $section;
    $this->postParams = $postParams;
    $this->vd = new YapealValidateXml($api, $section);
    $this->curTime = time();
    $ci = new CachedInterval();
    $this->cacheInterval = $ci->getInterval($api, $section);
  }// function __constructor
  /**
   * Function used to save API XML to cache database table and/or file and
   * update utilCachedUntil table.
   *
   * @param string $xml The Eve API XML to be cached.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   *
   * @throws YapealApiErrorException Throws YapealApiErrorException for any API
   * errors.
   */
  public function cacheXml($xml) {
    if (empty($xml)) {
      $mess = 'XML was empty' . PHP_EOL;
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// if empty($xml) ...
    $data = array( 'api' => $this->api, 'ownerID' => $this->ownerID,
      'section' => $this->section
    );
    $cu = new CachedUntil($data);
    // Update utilCachedUntil table with default short cache date/time. This
    // is changed when there is an API error that returns a different one or
    // normally is set to the new calculated date/time if there aren't any
    // errors.
    $cu->cachedUntil = YAPEAL_START_TIME;
    $cu->store();
    // check if XML is valid.
    $this->vd->xml = $xml;
    $this->vd->validateXML();
    // Throw exception for any API errors.
    if (TRUE == $this->vd->isApiError()) {
      // Throw exception
      // Have to use API error code for special API error handling to work.
      $error = $this->vd->getApiError();
      throw new YapealApiErrorException($error['message'], $error['code']);
    };// if $this->vd->isApiError() ...
    // Use now + interval + random value for cachedUntil.
    $until = $this->curTime + $this->cacheInterval;
    // Add random number of seconds to cache interval. Randomness is larger the
    // later in the day it is. Between 0 and 1 + 0 .. 23 hours * 15 seconds added.
    $until += mt_rand(0, ((1 + gmdate("G")) * 15));
    $cu->cachedUntil = gmdate('Y-m-d H:i:s', $until);
    $cu->store();
    $cu = NULL;
    switch (self::$cacheOutput) {
      case 'both':
        $this->cacheXmlDatabase($xml);
        $this->cacheXmlFile($xml);
        break;
      case 'database':
        $this->cacheXmlDatabase($xml);
        break;
      case 'file':
        $this->cacheXmlFile($xml);
        break;
      case 'none':
        return FALSE;
      default:
        $mess = 'Invalid value of "' . self::$cacheOutput;
        $mess .= '" for cache_output.';
        $mess .= ' Check that the setting in config/yapeal.ini is correct.';
        Logger::getLogger('yapeal')->warn($mess);
        return FALSE;
    };// switch YAPEAL_CACHE_OUTPUT ...
    if (FALSE == $this->vd->isValid()) {
      $mess = 'Caching invalid API XML for ' . $this->section . DS . $this->api;
      Logger::getLogger('yapeal')->warn($mess);
    };
    return TRUE;
  }// function cacheXml
  /**
   * Function used to save API XML into database table.
   *
   * @param string $xml The Eve API XML to be cached.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  private function cacheXmlDatabase($xml) {
    try {
      // Get a new query instance.
      $qb = new YapealQueryBuilder(YAPEAL_TABLE_PREFIX . 'utilXmlCache',
        YAPEAL_DSN, FALSE);
      $row = array('api' => $this->api, 'hash' => $this->hash,
        'section' => $this->section, 'xml' => $xml);
      $qb->addRow($row);
      $qb->store();
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    return TRUE;
  }// function cacheXmlDatabase
  /**
   * Used to save API XML into file.
   *
   * @param string $xml The Eve API XML to be cached.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  private function cacheXmlFile($xml) {
    // Build cache file path
    $cachePath = realpath(YAPEAL_CACHE . $this->section) . DS;
    if (!is_dir($cachePath)) {
      $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// if !is_dir $cachePath ...
    if (!is_writable($cachePath)) {
      $mess = 'XML cache directory ' . $cachePath . ' is not writable';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// if !is_writable $cachePath ...
    $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
    $ret = file_put_contents($cacheFile, $xml);
    if (FALSE == $ret || $ret == -1) {
      $mess = 'Could not cache XML to ' . $cacheFile;
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// if FALSE == $ret ||...
    return TRUE;
  }// function cacheXmlFile
  /**
   * Used to delete any cached XML.
   */
  public function delCachedApi() {
    switch (self::$cacheOutput) {
      case 'both':
        $this->delCachedDatabase();
        $this->delCachedFile();
        break;
      case 'database':
        $this->delCachedDatabase();
        break;
      case 'file':
        $this->delCachedFile();
        break;
      default:
        $mess = 'Invalid value of "' . self::$cacheOutput;
        $mess .= '" for cache_output.';
        $mess .= ' Check that the setting in config/yapeal.ini is correct.';
        Logger::getLogger('yapeal')->warn($mess);
    };// switch YAPEAL_CACHE_OUTPUT ...
  }// function delCachedApi
  /**
   * Used to delete any cached XML from database.
   *
   * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
   */
  private function delCachedDatabase() {
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
      $sql .= ' where';
      $sql .= ' `hash`=' . $con->qstr($this->hash);
      $con->Execute($sql);
    }
    catch (Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    return TRUE;
  }// function delCachedDatabase
  /**
   * Used to delete any cached XML from file.
   *
   * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
   */
  private function delCachedFile() {
    // Build cache file path
    $cachePath = realpath(YAPEAL_CACHE . $this->section) . DS;
    if (!is_dir($cachePath)) {
      $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    if (!is_writable($cachePath)) {
      $mess = 'XML cache directory ' . $cachePath . ' is not writable';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };// if !is_writable $cachePath ...
    $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
    if (!file_exists($cacheFile) || !is_file($cacheFile)) {
      return FALSE;
    }
    return @unlink($cacheFile);
  }// function getCachedFile
  /**
   * Used to fetch API XML from database table and/or file.
   *
   * @return mixed Returns XML if cached copy is available and not expired, else
   * returns FALSE.
   */
  public function getCachedApi() {
    switch (self::$cacheOutput) {
      case 'both':
        $xml = $this->getCachedDatabase();
        // If not cached in DB try file.
        if (FALSE === $xml) {
          $xml = $this->getCachedFile();
          // If XML was cached to file but not to database add it to database.
          if ($xml !== FALSE) {
            $this->cacheXmlDatabase($xml);
          };// if $xml !== FALSE ...
        };// if FALSE === $xml ...
        break;
      case 'database':
        $xml = $this->getCachedDatabase();
        break;
      case 'file':
        $xml = $this->getCachedFile();
        break;
      case 'none':
        return FALSE;
      default:
        $mess = 'Invalid value of "' . self::$cacheOutput;
        $mess .= '" for cache_output.';
        $mess .= ' Check that the setting in config/yapeal.ini is correct.';
        Logger::getLogger('yapeal')->warn($mess);
        return FALSE;
    };// switch YAPEAL_CACHE_OUTPUT ...
    $currentXML = strtotime($this->vd->getCurrentTime() . ' +0000') + $this->cacheInterval;
    // If already past cachedUntil need to get XML again.
    if ($this->curTime > $currentXML) {
      return FALSE;
    }
    return $xml;
  }// function getCachedApi
  /**
   * Used to fetch API XML from database table.
   *
   * @return mixed Returns XML if record is available and not expired, else
   * returns FALSE.
   */
  private function getCachedDatabase() {
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'select sql_no_cache `xml`';
      $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilXmlCache`';
      $sql .= ' where';
      $sql .= ' `hash`=' . $con->qstr($this->hash);
      $result = $con->GetOne($sql);
      if (empty($result)) {
        return FALSE;
      };
      // Validate the XML.
      $this->vd->xml = (string)$result;
      // Check if XML is valid.
      $this->vd->validateXML();
      $currentXML = strtotime($this->vd->getCurrentTime() . ' +0000') + $this->cacheInterval;
      // If already past cachedUntil need to get XML again.
      if ($this->curTime > $currentXML) {
        // Delete cached XML from database.
        $this->delCachedDatabase();
        return FALSE;
      };
    }
    catch (Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    return $result;
  }// function getCachedDatabase
  /**
   * Function used to fetch API XML from file.
   *
   * @return mixed Returns XML if file is available and not expired, else
   * returns FALSE.
   */
  private function getCachedFile() {
    // Build cache file path
    $cachePath = realpath(YAPEAL_CACHE . $this->section) . DS;
    if (!is_dir($cachePath)) {
      $mess = 'XML cache ' . $cachePath . ' is not a directory or does not exist';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
    $result = @file_get_contents($cacheFile);
    if (FALSE === $result || empty($result)) {
      return FALSE;
    };// if FALSE === $result ...
    // Validate the XML.
    $this->vd->xml = $result;
    $this->vd->validateXML();
    $currentXML = strtotime($this->vd->getCurrentTime() . ' +0000') + $this->cacheInterval;
    // If already past cachedUntil need to get XML again.
    if ($this->curTime > $currentXML) {
      // Delete cached XML from filesystem.
      $this->delCachedFile();
      return FALSE;
    };
    return $result;
  }// function getCachedFile
  /**
   * Returns if current cached XML is valid.
   *
   * @return bool Return TRUE if current XML was Validated and valid.
   */
  public function isValid() {
    return $this->vd->isValid();
  }// function isValid
  /**
   * function used to set constants from [Cache] section of the configuration file.
   *
   * @param array $section A list of settings for this section of configuration.
   */
  public static function setCacheSectionProperties(array $section) {
    self::$cacheOutput = $section['cache_output'];
  }// function setCacheSectionProperties
}
