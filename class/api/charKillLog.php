<?php
/**
 * Contains killLog class.
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
 * Class used to fetch and store char KillLog API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charKillLog extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'KillLog';
  /**
   * @var array Hold an array of the XML return from API.
   */
  protected $xml = array();
  /**
   * @var array Hold an array of the XML return from API.
   */
  protected $killList = array();
  /**
   * @var array Hold an array of the XML return from API.
   */
  protected $victimList = array();
  /**
   * @var array Hold an array of the XML return from API.
   */
  protected $attackersList = array();
  /**
   * @var array Hold an array of the XML return from API.
   */
  protected $itemsList = array();
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//rowset[@name="kills"]/row';
  /**
   * Used to get an item from Eve API.
   *
   * @return boolean Returns TRUE if item received.
   */
  public function apiFetch() {
    $ret = 0;
    $xml = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    $oldest = strtotime('7 days ago');
    $beforeID = 0;
    do {
      $postData = array('apiKey' => $this->apiKey, 'beforeKillID' => $beforeID,
        'characterID' => $this->characterID, 'userID' => $this->userID
      );
      $cnt = 0;
      try {
        // Build base part of cache file name.
        $cacheName = $this->serverName . $tableName;
        $cacheName .= $this->characterID . $beforeID;
        // Try to get XML from local cache first if we can.
        $xml = YapealApiRequests::getCachedXml($cacheName, YAPEAL_API_CHAR);
        if ($xml === FALSE) {
          $xml = YapealApiRequests::getAPIinfo($this->api, YAPEAL_API_CHAR,
            $postData, $this->proxy);
          if ($xml instanceof SimpleXMLElement) {
            YapealApiRequests::cacheXml($xml->asXML(), $cacheName,
              YAPEAL_API_CHAR);
          };// if $xml ...
        };// if $xml === FALSE ...
        if ($xml !== FALSE) {
          $this->xml[] = $xml;
          $datum = $xml->xpath($this->xpath);
          $cnt = count($datum);
          if ($cnt > 0) {
            // Get date/time of last record
            $lastDT = strtotime($datum[$cnt - 1]['killTime'] . ' +0000');
            // If last record is less than a week old we might be able to
            // continue walking backwards through records.
            if ($oldest < $lastDT) {
              $beforeID = (string)$datum[$cnt - 1]['killID'];
              // Pause to let CCP figure out we got last group of records before
              // trying to getting another batch :P
              sleep(2);
            } else {
              // Leave while loop if we can't walk back anymore.
              break;
            };// else $oldest<$lastDT
          } else {
            $mess = 'No records for ' . $tableName;
            trigger_error($mess, E_USER_NOTICE);
            break;
          }
        } else {
          $mess = 'No XML found for ' . $tableName;
          trigger_error($mess, E_USER_NOTICE);
          continue;
        };// else $xml !== FALSE ...
      }
      catch (YapealApiErrorException $e) {
        // Any API errors that need to be handled in some way are handled in this
        // function.
        $this->handleApiError($e);
        return FALSE;
      }
      catch (YapealApiFileException $e) {
        return FALSE;
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
    } while ($cnt == 25);
    ++$ret;
    if ($ret == 1) {
      return TRUE;
    };
    return FALSE;
  }// function apiFetch
  /**
   * Used to store XML to KillLog tables.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    $ret = 0;
    $cuntil = '1970-01-01 00:00:01';
    $tableName = $this->tablePrefix . $this->api;
    if (empty($this->xml)) {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };// if empty $this->xml ...
    foreach ($this->xml as $xml) {
      $kills = $xml->xpath('//rowset[@name="kills"]/row');
      $cnt = count($kills);
      if ($cnt > 0) {
        for ($i = 0; $i < $cnt; ++$i) {
          $kill = $kills[$i];
          $killID = $kill['killID'];
          $this->attackers($kill, $killID);
          $this->killLog($kill, $killID);
          $this->items($kill, $killID);
          $this->victim($kill, $killID);
          // Release memory as we go.
          unset($kills[$i]);
        };// for $i = 0...
        if (!empty($this->attackersList)) {
          $tableName = $this->tablePrefix . 'Attackers';
          $extras = array('finalBlow' => 0);
          try {
            YapealDBConnection::multipleUpsertAttributes($this->attackersList,
              $tableName, YAPEAL_DSN, $extras);
            ++$ret;
          }
          catch (ADODB_Exception $e) {
            // Just logging here.
          }
        };// if !empty $this->attackersList ...
        if (!empty($this->itemsList)) {
          $tableName = $this->tablePrefix . 'Items';
          try {
            YapealDBConnection::multipleUpsertAttributes($this->itemsList,
              $tableName, YAPEAL_DSN);
            ++$ret;
          }
          catch (ADODB_Exception $e) {
            // Just logging here.
          }
        };// if !empty $this->itemsList ...
        if (!empty($this->killList)) {
          $tableName = $this->tablePrefix . 'KillLog';
          try {
            YapealDBConnection::multipleUpsertAttributes($this->killList,
              $tableName, YAPEAL_DSN);
            ++$ret;
          }
          catch (ADODB_Exception $e) {
            // Just logging here.
          }
        };// if !empty $this->killList ...
        if (!empty($this->victimList)) {
          $tableName = $this->tablePrefix . 'Victim';
          try {
            YapealDBConnection::multipleUpsertAttributes($this->victimList,
              $tableName, YAPEAL_DSN);
            ++$ret;
          }
          catch (ADODB_Exception $e) {
            // Just logging here.
          }
        };// if !empty $this->victimList ...
      } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      };// else count $datum ...
    };// foreach $this->xml ...
    try {
      $tableName = $this->tablePrefix . $this->api;
      // Update CachedUntil time since we updated records and have new one.
      $cuntil = (string)$xml->cachedUntil[0];
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
    if ($ret == 4) {
      return TRUE;
    };
    return FALSE;
  }// function apiStore
  /**
   * Handles the attackers rowset.
   *
   * @param SimpleXMLElement $kill Current kill to extract items from.
   * @param integer $killID The Id for this kill.
   *
   * @return void
   */
  protected function attackers($kill, $killID) {
    $tableName = $this->tablePrefix . 'Attackers';
    $xml = simplexml_load_string($kill->rowset[0]->asXML());
    $data = $xml->xpath('//row');
    if (!empty($data)) {
      foreach ($data as $row) {
        $row->addAttribute('killID', $killID);
        $this->attackersList[] = simplexml_load_string($row->asXML());
      };
    };
  }//function attackers
  /**
   * Used to store XML to KillLog table.
   *
   * @param SimpleXMLElement $kill Current kill to extract items from.
   * @param integer $killID The Id for this kill.
   *
   * @return void
   */
  protected function killLog($kill, $killID) {
    $tableName = $this->tablePrefix . 'KillLog';
    $datum = simplexml_load_string($kill->asXML());
    if (!empty($datum)) {
      unset($datum->victim[0], $datum->rowset[1], $datum->rowset[0]);
      $this->killList[] = simplexml_load_string($datum->asXML());
    };
  }// function killLog
  /**
   * Handles the items rowsets.
   *
   * @param SimpleXMLElement $kill Current kill to extract items from.
   * @param integer $killID The Id for this kill.
   *
   * @return void
   */
  protected function items($kill, $killID) {
    $tableName = $this->tablePrefix . 'Items';
    $typeID = $kill->victim['shipTypeID'];
    // Walking the items and add nested set stuff.
    $rgt = $this->editItems($kill->rowset[1], $killID);
    $data = '<row flag="0" killID="' . $killID . '" lft="1" lvl="0" rgt="';
    $data .= $rgt . '" qtyDestroyed="1" qtyDropped="0" typeID="' . $typeID . '"/>';
    $root = new SimpleXMLElement($data);
    array_unshift($this->itemsList, $root);
  }// function items
  /**
   * Handles the victim element.
   *
   * @param SimpleXMLElement $kill Current kill to extract items from.
   * @param integer $killID The Id for this kill.
   *
   * @return void
   */
  protected function victim($kill, $killID) {
    $tableName = $this->tablePrefix . 'Attackers';
    $xml = simplexml_load_string($kill->asXML());
    $data = $xml->victim[0];
    if (!empty($data)) {
      $data->addAttribute('killID', $killID);
      $this->victimList[] = simplexml_load_string($data->asXML());
    };
  }// function victim
  /**
   * Navigates XML and adds lft and rgt attributes.
   *
   * Navigates XML using SimpleXML and adds lft and rgt attributes of Nested Set
   * for insertion into database.
   *
   * Original idea for function coded by Stephen.
   *
   * @author Stephen <stephenmg12@gmail.com>
   * @author Michael Cummings <mgcummings@yahoo.com>
   *
   * @param SimpleXMLElement $node Current element from tree.
   * @param integer $killID Id to be added to nodes.
   * @param integer $index Current index for lft/rgt counting.
   * @param integer $level Level of nesting.
   *
   * @return integer Current index for lft/rgt counting.
   */
  protected function editItems($node, $killID, $index = 2, $level = 0) {
    $nodeName = $node->getName();
    if ($nodeName == 'row') {
      $node->addAttribute('lft', $index++);
      $node->addAttribute('lvl', $level);
      $node->addAttribute('killID', $killID);
    } elseif ($nodeName == 'rowset') {
      ++$level;
    };// elseif $nodeName == 'rowset' ...
    if ($children = $node->children()) {
      foreach ($children as $child) {
        $index = $this->editItems($child, $killID, $index, $level);
      };// foreach children ...
    };
    if ($nodeName == 'row') {
      $node->addAttribute('rgt', $index++);
      $this->itemsList[] = simplexml_load_string($node->asXML());
    };
    return $index;
  }// function editItems
}
?>
