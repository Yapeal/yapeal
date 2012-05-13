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
  } else {
    fwrite(STDERR, $mess);
    exit(1);
  }
};
/**
 * Class used to build SQL queries.
 *
 * @package Yapeal
 * @subpackage YapealQueryBuilder
 */
class YapealQueryBuilder implements Countable {
  /**
   * @var bool Use to determine if autoStore mode is active or not.
   */
  protected $autoStore = TRUE;
  /**
   * @var mixed Holds max row count for autoStore mode.
   */
  protected $autoStoreRows = self::MAX_UPSERT_ROWS;
  /**
   * @var mixed Holds max byte size of data rows for autoStore mode.
   */
  protected static $autoStoreSize = self::MAX_UPSERT_SIZE;
  /**
   * @var string List of column ADOFieldObjects for table.
   */
  protected $colObjects = array();
  /**
   * @var array List of columns and their generic ADO types.
   */
  protected $colTypes = array();
  /**
   * @var ADOConnection Holds an instance of the DB connection.
   */
  protected $con;
  /**
   * @var array Holds a list of default column values.
   */
  protected $defaults = array();
  /**
   * @var array Holds a list of null-able columns.
   */
  protected $nullables = array();
  /**
   * @var array Holds the built rows of data to be inserted.
   */
  protected $rows = array();
  /**
   * @var integer Holds current number of rows.
   */
  private $rowCount = 0;
  /**
   * @var integer Holds current byte size of rows.
   */
  private $rowSize = 0;
  /**
   * @var string Holds the table name of the query that is being built.
   */
  protected $tableName;
  /**
   * @var bool Used by store() to decided between plain insert or upsert.
   */
  protected $upsert = TRUE;
  /**
   * Constructor
   *
   * @param string $tableName Name of the table this query is for.
   * @param string $dsn ADOdb DSN for database connection.
   * @param bool $autoStoreMode Used to turn autostore on or off.
   *
   * @throws InvalidArgumentException Throws InvalidArgumentException if
   * $tableName or $dsn aren't strings.
   * @throws RuntimeException Throws RuntimeException if can't get ADOdb
   * connection or table column information.
   */
  public function __construct($tableName, $dsn, $autoStoreMode = TRUE) {
    if (!is_string($tableName)) {
      $mess = '$tableName must be a string in ' . __CLASS__;
      throw new InvalidArgumentException($mess);
    };// if !is_string $tableName ...
    // Keep table name for later.
    $this->tableName = $tableName;
    if (!is_string($dsn)) {
      $mess = '$dsn must be a string in ' . __CLASS__;
      throw new InvalidArgumentException($mess);
    };// if !is_string $params[$k] ...
    //$this->dsn = $dsn;
    try {
      // Get a database connection.
      $this->con = YapealDBConnection::connect($dsn);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get database connection in ' . __CLASS__;
      throw new RuntimeException($mess);
    }
    try {
      // Get a list of column objects.
      $this->colObjects = $this->con->MetaColumns($tableName, FALSE);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get ADOFieldObjects for columns in ' . __CLASS__;
      throw new RuntimeException($mess);
    }
    // Extract some column information into more useful forms.
    foreach ($this->colObjects as $col) {
      // Add any columns with default values to default list.
      if (isset($col->has_default) && $col->has_default === TRUE) {
        if ($col->default_value !== 'CURRENT_TIMESTAMP') {
          $this->defaults[$col->name] = $col->default_value;
        } else {
          $this->nullables[] = $col->name;
        };
      };// if isset $col->has_default ...
      // Add any null-able columns to null list.
      if (isset($col->not_null) && $col->not_null === FALSE) {
        $this->nullables[] = $col->name;
      };// if isset $col->has_default ...
      // Make list of column names and their ADOdb generic types.
      $this->colTypes[$col->name] = $this->metaType($col);
    };// foreach $this->columns ...
    $this->autoStore = (bool)$autoStoreMode;
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
      Logger::getLogger('yapeal')->warn($mess);
    };
  }// function __destruct
  /**
   * Magic function to show object when being printed.
   *
   * The output is formatted as CSV (Comma Separated Values) with a header line
   * and string quoted. Note that decimal values are treated like strings and
   * blobs are in hexadecimal form with 0x appended but not quoted.
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
    $diff = array_diff(array_keys($this->colTypes), array_keys($data), $this->nullables);
    if (count($diff)) {
      $mess = 'Row was missing required fields (' . implode(', ', $diff);
      $mess .= ') that are needed for ' . $this->tableName;
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    };
    // Check for extra unknown fields in the data. This should only happen when
    // API has changed and the version of Yapeal is out of date.
    $diff = array_diff(array_keys($data), array_keys($this->colTypes));
    if (count($diff)) {
      $mess = 'Row has extra unknown fields (' . implode(', ', $diff);
      $mess .= ') that will be ignored for ' . $this->tableName;
      Logger::getLogger('yapeal')->warn($mess);
    };
    // Make a new array where database fields and API data fields overlap.
    //$fields = array_intersect(array_keys($this->colTypes), array_keys($data), $this->nullables);
    $set = array();
    foreach (array_keys($this->colTypes) as $field) {
      // Set any missing null-able column to NULL.
      if (!isset($data[$field]) && in_array($field, $this->nullables)) {
        $set[] = 'NULL';
        continue;
      };
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
    $newRow = '(' . implode(',', $set) . ')';
    // Put completed row in with the rest.
    $this->rows[] = $newRow;
    // Add row to the row count and size.
    ++$this->rowCount;
    $this->rowSize += strlen($newRow);
    // Check if doing auto stores and ready to do so.
    if ($this->autoStore === TRUE && ($this->autoStoreRows == $this->rowCount
      || self::$autoStoreSize <= $this->rowSize)) {
      $this->store();
    };// if $this->autoStore === TRUE ...
    return TRUE;
  }// function addRow
  /**
   * Implementation of count() for countable interface.
   *
   * @return int Returns count for rows.
   */
  public function count() {
    return $this->rowCount;
  }// function count
  /**
   * Function to access the list of columns and their generic ADO types.
   *
   * @return array Returns an array of column names and their associated generic
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
        if ($len <= 255) {
          return 'C';
        } else {
          return 'X';
        }
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
    $mess .= ' type received was ' . $t;
    Logger::getLogger('yapeal')->error($mess);
    exit(2);
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
      Logger::getLogger('yapeal')->warn($mess);
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
      Logger::getLogger('yapeal')->warn($mess);
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
   * Turns autoStore mode on if parameter is TRUE else mode is off for FALSE.
   *
   * @param bool $mode Setting for autoStore mode.
   */
  public function setAutoStoreMode($mode) {
    $this->autoStore = (bool)$mode;
  }// function setAutoStoreMode
  /**
   * Set the max number of rows to use in a single insert/upsert when using
   * autostore.
   *
   * @param int $autoStoreRows Sets how many rows can be added before they are
   * automatically stored.
   */
  public function setAutoStoreRows($autoStoreRows) {
    $this->autoStoreRows = (int)$autoStoreRows;
  }// function setAutoStoreRows
  /**
   * Set the max (soft) size in bytes of the data rows for a single
   * insert/upsert when using autostore.
   *
   * This method sets a soft limit on how many bytes the data rows will be. When
   * adding a row to the query if the total byte size goes over this limit it
   * will force an autostore to happen. This is ignored if autoStore mode is off.
   * The size should be a few percent below the MySQL server's max_packet_size.
   * This is made available mostly for people to use when they don't have an
   * option to increase the setting in my.cnf.
   *
   * @param int $autoStoreSize Sets max (soft) byte size.
   */
  public static function setAutoStoreSize($autoStoreSize) {
    self::$autoStoreSize = (int)$autoStoreSize;
  }// function setAutoStoreSize
  /**
   * Finishes making insert/upsert, empties out rows, then inserts/upserts data
   * to database.
   *
   * @param bool $upsert When TRUE use upsert else just use insert.
   *
   * @return bool Returns TRUE if upsert worked, else FALSE.
   */
  public function store($upsert = NULL) {
    if ($this->rowCount == 0) {
      if (Logger::getLogger('yapeal')->isInfoEnabled()) {
        $mess = 'No rows for ' . $this->tableName;
        Logger::getLogger('yapeal')->info($mess);
      };
      return FALSE;
    };
    if (!is_bool($upsert)) {
      $upsert = $this->upsert;
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
    $this->rowSize = 0;
    // Check if upsert is needed.
    if ($upsert === TRUE) {
      // Add update part to make upsert.
      $sql .= ' on duplicate key update ';
      // Loop through and build update.
      $updates = array();
      foreach (array_keys($this->colTypes) as $k) {
        $updates[] = '`' . $k . '`=values(`' . $k . '`)';
      };
      $sql .= implode(',', $updates);
    };
    // Use a transaction for larger inserts/upserts to make them faster but fall
    // back to normal insert/upsert if transaction fails.
    if ($cnt > 3) {
      $this->con->StartTrans();
      $this->con->Execute($sql);
      if (FALSE === $this->con->CompleteTrans()) {
        $mess = 'Transaction failed for ' . $this->tableName;
        Logger::getLogger('yapeal')->warn($mess);
      } else {
        return TRUE;
      };// else FALSE === $this->con->CompleteTrans() ...
    };// if $this->count() > 10 ...
    try {
      $this->con->Execute($sql);
    }
    catch(ADODB_Exception $e) {
      $mess = 'Insert/upsert failed for ' . $this->tableName;
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    }
    return TRUE;
  }// function store
  /**
   * Sets if store() should use plain insert or upsert (insert with duplicate
   * key update).
   *
   * @param bool $is When TRUE store() will use upserts.
   *
   * @return bool Returns value of $this->upsert.
   */
  public function useUpsert($is = NULL) {
    if (is_bool($is)) {
      $this->upsert = $is;
    };
    return $this->upsert;
  }// function useUpsert
  /**
   * Set max SQL insert/upsert size.
   *
   * This is a trade off of memory use and number of inserts needed for larger
   * APIs. Only a few APIs normal end up using this.
   * Examples are char/AssetList, corp/AssetList, eve/AllianceList, map/Jumps,
   * and map/Kills. The reason is they are the only APIs without a set maximum
   * number of rows that also tend to be very large.
   */
  const MAX_UPSERT_ROWS = 1000;
  /**
   * Set max SQL insert/upsert size in bytes.
   *
   * This is a trade off of packet size used with MySQL and the number of
   * inserts needed for larger APIs. Only a few APIs normal end up exceeding
   * this size.
   * Examples are char/AssetList, corp/AssetList, eve/AllianceList, map/Jumps,
   * and map/Kills. The reason is they are the only APIs without a set maximum
   * number of rows that also tend to be very large.
   * The value here should be about right for MySQL server's default
   * max_packet_size setting.
   */
  const MAX_UPSERT_SIZE = 990000;
}

