<?php
/**
 * Contains WalletTransactions class.
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
  } else {
    fwrite(STDERR, $mess);
    exit(1);
  }
};
/**
 * Class used to fetch and store char WalletTransactions API.
 *
 * @package Yapeal
 * @subpackage Api_char
 */
class charWalletTransactions extends AChar {
  /**
   * @var string Holds the refID from each row in turn to use when walking.
   */
  protected $beforeID;
  /**
   * @var string Holds the date from each row in turn to use when walking.
   */
  protected $date;
  /**
   * @var integer Hold row count used in walking.
   */
  protected $rowCount;
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
    /* This counter is used to insure do ... while can't become infinite loop.
     * Using 1000 means at most last 255794 rows can be retrieved. That works
     * out to over 355 entries per hour over the maximum 30 days allowed by
     * the API servers. If you have a corp or char with more than that please
     * contact me for addition help with Yapeal.
     */
    $counter = 1000;
    $this->date = gmdate('Y-m-d H:i:s', strtotime('1 hour'));
    $this->beforeID = '0';
    $rowCount = 250;
    $first = TRUE;
    // Need to add extra stuff to normal parameters to make walking work.
    $apiParams = $this->params;
    try {
      do {
        // Give each API 60 seconds to finish. This should never happen but is
        // here to catch runaways.
        set_time_limit(60);
        /* Not going to assume here that API servers figure oldest allowed
         * entry based on a saved time from first pull but instead use current
         * time. The few seconds of difference shouldn't cause any missed data
         * and is safer than assuming.
         */
        $oldest = gmdate('Y-m-d H:i:s', strtotime('30 days ago'));
        // Added the accountKey to params.
        $apiParams['accountKey'] = 1000;
        // This tells API server how many rows we want.
        $apiParams['rowCount'] = $rowCount;
        // First get a new cache instance.
        $cache = new YapealApiCache($this->api, $this->section, $this->ownerID,
          $apiParams);
        // See if there is a valid cached copy of the API XML.
        $result = $cache->getCachedApi();
        // If it's not cached need to try to get it.
        if (FALSE === $result) {
          $proxy = $this->getProxy();
          $con = new YapealNetworkConnection();
          $result = $con->retrieveXml($proxy, $apiParams);
          // FALSE means there was an error and it has already been report so
          // just return to caller.
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
        /* There are two normal conditions to end walking. They are:
         * Got less rows than expected because there are no more to get while
         * walking backwards.
         * The oldest row we got is oldest API allows us to get.
         */
        if (($first === FALSE && $this->rowCount != $rowCount)
          || $this->date < $oldest) {
          // Have to break while.
          break;
        };
        // This tells API server where to start from when walking backwards.
        $apiParams['fromID'] = $this->beforeID;
        $first = FALSE;
      } while ($counter--);
    }
    catch (YapealApiErrorException $e) {
      // Any API errors that need to be handled in some way are handled in this
      // function.
      $this->handleApiError($e);
      return FALSE;
    }
    return $result;
  }// function apiStore
  /**
   * Parsers the XML from API.
   *
   * Most common API style is a simple <rowset>. Transactions are a little more
   * complex because of need to do walking back for older records.
   *
   * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance with autoStore off.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Set any column defaults needed.
    $defaults = array('accountKey' => 1000, 'ownerID' => $this->ownerID);
    $qb->setDefaults($defaults);
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'row':
                /* The following assumes the transactionDateTime attribute
                 * exists and is not empty and the same is true for
                 * transactionID. Since XML would be invalid if ether were true
                 * they should never return bad values.
                 */
                $date = $this->xr->getAttribute('transactionDateTime');
                // If this date is the oldest so far need to save
                // transactionDateTime and transactionID to use in walking.
                if ($date < $this->date) {
                  $this->date = $date;
                  $this->beforeID = $this->xr->getAttribute('transactionID');
                };// if $date ...
                $row = array();
                // Walk through attributes and add them to row.
                while ($this->xr->moveToNextAttribute()) {
                  $row[$this->xr->name] = $this->xr->value;
                };// while $this->xr->moveToNextAttribute() ...
                $qb->addRow($row);
                break;
            };// switch $this->xr->localName ...
            break;
          case XMLReader::END_ELEMENT:
            if ($this->xr->localName == 'result') {
              // Save row count and store rows.
              $this->rowCount = count($qb);
              if ($this->rowCount > 0) {
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

