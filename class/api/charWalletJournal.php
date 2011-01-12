<?php
/**
 * Contains WalletJournal class.
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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
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
 * Class used to fetch and store char WalletJournal API.
 *
 * @package Yapeal
 * @subpackage Api_char
 */
class charWalletJournal extends AChar {
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
  private $rowCount;
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
    // This counter is used to insure do ... while can't become infinite loop.
    $counter = 1000;
    $this->date = '1970-01-01 00:00:01';
    $this->beforeID = 0;
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
        $oldest = gmdate('Y-m-d H:i:s', strtotime('7 days ago'));
        // Need to add extra stuff to normal parameters to make walking work.
        $apiParams = $this->params;
        // Added the accountKey to params.
        $apiParams['accountKey'] = 1000;
        // This tells API server where to start from when walking.
        $apiParams['beforeRefID'] = $this->beforeID;
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
        // Leave loop if already got as many entries as API servers allow.
        if ($this->rowCount != 1000 || $this->date < $oldest) {
          break;
        };
      } while ($counter--);
    }
    catch (YapealApiErrorException $e) {
      // Any API errors that need to be handled in some way are handled in this
      // function.
      $this->handleApiError($e);
      return FALSE;
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
    return $result;
  }// function apiStore
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
    $defaults = array('accountKey' => 1000, 'ownerID' => $this->ownerID);
    $qb->setDefaults($defaults);
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'row':
                // Walk through attributes and add them to row.
                while ($this->xr->moveToNextAttribute()) {
                  $row[$this->xr->name] = $this->xr->value;
                  switch ($this->xr->name) {
                    case 'date':
                      // Save date for walking.
                      $this->date = $this->xr->value;
                      break;
                    case 'refID':
                      // Save refID for walking.
                      $this->beforeID = $this->xr->value;
                      break;
                    case 'taxReceiverID':
                    case 'taxAmount':
                      // Fix blank with zero for upsert.
                      if ($this->xr->value === '') {
                        $row[$this->xr->name] = 0;
                      };// if $this->xr->value ...
                      break;
                    default:// Nothing to do here.
                  };// switch $this->xr->name ...
                };// while $this->xr->moveToNextAttribute() ...
                $qb->addRow($row);
                break;
            };// switch $this->xr->localName ...
            break;
          case XMLReader::END_ELEMENT:
            if ($this->xr->localName == 'result') {
              // Save row count and store rows.
              if ($this->rowCount = count($qb) > 0) {
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
