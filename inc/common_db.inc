<?php
/**
 * Group of common database functions.
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
if (basename( __FILE__ )==basename($_SERVER['PHP_SELF'])) {
  exit();
};

/* **************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * **************************************************************************/

/**
 * Find path for includes
 */
// Assumes this file is in same directory as common_backend.inc
require_once realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'common_backend.inc';

/* **************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * **************************************************************************/

require_once YAPEAL_ADODB.'adodb-exceptions.inc.php';
require_once YAPEAL_ADODB.'adodb.inc.php';

/**
 * Function used to connect to a DB.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return object Returns ADOdb connection object.
 *
 * @throws ADODB_Exception for any errors.
 */
function connect($dsn) {
  $con=&NewADOConnection($dsn);
  $con->debug=FALSE;
  $con->SetFetchMode(ADODB_FETCH_ASSOC);
  $con->Execute('set names utf8');
  return $con;
};

/**
 * Get stationid (corp HQ) from corporationsheet table
 * @param integer $corp Which corporation's HQ.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return string Returns the HQ station for this corp.
 *
 * @throws ADODB_Exception for any errors.
 */
function get_corpHQ($corp,$dsn) {
  $con=connect($dsn);
  $sql='select stationid from corporationsheet';
  $sql.=' where corporationid=?';
  $result=$con->GetOne($sql,array($corp));
  return $result;
};

/**
 * Get description (wallet name) from accountkey using corpwalletdivisions table
 * @param integer $accountkey Which wallet to get name of. Range 1000-1006.
 * @param integer $corp Which corporation's wallet.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return string Returns the wallet name for this corp.
 *
 * @throws ADODB_Exception for any errors.
 */
function get_walletname($accountkey,$corp,$dsn=DSN_CORP_WRITER) {
  $con=connect($dsn);
  $sql='select description from walletdivisions';
  $sql.=' where accountkey=? and corporationid=?';
  $result=$con->GetOne($sql,array($accountkey,$corp));
  return $result;
};

/**
 * Get list of accountkeys and description (wallet names) from walletdivisions
 * table.
 * @param integer $corp Which corporation's wallets to get.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return array Returns assoc array of accountkeys and names.
 *
 * @throws ADODB_Exception for any errors.
 */
function get_walletnames($corp,$dsn=DSN_CORP_WRITER) {
  $con=connect($dsn);
  $sql='select accountkey,description accountname from walletdivisions';
  $sql.=' where corporationid=?';
  $result=$con->GetAll($sql,array($corp));
  return $result;
};

/**
 * Get description (division name) from accountkey using divisions table
 * @param integer $accountkey Which division to get name of. Range 1000-1006.
 * @param integer $corp Which corporation's division.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return string Returns the division name for this corp.
 *
 * @throws ADODB_Exception for any errors.
 */
function get_divisionname($accountkey,$corp,$dsn=DSN_CORP_WRITER) {
  $con=connect($dsn);
  $sql='select description from divisions';
  $sql.=' where accountkey=? and corporationid=?';
  $result=$con->GetOne($sql,array($accountkey,$corp));
  return $result;
};

/**
 * Get list of accountkeys and description (division names) from divisions
 * table.
 * @param integer $corp Which corporation's divisions to get.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return array Returns assoc array of accountkeys and names.
 *
 * @throws ADODB_Exception for any errors.
 */
function get_divisionnames($corp,$dsn=DSN_CORP_WRITER) {
  $con=connect($dsn);
  $sql='select accountkey,description accountname from divisions';
  $sql.=' where corporationid=?';
  $result=$con->GetAssoc($sql,array($corp));
  return $result;
};

/**
 * Get cache until time for a table from cacheduntil table
 * @param string $tname Name of table to get time for.
 * @param integer $corp ID of corporation. Use 0 for non-corp tables like
 * RefTypes.
 * @param string $table Used to override table to get time from.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return string A date/time using format 'YYYY-MM-DD HH:MM:SS'
 */
function get_cacheduntil($tname,$owner=0,$table='cacheduntil',$dsn=DSN_UTIL_WRITER) {
  try {
    $con=connect($dsn);
    $sql='select cacheduntil from '.$table;
    $sql.=' where tablename=? and ownerid=?';
    $until=$con->GetOne($sql,array($tname,$owner));
    if (!strtotime($until)) {
      return '1970-01-01 00:00:01';// One second after epox
    };
  }
  catch (ADODB_Exception $e) {
    return '1970-01-01 00:00:01';// One second after epox
  };
  return $until;
};

/**
 * Get expires time use by header() for a table from cacheduntil table
 * @param string $tname Name of table to get time for.
 * @param integer $corp ID of corporation. Use 0 for non-corp tables like
 * RefTypes or ErrorList.
 * @param string $table Used to override table to get time from.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return string A date/time using format
 * 'weekday, 2digitDay month 4digitYear HH:MM:SS +0000'
 */
function get_expires($tname,$owner=0,$table='cacheduntil',$dsn=DSN_UTIL_WRITER) {
  try {
    $con=connect($dsn);
    $sql='select date_format(cacheduntil,"%a, %d %b %Y %T +0000") cacheduntil';
    $sql.=' from '.$table;
    $sql.=' where tablename=? and ownerid=?';
    $until=$con->GetOne($sql,array($tname,$owner));
    if (!strtotime($until)) {
      return gmdate("r",1);// One second after epox
    };
  }
  catch (ADODB_Exception $e) {
    return gmdate("r",1);// One second after epox
  };
  return $until;
};

/**
 * Function to build, prepare, execute an insert ... on duplicate key update
 *
 * Example of how to use:
 * <code>
 * $data=array('tablename'=>'eve-api-pull','ownerid'=>0,
 *   'cacheduntil'=>'2008-01-01 00:00:01');
 * $types=array('tablename'=>'text','ownerid'=>'integer',
 *   'cacheduntil'=>'timestamp');
 * upsert($data,$types,'cacheuntil',DSN_UTIL_WRITER);
 * </code>
 *
 * @param array $data Assoc array of column names and value to be Upserted.
 * @param array $types Assoc array of column names and ADOdb types.
 * @param string $table Name of table to Upsert into.
 * @param string $dsn A valid ADOdb DSN.
 *
 * @return mixed Number rows effected (1),
 * FALSE if either $data or $types is empty,
 * or PEAR::Error if not using exceptions for PEAR
 *
 * @throws ADODB_Exception for any errors.
 *
 * @uses makeMultiUpsert()
 */
function upsert(array $data,array $types,$table,$dsn) {
  if (empty($data)||empty($types)) {return FALSE;};
  $upsert=makeMultiUpsert(array($data),$types,$table,$dsn);
  $con=connect($dsn);
  $effected=$con->Execute($upsert);
  return $effected;
}// function upsert ...

/**
 * Function to build, prepare, execute an insert ... on duplicate key update
 * for an array of records
 *
 * The function tries to use a SQL transaction for larger upserts and falls
 * back to normal upsert if transactions not supported by DB or the transaction
 * throws an exception when we try to use it.
 * If not using PEAR Exceptions caller has to handle PEAR::Error on return
 * Example of how to use:
 * <code>
 * $data=array(
 * array('tablename'=>'eve-api-pull','ownerid'=>0,
 *   'cacheduntil'=>'2008-01-01 00:00:01'), ...
 * );
 * $types=array('tablename'=>'text','ownerid'=>'integer',
 *   'cacheduntil'=>'timestamp');
 * multipleUpsert($data,$types,'cacheuntil',DSN_UTIL_WRITER);
 * </code>
 *
 * @param array $data An array of assoc arrays of column names and values to be
 * Upserted.
 * @param array $types Assoc array of column names and ADOdb types.
 * @param string $table Name of table to Upsert into.
 * @param string $dsn A valid ADOdb DSN.
 *
 * @return mixed Number rows effected,
 * FALSE if either $data or $types is empty,
 * or PEAR::Error if not using exceptions for PEAR
 *
 * @throws ADODB_Exception for any errors.
 *
 * @uses makeMultiUpsert()
 */
function multipleUpsert(array $data,array $types,$table,$dsn) {
  if (empty($data)||empty($types)) {return FALSE;};
  $con=connect($dsn);
  $upsert=makeMultiUpsert($data,$types,$table,$dsn);
  // Use a transaction for larger upserts to make them faster when we can.
  if (count($data)>10) {
    try {
      $con->BeginTrans();
      $ok=$con->Execute($upsert);
      if (!$ok) {
        $con->RollbackTrans();
      } else {
        $con->CommitTrans();
      };
      return $ok;
    }
    catch (ADODB_Exception $e) {
      // Rollback transaction here then we'll re-try without transaction and
      // throw another exception if there's still a problem with upsert for
      // the caller to catch.
      $con->RollbackTrans();
    }
  };// if count $data > 10&&...
  return $con->Execute($upsert);
}// function multipleUpsert ...

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
 * @throws ADODB_Exception if connect used to do quoting fails.
 */
function makeMultiUpsert(array $data,array $params,$table,$dsn) {
  // Need this so we can do quoting.
  $con=connect($dsn);
  $needsQuote=array('C','X','D','T');
  // Build query sections
  $insert='insert into '.$table.' ('.implode(',',array_keys($params)).')';
  $values=' values';
  $sets=array();
  foreach ($data as $row) {
    $set=array();
    foreach ($params as $k=>$v) {
      if (in_array($v,$needsQuote)) {
        $set[]=$con->qstr($row[$k]);
      } else {
        $set[]=$row[$k];
      };
    };
    $sets[]='('.implode(',',$set).')';
  };
  $values.=' '.implode(',',$sets);
  $dupup=' on duplicate key update ';
  // Loop thru and build update section.
  $updates=array();
  foreach ($params as $k=>$v) {
    $updates[]=$k.'=values('.$k.')';
  };
  $dupup.=implode(',',$updates);
  return $insert.$values.$dupup;
}// function makeMultiUpsert ...
?>
