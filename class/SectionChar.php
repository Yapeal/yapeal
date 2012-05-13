<?php
/**
 * Contains Section Char class.
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
 * Class used to pull Eve APIs for char section.
 *
 * @package Yapeal
 * @subpackage Api_sections
 */
class SectionChar extends ASection {
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
          $mess = 'No characters for char section';
          Logger::getLogger('yapeal')->info($mess);
        };
        return FALSE;
      };// if empty $result ...
      // Build name of filter based on mode.
      $filter = array($this, YAPEAL_REGISTERED_MODE . 'Filter');
      $charList = array_filter($result, $filter);
      if (empty($charList)) {
        if (Logger::getLogger('yapeal')->isInfoEnabled()) {
          $mess = 'No active characters for char section';
          Logger::getLogger('yapeal')->info($mess);
        };
        return FALSE;
      };
      // Randomize order so no one character can starve the rest in case of
      // errors, etc.
      if (count($charList) > 1) {
        shuffle($charList);
      };
      foreach ($charList as $chr) {
        // Skip characters with no APIs.
        if ($chr['mask'] == 0) {
          continue;
        };
        $apis = $this->am->maskToAPIs($chr['mask'], $this->section);
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
          if (CachedUntil::cacheExpired($api, $chr['characterID']) === TRUE) {
            ++$apiCount;
            $class = $this->section . $api;
            // These are passed on to the API class instance and used as part of
            // hash for lock.
            $params = array('keyID' => $chr['keyID'], 'vCode' => $chr['vCode'],
              'characterID' => $chr['characterID']);
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
      };// foreach $charList
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
    }
    // Only truly successful if API was fetched and stored.
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
    $sql = 'select akb.`keyID`,akb.`characterID`,urk.`vCode`,aaki.`expires`,';
    switch (YAPEAL_REGISTERED_MODE) {
      case 'required':
        $sql .= 'urc.`isActive` as "RCActive",aaki.`accessMask`,';
        $sql .= 'urc.`activeAPIMask` as "RCMask"';
        $sql .= ' from';
        $sql .= ' `' . YAPEAL_TABLE_PREFIX . 'accountKeyBridge` as akb';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo` as aaki';
        $sql .= ' on (akb.`keyID` = aaki.`keyID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'utilRegisteredKey` as urk';
        $sql .= ' on (akb.`keyID` = urk.`keyID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'accountCharacters` as ac';
        $sql .= ' on (akb.`characterID` = ac.`characterID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCharacter` as urc';
        $sql .= ' on (akb.`characterID` = urc.`characterID`)';
        $sql .= ' where';
        $sql .= ' aaki.`type` in ("Account","Character")';
        break;
      case 'optional':
        $sql .= 'urk.`isActive` as "RKActive",urc.`isActive` as "RCActive",';
        $sql .= 'aaki.`accessMask`,urk.`activeAPIMask` as "RKMask",';
        $sql .= 'urc.`activeAPIMask` as "RCMask"';
        $sql .= ' from';
        $sql .= ' `' . YAPEAL_TABLE_PREFIX . 'accountKeyBridge` as akb';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo` as aaki';
        $sql .= ' on (akb.`keyID` = aaki.`keyID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'utilRegisteredKey` as urk';
        $sql .= ' on (akb.`keyID` = urk.`keyID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'accountCharacters` as ac';
        $sql .= ' on (akb.`characterID` = ac.`characterID`)';
        $sql .= ' left join `' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCharacter` as urc';
        $sql .= ' on (akb.`characterID` = urc.`characterID`)';
        $sql .= ' where';
        $sql .= ' aaki.`type` in ("Account","Character")';
        break;
      case 'ignored':
        $sql .= 'urk.`isActive` as "RKActive",aaki.`accessMask`,';
        $sql .= 'urk.`activeAPIMask` as "RKMask"';
        $sql .= ' from';
        $sql .= ' `' . YAPEAL_TABLE_PREFIX . 'accountKeyBridge` as akb';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo` as aaki';
        $sql .= ' on (akb.`keyID` = aaki.`keyID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'utilRegisteredKey` as urk';
        $sql .= ' on (akb.`keyID` = urk.`keyID`)';
        $sql .= ' join `' . YAPEAL_TABLE_PREFIX . 'accountCharacters` as ac';
        $sql .= ' on (akb.`characterID` = ac.`characterID`)';
        $sql .= ' where';
        $sql .= ' aaki.`type` in ("Account","Character")';
        break;
    };
    return $sql;
  }// function getSQLQuery
  /**
   * Filter used when YAPEAL_REGISTERED_MODE == 'ignored'.
   *
   * This function is used to filter out non-active rows and merge all of the
   * different masks into one for each row.
   *
   * All the settings in utilRegisteredCharacter are ignored and the ones in
   * utilRegisteredKey used instead. If any of the optional settings in
   * utilRegisteredKey are null then returns FALSE but no error messages.
   *
   * @param array $row The row currently being checked.
   *
   * @return bool Returns TRUE if row should exist in result.
   */
  protected function ignoredFilter(&$row) {
    // Filter out if isActive is NULL or FALSE
    if (is_null($row['RKActive']) || $row['RKActive'] == 0) {
      return FALSE;
    };
    // If expire is not empty but has expired then return FALSE.
    if (!is_null($row['expires']) && gmdate('Y-m-d H:i:s') > $row['expires']) {
      return FALSE;
    };
    $row['mask'] = $this->mask;
    if (!is_null($row['RKMask'])) {
      $row['mask'] &= $row['RKMask'];
    } else {
      // Filter out if ActiveAPIMask is NULL
      return FALSE;
    };
    // Use APIKeyInfo mask if available.
    if (!is_null($row['accessMask'])) {
      $row['mask'] &= $row['accessMask'];
    };
    return TRUE;
  }// function ignoredFilter
  /**
   * Filter used when YAPEAL_REGISTERED_MODE == 'optional'.
   *
   * This function is used to filter out non-active rows and merge all of the
   * different masks into one for each row.
   *
   * The best way to view this mode is it allows you to farther restrict the
   * settings in utilSections using the optional settings from
   * utilRegisteredKey and utilRegisteredCharacter if you choose to. Any
   * non-null optional settings in utilRegisteredCharacter has priority over the
   * ones in utilRegisteredKey which are ignored in that case.
   *
   * @param array $row The row currently being checked.
   *
   * @return bool Returns TRUE if row should exist in result.
   */
  protected function optionalFilter(&$row) {
    // If isActive from utilRegisteredCharacter is not empty and set to FALSE
    // then return FALSE.
    if (!is_null($row['RCActive']) && $row['RCActive'] == 0) {
      return FALSE;
      // Else if isActive from utilRegisteredCharacter is null and isActive from
      // utilRegisteredKey is not empty and set to FALSE then return FALSE.
    } elseif (is_null($row['RCActive']) && !is_null($row['RKActive'])
      && $row['RKActive'] == 0) {
      return FALSE;
    };
    // If expire is not empty but has expired then return FALSE.
    if (!is_null($row['expires']) && gmdate('Y-m-d H:i:s') > $row['expires']) {
      return FALSE;
    };
    $row['mask'] = $this->mask;
    // Use Character mask if not empty ...
    if (!is_null($row['RCMask'])) {
      $row['mask'] &= $row['RCMask'];
      // else use key mask if not empty.
    } elseif (!is_null($row['RKMask'])) {
      $row['mask'] &= $row['RKMask'];
    };
    // Use APIKeyInfo mask if available.
    if (!is_null($row['accessMask'])) {
      $row['mask'] &= $row['accessMask'];
    };
    return TRUE;
  }// function optionalFilter
  /**
   * Filter used when YAPEAL_REGISTERED_MODE == 'required'.
   *
   * This function is used to filter out non-active rows and merge all of the
   * different masks into one for each row.
   *
   * The best way to view this mode is it forces you to farther restrict the
   * settings in utilSections using the optional settings from
   * utilRegisteredCharacter. The optional settings become required with the
   * exception of proxy which is still optional so activeAPIMask and isActive
   * can no longer be null. This mode is much like the old legacy mode of Yapeal.
   *
   * @param array $row The row currently being checked.
   *
   * @return bool Returns TRUE if row should exist in result.
   */
  protected function requiredFilter(&$row) {
    // isActive is not optional.
    if (is_null($row['RCActive'])) {
      $mess = 'IsActive can not be null in utilRegisteredCharacter when';
      $mess .= ' registered_mode = "required"';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    // Deactivated.
    if ($row['RCActive'] == 0) {
      return FALSE;
    };
    // If expire is not empty but has expired then return FALSE.
    if (!is_null($row['expires']) && gmdate('Y-m-d H:i:s') > $row['expires']) {
      return FALSE;
    };
    // activeAPIMask is not optional.
    if (is_null($row['RCMask'])) {
      $mess = 'activeAPIMask can not be null in utilRegisteredCharacter when';
      $mess .= ' registered_mode = "required"';
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    $row['mask'] = $this->mask & $row['RCMask'];
    // Use APIKeyInfo mask if available.
    if (!is_null($row['accessMask'])) {
      $row['mask'] &= $row['accessMask'];
    };
    return TRUE;
  }// function requiredFilter
}

