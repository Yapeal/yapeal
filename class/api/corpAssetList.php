<?php
/**
 * Contains AssetList class.
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
 * Class used to fetch and store corp AssetList API.
 *
 * @package Yapeal
 * @subpackage Api_corporation
 */
class corpAssetList extends ACorporation {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'AssetList';
  /**
   * @var array An array holding rows from XML to be upsert to database.
   */
  private $upsertList = array();
  /**
   * @var string Holds name of table used in upsert.
   */
  private $tableName = '';
  /**
   * Used to store XML to AssetList table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    $ret = FALSE;
    $this->tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      if (count($this->xml) > 0) {
        try {
          $this->xml = self::sxiToArray($this->xml);
          $con = YapealDBConnection::connect(YAPEAL_DSN);
          $sql = 'delete from `' . $this->tableName . '`';
          $sql .= ' where `ownerID`=' . $this->corporationID;
          // Clear out old tree for this owner.
          $con->Execute($sql);
          // Use generated owner node as root for tree.
          $row = array('flag' => 0, 'itemID' => $this->corporationID,
            'lft' => 0, 'locationID' => 0, 'lvl' => 0,
            'ownerID' => $this->corporationID, 'quantity' => 1, 'rgt' => 1,
            'singleton' => 0, 'typeID' => 2
          );
          // $this->recursion will take care of inserting the root node.
          $this->upsertList[] = $row;
          $extras = array('ownerID' => $this->corporationID);
          $inherit = array('locationID' => '0', 'index' => 2, 'level' => 0);
          // Recurse through all the rows and add them to database.
          $row['rgt'] = $this->recursion($this->xml[1]['value'][0], $inherit, $extras);
          // Time to update owner node and upsert any leftover records.
          array_unshift($this->upsertList, $row);
          YapealDBConnection::multipleUpsert($this->upsertList,
            $this->tableName, YAPEAL_DSN);
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
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml[2]['value'];
        $data = array( 'tableName' => $this->tableName,
          'ownerID' => $this->corporationID, 'cachedUntil' => $cuntil
        );
        YapealDBConnection::upsert($data,
          YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        // Already logged nothing to do here.
      }
    };// if $this->xml ...
    return $ret;
  }// function apiStore
  /**
   * Navigates XML and build upsert groups to be added to database tables.
   *
   * Original idea for function coded by Stephen.
   *
   * @author Stephen <stephenmg12@gmail.com>
   * @author Michael Cummings <mgcummings@yahoo.com>
   *
   * @param array $node Current element from tree.
   * @param array $inherit An array of stuff that needs to propagate from parent
   * to child.
   * @param array $extra An array of defaults that need to be added to all
   * records.
   *
   * @return integer Current index for lft/rgt counting.
   */
  protected function recursion($node, $inherit, $extra = array()) {
    if ($node['tag'] == 'row') {
      $node['attributes']['lft'] = $inherit['index']++;
      $node['attributes']['lvl'] = $inherit['level'];
      if (!empty($extra)) {
        foreach ($extra as $k => $v) {
          $node['attributes'][$k] = (string)$v;
        };
      };
      if (isset($node['attributes']['locationID'])) {
        $inherit['locationID'] = (string)$node['attributes']['locationID'];
      } else {
        $node['attributes']['locationID'] = $inherit['locationID'];
      };//if isset $node['attributes']['locationID']...
    } elseif ($node['tag'] == 'rowset') {
      ++$inherit['level'];
    };// elseif $node['tag'] == 'rowset' ...
    if (isset($node['value']) && is_array($node['value'])) {
      foreach ($node['value'] as $child) {
        $inherit['index'] = $this->recursion($child, $inherit, $extra);
      };// foreach $node['value'] ...
    };// isset $node['value'] && ...
    if ($node['tag'] == 'row') {
      $node['attributes']['rgt'] = $inherit['index']++;
      // While here make a new array with just attributes from rows.
      $this->upsertList[] = $node['attributes'];
      if (YAPEAL_MAX_UPSERT == count($this->upsertList)) {
        YapealDBConnection::multipleUpsert($this->upsertList, $this->tableName,
        YAPEAL_DSN);
        $this->upsertList = array();
      };// if YAPEAL_MAX_UPSERT ...
    };// if $node['tag'] ...
    return $inherit['index'];
  }// function recursion
  /**
   * Basic opimized shell sort for array.
   *
   * @param array $elements The array to be sorted.
   * @param mixed $compare The callback function to use when comparing the items
   * from $elements.
   * @param mixed List of integers used as gap for sort. Must be in descending
   * order with last one equal to 1. i.e. array(a > b > c > ... > 1).
   *
   * @return bool Always returns TRUE.
   */
  protected static function shellSort(array &$elements,
      $compare = array(__CLASS__, 'sortCompare'), $sequence = NULL) {
    if (!is_callable($compare, FALSE)) {
      $mess = 'The function/method past to ' . __FUNCTION__;
      $mess .= ' to be used in compare is not callable';
      throw new InvalidArgumentException($mess, 1);
    };// if !is_callable...
    // Get the size of the array.
    $length = count($elements);
    // If not passed an array to use use the default optimal one.
    if (empty($sequence) || !is_array($sequence)) {
      $sequence = array(1147718700, 510097200, 226709866, 100759940, 44782196,
        19903198, 8845866, 3931496, 1747331, 776591, 345152, 153401, 68178, 30301,
        13467, 5985, 2660, 1182, 525, 233, 103, 46, 20, 9, 4, 1
      );
    };// if empty $elements ...
    // Make an array of steps to be used in sort based on sized of array.
    $num = $length >> 1;
    $gap = array();
    foreach ($sequence as $v) {
      if ($v < $num) {
        $gap[] = $v;
      } else {
        continue;
      };// else $v ...
    };// foreach $sequence ...
    // Loop through all the steps.
    foreach ($gap as $step) {
      for ($j = $step; $j < $length; ++$j) {
        $temp = $elements[$j];
        $p = $j - $step;
        // This is where the comparison is made to decide if the current element
        // needs to be swapped with the other element from the array.
        while ($p >= 0 &&
          call_user_func_array($compare, array(&$temp, &$elements[$p])) < 0) {
          $elements[$p + $step] = $elements[$p];
          $p = $p - $step;
        };//while $p >= 0 && ...
        $elements[$p + $step] = $temp;
      };// for j ...
    };// for i ...
    return TRUE;
  }// function shellSort
  /**
   * Function used to compare two row from array and decide if $a is less than,
   * equal, or great than $b
   *
   * @param array $a Row being compared.
   * @param array $b Row $a is being compared to.
   *
   * @return int Returns -1 if $a < $b, 0 if $a == $b, +1 if $a > $b.
   */
  protected static function sortCompare($a, $b) {
    if (isset($a['attributes']['locationID'], $b['attributes']['locationID'])) {
      if ($a['attributes']['locationID'] == $b['attributes']['locationID']) {
        if (isset($a['value'], $b['value'])) {
          if (is_array($a['value']) && is_array($b['value'])) {
            if (count($a['value'][0]['value']) == count($b['value'][0]['value'])) {
              if (isset($a['attributes']['flag'], $b['attributes']['flag'])) {
                if ($a['attributes']['flag'] != $b['attributes']['flag']) {
                  // Sort on flag attribute values.
                  return ($a['attributes']['flag'] < $b['attributes']['flag']) ? -1 : 1;
                };// if $a['attributes']['flag'] ...
              } else {
                //Sort on having flag attribute.
                return (isset($a['attributes']['flag'])) ? -1 : 1;
              };// else isset $a['attributes']['flag'], ...
            } else {
              // Sort on number of children.
              return (count($a['value'][0]['value']) > count($b['value'][0]['value'])) ? -1 : 1;
            };// else count $a['value'] ...
          } else {
            // Sort on having children.
            return (is_array($a['value'])) ? -1 : 1;
          };// else is_array $a['value']) && ...
        } else {
          // Sort on having a value.
          return (isset($a['value'])) ? -1 : 1;
        };// else isset $a['value'], ...
      };
      // Sort on locationID
      return ($a['attributes']['locationID'] < $b['attributes']['locationID']) ? -1 : 1;
    } else {
      // Sort on locationID attribute existing.
      return (isset($a['attributes']['locationID'])) ? -1 : 1;
    };
    // Nothing to sort by so say they are equal.
    return 0;
  }// function sortCompare
  /**
   * Function used to turn SimpleXMLIterator into an assocative array.
   *
   * Each SimpleXMLIterator item will be turned into an assocative array.
   * The format is as follows:
   *   array('tag' => 'row',
   *     ['attributes' => array('location' => '123', ...)],
   *     ['value' => (string) | array(children)]
   *   );
   * The 'attibutes' array and 'value' are both optional and should be tested
   * for with isset() before try to use them.
   *
   * Note that the 'root' node does not appear in the returned array, so if
   * passed for example:
   * '<rowset name="contents"><row id="123">1</row><row id="234">2</row></rowset>'
   *
   * the returned array would look like:
   * array(
   *   [0] => array('tag' => 'row',
   *     'attributes' => array('id' => "123"),
   *     'value' => '1'),
   *   [1] => array('tag' => 'row',
   *     'attributes' => array('id => '234'),
   *     'value' => '2')
   * );
   *
   * @param SimpleXMLIterator $sxi The object to be converted to array.
   *
   * @return array Returns $sxi convert to an array.
   */
  protected static function sxiToArray($sxi) {
    $tree = NULL;
    for ($sxi->rewind(); $sxi->valid(); $sxi->next()) {
      $node = array('tag' => $sxi->key());
      $curr = $sxi->current();
      if ($attrs = $curr->attributes()) {
        foreach ($attrs as $k => $v) {
          $node['attributes'][(string)$k] = (string)$v;
        };
      };
      // Recurse through any children.
      if ($sxi->hasChildren()) {
        $value = self::sxiToArray($sxi->current());
      } else {
        $value = strval($sxi->current());
      };
      if (!empty($value)) {
        $node['value'] = $value;
      };
      $tree[] = $node;
    };
    return $tree;
  }// function sxiToArray
}
?>
