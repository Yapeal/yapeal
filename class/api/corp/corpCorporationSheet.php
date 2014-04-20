<?php
/**
 * Contains CorporationSheet class.
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
use Yapeal\Database\ACorp;
use Yapeal\Database\QueryBuilder;
use Yapeal\Exception\YapealApiErrorException;
use Yapeal\Network\NetworkConnection;

/**
 * Class used to fetch and store CorporationSheet API.
 */
class corpCorporationSheet extends ACorp
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
        // Need to exclude some params when passing them to API server so it doesn't
        // get confused.
        $apiParams = $this->params;
        unset($apiParams['corporationID']);
        // First get a new cache instance.
        $cache = new EveApiXmlCache(
            $this->api,
            $this->section,
            $this->ownerID,
            $apiParams
        );
        try {
            // See if there is a valid cached copy of the API XML.
            $result = $cache->getCachedApi();
            // If it's not cached need to try to get it.
            if (false === $result) {
                $proxy = $this->getProxy();
                $con = new NetworkConnection();
                $result = $con->retrieveXml($proxy, $apiParams);
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
                    return false;
                };
            }
            // Create XMLReader.
            $this->xr = new XMLReader();
            // Pass XML to reader.
            $this->xr->XML($result);
            $cuntil = '';
            // Outer structure of XML is processed here.
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'currentTime':
                                break;
                            case 'result':
                                // Call the per API parser.
                                $result = $this->parserAPI();
                                break;
                            case 'cachedUntil':
                                $this->xr->read();
                                $cuntil = $this->xr->value;
                                break;
                        }
                        break;
                    case XMLReader::END_ELEMENT:
                        break;
                }
            }
            // Update CachedUntil time since we should have a new one.
            $data = array(
                'api' => $this->api,
                'cachedUntil' => $cuntil,
                'ownerID' => $this->ownerID,
                'section' => $this->section
            );
            $cu = new CachedUntil($data);
            $cu->store();
            $this->xr->close();
            return $result;
        } catch (YapealApiErrorException $e) {
            // Any API errors that need to be handled in some way are handled in this
            // function.
            $this->handleApiError($e);
            return false;
        } catch (ADODB_Exception $e) {
            $mess = 'Uncaught ADOdb exception' . PHP_EOL;
            Logger::getLogger('yapeal')
                  ->warn($mess);
            // Catch any uncaught ADOdb exceptions here.
            return false;
        }
    }
    /**
     * Used to store XML to CorporationSheet's logo table.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function logo()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . 'Logo';
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        $qb->setDefault('ownerID', $this->ownerID);
        $row = array();
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'color1':
                        case 'color2':
                        case 'color3':
                        case 'graphicID':
                        case 'shape1':
                        case 'shape2':
                        case 'shape3':
                            $name = $this->xr->localName;
                            $this->xr->read();
                            $row[$name] = $this->xr->value;
                            break;
                    }
                    break;
                case XMLReader::END_ELEMENT:
                    if ($this->xr->localName == 'logo') {
                        $qb->addRow($row);
                        return $qb->store();
                    }
                    break;
                default: // Nothing to do here.
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        Logger::getLogger('yapeal')
              ->warn($mess);
        return false;
    }
    /**
     * Per API parser for XML.
     *
     * @return bool Returns TRUE if XML was parsered correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        $qb->setDefault('allianceName', '');
        $row = array();
        try {
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'allianceID':
                            case 'allianceName':
                            case 'ceoID':
                            case 'ceoName':
                            case 'corporationID':
                            case 'corporationName':
                            case 'description':
                            case 'memberCount':
                            case 'memberLimit':
                            case 'shares':
                            case 'stationID':
                            case 'stationName':
                            case 'taxRate':
                            case 'ticker':
                            case 'url':
                                // Grab node name.
                                $name = $this->xr->localName;
                                // Move to text node.
                                $this->xr->read();
                                $row[$name] = $this->xr->value;
                                break;
                            case 'logo':
                                // Check if empty.
                                if ($this->xr->isEmptyElement == 1) {
                                    break;
                                }
                                // Grab node name.
                                $subTable = $this->xr->localName;
                                // Check for method with same name as node.
                                if (!is_callable(array($this, $subTable))) {
                                    $mess = 'Unknown what-to-be rowset '
                                        . $subTable;
                                    $mess .= ' found in ' . $this->api;
                                    Logger::getLogger('yapeal')
                                          ->warn($mess);
                                    return false;
                                };
                                $this->$subTable();
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
                                $this->rowset($subTable);
                                break;
                            default: // Nothing to do here.
                        }; // $this->xr->localName ...
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->xr->localName == 'result') {
                            $qb->addRow($row);
                            if (count($qb) > 0) {
                                $qb->store();
                            }
                            $qb = null;
                            return true;
                        }
                        break;
                    default: // Nothing to do.
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
     * Used to store XML to rowset tables.
     *
     * @param string $table Name of the table for this rowset.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function rowset($table)
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst($table);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        $qb->setDefault('ownerID', $this->ownerID);
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'row':
                            $row = array();
                            // Walk through attributes and add them to row.
                            while ($this->xr->moveToNextAttribute()) {
                                $row[$this->xr->name] = $this->xr->value;
                            }
                            $qb->addRow($row);
                            break;
                    }
                    break;
                case XMLReader::END_ELEMENT:
                    if ($this->xr->localName == 'rowset') {
                        // Insert any leftovers.
                        if (count($qb) > 0) {
                            $qb->store();
                        }
                        $qb = null;
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
}

