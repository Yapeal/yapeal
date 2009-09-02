<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal installer's yapeal.ini file creater.
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
 * @copyright  Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
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
};
function trueorfalse($checker) {
  if ($checker == 1) {
    return 'TRUE';
  } else {
    return 'FALSE';
  };
}
if (isset($ini)) {
  /*
   * Set default values for database
   */
  $host = $ini['Database']['host'];
  $username = $ini['Database']['username'];
  $password = $ini['Database']['password'];
  $database = $ini['Database']['database'];
  $table_prefix = $ini['Database']['table_prefix'];
  /*
   * Overwrite default values for database
   */
  if (isset($config['DB_Host'])) { $host = $config['DB_Host']; }; // if isset $config['DB_Host']
  if (isset($config['DB_Username'])) { $username = $config['DB_Username']; }; // if isset $config['DB_Username']
  if (isset($config['DB_Password'])) { $password = $config['DB_Password']; }; // if isset $config['DB_Password']
  if (isset($config['DB_Database'])) { $database = $config['DB_Database']; }; // if isset $config['DB_Database']
  if (isset($config['DB_Prefix'])) { $table_prefix = $config['DB_Prefix']; }; // if isset $config['DB_Prefix']
  /*
   * Set default values for Cache
   */
  if (isset($_POST['updatedb']) && conRev($ini['version'])<=800) {
    $cache_xml = trueorfalse($ini['Api']['cache_xml']);
  } else {
    $cache_xml = trueorfalse($ini['Cache']['cache_xml']);
  }
  /*
   * Overwrite default values for Cache
   */
  if (isset($config['api_cache_xml'])) { $cache_xml = trueorfalse($config['api_cache_xml']); }; // if isset $config['api_cache_xml']
	/*
   * Set debug handler for Yapeal
   */
  if (isset($config['api_debug']) && $config['api_debug']==1) {
    $log_level = 'E_ALL';
    $trace_active = 'TRUE';
    $trace_level = '1';
    $trace_section = 'YAPEAL_TRACE_API';
  } else {
    $log_level = 'E_ERROR|E_WARNING|E_USER_ERROR|E_USER_WARNING';
    $trace_active = 'FALSE';
    $trace_level = '0';
    $trace_section = 'YAPEAL_TRACE_NONE';
  }
  /*
   * Output updating yapeal.ini
   */
  $iniaction = 'Update File';
} else {
  $host = $config['DB_Host'];
  $username = $config['DB_Username'];
  $password = $config['DB_Password'];
  $database = $config['DB_Database'];
  $table_prefix = $config['DB_Prefix'];
  /*
   * Set default values for Cache
   */
  $cache_xml = trueorfalse($config['api_cache_xml']);
	/*
   * Turn off debug handler for Yapeal since this is the first setup we are doing
   */
  $log_level = 'E_ERROR|E_WARNING|E_USER_ERROR|E_USER_WARNING';
  $trace_active = 'FALSE';
  $trace_level = '0';
  $trace_section = 'YAPEAL_TRACE_NONE';
  /*
   * Output creating yapeal.ini
   */
  $iniaction = 'Create File';
}; // if isset $ini
$inidata = ';;;;;
; Comments on setting up config/yapeal.ini manually can be found in
; config/yapeal-example.ini or in config/yapeal_ini_docs.txt.
; Additional information can be found in the Wiki at:
; http://code.google.com/p/yapeal/
;;;;;

date="$Date::                      $"

stability="beta"

version="$Revision: '.$setupversion.' $"

[Cache]
cache_output="file"
cache_xml='.$cache_xml.'

[Database]
database="'.$database.'"
driver="mysqli://"
host="'.$host.'"
suffix="?new"
table_prefix="'.$table_prefix.'"
username="'.$username.'"
password="'.$password.'"

[Logging]
error_log="yapeal_error.log"
log_level='.$log_level.'
notice_log="yapeal_notice.log"
strict_log="yapeal_strict.log"
trace_active='.$trace_active.'
trace_level='.$trace_level.'
trace_log="yapeal_trace.log"
trace_output="file"
trace_sections='.$trace_section.'
warning_log="yapeal_warning.log"';
$fp = fopen(YAPEAL_CONFIG.'yapeal.ini', 'w');
fwrite($fp, $inidata);
fclose($fp);
if (!(@is_readable(YAPEAL_CONFIG.'yapeal.ini') || @is_file(YAPEAL_CONFIG.'yapeal.ini') || @parse_ini_file(YAPEAL_CONFIG.'yapeal.ini'))) {
  $stop++;
  $output .= '<tr>' . PHP_EOL;
  $output .= '  <td class="tableinfolbl" style="text-align: left;">'.$iniaction.':</td>' . PHP_EOL;
  $output .= '  <td class="notis">yapeal.ini</td>' . PHP_EOL;
  $output .= '  <td class="warning">yapeal.ini was not created or is not a valid ini file</td>' . PHP_EOL;
  $output .= '</tr>' . PHP_EOL;
} else {
  $output .= '<tr>' . PHP_EOL;
  $output .= '  <td class="tableinfolbl" style="text-align: left;">'.$iniaction.':</td>' . PHP_EOL;
  $output .= '  <td class="notis">yapeal.ini</td>' . PHP_EOL;
  $output .= '  <td class="good">Done</td>' . PHP_EOL;
  $output .= '</tr>' . PHP_EOL;
};
?>
