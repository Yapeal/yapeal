<?php
/**
 * Class used to fetch and store Corp StarbaseDetail API.
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
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
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
 * Class used to fetch and store corp StarbaseDetail API.
 *
 * @package Yapeal
 * @subpackage Api_corporation
 */
class corpStarbaseDetail extends ACorporation {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'StarbaseDetail';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('itemID' => 'I', 'locationID' => 'I', 'moonID' => 'I',
      'onlineTimestamp' => 'T', 'ownerID' => 'I', 'state' => 'I',
      'stateTimestamp' => 'T', 'typeID' => 'I'
  );
  /**
   * @var array Hold an array of the data return from API.
   */
  protected $combatSettingsList = array();
  /**
   * @var array Hold an array of the data return from API.
   */
  protected $fuelList = array();
  /**
   * @var array Hold an array of the data return from API.
   */
  protected $generalSettingsList = array();
  /**
   * @var array Hold an array of the data return from API.
   */
  protected $starbaseDetailList = array();
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
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    $list = $this->posList();
    if (!empty($list)) {
      foreach ($list as $pos) {
        $posID = $pos['itemID'];
        $postData = array('apiKey' => $this->apiKey,
          'characterID' => $this->characterID, 'itemID' => $posID,
          'userID' => $this->userID
        );
        $xml = FALSE;
        try {
          // Build base part of cache file name.
          $cacheName = $this->serverName . $tableName;
          $cacheName .= $this->corporationID . $posID . '.xml';
          // Try to get XML from local cache first if we can.
          $mess = 'getCachedXml for ' . $cacheName;
          $mess .= ' in ' . basename(__FILE__);
          $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
          $xml = YapealApiRequests::getCachedXml($cacheName, YAPEAL_API_CORP);
          if ($xml === FALSE) {
            $mess = 'getAPIinfo for ' . $this->api;
            $mess .= ' in ' . basename(__FILE__);
            $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
            $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
            $xml = YapealApiRequests::getAPIinfo($this->api, YAPEAL_API_CORP,
              $postData, $this->proxy);
            if ($xml instanceof SimpleXMLElement) {
              YapealApiRequests::cacheXml($xml->asXML(), $cacheName,
                YAPEAL_API_CORP);
            };// if $xml ...
          };// if $xml === FALSE ...
          if ($xml !== FALSE) {
            $mess = 'Got XML for ' . $tableName . $posID;
            trigger_error($mess, E_USER_NOTICE);
            $this->xml[$posID] = $xml;
          } else {
            $mess = 'No XML found for ' . $tableName . $posID;
            trigger_error($mess, E_USER_NOTICE);
            continue;
          };// else $xml !== FALSE ...
        }
        catch (YapealApiErrorException $e) {
          // Any API errors that need to be handled in some way are handled in this
          // function.
          $this->handleApiError($e);
          continue;
        }
        catch (YapealApiFileException $e) {
          continue;
        }
        catch (ADODB_Exception $e) {
          continue;
        }
      }// foreach $list ...
    }// if !empty $list ...
  }// function apiFetch ...
  /**
   * Used to store XML to StarbaseDetail table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    if (empty($this->xml)) {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };// if empty $this->xml ...
    foreach ($this->xml as $posID => $xml) {
      if ($xml instanceof SimpleXMLElement) {
        if ($this->combatSettings($xml, $posID)) {
          ++$ret;
        };
        if ($this->fuel($xml, $posID)) {
          ++$ret;
        };
        if ($this->generalSettings($xml, $posID)) {
          ++$ret;
        };
        if ($this->starbaseDetail($xml, $posID)) {
          ++$ret;
        };
      };// if $this->xml ...
    };// foreach $this->xml ...
    $tableName = $this->tablePrefix . 'CombatSettings';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from ' . $tableName;
      $sql .= ' where ownerID=' . $this->corporationID;
      $mess = 'Before delete for ' . $tableName;
      $mess .= ' in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
    if (!empty($this->combatSettingsList)) {
      // Set the field types of query by name.
      $types = array('onAggressionEnabled' => 'I',
        'onCorporationWarEnabled' => 'I',
        'onStandingDropStanding' => 'I', 'onStatusDropEnabled' => 'I',
        'onStatusDropStanding' => 'I', 'ownerID' => 'I', 'posID' => 'I'
      );
      try {
        $mess = 'multipleUpsert for ' . $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        YapealDBConnection::multipleUpsert($this->combatSettingsList, $types,
          $tableName, YAPEAL_DSN);
        ++$ret;
      }
      catch (ADODB_Exception $e) {
        // Just logging here.
      }
    };// if !empty $this->combatSettingsList ...
    $tableName = $this->tablePrefix . 'Fuel';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from ' . $tableName;
      $sql .= ' where ownerID=' . $this->corporationID;
      $mess = 'Before delete for ' . $tableName;
      $mess .= ' in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
    if (!empty($this->fuelList)) {
      // Set the field types of query by name.
      $types = array('ownerID' => 'I', 'posID' => 'I', 'quantity' => 'I',
        'typeID' => 'I'
      );
      try {
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        YapealDBConnection::multipleUpsertAttributes($this->fuelList, $types,
          $tableName, YAPEAL_DSN);
        ++$ret;
      }
      catch (ADODB_Exception $e) {
        // Just logging here.
      }
    };// if !empty $this->fuelList ...
    $tableName = $this->tablePrefix . 'GeneralSettings';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from ' . $tableName;
      $sql .= ' where ownerID=' . $this->corporationID;
      $mess = 'Before delete for ' . $tableName;
      $mess .= ' in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
    if (!empty($this->generalSettingsList)) {
      // Set the field types of query by name.
      $types = array('allowAllianceMembers' => 'L',
        'allowCorporationMembers' => 'L', 'deployFlags' => 'I',
        'ownerID' => 'I', 'posID' => 'I', 'usageFlags' => 'I'
      );
      try {
        $mess = 'multipleUpsert for ' . $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        YapealDBConnection::multipleUpsert($this->generalSettingsList, $types,
          $tableName, YAPEAL_DSN);
        ++$ret;
      }
      catch (ADODB_Exception $e) {
        // Just logging here.
      }
    };// if !empty $this->generalSettingsList ...
    $tableName = $this->tablePrefix . 'StarbaseDetail';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from ' . $tableName;
      $sql .= ' where ownerID=' . $this->corporationID;
      $mess = 'Before delete for ' . $tableName;
      $mess .= ' in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
    if (!empty($this->starbaseDetailList)) {
      // Set the field types of query by name.
      $types = array('onlineTimestamp' => 'T', 'ownerID' => 'I', 'posID' => 'I',
        'state' => 'I', 'stateTimestamp' => 'T'
      );
      try {
        $mess = 'multipleUpsert for ' . $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        YapealDBConnection::multipleUpsert($this->starbaseDetailList, $types,
          $tableName, YAPEAL_DSN);
        ++$ret;
      }
      catch (ADODB_Exception $e) {
        // Just logging here.
      }
    };// if !empty $this->starbaseDetailList ...
    return $ret;
  }// function apiStore
  /**
   * Handles the combatSettings table.
   *
   * @param SimpleXMLElement $pos Current pos to extract settings from.
   * @param integer $posID The Id for this pos.
   *
   * @return void
   */
  protected function combatSettings($pos, $posID) {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CombatSettings';
    $datum = $pos->xpath('//combatSettings');
    if (count($datum) > 0) {
      $row = array('ownerID' => $this->corporationID, 'posID' => $posID);
      foreach ($datum[0]->children() as $cn => $child) {
        foreach ($child->attributes() as $k => $v) {
          // Combine element and attrubute names.
          $row[(string)$cn . ucfirst($k)] = (string)$v;
        };
      };// foreach $datum[0]->children() ...
      $this->combatSettingsList[] = $row;
      $ret = TRUE;
    } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function combatSettings
  /**
   * Handles the fuel rowset.
   *
   * @param SimpleXMLElement $pos Current pos to extract fuel from.
   * @param integer $posID The Id for this pos.
   *
   * @return void
   */
  protected function fuel($pos, $posID) {
    global $tracing;
    $tableName = $this->tablePrefix . 'Fuel';
    $data = $pos->xpath('//row');
    if (!empty($data)) {
      foreach ($data as $row) {
        $row->addAttribute('ownerID', $this->corporationID);
        $row->addAttribute('posID', $posID);
        $this->fuelList[] = simplexml_load_string($row->asXML());
      };
    };
  }// function fuel
  /**
   * Handles the generalSettings table.
   *
   * @param SimpleXMLElement $pos Current pos to extract settings from.
   * @param integer $posID The Id for this pos.
   *
   * @return void
   */
  protected function generalSettings($pos, $posID) {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'GeneralSettings';
    $datum = $pos->xpath('//generalSettings');
    if (count($datum) > 0) {
      $data = array('ownerID' => $this->corporationID, 'posID' => $posID);
      foreach ($datum[0]->children() as $k => $v) {
        $data[(string)$k] = (string)$v;
      };
      $this->generalSettingsList[] = $data;
      $ret = TRUE;
    } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function generalSettings
  /**
   * Get per corp list of starbases from corpStarbaseList.
   *
   * @return mixed List of itemIDs for this corp's POSes or FALSE.
   */
  protected function posList() {
    global $tracing;
    $tableName = $this->tablePrefix . 'StarbaseList';
    $list = array();
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'select itemID';
      $sql .= ' from ';
      $sql .= '`' . $tableName . '`';
      $sql .= ' where ownerID=' . $this->corporationID;
      $mess = 'Before GetAll ' . $this->api . ' for ' . $this->corporationID;
      $mess .= ' in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      $list = $con->GetAll($sql);
    }
    catch (ADODB_Exception $e) {
      // Something wrong with query return FALSE.
      return FALSE;
    }
    return $list;
  }// function posList
  /**
   * Handles the StarbaseDetail table.
   *
   * @param SimpleXMLElement $pos Current pos to extract details from.
   * @param integer $posID The Id for this pos.
   *
   * @return void
   */
  protected function starbaseDetail($pos, $posID) {
    global $tracing;
    $ret = 0;
    $tableName = $this->tablePrefix . 'StarbaseDetail';
    $nodes = array('onlineTimestamp', 'state', 'stateTimestamp');
    $row = array();
    foreach ($nodes as $node) {
      $datum = $pos->xpath('//' . $node);
      if (count($datum) > 0) {
        $row[$node] = (string)$datum[0];
      };
    };// foreach $nodes ...
    if (!empty($row)) {
      $row['ownerID'] = $this->corporationID;
      $row['posID'] = $posID;
      $this->starbaseDetailList[] = $row;
      $ret = TRUE;
    } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
    };// else count $datum ...
    return $ret;
  }// function starbaseDetail
}
?>
