<?php
/**
 * Contains killmails class.
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
use Yapeal\Caching\EveApiXmlCache;
use Yapeal\Database\AbstractChar;
use Yapeal\Database\QueryBuilder;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Network\NetworkConnection;

/**
 * Class used to fetch and store char KillMails API.
 */
class charKillMails extends AbstractChar
{
    /**
     * Constructor
     *
     * @param array $params Holds the required parameters like keyID, vCode, etc
     *                      used in HTML POST parameters to API servers which varies depending on API
     *                      'section' being requested.
     *
     * @throws LengthException for any missing required $params.
     */
    public function __construct(array $params)
    {
        // Cut off 'A' and lower case abstract class name to make section name.
        $this->section = strtolower(substr(get_parent_class($this), 1));
        $this->api = str_replace($this->section, '', __CLASS__);
        parent::__construct($params);
    }
    /**
     * Used to store XML to MySQL table(s).
     *
     * @return Bool Return TRUE if store was successful.
     */
    public function apiStore()
    {
        // This counter is used to insure do ... while can't become infinite loop.
        $counter = 1000;
        $this->date = gmdate('Y-m-d H:i:s', strtotime('1 hour'));
        $this->fromID = 0;
        $rowCount = 25;
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
                $oldest = gmdate('Y-m-d H:i:s', strtotime('7 days ago'));
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
                    $result = $con->retrieveXml($proxy, $apiParams);
                    // FALSE means there was an error and it has already been report so
                    // just return to caller.
                    if (false === $result) {
                        return false;
                    };
                    // Cache the received result.
                    $cache->cacheXml($result);
                    // Check if XML is valid.
                    if (false === $cache->isValid()) {
                        // No use going any farther if the XML isn't valid.
                        return false;
                    };
                }
                // Create XMLReader.
                $this->xr = new XMLReader();
                // Pass XML to reader.
                $this->xr->XML($result);
                // Outer structure of XML is processed here.
                while ($this->xr->read()) {
                    if ($this->xr->nodeType == XMLReader::ELEMENT
                        && $this->xr->localName == 'result'
                    ) {
                        $result = $this->parserAPI();
                    }
                }
                $this->xr->close();
                // Leave loop if already got as many entries as API servers allow.
                if ($this->rowCount != $rowCount || $this->date < $oldest) {
                    break;
                };
                // This tells API server where to start from when walking.
                $apiParams['fromID'] = $this->fromID;
            } while ($counter--);
        } catch (YapealApiErrorException $e) {
            // Any API errors that need to be handled in some way are handled in this
            // function.
            $this->handleApiError($e);
            return false;
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->error($e);
            return false;
        }
        return $result;
    }
    /**
     * @var QueryBuilder QueryBuilder instance for attackers table.
     */
    protected $attackers;
    /**
     * @var QueryBuilder QueryBuilder instance for items table.
     */
    protected $items;
    /**
     * @var QueryBuilder QueryBuilder instance for victim table.
     */
    protected $victim;
    /**
     * Used to store XML to attackers table.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function attack()
    {
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'row':
                            $row = array('killID' => $this->fromID);
                            // Walk through attributes and add them to row.
                            while ($this->xr->moveToNextAttribute()) {
                                $row[$this->xr->name] = $this->xr->value;
                            }
                            $this->attackers->addRow($row);
                            break;
                    }
                    break;
                case XMLReader::END_ELEMENT:
                    if ($this->xr->localName == 'rowset') {
                        return true;
                    }
                    break;
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        Logger::getLogger('yapeal')
              ->warn($mess);
        return false;
    }
    /**
     * Navigates XML and build nested sets to be added to table.
     *
     * The function adds addition columns to preserve the parent child
     * relationships of killID->items, killID->containers, containers->items, etc.
     * by using the nested set method.
     * For more information about nested set see these project wiki pages:
     * {@link http://code.google.com/p/yapeal/wiki/HierarchicalData HierarchicalData}
     * {@link http://code.google.com/p/yapeal/wiki/HierarchicalData2 HierarchicalData2}
     *
     * @author Michael Cummings <mgcummings@yahoo.com>
     *
     * @param array $inherit An array of stuff that needs to propagate from parent
     *                       to child.
     *
     * @return integer Current index for lft/rgt counting.
     */
    protected function nestedSet($inherit)
    {
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'row':
                            // Add some of the inherit values to $row and update them as needed.
                            $row = array(
                                'lft' => $inherit['index']++,
                                'lvl' => $inherit['level'],
                                'killID' => $inherit['killID']
                            );
                            // Walk through attributes and add them to row.
                            while ($this->xr->moveToNextAttribute()) {
                                $row[$this->xr->name] = $this->xr->value;
                            }
                            // Move back up to element.
                            $this->xr->moveToElement();
                            // Check if parent node.
                            if ($this->xr->isEmptyElement != 1) {
                                // Save parent on stack.
                                $this->stack[] = $row;
                                // Continue on to process children.
                                break;
                            }
                            // Add 'rgt' and increment value.
                            $row['rgt'] = $inherit['index']++;
                            // The $row is complete and ready to add.
                            $this->items->addRow($row);
                            break;
                        case 'rowset':
                            // Level increases with each parent rowset.
                            ++$inherit['level'];
                            break;
                        default:
                            break;
                    }
                    // switch $this->xr->localName ...
                    break;
                case XMLReader::END_ELEMENT:
                    switch ($this->xr->localName) {
                        case 'row':
                            $row = array_pop($this->stack);
                            // Add 'rgt' and increment value.
                            $row['rgt'] = $inherit['index']++;
                            // The $row is complete and ready to add.
                            $this->items->addRow($row);
                            break;
                        case 'rowset':
                            // Level decrease with end of each parent rowset.
                            --$inherit['level'];
                            if ($inherit['level'] == 0) {
                                // Return the final index value to parserAPI().
                                return $inherit['index'];
                            }
                            break;
                        default:
                            break;
                    }
                    // switch $this->xr->localName ...
                    break;
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        Logger::getLogger('yapeal')
              ->warn($mess);
        return $inherit['index'];
    }
    /**
     * Full implementation of multiple tables and nested sets from XML.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Get a new query instance for attackers.
        $this->attackers = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'Attackers', YAPEAL_DSN
        );
        // Get a new query instance for items.
        $this->items = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'Items', YAPEAL_DSN
        );
        $this->items->setDefault('singleton', 0);
        // Get a new query instance for victim.
        $this->victim = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'Victim', YAPEAL_DSN
        );
        $typeID = 0;
        try {
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'row':
                                /* This code should only be ran for outer 'kills' rows so might
                                 * be safe to assume the attribute can't be empty but didn't.
                                 */
                                $date = $this->xr->getAttribute('killTime');
                                // If this date is the oldest so far need to save date and
                                // killID to use in walking.
                                if (!empty($date) && $date < $this->date) {
                                    $this->date = $date;
                                    $this->fromID =
                                        $this->xr->getAttribute('killID');
                                }
                                $row = array();
                                // Walk through attributes and add them to row.
                                while ($this->xr->moveToNextAttribute()) {
                                    $row[$this->xr->name] = $this->xr->value;
                                }
                                $qb->addRow($row);
                                break;
                            case 'victim':
                                $row = array('killID' => $this->fromID);
                                // Walk through attributes and add them to row.
                                while ($this->xr->moveToNextAttribute()) {
                                    // Save the ship type to use for root node of items table.
                                    if ($this->xr->name == 'shipTypeID') {
                                        $typeID = $this->xr->value;
                                    };
                                    $row[$this->xr->name] = $this->xr->value;
                                }
                                $this->victim->addRow($row);
                                break;
                            case 'rowset':
                                // Check if empty.
                                if ($this->xr->isEmptyElement == 1) {
                                    break;
                                }
                                // Grab rowset name.
                                $subTable = $this->xr->getAttribute('name');
                                if (empty($subTable)) {
                                    $mess = 'Name of rowset is missing in '
                                        . $this->api;
                                    Logger::getLogger('yapeal')
                                          ->warn($mess);
                                    return false;
                                };
                                if ($subTable == 'items') {
                                    $inherit = array(
                                        'killID' => $this->fromID,
                                        'index' => 2,
                                        'level' => 1
                                    );
                                    $row = array(
                                        'flag' => 0,
                                        'killID' => $this->fromID,
                                        'lft' => 0,
                                        'lvl' => 0,
                                        'qtyDestroyed' => 1,
                                        'qtyDropped' => 0,
                                        'typeID' => $typeID
                                    );
                                    $row['rgt'] = $this->nestedSet($inherit);
                                    $this->items->addRow($row);
                                } else {
                                    if ($subTable == 'attackers') {
                                        $this->attack();
                                    }
                                }
                                break;
                            default: // Nothing to do here.
                        }
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->xr->localName == 'result') {
                            // Save row count and store rows.
                            $this->rowCount = count($qb);
                            if ($this->rowCount > 0) {
                                $qb->store();
                            }
                            $qb = null;
                            // Store rows.
                            if (count($this->victim) > 0) {
                                $this->victim->store();
                            }
                            $this->victim = null;
                            // Store rows.
                            if (count($this->attackers) > 0) {
                                $this->attackers->store();
                            }
                            $this->attackers = null;
                            // Store rows.
                            if (count($this->items) > 0) {
                                $this->items->store();
                            }
                            $this->items = null;
                            return true;
                        }
                        break;
                }
            }
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->error($e);
            return false;
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        Logger::getLogger('yapeal')
              ->warn($mess);
        return false;
    }
    /**
     * @var string Holds the date from each row in turn to use when walking.
     */
    private $date;
    /**
     * @var string Holds the refID from each row in turn to use when walking.
     */
    private $fromID;
    /**
     * @var integer Hold row count used in walking.
     */
    private $rowCount;
    /**
     * @var array Holds a stack of parent nodes until after their children are
     * processed.
     */
    private $stack = array();
}

