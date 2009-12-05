<?php
/**
 * Class used to fetch and store AllianceList API.
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
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
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
   * @var string Holds the name of the API.
   */
  protected $api = 'AllianceList';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('allianceID' => 'I', 'executorCorpID' => 'I',
    'memberCount' => 'I', 'name' => 'C', 'shortName' => 'C', 'startDate' => 'T');
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//result/rowset[@name="alliances"]/row';
  /**
   * Used to save an item into database.
   *
   * Parent item (object) should call all child(ren)'s apiStore() as appropriate.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      $mess = 'Xpath for ' . $tableName . ' in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
      $datum = $this->xml->xpath($this->xpath);
      $cnt = count($datum);
      if ($cnt > 0) {
        try {
          $mess = 'Connect for '. $tableName;
          $mess .= ' in ' . basename(__FILE__);
          $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
          $con = YapealDBConnection::connect(YAPEAL_DSN);
          $mess = 'Before truncate ' . $tableName;
          $mess .= ' in ' . basename(__FILE__);
          $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
          // Empty out old data then upsert (insert) new
          $sql = 'truncate table ' . $this->tablePrefix . $this->api;
          $con->Execute($sql);
          $maxUpsert = 1000;
          for ($i = 0, $grp = (int)ceil($cnt / $maxUpsert),$pos = 0;
              $i < $grp;++$i, $pos += $maxUpsert) {
            $group = array_slice($datum, $pos, $maxUpsert, TRUE);
            $mess = 'multipleUpsertAttributes for ' . $tableName;
            $mess .= ' in ' . basename(__FILE__);
            $tracing->activeTrace(YAPEAL_TRACE_EVE, 1) &&
            $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
            YapealDBConnection::multipleUpsertAttributes($group, $this->types,
              $tableName, YAPEAL_DSN);
          };// for $i = 0...
        }
        catch (ADODB_Exception $e) {
          return FALSE;
        }
        $this->memberCorporations();
        $ret = TRUE;
      } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      trigger_error($mess, E_USER_NOTICE);
      $ret = FALSE;
      };// else count $datum ...
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
        $data = array('tableName' => $tableName, 'ownerID' => 0,
          'cachedUntil' => $cuntil);
        $mess = 'Upsert for '. $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
        $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
        YapealDBConnection::upsert($data, $cachetypes,
          YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        // Already logged nothing to do here.
      }
    };// if $this->xml ...
    return $ret;
  }// function apiStore()
  /**
   * Used to store XML to AllianceList's memberCorporations table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  protected function memberCorporations() {
    global $tracing;
    $pos =0;
    $ret = FALSE;
    $tableName = $this->tablePrefix . 'MemberCorporations';
    // Set the field types of query by name.
    $types = array('allianceID' => 'I', 'corporationID' => 'I',
      'startDate' => 'T');
    $xml = clone $this->xml->result[0];
    $this->editMemberCorporations($xml);
    $data = simplexml_load_string($xml->asXML());
    unset($xml);
    $datum = $data->xpath('//row//row');
    unset($data);
    $cnt = count($datum);
    if ($cnt > 0) {
      try {
        $con = YapealDBConnection::connect(YAPEAL_DSN);
        $mess = 'Before truncate ' . $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_EVE, 2) &&
        $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
        // Empty out old data then upsert (insert) new
        $sql = 'truncate table ' . $tableName;
        $con->Execute($sql);
        $maxUpsert = 1000;
        for ($i = 0, $grp = (int)ceil($cnt / $maxUpsert),$pos = 0;
            $i < $grp;++$i, $pos += $maxUpsert) {
          $group = array_slice($datum, $pos, $maxUpsert, TRUE);
          $mess = 'multipleUpsertAttributes for ' . $tableName;
          $mess .= ' in ' . basename(__FILE__);
          $tracing->activeTrace(YAPEAL_TRACE_EVE, 1) &&
          $tracing->logTrace(YAPEAL_TRACE_EVE, $mess);
          YapealDBConnection::multipleUpsertAttributes($group, $types,
            $tableName, YAPEAL_DSN);
        };// for $i = 0...
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
   * Navigates XML and adds allianceID attribute.
   *
   * @param SimpleXMLElement $node Current element from tree.
   * @param integer $alliance allianceID of corporation.
   * Used to propagate information from parents to children that don't include it
   * by default.
   *
   * @return integer Current alliance of corporation.
   */
  protected function editMemberCorporations($node, $alliance = 0) {
    $nodeName = $node->getName();
    if ($nodeName == 'row') {
      if (isset($node['allianceID'])) {
        $alliance = $node['allianceID'];
      } else {
        $node->addAttribute('allianceID', $alliance);
      };// if isset $node['allianceID']...
    };// if $nodeName=='row' ...
    if ($children = $node->children()) {
      foreach ($children as $child) {
        $alliance = $this->editMemberCorporations($child, $alliance);
      };// foreach children as child
    };
    return $alliance;
  }// function editMemberCorporations
}
?>
