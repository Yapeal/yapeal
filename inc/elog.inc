<?php
/**
 * Used to log information from Yapeal
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

/* *************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * *************************************************************************/

/**
 * Find path for includes
 */
// Assumes this file is in same directory as common_backend.inc
require_once realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'common_backend.inc';

/* *************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * *************************************************************************/

/**
 * Used to send message to a log file.
 * The "collected output" can be flushed anytime "elog()" is used...
 *
 * @param string $str Message to be sent to log file.
 * @param string $filename File to use for logging message.
 * @param bool $collected Should we flush stdout and stderr also.
 */
function elog($str,$filename=YAPEAL_ERROR_LOG,$collected=FALSE) {
  $mess='';
  // Only capture stdout and stderr when told to do so.
  if ($collected) {
    // Get anything written to stdout or stderr that did *NOT* use elog()
    // and write it now...
    $writeme=ob_get_contents();
    if ($writeme) {
      $mess.='['.gmdate('Y-m-d H:i:s').substr(microtime(false),1,4).'] ';
      $mess.=$writeme.PHP_EOL;
      ob_end_clean();
      ob_start();
    };
  };
  // Now write message this function was called to log.
  $mess.='['.gmdate('Y-m-d H:i:s').substr(microtime(false),1,4).'] ';
  $mess.=$str.PHP_EOL;
  error_log($mess,3,$filename);
  return TRUE;
}

/**
 * Only prints message if in command line mode.
 *
 * @param string $str Message to be printed.
 * @param bool $timestamp Add Timestamp in front of $str
 * @param bool $newline PHP_EOL will be added to end of $str
 *
 * @return void
 */
function print_on_command($str,$timestamp=TRUE,$newline=TRUE) {
  if (YAPEAL_SCRIPT_MODE=='cli') {
    $mess='';
    if ($timestamp) {
      $mess.='['.gmdate('Y-m-d H:i:s').substr(microtime(false),1,4).'] ';
    };
    $mess.=$str;
    if ($newline) {
      $mess.=PHP_EOL;
    };
    print $mess;
    if (ob_get_level()) {
      ob_flush();
    };
  };
}
?>
