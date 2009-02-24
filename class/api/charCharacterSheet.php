<?php
/**
 * Class used to fetch and store CharacterSheet API.
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
 * Class used to fetch and store CharacterSheet API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charCharacterSheet  extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'CharacterSheet';
  /**
   * Used to store XML to CharacterSheet tables.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      if ($this->attributes()) {
        ++$ret;
      };
      if ($this->attributeEnhancers()) {
        ++$ret;
      };
      if ($this->certificates()) {
        ++$ret;
      };
      if ($this->charSheet()) {
        ++$ret;
      };
      if ($this->corporationRoles()) {
        ++$ret;
      };
      if ($this->corporationRolesAtBase()) {
        ++$ret;
      };
      if ($this->corporationRolesAtHQ()) {
        ++$ret;
      };
      if ($this->corporationRolesAtOther()) {
        ++$ret;
      };
      if ($this->corporationTitles()) {
        ++$ret;
      };
      if ($this->skills()) {
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
    if ($ret == 10) {
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
  protected function charSheet() {
    global $tracing;
    $types = array(
      'balance' => 'N', 'bloodLine' => 'C', 'characterID' => 'I',
      'cloneName' => 'C', 'cloneSkillPoints' => 'I', 'corporationID' => 'I',
      'corporationName' => 'C', 'gender' => 'C', 'name' => 'C', 'race' => 'C'
    );
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    $mess = 'Clone for ' . $tableName . ' from char section in ' . __FILE__;
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    $datum = clone $this->xml->result[0];
    // Get rid of child table stuff
    $mess = 'Delete children for ' . $tableName;
    $mess .= ' from char section in ' . __FILE__;
    $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
    unset($datum->rowset, $datum->attributes, $datum->attributeEnhancers);
    $data = array();
    if (count($datum) > 0) {
      $data = array();
      foreach ($datum->children() as $k => $v) {
        $data[$k] = (string)$v;
      };
      try {
        $mess = 'Upsert for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        upsert($data, $types, $tableName, YAPEAL_DSN);
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
  }// function charSheet
  /**
   * Used to store XML to CharacterSheet's attributes table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function attributes() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Attributes';
    // Set the field types of query by name.
    $types = array('charisma' => 'I', 'intelligence' => 'I', 'memory' => 'I',
      'ownerID' => 'I', 'perception' => 'I', 'willpower' => 'I');
    $datum = $this->xml->result->attributes;
    if (count($datum) > 0) {
      $data = array('ownerID' => $this->characterID);
      foreach ($datum->children() as $k => $v) {
        $data[$k] = (string)$v;
      };
      try {
        $mess = 'Upsert for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        upsert($data, $types, $tableName, YAPEAL_DSN);
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
  }// function attributes
  /**
   * Used to store XML to CharacterSheet's attributeEnhancers table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function attributeEnhancers() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'AttributeEnhancers';
    $types = array('augmentatorName' => 'C', 'augmentatorValue' => 'I',
      'bonusName' => 'C', 'ownerID' => 'I'
    );
    $datum = $this->xml->xpath('//attributeEnhancers');
    if (count($datum) > 0) {
      $cnt = 0;
      foreach ($datum[0]->children() as $k) {
        $data[$cnt]['augmentatorName'] = (string)$k->augmentatorName[0];
        $data[$cnt]['augmentatorValue'] = (int)$k->augmentatorValue[0];
        $data[$cnt]['bonusName'] = $k->getName();
        $data[$cnt]['ownerID'] = $this->characterID;
        ++$cnt;
      };
      if (count($data) > 0) {
        try {
          $mess = 'Upsert for ' . $tableName;
          $mess .= ' from char section in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          multipleUpsert($data, $types, $tableName, YAPEAL_DSN);
        }
        catch (ADODB_Exception $e) {
          return FALSE;
        }
        $ret = TRUE;
      } else {
        $mess = 'No implants for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        trigger_error($mess, E_USER_NOTICE);
        $ret = FALSE;
      };// else count $data ...
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    $mess .= ' from char section in ' . __FILE__;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function attributeEnhancers
  /**
   * Used to store XML to CharacterSheet's certificates table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function certificates() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Certificates';
    // Set the field types of query by name.
    $types = array('certificateID' => 'I', 'ownerID' => 'I');
    $datum = $this->xml->xpath('//rowset[@name="certificates"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function certificates
  /**
   * Used to store XML to CharacterSheet's corporationRoles table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRoles() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRoles';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRoles"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function corporationRoles
  /**
   * Used to store XML to CharacterSheet's corporationRolesAtBase table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRolesAtBase() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRolesAtBase';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRolesAtBase"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function corporationRolesAtBase
  /**
   * Used to store XML to CharacterSheet's corporationRolesAtHQ table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRolesAtHQ() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRolesAtHQ';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRolesAtHQ"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function corporationRolesAtHQ
  /**
   * Used to store XML to CharacterSheet's corporationRolesAtOther table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRolesAtOther() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRolesAtOther';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRolesAtOther"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function corporationRolesAtOther
  /**
   * Used to store XML to CharacterSheet's corporationTitles table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationTitles() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationTitles';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'titleID' => 'I', 'titleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationTitles"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function corporationTitles
  /**
   * Used to store XML to CharacterSheet's skills table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function skills() {
    global $tracing;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Skills';
    // Set the field types of query by name.
    $types = array('level' => 'I', 'ownerID' => 'I', 'skillpoints' => 'I',
      'typeID' => 'I', 'unpublished' => 'L');
    $datum = $this->xml->xpath('//rowset[@name="skills"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID, 'level' => 0,
          'unpublished' => 0
        );
        $mess = 'multipleUpsertAttributes for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        multipleUpsertAttributes($datum, $types, $tableName, YAPEAL_DSN,
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
  }// function skills
}
?>
