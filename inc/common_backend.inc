<?php
/**
 * Common include file used to setup environment for Yapeal
 *
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

// Insure minimum version of PHP 5 we need to run.
if (version_compare(PHP_VERSION,'5.2.0','<')) {
  $mess='Need minimum of PHP 5.2.0 to use this software';
  trigger_error($mess,E_USER_ERROR);
}

// Used to over come path issues caused by how script is ran on server.
$dir=realpath(dirname(__FILE__));
chdir($dir);

/* **************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * **************************************************************************/

// Set a constant for location of ini file.
// Default assumes that this file and yapeal ini file are in 'neighboring'
// directories.
$ds=DIRECTORY_SEPARATOR;
if (!$ini_file=realpath($dir.$ds.'..'.$ds.'config'.$ds.'yapeal.ini')) {
  $mess="Bad path to required ini configuration file for Yapeal";
  trigger_error($mess,E_USER_ERROR);
};
if (is_file($ini_file)&&is_readable($ini_file)) {
  defined('YAPEAL_INI_FILE')||define('YAPEAL_INI_FILE',$ini_file);
} else {
  $mess="Can't find required ini configuration file for Yapeal";
  trigger_error($mess,E_USER_ERROR);
};

/* **************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * **************************************************************************/

/* Any errors that are trigger in this file are reported to the system default
 * logging location until we're done setting up some of the required vars and
 * we can start our own.
 */

// Set constant for how we're being run.
// Mostly being used to detect cli vs cgi or apache for now where needed.
defined('YAPEAL_SCRIPT_MODE')||define('YAPEAL_SCRIPT_MODE',php_sapi_name());

// Define some constants used with debug_section setting.
// They need to be set here so they exist before reading ini file.
define('YAPEAL_DEBUG_NONE',0);
define('YAPEAL_DEBUG_ACCOUNT',1);
define('YAPEAL_DEBUG_DATABASE',2);
define('YAPEAL_DEBUG_CHAR',4);
define('YAPEAL_DEBUG_CORP',8);
define('YAPEAL_DEBUG_EVE',16);
define('YAPEAL_DEBUG_MAP',32);
define('YAPEAL_DEBUG_REQUEST',64);
define('YAPEAL_DEBUG_ALL',16777215);

// Grab the info from ini file.
$iniVars=parse_ini_file(YAPEAL_INI_FILE,TRUE);
// Abort if required sections aren't defined
if (!(isset($iniVars['Api'])&&isset($iniVars['Database'])&&
  isset($iniVars['Debugging'])&&isset($iniVars['Logging'])&&
  isset($iniVars['Paths']))) {
  $mess='One or more of the required sections:';
  $mess.=' API, Debugging, Database, Paths, Logging';
  $mess.=' is missing from '.YEPEAL_INI_FILE;
  trigger_error($mess,E_USER_ERROR);
};// if isset ...
// Set vars use in error messages.
$req1='Required variable ';
$req2=' in '.YAPEAL_INI_FILE." file isn't defined";
$nonexist='None existent path defined for ';
$ds=DIRECTORY_SEPARATOR;

/* **************************************************************************
 * Paths
 * **************************************************************************/
// Make sure required setting vars aren't already set
unset($adodb_dir,$base_dir,$backend_dir,$cache_dir,$class_dir,$config_dir,$inc_dir,$log_dir);
// Pull out Paths settings
extract($iniVars['Paths']);

// Triggers error if a required setting is missing.
isset($adodb_dir)||trigger_error($req1.'adodb_dir'.$req2,E_USER_ERROR);
isset($base_dir)||trigger_error($req1.'base_dir'.$req2,E_USER_ERROR);
isset($backend_dir)||trigger_error($req1.'backend_dir'.$req2,E_USER_ERROR);
isset($cache_dir)||trigger_error($req1.'cache_dir'.$req2,E_USER_ERROR);
isset($class_dir)||trigger_error($req1.'class_dir'.$req2,E_USER_ERROR);
isset($config_dir)||trigger_error($req1.'config_dir'.$req2,E_USER_ERROR);
isset($inc_dir)||trigger_error($req1.'inc_dir'.$req2,E_USER_ERROR);
isset($log_dir)||trigger_error($req1.'log_dir'.$req2,E_USER_ERROR);

// Define some constants from vars
if ($realpath=realpath($dir.$ds.$adodb_dir)) {
  define('YAPEAL_ADODB',$realpath.$ds);
} else {
  trigger_error($nonexist.'adodb_dir',E_USER_ERROR);
};
if ($realpath=realpath($dir.$ds.$base_dir)) {
  define('YAPEAL_BASE',$realpath.$ds);
} else {
  trigger_error($nonexist.'base_dir',E_USER_ERROR);
};
if ($realpath=realpath($dir.$ds.$backend_dir)) {
  define('YAPEAL_BACKEND',$realpath.$ds);
} else {
  trigger_error($nonexist.'backend_dir',E_USER_ERROR);
};
if ($realpath=realpath($dir.$ds.$cache_dir)) {
  if (is_writable($realpath)) {
    define('YAPEAL_CACHE',$realpath.$ds);
  } else {
    trigger_error($realpath.' is not writeable',E_USER_ERROR);
  };
} else {
  trigger_error($nonexist.'cache_dir',E_USER_ERROR);
};
if ($realpath=realpath($dir.$ds.$class_dir)) {
  define('YAPEAL_CLASS',$realpath.$ds);
} else {
  trigger_error($nonexist.'class_dir',E_USER_ERROR);
};
if ($realpath=realpath($dir.$ds.$inc_dir)) {
  define('YAPEAL_INC',$realpath.$ds);
} else {
  trigger_error($nonexist.'inc_dir',E_USER_ERROR);
};
// This is relative to YAPEAL_CACHE
if ($realpath=realpath(YAPEAL_CACHE.$log_dir)) {
  if (is_writable($realpath)) {
    define('YAPEAL_LOG',$realpath.$ds);
  } else {
    trigger_error($realpath.' is not writeable',E_USER_ERROR);
  };
} else {
  trigger_error($nonexist.'log_dir',E_USER_ERROR);
};

/* **************************************************************************
 * Logging
 * **************************************************************************/
// Make sure required setting vars aren't already set
unset($debug_log,$error_log,$level,$notice_log,$use_exception,$warning_log);
// Pull out Logging settings
extract($iniVars['Logging']);

// Triggers error if a required setting is missing.
isset($debug_log)||trigger_error($req1.'debug_log'.$req2,E_USER_ERROR);
isset($error_log)||trigger_error($req1.'error_log'.$req2,E_USER_ERROR);
isset($level)||trigger_error($req1.'level'.$req2,E_USER_ERROR);
isset($notice_log)||trigger_error($req1.'notice_log'.$req2,E_USER_ERROR);
isset($use_exception)||trigger_error($req1.'use_exception'.$req2,E_USER_ERROR);
isset($warning_log)||trigger_error($req1.'warning_log'.$req2,E_USER_ERROR);

// Define some constants from vars
define('YAPEAL_DEBUG_LOG',YAPEAL_LOG.$debug_log);
define('YAPEAL_ERROR_LOG',YAPEAL_LOG.$error_log);
// NOTE this is deprecated and being forced to TRUE for now. It will be removed
// in the near future.
define('YAPEAL_EXCEPTIONS',TRUE);
define('YAPEAL_LOG_LEVEL',$level);
define('YAPEAL_NOTICE_LOG',YAPEAL_LOG.$notice_log);
define('YAPEAL_WARNING_LOG',YAPEAL_LOG.$warning_log);

/* **************************************************************************
 * Setup error and exception logging
 * **************************************************************************/

require_once YAPEAL_CLASS.'ErrorHandler.class.php';
require_once YAPEAL_CLASS.'EVE_API_Exception.class.php';
require_once YAPEAL_CLASS.'Logging_Exception_Observer.class.php';
require_once YAPEAL_CLASS.'Printing_Exception_Observer.class.php';

//Setup Error logging
error_reporting(YAPEAL_LOG_LEVEL);
ini_set('error_log',YAPEAL_ERROR_LOG);
ini_set('ignore_repeated_errors',true);
ini_set('ignore_repeated_source',true);
ini_set('log_errors',true);

// Setup exception observers.
EVE_API_Exception::attach(new Logging_Exception_Observer(YAPEAL_ERROR_LOG));
EVE_API_Exception::attach(new Printing_Exception_Observer());
//Yapeal_ADOdb_Exception::attach(new Logging_Exception_Observer(YAPEAL_ERROR_LOG));
//Yapeal_ADOdb_Exception::attach(new Printing_Exception_Observer());
/* Any errors or exceptions after this point go to our custom settings */

/* **************************************************************************
 * Api
 * **************************************************************************/

// Make sure required setting vars aren't already set
unset($cache_xml,$file_suffix,$url_base);
// Pull out API settings
extract($iniVars['Api']);

// Triggers error if a required setting is missing.
isset($cache_xml)||trigger_error($req1.'cache_xml'.$req2,E_USER_ERROR);
isset($file_suffix)||trigger_error($req1.'file_suffix'.$req2,E_USER_ERROR);
isset($url_base)||trigger_error($req1.'url_base'.$req2,E_USER_ERROR);

// Define some constants from vars
define('API_URL_BASE',$url_base);
define('API_SUFFIX',$file_suffix);
define('YAPEAL_CACHE_XML',$cache_xml);

// Define some addtional constants that are mostly used by pull scripts
define('YAPEAL_API_EVE',0);
define('YAPEAL_API_CHAR',1);
define('YAPEAL_API_CORP',2);
define('YAPEAL_API_MAP',3);
define('YAPEAL_API_ACCOUNT',4);

/* **************************************************************************
 * Debug
 * **************************************************************************/

// Make sure required setting vars aren't already set
unset($debug,$debug_level,$debug_section);
// Pull out API settings
extract($iniVars['Debugging']);

// Triggers error if a required setting is missing.
isset($debug)||trigger_error($req1.'debug'.$req2,E_USER_ERROR);
isset($debug_level)||trigger_error($req1.'debug_level'.$req2,E_USER_ERROR);
isset($debug_section)||trigger_error($req1.'debug_section'.$req2,E_USER_ERROR);

// Define some constants from vars
define('YAPEAL_DEBUG',$debug);
define('YAPEAL_DEBUG_LEVEL',$debug_level);
define('YAPEAL_DEBUG_SECTION',$debug_section);

// Var used to collect debug info
$yapealDebugging='';

/* **************************************************************************
 * Required DBs
 * **************************************************************************/

$dbs=array('char','corp','eve','util');
foreach ($dbs as $db) {
  // Make sure required setting vars aren't already set
  unset($active,$database,$driver,$host,$suffix,$writer);
  // Pull out default database settings
  extract($iniVars['Database']);
  // Overwrite defaults with ones given in Database-xxx section
  if (isset($iniVars['Database-'.$db])) {
    extract($iniVars['Database-'.$db]);
  };
  // All constant names are in uppercase only.
  $db=strtoupper($db);
  if (isset($active)&&$active) {
    // Triggers error if a required setting is missing for any section.
    isset($database)||trigger_error($req1.'database'.$req2,E_USER_ERROR);
    isset($driver)||trigger_error($req1.'driver'.$req2,E_USER_ERROR);
    isset($host)||trigger_error($req1.'host'.$req2,E_USER_ERROR);
    isset($suffix)||trigger_error($req1.'suffix'.$req2,E_USER_ERROR);
    isset($writer)||trigger_error($req1.'writer'.$req2,E_USER_ERROR);

    // Mark as active.
    define('YAPEAL_'.$db.'_ACTIVE',TRUE);
    // Define some constants from vars.
    define('DB_'.$db,$database);
    define('DSN_'.$db.'_WRITER',$driver.$writer.'@'.$host.'/'.$database.$suffix);
  } else {
    // Mark as inactive.
    define('YAPEAL_'.$db.'_ACTIVE',FALSE);
  };// else $active...
};// foreach $dbs...
?>
