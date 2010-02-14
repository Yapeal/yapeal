<?php
/**
 * Contains AllianceList class.
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
 * Class used to fetch and store AllianceList API.
 *
 * @package Yapeal
 * @subpackage Api_eve
 */
class eveAllianceList extends AEve {
  /**
   * @var array Group of alliance rows to be added to table.
   */
  private $alliances = array();
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'AllianceList';
  /**
   * @var array Group of corporation rows to be added to table.
   */
  private $corporations = array();
  /**
   * Used to save an item into database.
   *
   * Parent item (object) should call all child(ren)'s apiStore() as appropriate.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  function apiStore() {
    $ret = FALSE;
    if ($this->xml instanceof SimpleXMLElement) {
      if (count($this->xml->result->rowset->row) > 0) {
        try {
          $tableName = $this->tablePrefix . $this->api;
          $con = YapealDBConnection::connect(YAPEAL_DSN);
          // Empty out old data then upsert (insert) new
          $sql = 'truncate table ' . $tableName;
          $con->Execute($sql);
          $tableName = $this->tablePrefix . 'MemberCorporations';
          $con = YapealDBConnection::connect(YAPEAL_DSN);
          // Empty out old data then upsert (insert) new
          $sql = 'truncate table ' . $tableName;
          $con->Execute($sql);
          // Recurse through the XML and insert groups of alliances and member
          // corporations.
          $this->recursion($this->xml);
          // Insert any leftover alliances.
          if (count($this->alliances) > 0) {
            $tableName = $this->tablePrefix . $this->api;
            YapealDBConnection::multipleUpsert($this->alliances, $tableName,
              YAPEAL_DSN);
          };
          if (count($this->corporations) > 0) {
            $tableName = $this->tablePrefix . 'MemberCorporations';
            YapealDBConnection::multipleUpsert($this->corporations, $tableName,
              YAPEAL_DSN);
          };
        }
        catch (ADODB_Exception $e) {
          return FALSE;
        }
        //$this->memberCorporations();
        $ret = TRUE;
      } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      $ret = FALSE;
      };// else count $datum ...
      try {
        $tableName = $this->tablePrefix . $this->api;
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
        $data = array('tableName' => $tableName, 'ownerID' => 0,
          'cachedUntil' => $cuntil);
        YapealDBConnection::upsert($data,
          YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        // Already logged nothing to do here.
      }
    };// if $this->xml ...
    return $ret;
  }// function apiStore()
  /**
   * Navigates XML and groups of alliances and member corporations to be added
   * to tables.
   *
   * @param SimpleXMLElement $node Current element from tree.
   * @param integer $alliance allianceID of corporation.
   * Used to propagate information from parents to children that don't include it
   * by default.
   *
   * @return integer Current alliance of corporation.
   */
  protected function recursion($node, $alliance = 0) {
    $nodeName = $node->getName();
    if ($nodeName == 'row') {
      if (isset($node['allianceID'])) {
        $alliance = $node['allianceID'];
        $row = array();
        foreach ($node->attributes() as $k => $v) {
          $row[(string)$k] = (string)$v;
        };
        $this->alliances[] = $row;
        // Insert alliances as group is filled.
        if (YAPEAL_MAX_UPSERT == count($this->alliances)) {
          $tableName = $this->tablePrefix . $this->api;
          YapealDBConnection::multipleUpsert($this->alliances,
            $tableName, YAPEAL_DSN);
          $this->alliances = array();
        };
      } else {
        $node->addAttribute('allianceID', $alliance);
      };// if isset $node['allianceID']...
      if (isset($node['corporationID'])) {
        $row = array();
        foreach ($node->attributes() as $k => $v) {
          $row[(string)$k] = (string)$v;
        };
        $this->corporations[] = $row;
        // Insert corporations as group is filled.
        if (YAPEAL_MAX_UPSERT == count($this->corporations)) {
          $tableName = $this->tablePrefix . 'MemberCorporations';
          YapealDBConnection::multipleUpsert($this->corporations,
            $tableName, YAPEAL_DSN);
          $this->corporations = array();
        };
      };
    };// if $nodeName=='row' ...
    if ($children = $node->children()) {
      foreach ($children as $child) {
        $alliance = $this->recursion($child, $alliance);
      };// foreach children as child
    };
    return $alliance;
  }// function editMemberCorporations
}
?>
