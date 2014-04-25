<?php
/**
 * Contains StarbaseDetail class.
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
namespace Yapeal\Database\Corp;

use Yapeal\Caching\EveApiXmlCache;
use Yapeal\Database\AbstractCorp;
use Yapeal\Database\DBConnection;
use Yapeal\Database\QueryBuilder;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Network\NetworkConnection;

/**
 * Class used to fetch and store corp StarbaseDetail API.
 */
class StarbaseDetail extends AbstractCorp
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
        $this->api = basename(__CLASS__);
        parent::__construct($params);
    }
    /**
     * Used to store XML to MySQL table(s).
     *
     * @return Bool Return TRUE if store was successful.
     */
    public function apiStore()
    {
        $posList = $this->posList();
        if (false === $posList) {
            return false;
        }
        $ret = true;
        $this->prepareTables();
        foreach ($posList as $this->posID) {
            try {
                // Give each POS 60 seconds to finish. This should never happen but is
                // here to catch runaways.
                set_time_limit(60);
                // Need to add extra stuff to normal parameters to make walking work.
                $apiParams = $this->params;
                // This tells API server which tower we want.
                $apiParams['itemID'] = (string)$this->posID['itemID'];
                // First get a new cache instance.
                $cache = new EveApiXmlCache(
                    $this->api,
                    $this->section,
                    $this->ownerID,
                    $apiParams
                );
                // See if there is a valid cached copy of the API XML.
                $result = $cache->getCachedApi();
                // If it's not cached need to try to get it.
                if (false === $result) {
                    $proxy = $this->getProxy();
                    $con = new NetworkConnection();
                    $result = $con->retrieveEveApiXml($proxy, $apiParams);
                    // FALSE means there was an error and it has already been report so just
                    // return to caller.
                    if (false === $result) {
                        return false;
                    };
                    // Cache the received XML.
                    $cache->cacheXml($result);
                    // Check if XML is valid.
                    if (false === $cache->isValid()) {
                        // No use going any farther if the XML isn't valid.
                        $ret = false;
                        break;
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
                        if ($result === false) {
                            $ret = false;
                        }
                    }
                }
                $this->reader->close();
            } catch (YapealApiErrorException $e) {
                // Any API errors that need to be handled in some way are handled in
                // this function.
                $this->handleApiError($e);
                if ($e->getCode() == 114) {
                    $mess = 'Deleted ' . $this->posID['itemID'];
                    $mess .= ' from StarbaseList for ' . $this->ownerID;
                    $tableName =
                        YAPEAL_TABLE_PREFIX . $this->section . 'StarbaseList';
                    try {
                        $con = DBConnection::connect(YAPEAL_DSN);
                        $sql = 'DELETE FROM ';
                        $sql .= '`' . $tableName . '`';
                        $sql .= ' where `ownerID`=' . $this->ownerID;
                        $sql .= ' and `itemID`=' . $this->posID['itemID'];
                        $con->Execute($sql);
                    } catch (\ADODB_Exception $e) {
                        $mess = 'Could not delete ' . $this->posID['itemID'];
                        $mess .= ' from StarbaseList for ' . $this->ownerID;
                        \Logger::getLogger('yapeal')
                               ->warn($mess);
                        // Something wrong with query return FALSE.
                        return false;
                    }
                    \Logger::getLogger('yapeal')
                           ->warn($mess);
                }
                $ret = false;
                continue;
            } catch (\ADODB_Exception $e) {
                \Logger::getLogger('yapeal')
                       ->warn($e);
                $ret = false;
                continue;
            }
        }
        return $ret;
    }
    /**
     * Used to store XML to StarbaseDetail CombatSettings table.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function combatSettings()
    {
        $row = array('posID' => $this->posID['itemID']);
        while ($this->reader->read()) {
            switch ($this->reader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->reader->localName) {
                        case 'onAggression':
                        case 'onCorporationWar':
                        case 'onStandingDrop':
                        case 'onStatusDrop':
                        case 'useStandingsFrom':
                            // Save element name to use as prefix for attributes.
                            $prefix = $this->reader->localName;
                            // Walk through attributes and add them to row.
                            while ($this->reader->moveToNextAttribute()) {
                                $row[$prefix . ucfirst($this->reader->name)] =
                                    $this->reader->value;
                            }
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    if ($this->reader->localName == 'combatSettings') {
                        $this->combat->addRow($row);
                        return true;
                    }
                    break;
                default: // Nothing to do here.
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
    /**
     * Used to store XML to StarbaseDetail GeneralSettings table.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function generalSettings()
    {
        $row = array('posID' => $this->posID['itemID']);
        while ($this->reader->read()) {
            switch ($this->reader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->reader->localName) {
                        case 'allowAllianceMembers':
                        case 'allowCorporationMembers':
                        case 'deployFlags':
                        case 'usageFlags':
                            $name = $this->reader->localName;
                            $this->reader->read();
                            $row[$name] = $this->reader->value;
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    if ($this->reader->localName == 'generalSettings') {
                        $this->general->addRow($row);
                        return true;
                    }
                    break;
                default: // Nothing to do here.
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
    /**
     * Method used to determine if Need to use upsert or insert for API.
     *
     * @return bool
     */
    protected function needsUpsert()
    {
        return false;
    }
    /**
     * Per API parser for XML.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        $defaults = array('ownerID' => $this->ownerID);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
        $qb->setDefaults($defaults);
        // Get a new query instance.
        $this->combat = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'CombatSettings', YAPEAL_DSN
        );
        // Save some overhead for tables that are truncated or in some way emptied.
        $this->combat->useUpsert(false);
        $this->combat->setDefaults($defaults);
        // Get a new query instance.
        $this->fuel = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'Fuel', YAPEAL_DSN
        );
        // Save some overhead for tables that are truncated or in some way emptied.
        $this->fuel->useUpsert(false);
        $this->fuel->setDefaults($defaults);
        // Get a new query instance.
        $this->general = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'GeneralSettings', YAPEAL_DSN
        );
        // Save some overhead for tables that are truncated or in some way emptied.
        $this->general->useUpsert(false);
        $this->general->setDefaults($defaults);
        try {
            $ret = true;
            $row = array('posID' => $this->posID['itemID']);
            while ($this->reader->read()) {
                switch ($this->reader->nodeType) {
                    case \XMLReader::ELEMENT:
                        switch ($this->reader->localName) {
                            case 'onlineTimestamp':
                            case 'state':
                            case 'stateTimestamp':
                                // Grab node name.
                                $name = $this->reader->localName;
                                // Move to text node.
                                $this->reader->read();
                                $row[$name] = $this->reader->value;
                                break;
                            case 'combatSettings':
                            case 'generalSettings':
                                // Check if empty.
                                if ($this->reader->isEmptyElement == 1) {
                                    break;
                                }
                                // Grab node name.
                                $subTable = $this->reader->localName;
                                // Check for method with same name as node.
                                if (!is_callable(array($this, $subTable))) {
                                    $mess = 'Unknown what-to-be rowset '
                                        . $subTable;
                                    $mess .= ' found in ' . $this->api;
                                    \Logger::getLogger('yapeal')
                                           ->warn($mess);
                                    $ret = false;
                                    continue;
                                }
                                $result = $this->$subTable();
                                if (false === $result) {
                                    $ret = false;
                                }
                                break;
                            case 'rowset':
                                // Check if empty.
                                if ($this->reader->isEmptyElement == 1) {
                                    break;
                                }
                                // Grab rowset name.
                                $subTable = $this->reader->getAttribute('name');
                                if (empty($subTable)) {
                                    $mess = 'Name of rowset is missing in '
                                        . $this->api;
                                    \Logger::getLogger('yapeal')
                                           ->warn($mess);
                                    $ret = false;
                                    continue;
                                };
                                $result = $this->rowset($subTable);
                                if (false === $result) {
                                    $ret = false;
                                }
                                break;
                            default: // Nothing to do here.
                        }
                        break;
                    case \XMLReader::END_ELEMENT:
                        if ($this->reader->localName == 'result') {
                            $qb->addRow($row);
                            if (count($qb) > 0) {
                                $qb->store();
                            }
                            $qb = null;
                            if (count($this->combat) > 0) {
                                $this->combat->store();
                            }
                            $this->combat = null;
                            if (count($this->fuel) > 0) {
                                $this->fuel->store();
                            }
                            $this->fuel = null;
                            if (count($this->general) > 0) {
                                $this->general->store();
                            }
                            $this->general = null;
                            return $ret;
                        }
                        break;
                    default: // Nothing to do.
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
     * Get per corp list of starbases from corpStarbaseList.
     *
     * @return mixed List of itemIDs for this corp's POSes or FALSE if error or
     * no POSes found for corporation.
     */
    protected function posList()
    {
        try {
            $con = DBConnection::connect(YAPEAL_DSN);
            $sql = 'select `itemID`';
            $sql .= ' from ';
            $sql .= '`' . YAPEAL_TABLE_PREFIX . $this->section . 'StarbaseList'
                . '`';
            $sql .= ' where `ownerID`=' . $this->ownerID;
            $list = $con->GetAll($sql);
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                   ->warn($e);
            return false;
        }
        if (count($list) == 0) {
            return false;
        }
        // Randomize order so no one POS can starve the rest in case of
        // errors, etc.
        if (count($list) > 1) {
            shuffle($list);
        };
        return $list;
    }
    /**
     * Method used to prepare database table(s) before parsing API XML data.
     *
     * If there is any need to delete records or empty tables before parsing XML
     * and adding the new data this method should be used to do so.
     *
     * @return bool Will return TRUE if table(s) were prepared correctly.
     */
    protected function prepareTables()
    {
        $tables = array(
            'CombatSettings',
            'Fuel',
            'GeneralSettings',
            'StarbaseDetail'
        );
        foreach ($tables as $table) {
            try {
                $con = DBConnection::connect(YAPEAL_DSN);
                // Empty out old data then upsert (insert) new.
                $sql = 'DELETE FROM `';
                $sql .= YAPEAL_TABLE_PREFIX . $this->section . $table . '`';
                $sql .= ' where `ownerID`=' . $this->ownerID;
                $con->Execute($sql);
            } catch (\ADODB_Exception $e) {
                \Logger::getLogger('yapeal')
                       ->warn($e);
                return false;
            }
        }
        return true;
    }
    /**
     * Used to store XML to rowset tables.
     *
     * @param string $table Name of the table for this rowset.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function rowset($table)
    {
        while ($this->reader->read()) {
            switch ($this->reader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->reader->localName) {
                        case 'row':
                            $row = array('posID' => $this->posID['itemID']);
                            // Walk through attributes and add them to row.
                            while ($this->reader->moveToNextAttribute()) {
                                $row[$this->reader->name] =
                                    $this->reader->value;
                            }
                            $this->$table->addRow($row);
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    if ($this->reader->localName == 'rowset') {
                        return true;
                    }
                    break;
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
    /**
     * @var QueryBuilder Query instance for combatSettings table.
     */
    private $combat;
    /**
     * @var QueryBuilder Query instance for fuel table.
     */
    private $fuel;
    /**
     * @var QueryBuilder Query instance for generalSettings table.
     */
    private $general;
    /**
     * @var integer Holds current POS ID.
     */
    private $posID;
}

