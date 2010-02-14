<?php
/**
 * Contains YapealDBConnection class.
 *
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal.
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
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * A factory to manage ADOdb connections to databases.
 *
 * @package Yapeal
 * @subpackage ADOdb
 */
class YapealDBConnection {
  /**
   * @var object
   */
  private static $instance;
  /**
   * @var resource
   */
  private $connections;
  /**
   * Only way to make instance is through {@link getInstance() getInstance()}.
   */
  private function __construct() {
    $this->connections = array();
  }
  /**
   * No backdoor through cloning either.
   */
  private function __clone() {}
  /**
   * Used to get an instance of the class.
   *
   * @return YapealDBConnection Returns an instance of the class.
   */
  protected static function getInstance() {
    if (!(self::$instance instanceof self)) {
      self::$instance = new self();
    };
    return self::$instance;
  }
  /**
   * Use to get a ADOdb connection object.
   *
   * This method will create a new ADOdb connection for each DSN it is passed and
   * return the same connection for any other requests for the same DSN. It was
   * developed to get around some problems with how ADOdb handles connections
   * that wasn't compatable with what I needed.
   *
   * @param string $dsn An ADOdb compatible connection string.
   * @param string $section Which API section connection is for.
   *
   * @return object Returns an ADOdb connection.
   *
   * @throws InvalidArgumentException if $dsn is empty or if $dsn isn't a string.
   * @throws ADODB_Exception Passes through any problem with actual connection.
   */
  private function factory($dsn) {
    if (empty($dsn) || !is_string($dsn)) {
      throw new InvalidArgumentException('Bad value passed for $dsn');
    };
    $hash = sha1($dsn);
    if (!array_key_exists($hash, $this->connections)) {
      require_once YAPEAL_CLASS . 'ADODB_Exception.php';
      require_once YAPEAL_ADODB . 'adodb.inc.php';
      $con = NewADOConnection($dsn);
      $con->Execute('set names utf8');
      $con->Execute('set time_zone="+0:00"');
      $this->connections[$hash] = $con;
    };
    return $this->connections[$hash];
  }
  /**
   * Function used to connect to a DB.
   * @param string $dsn An ADOdb compatible connection string.
   *
   * @return object Returns ADOdb connection object.
   *
   * @throws ADODB_Exception for any errors.
   */
  public static function connect($dsn) {
    $instance = self::getInstance();
    $con = $instance->factory($dsn);
    $con->debug = FALSE;
    $con->SetFetchMode(ADODB_FETCH_ASSOC);
    return $con;
  }// function connect
  /**
   * Function to return an array of column names and generic ADOdb types.
   *
   * @param string $table Table to use in query's from clause.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return array Returns array of column names and their generic ADOdb types.
   *
   * @throws ADODB_Exception if connection fails.
   */
  private static function getColumnTypes($table, $dsn) {
    $con = self::connect($dsn);
    $columns = $con->MetaColumns($table, FALSE);
    $types = array();
    foreach ($columns as $col) {
      $types[$col->name] = self::metaType($col);
    };// foreach $columns ...
    return $types;
  }// function getColumnTypes
  /**
   * Function to return an array of optional column names.
   *
   * @param string $table Table to use in query's from clause.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return array Returns an array of optional column names.
   *
   * @throws ADODB_Exception if connection fails.
   */
  private static function getOptionalColumns($table, $dsn) {
    $con = self::connect($dsn);
    $columns = $con->MetaColumns($table, FALSE);
    $types = array();
    foreach ($columns as $col) {
      if (TRUE == $col->has_default) {
        $types[] = $col->name;
      };// if TRUE == $col->has_default ...
    };// foreach $columns ...
    return $types;
  }// function getOptionalColumns
  /**
   * Function to return an array of required column names.
   *
   * @param string $table Table to use in query's from clause.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return array Returns an array of required column names.
   *
   * @throws ADODB_Exception if connection fails.
   */
  private static function getRequiredColumns($table, $dsn) {
    $con = self::connect($dsn);
    $columns = $con->MetaColumns($table, FALSE);
    $types = array();
    foreach ($columns as $col) {
      if (FALSE == $col->has_default) {
        $types[] = $col->name;
      };// if FALSE == $col->has_default ...
    };// foreach $columns ...
    return $types;
  }// function getRequiredColumns
  /**
   * Function to build a multi-values insert ... on duplicate key update query
   *
   * @param array $data Values to be put into query
   * @param string $table Table to use in query's from clause
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return string Returns a complete SQL statement ready to be used by a
   * ADOdb::Execute()
   *
   * @throws ADODB_Exception if connection used to do quoting fails.
   */
  private static function makeMultiUpsert(array $data, $table, $dsn) {
    $dkeys = array_keys($data[0]);
    $okeys = YapealDBConnection::getOptionalColumns($table, $dsn);
    $rkeys = YapealDBConnection::getRequiredColumns($table, $dsn);
    // Make an array of required and optional fields
    $akeys = array_merge($rkeys, $okeys);
    // Check for missing required fields
    $missing = array_diff($rkeys, $dkeys);
    if (count($missing)) {
      $mess = 'Missing required fields (' . implode(', ', $missing);
      $mess .= ') found while making upsert for ' . $table;
      throw new UnexpectedValueException($mess, 1);
    };
    // Check for extra unknown fields in the data. This should only happen when
    // API has changed and the version of Yapeal is out of date.
    $extras = array_diff($dkeys, $akeys);
    if (count($extras)) {
      $mess = 'Extra unknown fields (' . implode(', ', $extras);
      $mess .= ') found while making upsert for ' . $table;
      trigger_error($mess, E_USER_WARNING);
    };
    // Make a new array where database fields and API data fields overlap.
    $fields = array_intersect($akeys, $dkeys);
    // Get an array of database fields and their generic ADOdb types.
    $types = YapealDBConnection::getColumnTypes($table, $dsn);
    // Need this so we can do quoting.
    $con = self::connect($dsn);
    $needsQuote = array('C', 'X', 'D', 'T');
    // Build query sections
    $insert = 'insert into `' . $table . '` (`';
    $insert .= implode('`,`', $fields) . '`)';
    $values = ' values';
    $sets = array();
    // Shift first row off the start of $data and quote value as needed.
    while (NULL != $row = array_shift($data)) {
      $set = array();
      foreach ($fields as $field) {
        if (in_array($types[$field], $needsQuote)) {
          $set[] = $con->qstr($row[$field]);
        } else {
          $set[] = (string)$row[$field];
        };// else in_array $params...
      };// foreach $fields ...
      $sets[] = '(' . implode(',', $set) . ')';
    };// while NULL != $row...
    $values .= ' ' . implode(',', $sets);
    $dupup = ' on duplicate key update ';
    // Loop thru and build update section.
    $updates = array();
    foreach ($fields as $k) {
      $updates[] = '`' . $k . '`=values(`' . $k . '`)';
    };
    $dupup .= implode(',', $updates);
    return $insert . $values . $dupup;
  }// function makeMultiUpsert
  /**
   * Function that will return ADOdb generic data type for an ADOFieldObject
   *
   * @param object $fieldobj An ADOFieldObject to figure out generic type of.
   *
   * @return string Returns a single character string of the ADOdb generic type.
   *
   * @throws InvalidArgumentException If $fieldobj isn't an object throws an
   * InvalidArgumentException.
   */
  private static function metaType($fieldobj) {
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
        //if (!empty($fieldobj->primary_key)) return 'R';
        return 'I';
      // Added floating-point types
      // Maybe not necessery.
      case 'FLOAT':
      case 'DOUBLE':
      case 'DECIMAL':
      case 'DEC':
      case 'FIXED':
      default:
        return 'N';
    };// switch
  }// function metaType
  /**
   * Function to build, prepare, execute an insert ... on duplicate key update
   * for an array of records
   *
   * The function tries to use a SQL transaction for larger upserts and falls
   * back to normal upsert if transactions not supported by DB or the transaction
   * throws an exception when we try to use it.
   * Example of how to use:
   * <code>
   * $data=array(
   * array('tableName'=>'eve-api-pull','ownerID'=>0,
   *   'cachedUntil'=>'2008-01-01 00:00:01'), ...
   * );
   * YapealDBConnection::multipleUpsert($data, 'CacheUntil', YAPEAL_DSN);
   * </code>
   *
   * @param array $data An array of assoc arrays of column names and values to be
   * Upserted.
   * @param string $table Name of table to Upsert into.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return mixed Number rows effected,
   * FALSE if either $data or $types is empty
   *
   * @throws ADODB_Exception for any errors.
   *
   * @uses YapealDBConnection::makeMultiUpsert()
   */
  public static function multipleUpsert(array $data, $table, $dsn) {
    if (empty($data)) {
      return FALSE;
    };
    $cnt = count($data);
    $con = self::connect($dsn);
    $mess = 'Upserting ' . $cnt . ' records for ' . $table;
    trigger_error($mess, E_USER_NOTICE);
    $upsert = self::makeMultiUpsert($data, $table, $dsn);
    // Use a transaction for larger upserts to make them faster when we can.
    if ($cnt > 10) {
      try {
        $con->BeginTrans();
        $ok = $con->Execute($upsert);
        if (!$ok) {
          $con->RollbackTrans();
        } else {
          $con->CommitTrans();
        };
        return $ok;
      }
      catch(ADODB_Exception $e) {
        $mess = 'Transaction failed for ' . $table . ' in ' . basename(__FILE__);
        trigger_error($mess, E_USER_WARNING);
        // Rollback transaction here then we'll re-try without transaction and
        // throw another exception if there's still a problem with upsert for
        // the caller to catch.
        $con->RollbackTrans();
      }
    };// if count $data > 10&&...
    return $con->Execute($upsert);
  }// function multipleUpsert
  /**
   * Builds table upsert from the attributes of a SimpleXMLElement array
   *
   * @param mixed $datum SimpleXMLElement object who's element attributes will
   * be inserted into table or an array of SimpleXMLElement objects with
   * attributes
   * @param string $table Name of the table to use in query's from clause
   * @param string $dsn The connection infomation needed to connect to DB
   * @param array $extras Used when you need to add any extra table fields that
   * aren't included in attributes
   *
   * @return mixed Number of records inserted/update or FALSE
   *
   * @uses YapealDBConnection::multipleUpsert()
   */
  public static function multipleUpsertAttributes($datum, $table, $dsn,
    $extras = array()) {
    if (count($datum) > 0) {
      $data = array();
      $row = array();
      foreach ($datum as $record) {
        $row = $extras;
        foreach ($record->attributes() as $k => $v) {
          $row[$k] = (string)$v;
        };
        $data[] = $row;
      };// foreach $datum
      return self::multipleUpsert($data, $table, $dsn);
    };// if $datum>0
    return FALSE;
  }// function multipleUpsertAttributes
  /**
   * Function to build, prepare, execute an insert ... on duplicate key update
   *
   * Example of how to use:
   * <code>
   * $data=array('tableName'=>'eve-api-pull','ownerID'=>0,
   *   'cachedUntil'=>'2008-01-01 00:00:01');
   * YapealDBConnection::upsert($data, 'CacheUntil', YAPEAL_DSN);
   * </code>
   *
   * @param array $data Record to be upsert into database table.
   * @param string $table Name of table to Upsert into.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return mixed Number rows effected (1), FALSE if either $data or $types is
   * empty
   *
   * @throws ADODB_Exception for any errors.
   *
   * @uses YapealDBConnection::makeMultiUpsert()
   */
  public static function upsert(array $data, $table, $dsn) {
    if (empty($data)) {
      return FALSE;
    };
    $upsert = self::makeMultiUpsert(array($data), $table, $dsn);
    $con = self::connect($dsn);
    return $con->Execute($upsert);
  }// function upsert
  /**
   * Used to find out if a Database already exists.
   *
   * @param string $name Name of the Database to check for.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return bool True if $name is found else FALSE.
   *
   * @throws ADODB_Exception for any errors.
   */
  public static function hasDatabase($name, $dsn) {
    if (empty($name) || empty($dsn)) {
      return FALSE;
    };
    $con = self::connect($dsn);
    return in_array($name, $con->MetaDatabases());
  }// function hasDatabase
  /**
   * Used to find out if a table already exists in a database.
   *
   * @param string $name Name of the table to check for.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return bool True if $name is found else FALSE.
   *
   * @throws ADODB_Exception for any errors.
   */
  public static function hasTable($name, $dsn) {
    if (empty($name) || empty($dsn)) {
      return FALSE;
    };
    $con = self::connect($dsn);
    return in_array($name, $con->MetaTables('TABLES'));
  }// function hasDatabase
  /**
   * Used to decide if we want to wait or not getting EVE API data. Has
   * randomizing wait option to help even out server and network loading.
   *
   * @param string $api Needs to be set to base part of name for example:
   * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
   * @param integer $owner Identifies owner of the api we're trying to update.
   * @param boolean $randomize When true (the default) can randomly decide
   * to delay get API data.
   *
   * @return Boolean Returns true when we need to get API data.
   */
  public static function dontWait($api, $owner = 0, $randomize = TRUE) {
    $now = time() - 5; // 5 seconds for EVE API time offset added :P
    $ctime = strtotime(self::getCachedUntil($api, $owner) . ' +0000');
    // hard limited to maximum delay of 6 minutes for randomized pulls.
    // 5 minutes (300) plus a minute from being almost ready last time.
    $mess = '';
    if (($now - $ctime) > 300) {
      $mess = 'Tired of waiting! Getting ' . $api . ' for ' . $owner;
      trigger_error($mess, E_USER_NOTICE);
      return TRUE;
    };
    // Got to wait until our time.
    if ($now < $ctime) {
      return FALSE;
    };
    if ($randomize) {
      // The later in the day and having been delay already decreases chance of
      // being delayed again.
      // 1 in $mod chance each time with 1 in 2 up to 1 in 29 max
      // 1 + 0-23 (hours) + Time difference in minutes
      $mod = 1 + gmdate('G') + floor(($now - $ctime) / 60);
      $rand = mt_rand(0, $mod);
      $mess = 'Rolled ' . $rand . ' out of ' . $mod . '. ';
      // Get to wait a while longer
      if ($rand == $mod) {
        return FALSE;
      };// if $rand==$mod ...
    };// if $randomize ...
      $mess .= 'Get ' . $api . ' for ' . $owner;
      trigger_error($mess, E_USER_NOTICE);
    return TRUE;
  }// function dontWait
  /**
   * Get cache until time for a table from cacheduntil table
   * @param string $tname Name of table to get time for.
   * @param integer $owner ID of owner. Use 0 for non-corp tables like
   * RefTypes.
   *
   * @return string A date/time using format 'YYYY-MM-DD HH:MM:SS'
   */
  public static function getCachedUntil($tname, $owner) {
    try {
      $con = self::connect(YAPEAL_DSN);
      $sql = 'select cachedUntil';
      $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilCachedUntil`';
      $sql .= ' where tableName=? and ownerID=?';
      $until = $con->GetOne($sql, array($tname, $owner));
      if (!strtotime($until)) {
        $until = '1970-01-01 00:00:01'; // One second after epox
      };
    }
    catch(ADODB_Exception $e) {
      $until = '1970-01-01 00:00:01'; // One second after epox
    };
    return $until;
  }// function getCachedUntil
}
?>
