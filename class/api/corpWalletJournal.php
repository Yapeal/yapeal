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
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Class used to fetch and store corp WalletJournal API.
 *
 * @package Yapeal
 * @subpackage Api_corp
 */
class corpWalletJournal extends ACorp {
  /**
   * @var integer Holds the current wallet account.
   */
  protected $account;
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
    print 'Got here for owner = ' . $this->ownerID . PHP_EOL;
    $ret = TRUE;
    $accounts = range(1000, 1006);
    shuffle($accounts);
    $future = gmdate('Y-m-d H:i:s', strtotime('1 hour'));
    foreach ($accounts as $k => $this->account) {
      /* This counter is used to insure do ... while can't become infinite loop.
       * Using 1000 means at most last 255794 rows can be retrieved. That works
       * out to over 355 entries per hour over the maximum 30 days allowed by
       * the API servers. If you have a corp or char with more than that please
       * contact me for addition help with Yapeal.
       */
      $counter = 1000;
      // Use an hour in the future as date and let $this->parserAPI() finds the
      // oldest available date from the XML.
      $this->date = $future;
      $this->beforeID = 0;
      // Only try to get a few rows the first time.
      $rowCount = 32;
      // SQL use to find actual number of records for this owner and account.
      $sql = 'select sum(if(`ownerID`=' . $this->ownerID . ',1,0))';
      $sql .= ' from ' . YAPEAL_TABLE_PREFIX . $this->section . $this->api;
      $sql .= ' where `accountKey`=' . $this->account;
      try {
        // Need database connection to do some counting.
        $dbCon = YapealDBConnection::connect(YAPEAL_DSN);
        do {
          // Give each wallet 60 seconds to finish. This should never happen but
          // is here to catch runaways.
          set_time_limit(60);
          /* Not going to assume here that API servers figure oldest allowed
           * entry based on a saved time from first pull but instead use current
           * time. The few seconds of difference shouldn't cause any missed data
           * and is safer than assuming.
           */
          $oldest = gmdate('Y-m-d H:i:s', strtotime('30 days ago'));
          // Need to add extra stuff to normal parameters to make walking work.
          $apiParams = $this->params;
          // Added the accountKey to params.
          $apiParams['accountKey'] = $this->account;
          // This tells API server where to start from when walking.
          $apiParams['fromID'] = $this->beforeID;
          // This tells API server how many rows we want.
          $apiParams['rowCount'] = $rowCount;
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
              $ret = FALSE;
              // No use going any farther if the XML isn't valid.
              // Have to continue with next account not just break while.
              continue 2;
            };
          };// if FALSE === $result ...
          // Create XMLReader.
          $this->xr = new XMLReader();
          // Pass XML to reader.
          $this->xr->XML($result);
          // Calculate how many records there should be if have no dups in XML.
          $expectedCount = $dbCon->GetOne($sql) + $rowCount;
          print 'Expected = ' . $expectedCount . PHP_EOL;
          // Outer structure of XML is processed here.
          while ($this->xr->read()) {
            if ($this->xr->nodeType == XMLReader::ELEMENT &&
              $this->xr->localName == 'result') {
              $result = $this->parserAPI();
              if ($result === FALSE) {
                $ret = FALSE;
                $this->xr->close();
                continue 2;
              };// if $result ...
            };// if $this->xr->nodeType ...
          };// while $this->xr->read() ...
          $this->xr->close();
          $actual = $dbCon->GetOne($sql) + 0;
          print 'Actual = ' . $actual . PHP_EOL;
          /* There are three normal conditions to end walking. They are:
           * Got less rows than expected because there are no more to get.
           * The oldest row we got is oldest API allows us to get.
           * Some of the rows are duplicates of existing records and there is no
           * reason to waste any time walking back to get more.
           */
          if ($this->rowCount != $rowCount || $this->date < $oldest
            || $actual < $expectedCount) {
            // Have to continue with next account not just break while.
            continue 2;
          };
          /* Get less rows at first but keep getting more until we hit maximum.
           * Wastes some time when doing initial walk for new owners but works
           * well after that.
           */
          if ($rowCount < 129) {
            $rowCount *= 2;
          } else {
            $rowCount = 256;
          };
          // Give API servers time to figure out we got last one before trying
          // to walk back for more.
          sleep(2);
        } while ($counter--);
      }
      catch (YapealApiErrorException $e) {
        // Any API errors that need to be handled in some way are handled in
        // this function.
        $this->handleApiError($e);
        $ret = FALSE;
        // Break out of foreach as once one wallet returns an error they all do.
        break;
      }
      catch (ADODB_Exception $e) {
        $ret = FALSE;
        continue;
      }
    };// foreach range(1000, 1006) ...
    return $ret;
  }// function apiStore
  /**
   * Parsers the XML from API.
   *
   * Most common API style is a simple <rowset>. Journals are a little more
   * complex because of need to do walking back for older records.
   *
   * @return bool Returns TRUE if XML was parsered correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Set any column defaults needed.
    $defaults = array('accountKey' => $this->account,
      'ownerID' => $this->ownerID
    );
    $qb->setDefaults($defaults);
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'row':
                /* The following assumes the date attribute exists and is not
                 * empty and the same is true for refID. Since XML would be
                 * invalid if ether were true they should never return bad
                 * values.
                 */
                $date = $this->xr->getAttribute('date');
                // If this date is the oldest so far need to save date and refID
                // to use in walking.
                if ($date < $this->date) {
                  $this->date = $date;
                  $this->beforeID = $this->xr->getAttribute('refID');
                };// if $date ...
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
