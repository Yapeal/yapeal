<?php
/**
 * Used to get corp information from Eve-online API
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
/****************************************************************************
* Per corp API pulls
****************************************************************************/
/* Corporation Sheet */
$api = 'CorporationSheet';
$postdata = array(
  'userID' => $userid,
  'apiKey' => $apikey,
  'characterID' => $charid
);
// Set the field types of query by name.
$types = array(
  'corporationID' => 'I',
  'corporationName' => 'C',
  'ticker' => 'C',
  'ceoID' => 'I',
  'ceoName' => 'C',
  'stationID' => 'I',
  'stationName' => 'C',
  'description' => 'X',
  'url' => 'C',
  'allianceID' => 'I',
  'allianceName' => 'X',
  'taxRate' => 'N',
  'memberCount' => 'I',
  'memberLimit' => 'I',
  'shares' => 'I'
);
try {
  // Should we wait to get API data
  // No randomness here because other tables depend on this data
  $xml = dontWait($api, $ownerid, false) ? getAPIinfo($api, YAPEAL_API_CORP, $postdata) : false;
  if ($xml instanceof SimpleXMLElement) {
    // Update cacheduntil time
    $cuntil = (string)$xml->cachedUntil[0];
    $data = array(
      'tableName' => $api,
      'ownerID' => $ownerid,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
      $mess = 'CORP: Before clone for ' . $api . ' in ' . basename(__FILE__);
      print_on_command($mess);
      $yapealTracing.= $mess . PHP_EOL;
    }; // if YAPEAL_TRACE&&...
    // Main table first
    // Must be done first because of foreign key constraints in
    // other tables
    $datum = clone $xml->result[0]->children();
    // Get rid of child table stuff
    unset($datum->rowset, $datum->logo);
    if (count($datum) > 0) {
      // Only get alliance name field if in one ugh!
      // Set it to '' and let it be overwritten for corps in alliances
      $data = array('allianceName' => '');
      foreach($datum as $k => $v) {
        $data[$k] = (string)$v;
      };
      if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before upsert for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      upsert($data, $types, $api, DSN_CORP_WRITER);
    }; // if $datum>0
    /* walletDivisions */
    $dbTable = 'walletDivisions';
    // Set the field types of query by name.
    $types = array(
      'accountKey' => 'I',
      'ownerID' => 'I',
      'description' => 'C'
    );
    $datum = $xml->xpath('//rowset[@name="' . $dbTable . '"]/row');
    if (count($datum) > 0) {
      $extras = array('ownerID' => $ownerid);
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before multiUpsert for ' . $dbTable . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      multipleUpsertAttributes($datum, $types, $dbTable, DSN_CORP_WRITER, $extras);
    }; // if $datum>0
    /* Divisions */
    $dbTable = 'divisions';
    // Set the field types of query by name.
    $types = array(
      'accountKey' => 'I',
      'ownerID' => 'I',
      'description' => 'C'
    );
    $datum = $xml->xpath('//rowset[@name="' . $dbTable . '"]/row');
    if (count($datum) > 0) {
      $extras = array(
        'ownerID' => $ownerid
      );
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before multiUpsert for ' . $dbTable . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      multipleUpsertAttributes($datum, $types, $dbTable, DSN_CORP_WRITER, $extras);
    }; // if $datum>0
    /* corplogo */
    $dbTable = 'logo';
    // Set the field types of query by name.
    $types = array(
      'graphicID' => 'I',
      'ownerID' => 'I',
      'shape1' => 'I',
      'shape2' => 'I',
      'shape3' => 'I',
      'color1' => 'I',
      'color2' => 'I',
      'color3' => 'I'
    );
    $datum = $xml->xpath('//logo');
    if (count($datum) > 0) {
      $data = array('ownerID' => $ownerid);
      foreach($datum[0]->children() as $k => $v) {
        $data[$k] = (int)$v;
      };
      if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before upsert for ' . $dbTable . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      upsert($data, $types, $dbTable, DSN_CORP_WRITER);
    }; // if $datum>0

  }; // if $xml instanceof SimpleXMLElement

}
catch(EVE_API_File_Exception $e) {
  // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
  $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
  $data = array('tableName' => $api, 'ownerID' => $ownerid,
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
$apis = array(
  'AccountBalance' => array(
    'types' => array(
      'accountID' => 'I',
      'accountKey' => 'I',
      'balance' => 'N',
      'ownerID' => 'I'
    ) ,
    'xpath' => '//row'
  ) ,
  'IndustryJobs' => array(
    'types' => array(
      'activityID' => 'I',
      'assemblyLineID' => 'I',
      'beginProductionTime' => 'T',
      'charMaterialMultiplier' => 'N',
      'charTimeMultiplier' => 'N',
      'completed' => 'I',
      'completedStatus' => 'I',
      'completedSuccessfully' => 'I',
      'containerID' => 'I',
      'containerLocationID' => 'I',
      'containerTypeID' => 'I',
      'endProductionTime' => 'T',
      'installedInSolarSystemID' => 'T',
      'installedItemCopy' => 'I',
      'installedItemFlag' => 'I',
      'installedItemID' => 'I',
      'installedItemLicensedProductionRunsRemaining' => 'I',
      'installedItemLocationID' => 'I',
      'installedItemMaterialLevel' => 'I',
      'installedItemProductivityLevel' => 'I',
      'installedItemQuantity' => 'I',
      'installedItemTypeID' => 'I',
      'installerID' => 'I',
      'installTime' => 'T',
      'jobID' => 'I',
      'licensedProductionRuns' => 'I',
      'materialMultiplier' => 'N',
      'outputFlag' => 'I',
      'outputLocationID' => 'I',
      'outputTypeID' => 'I',
      'ownerID' => 'I',
      'pauseProductionTime' => 'T',
      'runs' => 'I',
      'timeMultiplier' => 'N'
    ) ,
    'xpath' => '//row'
  ) ,
  'MarketOrders' => array(
    'types' => array(
      'accountKey' => 'I',
      'bid' => 'I',
      'charID' => 'I',
      'duration' => 'I',
      'escrow' => 'N',
      'issued' => 'T',
      'minVolume' => 'I',
      'orderID' => 'I',
      'orderState' => 'I',
      'ownerID' => 'I',
      'price' => 'N',
      'range' => 'I',
      'stationID' => 'I',
      'typeID' => 'I',
      'volEntered' => 'I',
      'volRemaining' => 'I'
    ) ,
    'xpath' => '//row'
  )
);
$postdata = array('userID' => $userid, 'apiKey' => $apikey,
  'characterID' => $charid
);
foreach($apis as $api => $settings) {
  try {
    // Should we wait to get API data
    $xml = dontWait($api, $ownerid) ? getAPIinfo($api, YAPEAL_API_CORP, $postdata) : false;
    if ($xml instanceof SimpleXMLElement) {
      // Now update cacheduntil time
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil
      );
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      $datum = $xml->xpath($settings['xpath']);
      if (count($datum) > 0) {
        $extras = array('ownerID' => $ownerid);
        if (YAPEAL_TRACE &&
          (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
          $mess = 'CORP: Before multiUpsert for ' . $api . ' in ' . basename(__FILE__);
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        multipleUpsertAttributes($datum, $settings['types'], $api, DSN_CORP_WRITER, $extras);
      }; // if $datum>0
    }; // if $xml instanceof SimpleXMLElement
  }
  catch(EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
    $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  }
  catch(EVE_API_Error_Exception $e) {
    // Some error codes give us a new time to retry after that should be
    // used for cached until time.
    switch ($e->getCode()) {
      case 116: // Industry jobs already downloaded

      case 117: // Market orders already downloaded
        $cuntil = substr($e->getMessage() , -20, 19);
        $data = array( 'tableName' => $api, 'ownerID' => $ownerid,
          'cachedUntil' => $cuntil
        );
        upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      break;
      default:
        // Do nothing but logging by default
    }; // case $e->getCode()
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
$apis = array(
  'MemberTracking' => array(
    'types' => array(
      'base' => 'C',
      'baseID' => 'I',
      'characterID' => 'I',
      'grantableRoles' => 'C',
      'location' => 'C',
      'locationID' => 'I',
      'logoffDateTime' => 'T',
      'logonDateTime' => 'T',
      'name' => 'C',
      'ownerID' => 'I',
      'roles' => 'C',
      'shipType' => 'C',
      'shipTypeID' => 'I',
      'startDateTime' => 'T',
      'title' => 'C'
    ) ,
    'xpath' => '//row',
    'check' => 'characterID'
  ) ,
  'StarbaseList' => array(
    'types' => array(
      'itemID' => 'I',
      'locationID' => 'I',
      'moonID' => 'I',
      'onlineTimestamp' => 'T',
      'ownerID' => 'I',
      'state' => 'I',
      'stateTimestamp' => 'T',
      'typeID' => 'I'
    ) ,
    'xpath' => '//row',
    'check' => 'itemID'
  )
);
$postdata = array('userID' => $userid, 'apiKey' => $apikey,
  'characterID' => $charid
);
foreach($apis as $api => $settings) {
  try {
    // Should we wait to get API data
    $xml = dontWait($api, $ownerid) ? getAPIinfo($api, YAPEAL_API_CORP, $postdata) : false;
    if ($xml instanceof SimpleXMLElement) {
      // Now update cacheduntil time
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil
      );
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before xpath for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      $datum = $xml->xpath($settings['xpath']);
      if (count($datum) > 0) {
        $sql = 'select ' . $settings['check'];
        $sql.= ' from ' . $api;
        $sql.= ' where ownerid=' . $ownerid;
        if (YAPEAL_TRACE &&
          (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
          $mess = 'CORP: Before select check for ' . $api . ' in ' . basename(__FILE__);
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        $result = $con->getAll($sql);
        $checking = array();
        // Change assoc array of records from DB into simple array
        foreach($result as $k => $v) {
          $checking[] = (string)$v[$settings['check']];
        };
        //$check=$xml->xpath($settings['xpath'].'[@'.$settings['check'].']');
        // Extract attribute into simple array
        $current = array();
        foreach($datum as $data) {
          foreach($data->attributes() as $k => $v) {
            if ($k == $settings['check']) {
              $current[] = (string)$v;
            }; // if $k==...

          }; // foreach $data[$settings['check']]...

        }; // foreach $datum...
        $delete = array_diff($checking, $current);
        if (!empty($delete)) {
          foreach($delete as $k => $v) {
            $sql = 'delete from ' . $api;
            $sql.= ' where ownerid=' . $ownerid;
            $sql.= ' and ' . $settings['check'] . '=' . $v;
            if (YAPEAL_TRACE &&
              (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
              $mess = 'CORP: Before delete losers for ' . $api . ' in ' . basename(__FILE__);
              print_on_command($mess);
              $yapealTracing.= $mess . PHP_EOL;
            }; // if YAPEAL_TRACE&&...
            $con->Execute($sql);
          }; // foreach $delete...

        }; // if !empty $delete
        $extras = array('ownerID' => $ownerid);
        if (YAPEAL_TRACE &&
          (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
          $mess = 'CORP: Before multiUpsert for ' . $api . ' in ' . basename(__FILE__);
          print_on_command($mess);
          $yapealTracing.= $mess . PHP_EOL;
        }; // if YAPEAL_TRACE&&...
        multipleUpsertAttributes($datum, $settings['types'], $api, DSN_CORP_WRITER, $extras);
      }; // if $datum>0

    }; // if $xml instanceof SimpleXMLElement

  }
  catch(EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
    $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  }
  catch(EVE_API_Error_Exception $e) {
    // Some error codes give us a new time to retry after that should be
    // used for cached until time.
    switch ($e->getCode()) {
      case 116: // Industry jobs already downloaded
      case 117: // Market orders already downloaded
        $cuntil = substr($e->getMessage() , -20, 19);
        $data = array('tableName' => $api, 'ownerID' => $ownerid,
          'cachedUntil' => $cuntil
        );
        upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      break;
      default:
        // Do nothing but logging by default

    }; // case $e->getCode()
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
$apis = array(
  'WalletJournal' => array(
    'beforeid' => 'beforeRefID',
    'types' => array(
      'accountKey' => 'I',
      'amount' => 'N',
      'argID1' => 'I',
      'argName1' => 'C',
      'balance' => 'N',
      'date' => 'T',
      'ownerID' => 'I',
      'ownerID1' => 'I',
      'ownerID2' => 'I',
      'ownerName1' => 'C',
      'ownerName2' => 'C',
      'reason' => 'X',
      'refID' => 'I',
      'refTypeID' => 'I'
    ) ,
    'walkdt' => 'date',
    'walkid' => 'refID',
    'xpath' => '//row'
  ) ,
  'WalletTransactions' => array(
    'beforeid' => 'beforeTransID',
    'types' => array(
      'accountKey' => 'I',
      'clientID' => 'I',
      'clientName' => 'C',
      'ownerID' => 'I',
      'price' => 'N',
      'quantity' => 'I',
      'stationID' => 'I',
      'stationName' => 'C',
      'transactionDateTime' => 'T',
      'transactionFor' => 'C',
      'transactionID' => 'I',
      'transactionType' => 'C',
      'typeID' => 'I',
      'typeName' => 'C'
    ) ,
    'walkdt' => 'transactionDateTime',
    'walkid' => 'transactionID',
    'xpath' => '//row'
  )
);
$accounts = array(1000, 1001, 1002, 1003, 1004, 1005, 1006);
foreach($apis as $api => $settings) {
  foreach($accounts as $account) {
    $postdata = array('userID' => $userid, 'apiKey' => $apikey,
      'characterID' => $charid, 'accountKey' => $account,
      (string)$settings['beforeid'] => 0
    );
    try {
      // Continue to next if we're not ready at this time
      if (false === dontWait($api . $account, $ownerid)) {
        continue;
      }; // if false===dontWait ...
      do {
        $xml = getAPIinfo($api, YAPEAL_API_CORP, $postdata);
        if ($xml instanceof SimpleXMLElement) {
          // Now update cacheduntil time.
          $cuntil = (string)$xml->cachedUntil[0];
          // Note tablename
          $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
            'cachedUntil' => $cuntil
          );
          upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
          $datum = $xml->xpath($settings['xpath']);
          $cnt = count($datum);
          $extras = array('ownerID' => $ownerid, 'accountKey' => $account
          );
          if ($cnt > 0) {
            if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
              $mess = 'CORP: Before multiUpsert for ' . $api . ' in ' . basename(__FILE__);
              print_on_command($mess);
              $yapealTracing.= $mess . PHP_EOL;
            }; // if YAPEAL_TRACE&&...
            multipleUpsertAttributes($datum, $settings['types'], $api, DSN_CORP_WRITER, $extras);
          } else {
            break; // Leave while if no records returned

          }; // else $cnt>0
          // Get date/time of last record
          $lastDT = strtotime($datum[$cnt - 1][$settings['walkdt']] . ' +0000');
          $oldest = strtotime('7 days ago');
          // If last record is less than a week old we might be able to
          // continue walking backwards thru records
          if ($oldest < $lastDT) {
            $walkid = (int)$datum[$cnt - 1][$settings['walkid']];
            $postdata[(string)$settings['beforeid']] = $walkid;
            // Pause to let CCP figure out we got last 1000 records before
            // trying to getting another batch :P
            sleep(2);
          } else {
            // Leave while loop if we can't walk back anymore.
            $cnt = 0;
          }; // if $oldest<$lastDT

        }; // if $xml instanceof SimpleXMLElement

      }
      while ($cnt == 1000 && isset($postdata[$settings['beforeid']]));
      // API returning wrong cache until time need to set cacheduntil to
      // 60 minutes
      $cuntil = gmdate('Y-m-d H:i:s', strtotime('60 minutes'));
      $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil
      );
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    }
    catch(EVE_API_Error_Exception $e) {
      // Some error codes give us a new time to retry after that should be
      // used for cached until time.
      switch ($e->getCode()) {
        case 101: // Wallet exhausted
        case 103: // Already returned one week of data
          $cuntil = substr($e->getMessage() , -20, 19);
          $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
            'cachedUntil' => $cuntil
          );
          upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
        break;
        default:
          // Do nothing but logging by default
      }; // case $e->getCode()
  }
  catch(EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
    $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
    $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
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
}; // foreach $accounts ...

}; // foreach $apis ...
/* Starbase Detail */
// Need to get list of POSes first
$api = 'StarbaseList';
try {
  $sql = 'select itemid from ' . $api;
  $sql.= ' where moonid!=0';
  $sql.= ' and ownerid=' . $ownerid;
  if (YAPEAL_TRACE &&
    (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
    $mess = 'CORP: Before getALL $POSList for ' . $api . ' in ' . basename(__FILE__);
    print_on_command($mess);
    $yapealTracing.= $mess . PHP_EOL;
  }; // if YAPEAL_TRACE&&...
  $datum = $con->GetAll($sql);
  $POSlist = array();
  if (count($datum) > 0) {
    foreach($datum as $k) {
      $POSlist[] = (int)$k['itemid'];
    }; // foreach $datum

  }; // if count $datum

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
// TODO: Whole table structure needs to be changed at some point here to make
// it better organized.
/*$api='StarbaseDetail';
if (!empty($POSlist)) {
foreach ($POSlist as $pos) {
// Build http query
$postdata=array('userID'=>$userid,'apiKey'=>$apikey,
'characterID'=>$charid,'itemID'=>$pos);
$types=array(
'allowalliancemembers'=>'L','allowcorporationmembers'=>'L',
'claimsovereignty'=>'L','deployflags'=>'I',
'itemID'=>'I','onaggressionenabled'=>'L',
'oncorporationwarenabled'=>'L','onstandingdropenabled'=>'L',
'onstandingdropstanding'=>'N','onstatusdropenabled'=>'L',
'onstatusdropstanding'=>'N','ownerID'=>'I',
'usageflags'=>'I'
);
try {
// Should we wait to get API data
$xml=dontWait($api.$pos,$ownerid)?getAPIinfo($api,YAPEAL_API_CORP,$postdata):false;
if ($xml instanceof SimpleXMLElement) {
// Now update cacheduntil time.
$cuntil=(string)$xml->cachedUntil[0];
// Note tablename
$data=array('tableName'=>$api.$pos,'ownerID'=>$ownerid,
'cachedUntil'=>$cuntil);
upsert($data,$cachetypes,'CachedUntil',DSN_UTIL_WRITER);
// Set the field types of query by name.
$data=array();
// Loop thru the first group of data.
$datum=$xml->result->generalSettings->children();
foreach ($datum as $k=>$v) {
$data[$k]=(string)$v;
};
// Loop thru the second group of data.
$datum=$xml->result->combatSettings->children();
foreach ($datum as $record=>$v) {
foreach ($v->attributes() as $index=>$value) {
$data[$record.$index]=(string)$value;
};
};
if (!array_key_exists('onstandingdropenabled',$data)) {
$data['onstandingdropenabled']=FALSE;
};
$data['ownerID']=$ownerid;
$data['itemid']=(int)$pos;
$result=upsert($data,$types,$api,DSN_CORP_WRITER);*/
/* Pos Fuel */
/*$dbTable='fuel';
// Set the field types of query by name.
$types=array('itemID'=>'I','typeID'=>'I',
'quantity'=>'I');
$datum=$xml->xpath('//result/rowset[@name="'.$dbTable.'"]/row');
if (count($datum)>0) {
foreach ($datum as $record) {
$data=array();
foreach ($record->attributes() as $k=>$v) {
$data[$k]=(string)$v;
};
$data['itemid']=$pos;
$dbTable=$dbTable;
$result=upsert($data,$types,$dbTable,DSN_CORP_WRITER);
};// foreach $datum
};// if $datum>0
};// if $xml instanceof SimpleXMLElement
}
catch (EVE_API_File_Exception $e) {
// Set cacheduntil to wait 5 minutes if we couldn't get XML from API
$cuntil=gmdate('Y-m-d H:i:s',strtotime('5 minutes'));
$data=array('tableName'=>$api.$pos,'ownerID'=>$ownerid,
'cachedUntil'=>$cuntil);
upsert($data,$cachetypes,'CachedUntil',DSN_UTIL_WRITER);
}
catch (EVE_API_Exception $e) {
// Do nothing use observers to log info
}
catch (ADODB_Exception $e) {
// Do nothing use observers to log info
}
};// foreach $POSlist
};// if !empty $POSlist ...*/
/* Asset List */
$api = 'AssetList';
try {
  // Now get API pull and re-build asset list
  $postdata = array('userID' => $userid, 'apiKey' => $apikey,
    'characterID' => $charid
  );
  // Should we wait to get API data
  $xml = dontWait($api, $ownerid) ? getAPIinfo($api, YAPEAL_API_CORP, $postdata) : false;
  if ($xml instanceof SimpleXMLElement) {
    // Now update cacheduntil time.
    $cuntil = (string)$xml->cachedUntil[0];
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => $cuntil
    );
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    $datum = $xml;
    if (count($datum) > 0) {
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before editAssets for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      // Call recursive function to modify XML.
      editAssets($datum);
      // Use generated owner node as root for tree.
      $corpHQ = getCorpHQ($ownerid, DSN_CORP_WRITER);
      $lft = $datum->result[0]['lft'];
      $rgt = $datum->result[0]['rgt'];
      $nodeData = array(
        'flag' => '0',
        'itemID' => $ownerid,
        'lft' => $lft,
        'locationID' => $corpHQ,
        'ownerID' => $ownerid,
        'quantity' => '1',
        'rgt' => $rgt,
        'singleton' => '0',
        'typeID' => 2
      );
      $sql = 'delete';
      $sql.= ' from ' . $api;
      $sql.= ' where ownerid=' . $ownerid;
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before delete for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      // Clear out old tree for this owner
      $con->Execute($sql);
      $types = array(
        'flag' => 'I',
        'itemID' => 'I',
        'lft' => 'I',
        'locationID' => 'I',
        'ownerID' => 'I',
        'quantity' => 'I',
        'rgt' => 'I',
        'singleton' => 'L',
        'typeID' => 'I'
      );
      // Insert the new owner's root node.
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before owner node insert for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      upsert($nodeData, $types, $api, DSN_CORP_WRITER);
      //Just need the rows from XML now
      $datum = $xml->xpath('//row');
      $extras = array(
        'locationID' => 0,
        'ownerID' => $ownerid
      );
      if (YAPEAL_TRACE &&
        (YAPEAL_TRACE_SECTION & YAPEAL_TRACE_CORP) == YAPEAL_TRACE_CORP) {
        $mess = 'CORP: Before multiUpsert for ' . $api . ' in ' . basename(__FILE__);
        print_on_command($mess);
        $yapealTracing.= $mess . PHP_EOL;
      }; // if YAPEAL_TRACE&&...
      multipleUpsertAttributes($datum, $types, $api, DSN_CORP_WRITER, $extras);
    }; // if count $datum ...

  }; // if $xml instanceof SimpleXMLElement

}
catch(EVE_API_File_Exception $e) {
  // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
  $cuntil = gmdate('Y-m-d H:i:s', strtotime('5 minutes'));
  $data = array('tableName' => $api, 'ownerID' => $ownerid,
    'cachedUntil' => $cuntil
  );
  upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
}
catch(EVE_API_Error_Exception $e) {
  // Some error codes give us a new time to retry after that should be
  // used for cached until time.
  switch ($e->getCode()) {
    case 115: // Assets already downloaded
      $cuntil = substr($e->getMessage() , -20, 19);
      $data = array('tableName' => $api, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil
      );
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    break;
    default:
      // Do nothing but logging by default

  }; // case $e->getCode()

}
catch(EVE_API_Exception $e) {
  // Do nothing use observers to log info
}
catch(ADODB_Exception $e) {
  elog('Uncaught ADOdb exception in '.basename(__FILE__), YAPEAL_WARNING_LOG);
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
?>
