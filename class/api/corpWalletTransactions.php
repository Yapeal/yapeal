<?php
/**
 * Class used to fetch and store Corp WalletTransactions API.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
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
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Class used to fetch and store corp WalletTransactions API.
 *
 * @package Yapeal
 * @subpackage Api_corporation
 */
class corpWalletTransactions extends ACorporation {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'WalletTransactions';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('accountKey' => 'I', 'characterID' => 'I',
    'characterName' => 'C', 'clientID' => 'I', 'clientName' => 'C',
    'ownerID' => 'I', 'price' => 'N', 'quantity' => 'I', 'stationID' => 'I',
    'stationName' => 'C', 'transactionDateTime' => 'T', 'transactionFor' => 'C',
    'transactionID' => 'I', 'transactionType' => 'C', 'typeID' => 'I',
    'typeName' => 'C'
  );
  /**
   * @var array Hold an array of the XML return from API.
   */
  protected $xml = array();
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//row';
  /**
   * Used to get an item from Eve API.
   *
   * @return boolean Returns TRUE if item received.
   */
  public function apiFetch() {
    global $tracing;
    global $cachetypes;
    $accounts = array(1000, 1001, 1002, 1003, 1004, 1005, 1006);
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    $oldest = strtotime('7 days ago');
    foreach ($accounts as $account) {
      $beforeID = 0;
      do {
        $cnt = 0;
        $postData = array('accountKey' => $account, 'apiKey' => $this->apiKey,
          'beforeTransID' => $beforeID, 'characterID' => $this->characterID,
          'userID' => $this->userID
        );
        $xml = FALSE;
        try {
          // Build base part of cache file name.
          $cacheName = $this->serverName . $tableName;
          $cacheName .= $this->corporationID . $account . $beforeID . '.xml';
          // Try to get XML from local cache first if we can.
          $mess = 'getCachedXml for ' . $cacheName;
          $mess .= ' in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
          $xml = YapealApiRequests::getCachedXml($cacheName, YAPEAL_API_CORP);
          if ($xml === FALSE) {
            $mess = 'getAPIinfo for ' . $this->api;
            $mess .= ' in ' . __FILE__;
            $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
            $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
            $xml = YapealApiRequests::getAPIinfo($this->api, YAPEAL_API_CORP,
              $postData);
            if ($xml instanceof SimpleXMLElement) {
              // Get current API time and add an hour to it.
              $currentTime = strtotime((string)$xml->currentTime[0] . ' +0000');
              $ncu = gmdate('Y-m-d H:i:s', $currentTime + 3600);
              // Change XML cachedUntil to correct value.
              $xml->cachedUntil[0] = $ncu;
              // Store XML in local cache.
              YapealApiRequests::cacheXml($xml->asXML(), $cacheName,
                YAPEAL_API_CORP);
            };// if $xml ...
          };// if $xml === FALSE ...
          if ($xml !== FALSE) {
            print 'Storing XML' . PHP_EOL;
            $this->xml[$account][] = $xml;
            $datum = $xml->xpath($this->xpath);
            $cnt = count($datum);
            print 'Row count: ' .$cnt . PHP_EOL;
            if ($cnt > 0) {
              // Get date/time of last record
              $lastDT = strtotime($datum[$cnt - 1]['transactionDateTime'] . ' +0000');
              // If last record is less than a week old we might be able to
              // continue walking backwards through records.
              if ($oldest < $lastDT) {
                $beforeID = (int)$datum[$cnt - 1]['transactionID'];
                // Pause to let CCP figure out we got last 1000 records before
                // trying to getting another batch :P
                sleep(2);
              } else {
                // Leave while loop if we can't walk back anymore.
                break;
              }; // if $oldest<$lastDT
            } else {
              $mess = 'No records for ' . $tableName;
              $mess .= ' in ' . __FILE__;
              trigger_error($mess, E_USER_NOTICE);
              break;
            }
          } else {
            $mess = 'No XML found for ' . $tableName;
            $mess .= ' in ' . __FILE__;
            trigger_error($mess, E_USER_NOTICE);
            continue 2;
          };// else $xml !== FALSE ...
        }
        catch (YapealApiErrorException $e) {
          // Some error codes give us a new time to retry after that should be
          // used for cached until time.
          switch ($e->getCode()) {
            case 101: // Wallet exhausted.
            case 103: // Already returned one week of data.
              $cuntil = substr($e->getMessage() , -21, 20);
              print 'Error cachedUntil: ' . $cuntil . PHP_EOL;
              $data = array( 'tableName' => $tableName,
                'ownerID' => $this->corporationID, 'cachedUntil' => $cuntil
              );
              upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
                YAPEAL_DSN);
              break;
            case 211: // Login denied by account status.
              // The corporation's account isn't active no use trying any of the
              // other APIs.
              break 4;// switch, while, foreach $accounts, foreach $apis
            default:
              // Do nothing but logging by default
          };// switch $e->getCode()
          continue 2;// while, foreach $accounts
        }
        catch (YapealApiException $e) {
          continue 2;
        }
        catch (ADODB_Exception $e) {
          continue 2;
        }
      } while ($cnt == 1000);
      ++$ret;
    };// foreach $accounts ...
    if ($ret == 7) {
      return TRUE;
    };
    return FALSE;
  }// function apiFetch
  /**
   * Used to store XML to WalletJournal table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $accounts = array(1000, 1001, 1002, 1003, 1004, 1005, 1006);
    $ret = 0;
    $cuntil = '1970-01-01 00:00:01';
    $tableName = $this->tablePrefix . $this->api;
    if (empty($this->xml)) {
      $mess = 'There was no XML data to store for ' . $tableName;
      $mess .= ' in ' . __FILE__;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };// if empty $this->xml ...
    foreach ($accounts as $account) {
      if (empty($this->xml[$account])) {
        $mess = 'There was no XML data to store for ' . $tableName . $account;
        $mess .= ' in ' . __FILE__;
        trigger_error($mess, E_USER_NOTICE);
      };// if empty $this->xml[$account] ...
      foreach ($this->xml[$account] as $xml) {
        $mess = 'Xpath for ' . $tableName . $account;
        $mess .= ' in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        $datum = $xml->xpath($this->xpath);
        $cnt = count($datum);
        if ($cnt > 0) {
          try {
            $extras = array('ownerID' => $this->corporationID,
              'accountKey' => $account);
            $mess = 'multipleUpsertAttributes for ' . $tableName . $account;
            $mess .= ' in ' . __FILE__;
            $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
            $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
            multipleUpsertAttributes($datum, $this->types, $tableName, YAPEAL_DSN,
              $extras);
          }
          catch (ADODB_Exception $e) {
            // Any failure to store XML on any account returns FALSE;
            continue 2;
          }
          // This doesn't work until CCP fixes thier cachedUntil timer.
          // Now correcting the time in XML instead.
          $until = (string)$xml->cachedUntil[0];
          //if ($until > $cuntil) {
          //  $cuntil = $until;
          //};
        } else {
        $mess = 'There was no XML data to store for ' . $tableName . $account;
        $mess .= ' in ' . __FILE__;
        trigger_error($mess, E_USER_NOTICE);
        };// else count $datum ...
      };// foreach $this->xml[$account] ...
      ++$ret;
    };// foreach $accounts ...
    try {
      // Update CachedUntil time since we updated records and have new one.
      // API returning wrong cache until time need to set cachedUntil to
      // 60 minutes
      //$cuntil = gmdate('Y-m-d H:i:s', strtotime('60 minutes'));
      // Now correcting the time in XML instead.
      $cuntil = (string)$xml->cachedUntil[0];
      $data = array( 'tableName' => $tableName,
        'ownerID' => $this->corporationID, 'cachedUntil' => $cuntil
      );
      $mess = 'Upsert for '. $tableName;
      $mess .= ' in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
      upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
        YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      // Already logged nothing to do here.
    }
    // If we stored everything correctly return TRUE.
    if ($ret == 7) {
      return TRUE;
    };
    return FALSE;
  }// function apiStore
}
?>
