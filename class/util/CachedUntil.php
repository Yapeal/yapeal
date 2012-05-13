<?php
/**
 * Contains CachedUntil class.
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
 * Wrapper class for utilCachedUntil table.
 *
 * @property string $cachedUntil
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class CachedUntil extends ALimitedObject implements IGetBy {
  /**
   * @var ADODB_mysqli Holds an instance of the DB connection.
   */
  protected $con;
  /**
   * @var string Holds the table name of the query is being built.
   */
  protected $tableName;
  /**
   * Holds query builder object.
   * @var YapealQueryBuilder
   */
  protected $qb;
  /**
   * Set to TRUE if a database record exists.
   * @var bool
   */
  private $recordExists;
  /**
   * Constructor
   *
   * @param mixed $id Id of cached time wanted.
   * @param bool $create When $create is set to FALSE will throw DomainException
   * if $id doesn't exist in database.
   *
   * @throws DomainException If $create is FALSE and a database record for $id
   * doesn't exist a DomainException will be thrown.
   * @throws InvalidArgumentException If required parameters in $id aren't
   * correct data types throws InvalidArgumentException.
   * @throws LengthException If $id is missing any require parameter throws a
   * LengthException.
   * @throws RuntimeException Throws RuntimeException if fails to get database
   * connection.
   */
  public function __construct($id = NULL, $create = TRUE) {
    $this->tableName = YAPEAL_TABLE_PREFIX . 'util' . __CLASS__;
    try {
      // Get a database connection.
      $this->con = YapealDBConnection::connect(YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get database connection in ' . __CLASS__;
      throw new RuntimeException($mess);
    }
    // Get a new query builder object.
    $this->qb = new YapealQueryBuilder($this->tableName, YAPEAL_DSN);
    // Get a list of column names and their ADOdb generic types.
    $this->colTypes = $this->qb->getColumnTypes();
    // Was $id set?
    $required = array('api' => 'C', 'ownerID' => 'I');
    // Check if $id is valid.
    foreach ($required as $k => $v) {
      if (!isset($id[$k])) {
        $mess = 'Missing required parameter $id["' . $k . '"]';
        $mess .= ' to constructor in ' . __CLASS__;
        throw new LengthException($mess);
      };// if !isset $id[$k] ...
      switch ($v) {
        case 'C':
        case 'X':
          if (!is_string($id[$k])) {
            $mess = '$id["' . $k . '"] must be a string';
            throw new InvalidArgumentException($mess);
          };// if !is_string $params[$k] ...
          break;
        case 'I':
          if (0 != strlen(str_replace(range(0,9),'',$id[$k]))) {
            $mess = '$id["' . $k . '"] must be an integer';
            throw new InvalidArgumentException($mess);
          };// if 0 == strlen(...
          break;
      };// switch $v ...
    };// foreach $required ...
    // Check if record already exists in database table.
    if (FALSE === $this->getItemById($id)) {
      // If record doesn't exists should it be created?
      if (TRUE == $create) {
        // Add required columns from $id to object.
        foreach ($required as $k => $v) {
          $this->$k = $id[$k];
        };// foreach $required ...
      } else {
        $mess = 'No cached time for API = ' . $id['api'];
        $mess .= ' exists for ownerID = ' . $id['ownerID'];
        throw new DomainException($mess);
      };// else TRUE == $create ...
    };// if FALSE === $this->getItemById($id) ...
    // See if any columns beyond required were include and insure they get
    // set/updated as well.
    if (count($id) > count($required)) {
      foreach (array_diff_key($id, $required) as $k => $v) {
        $this->$k = $v;
      };// foreach array_diff_key($id, $required) ...
    };// if count($id) ...
  }// function __construct
  /**
   * Destructor used to make sure to release ADOdb resource correctly more for
   * peace of mind than actual need.
   */
  public function __destruct() {
    $this->con = NULL;
  }// function __destruct
  /**
   * Used to tell if it is time to get the current API for the current owner by
   * checking the datatime in utilCachedUntil table.
   *
   * @param string $api Which API is data is needed for.
   * @param string|int $owner Which owner the API data is needed for.
   *
   * @return bool Returns TRUE if it is time to get the API.
   */
  public static function cacheExpired($api, $owner = 0) {
    $now = time();
    $sql = 'select `cachedUntil`';
    $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilCachedUntil`';
    $sql .= ' where';
    $sql .= ' `ownerID`=' . $owner;
    try {
      // Get a database connection.
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql .= ' and `api`='. $con->qstr($api);
      $result = (string)$con->GetOne($sql);
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return TRUE;
    }
    if (empty($result)) {
      return TRUE;
    };
    $cuntil = strtotime($result . ' +0000');
    if ($now < $cuntil) {
      return FALSE;
    };
    return TRUE;
  }//function cacheExpired
  /**
   * Used to get cachedUntil time from cachedUntil table by ID.
   *
   * @param mixed $id Id of time wanted.
   *
   * @return bool Returns TRUE if time was retrieved.
   */
  public function getItemById($id) {
    $sql = 'select `' . implode('`,`', array_keys($this->colTypes)) . '`';
    $sql .= ' from `' . $this->tableName . '`';
    $sql .= ' where';
    $sql .= ' `ownerID`=' . $id['ownerID'];
    try {
      $sql .= ' and `api`=' . $this->con->qstr($id['api']);
      $result = $this->con->GetRow($sql);
      if (count($result) == 0) {
        $this->recordExists = FALSE;
      } else {
        $this->properties = $result;
        $this->recordExists = TRUE;
      };
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      $this->recordExists = FALSE;
    }
    return $this->recordExists;
  }// function getItemById
  /**
   * Used to get item from table by name.
   *
   * @param mixed $name Name of record wanted.
   *
   * @return bool TRUE if item was retrieved else FALSE.
   *
   * @throws LogicException Throws LogicException because there is no 'name' type
   * field for this database table.
   */
  public function getItemByName($name) {
    throw new LogicException('Not implemented for ' . __CLASS__ . ' table', 1);
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

