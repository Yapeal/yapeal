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
 * @copyright Copyright (c) 2008, 2009, Michael Cummings
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
/****************************************************************************
* Per character API pulls
****************************************************************************/
/* Character Sheet */
$api = 'CharacterSheet';
$postdata = array('userID' => $userid, 'apiKey' => $apikey,
  'characterID' => $charid
);
// Set the field types of query by name.
$types = array(
  'balance' => 'N', 'bloodLine' => 'C', 'characterID' => 'I', 'cloneName' => 'C',
  'cloneSkillPoints' => 'I', 'corporationID' => 'I', 'corporationName' => 'C',
  'gender' => 'C', 'name' => 'C', 'race' => 'C',
  'charisma' => 'I', 'intelligence' => 'I', 'memory' => 'I', 'perception' => 'I',
  'willpower' => 'I'
);
$xml = NULL;
try {
  $mess = 'Before dontWait for ' . $api . ' in ' . $sectionFile;
  $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
  // Should we wait to get API data
  // No randomness here because other tables depend on this data
  if (dontWait($api, $ownerid, FALSE)) {
    // Set it so we wait a bit before trying again if something goes wrong.
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => YAPEAL_START_TIME);
    $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    $mess = 'Before getAPIinfo for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    // Grab the XML data
    $xml = getAPIinfo($api, YAPEAL_API_CHAR, $postdata);
  };// if dontWait ...
  if ($xml instanceof SimpleXMLElement) {
    $mess = 'Before clone for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    // Main table first
    $datum = clone $xml->result;
    $mess = 'Before delete children for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    // Get rid of child table stuff
    unset($datum->rowset, $datum->attributes);
    unset($datum->attributeEnhancers, $datum->currentTime);
    unset($datum->cachedUntil);
    $data = array();
    if (count($datum) > 0) {
      foreach($datum->children() as $k => $v) {
        $data[$k] = (string)$v;
      };
    }; // if $datum>0
    $mess = 'Before append attributes for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    // Should change this to bring in line with other tables.
    // Append attributes there's no need to have another table for them.
    /* attributes */
    $dbTable = 'attributes';
    $datum = $xml->result->attributes;
    if (count($datum) > 0) {
      foreach($datum[0]->children() as $k => $v) {
        $data[$k] = (string)$v;
      };
    }; // if $datum>0
    if (count($data)) {
      $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      upsert($data, $types, $api, DSN_CHAR_WRITER);
    };
    /* Certificates */
    $dbTable = 'certificates';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'certificateID' => 'I');
    $datum = $xml->xpath('//rowset[@name="' . $dbTable . '"]/row');
    if (count($datum) > 0) {
      $extras = array('ownerID' => $ownerid);
      $mess = 'Before upsert for ' . $dbTable . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      multipleUpsertAttributes($datum, $types, $dbTable, DSN_CHAR_WRITER, $extras);
    }; // if $datum>0
    /* Skills */
    $dbTable = 'skills';
    // Set the field types of query by name.
    $types = array(
      'ownerID' => 'I', 'level' => 'I', 'skillpoints' => 'I', 'typeID' => 'I',
      'unpublished' => 'L'
    );
    $datum = $xml->xpath('//rowset[@name="' . $dbTable . '"]/row');
    if (count($datum) > 0) {
      $extras = array(
        'ownerID' => $ownerid, 'level' => 0, 'unpublished' => 0
      );
      $mess = 'Before MultiUpsert for ' . $dbTable . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) && $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      multipleUpsertAttributes($datum, $types, $dbTable, DSN_CHAR_WRITER, $extras);
    }; // if $datum>0
    // Update CachedUntil time since we updated records and have new one.
    $cuntil = (string)$xml->cachedUntil[0];
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => $cuntil);
    $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  }; // if $xml instanceof SimpleXMLElement
}
catch(YapealApiFileException $e) {
  // Do nothing CachedUntil already set to retry in a bit.
}
catch(YapealApiException $e) {
  // Do nothing use observers to log info
}
catch(ADODB_Exception $e) {
  // Do nothing use observers to log info
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
      'installedInSolarSystemID' => 'I',
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
  $xml = NULL;
  try {
    $mess = 'Before dontWait for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    // Should we wait to get API data
    if (dontWait($api, $ownerid)) {
      // Set it so we wait a bit before trying again if something goes wrong.
      $data = array('tableName' => $api, 'ownerID' => $ownerid,
        'cachedUntil' => YAPEAL_START_TIME);
      $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
      $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      $mess = 'Before getAPIinfo for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_SERVER, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
      // Grab the XML data
      $xml = getAPIinfo($api, YAPEAL_API_CHAR, $postdata);
    } else {
      continue;
    };// else dontWait ...
    if ($xml instanceof SimpleXMLElement) {
      $datum = $xml->xpath($settings['xpath']);
      if (count($datum) > 0) {
        $extras = array('ownerID' => $ownerid);
        $mess = 'Before MultiUpsert for ' . $api . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $settings['types'], $api, DSN_CHAR_WRITER, $extras);
      }; // if $datum>0
      // Update CachedUntil time since we updated records and have new one.
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil);
      $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    }; // if $xml instanceof SimpleXMLElement
  }
  catch(YapealApiErrorException $e) {
    // Some error codes give us a new time to retry after that should be
    // used for cached until time.
    switch ($e->getCode()) {
      case 116: // Industry jobs already downloaded
      case 117: // Market orders already downloaded
        $cuntil = substr($e->getMessage() , -20, 19);
        $data = array('tableName' => $api, 'ownerID' => $ownerid,
          'cachedUntil' => $cuntil);
        upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
        break;
      default:
        // Do nothing but logging by default
    }; // case $e->getCode()
  }
  catch(YapealApiFileException $e) {
    // Do nothing CachedUntil already set to retry in a bit.
  }
  catch(YapealApiException $e) {
    // Do nothing use observers to log info
  }
  catch(ADODB_Exception $e) {
    // Do nothing use observers to log info
  }
}; // foreach $apis ...

/* WalletJournal */
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
      'reason' => 'C',
      'refID' => 'I',
      'refTypeID' => 'I'
    ) ,
    'walkdt' => 'date',
    'walkid' => 'refID',
    'xpath' => '//row'
  )
);
$accounts = array(1000);
foreach($apis as $api => $settings) {
  foreach($accounts as $account) {
    $postdata = array('userID' => $userid, 'apiKey' => $apikey,
      'characterID' => $charid, 'accountKey' => $account
    );
    $xml = NULL;
    try {
      $mess = 'Before dontWait for ' . $api . $account . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      // Continue to next if we're not ready at this time
      if (dontWait($api . $account, $ownerid)) {
        // Set it so we wait a bit before trying again if something goes wrong.
        $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
          'cachedUntil' => YAPEAL_START_TIME);
        $mess = 'Before upsert for ' . $api . $account . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
        upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      } else {
        continue;
      };// else dontWait ...
      do {
        $mess = 'Before getAPIinfo for ' . $api . $account . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        // Grab the XML data
        $xml = getAPIinfo($api, YAPEAL_API_CHAR, $postdata);
        if ($xml instanceof SimpleXMLElement) {
          $mess = 'Before xpath for ' . $api . ' in ' . $sectionFile;
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          $datum = $xml->xpath($settings['xpath']);
          $cnt = count($datum);
          $extras = array('accountKey' => $account, 'ownerID' => $ownerid);
          if ($cnt > 0) {
            $mess = 'Before multiUpsert for ' . $api . $account . ' in ' . $sectionFile;
            $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
            $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
            multipleUpsertAttributes($datum, $settings['types'], $api,
              DSN_CHAR_WRITER, $extras);
          } else {
            break; // Leave while if no records returned
          }; // else $cnt>0
          // Get date/time of last record
          $lastDT = strtotime($datum[$cnt - 1][$settings['walkdt']] . ' +0000');
          $oldest = strtotime('7 days ago');
          // If last record is less than a week old we may be able to
          // continue walking backwards thru records
          if ($oldest < $lastDT) {
            $walkid = (int)$datum[$cnt - 1][$settings['walkid']];
            $postdata[(string)$settings['beforeid']] = $walkid;
            // Pause to let CCP figure out we got last 1000 records before
            // trying to getting another batch :P
            sleep(2);
          } else {
            // Leave while loop if we can't walk back anymore.
            break;
          }; // if $oldest<$lastDT
        }; // if $xml instanceof SimpleXMLElement
      } while ($cnt == 1000 && isset($postdata[$settings['beforeid']]));
      // Update CachedUntil time since we updated records and have new one.
      // API returning wrong cache until time need to set cacheduntil to
      // 60 minutes
      $cuntil = gmdate('Y-m-d H:i:s', strtotime('60 minutes'));
      //$cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil);
      $mess = 'Before upsert for ' . $api . $account . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    }
    catch(YapealApiErrorException $e) {
      // Some error codes give us a new time to retry after that should be
      // used for cached until time.
      switch ($e->getCode()) {
        case 101: // Wallet exhausted
        case 103: // Already returned one week of data
          $cuntil = substr($e->getMessage() , -20, 19);
          $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
            'cachedUntil' => $cuntil);
          upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
        break;
        default:
          // Do nothing but logging by default
      }; // case $e->getCode()
    }
    catch(YapealApiFileException $e) {
      // Do nothing CachedUntil already set to retry in a bit.
    }
    catch(YapealApiException $e) {
      // Do nothing use observers to log info
    }
    catch(ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
  }; // foreach $accounts ...
}; // foreach $apis ...

/* WalletTransactions */
$apis = array(
  'WalletTransactions' => array(
    'beforeid' => 'beforeTransID',
    'types' => array(
      'accountKey' => 'I',
      'characterID' => 'I',
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
$accounts = array(1000);
foreach($apis as $api => $settings) {
  foreach($accounts as $account) {
    $postdata = array('userID' => $userid, 'apiKey' => $apikey,
      'characterID' => $charid, 'accountKey' => $account
    );
    $xml = NULL;
    try {
      $mess = 'Before dontWait for ' . $api . $account . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      // Continue to next if we're not ready at this time
      if (dontWait($api . $account, $ownerid)) {
        // Set it so we wait a bit before trying again if something goes wrong.
        $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
          'cachedUntil' => YAPEAL_START_TIME);
        $mess = 'Before upsert for ' . $api . $account . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
        upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
      } else {
        continue;
      };// else dontWait ...
      do {
        $mess = 'Before getAPIinfo for ' . $api . $account . ' in ' . $sectionFile;
        $tracing->activeTrace(YAPEAL_TRACE_SERVER, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
        // Grab the XML data
        $xml = getAPIinfo($api, YAPEAL_API_CHAR, $postdata);
        if ($xml instanceof SimpleXMLElement) {
          $mess = 'Before xpath for ' . $api . ' in ' . $sectionFile;
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 3) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          $datum = $xml->xpath($settings['xpath']);
          $cnt = count($datum);
          $extras = array('accountKey' => $account, 'characterID' => $ownerid,
            'ownerID' => $ownerid
          );
          if ($cnt > 0) {
            $mess = 'Before multiUpsert for ' . $api . ' in ' . $sectionFile;
            $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
            $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
            multipleUpsertAttributes($datum, $settings['types'], $api, DSN_CHAR_WRITER, $extras);
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
            break;
          }; // if $oldest<$lastDT
        }; // if $xml instanceof SimpleXMLElement
      } while ($cnt == 1000 && isset($postdata[$settings['beforeid']]));
      // Update CachedUntil time since we updated records and have new one.
      // API returning wrong cache until time need to set cacheduntil to
      // 60 minutes
      $cuntil = gmdate('Y-m-d H:i:s', strtotime('60 minutes'));
      //$cuntil = (string)$xml->cachedUntil[0];
      $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil);
      $mess = 'Before upsert for ' . $api . $account . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    }
    catch(YapealApiErrorException $e) {
      // Some error codes give us a new time to retry after that should be
      // used for cached until time.
      switch ($e->getCode()) {
        case 101: // Wallet exhausted
        case 103: // Already returned one week of data
          $cuntil = substr($e->getMessage() , -20, 19);
          $data = array('tableName' => $api . $account, 'ownerID' => $ownerid,
            'cachedUntil' => $cuntil);
          upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
        break;
        default:
          // Do nothing but logging by default
      }; // case $e->getCode()
    }
    catch(YapealApiFileException $e) {
      // Do nothing CachedUntil already set to retry in a bit.
    }
    catch(YapealApiException $e) {
      // Do nothing use observers to log info
    }
    catch(ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
  }; // foreach $accounts ...
}; // foreach $apis ...
/* Asset List */
$api = 'AssetList';
$xml = NULL;
try {
  // Now get API pull and re-build asset list
  $postdata = array('userID' => $userid, 'apiKey' => $apikey,
    'characterID' => $charid);
  $mess = 'Before dontWait for ' . $api . ' in ' . $sectionFile;
  $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
  // Should we wait to get API data
  if (dontWait($api, $ownerid)) {
    // Set it so we wait a bit before trying again if something goes wrong.
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => YAPEAL_START_TIME);
    $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    $mess = 'Before getAPIinfo for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_SERVER, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_SERVER, $mess);
    // Grab the XML data
    $xml = getAPIinfo($api, YAPEAL_API_CHAR, $postdata);
  };// if dontWait ...
  if ($xml instanceof SimpleXMLElement) {
    $datum = $xml;
    if (count($datum) > 0) {
      $mess = 'Before editAssets for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      // Call recursive function to modify XML.
      editAssets($datum);
      // Use generated owner node as root for tree.
      $chartype = getCharBloodLine($ownerid, DSN_CHAR_WRITER);
      $charHQ = getBloodLineHQ($chartype);
      $typeID = getBloodLineTypeID($chartype);
      $lft = $datum->result[0]['lft'];
      $rgt = $datum->result[0]['rgt'];
      $nodeData = array('flag' => '0', 'itemID' => $ownerid, 'lft' => $lft,
        'locationID' => $charHQ, 'ownerID' => $ownerid, 'quantity' => '1',
        'rgt' => $rgt, 'singleton' => '0', 'typeID' => $typeID
      );
      // Clear out old tree for this owner.
      $sql = 'delete from ' . $api;
      $sql.= ' where ownerid=' . $ownerid;
      $mess = 'Before delete for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      $con->Execute($sql);
      $types = array('flag' => 'I', 'itemID' => 'I', 'lft' => 'I',
        'locationID' => 'I', 'ownerID' => 'I', 'quantity' => 'I', 'rgt' => 'I',
        'singleton' => 'L', 'typeID' => 'I'
      );
      $mess = 'Before upsert owner node for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      // Insert the new owner's root node.
      upsert($nodeData, $types, $api, DSN_CHAR_WRITER);
      // Just need the rows from XML now
      $datum = $xml->xpath('//row');
      $extras = array('locationID' => 0, 'ownerID' => $ownerid);
      $mess = 'Before multiUpsert for ' . $api . ' in ' . $sectionFile;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      multipleUpsertAttributes($datum, $types, $api, DSN_CHAR_WRITER, $extras);
    }; // if count $datum ...
    // Update CachedUntil time since we updated records and have new one.
    $cuntil = (string)$xml->cachedUntil[0];
    $data = array('tableName' => $api, 'ownerID' => $ownerid,
      'cachedUntil' => $cuntil);
    $mess = 'Before upsert for ' . $api . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
  }; // if $xml instanceof SimpleXMLElement
}
catch(YapealApiErrorException $e) {
  // Some error codes give us a new time to retry after that should be
  // used for cached until time.
  switch ($e->getCode()) {
    case 115: // Assets already downloaded
      $cuntil = substr($e->getMessage() , -20, 19);
      $data = array('tableName' => $api, 'ownerID' => $ownerid,
        'cachedUntil' => $cuntil);
      upsert($data, $cachetypes, 'CachedUntil', DSN_UTIL_WRITER);
    break;
    default:
      // Do nothing but logging by default
  }; // case $e->getCode()
}
catch(YapealApiFileException $e) {
  // Do nothing CachedUntil already set to retry in a bit.
}
catch(YapealApiException $e) {
  // Do nothing use observers to log info
}
catch(ADODB_Exception $e) {
  // Do nothing use observers to log info
}
?>
