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
 * @copyright  Copyright (c) 2008-2012, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
  $mess = basename(__FILE__)
    . ' must be included it can not be ran directly.' . PHP_EOL;
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  } else {
    fwrite(STDERR, $mess);
    exit(1);
  }
};
/**
 * Class used to fetch and store CharacterSheet API.
 *
 * @package Yapeal
 * @subpackage Api_char
 */
class charCharacterSheet  extends AChar {
  /**
   * Constructor
   *
   * @param array $params Holds the required parameters like keyID, vCode, etc
   * used in HTML POST parameters to API servers which varies depending on API
   * 'section' being requested.
   *
   * @throws LengthException for any missing required $params.
   */
  public function __construct(array $params) {
    // Cut off 'A' and lower case abstract class name to make section name.
    $this->section = strtolower(substr(get_parent_class($this), 1));
    $this->api = str_replace($this->section, '', __CLASS__);
    parent::__construct($params);
  }// function __construct
  /**
   * Per API parser for XML.
   *
   * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    $qb->setDefault('allianceName', '');
    $row = array();
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'allianceID':
              case 'allianceName':
              case 'ancestry':
              case 'balance':
              case 'bloodLine':
              case 'characterID':
              case 'cloneName':
              case 'cloneSkillPoints':
              case 'corporationID':
              case 'corporationName':
              case 'DoB':
              case 'gender':
              case 'name':
              case 'race':
                // Grab node name.
                $name = $this->xr->localName;
                if ($name == 'allianceName' && $this->xr->isEmptyElement == TRUE) {
                  $row[$name] = '';
                } else {
                  // Move to text node.
                  $this->xr->read();
                  $row[$name] = $this->xr->value;
                };
                break;
              case 'attributes':
              case 'attributeEnhancers':
                // Check if empty.
                if ($this->xr->isEmptyElement == TRUE) {
                  break;
                };// if $this->xr->isEmptyElement ...
                // Grab node name.
                $subTable = $this->xr->localName;
                // Check for method with same name as node.
                if (!is_callable(array($this, $subTable))) {
                  $mess = 'Unknown what-to-be rowset ' . $subTable;
                  $mess .= ' found in ' . $this->api;
                  Logger::getLogger('yapeal')->warn($mess);
                  return FALSE;
                };
                $this->$subTable();
                break;
              case 'rowset':
                // Check if empty.
                if ($this->xr->isEmptyElement == TRUE) {
                  break;
                };// if $this->xr->isEmptyElement ...
                // Grab rowset name.
                $subTable = $this->xr->getAttribute('name');
                if (empty($subTable)) {
                  $mess = 'Name of rowset is missing in ' . $this->api;
                  Logger::getLogger('yapeal')->warn($mess);
                  return FALSE;
                };
                if ($subTable == 'skills') {
                  $this->$subTable();
                } else {
                $this->rowset($subTable);
                };// else $subTable ...
                break;
              default:// Nothing to do here.
            };// $this->xr->localName ...
            break;
          case XMLReader::END_ELEMENT:
            if ($this->xr->localName == 'result') {
              $qb->addRow($row);
              if (count($qb) > 0) {
                $qb->store();
              };// if count $rows ...
              $qb = NULL;
              return TRUE;
            };// if $this->xr->localName == 'row' ...
            break;
          default:// Nothing to do.
        };// switch $this->xr->nodeType ...
      };// while $this->xr->read() ...
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->error($e);
      return FALSE;
    }
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function parserAPI
  /**
   * Handles attributes table.
   *
   * @return bool Returns TRUE if data stored to database table.
   */
  protected function attributes() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst(__FUNCTION__);
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    $qb->useUpsert(FALSE);
    $row = array('ownerID' => $this->ownerID);
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'charisma':
            case 'intelligence':
            case 'memory':
            case 'perception':
            case 'willpower':
              $name = $this->xr->localName;
              $this->xr->read();
              $row[$name] = $this->xr->value;
              break;
          };// switch $xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'attributes') {
            $qb->addRow($row);
            return $qb->store();
          };// if $this->xr->localName ...
          break;
        default:// Nothing to do here.
      };// switch $this->xr->nodeType ...
    };// while $xr->read() ...
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function attributes
  /**
   * Used to store XML to CharacterSheet's attributeEnhancers table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function attributeEnhancers() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst(__FUNCTION__);
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    $qb->useUpsert(FALSE);
    $row = array();
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'charismaBonus':
            case 'intelligenceBonus':
            case 'memoryBonus':
            case 'perceptionBonus':
            case 'willpowerBonus':
              $row = array('ownerID' => $this->ownerID);
              $row['bonusName'] = $this->xr->localName;
              break;
            case 'augmentatorName':
            case 'augmentatorValue':
              $name = $this->xr->localName;
              $this->xr->read();
              $row[$name] = $this->xr->value;
              break;
            default:// Nothing to do here.
          };// switch $xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          switch ($this->xr->localName) {
            case 'charismaBonus':
            case 'intelligenceBonus':
            case 'memoryBonus':
            case 'perceptionBonus':
            case 'willpowerBonus':
              $qb->addRow($row);
              break;
            case 'attributeEnhancers':
              return $qb->store();
            default:// Nothing to do here.
          };// switch $xr->localName ...
          break;
        default:// Nothing to do here.
      };// switch $this->xr->nodeType ...
    };// while $xr->read() ...
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function attributeEnhancers
  /**
   * Used to store XML to rowset tables.
   *
   * @param string $table Name of the table for this rowset.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function rowset($table) {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst($table);
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    $qb->useUpsert(FALSE);
    $qb->setDefault('ownerID', $this->ownerID);
    $row = array();
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'row':
              // Walk through attributes and add them to row.
              while ($this->xr->moveToNextAttribute()) {
                $row[$this->xr->name] = $this->xr->value;
              };// while $this->xr->moveToNextAttribute() ...
              $qb->addRow($row);
              break;
          };// switch $this->xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'rowset') {
            // Insert any leftovers.
            if (count($qb) > 0) {
              $qb->store();
            };// if count $rows ...
            $qb = NULL;
            return TRUE;
          };// if $this->xr->localName == 'row' ...
          break;
      };// switch $this->xr->nodeType
    };// while $this->xr->read() ...
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function rowset
  /**
   * Used to store XML to CharacterSheet's skills table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function skills() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst(__FUNCTION__);
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    $qb->useUpsert(FALSE);
    $defaults = array('level' => 0, 'ownerID' => $this->ownerID,
      'published' => 1
    );
    $qb->setDefaults($defaults);
    $row = array();
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'row':
              // Walk through attributes and add them to row.
              while ($this->xr->moveToNextAttribute()) {
                $row[$this->xr->name] = $this->xr->value;
              };// while $this->xr->moveToNextAttribute() ...
              $qb->addRow($row);
              break;
          };// switch $this->xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'rowset') {
            // Insert any leftovers.
            if (count($qb) > 0) {
              $qb->store();
            };// if count $rows ...
            $qb = NULL;
            return TRUE;
          };// if $this->xr->localName == 'row' ...
          break;
      };// switch $this->xr->nodeType
    };// while $this->xr->read() ...
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function skills
  /**
   * Method used to prepare database table(s) before parsing API XML data.
   *
   * If there is any need to delete records or empty tables before parsing XML
   * and adding the new data this method should be used to do so.
   *
   * @return bool Will return TRUE if table(s) were prepared correctly.
   */
  protected function prepareTables() {
    $tables = array('Attributes', 'AttributeEnhancers', 'Certificates',
      'CorporationRoles', 'CorporationRolesAtBase', 'CorporationRolesAtHQ',
      'CorporationRolesAtOther', 'CorporationTitles', 'Skills'
    );
    foreach ($tables as $table) {
      try {
        $con = YapealDBConnection::connect(YAPEAL_DSN);
        // Empty out old data then upsert (insert) new.
        $sql = 'delete from `';
        $sql .= YAPEAL_TABLE_PREFIX . $this->section . $table . '`';
        $sql .= ' where `ownerID`=' . $this->ownerID;
        $con->Execute($sql);
      }
      catch (ADODB_Exception $e) {
        Logger::getLogger('yapeal')->warn($e);
        return FALSE;
      }
    };// foreach $tables ...
    return TRUE;
  }// function prepareTables
}

