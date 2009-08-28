<?php
/**
 * Group of common database functions.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal.
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
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
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
/**
 * Used to decide if we want to wait or not getting EVE API data. Has
 * randomizing wait option to help even out server and network loading.
 *
 * @param string $api Needs to be set to base part of name for example:
 * /corp/StarbaseDetail.xml.aspx would just be StarbaseDetail
 * @param integer $owner Identifies owner of the api we're trying to update.
 * @param boolean $randomize When true (the default) can randomly decide
 * to delay get API data.
 *
 * @return Boolean Returns true when we need to get API data.
 */
function dontWait($api, $owner = 0, $randomize = TRUE) {
  global $tracing;
  $mess = 'Before getCachedUntil for ' . $api . ' in ' . basename(__FILE__);
  $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 1) &&
  $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
  $ctime = strtotime(getCachedUntil($api, $owner) . ' +0000');
  $now = time() - 10; // 10 seconds for EVE API time offset added :P
  // hard limited to maximum delay of 6 minutes for randomized pulls.
  // 5 minutes (300) plus a minute from being almost ready last time.
  $mess = '';
  if (($now - $ctime) > 300) {
    $mess = 'Tired of waiting! Getting ' . $api . ' for ' . $owner;
    trigger_error($mess, E_USER_NOTICE);
    return TRUE;
  };
  // Got to wait until our time.
  if ($now < $ctime) {
    return FALSE;
  };
  if ($randomize) {
    // The later in the day and having been delay already decreases chance of
    // being delayed again.
    // 1 in $mod chance each time with 1 in 2 up to 1 in 29 max
    // 1 + 0-23 (hours) + Time difference in minutes
    $mod = 1 + gmdate('G') + floor(($now - $ctime) / 60);
    $rand = mt_rand(0, $mod);
    $mess = 'Rolled ' . $rand . ' out of ' . $mod . '. ';
    // Get to wait a while longer
    if ($rand == $mod) {
      return FALSE;
    };// if $rand==$mod ...
  };// if $randomize ...
    $mess .= 'Get ' . $api . ' for ' . $owner;
    trigger_error($mess, E_USER_NOTICE);
  return TRUE;
}// function dontWait
/**
 * Get cache until time for a table from cacheduntil table
 * @param string $tname Name of table to get time for.
 * @param integer $owner ID of owner. Use 0 for non-corp tables like
 * RefTypes.
 *
 * @return string A date/time using format 'YYYY-MM-DD HH:MM:SS'
 */
function getCachedUntil($tname, $owner) {
  try {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $sql = 'select cachedUntil';
    $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilCachedUntil`';
    $sql .= ' where tableName=? and ownerID=?';
    $until = $con->GetOne($sql, array($tname, $owner));
    if (!strtotime($until)) {
      $until = '1970-01-01 00:00:01'; // One second after epox
    };
  }
  catch(ADODB_Exception $e) {
    $until = '1970-01-01 00:00:01'; // One second after epox
  };
  return $until;
}// function getCachedUntil
?>
