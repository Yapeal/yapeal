<?php
/**
 * RegisteredCharacterManagement class
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
require_once YAPEAL_CLASS . 'IFetchApiTable.php';
require_once YAPEAL_CLASS . 'ILoadApiTable.php';
require_once YAPEAL_CLASS . 'IStoreApiTable.php';
/**
 * Use to manage Registered Characters table in Yapeal.
 *
 * @package Yapeal
 */
class RegisteredCharacterManagement implements IFetchApiTable, ILoadApiTable,
  IStoreApiTable {
  /**
   * @var array Holds types of allowed fields for $this->character
   */
  static $types = array('characterID'=>'I', 'corporationID'=>'I',
    'corporationName'=>'C', 'graphic'=>'B', 'graphicType'=>'C', 'isActive'=>'L',
    'name'=>'C', 'userID'=>'I');

  /**
   * @var array Holds the information about the character.
   */
  protected $character = array('characterID'=>0, 'corporationID'=>0,
    'corporationName'=>'', 'graphic'=>NULL, 'graphicType'=>'', 'isActive'=>0,
    'name'=>'', 'userID'=>0);

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
    if (array_key_exists($index, self::$types)) {
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
    };
    if (isset($corporationID) && is_int($corporationID)) {
      $this->character['corporationID'] = $corporationID;
    };
    if (isset($corporationName) && is_string($corporationName) &&
      !empty($corporationName)) {
      $this->character['corporationName'] = $corporationName;
    };
    if (isset($graphic) && is_string($graphic) && !empty($graphic)) {
      $this->character['graphic'] = $graphic;
    };
    if (isset($graphicType) && is_string($graphicType) &&
      !empty($graphicType)) {
      $this->character['graphicType'] = $graphicType;
    };
    if (isset($isActive) && is_bool($isActive)) {
      $this->character['isActive'] = $isActive;
    };
    if (isset($name) && is_string($name) && !empty($name)) {
      $this->character['name'] = $name;
    };
    if (isset($userID) && is_int($userID)) {
      $this->character['userID'] = $userID;
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
      $con = connect(YAPEAL_DSN);
      // If we have just a field and it's value is set we can try that way.
      if ((!isset($item) || !is_int($item)) && isset($field) &&
        array_key_exists($field, self::$types) &&
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
      foreach(array_keys(self::$types) as $column) {
        $cols[] = '`' . $column .'`';
      };
      $sql .= ' ' . implode(',', $cols);
      $sql.= ' from `' . YAPEAL_DB . '`.`' . $api . '`';
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
      $table = '`' . YAPEAL_DB .'`.`RegisteredCharacter`';;
      $con = connect(YAPEAL_DSN);
      // If we have just a field and it's value is set we can try that way.
      if ((!isset($item) || !is_int($item)) && isset($field) &&
        array_key_exists($field, self::$types) &&
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
      foreach(array_keys(self::$types) as $column) {
        $cols[] = '`' . $column .'`';
      };
      $sql = 'select ' . implode(',', $cols);
      $sql.= ' from ' . $table;
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
   * Used to save an item into database.
   *
   * Parent item (object) should call all child(ren)'s apiStore() as appropriate.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  function apiStore() {
    if (isset($this->character['characterID'],
          $this->character['corporationID'],
          $this->character['corporationName'],
          $this->character['graphicType'],
          $this->character['isActive'],
          $this->character['name'],
          $this->character['userID']) &&
      !(empty($this->character['characterID']) ||
      empty($this->character['corporationID']) ||
      empty($this->character['corporationName']) ||
      empty($this->character['name']) ||
      empty($this->character['userID']))) {
      $table = '`' . YAPEAL_DB .'`.`RegisteredCharacter`';
      $data = $this->character;
      // Graphic BLOB requires special handling
      if (isset($this->character['graphic'])) {
        $graphic = $this->character['graphic'];
        $data['graphic'] = 'null';
      }
      try {
        upsert($data, self::$types, $table,
          YAPEAL_DSN);
        $where = '`characterID`=' . $this->character['characterID'];
        $con = connect(YAPEAL_DSN);
        $con->UpdateBlob($table, 'graphic', $graphic, $where);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      return TRUE;
    }
    return FALSE;
  }
}
?>
