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
function activecheck($checker) {
  if ($checker == 1) {
    return PHP_EOL . 'active=TRUE';
  } else {
    return '';
  };
}
function trueorfalse($checker) {
  if ($checker == 1) {
    return 'TRUE';
  } else {
    return 'FALSE';
  };
}
$inidata = ';;;;;
; Comments on setting up config/yapeal.ini manually can be found in
; config/yapeal.ini-example or in config/yapeal_ini_docs.txt.
; Additional information can be found in the Wiki at:
; http://code.google.com/p/yapeal/
;;;;;

date="$Date:: 2009-01-19 19:07:56 #$"

stability="beta"

version="$Revision: 471 $"

[Api]
cache_xml='.trueorfalse($_POST['cache_xml']).'
file_suffix=".xml.aspx"
url_base="http://api.eve-online.com"

[Database]
active=FALSE
database="'.$_POST['DB_Database_Main'].'"
driver="mysqli://"
host="'.$_POST['DB_Host_Main'].'"
suffix="?new"
writer="'.$_POST['DB_Username_Main'].':'.$_POST['DB_Password_Main'].'"

[Database-account]'.activecheck($_POST['db_account']).'

[Database-char]'.activecheck($_POST['db_char']).'

[Database-corp]'.activecheck($_POST['db_corp']).'

[Database-eve]'.activecheck($_POST['db_eve']).'

[Database-map]'.activecheck($_POST['db_map']).'

[Database-server]
active=TRUE

[Database-util]
active=TRUE

[Logging]
error_log="yapeal_error.log"
log_level=E_ERROR|E_WARNING|E_USER_ERROR|E_USER_WARNING
notice_log="yapeal_notice.log"
trace_log="yapeal_trace.log"
warning_log="yapeal_warning.log"

[Paths]
adodb="../ADOdb/"
base="../"
backend="../backend/"
cache="../cache/"
class="../class/"
config="../config/"
log="log/"

[Tracing]
trace=FALSE
trace_level=0
trace_output="file"
trace_section=YAPEAL_TRACE_NONE';
$fp = fopen('../config/yapeal.ini', 'w');
fwrite($fp, $inidata);
fclose($fp);

if (!(@is_readable('../config/yapeal.ini') || @is_file('../config/yapeal.ini') || @parse_ini_file('../config/yapeal.ini'))) {
  $stop++;
  $output .= '<tr>' . PHP_EOL;
  $output .= '  <td class="tableinfolbl" style="text-align: left;">Create File:</td>' . PHP_EOL;
  $output .= '  <td class="notis">yapeal.ini</td>' . PHP_EOL;
  $output .= '  <td class="warning">yapeal.ini was not created or not a valid ini file</td>' . PHP_EOL;
  $output .= '</tr>' . PHP_EOL;
} else {
  $output .= '<tr>' . PHP_EOL;
  $output .= '  <td class="tableinfolbl" style="text-align: left;">Creat File:</td>' . PHP_EOL;
  $output .= '  <td class="notis">yapeal.ini</td>' . PHP_EOL;
  $output .= '  <td class="good">Done</td>' . PHP_EOL;
  $output .= '</tr>' . PHP_EOL;
};
?>
