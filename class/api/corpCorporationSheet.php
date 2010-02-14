<?php
/**
 * Contains CorporationSheet class.
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
        YapealDBConnection::upsert($data,
          YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
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
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    $datum = clone $this->xml->result[0]->children();
    // Get rid of child table stuff
    unset($datum->rowset[1], $datum->rowset[0], $datum->logo);
    if (count($datum) > 0) {
      $data = array('allianceName' => '');
      foreach ($datum as $k => $v) {
        $data[$k] = (string)$v;
      };
      try {
        YapealDBConnection::upsert($data, $tableName, YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
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
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Divisions';
    $datum = $this->xml->xpath('//rowset[@name="divisions"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->corporationID);
        YapealDBConnection::multipleUpsertAttributes($datum, $tableName,
          YAPEAL_DSN, $extras);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
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
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Logo';
    $datum = $this->xml->xpath('//logo');
    if (count($datum) > 0) {
      $data = array('ownerID' => $this->corporationID);
      foreach ($datum[0]->children() as $k => $v) {
        $data[$k] = (string)$v;
      };
      try {
        YapealDBConnection::upsert($data, $tableName, YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
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
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'WalletDivisions';
    $datum = $this->xml->xpath('//rowset[@name="walletDivisions"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->corporationID);
        YapealDBConnection::multipleUpsertAttributes($datum, $tableName,
          YAPEAL_DSN, $extras);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function division
}
?>
