<?php
/**
 * Contains MailBodies class.
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
 * Class used to fetch and store char MailBodies API.
 *
 * @package Yapeal
 * @subpackage Api_char
 */
class charMailBodies extends AChar {
  /**
   * Constructor
   *
   * @param array $params Holds the required parameters like userID, apiKey, etc
   * used in HTML POST parameters to API servers which varies depending on API
   * 'section' being requested.
   *
   * @throws LengthException for any missing required $params.
   */
  public function __construct(array $params) {
    parent::__construct($params);
    $this->api = str_replace($this->section, '', __CLASS__);
  }// function __construct
  /**
   * Used to store XML to MySQL table(s).
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    try {
      // First get a new cache instance.
      $cache = new YapealApiCache($this->api, $this->section, $this->ownerID, $this->params);
      // See if there is a valid cached copy of the API XML.
      $result = $cache->getCachedApi();
      // If it's not cached need to try to get it.
      if (FALSE === $result) {
        $proxy = $this->getProxy();
        $con = new YapealNetworkConnection();
        $ids = $this->getIds();
        // Need to add $ids to normal parameters.
        $this->params['ids'] = implode(',', $ids);
        $result = $con->retrieveXml($proxy, $this->params);
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
          return FALSE;
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
        };// if $this->xr->nodeType ...
      };// while $this->xr->read() ...
      $this->xr->close();
      return $result;
    }
    catch (YapealApiErrorException $e) {
      // Any API errors that need to be handled in some way are handled in this
      // function.
      $this->handleApiError($e);
      return FALSE;
    }
    catch (ADODB_Exception $e) {
      $mess = 'Uncaught ADOdb exception' . PHP_EOL;
      trigger_error($mess, E_USER_WARNING);
      // Catch any uncaught ADOdb exceptions here.
      return FALSE;
    }
  }// function apiStore
  /**
   * Used to get a list of message IDs.
   *
   * @return array Returns a list of messages IDs.
   */
  protected function getIds() {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $sql = 'select messageID';
    $sql .= ' from ';
    $sql .= '`' . YAPEAL_TABLE_PREFIX . 'charMailMessages`';
    $sql .= ' where';
    $sql .= ' `ownerID`=' . $this->ownerID;
    try {
      $result = $con->getCol($sql);
    }
    catch (ADODB_Exception $e) {
      $result = array('0');
    }
    if (count($result) == 0) {
      $result = array('0');
    };
    // Randomize order so no one mail can starve the rest in case of errors,
    // etc.
    if (count($result) > 1) {
      shuffle($result);
    };
    return $result;
  }// function getMissingIds
  /**
   * Simple <rowset> per API parser for XML.
   *
   * Most common API style is a simple <rowset>. This implementation allows most
   * API classes to be empty except for a constructor which sets $this->api and
   * calls their parent constructor.
   *
   * @return bool Returns TRUE if XML was parsered correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Set any column defaults needed.
    $qb->setDefault('ownerID', $this->ownerID);
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'row':
                $row = array();
                $row['messageID'] = $this->xr->getAttribute('messageID');
                //print 'string = ' . $this->xr->readString() . PHP_EOL;
                $row['body'] = $this->xr->readString();
                $qb->addRow($row);
                break;
            };// switch $this->xr->localName ...
            break;
          case XMLReader::END_ELEMENT:
            if ($this->xr->localName == 'result') {
              // Insert any leftovers.
              if (count($qb) > 0) {
                $qb->store();
              };// if count $rows ...
              $qb = NULL;
              return TRUE;
            };// if $this->xr->localName == 'row' ...
            break;
        };// switch $this->xr->nodeType
      };// while $xr->read() ...
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    trigger_error($mess, E_USER_WARNING);
    return FALSE;
  }// function parserAPI
}
?>
