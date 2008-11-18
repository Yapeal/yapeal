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
require_once YAPEAL_INC.'elog.inc';
require_once YAPEAL_CLASS.'CurlRequest.class.php';

/**
 * Function used to connect to a DB.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return object Returns ADOdb connection object.
 *
 * @throws ADODB_Exception for any errors.
 */
function connect($dsn) {
  global $yapealDebugging;
  if (YAPEAL_DEBUG&&
    (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_DATABASE)==YAPEAL_DEBUG_DATABASE) {
    $mess='DATABASE: Before connect for '.$dsn.' in '.__FILE__;
    print_on_command($mess);
    $yapealDebugging.=$mess.PHP_EOL;
  };// if YAPEAL_DEBUG&&...
  $con=&NewADOConnection($dsn);
  if (YAPEAL_DEBUG&&YAPEAL_DEBUG_LEVEL>0&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_DATABASE)==YAPEAL_DEBUG_DATABASE) {
    $con->debug=TRUE;
  } else {
    $con->debug=FALSE;
  };
  $con->SetFetchMode(ADODB_FETCH_ASSOC);
  $con->Execute('set names utf8');
  return $con;
};

/**
 * Used to decide if we want to wait or not getting EVE API data. Has
 * randomizing wait option to help even out server and network loading.
 *
 * @param string $api Needs to be set to base part of name for example:
 * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
 * @param integer $owner Identifies owner of the api we're trying to update.
 * @param boolean $randomize When true (the default) can randomly decide
 * to delay get API data.
 * @param boolean $logit Log status by default if not waiting.
 *
 * @return Boolean Returns true when we need to get API data.
 */
function dontWait($api,$owner=0,$randomize=true,$logit=YAPEAL_LOG_LEVEL) {
  global $yapealDebugging;
  $mess='';
  $ctime=strtotime(get_cacheduntil($api,$owner).' +0000');
  $now=time()-10;// 10 seconds for EVE API time offset added :P
  // hard limited to maximum delay of 6 minutes for randomized pulls.
  // 5 minutes (300) plus a minute from being almost ready last time.
  if (($now-$ctime)>300) {
    if (($logit&E_USER_NOTICE)==E_USER_NOTICE) {
      $mess='Tired of waiting! Getting '.$api.' for '.$owner;
      print_on_command($mess);
      trigger_error($mess,E_USER_NOTICE);
    };
    return true;
  };
  // Got to wait until our time.
  if ($now<$ctime) {
    return false;
  };
  if ($randomize) {
    // The later in the day and having been delay already decreases chance of
    // being delayed again.
    // 1 in $mod chance each time with 1 in 2 up to 1 in 29 max
    // 1 + 0-23 (hours) + Time difference in minutes
    $mod=1+gmdate('G')+floor(($now-$ctime)/60);
    $rand=mt_rand(0,$mod);
    $mess='Rolled '.$rand.' '.$mod.' ';
    // Get to wait a while longer
    if ($rand==$mod) {
      return false;
    };// if $rand==$mod ...
  };// if $randomize ...
  if (($logit&E_USER_NOTICE)==E_USER_NOTICE) {
    $mess.='Get '.$api.' for '.$owner;
    print_on_command($mess);
    trigger_error($mess,E_USER_NOTICE);
  };
  return true;
};// function dontWait

/**
 * Function used to get info from Eve API.
 *
 * GetAPIinfo has been updated and now only throws Exception.
 * It will no longer trigger errors.
 *
 * @param string $api Needs to be set to base part of name for example:
 * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
 * @param integer $posttype 0=eve, 1=char, 2=corp, 3=map, 4=account See API
 * section of common_backend.inc for a list constants that should be used here.
 * @param array $postdata Is an array of data ready to be used in
 * http_build_query.
 * @param array $prefixes Is used to override api prefixes should they change
 * them in the future or add more.
 *
 * @return mixed Returns SimpleXML object or false
 *
 * @throws EVE_API_Exception for API file errors
 */
function getAPIinfo($api='ErrorList',$posttype=YAPEAL_API_EVE,$postdata=array()) {
  global $yapealDebugging;
  $prefixes=array(
    YAPEAL_API_EVE=>'/eve/',
    YAPEAL_API_CHAR=>'/char/',
    YAPEAL_API_CORP=>'/corp/',
    YAPEAL_API_MAP=>'/map/',
    YAPEAL_API_ACCOUNT=>'/account/'
  );
  // If using cache file check for it first.
  if (YAPEAL_CACHE_XML) {
    // Build cache file path
    $cachePath = YAPEAL_CACHE.$prefixes[$posttype];
    // Build base part of cache file name
    $cacheFileName = $api;
    if (isset($postdata['accountKey'])) {
      $cacheFileName .= $postdata['accountKey'];
    };
    if (isset($postdata['characterID'])) {
      $cacheFileName .= '_'.$postdata['characterID'];
    };
    $cacheFileName .= '.xml';
    $cacheFile = $cachePath.$cacheFileName;
    if (file_exists($cacheFile) && is_readable($cacheFile)) {
      if (YAPEAL_DEBUG&&
        (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST) {
        $mess='REQUEST: Loading '.$cacheFileName.' in '.__FILE__;
        print_on_command($mess);
        $yapealDebugging.=$mess.PHP_EOL;
      };// if YAPEAL_DEBUG&&...
      $xml = simplexml_load_file($cacheFile);
      $cuntil = strtotime($xml->cachedUntil[0].' +0000');
      $ctime = time();
      if ($ctime<$cuntil) {
        if (YAPEAL_DEBUG&&
          (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST) {
          $mess='REQUEST: Using '.$cacheFileName.' in '.__FILE__;
          print_on_command($mess);
          $yapealDebugging.=$mess.PHP_EOL;
        };// if YAPEAL_DEBUG&&...
        return $xml;
      };
    };
  };
  $result=array();
  $xml=NULL;
  // Build http parameter.
  $http=array('timeout'=>60,
    'url'=>API_URL_BASE.$prefixes[$posttype].$api.API_SUFFIX);
  if ($posttype==YAPEAL_API_EVE||$posttype==YAPEAL_API_MAP) {
    // Global APIs like eve, and map don't use POST data.
    $http['method']='GET';
  } else {
    // Setup for POST query.
    $http['method']='POST';
    $http['content']=http_build_query($postdata,NULL,'&');
  };// if $posttype=YAPEAL_API_EVE||...

  // Setup connection options.
  $sh=new CurlRequest($http);
  // Try to get XML.
  $result=$sh->exec();
  // Now check for errors.
  if ($result['curl_error']) {
    $mess='cURL error'.PHP_EOL;
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST&&
      $http['content']) {
      $mess.='Post parameters: '.$http['content'].PHP_EOL;
    };
    $mess.='Error code: '.$result['curl_errno'].' for '.$http['url'].PHP_EOL;
    $mess.='Error message: '.$result['curl_error'];
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess,1);
  };
  if (200!=$result['http_code']) {
    $mess='HTTP error'.PHP_EOL;
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST&&
      $http['content']) {
      $mess.='Post parameters: '.$http['content'].PHP_EOL;
    };
    $mess.='Error code: '.$result['http_code'].' for '.$http['url'].PHP_EOL;
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess,2);
  };
  if (!$result['body']) {
    $mess='API data error'.PHP_EOL;
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST&&
      $http['content']) {
      $mess.='Post parameters: '.$http['content'].PHP_EOL;
    };
    $mess.='Data empty for '.$http['url'].PHP_EOL;
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess,3);
  };
  if (!mb_strpos($result['body'],'<eveapi version="')) {
    $result=FALSE;
    $mess='API data error'.PHP_EOL;
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST&&
      $http['content']) {
      $mess.='Post parameters: '.$http['content'].PHP_EOL;
    };
    $mess.='No XML returned for '.$http['url'].PHP_EOL;
    print_on_command($mess);
    // Throw exception
    require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
    throw new EVE_API_File_Exception($mess,4);
  };
  $xml=simplexml_load_string($result['body']);
  if (isset($xml->error[0])) {
    $result=FALSE;
    $mess='Eve API error'.PHP_EOL;
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_REQUEST)==YAPEAL_DEBUG_REQUEST&&
      $http['content']) {
      $mess.='Post parameters: '.$http['content'].PHP_EOL;
    };
    $mess.='Error code: '.(int)$xml->error[0]['code'].' for '.$http['url'].PHP_EOL;
    $mess.='Error message: '.(string)$xml->error[0];
    if (YAPEAL_CACHE_XML) {
      $cacheFile = $cachePath.'error_'.$cacheFileName;
      if (is_dir($cachePath) && is_writeable($cachePath)) {
        file_put_contents($cacheFile,$xml->asXML());
      };
    };// if YAPEAL_CACHE_XML
    // Throw exception
    require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
    throw new EVE_API_Error_Exception($mess,(int)$xml->error[0]['code']);
  };
  if (YAPEAL_CACHE_XML) {
    $cacheFile = $cachePath.'error_'.$cacheFileName;
    // Delete old XML error file if it exists
    if (file_exists($cacheFile) && is_writeable($cacheFile)) {
      unlink($cacheFile);
    };
    $cacheFile = $cachePath.$cacheFileName;
    if (is_dir($cachePath) && is_writeable($cachePath)) {
      file_put_contents($cacheFile,$xml->asXML());
    };
  };// if YAPEAL_CACHE_XML
  return $xml;
};// function getAPIinfo

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
 * @return mixed Number of records inserted/update, PEAR error object, or false
 *
 * @uses multipleUpsert()
 */
function multipleUpsertAttributes(
  $datum,array $types,$table,$dsn,$extras=array()) {
  if (count($datum)>0) {
    $data=array();
    $row=array();
    foreach ($datum as $record) {
      $row=$extras;
      foreach ($record->attributes() as $k=>$v) {
        // Note that all attributes names are converted to lower case
        // Should probably NOT do this and just pass them thru
        $row[$k]=(string)$v;
      };
      $data[]=$row;
    };// foreach $datum
    multipleUpsert($data,$types,$table,$dsn);
  };// if $datum>0
}

/**
 * Get solarsystemid for a blood line.
 * @param string $chartype Which character type (blood line) to get HQ for.
 *
 * @return string Returns the HQ system for this blood line.
 *
 */
function get_BloodLineHQ($chartype) {
  $raceToSystem=array(
    'Achura'=>30000145,'Amarr'=>30002187,'Brutor'=>30002544,
    'Civire'=>30000145,'Deteis'=>30000145,'Gallente'=>30004967,
    'Intaki'=>30003788,'JinMei'=>30004967,'Khanid'=>30003863,
    'NiKunni'=>30002187,'Sebiestor'=>30002544,'Vherokior'=>30002544);
  return $raceToSystem[$chartype];
}// function get_BloodLineHQ ...

/**
 * Get blood line for a character.
 * @param integer $charid Which character to get blood line for.
 * @param string $dsn An ADOdb compatible connection string.
 *
 * @return string Returns the HQ system for this race.
 *
 * @throws PEAR_Exception for any errors.
 */
function get_charBloodLine($charid,$dsn) {
  $con=connect($dsn);
  $sql='select bloodLine from CharacterSheet';
  $sql.=' where characterID='.$charid;
  $result=$con->GetOne($sql);
  print_r($result)."\n";
  return $result;
}// function get_charBloodLine ...

/**
 * Get typeID of blood line for a character.
 * @param integer $bloodLine Which blood line to get typeID for.
 *
 * @return string Returns the typeID for this blood line.
 */
function get_bloodLineTypeID($bloodLine) {
  $bloodLineToTypeID=array(
    'Achura'=>1383,'Amarr'=>1373,'Brutor'=>1380,'Civire'=>1375,'Deteis'=>1376,
    'Gallente'=>1377,'Intaki'=>1378,'JinMei'=>1384,'Khanid'=>1385,
    'NiKunni'=>1374,'Sebiestor'=>1379,'Vherokior'=>1386);
  return $bloodLineToTypeID[$bloodLine];
}// function get_charBloodLine ...

/**
 * Navigates XML and adds lft and rgt attributes
 *
 * Navigates XML using SimpleXML and adds lft and rgt attributes of Nested Set
 * for insertion into database.
 *
 * @param SimpleXMLElement $node Current element from tree.
 * @param integer $_Id Current index for lft/rgt counting.
 * @param integer $location Location of asset.
 * Used to propagate information from parents to children that don't include it
 * by default.
 *
 * @return void
 */
function editAssets($node, $_Id = 1, $location = 0) {
  $nodeName = $node->getName();
  if ($nodeName == 'row' || $nodeName == 'result') {
    $node->addAttribute('lft', $_Id++);
    if (isset($node['locationID'])) {
      $location = $node['locationID'];
    } else {
      $node->addAttribute('locationID', $location);
    }; //if isset $node['locationID']...
  }; // if $nodeName=='row'||$nodeName=='result'
  if ($children = $node->children()) {
    foreach($children as $child) {
      $_Id = editAssets($child, $_Id, $location);
    }; //foreach children as child
  };
  if ($nodeName == 'row' || $nodeName == 'result') {
    $node->addAttribute('rgt', $_Id++);
  };
  return $_Id;
} //function editassets

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
  $sql='select stationID from CorporationSheet';
  $sql.=' where corporationID=?';
  $result=$con->GetOne($sql,array($corp));
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
function get_cacheduntil($tname,$owner=0,$table='CachedUntil',$dsn=DSN_UTIL_WRITER) {
  try {
    $con=connect($dsn);
    $sql='select cachedUntil from '.$table;
    $sql.=' where tableName=? and ownerID=?';
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
 * Function to build, prepare, execute an insert ... on duplicate key update
 *
 * Example of how to use:
 * <code>
 * $data=array('tableName'=>'eve-api-pull','ownerID'=>0,
 *   'cachedUntil'=>'2008-01-01 00:00:01');
 * $types=array('tableName'=>'text','ownerID'=>'integer',
 *   'cachedUntil'=>'timestamp');
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
 * array('tableName'=>'eve-api-pull','ownerID'=>0,
 *   'cachedUntil'=>'2008-01-01 00:00:01'), ...
 * );
 * $types=array('tableName'=>'text','ownerID'=>'integer',
 *   'cachedUntil'=>'timestamp');
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
