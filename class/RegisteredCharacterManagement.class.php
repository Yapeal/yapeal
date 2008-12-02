<?php
/**
 * RegisteredCharacterManagement class
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
/* This would need to be changed if this file isn't in another path at same
* level as 'inc' directory where common_emt.inc is.
*/
// Move up and over to 'inc' directory to read common_backend.inc
$path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
$path.= '..' . DIRECTORY_SEPARATOR . 'inc';
$path.= DIRECTORY_SEPARATOR . 'common_backend.inc';
require_once realpath($path);
/* **************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* **************************************************************************/
require_once YAPEAL_CLASS . 'ILoadApiTable.php';
require_once YAPEAL_CLASS . 'IFetchStoreApiTable.php';
/**
 * Use to manage Registered Characters table in Yapeal.
 *
 * @package Yapeal
 */
class RegisteredCharacterManagement implements ILoadApiTable, IFetchStoreApiTable {
  /**
   * @var array Holds types of allowed fields for $this->characters
   */
  static $types = array('characterID'=>'I', 'corporationID'=>'I', 'corporationName'=>'C', 'graphic'=>'B', 'graphicType'=>'C', 'isActive'=>'L', 'name'=>'C', 'userID'=>'I');

  /**
   * @var array Holds the information about the character.
   */
  protected $character = array();

  /**
   * Default action is to return information from $this->characters.
   *
   * @param string $index Which element to get value of.
   *
   * @return mixed Value of the element in $this->character or
   * Null if it doesn't exist.
   */
  public function __get($index) {
    if (array_key_exists($index,$this->character)) {
      return $this->character[$index];
    };
    return NULL;
  }

  /**
   * Default action is to set information in $this->character.
   *
   * @param string $index Which element to set value for.
   * @param mixed $value Value to set element to.
   *
   * @return boolean Returns TRUE if element already existed, FALSE if not.
   */
  public function __set($index, $value) {
    $ret = FALSE;
    if (array_key_exists($index, RegisteredCharacterManagement::$types)) {
      if (array_key_exists($index,$this->character)) {
        $ret = TRUE;
      };
      $this->character[$index] = $value;
    };
    return $ret;
  }

  /**
   * Constructor
   *
   * @param integer $characterID Character Id to set in $this->character.
   * @param integer $corporationID Corporation Id of character.
   * @param string  $corporationName Corporation name of character.
   * @param string  $graphic Small picture of character.
   * @param string  $graphicType Type of picture (jpg,png,gif).
   * @param boolean $isActive Make character active to receive updates.
   * @param string  $name Character's name.
   * @param integer $userID User ID that character belongs to.
   */
  public function __construct($characterID = NULL, $corporationID = NULL,
    $corporationName = NULL, $graphic = NULL, $graphicType = NULL,
    $isActive = NULL, $name = NULL, $userID = NULL) {
    if (isset($characterID) && is_int($characterID)) {
      $this->character['characterID'] = $characterID;
    } else {
      $this->character['characterID'] = 0;
    };
    if (isset($corporationID) && is_int($corporationID)) {
      $this->character['corporationID'] = $corporationID;
    } else {
      $this->character['corporationID'] = 0;
    };
    if (isset($corporationName) && is_string($corporationName) &&
      !empty($corporationName)) {
      $this->character['corporationName'] = $corporationName;
    } else {
      $this->character['corporationName'] = '';
    };
    if (isset($graphic) && is_string($graphic) && !empty($graphic)) {
      $this->character['graphic'] = $graphic;
    } else {
      $this->character['graphic'] = NULL;
    };
    if (isset($graphicType) && is_string($graphicType) &&
      !empty($graphicType)) {
      $this->character['graphicType'] = $graphicType;
    } else {
      $this->character['graphicType'] = '';
    };
    if (isset($isActive) && is_bool($isActive)) {
      $this->character['isActive'] = $isActive;
    } else {
      $this->character['isActive'] = FALSE;
    };
    if (isset($name) && is_string($name) && !empty($name)) {
      $this->character['name'] = $name;
    } else {
      $this->character['name'] = '';
    };
    if (isset($userID) && is_int($userID)) {
      $this->character['userID'] = $userID;
    } else {
      $this->character['userID'] = 0;
    };
    require_once YAPEAL_INC . 'elog.inc';
    require_once YAPEAL_INC . 'common_db.inc';
  }

  /**
   * Used to load an item by ID from database.
   *
   * @param mixed $item ID of an item to load. Can be an integer for normal IDs
   * or string for big integer IDs
   * @param string $field column name to use in where clause.
   *
   * @return array Returns an array containing item or NULL if item not found.
   */
  function apiLoadByID($item = NULL, $field = NULL) {
    $ret = FALSE;
    try {
      $api = 'RegisteredCharacter';
      $con = connect(DSN_UTIL_WRITER);
      // If we have just a field and it's value is set we can try that way.
      if ((!isset($item) || !is_int($item)) && isset($field) &&
        array_key_exists($field, RegisteredCharacterManagement::$types) &&
        !empty($this->character[$field])) {
        $item = $this->character[$field];
        // If we know at least characterID we can try to get the rest.
      } else if (!empty($this->character['characterID'])) {
        $item = $this->character['characterID'];
        $field = 'characterID';
      };
      // Can't load anything without $item and $field
      if (empty($item) || empty($field)) {
        return FALSE;
      };
      $item = $con->qstr($item);
      $field = '`' . $field .'`';
      $cols = array();
      $sql = 'select';
      foreach(array_keys(RegisteredCharacterManagement::$types) as $column) {
        $cols[] = '`' . $column .'`';
      };
      $sql .= ' ' . implode(',', $cols);
      $sql.= ' from `' . DB_UTIL . '`.`' . $api . '`';
      $sql.= ' where ' . $field . '=' . $item;
      $result = $con->GetAll($sql);
      if (count($result) == 1) {
        $this->character = $result;
        $ret = TRUE;
      };// if count $result ...
      return $ret;
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
  }

  /**
   * Used to load a named item from database.
   *
   * @param string $item Name of an item to load.
   * @param string $field column name to use in where clause.
   *
   * @return array Returns an array containing item or NULL if item not found.
   */
  function apiLoadByName($item = NULL, $field = NULL) {
    $ret = FALSE;
    try {
      $api = 'RegisteredCharacter';
      $con = connect(DSN_UTIL_WRITER);
      // If we have just a field and it's value is set we can try that way.
      if ((!isset($item) || !is_int($item)) && isset($field) &&
        array_key_exists($field, RegisteredCharacterManagement::$types) &&
        !empty($this->character[$field])) {
        $item = $this->character[$field];
        // If we know at least name we can try to get the rest.
      } else if (!empty($this->character['name'])) {
        $item = $this->character['name'];
        $field = 'name';
      };
      // Can't load anything without $item and $field
      if (empty($item) || empty($field)) {
        return FALSE;
      };
      $item = $con->qstr($item);
      $field = '`' . $field .'`';
      $cols = array();
      $sql = 'select';
      foreach(array_keys(RegisteredCharacterManagement::$types) as $column) {
        $cols[] = '`' . $column .'`';
      };
      $sql .= ' ' . implode(',', $cols);
      $sql.= ' from `' . DB_UTIL . '`.`' . $api . '`';
      $sql.= ' where ' . $field . '=' . $item;
      $result = $con->GetAll($sql);
      if (count($result) == 1) {
        $this->character = $result;
        $ret = TRUE;
      };// if count $result ...
      return $ret;
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
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
      $table = '`' . DB_UTIL .'`.`RegisteredCharacter`';
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
        multipleUpsert($data, $types, $table, DSN_UTIL_WRITER);
      };// isset $characters && ...
    }
    catch (ADODB_Exception $e) {
      return 0;
    }
  }

}
?>
