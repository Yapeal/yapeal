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
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
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
    // Should we wait to get API data
    $xml = dontWait($api) ? getAPIinfo($api, YAPEAL_API_EVE) : false;
    if ($xml instanceof SimpleXMLElement) {
      // Update cacheduntil time.
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array(
        'tableName' => $api,
        'ownerID' => 0,
        'cachedUntil' => $cuntil
      );
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_EVE) == YAPEAL_TRACE_EVE) {
        $mess = 'EVE: Before xpath for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      $datum = $xml->xpath($settings['xpath']);
      if (count($datum) > 0) {
        if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_EVE) == YAPEAL_TRACE_EVE) {
          $mess = 'EVE: Before truncate ' . $api . ' in ' . basename(__FILE__);
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        // Empty out old data then upsert (insert) new
        $sql = 'truncate table ' . $api;
        $con->Execute($sql);
        if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_EVE) == YAPEAL_TRACE_EVE) {
          $mess = 'EVE: Before multiUpsert for ' . $api . ' in ' . basename(__FILE__);
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        multipleUpsertAttributes($datum, $settings['types'], $api, DSN_EVE_WRITER);
      }; // if count $datum ...

    }; // if $xml instanceof SimpleXMLElement

  }
  catch(EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
    $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
    $data = array(
      'tableName' => $api,
      'ownerID' => 0,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  }
  catch(EVE_API_Exception $e) {
    // Do nothing use observers to log info

  }
  catch(ADODB_Exception $e) {
    $mess = 'ADOdb exception during ' . $api .'in '.basename(__FILE__);
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
