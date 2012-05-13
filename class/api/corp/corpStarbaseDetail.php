<?php
/**
 * Contains StarbaseDetail class.
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
  };
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Class used to fetch and store corp StarbaseDetail API.
 *
 * @package Yapeal
 * @subpackage Api_corp
 */
class corpStarbaseDetail extends ACorp {
  /**
   * @var integer Holds current POS ID.
   */
  private $posID;
  /**
   * @var YapealQueryBuilder Query instance for combatSettings table.
   */
  private $combat;
  /**
   * @var YapealQueryBuilder Query instance for fuel table.
   */
  private $fuel;
  /**
   * @var YapealQueryBuilder Query instance for generalSettings table.
   */
  private $general;
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
   * Used to store XML to MySQL table(s).
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    $posList = $this->posList();
    if (FALSE === $posList) {
      return FALSE;
    };// if FALSE ...
    $ret = TRUE;
    $this->prepareTables();
    foreach ($posList as $this->posID) {
      try {
        // Give each POS 60 seconds to finish. This should never happen but is
        // here to catch runaways.
        set_time_limit(60);
        // Need to add extra stuff to normal parameters to make walking work.
        $apiParams = $this->params;
        // This tells API server which tower we want.
        $apiParams['itemID'] = (string)$this->posID['itemID'];
        // First get a new cache instance.
        $cache = new YapealApiCache($this->api, $this->section, $this->ownerID, $apiParams);
        // See if there is a valid cached copy of the API XML.
        $result = $cache->getCachedApi();
        // If it's not cached need to try to get it.
        if (FALSE === $result) {
          $proxy = $this->getProxy();
          $con = new YapealNetworkConnection();
          $result = $con->retrieveXml($proxy, $apiParams);
          // FALSE means there was an error and it has already been report so just
          // return to caller.
          if (FALSE === $result) {
            return FALSE;
          };
          // Cache the received XML.
          $cache->cacheXml($result);
          // Check if XML is valid.
          if (FALSE === $cache->isValid()) {
            // No use going any farther if the XML isn't valid.
            $ret = FALSE;
            break;
          };
        };// if FALSE === $result ...
        // Create XMLReader.
        $this->xr = new XMLReader();
        // Pass XML to reader.
        $this->xr->XML($result);
        // Outer structure of XML is processed here.
        while ($this->xr->read()) {
          if ($this->xr->nodeType == XMLReader::ELEMENT &&
            $this->xr->localName == 'result') {
            $result = $this->parserAPI();
            if ($result === FALSE) {
              $ret = FALSE;
            };// if $result ...
          };// if $this->xr->nodeType ...
        };// while $this->xr->read() ...
        $this->xr->close();
      }
      catch (YapealApiErrorException $e) {
        // Any API errors that need to be handled in some way are handled in
        // this function.
        $this->handleApiError($e);
        if ($e->getCode() == 114) {
          $mess = 'Deleted ' . $this->posID['itemID'];
          $mess .= ' from StarbaseList for ' . $this->ownerID;
          $tableName = YAPEAL_TABLE_PREFIX . $this->section . 'StarbaseList';
          try {
            $con = YapealDBConnection::connect(YAPEAL_DSN);
            $sql = 'delete from ';
            $sql .= '`' . $tableName . '`';
            $sql .= ' where `ownerID`=' . $this->ownerID;
            $sql .= ' and `itemID`=' . $this->posID['itemID'];
            $con->Execute($sql);
          }
          catch (ADODB_Exception $e) {
            $mess = 'Could not delete ' . $this->posID['itemID'];
            $mess .= ' from StarbaseList for ' . $this->ownerID;
            Logger::getLogger('yapeal')->warn($mess);
            // Something wrong with query return FALSE.
            return FALSE;
          }
          Logger::getLogger('yapeal')->warn($mess);
        };// if $e->getCode() == 114 ...
        $ret = FALSE;
        continue;
      }
      catch (ADODB_Exception $e) {
        Logger::getLogger('yapeal')->warn($e);
        $ret = FALSE;
        continue;
      }
    };// foreach $posList ...
    return $ret;
  }// function apiStore
  /**
   * Per API parser for XML.
   *
   * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    $defaults = array('ownerID' => $this->ownerID);
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    $qb->useUpsert(FALSE);
    $qb->setDefaults($defaults);
    // Get a new query instance.
    $this->combat = new YapealQueryBuilder(
      YAPEAL_TABLE_PREFIX . $this->section . 'CombatSettings', YAPEAL_DSN
    );
    // Save some overhead for tables that are truncated or in some way emptied.
    $this->combat->useUpsert(FALSE);
    $this->combat->setDefaults($defaults);
    // Get a new query instance.
    $this->fuel = new YapealQueryBuilder(
      YAPEAL_TABLE_PREFIX . $this->section . 'Fuel', YAPEAL_DSN
    );
    // Save some overhead for tables that are truncated or in some way emptied.
    $this->fuel->useUpsert(FALSE);
    $this->fuel->setDefaults($defaults);
    // Get a new query instance.
    $this->general = new YapealQueryBuilder(
      YAPEAL_TABLE_PREFIX . $this->section . 'GeneralSettings', YAPEAL_DSN
    );
    // Save some overhead for tables that are truncated or in some way emptied.
    $this->general->useUpsert(FALSE);
    $this->general->setDefaults($defaults);
    try {
      $ret = TRUE;
      $row = array('posID' => $this->posID['itemID']);
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'onlineTimestamp':
              case 'state':
              case 'stateTimestamp':
                // Grab node name.
                $name = $this->xr->localName;
                // Move to text node.
                $this->xr->read();
                $row[$name] = $this->xr->value;
                break;
              case 'combatSettings':
              case 'generalSettings':
                // Check if empty.
                if ($this->xr->isEmptyElement == 1) {
                  break;
                };// if $this->xr->isEmptyElement ...
                // Grab node name.
                $subTable = $this->xr->localName;
                // Check for method with same name as node.
                if (!is_callable(array($this, $subTable))) {
                  $mess = 'Unknown what-to-be rowset ' . $subTable;
                  $mess .= ' found in ' . $this->api;
                  Logger::getLogger('yapeal')->warn($mess);
                  $ret = FALSE;
                  continue;
                };
                $result = $this->$subTable();
                if (FALSE === $result) {
                  $ret = FALSE;
                };// if FALSE ...
                break;
              case 'rowset':
                // Check if empty.
                if ($this->xr->isEmptyElement == 1) {
                  break;
                };// if $this->xr->isEmptyElement ...
                // Grab rowset name.
                $subTable = $this->xr->getAttribute('name');
                if (empty($subTable)) {
                  $mess = 'Name of rowset is missing in ' . $this->api;
                  Logger::getLogger('yapeal')->warn($mess);
                  $ret = FALSE;
                  continue;
                };
                $result = $this->rowset($subTable);
                if (FALSE === $result) {
                  $ret = FALSE;
                };// if FALSE ...
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
              if (count($this->combat) > 0) {
                $this->combat->store();
              };// if count $rows ...
              $this->combat = NULL;
              if (count($this->fuel) > 0) {
                $this->fuel->store();
              };// if count $rows ...
              $this->fuel = NULL;
              if (count($this->general) > 0) {
                $this->general->store();
              };// if count $rows ...
              $this->general = NULL;
              return $ret;
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
   * Used to store XML to StarbaseDetail CombatSettings table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function combatSettings() {
    $row = array('posID' => $this->posID['itemID']);
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'onAggression':
            case 'onCorporationWar':
            case 'onStandingDrop':
            case 'onStatusDrop':
            case 'useStandingsFrom':
              // Save element name to use as prefix for attributes.
              $prefix = $this->xr->localName;
              // Walk through attributes and add them to row.
              while ($this->xr->moveToNextAttribute()) {
                $row[$prefix . ucfirst($this->xr->name)] = $this->xr->value;
              };// while $this->xr->moveToNextAttribute() ...
              break;
          };// switch $xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'combatSettings') {
            $this->combat->addRow($row);
            return TRUE;
          };// if $this->xr->localName ...
          break;
        default:// Nothing to do here.
      };// switch $this->xr->nodeType ...
    };// while $xr->read() ...
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function combatSettings
  /**
   * Used to store XML to StarbaseDetail GeneralSettings table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function generalSettings() {
    $row = array('posID' => $this->posID['itemID']);
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'allowAllianceMembers':
            case 'allowCorporationMembers':
            case 'deployFlags':
            case 'usageFlags':
              $name = $this->xr->localName;
              $this->xr->read();
              $row[$name] = $this->xr->value;
              break;
          };// switch $xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'generalSettings') {
            $this->general->addRow($row);
            return TRUE;
          };// if $this->xr->localName ...
          break;
        default:// Nothing to do here.
      };// switch $this->xr->nodeType ...
    };// while $xr->read() ...
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function generalSettings
  /**
   * Used to store XML to rowset tables.
   *
   * @param string $table Name of the table for this rowset.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function rowset($table) {
    while ($this->xr->read()) {
      switch ($this->xr->nodeType) {
        case XMLReader::ELEMENT:
          switch ($this->xr->localName) {
            case 'row':
              $row = array('posID' => $this->posID['itemID']);
              // Walk through attributes and add them to row.
              while ($this->xr->moveToNextAttribute()) {
                $row[$this->xr->name] = $this->xr->value;
              };// while $this->xr->moveToNextAttribute() ...
              $this->$table->addRow($row);
              break;
          };// switch $this->xr->localName ...
          break;
        case XMLReader::END_ELEMENT:
          if ($this->xr->localName == 'rowset') {
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
   * Get per corp list of starbases from corpStarbaseList.
   *
   * @return mixed List of itemIDs for this corp's POSes or FALSE if error or
   * no POSes found for corporation.
   */
  protected function posList() {
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'select `itemID`';
      $sql .= ' from ';
      $sql .= '`' . YAPEAL_TABLE_PREFIX . $this->section . 'StarbaseList' . '`';
      $sql .= ' where `ownerID`=' . $this->ownerID;
      $list = $con->GetAll($sql);
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    if (count($list) == 0) {
      return FALSE;
    };// if count($list) ...
    // Randomize order so no one POS can starve the rest in case of
    // errors, etc.
    if (count($list) > 1) {
      shuffle($list);
    };
    return $list;
  }// function posList
  /**
   * Method used to prepare database table(s) before parsing API XML data.
   *
   * If there is any need to delete records or empty tables before parsing XML
   * and adding the new data this method should be used to do so.
   *
   * @return bool Will return TRUE if table(s) were prepared correctly.
   */
  protected function prepareTables() {
    $tables = array('CombatSettings', 'Fuel', 'GeneralSettings',
      'StarbaseDetail');
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

