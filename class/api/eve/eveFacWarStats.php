<?php
/**
 * Contains FacWarStats class.
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
 * @author     Claus G. Pedersen <satissis@gmail.com>
 * @copyright  Copyright (c) 2008-2012, Michael Cummings, Claus G. Pedersen
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
  };
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Class used to fetch and store eve FacWarStats API.
 *
 * @package Yapeal
 * @subpackage Api_eve
 */
class eveFacWarStats extends AEve {
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
   * API parser for XML.
   *
   * @return bool Returns TRUE if XML was parsered correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    try {
      $row = array();
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'totals':
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
                $row = $this->$subTable();
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
                $this->rowset($subTable);
                break;
              default:// Nothing to do here.
            };// $this->xr->localName ...
            break;
          case XMLReader::END_ELEMENT:
            if ($this->xr->localName == 'result') {
              if ($row && is_array($row) && count($row) > 0) {
                $qb->addRow($row);
              }
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
   * Handles totals from XML Note.
   *
   * @return array Returns array of data to store in database table.
   */
  protected function totals() {
    $row = array();
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'killsYesterday':
            case 'killsLastWeek':
            case 'killsTotal':
            case 'victoryPointsYesterday':
            case 'victoryPointsLastWeek':
            case 'victoryPointsTotal':
              $name = $this->xr->localName;
              $this->xr->read();
              $row[$name] = $this->xr->value;
              break;
          };// switch $xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'totals') {
            return $row;
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
    $qb->useUpsert(TRUE);
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'row':
              $row = array();
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
   * Method used to prepare database table(s) before parsing API XML data.
   *
   * If there is any need to delete records or empty tables before parsing XML
   * and adding the new data this method should be used to do so.
   *
   * @return bool Will return TRUE if table(s) were prepared correctly.
   */
  protected function prepareTables() {
    $tables = array('FactionWars', 'FacWarStats');
    foreach ($tables as $table) {
      try {
        $con = YapealDBConnection::connect(YAPEAL_DSN);
        // Empty out old data then upsert (insert) new.
        $sql = 'TRUNCATE TABLE `';
        $sql .= YAPEAL_TABLE_PREFIX . $this->section . $table . '`';
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

