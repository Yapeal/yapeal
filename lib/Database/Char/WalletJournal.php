<?php
/**
 * Contains WalletJournal class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Database\Char;

use Yapeal\Caching\EveApiXmlCache;
use Yapeal\Database\AbstractChar;
use Yapeal\Database\QueryBuilder;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Network\NetworkConnection;

/**
 * Class used to fetch and store char WalletJournal API.
 */
class WalletJournal extends AbstractChar
{
    /**
     * Constructor
     *
     * @param array $params Holds the required parameters like keyID, vCode, etc
     *                      used in HTML POST parameters to API servers which varies depending on API
     *                      'section' being requested.
     *
     * @throws \LengthException for any missing required $params.
     */
    public function __construct(array $params)
    {
        $this->section = strtolower(basename(__DIR__));
        $this->api = basename(str_replace('\\', '/', __CLASS__));
        parent::__construct($params);
    }
    /**
     * Used to store XML to MySQL table(s).
     *
     * @return Bool Return TRUE if store was successful.
     */
    public function apiStore()
    {
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
        $first = true;
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
                $cache = new EveApiXmlCache(
                    $this->api, $this->section, $this->ownerID,
                    $apiParams
                );
                // See if there is a valid cached copy of the API XML.
                $result = $cache->getCachedApi();
                // If it's not cached need to try to get it.
                if (false === $result) {
                    $proxy = $this->getProxy();
                    $con = new NetworkConnection();
                    $result = $con->retrieveEveApiXml($proxy, $apiParams);
                    // FALSE means there was an error and it has already been report so
                    // just return to caller.
                    if (false === $result) {
                        return false;
                    };
                    // Cache the received XML.
                    $cache->cacheXml($result);
                    // Check if XML is valid.
                    if (false === $cache->isValid()) {
                        // No use going any farther if the XML isn't valid.
                        return false;
                    };
                }
                // Create XMLReader.
                $this->reader = new \XMLReader();
                // Pass XML to reader.
                $this->reader->XML($result);
                // Outer structure of XML is processed here.
                while ($this->reader->read()) {
                    if ($this->reader->nodeType == \XMLReader::ELEMENT
                        && $this->reader->localName == 'result'
                    ) {
                        $result = $this->parserAPI();
                    }
                }
                $this->reader->close();
                /* There are two normal conditions to end walking. They are:
                 * Got less rows than expected because there are no more to get while
                 * walking backwards.
                 * The oldest row we got is oldest API allows us to get.
                 */
                if (($first === false && $this->rowCount != $rowCount)
                    || $this->date < $oldest
                ) {
                    // Have to break while.
                    break;
                };
                // This tells API server where to start from when walking backwards.
                $apiParams['fromID'] = $this->beforeID;
                $first = false;
            } while ($counter--);
        } catch (YapealApiErrorException $e) {
            // Any API errors that need to be handled in some way are handled in this
            // function.
            $this->handleApiError($e);
            return false;
        }
        return $result;
    }
    /**
     * @var string Holds the refID from each row in turn to use when walking.
     */
    protected $beforeID;
    /**
     * @var string Holds the date from each row in turn to use when walking.
     */
    protected $date;
    /**
     * Parsers the XML from API.
     *
     * Most common API style is a simple <rowset>. Journals are a little more
     * complex because of need to do walking back for older records.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance with autoStore off.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Set any column defaults needed.
        $defaults = array('accountKey' => 1000, 'ownerID' => $this->ownerID);
        $qb->setDefaults($defaults);
        try {
            while ($this->reader->read()) {
                switch ($this->reader->nodeType) {
                    case \XMLReader::ELEMENT:
                        switch ($this->reader->localName) {
                            case 'row':
                                /* The following assumes the date attribute exists and is not
                                 * empty and the same is true for refID. Since XML would be
                                 * invalid if ether were true they should never return bad
                                 * values.
                                 */
                                $date = $this->reader->getAttribute('date');
                                // If this date is the oldest so far need to save date and refID
                                // to use in walking.
                                if ($date < $this->date) {
                                    $this->date = $date;
                                    $this->beforeID =
                                        $this->reader->getAttribute('refID');
                                }
                                $row = array();
                                // Walk through attributes and add them to row.
                                while ($this->reader->moveToNextAttribute()) {
                                    $row[$this->reader->name] =
                                        $this->reader->value;
                                    switch ($this->reader->name) {
                                        case 'taxReceiverID':
                                        case 'taxAmount':
                                            // Fix blank with zero for upsert.
                                            if ($this->reader->value === '') {
                                                $row[$this->reader->name] = 0;
                                            }
                                            break;
                                        default: // Nothing to do here.
                                    }
                                }
                                $qb->addRow($row);
                                break;
                        }
                        break;
                    case \XMLReader::END_ELEMENT:
                        if ($this->reader->localName == 'result') {
                            // Save row count and store rows.
                            $this->rowCount = count($qb);
                            if ($this->rowCount > 0) {
                                $qb->store();
                            }
                            $qb = null;
                            return true;
                        }
                        break;
                }
            }
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                   ->error($e);
            return false;
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
    /**
     * @var integer Hold row count used in walking.
     */
    private $rowCount;
}

