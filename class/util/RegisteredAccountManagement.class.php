<?php
/**
 * RegisteredAccountManagement class
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
 * Use to manage Registered Users, Character, and Corporation in Yapeal.
 *
 * @package Yapeal
 */
class RegisteredAccountManagement {
  /**
   * @var array Holds types of allowed fields for $this->user
   */
  static $types = array('userID'=>'I', 'fullApiKey'=>'C', 'limitedApiKey'=>'C');

  /**
   * @var array Holds the information about the user.
   */
  protected $user = array();

  /**
   * @var array Holds the information about the characters from $this->user.
   */
  protected $characters = array();

  /**
   * @var array Holds the infomation about the corporations of $this->characters.
   */
  protected $corporations = array();

  /**
   * Default action is to return information from $this->user.
   *
   * @param string $index Which element to get value of.
   *
   * @return mixed Value of the element in $this->user or
   * Null if it doesn't exist.
   */
  public function __get($index) {
    if (array_key_exists($index,$this->user)) {
      return $this->user[$index];
    };
    return NULL;
  }

  /**
   * Default action is to set information in $this->user.
   *
   * @param string $index Which element to set value for.
   * @param mixed $value Value to set element to.
   *
   * @return boolean Returns TRUE if element already existed, FALSE if not.
   */
  public function __set($index, $value) {
    $ret = FALSE;
    if (array_key_exists($index, self::$types)) {
      if (array_key_exists($index,$this->user)) {
        $ret = TRUE;
      };
      $this->user[$index] = $value;
    };
    return $ret;
  }

  /**
   * Constructor
   *
   * @param integer $userID User Id to set in $this->user.
   * @param string $apiKey Full API Key for user.
   * @param string $limited Limited API key for user.
   */
  public function __construct($userID, $apiKey, $limited) {
    if (isset($userID) && is_int($userID)) {
      $this->user['userID'] = $userID;
    } else {
      $this->user['userID'] = 0;
    };
    if (isset($apiKey) && is_string($apiKey) && !empty($apiKey)) {
      $this->user['fullApiKey'] = $apiKey;
    } else {
      $this->user['fullApiKey'] = '';
    };
    if (isset($limited) && is_string($limited) && !empty($limited)) {
      $this->user['limitedApiKey'] = $limited;
    } else {
      $this->user['limitedApiKey'] = '';
    };
    require_once YAPEAL_INC . 'elog.inc';
    require_once YAPEAL_INC . 'common_db.inc';
  }

  /**
   * Load character(s) from database.
   *
   * @param mixed $characters String with name of character or array with list
   * of characters to load from database.
   *
   * @return integer number of character(s) that were loaded.
   */
  protected function loadCharacters($characters) {
    try {
      $api = 'RegisteredCharacter';
      $con = connect(YAPEAL_DSN);
      $sql = 'select characterID,corporationID,corporationName,isActive,name,';
      $sql.= 'userID';
      $sql.= ' from `' . YAPEAL_DB . '`.`' . $api . '`';
      $sql.= ' where userID=' . $this->user['userID'];
      if (isset($characters) && !empty($characters) &&
        (is_string($characters) || is_array($characters))) {
        $sql .= ' and name in ';
        if (is_string($characters)) {
          $characters = array($characters);
        };
        foreach ($characters as $char) {
          $chars[] = $con->qstr($char);
        };
        $sql .= '(' . implode(',', $chars) . ')';
        $result = $con->GetAll($sql);
        if ($ret=count($result)) {
          $this->characters = array();
          foreach ($result as $record) {
            $this->characters[$record['name']] = $record;
          };
        };// if count $result ...
      };// if isset $characters && ...
      return $ret;
    }
    catch (ADODB_Exception $e) {
      return 0;
    }
  }

  /**
   * Activate character(s) to receive API updates.
   *
   * @param mixed $characters String with name of character or array with list
   * of characters that need activated to receive API updates.
   *
   * @return integer number of character(s) that were activated.
   */
  protected function activateCharacters($characters) {
    $ret = 0;
    if (isset($characters) && !empty($characters) &&
      (is_string($characters) || is_array($characters))) {
      if (is_string($characters)) {
        $characters = array($characters);
      };
      foreach ($characters as $char) {
        if (array_key_exists($char,$this->characters)) {
          $this->characters[$char]['isActive'] = 1;
          ++$ret;
        };
      };// foreach $characters
    };// if isset $characters && ...
    return $ret;
  }

  /**
   * Deactivate character(s) from receiving API updates.
   *
   * @param mixed $characters String with name of character or array with list
   * of characters that need deactivated from receiving API updates.
   *
   * @return integer number of character(s) that were deactivated.
   */
  protected function deactivateCharacters($characters) {
    $ret = 0;
    if (isset($characters) && !empty($characters) &&
      (is_string($characters) || is_array($characters))) {
      if (is_string($characters)) {
        $characters = array($characters);
      };
      foreach ($characters as $char) {
        if (array_key_exists($char,$this->characters)) {
          $this->characters[$char]['isActive'] = 0;
          ++$ret;
        };
      };// foreach $characters
    };// if isset $characters && ...
    return $ret;
  }

  /**
   * Saves character(s) data to database
   *
   * @param mixed $characters String with name of character or array with list
   * of characters that we wish to save back to database.
   *
   * @return integer Number of characters save into database.
   */
  protected function saveCharacters($characters) {
    $ret = 0;
    try {
      $table = '`' . YAPEAL_DB .'`.`RegisteredCharacter`';
      if (isset($characters) && !empty($characters) &&
        (is_string($characters) || is_array($characters))) {
        if (is_string($characters)) {
          $characters = array($characters);
        };
        $data = array();
        $types = array('characterID'=>'I', 'corporationID'=>'I',
          'corporationName'=>'C', 'isActive'=>'C',
          'name'=>'C', 'userID'=>'I');
        foreach ($characters as $char) {
          if (array_key_exists($char,$this->characters)) {
            $data[] = $this->characters[$char];
          }
        };// foreach $characters
        multipleUpsert($data, $types, $table, YAPEAL_DSN);
      };// isset $characters && ...
    }
    catch (ADODB_Exception $e) {
      return 0;
    }
  }

}
?>
