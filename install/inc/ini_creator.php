<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer's yapeal.ini file creater.
 *
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
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */

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
if (isset($_GET['edit'])) {
  if ($config['db_action']==2) {
    $host = $config['DB_Host'];
    $username = $config['DB_Username'];
    $password = $config['DB_Password'];
    $database = $config['DB_Database'];
    $table_prefix = $config['DB_Prefix'];
  } elseif ($config['db_action']==1) {
    $host = $config['DB_Host'];
    $username = $config['DB_Username'];
    $password = $config['DB_Password'];
    $database = $ini_yapeal['Database']['database'];
    $table_prefix = $ini_yapeal['Database']['table_prefix'];
  } else {
    $host = $ini_yapeal['Database']['host'];
    $username = $ini_yapeal['Database']['username'];
    $password = $ini_yapeal['Database']['password'];
    $database = $ini_yapeal['Database']['database'];
    $table_prefix = $ini_yapeal['Database']['table_prefix'];
  }
  if ($config['debug']==1) {
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
} else {
  $host = $config['DB_Host'];
  $username = $config['DB_Username'];
  $password = $config['DB_Password'];
  $database = $config['DB_Database'];
  $table_prefix = $config['DB_Prefix'];
  $log_level = 'E_ERROR|E_WARNING|E_USER_ERROR|E_USER_WARNING';
  $trace_active = 'FALSE';
  $trace_level = '0';
  $trace_section = 'YAPEAL_TRACE_NONE';
}
$inidata = ';;;;;
; Comments on setting up config/yapeal.ini manually can be found in
; config/yapeal-example.ini or in config/yapeal_ini_docs.txt.
; Additional information can be found in the Wiki at:
; http://code.google.com/p/yapeal/
;;;;;

date="$Date:: 2009-01-31 05:03:14 #$"

stability="beta"

version="$Revision: 526 $"

[Api]
cache_xml='.trueorfalse($config['cache_xml']).'
file_suffix=".xml.aspx"
url_base="http://api.eve-online.com"
account_active='.trueorfalse($config['db_account']).'
char_active='.trueorfalse($config['db_char']).'
corp_active='.trueorfalse($config['db_corp']).'
eve_active='.trueorfalse($config['db_eve']).'
map_active='.trueorfalse($config['db_map']).'
server_active=TRUE

[Database]
host="'.$host.'"
username="'.$username.'"
password="'.$password.'"
database="'.$database.'"
table_prefix="'.$table_prefix.'"
driver="mysqli://"
suffix="?new"

[Logging]
error_log="yapeal_error.log"
log_level='.$log_level.'
notice_log="yapeal_notice.log"
warning_log="yapeal_warning.log"
trace_active='.$trace_active.'
trace_level='.$trace_level.'
trace_log="yapeal_trace.log"
trace_output="file"
trace_section='.$trace_section;
$fp = fopen('..'.$DS.'config'.$DS.'yapeal.ini', 'w');
fwrite($fp, $inidata);
fclose($fp);
if (isset($_GET['edit'])) { $iniaction = ED_UPDATE_FILE; } else { $iniaction = INSTALLER_CREATE_FILE; }; // if isset $_GET['edit']
if (!(@is_readable('..'.$DS.'config'.$DS.'yapeal.ini') || @is_file('..'.$DS.'config'.$DS.'yapeal.ini') || @parse_ini_file('..'.$DS.'config'.$DS.'yapeal.ini'))) {
  $stop++;
  $output .= '<tr>' . PHP_EOL;
  $output .= '  <td class="tableinfolbl" style="text-align: left;">'.$iniaction.':</td>' . PHP_EOL;
  $output .= '  <td class="notis">yapeal.ini</td>' . PHP_EOL;
  $output .= '  <td class="warning">yapeal.ini '.INSTALLER_CREATE_ERROR.'</td>' . PHP_EOL;
  $output .= '</tr>' . PHP_EOL;
} else {
  
  $output .= '<tr>' . PHP_EOL;
  $output .= '  <td class="tableinfolbl" style="text-align: left;">'.$iniaction.':</td>' . PHP_EOL;
  $output .= '  <td class="notis">yapeal.ini</td>' . PHP_EOL;
  $output .= '  <td class="good">'.DONE.'</td>' . PHP_EOL;
  $output .= '</tr>' . PHP_EOL;
};
?>
