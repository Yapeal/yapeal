<?php
/**
 * Contains NotificationTexts class.
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
 * Class used to fetch and store char NotificationTexts API.
 *
 * @package Yapeal
 * @subpackage Api_char
 */
class charNotificationTexts extends AChar {
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
        if (FALSE === $ids) {
          return FALSE;
        };
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
      Logger::getLogger('yapeal')->warn($mess);
      // Catch any uncaught ADOdb exceptions here.
      return FALSE;
    }
  }// function apiStore
  /**
   * Used to get a list of notification IDs.
   *
   * @return mixed Returns a list of notification IDs or FALSE on error.
   */
  protected function getIds() {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $sql = 'select n.`notificationID`';
    $sql .= ' from ';
    $sql .= '`' . YAPEAL_TABLE_PREFIX . 'charNotifications` as n';
    $sql .= ' left join `'. YAPEAL_TABLE_PREFIX . 'charNotificationTexts` as nt';
		$sql .= ' on (n.`ownerID` = nt.`ownerID` && n.`notificationID` = nt.`notificationID`)';
    $sql .= ' where';
    $sql .= ' nt.`notificationID` is null';
    $sql .= ' and n.`ownerID` = ' . $this->ownerID;
    try {
      $result = $con->getCol($sql);
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    if (count($result) == 0) {
      return FALSE;
    };
    // Randomize order so no one notification can starve the rest in case of errors,
    // etc.
    if (count($result) > 1) {
      shuffle($result);
    };
    return $result;
  }// function getMissingIds
  /**
   * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
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
                $row['notificationID'] = $this->xr->getAttribute('notificationID');
                $row['text'] = $this->xr->readString();
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
      Logger::getLogger('yapeal')->error($e);
      return FALSE;
    }
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function parserAPI
}

