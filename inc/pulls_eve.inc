<?php
/**
 * Used to get eve information from Eve-online API
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
$sectionFile = basename(__FILE__);
if ($sectionFile == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/* *************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* *************************************************************************/
/* *************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* *************************************************************************/
/* **************************************************************************
* Global API pulls
* **************************************************************************/
$apis = array(
  'AllianceList' => array(
    'types' => array(
      'allianceID' => 'I',
      'executorCorpID' => 'I',
      'memberCount' => 'I',
      'name' => 'C',
      'shortName' => 'C',
      'startDate' => 'T'
    ) ,
    'xpath' => '//result/rowset[@name="alliances"]/row'
  ) ,
  'ConquerableStationList' => array(
    'types' => array(
      'corporationID' => 'I',
      'corporationName' => 'C',
      'solarSystemID' => 'I',
      'stationID' => 'I',
      'stationName' => 'C',
      'stationTypeID' => 'I'
    ) ,
    'xpath' => '//row'
  ) ,
  'ErrorList' => array(
    'types' => array(
      'errorCode' => 'I',
      'errorText' => 'C'
    ) ,
    'xpath' => '//row'
  ) ,
  'RefTypes' => array(
    'types' => array(
      'refTypeID' => 'I',
      'refTypeName' => 'C'
    ) ,
    'xpath' => '//row'
  )
);
foreach($apis as $api => $settings) {
  try {
    $mess = 'Before dontWait';
    $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
    // Should we wait to get API data
    $xml = dontWait($api) ? getAPIinfo($api, YAPEAL_API_EVE) : FALSE;
    // Set it so we wait a bit before trying again if something goes wrong.
    $cuntil = (string)$xml->cachedUntil[0];
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => YAPEAL_START_TIME);
    $mess = 'Before upsert for ' . $api . ' in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    if ($xml instanceof SimpleXMLElement) {
      $mess = 'Before xpath for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
      $datum = $xml->xpath($settings['xpath']);
      if (count($datum) > 0) {
        $mess = 'Before truncate ' . $api . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
        // Empty out old data then upsert (insert) new
        $sql = 'truncate table ' . $api;
        $con->Execute($sql);
        $mess = 'Before multiUpsert for ' . $api . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_EVE, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
        multipleUpsertAttributes($datum, $settings['types'], $api, DSN_EVE_WRITER);
      }; // if count $datum ...
      // Update CachedUntil time since we updated records and have new one.
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api, 'ownerID' => 0,
        'cachedUntil' => $cuntil);
      $mess = 'Before upsert for ' . $api . ' in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    }; // if $xml instanceof SimpleXMLElement
  }
  catch(EVE_API_File_Exception $e) {
    // Do nothing CachedUntil already set to retry in a bit.
  }
  catch(EVE_API_Exception $e) {
    // Do nothing use observers to log info
  }
  catch(ADODB_Exception $e) {
    $mess = 'ADOdb exception during ' . $api .' in '.$sectionFile;
    elog($mess, YAPEAL_WARNING_LOG);
    $message = <<<MESS
EXCEPTION:
     Code: {$e->getCode() }
  Message: {$e->getMessage() }
     File: {$e->getFile() }
     Line: {$e->getLine() }
Backtrace:
  {$e->getTraceAsString() }
  \t--- END TRACE ---
MESS;
    elog($message, YAPEAL_WARNING_LOG);
  }
}; // foreach $apis ...
?>
