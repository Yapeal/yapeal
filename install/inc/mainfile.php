<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal Setup - mainfile.php
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know as Yapeal.
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
 * @author     Claus Pedersen <satissis@gmail.com>
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2010, Claus Pedersen, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @subpackage Setup
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
}
/***********************************************************
 * Create Error Handler
 ***********************************************************
 * Require Exception files
 */
require_once('..' . DS . 'inc' . DS . 'common_paths.php');
require_once YAPEAL_CLASS . 'YapealAutoLoad.php';
require_once YAPEAL_CLASS . 'ADODB_Exception.php';
require_once YAPEAL_CLASS . 'LoggingExceptionObserver.php';
require_once YAPEAL_CLASS . 'PrintingExceptionObserver.php';
// log_dir is relative to YAPEAL_CACHE
$realpath = realpath(YAPEAL_CACHE . 'log');
if ($realpath && is_dir($realpath)) {
  /**
   * @ignore
   */
  define('YAPEAL_LOG', $realpath . DS);
} else {
  trigger_error('Nonexistent directory defined for log', E_USER_ERROR);
};
/*
 * Define log file path
 */
/**
 * @ignore
 */
define('YAPEAL_ERROR_LOG', YAPEAL_LOG . 'setup_error.log');
/**
 * @ignore
 */
define('YAPEAL_WARNING_LOG', YAPEAL_LOG . 'setup_warning.log');
/**
 * @ignore
 */
define('YAPEAL_NOTICE_LOG', YAPEAL_LOG . 'setup_notice.log');
/**
 * @ignore
 */
define('YAPEAL_LOG_LEVEL',E_ERROR|E_USER_ERROR|E_WARNING|E_USER_WARNING);
/*
 * Setup error reporting.
 */
error_reporting(E_ALL);
ini_set('ignore_repeated_errors', 0);
ini_set('ignore_repeated_source', 0);
ini_set('html_errors', 0);
ini_set('display_errors', 0);
ini_set('error_log', YAPEAL_ERROR_LOG);
ini_set('log_errors', 1);
/*
 * Setup exception observers.
 */
$logObserver = new LoggingExceptionObserver(YAPEAL_WARNING_LOG);
$printObserver = new PrintingExceptionObserver();
ADODB_Exception::attach($logObserver);
ADODB_Exception::attach($printObserver);
unset($logObserver, $printObserver);
/*
 * Load error handler
 */
require_once YAPEAL_INC . 'elog.php';
require_once YAPEAL_CLASS . 'YapealErrorHandler.php';
/*
 * Start using custom error handler.
 */
set_error_handler(array('YapealErrorHandler', 'handle'));

/***********************************************************
 * Do the normal stuff after the error handler is done
 ***********************************************************
 * Check if there is an existing yapeal.ini file.
 * If so, then tell open Yapeal config updater.
 */
if (file_exists(YAPEAL_CONFIG . 'yapeal.ini')) {
  /*
   * Get yapeal.ini info
   */
  // Set vars use in error messages.
  $req1 = 'Missing required setting ';
  $req2 = ' in ' . YAPEAL_CONFIG . 'yapeal.ini';
  $ini = parse_ini_file(YAPEAL_CONFIG . 'yapeal.ini', TRUE);
  $dbconerror = FALSE;
  $settings = array('database', 'host', 'table_prefix', 'username', 'password');
  $data = array();
  foreach ($settings as $setting) {
    if (isset($ini['Database'][$setting])) {
      $data[$setting] = $ini['Database'][$setting];
    } else {
      $mess = $req1 . $setting . $req2 . ' section [Database].';
      trigger_error($mess, E_USER_ERROR);
    };
  };// foreach $settings ...
  // Extract the required fields from array.
  extract($data);
  /**
   * Defines the DSN used for ADOdb connection.
   */
  $dsn = 'mysql://' . $username . ':' . $password . '@' . $host . '/' . $database;
  /*
   * Try connecting to database
   */
  try {
    $con = ADONewConnection($dsn);  # no need for Connect()
  } catch (ADODB_Exception $e) {
    trigger_error($e->getMessage(), E_USER_NOTICE);
    $dbconerror = $e->getMessage();
    unset($con);
  }
  /*
   * Check if config table is there
   */
  $configTable = false;
  foreach ($cfgtables as $table) {
    $foundTable = DBHandler('CHKTAB',$table);
    if ($foundTable) {
      $configTable = $table;
    };
  };
  /*
   * Get config info
   */
  if ($configTable) {
    try {
      $query = 'SELECT * FROM `' . $table_prefix . $configTable . '`';
      $rs = $con->GetAssoc($query);
      foreach ($rs as $name=>$value) {
        $conf[$name] = $value;
      }
      unset($rs);
    } catch (ADODB_Exception $e) {
      trigger_error($e->getMessage(), E_USER_NOTICE);
      $dbconerror = $e->getMessage();
    }
  } else {
    unset($ini);
    $con->Close();
  }; // if $configTable
};

/*
 * Connect to database if yapeal.ini is false and we are on a progress page
 */
if (isset($_GET['funk']) && ($_GET['funk'] == 'dodb' || $_GET['funk'] == 'doini') && !isset($ini)) {
  $dbconerror = false;
  try {
    $config = $_POST['config'];
    $dsn = 'mysql://' . $config['DB_Username'] . ':' . $config['DB_Password'];
    $dsn .= '@' . $config['DB_Host'] . '/' . $config['DB_Database'];
    $con = ADONewConnection($dsn);  # no need for Connect()
  } catch (ADODB_Exception $e) {
    trigger_error($e->getMessage(),E_USER_NOTICE);
    $dbconerror = $e->getMessage();
    unset($con);
  }
};
?>
