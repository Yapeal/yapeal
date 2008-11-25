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
  'ServerStatus' => array(
    'types' => array(
      'serverOpen' => 'L',
      'onlinePlayers' => 'I'
    ) ,
    'xpath' => '//result'
  )
);
foreach($apis as $api => $settings) {
  try {
    // Should we wait to get API data
    $xml = dontWait($api) ? getAPIinfo($api, YAPEAL_API_SERVER) : FALSE;
    if ($xml instanceof SimpleXMLElement) {
      // Update cacheduntil time.
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api, 'ownerID' => 0,
        'cachedUntil' => $cuntil
      );
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_SERVER) == YAPEAL_TRACE_SERVER) {
        $mess = 'SERVER: Before xpath for ' . $api . ' in ' . $sectionFile;
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      $datum = $xml->xpath($settings['xpath']);
      print_r($datum)."\n";
      if (count($datum) > 0) {
        if (YAPEAL_TRACE &&
          (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_SERVER) == YAPEAL_TRACE_SERVER) {
          $mess = 'SERVER: Before truncate ' . $api . ' in ' . $sectionFile;
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        // Empty out old data then upsert (insert) new
        $sql = 'truncate table ' . $api;
        $con->Execute($sql);
        if (YAPEAL_TRACE &&
          (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_SERVER) == YAPEAL_TRACE_SERVER) {
          $mess = 'SERVER: Before multiUpsert for ' . $api . ' in ' . $sectionFile;
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        $data = array();
        $data['serverOpen'] = (string)$datum[0]->serverOpen;
        $data['onlinePlayers'] = (string)$datum[0]->onlinePlayers;
        print_r($data)."\n";
        Upsert($data, $settings['types'], $api, DSN_SERVER_WRITER);
      }; // if count $datum ...

    }; // if $xml instanceof SimpleXMLElement

  }
  catch(EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
    $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
    $data = array('tableName' => $api, 'ownerID' => 0,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
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
