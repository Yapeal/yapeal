<?php
/**
 * Class used to fetch and store Corp MarketOrders API.
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
 * Class used to fetch and store corp MarketOrders API.
 *
 * @package Yapeal
 * @subpackage Api_corporation
 */
class corpMarketOrders extends ACorporation {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'MarketOrders';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('accountKey' => 'I', 'bid' => 'I', 'charID' => 'I',
    'duration' => 'I', 'escrow' => 'N', 'issued' => 'T', 'minVolume' => 'I',
    'orderID' => 'I', 'orderState' => 'I', 'ownerID' => 'I', 'price' => 'N',
    'range' => 'I', 'stationID' => 'I', 'typeID' => 'I', 'volEntered' => 'I',
    'volRemaining' => 'I'
  );
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//row';
  /**
   * Used to store XML to MarketOrders table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      $mess = 'Xpath for ' . $tableName . ' in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      $datum = $this->xml->xpath($this->xpath);
      $cnt = count($datum);
      if ($cnt > 0) {
        try {
          $extras = array('ownerID' => $this->corporationID);
          $maxUpsert = 1000;
          for ($i = 0, $grp = (int)ceil($cnt / $maxUpsert),$pos = 0;
              $i < $grp;++$i, $pos += $maxUpsert) {
            $group = array_slice($datum, $pos, $maxUpsert, TRUE);
            $mess = 'multipleUpsertAttributes for ' . $tableName;
            $mess .= ' in ' . basename(__FILE__);
            $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
            $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
            YapealDBConnection::multipleUpsertAttributes($group, $this->types,
              $tableName, YAPEAL_DSN, $extras);
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
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
        $data = array( 'tableName' => $tableName,
          'ownerID' => $this->corporationID, 'cachedUntil' => $cuntil
        );
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
  }// function apiStore
}
?>
