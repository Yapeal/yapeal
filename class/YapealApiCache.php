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
   * @var integer Holds current Unix time to have consistant caching time.
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
   * @param string $owner Owner for current Eve API being cached. This maybe
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
    $this->hash = hash('sha1', $section . $api . $params);
    $this->ownerID = $owner;
    $this->section = $section;
    $this->postParams = $postParams;
    $this->vd = new YapealValidateXml($api, $section);
    $this->curTime = time();
    $this->cacheInterval = $this->getCachedInterval();
  }// function __constructor
  /**
   * Function used to save API XML to cache database table and/or file and
   * update utilCachedUntil table.
   *
   * @param string $xml The Eve API XML to be cached.
   *
   * @return bool Returns TRUE if XML was cached, FALSE otherwise.
   */
  public function cacheXml($xml) {
    if (empty($xml)) {
      $mess = 'XML was empty' . PHP_EOL;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if empty($xml) ...
    // Do a default setting for cacheUntil so Yapeal waits a bit before trying
    // again if something goes wrong. If everything works correctly time will be
    // set to new cachedUntil time decided by what API XML returns.
    $data = array( 'api' => $this->api, 'ownerID' => $this->ownerID,
      'section' => $this->section
    );
    $cu = new CachedUntil($data);
    // Use now + interval for cachedUntil.
    $cu->cachedUntil = gmdate('Y-m-d H:i:s', ($this->curTime + $this->cacheInterval));
    // check if XML is valid.
    $this->vd->xml = $xml;
    $this->vd->validateXML();
    // If cachedUntil that API supplied is longer use it.
    //if ($this->vd->getCachedUntil() > $cu->cachedUntil) {
    //  // Set cachedUntil to whatever API returned.
    //  $cu->cachedUntil = $this->vd->getCachedUntil();
    //};
    $cu->store();
    $cu = NULL;
    // Throw exception for any API errors.
    if (TRUE == $this->vd->isApiError()) {
      // Throw exception
      // Have to use API error code for special API error handling to work.
      $error = $this->vd->getApiError();
      throw new YapealApiErrorException($error['message'], $error['code']);
    };// if $this->vd->isApiError() ...
    switch (YAPEAL_CACHE_OUTPUT) {
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
        $mess = 'Invalid value of "' . YAPEAL_CACHE_OUTPUT;
        $mess .= '" for YAPEAL_CACHE_OUTPUT.';
        $mess .= ' Check that the setting in config/yapeal.ini is correct.';
        trigger_error($mess, E_USER_WARNING);
        return FALSE;
    };// switch YAPEAL_CACHE_OUTPUT ...
    if (FALSE == $this->vd->isValid()) {
      $mess = 'Caching invalid API XML for ' . $this->section . DS . $this->api;
      trigger_error($mess, E_USER_WARNING);
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
      $qb = new YapealQueryBuilder(YAPEAL_TABLE_PREFIX . 'utilXmlCache', YAPEAL_DSN);
      $row = array('api' => $this->api, 'hash' => $this->hash,
        'section' => $this->section, 'xml' => $xml);
      $qb->addRow($row);
      $qb->store();
    }
    catch (ADODB_Exception $e) {
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
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if !is_dir $cachePath ...
    if (!is_writable($cachePath)) {
      $mess = 'XML cache directory ' . $cachePath . ' is not writable';
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if !is_writable $cachePath ...
    $cacheFile = $cachePath . $this->api . $this->hash . '.xml';
    $ret = file_put_contents($cacheFile, $xml);
    if (FALSE == $ret || $ret == -1) {
      $mess = 'Could not cache XML to ' . $cacheFile;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if FALSE == $ret ||...
    return TRUE;
  }// function cacheXmlFile
  /**
   * Used to delete any cached XML.
   */
  public function delCachedApi() {
    switch (YAPEAL_CACHE_OUTPUT) {
      case 'both':
        $this->delCachedDatabase($xml);
        $this->delCachedFile($xml);
        break;
      case 'database':
        $this->delCachedDatabase($xml);
        break;
      case 'file':
        $this->delCachedFile($xml);
        break;
      default:
        $mess = 'Invalid value of "' . YAPEAL_CACHE_OUTPUT;
        $mess .= '" for YAPEAL_CACHE_OUTPUT.';
        $mess .= ' Check that the setting in config/yapeal.ini is correct.';
        trigger_error($mess, E_USER_WARNING);
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
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };
    if (!is_writable($cachePath)) {
      $mess = 'XML cache directory ' . $cachePath . ' is not writable';
      trigger_error($mess, E_USER_WARNING);
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
    switch (YAPEAL_CACHE_OUTPUT) {
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
        $mess = 'Invalid value of "' . YAPEAL_CACHE_OUTPUT;
        $mess .= '" for YAPEAL_CACHE_OUTPUT.';
        $mess .= ' Check that the setting in config/yapeal.ini is correct.';
        trigger_error($mess, E_USER_WARNING);
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
      trigger_error($mess, E_USER_WARNING);
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
   * Used to get the cache interval for this API.
   *
   * @return int Returns cache interval for this API.
   */
  private function getCachedInterval() {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $sql = 'select `interval`';
    $sql .= ' from ';
    $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilCachedInterval`';
    $sql .= ' where';
    try {
      $sql .= ' `section`=' . $con->qstr($this->section);
      $sql .= ' and `api`=' . $con->qstr($this->api);
      $result = (int)$con->getOne($sql);
    }
    catch (ADODB_Exception $e) {
      $result = 3600;// Use an hour as default.
    }
    return $result;
  }// function getCachedInterval
  /**
   * Returns if current cached XML is valid.
   *
   * @return bool Return TRUE if current XML was Validated and valid.
   */
  public function isValid() {
    return $this->vd->isValid();
  }// function isValid
}
?>
