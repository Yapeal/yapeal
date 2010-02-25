<?php
/**
 * Contains Characters class.
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
 * Class used to fetch and store account Characters API.
 *
 * @package Yapeal
 * @subpackage Api_account
 */
class accountCharacters extends AAccount {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'Characters';
  /**
   * @var SimpleXMLElement Hold the XML return from API.
   */
  protected $xml;
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//row';
  /**
   * Used to save an item into database.
   *
   * Parent item (object) should call all child(ren)'s apiStore() as appropriate.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  function apiStore() {
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      $cuntil = (string)$this->xml->cachedUntil[0];
      $this->xml = $this->xml->xpath($this->xpath);
      if (count($this->xml) > 0) {
        try {
          $extras = array('userID' => $this->userID);
          YapealDBConnection::multipleUpsertAttributes($this->xml, $tableName,
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
      };// else count $this->xml ...
      try {
        // Update CachedUntil time since we should have a new one.
        $data = array('tableName' => $tableName, 'ownerID' => $this->userID,
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
}
?>
