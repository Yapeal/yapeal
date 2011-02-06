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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
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
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Class used to pull Eve APIs for account section.
 *
 * @package Yapeal
 * @subpackage Api_sections
 */
class SectionAccount {
  /**
   * @var array Holds the list of APIs for this section.
   */
  private $apiList;
  /**
   * @var string Holds section name.
   */
  private $section;
  /**
   * Constructor
   *
   * @param array $allowedAPIs An array of admin allowed APIs in this section.
   * Used to limit which APIs out of the list of APIs from this section will be
   * fetched.
   */
  public function __construct($allowedAPIs) {
    $this->section = strtolower(str_replace('Section', '', __CLASS__));
    $path = YAPEAL_CLASS . 'api' . DS;
    $knownApis = FilterFileFinder::getStrippedFiles($path, $this->section);
    $this->apiList = array_intersect($allowedAPIs, $knownApis);
  }
  /**
   * Function called by Yapeal.php to start section pulling XML from servers.
   *
   * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
   */
  public function pullXML() {
    $apiCount = 0;
    $apiSuccess = 0;
    try {
      $userList = $this->getRegisteredUsers();
      if (count($userList) == 0) {
        $mess = 'No active users for account section';
        trigger_error($mess, E_USER_NOTICE);
        return FALSE;
      };// if empty $userList ...
      // Ok now that we have a list of users we can check which APIs need updated.
      foreach ($userList as $usr) {
        $userID = $usr['userID'];
        try {
          // Grab a user object.
          $user = new RegisteredUser($userID, FALSE);
        }
        catch (Exception $e) {
          // Can't update users that don't exist or cause errors.
          continue;
        }
        if ($user->isActive == 0) {
          // Skip inactive users.
          continue;
        };
        if (isset($user->fullApiKey)) {
          $apiKey = $user->fullApiKey;
        } else {
          $apiKey = $user->limitedApiKey;
        };
        // Get a list of allowed APIs
        $apis = array_intersect($this->apiList, explode(' ', $user->activeAPI));
        if (count($apis) == 0) {
          $mess = 'None of the allowed APIs are currently active for ' . $userID;
          trigger_error($mess, E_USER_NOTICE);
          continue;
        };
        // Randomize order in which APIs are tried if there is a list.
        if (count($apis) > 1) {
          shuffle($apis);
        };
        foreach ($apis as $api) {
          // If the cache for this API has expire try to get update.
          if (CachedUntil::cacheExpired($api, $userID) === TRUE) {
            ++$apiCount;
            $class = $this->section . $api;
            $tableName = YAPEAL_TABLE_PREFIX . $class;
            // These are passed on to the API class instance and used as part of
            // hash for lock.
            $params = array('apiKey' => $apiKey, 'userID' => $userID);
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
                $mess = 'Failed to get lock for ' . $class . $hash;
                trigger_error($mess, E_USER_NOTICE);
                continue;
              };// if $con->GetOne($sql) ...
            }
            catch(ADODB_Exception $e) {
              continue;
            }
            // Give each API 60 seconds to finish. This should never happen but is
            // here to catch runaways.
            set_time_limit(60);
            $instance = new $class($params);
            if ($instance->apiStore()) {
              ++$apiSuccess;
            };
            $instance = null;
          };// if CachedUntil::cacheExpired...
          // See if Yapeal has been running for longer than 'soft' limit.
          if (YAPEAL_MAX_EXECUTE < time()) {
            $mess = 'Yapeal has been working very hard and needs a break';
            trigger_error($mess, E_USER_NOTICE);
            exit;
          };// if YAPEAL_MAX_EXECUTE < time() ...
        };// foreach $apis ...
      };// foreach $userList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info.
    }
    // Only truly successful if all APIs were fetched and stored.
    if ($apiCount == $apiSuccess) {
      return TRUE;
    } else {
      return FALSE;
    }// else $apiCount == $apiSuccess ...
  }// function pullXML
  /**
   * Gets a list of users from RegisteredUser.
   *
   * @return array Returns the list of users.
   *
   * @throws ADODB_Exception for any errors.
   */
  private function getRegisteredUsers() {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    // Generate a list of user(s) we need to do updates for
    $sql = 'select `userID`';
    $sql .= ' from ';
    $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredUser`';
    $result = $con->GetAll($sql);
    // Randomize order so no one user can starve the rest in case of errors,
    // etc.
    if (count($result) > 1) {
      shuffle($result);
    };
    return $result;
  }// function getRegisteredUsers
}
?>
