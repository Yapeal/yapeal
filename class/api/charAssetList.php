<?php
/**
 * Class used to fetch and store char AssetList API.
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
 * Class used to fetch and store char AssetList API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charAssetList extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'AssetList';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('flag' => 'I', 'itemID' => 'I', 'lft' => 'I',
    'locationID' => 'I', 'lvl' => 'I', 'ownerID' => 'I', 'quantity' => 'I',
    'rgt' => 'I', 'singleton' => 'L', 'typeID' => 'I'
  );
  /**
   * Used to store XML to AssetList table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      $mess = 'Xpath for ' . $tableName . ' from char section in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
      $data = $this->xml;
      if (count($data) > 0) {
        $mess = 'Before editAssets for ' . $tableName;
        $mess .= ' from char section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 3) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        // Call recursive function to modify XML.
        $rgt = $this->editAssets($data);
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
          //Just need the rows from XML now
          $datum = $data->xpath('//row');
          // Use generated owner node as root for tree.
          $nodeData = '<row itemID="' . $this->characterID .
            '" typeID="25" quantity="1" flag="0" singleton="0"' .
            ' lft="1" locationID="0" lvl="0" rgt="' . $rgt . '" />';
          $root = new SimpleXMLElement($nodeData);
          array_unshift($datum, $root);
          $extras = array('ownerID' => $this->characterID);
          $mess = 'multipleUpsertAttributes for ' . $tableName;
          $mess .= ' in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          multipleUpsertAttributes($datum, $this->types, $tableName,
            YAPEAL_DSN, $extras);
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
    return $ret;
  }// function apiStore
  /**
   * Navigates XML and adds lft and rgt attributes.
   *
   * Navigates XML using SimpleXML and adds lft and rgt attributes of Nested Set
   * for insertion into database.
   *
   * Original idea for function coded by Stephen.
   *
   * @author Stephen <stephenmg12@gmail.com>
   * @author Michael Cummings <mgcummings@yahoo.com>
   *
   * @param SimpleXMLElement $node Current element from tree.
   * @param integer $locationID Id to be added to nodes.
   * @param integer $index Current index for lft/rgt counting.
   * @param integer $level Level of nesting.
   *
   * @return integer Current index for lft/rgt counting.
   * 
   * @todo Look at pre-sort the <row>s by flag so items in the same hanger etc
   * are grouped together for lft/rgt.
   */
  protected function editAssets($node, $locationID = 0, $index = 2, $level = 0) {
    $nodeName = $node->getName();
    if ($nodeName == 'row') {
      $node->addAttribute('lft', $index++);
      $node->addAttribute('lvl', $level);
      if (isset($node['locationID'])) {
        $locationID = $node['locationID'];
      } else {
        $node->addAttribute('locationID', $locationID);
      };//if isset $node['locationID']...
    } elseif ($nodeName == 'rowset') {
      ++$level;
    };// elseif $nodeName == 'rowset' ...
    if ($children = $node->children()) {
      foreach ($children as $child) {
        $index = $this->editAssets($child, $locationID, $index, $level);
      };// foreach children ...
    };
    if ($nodeName == 'row') {
      $node->addAttribute('rgt', $index++);
      $this->itemsList[] = simplexml_load_string($node->asXML());
    };
    return $index;
  }// function editAssets
}
?>
