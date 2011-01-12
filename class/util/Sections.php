<?php
/**
 * Contains Sections class.
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
 * Wrapper class for utilSections table.
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class Sections extends ALimitedObject implements IGetBy {
  /**
   * @var string Holds an instance of the DB connection.
   */
  protected $con;
  /**
   * Table name
   * @var string
   */
  protected $tableName;
  /**
   * Holds query builder object.
   * @var object
   */
  protected $qb;
  /**
   * List of all section APIs
   * @var array
   */
  private $apiList;
  /**
   * List of all sections
   * @var array
   */
  private $sectionList;
  /**
   * Set to TRUE if a database record exists.
   * @var bool
   */
  private $recordExists;
  /**
   * Constructor
   *
   * @param mixed $id Id of Section wanted.
   * @param bool $create When $create is set to FALSE will throw DomainException
   * if $id doesn't exist in database.
   *
   * @throws InvalidArgumentException If $id isn't a number or string throws an
   * InvalidArgumentException.
   * @throws DomainException If $create is FALSE and a database record for $id
   * doesn't exist a DomainException will be thrown.
   */
  public function __construct($id = NULL, $create = TRUE) {
    $this->sectionList = FilterFileFinder::getStrippedFiles(YAPEAL_CLASS, 'Section');
    $this->tableName = YAPEAL_TABLE_PREFIX . 'util' . __CLASS__;
    try {
      // Get a database connection.
      $this->con = YapealDBConnection::connect(YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get database connection in ' . __CLASS__;
      throw new RuntimeException($mess, 1);
    }
    // Get a new query builder object.
    $this->qb = new YapealQueryBuilder($this->tableName, YAPEAL_DSN);
    // Get a list of column names and their ADOdb generic types.
    $this->colTypes = $this->qb->getColumnTypes();
    // Was $id set?
    if (!empty($id)) {
      // If $id has any characters other than 0-9 it's not a sectionID.
      if (0 == strlen(str_replace(range(0, 9), '', $id))) {
        if (FALSE === $this->getItemById($id)) {
          // If $id is a number and doesn't exist yet set sectionID with it.
          if (TRUE == $create) {
            $this->properties['sectionID'] = $id;
          } else {
            $mess = 'Unknown section ' . $id;
            throw new DomainException($mess, 2);
          };// else ...
        };
        // else if it's a string ...
      } else if (is_string($id)) {
        if (FALSE === $this->getItemByName($id)) {
          // If $id is a string and doesn't exist yet set section with it.
          if (TRUE == $create) {
            $this->properties['section'] = $id;
          } else {
            $mess = 'Unknown section ' . $id;
            throw new DomainException($mess, 3);
          };// else ...
        };
      } else {
        $mess = 'Parameter $id must be an integer or a string';
        throw new InvalidArgumentException($mess, 4);
      };// else ...
    };// if !empty $id ...
  }// function __construct
  /**
   * Destructor used to make sure to release ADOdb resource correctly more for
   * peace of mind than actual need.
   */
  public function __destruct() {
    $this->con = NULL;
  }// function __destruct
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
    $this->getApiList();
    if (!in_array($name, $this->apiList)) {
      $mess = 'Unknown API: ' . $name;
      throw new DomainException($mess, 5);
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
      throw new DomainException($mess, 6);
    };// if !in_array...
    $apis = explode(' ', $this->properties['activeAPI']);
    $ret = FALSE;
    foreach ($apis as $k => $v) {
      if ($name == $v) {
        $ret = TRUE;
        unset($apis[$k]);
        break;
      };// if $name == $v ...
    };// foreach $apis ...
    $this->properties['activeAPI'] = implode(' ', $apis);
    return $ret;
  }// function deleteActiveAPI
  /**
   * Used to get list of available API classes for a section.
   *
   * @return Returns TRUE if $this->apiList is not empty.
   *
   * @throws LogicException Throws a LogicException if method is call when
   * section is unknown.
   */
  protected function getApiList() {
    if (!empty($this->apiList)) {
      return TRUE;
    };
    if (!isset($this->properties['section'])) {
      $mess = 'Can not add APIs without know which section they belong to';
      throw new LogicException($mess, 7);
    };
    $path = YAPEAL_CLASS . 'api' . DS;
    $section = $this->properties['section'];
    $this->apiList = FilterFileFinder::getStrippedFiles($path, $section);
    if (empty($this->apiList)) {
      $mess = 'There are no available API classes for ' . $section;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };
    return TRUE;
  }// function getApiList
  /**
   * Used to get section from Sections table by section ID.
   *
   * @param $id Id of section wanted.
   *
   * @return bool TRUE if section was retrieved.
   */
  public function getItemById($id) {
    $sql = 'select `' . implode('`,`', array_keys($this->colTypes)) . '`';
    $sql .= ' from `' . $this->tableName . '`';
    $sql .= ' where `sectionID`=' . $id;
    try {
      $result = $this->con->GetRow($sql);
      $this->properties = $result;
      $this->recordExists = TRUE;
    }
    catch (ADODB_Exception $e) {
      $this->recordExists = FALSE;
    }
    // Get list of available APIs for section if possible.
    if (TRUE === $this->recordExists()) {
      $this->getApiList();
    };
    return $this->recordExists();
  }// function getItemById
  /**
   * Used to get item from table by name.
   *
   * @param $name Name of record wanted.
   *
   * @return bool TRUE if item was retrieved else FALSE.
   *
   * @throws DomainException If $name not in $this->sectionList.
   */
  public function getItemByName($name) {
    if (!in_array(ucfirst($name), $this->sectionList)) {
      $mess = 'Unknown section: ' . $name;
      throw new DomainException($mess, 8);
    };// if !in_array...
    $sql = 'select `' . implode('`,`', array_keys($this->colTypes)) . '`';
    $sql .= ' from `' . $this->tableName . '`';
    try {
      $sql .= ' where `section`=' . $this->con->qstr($name);
      $result = $this->con->GetRow($sql);
      $this->properties = $result;
      $this->recordExists = TRUE;
    }
    catch (ADODB_Exception $e) {
      $this->recordExists = FALSE;
    }
    // Get list of available APIs for section if possible.
    if (TRUE === $this->recordExists()) {
      $this->getApiList();
    };
    return $this->recordExists();
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
   * Used to set default for column.
   *
   * @param string $name Name of the column.
   * @param mixed $value Value to be used as default for column.
   *
   * @return bool Returns TRUE if column exists in table and default was set.
   */
  public function setDefault($name, $value) {
    return $this->qb->setDefault($name, $value);
  }// function setDefault
  /**
   * Used to set defaults for multiple columns.
   *
   * @param array $defaults List of column names and new default values.
   *
   * @return bool Returns TRUE if all column defaults could be set, else FALSE.
   */
  public function setDefaults(array $defaults) {
    return $this->qb->setDefaults($defaults);
  }// function setDefaults
  /**
   * Used to store data into table.
   *
   * @return bool Return TRUE if store was successful.
   */
  public function store() {
    if (FALSE === $this->qb->addRow($this->properties)) {
      return FALSE;
    };// if FALSE === ...
    return $this->qb->store();
  }// function store
}
?>
