<?php
/**
 * Contains CharacterSheet class.
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
      if ($this->characterSheet()) {
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
        YapealDBConnection::upsert($data,
          YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
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
  protected function characterSheet() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    $datum = clone $this->xml->result[0];
    // Get rid of child table stuff
    unset($datum->rowset, $datum->attributes, $datum->attributeEnhancers);
    $data = array();
    if (count($datum) > 0) {
      $data = array();
      foreach ($datum->children() as $k => $v) {
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
  }// function charSheet
  /**
   * Used to store XML to CharacterSheet's attributes table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function attributes() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Attributes';
    $datum = $this->xml->result->attributes;
    if (count($datum) > 0) {
      $data = array('ownerID' => $this->characterID);
      foreach ($datum->children() as $k => $v) {
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
  }// function attributes
  /**
   * Used to store XML to CharacterSheet's attributeEnhancers table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function attributeEnhancers() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'AttributeEnhancers';
    $types = array('augmentatorName' => 'C', 'augmentatorValue' => 'I',
      'bonusName' => 'C', 'ownerID' => 'I'
    );
    $datum = $this->xml->xpath('//attributeEnhancers');
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'delete from `' . $tableName . '`';
      $sql .= ' where `ownerID`=' . $this->characterID;
      // Clear out old info for this owner.
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {}
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
          YapealDBConnection::multipleUpsert($data, $tableName, YAPEAL_DSN);
        }
        catch (ADODB_Exception $e) {
          return FALSE;
        }
        $ret = TRUE;
      } else {
        $mess = 'No implants for ' . $tableName;
        trigger_error($mess, E_USER_NOTICE);
        $ret = FALSE;
      };// else count $data ...
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
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
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'Certificates';
    // Set the field types of query by name.
    $types = array('certificateID' => 'I', 'ownerID' => 'I');
    $datum = $this->xml->xpath('//rowset[@name="certificates"]/row');
    if (count($datum) > 0) {
      try {
        $extras = array('ownerID' => $this->characterID);
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
  }// function certificates
  /**
   * Used to store XML to CharacterSheet's corporationRoles table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRoles() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRoles';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRoles"]/row');
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
        $extras = array('ownerID' => $this->characterID);
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
  }// function corporationRoles
  /**
   * Used to store XML to CharacterSheet's corporationRolesAtBase table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRolesAtBase() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRolesAtBase';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRolesAtBase"]/row');
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
        $extras = array('ownerID' => $this->characterID);
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
  }// function corporationRolesAtBase
  /**
   * Used to store XML to CharacterSheet's corporationRolesAtHQ table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRolesAtHQ() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRolesAtHQ';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRolesAtHQ"]/row');
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
        $extras = array('ownerID' => $this->characterID);
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
  }// function corporationRolesAtHQ
  /**
   * Used to store XML to CharacterSheet's corporationRolesAtOther table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationRolesAtOther() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationRolesAtOther';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'roleID' => 'I', 'roleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationRolesAtOther"]/row');
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
        $extras = array('ownerID' => $this->characterID);
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
  }// function corporationRolesAtOther
  /**
   * Used to store XML to CharacterSheet's corporationTitles table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function corporationTitles() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'CorporationTitles';
    // Set the field types of query by name.
    $types = array('ownerID' => 'I', 'titleID' => 'I', 'titleName' => 'C');
    $datum = $this->xml->xpath('//rowset[@name="corporationTitles"]/row');
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
        $extras = array('ownerID' => $this->characterID);
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
  }// function corporationTitles
  /**
   * Used to store XML to CharacterSheet's skills table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function skills() {
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
  }// function skills
}
?>
