<?php
/**
 * Class used to fetch and store char SkillQueue API.
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
 * Class used to fetch and store char SkillQueue API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charSkillQueue extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'SkillQueue';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('endSP' => 'I', 'endTime' => 'T', 'level' => 'I',
    'ownerID' => 'I', 'queuePosition' => 'I', 'startSP' => 'I',
    'startTime' => 'T', 'typeID' => 'I');
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//row';
  /**
   * Used to store XML to SkillQueue table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      $mess = 'Xpath for ' . $tableName . ' in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      $datum = $this->xml->xpath($this->xpath);
      if (count($datum) > 0) {
        try {
          $con = connect(YAPEAL_DSN, $tableName);
          $sql = 'delete from ' . $tableName;
          $sql .= ' where ownerID=' . $this->characterID;
          $mess = 'Before delete for ' . $tableName;
          $mess .= ' in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          // Clear out old tree for this owner.
          $con->Execute($sql);
          $extras = array('ownerID' => $this->characterID);
          $mess = 'multipleUpsertAttributes for ' . $tableName;
          $mess .= ' in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          multipleUpsertAttributes($datum, $this->types, $tableName, YAPEAL_DSN,
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
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
        $data = array( 'tableName' => $tableName,
          'ownerID' => $this->characterID, 'cachedUntil' => $cuntil
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
    return $ret;
  }// function apiStore
}
?>
