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
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
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
    global $tracing;
    if (empty($dsn) || !is_string($dsn)) {
      throw new InvalidArgumentException('Bad value passed for $dsn');
    };
    $hash = sha1($dsn);
    if (!array_key_exists($hash, $this->connections)) {
      require_once YAPEAL_ADODB . 'adodb.inc.php';
      $mess = 'Before NewADOConnection in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
      $con = NewADOConnection($dsn);
      $con->Execute('set names utf8');
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
    global $tracing;
    $mess = 'Before getInstance in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    $instance = self::getInstance();
    $mess = 'Before factory in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    $con = $instance->factory($dsn);
    if ($tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2)) {
      $con->debug = TRUE;
    } else {
      $con->debug = FALSE;
    };
    $con->SetFetchMode(ADODB_FETCH_ASSOC);
    return $con;
  }// function connect
  /**
   * Function to build a multi-values insert ... on duplicate key update query
   *
   * @param array $data Values to be put into query
   * @param array $params Keys are parameter names and values their types
   * @param string $table Table to use in query's from clause
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return string Returns a complete SQL statement ready to be used by a
   * ADOdb::Execute()
   *
   * @throws ADODB_Exception if connection used to do quoting fails.
   */
  private static function makeMultiUpsert(array $data, array $params, $table, $dsn) {
    global $tracing;
    $pkeys = array_keys($params);
    $dkeys = array_keys($data[0]);
    // Check for missing fields
    $missing = array_diff($pkeys, $dkeys);
    if (count($missing)) {
      $mess = 'Missing required fields (' . implode(', ', $missing);
      $mess .= ') found while making upsert for ' . $table;
      throw new UnexpectedValueException($mess,1);
    };
    // Check for extra unknown fields
    $extras = array_diff($dkeys, $pkeys);
    if (count($extras)) {
      $mess = 'Extra unknown fields (' . implode(', ', $extras);
      $mess .= ') found while making upsert for ' . $table;
      trigger_error($mess, E_USER_WARNING);
    };
    $fields = array_intersect($pkeys, $dkeys);
    // Need this so we can do quoting.
    $con = self::connect($dsn);
    $needsQuote = array('C', 'X', 'D', 'T');
    // Build query sections
    $insert = 'insert into `' . $table . '` (`';
    $insert .= implode('`,`', $pkeys) . '`)';
    $values = ' values';
    $sets = array();
    foreach ($data as $row) {
      $set = array();
      foreach ($fields as $field) {
        if (in_array($params[$field], $needsQuote)) {
          $set[] = $con->qstr($row[$field]);
        } else {
          $set[] = (string)$row[$field];
        };// else in_array $params...
      };// foreach $fields ...
      $sets[] = '(' . implode(',', $set) . ')';
      //unset($row);
    };// foreach $data ...
    $values .= ' ' . implode(',', $sets);
    $dupup = ' on duplicate key update ';
    // Loop thru and build update section.
    $updates = array();
    foreach ($pkeys as $k) {
      $updates[] = '`' . $k . '`=values(`' . $k . '`)';
    };
    $dupup .= implode(',', $updates);
    $mess = 'makeMutliUpsert query: ' . PHP_EOL;
    $mess .= $insert . $values . $dupup;
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 3) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    return $insert . $values . $dupup;
  }// function makeMultiUpsert
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
   * $types=array('tableName'=>'text','ownerID'=>'integer',
   *   'cachedUntil'=>'timestamp');
   * YapealDBConnection::multipleUpsert($data,$types,'CacheUntil',YAPEAL_DSN);
   * </code>
   *
   * @param array $data An array of assoc arrays of column names and values to be
   * Upserted.
   * @param array $types Assoc array of column names and ADOdb types.
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
  public static function multipleUpsert(array $data, array $types, $table, $dsn) {
    global $tracing;
    if (empty($data) || empty($types)) {
      return FALSE;
    };
    $cnt = count($data);
    $con = self::connect($dsn);
    $mess = 'Before makeMultiUpsert for ' . $table . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    $mess = 'Upserting ' . $cnt . ' records for ' . $table;
    trigger_error($mess, E_USER_NOTICE);
    $upsert = self::makeMultiUpsert($data, $types, $table, $dsn);
    // Use a transaction for larger upserts to make them faster when we can.
    if ($cnt > 10) {
      try {
        $mess = 'Before transaction for ' . $table . ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
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
    $mess = 'Before non-transaction Execute for ' . $table . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    return $con->Execute($upsert);
  }// function multipleUpsert
  /**
   * Builds table upsert from the attributes of a SimpleXMLElement array
   *
   * @param mixed $datum SimpleXMLElement object who's element attributes will
   * be inserted into table or an array of SimpleXMLElement objects with
   * attributes
   * @param array $types Keys are DB field names and values are thier ADOdb type
   * i.e. 'ID'=>'integer','name'=>'text' ...
   * @param string $table Name of the table to use in query's from clause
   * @param string $dsn The connection infomation needed to connect to DB
   * @param array $extras Used when you need to add any extra table fields that
   * aren't included in attributes
   * @return mixed Number of records inserted/update or FALSE
   *
   * @uses YapealDBConnection::multipleUpsert()
   */
  public static function multipleUpsertAttributes($datum, array $types, $table, $dsn,
    $extras = array()) {
    global $tracing;
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
      $mess = 'Before multipleUpsert for ' . $table . ' in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
      return self::multipleUpsert($data, $types, $table, $dsn);
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
   * $types=array('tableName'=>'text','ownerID'=>'integer',
   *   'cachedUntil'=>'timestamp');
   * YapealDBConnection::upsert($data,$types,'CacheUntil',YAPEAL_DSN);
   * </code>
   *
   * @param array $data Assoc array of column names and value to be Upserted.
   * @param array $types Assoc array of column names and ADOdb types.
   * @param string $table Name of table to Upsert into.
   * @param string $dsn A valid ADOdb DSN.
   *
   * @return mixed Number rows effected (1),
   * FALSE if either $data or $types is empty
   *
   * @throws ADODB_Exception for any errors.
   *
   * @uses YapealDBConnection::makeMultiUpsert()
   */
  public static function upsert(array $data, array $types, $table, $dsn) {
    global $tracing;
    if (empty($data) || empty($types)) {
      return FALSE;
    };
    $mess = 'Before makeMultiUpsert for ' . $table . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    $upsert = self::makeMultiUpsert(array($data) , $types, $table, $dsn);
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
    global $tracing;
    if (empty($name) || empty($dsn)) {
      return FALSE;
    };
    $mess = 'Before MetaDatabases in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
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
    global $tracing;
    if (empty($name) || empty($dsn)) {
      return FALSE;
    };
    $mess = 'Before MetaDatabases in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
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
    global $tracing;
    $mess = 'Before getCachedUntil for ' . $api . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
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
