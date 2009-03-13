<?php
/**
 * Class used to fetch and store CorporationSheet API.
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
 * Class used to fetch and store CorporationSheet API.
 *
 * @package Yapeal
 * @subpackage Api_corporation
 */
class corpCorporationSheet  extends ACorporation {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'CorporationSheet';
  /**
   * Used to store XML to CorporationSheet tables.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      if ($this->corpSheet()) {
        ++$ret;
      };
      if ($this->divisions()) {
        ++$ret;
      };
      if ($this->walletDivisions()) {
        ++$ret;
      };
      if ($this->logo()) {
        ++$ret;
      };
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
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
    };// if $this->xml ...
    if ($ret == 4) {
      return TRUE;
    } else {
      return FALSE;
    };
  }// function apiStore()
  /**
   * Used to store XML to main CorporationSheet table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corpSheet() {
    global $tracing;
    $types = array(
      'allianceID' => 'I', 'allianceName' => 'C', 'ceoID' => 'I', 'ceoName' => 'C',
      'corporationID' => 'I', 'corporationName' => 'C', 'description' => 'X',
      'memberCount' => 'I', 'memberLimit' => 'I', 'shares' => 'I',
      'stationID' => 'I', 'stationName' => 'C', 'taxRate' => 'N', 'ticker' => 'C',
      'url' => 'C'
    );
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    $mess = 'Clone for ' . $tableName . ' in ' . __FILE__;
    $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
    $datum = clone $this->xml->result[0]->children();
    // Get rid of child table stuff
    $mess = 'Delete children for ' . $tableName;
    $mess .= ' in ' . __FILE__;
    $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
    unset($datum->rowset[1], $datum->rowset[0], $datum->logo);
    if (count($datum) > 0) {
      $data = array('allianceName' => '');
      foreach ($datum as $k => $v) {
        $data[$k] = (string)$v;
      };
      try {
        $mess = 'Upsert for ' . $tableName;
        $mess .= ' in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        upsert($data, $types, $tableName, YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    $mess .= ' in ' . __FILE__;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function characterSheet
  /**
   * Used to store XML to CorporationSheet's divisions table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function divisions() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Divisions';
    // Set the field types of query by name.
    $types = array('accountKey' => 'I', 'description' => 'C', 'ownerID' => 'I');
    $datum = $this->xml->xpath('//rowset[@name="divisions"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->corporationID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
          $extras);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    $mess .= ' in ' . __FILE__;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function division
  /**
   * Used to store XML to CorporationSheet's logo table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function logo() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Logo';
    $types = array('color1' => 'I', 'color2' => 'I', 'color3' => 'I',
      'graphicID' => 'I', 'ownerID' => 'I', 'shape1' => 'I', 'shape2' => 'I',
      'shape3' => 'I');
    $datum = $this->xml->xpath('//logo');
    if (count($datum) > 0) {
      $data = array('ownerID' => $this->corporationID);
      foreach ($datum[0]->children() as $k => $v) {
        $data[$k] = (string)$v;
      };
      try {
        $mess = 'Upsert for ' . $tableName;
        $mess .= ' in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        upsert($data, $types, $tableName, YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    $mess .= ' in ' . __FILE__;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function logo
  /**
   * Used to store XML to CorporationSheet's walletDivisions table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function walletDivisions() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'WalletDivisions';
    // Set the field types of query by name.
    $types = array('accountKey' => 'I', 'description' => 'C', 'ownerID' => 'I');
    $datum = $this->xml->xpath('//rowset[@name="walletDivisions"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->corporationID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
          $extras);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    $mess .= ' in ' . __FILE__;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function division
}
?>
