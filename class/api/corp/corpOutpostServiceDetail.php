<?php
/**
 * Contains OutpostServiceDetail class.
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
 * Class used to fetch and store corp OutpostServiceDetail API.
 *
 * @package Yapeal
 * @subpackage Api_corp
 */
class corpOutpostServiceDetail extends ACorp {
  /**
   * @var integer Holds current Outpost ID.
   */
  private $outpostID;
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
    $outpostList = $this->outpostList();
    if (FALSE === $outpostList) {
      return FALSE;
    };// if FALSE ...
    $ret = TRUE;
    foreach ($outpostList as $this->outpostID) {
      try {
        // Need to add extra stuff to normal parameters to make walking work.
        $apiParams = $this->params;
        // This tells API server which outpost we want.
        $apiParams['itemID'] = (string)$this->outpostID['stationID'];
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
            return FALSE;
          };
        };// if FALSE === $result ...
        $this->prepareTables();
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
   * Get per corp list of outposts from corpOutpostList.
   *
   * @return array|bool List of stationIDs for this corp's outposts or FALSE if
   * error or no outposts found for corporation.
   */
  protected function outpostList() {
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql = 'select `stationID`';
      $sql .= ' from ';
      $sql .= '`' . YAPEAL_TABLE_PREFIX . $this->section . 'OutpostList' . '`';
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
    // Randomize order so no one Outpost can starve the rest in case of
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
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      // Empty out old data then upsert (insert) new.
      $sql = 'delete from `';
      $sql .= YAPEAL_TABLE_PREFIX . $this->section . $this->api . '`';
      $sql .= ' where `ownerID`=' . $this->ownerID;
      $con->Execute($sql);
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    return TRUE;
  }// function prepareTables
}

