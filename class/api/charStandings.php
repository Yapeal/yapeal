<?php
/**
 * Contains Standings class.
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
 * @author     Simon Dellenbach <simon@dellenba.ch>
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
 * Class used to fetch and store char Standings API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charStandings extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'Standings';
  /**
   * Used to store XML to Standings tables.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      if ($this->standingsTo()) {
        ++$ret;
      };
      if ($this->standingsFrom()) {
        ++$ret;
      };
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
        $data = array( 'tableName' => $tableName,
          'ownerID' => $this->characterID, 'cachedUntil' => $cuntil
        );
        YapealDBConnection::upsert($data,
          YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        // Already logged nothing to do here.
      }
    };// if $this->xml ...
    if ($ret == 2) {
      return TRUE;
    } else {
      return FALSE;
    };
  }// function apiStore()
  /**
   * Used to store XML to the charStandingsTo* tables.
   *
   * @return Bool Return TRUE if store was successful for all sub-tables.
   */
  protected function standingsTo() {
    $retTo = 0;
    if ($this->standingsToCharacters()) {
      ++$retTo;
    };
    if ($this->standingsToCorporations()) {
      ++$retTo;
    };
    if ($retTo == 2) {
      return TRUE;
    } else {
      return FALSE;
    };
  }// function standingsTo
  /**
   * Used to store XML to the charStandingsFrom* tables.
   *
   * @return Bool Return TRUE if store was successful for all sub-tables.
   */
  protected function standingsFrom() {
    $retFrom = 0;
    if ($this->standingsFromAgents()) {
      ++$retFrom;
    };
    if ($this->standingsFromNPCCorporations()) {
      ++$retFrom;
    };
    if ($this->standingsFromFactions()) {
      ++$retFrom;
    };
    if ($retFrom == 3) {
      return TRUE;
    } else {
      return FALSE;
    };
  }// function standingsFrom
  /**
   * Used to store XML to charStandingsToCharacters table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsToCharacters() {
    $tableName = $this->tablePrefix . $this->api . 'ToCharacters';
    $currentPath = '//standingsTo/rowset[@name="characters"]/row';
    return $this->standingsToCommon($tableName, $currentPath);
  }// function standingsToCharacters
  /**
   * Used to store XML to charStandingsToCorporations table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsToCorporations() {
    $tableName = $this->tablePrefix . $this->api . 'ToCorporations';
    $currentPath = '//standingsTo/rowset[@name="corporations"]/row';
    return $this->standingsToCommon($tableName, $currentPath);
  }// function standingsToCorporations
  /**
   * Used to store XML to charStandingsFromAgents table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsFromAgents() {
    $tableName = $this->tablePrefix . $this->api . 'FromAgents';
    $currentPath = '//standingsFrom/rowset[@name="agents"]/row';
    return $this->standingsFromCommon($tableName, $currentPath);
  }// function standingsFromAgents
  /**
   * Used to store XML to charStandingsFromNPCCorporations table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsFromNPCCorporations() {
    $tableName = $this->tablePrefix . $this->api . 'FromNPCCorporations';
    $currentPath = '//standingsFrom/rowset[@name="NPCCorporations"]/row';
    return $this->standingsFromCommon($tableName, $currentPath);
  }// function standingsFromNPCCorporations
  /**
   * Used to store XML to charStandingsFromFactions table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsFromFactions() {
    $tableName = $this->tablePrefix . $this->api . 'FromFactions';
    $currentPath = '//standingsFrom/rowset[@name="factions"]/row';
    return $this->standingsFromCommon($tableName, $currentPath);
  }// function standingsFromFactions
  /**
   * Common code used to store XML to charStandingsTo* tables.
   * All the different standingsTo* functions call this function.
   * It's to keep the number of lines to be maintained low.
   *
   * @param String $tableName      Name of the table to store the data to.
   * @param String $currentPath    String to be used for xpath.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsToCommon($tableName, $currentPath) {
    $ret = FALSE;
    $extras = array('ownerID' => $this->characterID);
    $datum = $this->xml->xpath($currentPath);
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from `' . $tableName . '`';
      $sql .= ' where `ownerID`=' . $this->characterID;
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
    if (count($datum) > 0) {
      try {
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
  }// function standingsToCommon
  /**
   * Common code used to store XML to charStandingsFrom* tables.
   * All the different standingsFrom* functions call this function.
   *
   * @param String $tableName      Name of the table to store the data to.
   * @param String $currentPath    String to be used for xpath.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function standingsFromCommon($tableName, $currentPath) {
    $ret = FALSE;
    $extras = array('ownerID' => $this->characterID);
    $datum = $this->xml->xpath($currentPath);
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from `' . $tableName . '`';
      $sql .= ' where `ownerID`=' . $this->characterID;
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
    if (count($datum) > 0) {
      try {
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
  }// function standingsFromCommon
}
?>
