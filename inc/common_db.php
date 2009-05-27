<?php
/**
 * Group of common database functions.
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
// Assumes this file is in same directory as common_backend.php
require_once YAPEAL_CLASS . 'ADOdbFactory.php';
/**
 * Function used to connect to a DB.
 * @param string $dsn An ADOdb compatible connection string.
 * @param string $section Which API section connection is for.
 *
 * @return object Returns ADOdb connection object.
 *
 * @throws ADODB_Exception for any errors.
 */
function connect($dsn, $section = '') {
  global $tracing;
  $mess = 'Before getInstance in ' . __FILE__;
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $instance = ADOdbFactory::getInstance();
  $mess = 'Before factory for section ' . $section .' in ' . __FILE__;
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 0) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $con = $instance->factory($dsn, $section);
  if ($tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2)) {
    $con->debug = TRUE;
  } else {
    $con->debug = FALSE;
  };
  $con->SetFetchMode(ADODB_FETCH_ASSOC);
  return $con;
}// function connect
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
function dontWait($api, $owner = 0, $randomize = TRUE) {
  global $tracing;
  $mess = 'Before getCachedUntil for ' . $api . ' in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $ctime = strtotime(getCachedUntil($api, $owner) . ' +0000');
  $now = time() - 10; // 10 seconds for EVE API time offset added :P
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
function getCachedUntil($tname, $owner) {
  try {
    $con = connect(YAPEAL_DSN, YAPEAL_TABLE_PREFIX . 'utilCachedUntil');
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
/**
 * Gets a list of characters that are active from RegisteredCharacter.
 *
 * @return array Returns the list of active characters.
 *
 * @throws ADODB_Exception for any errors.
 */
function getRegisteredCharacters() {
  global $tracing;
  $con = connect(YAPEAL_DSN, 'Yapeal');
  /* Generate a list of character(s) we need to do updates for */
  $sql = 'select u.userID "userID",u.fullApiKey "apiKey",';
  $sql .= 'chr.characterID "charID", chr.activeAPI';
  $sql .= ' from ';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCharacter` as chr,';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredUser` as u';
  $sql .= ' where chr.isActive=1';
  $sql .= ' and chr.userID=u.userID';
  $mess = 'Before GetAll active characters in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $list = $con->GetAll($sql);
  return $list;
}// function getRegisteredCharacters
/**
 * Gets a list of corporations that are active from RegisteredCorporation.
 *
 * @return array Returns the list of active corporations.
 *
 * @throws ADODB_Exception for any errors.
 */
function getRegisteredCorporations() {
  global $tracing;
  $con = connect(YAPEAL_DSN, 'Yapeal');
  // Generate a list of corporation(s) we need to do updates for
  $sql = 'select cp.corporationID "corpID",u.userID "userID",';
  $sql .= 'u.fullApiKey "apiKey",cp.characterID "charID", cp.activeAPI';
  $sql .= ' from ';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCorporation` as cp,';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCharacter` as chr,';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredUser` as u';
  $sql .= ' where';
  $sql .= ' cp.isActive=1';
  $sql .= ' and cp.characterID=chr.characterID';
  $sql .= ' and chr.userID=u.userID';
  $mess = 'Before GetAll active corporations in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $list = $con->GetAll($sql);
  return $list;
}// function getRegisteredCorporations
/**
 * Gets a list of users from RegisteredUser.
 *
 * @return array Returns the list of users.
 *
 * @throws ADODB_Exception for any errors.
 */
function getRegisteredUsers() {
  global $tracing;
  $con = connect(YAPEAL_DSN, 'Yapeal');
  /* Generate a list of user(s) we need to do updates for */
  $sql = 'select userID ,fullApiKey "apiKey"';
  $sql .= ' from ';
  $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredUser`';
  $sql .= ' where 1=1';
  $mess = 'Before GetAll users in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $list = $con->GetAll($sql);
  return $list;
}// function getRegisteredUsers
/**
 * Function to build a multi-values insert ... on duplicate key update query
 *
 * @param array $data Values to be put into query
 * @param array $params Keys are parameter names and values their types
 * @param string $table Table to use in query's from clause
 * @param string $dsn A valid ADOdb DSN.
 *
 * @return string Returns a complete SQL statement ready to be used by a
 * ADOdb exec
 *
 * @throws ADODB_Exception if connection used to do quoting fails.
 */
function makeMultiUpsert(array $data, array $params, $table, $dsn) {
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
  $con = connect($dsn, $table);
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
        $set[] = $row[$field];
      };// else in_array $params...
    };// foreach $fields ...
    $sets[] = '(' . implode(',', $set) . ')';
  };// foreach $data ...
  $values .= ' ' . implode(',', $sets);
  $dupup = ' on duplicate key update ';
  // Loop thru and build update section.
  $updates = array();
  foreach ($params as $k => $v) {
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
 * multipleUpsert($data,$types,'CacheUntil',YAPEAL_DSN);
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
 * @uses makeMultiUpsert()
 */
function multipleUpsert(array $data, array $types, $table, $dsn) {
  global $tracing;
  if (empty($data) || empty($types)) {
    return FALSE;
  };
  $con = connect($dsn, $table);
  $mess = 'Before makeMultiUpsert for ' . $table . ' in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $upsert = makeMultiUpsert($data, $types, $table, $dsn);
  // Use a transaction for larger upserts to make them faster when we can.
  if (count($data) > 10) {
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
 * @uses multipleUpsert()
 */
function multipleUpsertAttributes($datum, array $types, $table, $dsn,
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
    multipleUpsert($data, $types, $table, $dsn);
  };// if $datum>0
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
 * upsert($data,$types,'CacheUntil',YAPEAL_DSN);
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
 * @uses makeMultiUpsert()
 */
function upsert(array $data, array $types, $table, $dsn) {
  global $tracing;
  if (empty($data) || empty($types)) {
    return FALSE;
  };
  $mess = 'Before makeMultiUpsert for ' . $table . ' in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $upsert = makeMultiUpsert(array($data) , $types, $table, $dsn);
  $con = connect($dsn);
  $effected = $con->Execute($upsert);
  return $effected;
}// function upsert
?>
