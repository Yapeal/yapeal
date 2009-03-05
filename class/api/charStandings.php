<?php
/**
 * Class used to fetch and store char Standings API.
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
 * @author Simon Dellenbach <simon@dellenba.ch>
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
    global $tracing;
    global $cachetypes;
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
        $mess = 'Upsert for '. $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
        $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
        upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
          YAPEAL_DSN);
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
    global $tracing;
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
    global $tracing;
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
    global $tracing;
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
    global $tracing;
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
    global $tracing;
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
    global $tracing;
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
    global $tracing;
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
    global $tracing;
    $ret = FALSE;
    $typesTo = array('toID' => 'I', 'toName' => 'T',
      'standing' => 'N', 'ownerID' => 'I');
    $extras = array('ownerID' => $this->characterID);
    $datum = $this->xml->xpath($currentPath);
    if (count($datum) > 0) {
      try {
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $typesTo, $tableName, YAPEAL_DSN,
          $extras);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      $mess .= ' from char section in ' . __FILE__;
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
    global $tracing;
    $ret = FALSE;
    $typesFrom = array('fromID' => 'I', 'fromName' => 'T',
      'standing' => 'N', 'ownerID' => 'I');
    $extras = array('ownerID' => $this->characterID);
    $datum = $this->xml->xpath($currentPath);
    if (count($datum) > 0) {
      try {
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $typesFrom, $tableName, YAPEAL_DSN,
          $extras);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      $mess .= ' from char section in ' . __FILE__;
      trigger_error($mess, E_USER_NOTICE);
      $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function standingsFromCommon
}
?>
