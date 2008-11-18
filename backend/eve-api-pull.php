#!/usr/bin/php
<?php
/**
 * Used to get information from Eve-online API and store in database.
 *
 * This script expects to be ran from a command line or from a crontab job that
 * can optionally pass a config file name with -c option.
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

// Track version of script.
define('YAPEAL_VERSION',str_replace(array('$','#'),'',
  '$Revision$ $Date:: 2008-10-24 00:48:59 #$'));

/* **************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * **************************************************************************/

// Used to over come path issues caused by how script is ran on server.
$dir=realpath(dirname(__FILE__));
chdir($dir);
// Set constant for how we're being run.
// Being used here to detect cli mode.
/**
 * @ignore
 */
defined('YEPEAL_SCRIPT_MODE')||define('YAPEAL_SCRIPT_MODE',php_sapi_name());
if (YAPEAL_SCRIPT_MODE=='cli') {
  $options=getopt('hVc:');
  foreach ($options as $opt=>$value) {
    switch ($opt) {
      case 'c':
      case '--config':
        if (realpath($value)&&is_readable(realpath($value))) {
          /**
           * @ignore
           */
          define('YAPEAL_INI_FILE',realpath($value));
          break;
        } else {
          $mess=$opt[1].' does not exist or is not readable'.PHP_EOL;
          fwrite(STDERR,$mess);
        };// else realpath $opt[1]&& ...
      case 'h':
      case '--help':
        usage();
        exit;
      case 'V':
      case '--version':
        $mess=$argv[0].' '.YAPEAL_VERSION.PHP_EOL;
        $mess.="Copyright (C) 2008, Michael Cummings".PHP_EOL;
        $mess.="This program comes with ABSOLUTELY NO WARRANTY.".PHP_EOL;
        $mess.='Licensed under the GNU LPGL 3.0 License.'.PHP_EOL;
        $mess.='See COPYING and COPYING-LESSER for more details.'.PHP_EOL;
        fwrite(STDERR,$mess);
        exit;
    };
  };
};

/* This would need to be changed if this file isn't in another path at same
 * level as 'inc' directory where common_emt.inc is.
 */
// Move up and over to 'inc' directory to read common_backend.inc
$path=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
$path.='..'.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'common_backend.inc';
require_once realpath($path);
/* **************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * **************************************************************************/

require_once YAPEAL_INC.'common_db.inc';
require_once YAPEAL_INC.'elog.inc';
require_once YAPEAL_INC.'eap_functions.inc';
//require_once YAPEAL_CLASS.'Logging_Exception_Observer.class.php';
//require_once YAPEAL_CLASS.'Printing_Exception_Observer.class.php';

function usage() {
  $progname=basename($GLOBALS['argv'][0]);
  $scriptversion=YAPEAL_VERSION;
  $use=<<<USAGE_MESSAGE
Usage: $progname [-h | -V | -c config.ini]
Options:
  -c config.ini, --config=config.ini   Read configation from 'config.ini'.
  -h, --help                           Show this help.
  -V, --version                        Show $progname version.

Version $scriptversion
USAGE_MESSAGE;
  fwrite(STDERR,$use.PHP_EOL);
};

$cachetypes=array('tableName'=>'C','ownerID'=>'I',
  'cachedUntil'=>'T');
try {
  $api='eve-api-pull';
  // Mutex to keep from having more than one pull going at once most the time.
  // Turned logging off here since this runs every minute.
  if (dontWait($api,0,FALSE,FALSE)) {
    // Give ourself up to 5 minutes to finish.
    $timer=gmdate('Y-m-d H:i:s',strtotime('5 minutes'));
    $data=array('tableName'=>$api,'ownerID'=>0,'cachedUntil'=>$timer);
    upsert($data,$cachetypes,'CachedUntil',DSN_UTIL_WRITER);
    $ctime=strtotime($timer.' +0000');
  } else {
    // Someone else has set timer need to wait it out.
    exit;
  };// else dontwait $api ...

  /* ************************************************************************
   * /eve/ API pulls
   * ************************************************************************/
  // Only pull if activated.
  if (YAPEAL_EVE_ACTIVE) {
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_EVE)==YAPEAL_DEBUG_EVE) {
      $mess='Connect before EVE section in '.__FILE__;
      print_on_command($mess);
      $yapealDebugging[YAPEAL_DEBUG_EVE]=$mess;
    };// if YAPEAL_DEBUG&&...
    $con=connect(DSN_EVE_WRITER);
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_EVE)==YAPEAL_DEBUG_EVE) {
      $mess='Before require pulls_eve.inc';
      print_on_command($mess);
      $yapealDebugging[YAPEAL_DEBUG_EVE]=$mess;
    };// if YAPEAL_DEBUG&&...
    require YAPEAL_INC.'pulls_eve.inc';
  };// if YAPEAL_EVE_ACTIVE...

  /* ************************************************************************
   * Generate corp list
   * ************************************************************************/

  // Only pull if activated.
  if (YAPEAL_CORP_ACTIVE) {
    $api='RegisteredCorporation';
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_CORP)==YAPEAL_DEBUG_CORP) {
      $mess='Connect before CORP section in '.__FILE__;
      print_on_command($mess);
      $yapealDebugging[YAPEAL_DEBUG_CORP]=$mess;
    };// if YAPEAL_DEBUG&&...
    $con=connect(DSN_CORP_WRITER);
    // Generate a list of corporation(s) we need to do updates for
    $sql='select cp.corporationID "corpid",u.userID "userid",u.fullApiKey "apikey",';
    $sql.='u.limitedApiKey "lapikey",cp.characterID "charid"';
    $sql.=' from '.DB_UTIL.'.RegisteredCorporation as cp,';
    $sql.=DB_UTIL.'.RegisteredCharacter as chr,';
    $sql.=DB_UTIL.'.RegisteredUser as u';
    $sql.=' where cp.isActive=true';
    $sql.=' and cp.characterID=chr.characterID';
    $sql.=' and chr.userID=u.userID';
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_CORP)==YAPEAL_DEBUG_CORP) {
      $mess='Before GetAll CORP updates in '.__FILE__;
      print_on_command($mess);
      $yapealDebugging[YAPEAL_DEBUG_CORP]=$mess;
    };// if YAPEAL_DEBUG&&...
    $corpList=$con->GetAll($sql);
    // Ok now that we have a list of corporations that need updated
    // we can check API for updates to their infomation.
    foreach ($corpList as $corp) {
      extract($corp);
      $ownerid=$corpid;
      /* ********************************************************************
       * Per corp API pulls
       * ********************************************************************/
      if (YAPEAL_DEBUG&&
        (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_CORP)==YAPEAL_DEBUG_CORP) {
        $mess='Before require pulls_corp.inc';
        print_on_command($mess);
        $yapealDebugging[YAPEAL_DEBUG_CORP]=$mess;
      };// if YAPEAL_DEBUG&&...
      require YAPEAL_INC.'pulls_corp.inc';
    };// foreach $corpList
  };// if YAPEAL_CORP_ACTIVE...

  /* ************************************************************************
   * Generate character list
   * ************************************************************************/

  // Only pull if activated.
  if (YAPEAL_CHAR_ACTIVE) {
    $api='RegisteredCharacter';
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_CHAR)==YAPEAL_DEBUG_CHAR) {
      $mess='Connect before CHAR section in '.__FILE__;
      print_on_command($mess);
      $yapealDebugging[YAPEAL_DEBUG_CHAR]=$mess;
    };// if YAPEAL_DEBUG&&...
    $con=connect(DSN_CHAR_WRITER);
    /* Generate a list of character(s) we need to do updates for */
    $sql='select u.userID "userid",u.fullApiKey "apikey",u.limitedApiKey "lapikey",';
    $sql.='chr.characterID "charid"';
    $sql.=' from ';
    $sql.=DB_UTIL.'.RegisteredCharacter as chr,';
    $sql.=DB_UTIL.'.RegisteredUser as u';
    $sql.=' where chr.isActive=true';
    $sql.=' and chr.userID=u.userID';
    if (YAPEAL_DEBUG&&
      (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_CHAR)==YAPEAL_DEBUG_CHAR) {
      $mess='Before GetAll CHAR updates in '.__FILE__;
      print_on_command($mess);
      $yapealDebugging[YAPEAL_DEBUG_CHAR]=$mess;
    };// if YAPEAL_DEBUG&&...
    $charList=$con->GetAll($sql);
    // Ok now that we have a list of characters that need updated
    // we can check API for updates to their infomation.
    foreach ($charList as $char) {
      extract($char);
      $ownerid=$charid;
      /* **********************************************************************
       * Per character API pulls
       * **********************************************************************/
      if (YAPEAL_DEBUG&&
        (YAPEAL_DEBUG_SECTION&YAPEAL_DEBUG_CHAR)==YAPEAL_DEBUG_CHAR) {
        $mess='Before require pulls_char.inc';
        print_on_command($mess);
        $yapealDebugging[YAPEAL_DEBUG_CHAR]=$mess;
      };// if YAPEAL_DEBUG&&...
      require YAPEAL_INC.'pulls_char.inc';
    };// foreach $charList
  };// if YAPEAL_CHAR_ACTIVE...

  $api='eve-api-pull';
  // Reset Mutex if we still own it.
  $ctime2=strtotime(get_cacheduntil($api).' +0000');
  if ($ctime==$ctime2) {
    $cuntil=gmdate('Y-m-d H:i:s');
    $data=array('tableName'=>$api,'ownerID'=>0,'cachedUntil'=>$cuntil);
    upsert($data,$cachetypes,'CachedUntil',DSN_UTIL_WRITER);
  } else {
    // Lost Mutex we should log that as notice.
    if ((YAPEAL_LOG_LEVEL&E_USER_NOTICE)==E_USER_NOTICE) {
      $mess=$api.' '.$timer.' ran long';
      print_on_command($mess);
      trigger_error($mess,E_USER_NOTICE);
    };
  };// else $timer==get_cacheduntil $api ...
  if (YAPEAL_DEBUG&&!empty($yapealDebugging)) {
    print_on_command(YAPEAL_DEBUG_SECTION);
    $sectionToName=array(
      YAPEAL_DEBUG_ACCOUNT=>'Account',YAPEAL_DEBUG_DATABASE=>'Database',
      YAPEAL_DEBUG_CHAR=>'Character',YAPEAL_DEBUG_CORP=>'Corporation',
      YAPEAL_DEBUG_EVE=>'Eve',YAPEAL_DEBUG_MAP=>'Map',
      YAPEAL_DEBUG_REQUEST=>'Request'
    );
    $data='';
    foreach ($yapealDebugging as $sections=>$messages) {
      $data='== '.$sectionToName[$sections].' =='.PHP_EOL;
      $data.=$messages;
    };
    elog($data,YAPEAL_DEBUG_LOG,TRUE);
  };// if YAPEAL_DEBUG&&...
  exit;
}
catch (Exception $e) {
  elog('Uncaught exception in eve-api-pull.php',YAPEAL_WARNING_LOG);
}
?>
