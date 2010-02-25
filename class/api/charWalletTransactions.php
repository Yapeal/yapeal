<?php
/**
 * Contains WalletTransactions class.
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
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
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
 * Class used to fetch and store char WalletTransactions API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charWalletTransactions extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'WalletTransactions';
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
    $accounts = array(1000);
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
          $cacheName .= $this->characterID . $account . $beforeID;
          // Try to get XML from local cache first if we can.
          $xml = YapealApiRequests::getCachedXml($cacheName, YAPEAL_API_CHAR);
          if ($xml === FALSE) {
            $xml = YapealApiRequests::getAPIinfo($this->api, YAPEAL_API_CHAR,
              $postData, $this->proxy);
            if ($xml instanceof SimpleXMLElement) {
              // Get current API time and add an hour to it.
              $currentTime = strtotime((string)$xml->currentTime[0] . ' +0000');
              $ncu = gmdate('Y-m-d H:i:s', $currentTime + 3600);
              // Change XML cachedUntil to correct value.
              $xml->cachedUntil[0] = $ncu;
              // Store XML in local cache.
              YapealApiRequests::cacheXml($xml->asXML(), $cacheName,
                YAPEAL_API_CHAR);
            };// if $xml ...
          };// if $xml === FALSE ...
          if ($xml !== FALSE) {
            $this->xml[$account][] = $xml;
            $datum = $xml->xpath($this->xpath);
            $cnt = count($datum);
            if ($cnt > 0) {
              // Get date/time of last record
              $lastDT = strtotime($datum[$cnt - 1]['transactionDateTime'] . ' +0000');
              // If last record is less than a week old we might be able to
              // continue walking backwards through records.
              if ($oldest < $lastDT) {
                $beforeID = (string)$datum[$cnt - 1]['transactionID'];
                // Pause to let CCP figure out we got last 1000 records before
                // trying to getting another batch :P
                sleep(2);
              } else {
                // Leave while loop if we can't walk back anymore.
                break;
              }; // if $oldest<$lastDT
            } else {
              $mess = 'No records for ' . $tableName;
              trigger_error($mess, E_USER_NOTICE);
              break;
            }
          } else {
            $mess = 'No XML found for ' . $tableName;
            trigger_error($mess, E_USER_NOTICE);
            continue 2;// while, foreach $accounts
          };// else $xml !== FALSE ...
        }
        catch (YapealApiErrorException $e) {
          if ($this->handleApiRetry($e)) {
            continue 2;
          } else if ($this->handleApiError($e)) {
            return FALSE;
          };
          continue 2;
        }
        catch (YapealApiFileException $e) {
          continue 2;
        }
        catch (ADODB_Exception $e) {
          continue 2;
        }
      } while ($cnt == 1000);
      ++$ret;
    };// foreach $accounts ...
    if ($ret == 1) {
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
    $accounts = array(1000);
    $ret = 0;
    $cuntil = '1970-01-01 00:00:01';
    $tableName = $this->tablePrefix . $this->api;
    if (empty($this->xml)) {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };// if empty $this->xml ...
    foreach ($accounts as $account) {
      if (empty($this->xml[$account])) {
        $mess = 'There was no XML data to store for ' . $tableName . $account;
        trigger_error($mess, E_USER_NOTICE);
        continue;
      };// if empty $this->xml[$account] ...
      foreach ($this->xml[$account] as $xml) {
        $cuntil = (string)$xml->cachedUntil[0];
        $xml = $xml->xpath($this->xpath);
        $cnt = count($xml);
        if ($cnt > 0) {
          try {
            $extras = array('ownerID' => $this->characterID,
              'accountKey' => $account);
            for ($i = 0, $grp = (int)ceil($cnt / YAPEAL_MAX_UPSERT),$pos = 0;
                $i < $grp;++$i, $pos += YAPEAL_MAX_UPSERT) {
              $group = array_slice($xml, $pos, YAPEAL_MAX_UPSERT, TRUE);
              YapealDBConnection::multipleUpsertAttributes($group, $tableName,
                YAPEAL_DSN, $extras);
            };// for $i = 0...
          }
          catch (ADODB_Exception $e) {
            // Any failure to store XML on any account returns FALSE;
            continue 2;
          }
        } else {
        $mess = 'There was no XML data to store for ' . $tableName . $account;
        trigger_error($mess, E_USER_NOTICE);
        };// else count $datum ...
      };// foreach $this->xml[$account] ...
      ++$ret;
    };// foreach $accounts ...
    try {
      $data = array( 'tableName' => $tableName,
        'ownerID' => $this->characterID, 'cachedUntil' => $cuntil
      );
      YapealDBConnection::upsert($data,
        YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      // Already logged nothing to do here.
    }
    // If we stored everything correctly return TRUE.
    if ($ret == 1) {
      return TRUE;
    };
    return FALSE;
  }// function apiStore
  /**
   * Handles some Eve API error codes in special ways.
   *
   * @param object $e Eve API exception returned.
   *
   * @return bool Returns TRUE if handled the error else FALSE.
   */
  private function handleApiRetry($e) {
    try {
      switch ($e->getCode()) {
        // All of these codes give a new cachedUntil time to use.
        case 101: // Wallet exhausted: retry after {0}.
        case 103: // Already returned one week of data: retry after {0}.
        case 115: // Assets already downloaded: retry after {0}.
        case 116: // Industry jobs already downloaded: retry after {0}.
        case 117: // Market orders already downloaded. retry after {0}.
        case 119: // Kills exhausted: retry after {0}.
          $cuntil = substr($e->getMessage() , -21, 20);
          $data = array( 'tableName' => $this->tablePrefix . $this->api,
            'ownerID' => $this->characterID, 'cachedUntil' => $cuntil
          );
            YapealDBConnection::upsert($data,
              YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
          return TRUE;
          break;
      };// switch $code ...
    }
    catch (ADODB_Exception $e) {}
    return FALSE;
  }// function handleApiRetry
}
?>
