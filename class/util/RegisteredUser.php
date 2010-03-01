<?php
/**
 * Contains RegisteredUser class.
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
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
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
 * Wrapper class for utilRegisteredUser table.
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class RegisteredUser extends ALimitedObject implements IGetBy {
  /**
   * List of all section APIs
   * @var array
   */
  private $apiList;
  /**
   * Set to TRUE if a database record exists.
   * @var bool
   */
  private $recordExists;
  /**
   * Table name
   * @var string
   */
  private $table;
  /**
   * Constructor
   *
   * @param integer $id Id of user wanted.
   * @param bool $create When $create is set to FALSE will throw DomainException
   * if $id doesn't exist in database.
   *
   * @throws InvalidArgumentException If $id isn't a number throws an
   * InvalidArgumentException.
   * @throws DomainException If $create is FALSE and a database record for $id
   * doesn't exist a DomainException will be thrown.
   */
  public function __construct($id = NULL, $create = TRUE) {
    $path = YAPEAL_CLASS . 'api' . DS;
    $this->apiList = FilterFileFinder::getStrippedFiles($path, 'account');
    $this->table = YAPEAL_TABLE_PREFIX . 'utilRegisteredUser';
    $okeys = YapealDBConnection::getOptionalColumns($this->table, YAPEAL_DSN);
    $rkeys = YapealDBConnection::getRequiredColumns($this->table, YAPEAL_DSN);
    // Make an array of required and optional fields
    $this->types = array_merge($rkeys, $okeys);
    // Was $id set?
    if (!empty($id)) {
      // If $id is a number and doesn't exist yet set userID with it.
      // If $id has any characters other than 0-9 it's not an userID.
      if (0 == strlen(str_replace(range(0,9),'',$id))) {
        if (FALSE === $this->getItemById($id)) {
          if (TRUE == $create) {
            $this->properties['userID'] = $id;
          } else {
            $mess = 'Unknown user ' . $id;
            throw new DomainException($mess, 1);
          };// else ...
        };
      } else {
        $mess = 'Parameter $id must be an integer';
        throw new InvalidArgumentException($mess, 3);
      };// else ...
    };// if !empty $id ...
  }// function __construct
  /**
   * Used to add an API to the list in activeAPI.
   *
   * @param string $name Name of the API to add without 'account' part i.e.
   * 'accountCharacters' would just be 'Characters'
   *
   * @return bool Returns TRUE if $name already exists else FALSE.
   *
   * @throws DomainException If $name not in $this->apiList.
   */
  public function addActiveAPI($name) {
    if (!in_array($name, $this->apiList)) {
      $mess = 'Unknown API: ' . $name;
      throw new DomainException($mess, 1);
    };// if !in_array...
    $apis = explode(' ', $this->properties['activeAPI']);
    if (in_array($name, $apis)) {
      $ret = TRUE;
    } else {
      $ret = FALSE;
      $apis[] = $name;
    };// if isset...
    $this->properties['activeAPI'] = implode(' ', $apis);
    return $ret;
  }// function addActiveAPI
  /**
   * Used to delete an API from the list in activeAPI.
   *
   * @param string $name Name of the API to delete without 'char' part i.e.
   * 'charAccountBalance' would just be 'AccountBalance'
   *
   * @return bool Returns TRUE if $name existed else FALSE.
   *
   * @throws DomainException If $name not in $this->apiList.
   */
  public function deleteActiveAPI($name) {
    if (!in_array($name, $this->apiList)) {
      $mess = 'Unknown API: ' . $name;
      throw new DomainException($mess, 1);
    };// if !in_array...
    $apis = explode(' ', $this->properties['activeAPI']);
    if (in_array($name, $apis)) {
      $ret = TRUE;
      unset($apis[$name]);
    } else {
      $ret = FALSE;
    };// if isset...
    $this->properties['activeAPI'] = implode(' ', $apis);
    return $ret;
  }// function deleteActiveAPI
  /**
   * Used to get user from RegisteredUser table by user ID.
   *
   * @param $id Id of user wanted.
   *
   * @return bool TRUE if user was retrieved.
   */
  public function getItemById($id) {
    $sql = 'select `' . implode('`,`', $this->types) . '`';
    $sql .= ' from `' . $this->table . '`';
    $sql .= ' where `userID`=' . $id;
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $result = $con->GetRow($sql);
      if (!empty($result)) {
        $this->properties = $result;
        $this->recordExists = TRUE;
      } else {
        $this->recordExists = FALSE;
      };
    }
    catch (ADODB_Exception $e) {
      $this->recordExists = FALSE;
    }
    return $this->recordExists;
  }// function getItemById
  /**
   * Used to get item from table by name.
   *
   * @param $name Name of record wanted.
   *
   * @return bool TRUE if item was retrieved else FALSE.
   *
   * @throws LogicException Throws LogicException because there is no 'name' type
   * field for this database table.
   */
  public function getItemByName($name) {
    throw new LogicException('Not implimented for RegisteredUser table', 1);
  }// function getItemByName
  /**
   * Function used to check if database record already existed.
   *
   * @return bool Returns TRUE if the the database record already existed.
   */
  public function recordExists() {
    return $this->recordExists;
  }// function recordExists
  /**
   * Used to store data into table.
   *
   * @return bool Return TRUE if store was successful.
   */
  public function store() {
    try {
      YapealDBConnection::upsert($this->properties,
        $this->table, YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
    return TRUE;
  }// function store
}
?>
