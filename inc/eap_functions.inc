<?php
/**
 * Used to get information from Eve-online API
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

/**
 * Find path for includes
 */
// Assumes this file is in same directory as common_backend.inc
require_once realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'common_backend.inc';
require_once 'DB/NestedSet.php';
require_once YAPEAL_INC.'common_db.inc';
require_once YAPEAL_INC.'elog.inc';
require_once YAPEAL_CLASS.'CurlRequest.class.php';

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
  $prefixes=array(
    YAPEAL_API_EVE=>'/eve/',
    YAPEAL_API_CHAR=>'/char/',
    YAPEAL_API_CORP=>'/corp/',
    YAPEAL_API_MAP=>'/map/',
    YAPEAL_API_ACCOUNT=>'/account/'
  );
  $result=array();
  $xml=NULL;
  // Build http parameter.
  $http=array('timeout'=>60,'url'=>API_URL_BASE.$prefixes[$posttype].$api.API_SUFFIX);
  if ($posttype==YAPEAL_API_EVE||$posttype==YAPEAL_API_MAP) {
    // Global APIs like eve, and map don't use POST data.
    $http['method']='GET';
  } else {
    // Setup for POST query.
    $http['method']='POST';
    $http['post_fields']=http_build_query($postdata,NULL,'&');
  };// if $posttype=YAPEAL_API_EVE||...

  // Setup connection options.
  $sh=new CurlRequest($http);
  // Try to get XML.
  $result=$sh->exec();
  // Now check for errors.
  if ($result['curl_error']) {
    $mess='CURL error '.$result['curl_error'].' for '.$http['url'];
    if ($http['post_fields']) {
      $mess.=PHP_EOL.'Parameters '.$http['post_fields'];
    };
    // Throw exception, or ...
    if (YAPEAL_EXCEPTIONS) {
      require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
      throw new EVE_API_File_Exception($mess,1);
    // trigger error instead.
    } else if ((YAPEAL_LOG_LEVEL&E_USER_WARNING)==E_USER_WARNING) {
      print_on_command($mess);
      trigger_error($mess,E_USER_WARNING);
    };
  };
  if (200!=$result['http_code']) {
    $mess='HTTP error '.$result['http_code'].' for '.$http['url'];
    if ($http['post_fields']) {
      $mess.=PHP_EOL.'Parameters '.$http['post_fields'];
    };
    // Throw exception, or ...
    if (YAPEAL_EXCEPTIONS) {
      require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
      throw new EVE_API_File_Exception($mess,2);
    // trigger error instead.
    } else if ((YAPEAL_LOG_LEVEL&E_USER_WARNING)==E_USER_WARNING) {
      print_on_command($mess);
      trigger_error($mess,E_USER_WARNING);
    };
  };
  if (!$result['body']) {
    $mess='API data was empty for '.$http['url'];
    if ($http['post_fields']) {
      $mess.=PHP_EOL.'Parameters '.$http['post_fields'];
    };
    // Throw exception, or ...
    if (YAPEAL_EXCEPTIONS) {
      require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
      throw new EVE_API_File_Exception($mess,3);
    // trigger error instead.
    } else if ((YAPEAL_LOG_LEVEL&E_USER_WARNING)==E_USER_WARNING) {
      print_on_command($mess);
      trigger_error($mess,E_USER_WARNING);
    };
  };
  if (!mb_strpos($result['body'],'<eveapi version="')) {
    $result=FALSE;
    $mess="API didn't return xml for ".$http['url'];
    if ($http['post_fields']) {
      $mess.=PHP_EOL.'Parameters '.$http['post_fields'];
    };
    // Throw exception, or ...
    if (YAPEAL_EXCEPTIONS) {
      require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
      throw new EVE_API_File_Exception($mess,4);
    // trigger error instead.
    } else if ((YAPEAL_LOG_LEVEL&E_USER_WARNING)==E_USER_WARNING) {
      print_on_command($mess);
      trigger_error($mess,E_USER_WARNING);
    };
  };
  $xml=simplexml_load_string($result['body']);
  if (isset($xml->error[0])) {
    $result=FALSE;
    $mess='API error '.(int)$data->error[0]['code'].' '.(string)$data->error[0];
    $mess.=' for '.$http['url'];
    if ($http['post_fields']) {
      $mess.=PHP_EOL.'Parameters '.$http['post_fields'];
    };
    // Throw exception, or ...
    if (YAPEAL_EXCEPTIONS) {
      require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
      throw new EVE_API_Error_Exception($mess,(int)$data->error[0]['code']);
    // trigger error instead.
    } else if ((YAPEAL_LOG_LEVEL&E_USER_WARNING)==E_USER_WARNING) {
      print_on_command($mess);
      trigger_error($mess,E_USER_WARNING);
    };
  };
  return $xml;
};// function getAPIinfo

/**
 * Builds table upsert from the attributes of a SimpleXMLElement array
 *
 * @param mixed $datum SimpleXMLElement object who's element attributes will
 * be inserted into table or an array of SimpleXMLElement objects with
 * attributes
 * @param array $types Keys are DB field names and values are thier ADOdb type
 * i.e. 'id'=>'integer','name'=>'text' ...
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
    print_on_command('In multiupsertattr');
    foreach ($datum as $record) {
      $row=$extras;
      foreach ($record->attributes() as $k=>$v) {
        // Note that all attributes names are converted to lower case
        // Should probably NOT do this and just pass them thru
        $row[mb_strtolower($k)]=(string)$v;
      };
      $data[]=$row;
    };// foreach $datum
    elog($data,YAPEAL_WARNING_LOG);
    multipleUpsert($data,$types,$table,$dsn);
  };// if $datum>0
}

/**
 * Recrusive function used for asset list API to build NestedSet
 *
 * @param array $row SimpleXML element who's attributes we'll be adding.
 * @param integer $parent ID of parent DB item we're adding this $row to.
 * @param integer @nestedSet The nested set resource to use to add $row.
 * @param integer $ownerid Owner of the $row we're adding (corp,char).
 * @param integer $location ID of location in eve for $row will be one of
 * hanger, office, or somewhere in space.
 *
 * @return bool
 *
 * @uses DB_NestedSet::createSubNode()
 */
// TODO: Needs to be changed to work with characters as well.
function addNode($row,$parent,$nestedSet,$ownerid,$location) {
  $nodeData=array('ownerid'=>$ownerid,'locationid'=>$location);
  foreach ($row->attributes() as $k=>$v) {
    $nodeData[mb_strtolower($k)]=(string)$v;
    // If we have our own locationid save it for any children we might have
    if ($k=='locationID') {
      $location=(string)$v;
    };// if $k ...
  };// foreach $row->attributes() ...
  $node=$nestedSet->createSubNode($parent,$nodeData);
  if (count($row->children())) {
    foreach ($row->rowset->row as $subnode) {
      // The recursive call that makes magic happen
      addNode($subnode,$node,$nestedSet,$ownerid,$location);
    };// foreach
  };// if count $row->children ...
  return true;
}// function addNode ...
?>
