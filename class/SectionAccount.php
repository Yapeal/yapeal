<?php
/**
 * Contains Section Account class.
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
 * Class used to pull Eve APIs for account section.
 *
 * @package Yapeal
 * @subpackage Api_sections
 */
class SectionAccount extends ASection {
  /**
   * Constructor
   */
  public function __construct() {
    $this->section = strtolower(str_replace('Section', '', __CLASS__));
    parent::__construct();
  }// function __construct
  /**
   * Function called by Yapeal.php to start section pulling XML from servers.
   *
   * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
   */
  public function pullXML() {
    if ($this->abort === TRUE) {
      return FALSE;
    };
    $apiCount = 0;
    $apiSuccess = 0;
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = $this->getSQLQuery();
      $result = $con->GetAll($sql);
      if (count($result) == 0) {
        if (Logger::getLogger('yapeal')->isInfoEnabled()) {
          $mess = 'No keys for account section';
          Logger::getLogger('yapeal')->info($mess);
        };
        return FALSE;
      };// if empty $result ...
      // Build name of filter based on mode.
      $filter = array($this, YAPEAL_REGISTERED_MODE . 'Filter');
      $keyList = array_filter($result, $filter);
      if (empty($keyList)) {
        if (Logger::getLogger('yapeal')->isInfoEnabled()) {
          $mess = 'No active keys for account section';
          Logger::getLogger('yapeal')->info($mess);
        };
        return FALSE;
      };
      // Randomize order so no one key can starve the rest in case of
      // errors, etc.
      if (count($keyList) > 1) {
        shuffle($keyList);
      };
      // Ok now that we have a list of keys we can check which APIs need updated.
      foreach ($keyList as $ky) {
        // Skip keys with no APIs.
        if ($ky['mask'] == 0) {
          continue;
        };
        $apis = $this->am->maskToAPIs($ky['mask'], $this->section);
        if ($apis === FALSE) {
          $mess = 'Problem retrieving API list using mask';
          Logger::getLogger('yapeal')->warn($mess);
          continue;
        };
        // Randomize order in which APIs are tried if there is a list.
        if (count($apis) > 1) {
          shuffle($apis);
        };
        foreach ($apis as $api) {
          // If the cache for this API has expired try to get update.
          if (CachedUntil::cacheExpired($api, $ky['keyID']) === TRUE) {
            ++$apiCount;
            $class = $this->section . $api;
            // These are passed on to the API class instance and used as part of
            // hash for lock.
            $params = array('keyID' => $ky['keyID'], 'vCode' => $ky['vCode']);
            $parameters = '';
            foreach ($params as $k => $v) {
              $parameters .= $k . '=' . $v;
            };
            $hash = hash('sha1', $class . $parameters);
            // Use lock to keep from wasting time trying to do API that another
            // Yapeal is already working on.
            try {
              $con = YapealDBConnection::connect(YAPEAL_DSN);
              $sql = 'select get_lock(' . $con->qstr($hash) . ',5)';
              if ($con->GetOne($sql) != 1) {
                if (Logger::getLogger('yapeal')->isInfoEnabled()) {
                  $mess = 'Failed to get lock for ' . $class . $hash;
                  Logger::getLogger('yapeal')->info($mess);
                };
                continue;
              };// if $con->GetOne($sql) ...
            }
            catch(ADODB_Exception $e) {
              continue;
            }
            // Give each API 60 seconds to finish. This should never happen but
            // is here to catch runaways.
            set_time_limit(60);
            $instance = new $class($params);
            if ($instance->apiStore()) {
              ++$apiSuccess;
            };
            $instance = NULL;
          };// if CachedUntil::cacheExpired...
          // See if Yapeal has been running for longer than 'soft' limit.
          if (YAPEAL_MAX_EXECUTE < time()) {
            if (Logger::getLogger('yapeal')->isInfoEnabled()) {
              $mess = 'Yapeal has been working very hard and needs a break';
              Logger::getLogger('yapeal')->info($mess);
            };
            exit;
          };// if YAPEAL_MAX_EXECUTE < time() ...
        };// foreach $apis ...
      };// foreach $userList
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
    }
    // Only truly successful if all APIs were fetched and stored.
    if ($apiCount == $apiSuccess) {
      return TRUE;
    } else {
      return FALSE;
    }// else $apiCount == $apiSuccess ...
  }// function pullXML
  /**
   * Used to get the correct SQL for each mode of YAPEAL_REGISTERED_MODE.
   *
   * @return string Returns the SQL query string.
   */
  protected function getSQLQuery() {
    $sql = 'select urk.`keyID`,urk.`vCode`,urk.`activeAPIMask` as "RKMask",';
    $sql .= 'urk.`isActive` as "RKActive",aaki.`accessMask`,aaki.`type`';
    $sql .= ' from';
    $sql .= ' `' . YAPEAL_TABLE_PREFIX . 'utilRegisteredKey` as urk';
    $sql .= ' left join `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo` as aaki';
    $sql .= ' on (urk.`keyID` = aaki.`keyID`)';
    return $sql;
  }// function getSQLQuery
  /**
   * Filter used when YAPEAL_REGISTERED_MODE == 'ignored'.
   *
   * This function is used to filter out non-active rows and merge all of the
   * different masks into one for each row.
   *
   * In this mode the utilRegisteredKey table column act most like a hybrid
   * required mode without all the error messages. If the activeAPIMask or
   * isActive column is null the key will not be included in list to be
   * retrieved.
   *
   * @param array $row The row currently being checked.
   *
   * @return bool Returns TRUE if row should exist in result.
   */
  protected function ignoredFilter(&$row) {
    if (is_null($row['RKActive']) || $row['RKActive'] == 0) {
      return FALSE;
    };
    $row['mask'] = $this->mask;
    if (!is_null($row['RKMask'])) {
      // Since there is no mask value for APIKeyInfo in API mask for obvious
      // reasons it can only be controlled by mask from utilSections.
      $row['mask'] &= $row['RKMask'] | 1;
    } else {
      return FALSE;
    };
    if (!is_null($row['accessMask'])) {
      // Since there is no mask value for APIKeyInfo in API mask for obvious
      // reasons it can only be controlled by mask from utilSections.
      $row['mask'] &= $row['accessMask'] | 1;
    };
    // Can't get accountStatus API with corporation key or with an unknown key
    // type since it might be a corporation key.
    if (is_null($row['type']) || $row['type'] == 'Corporation') {
      $row['mask'] &= 1;
    };
    return TRUE;
  }// function ignoredFilter
  /**
   * Filter used when YAPEAL_REGISTERED_MODE == 'optional'.
   *
   * This function is used to filter out non-active rows and merge all of the
   * different masks into one for each row. If there is something in
   * utilRegisteredCharacter it gets priority and settings in utilRegisteredKey
   * are ignored. If not then utilRegisteredKey is used.
   *
   * @param array $row The row currently being checked.
   *
   * @return bool Returns TRUE if row should exist in result.
   */
  protected function optionalFilter(&$row) {
    if (!is_null($row['RKActive']) && $row['RKActive'] == 0) {
      return FALSE;
    };
    $row['mask'] = $this->mask;
    if (!is_null($row['accessMask'])) {
      // Since there is no mask value for APIKeyInfo in API mask for obvious
      // reasons it can only be controlled by mask from utilSections.
      $row['mask'] &= $row['accessMask'] | 1;
    };
    if (!is_null($row['RKMask'])) {
      // Since there is no mask value for APIKeyInfo in API mask for obvious
      // reasons it can only be controlled by mask from utilSections.
      $row['mask'] &= $row['RKMask'] | 1;
    };
    // Can't get accountStatus API with corporation key or with an unknown key
    // type since it might be a corporation key.
    if (is_null($row['type']) || $row['type'] == 'Corporation') {
      $row['mask'] &= 1;
    };
    return TRUE;
  }// function optionalFilter
  /**
   * Filter used when YAPEAL_REGISTERED_MODE == 'required'.
   *
   * This function is used to filter out non-active rows and merge all of the
   * different masks into one for each row.
   *
   * @param array $row The row currently being checked.
   *
   * @return bool Returns TRUE if row should exist in result.
   */
  protected function requiredFilter(&$row) {
    if (is_null($row['RKActive'])) {
      $mess = 'IsActive can not be null in utilRegisteredKey when';
      $mess .= ' registered_mode = "required"';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    if ($row['RKActive'] == 0) {
      return FALSE;
    };
    if (is_null($row['RKMask'])) {
      $mess = 'activeAPIMask can not be null in utilRegisteredKey when';
      $mess .= ' registered_mode = "required"';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    $row['mask'] = $this->mask;
    // Handle missing APIKeyInfo data.
    if (is_null($row['accessMask'])) {
      // Since there is no mask value for APIKeyInfo in API mask for obvious
      // reasons it can only be controlled by mask from utilSections.
      $row['mask'] &= $row['RKMask'] | 1;
    } else {
      // Since there is no mask value for APIKeyInfo in API mask for obvious
      // reasons it can only be controlled by mask from utilSections.
      $row['mask'] &= $row['accessMask'] & $row['RKMask'] | 1;
    };
    // Can't get accountStatus API with corporation key or with an unknown key
    // type since it might be a corporation key.
    if (is_null($row['type']) || $row['type'] == 'Corporation') {
      $row['mask'] &= 1;
    };
    return TRUE;
  }// function requiredFilter
}

