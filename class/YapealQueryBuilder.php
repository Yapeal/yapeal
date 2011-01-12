<?php
/**
 * Contains YapealQueryBuilder class.
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
 * Class used to build SQL queries.
 *
 * @package Yapeal
 * @subpackage YapealQueryBuilder
 */
class YapealQueryBuilder implements Countable {
  /**
   * @var mixed Holds count for auto store.
   */
  protected $autoStore;
  /**
   * @var string List of column ADOFieldObjects for table.
   */
  protected $colObjects = array();
  /**
   * @var array List of columns and their generic ADO types.
   */
  protected $colTypes = array();
  /**
   * @var string Holds an instance of the DB connection.
   */
  protected $con;
  /**
   * @var array Holds a list of default column values.
   */
  protected $defaults = array();
  /**
   * @var array Holds the built rows of data to be inserted.
   */
  protected $rows = array();
  /**
   * @var integer Holds current number of rows.
   */
  private $rowCount = 0;
  /**
   * @var string Holds the table name of the query is being built.
   */
  protected $tableName;
  /**
   * Constructor
   *
   * @param string $tableName Name of the table this query is for.
   * @param string $dsn ADOdb DSN for database connection.
   * @param mixed $autoStore Sets how many rows can be added before they are
   * automatically stored. Set to FALSE to turn off.
   *
   * @throws InvalidArgumentException Throws InvalidArgumentException if
   * $tableName or $dsn aren't strings.
   * @throws RuntimeException Throws RuntimeException if can't get ADOdb
   * connection or table column information.
   */
  public function __construct($tableName, $dsn,
    $autoStore = YapealQueryBuilder::MAX_UPSERT) {
    if (!is_string($tableName)) {
      $mess = '$tableName must be a string in ' . __CLASS__;
      throw new InvalidArgumentException($mess, 1);
    };// if !is_string $params[$k] ...
    // Keep table name for later.
    $this->tableName = $tableName;
    if (!is_string($dsn)) {
      $mess = '$dsn must be a string in ' . __CLASS__;
      throw new InvalidArgumentException($mess, 2);
    };// if !is_string $params[$k] ...
    //$this->dsn = $dsn;
    try {
      // Get a database connection.
      $this->con = YapealDBConnection::connect($dsn);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get database connection in ' . __CLASS__;
      throw new RuntimeException($mess, 3);
    }
    try {
      // Get a list of column objects.
      $this->colObjects = $this->con->MetaColumns($tableName, FALSE);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get ADOFieldObjects for columns in ' . __CLASS__;
      throw new RuntimeException($mess, 4);
    }
    // Extract some column information into more useful forms.
    foreach ($this->colObjects as $col) {
      // Add any columns with default values to default list.
      if (isset($col->has_default) && $col->has_default === TRUE) {
        $this->defaults[$col->name] = $col->default_value;
      };// if isset $col->has_default ...
      // Make list of column names and their ADOdb generic types.
      $this->colTypes[$col->name] = $this->metaType($col);
    };// foreach $this->columns ...
    $this->autoStore = $autoStore;
  }// function __construct
  /**
   * Destructor used to make sure to release ADOdb resource correctly more for
   * peace of mind than actual need.
   */
  public function __destruct() {
    $this->colObjects = NULL;
    $this->con = NULL;
    if ($this->rowCount > 0 ) {
      $mess = 'Query destroyed before all rows were saved';
      trigger_error($mess, E_USER_WARNING);
    };
  }// function __destruct
  /**
   * Magic function to show object when being printed.
   *
   * The output is formatted as CSV (Comma Separated Values) with a header line
   * and string quoted. Note that decimal values are treated like strings and
   * blobs are in hexdecminal form with 0x appended but not quoted.
   *
   * @return string Returns the rows ready to be printed.
   */
  public function __toString() {
    $value = '"' . implode('","', array_keys($this->colTypes)) . '"' . PHP_EOL;
    foreach ($this->rows as $row) {
      $value .= trim($row, '()') . PHP_EOL;
    };
    return $value;
  }// function __toString ...
  /**
   * Function used to add row of data to query.
   *
   * @param array $row Contain assoc array of columns and values to be added to
   * query.
   *
   * @return bool Returns TRUE if row was added, else FALSE.
   */
  public function addRow($row) {
    // Merging defaults with API row should make a complete database record.
    $data = array_merge($this->defaults, $row);
    $diff = array_diff(array_keys($this->colTypes), array_keys($data));
    if (count($diff)) {
      $mess = 'Row was missing required fields (' . implode(', ', $diff);
      $mess .= ') that are needed for ' . $this->tableName;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };
    // Check for extra unknown fields in the data. This should only happen when
    // API has changed and the version of Yapeal is out of date.
    $diff = array_diff(array_keys($data), array_keys($this->colTypes));
    if (count($diff)) {
      $mess = 'Row has extra unknown fields (' . implode(', ', $diff);
      $mess .= ') that will be ignored for ' . $this->tableName;
      trigger_error($mess, E_USER_WARNING);
    };
    // Make a new array where database fields and API data fields overlap.
    $fields = array_intersect(array_keys($this->colTypes), array_keys($data));
    $set = array();
    foreach ($fields as $field) {
      switch ($this->colTypes[$field]) {
        case 'C':
        case 'D':
        case 'N':
        case 'T':
        case 'X':
          // Quote all text, decimal or date type fields.
          $set[] = $this->con->qstr($data[$field]);
          break;
        case 'B':
          // If the BLOB is empty use NULL.
          if (strlen($data[$field]) == 0) {
            $set[] = 'NULL';
          // BLOBs need to be converted to hex strings if they aren't already.
          } else if ('0x' !== substr($data[$field], 0, 2)) {
            $set[] = '0x' . bin2hex($data[$field]);
          } else {
            $set[] = (string)$data[$field];
          };// else '0x' !== substr($row[$field], 0, 2) ...
          break;
        default:
        $set[] = (string)$data[$field];
      };// switch $types($field) ...
    };// foreach $fields ...
    // Put completed row in with the rest.
    $this->rows[] = '(' . implode(',', $set) . ')';
    // Add row to the row count.
    ++$this->rowCount;
    // Check if doing auto stores and if there are enough rows do so.
    if ($this->autoStore !== FALSE && $this->autoStore == $this->rowCount) {
      $this->store();
    };// if $this->autoStore !== FALSE ...
    return TRUE;
  }// function addRow
  /**
   * Implimentation of count() for countable interface.
   *
   * @return int Returns count for rows.
   */
  public function count() {
    return $this->rowCount;
  }// function count
  /**
   * Function to access the list of columns and their generic ADO types.
   *
   * @return array Returns an array of column names and their assocated generic
   * ADO types.
   */
  public function getColumnTypes() {
    return $this->colTypes;
  }// function getColumnTypes
  /**
   * Function that will return ADOdb generic data type for an ADOFieldObject.
   *
   * This is a custom version of the same function available in ADOdb.
   *
   * @param object $fieldobj An ADOFieldObject to figure out generic type of.
   *
   * @return string Returns a single character string of the ADOdb generic type.
   *
   * @throws InvalidArgumentException If $fieldobj isn't an object throws an
   * InvalidArgumentException.
   */
  protected function metaType($fieldobj) {
    if (is_object($fieldobj)) {
        $t = $fieldobj->type;
        $len = $fieldobj->max_length;
    } else {
      $mess = 'Parameter $fieldobj must be an ADOFieldObject';
      throw new InvalidArgumentException($mess, 1);
    };// else is_object $fieldobj
    switch (strtoupper($t)) {
      case 'STRING':
      case 'CHAR':
      case 'VARCHAR':
      case 'TINYBLOB':
      case 'TINYTEXT':
      case 'ENUM':
      case 'SET':
        if ($len <= 255) return 'C';
      case 'TEXT':
      case 'LONGTEXT':
      case 'MEDIUMTEXT':
         return 'X';
      // php_mysql extension always returns 'blob' even if 'text'
      // so we have to check whether binary...
      case 'IMAGE':
      case 'LONGBLOB':
      case 'BLOB':
      case 'MEDIUMBLOB':
        return !empty($fieldobj->binary) ? 'B' : 'X';
      case 'YEAR':
      case 'DATE':
        return 'D';
      case 'TIME':
      case 'DATETIME':
      case 'TIMESTAMP':
        return 'T';
      case 'INT':
      case 'INTEGER':
      case 'BIGINT':
      case 'TINYINT':
      case 'MEDIUMINT':
      case 'SMALLINT':
        return 'I';
      case 'FLOAT':
      case 'DOUBLE':
      case 'DECIMAL':
      case 'DEC':
      case 'FIXED':
        return 'N';
    };// switch strtoupper($t) ...
    $mess = 'Unknown ADOFieldObject type in ' . __CLASS__ . PHP_EOL;
    $mess .= ' type recieved was ' . $t;
    trigger_error($mess, E_USER_ERROR);
  }// function metaType
  /**
   * Used to set default for column.
   *
   * @param string $name Name of the column.
   * @param mixed $value Value to be used as default for column.
   *
   * @return bool Returns TRUE if column exists in table and default was set.
   *
   * @throws LogicException Throws LogicException if any rows have already been
   * added. All defaults must be set before starting to add data rows.
   */
  public function setDefault($name, $value) {
    if ($this->count() > 0) {
      $mess = 'Defaults must be set before any data rows are added';
      throw new LogicException($mess, 1);
    }
    if (!array_key_exists($name, $this->colTypes)) {
      $mess = 'Ignoring default for unknown column ' . $name;
      $mess .= ' which does not exist in table ' . $this->tableName;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    };// if !array_key_exists $name ...
    $this->defaults[$name] = $value;
    return TRUE;
  }// function setDefault
  /**
   * Used to set defaults for multiple columns.
   *
   * @param array $defaults List of column names and new default values.
   *
   * @return bool Returns TRUE if all column defaults could be set, else FALSE.
   */
  public function setDefaults(array $defaults) {
    if(empty($defaults)) {
      $mess = 'List must contain as least one column name and value';
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    }// if empty $defaults ...
    $ret = TRUE;
    foreach ($defaults as $k => $v) {
      if (FALSE === $this->setDefault($k, $v)) {
        $ret = FALSE;
      };// if !$this->setDefault($k, $v) ...
    };// foreach $defaults ...
    return $ret;
  }// function setDefaults
  /**
   * Finishes making upsert, empties out rows, then upserts data to database.
   *
   * @return bool Returns TRUE if upsert worked, else FALSE.
   */
  public function store() {
    if ($this->rowCount == 0) {
      $mess = 'No rows to be upsert for ' . $this->tableName;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };
    // Make insert part of upsert.
    $sql = 'insert into `' . $this->tableName;
    $sql .= '` (`' . implode('`,`', array_keys($this->colTypes)) . '`)';
    $sql .= ' values ' . implode(',', $this->rows);
    // Insert is now complete don't need rows anymore.
    $this->rows = array();
    // Keep local copy of row count for transaction check.
    $cnt = $this->rowCount;
    $this->rowCount = 0;
    // Add update part to upsert.
    $sql .= ' on duplicate key update ';
    // Loop thru and build update.
    $updates = array();
    foreach (array_keys($this->colTypes) as $k) {
      $updates[] = '`' . $k . '`=values(`' . $k . '`)';
    };
    $sql .= implode(',', $updates);
    // Use a transaction for larger upserts to make them faster but fall back to
    // normal upsert if transaction fails.
    if ($cnt > 10) {
      $this->con->StartTrans();
      $this->con->Execute($sql);
      if (FALSE === $this->con->CompleteTrans()) {
        $mess = 'Transaction failed for ' . $this->tableName;
        trigger_error($mess, E_USER_WARNING);
      } else {
        return TRUE;
      };// else FALSE === $this->con->CompleteTrans() ...
    };// if $this->count() > 10 ...
    try {
      $this->con->Execute($sql);
    }
    catch(ADODB_Exception $e) {
      $mess = 'Upsert failed for ' . $this->tableName;
      trigger_error($mess, E_USER_WARNING);
      return FALSE;
    }
    return TRUE;
  }// function store
  /**
   * Set max SQL insert size. This is a trade off of memory use and number of
   * inserts needed for larger APIs.
   */
  const MAX_UPSERT = 1000;
}
?>
