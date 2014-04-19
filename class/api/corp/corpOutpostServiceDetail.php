<?php
/**
 * Contains OutpostServiceDetail class.
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
use Yapeal\Database\DBConnection;
use Yapeal\Exception\YapealApiErrorException;

/**
 * Class used to fetch and store corp OutpostServiceDetail API.
 */
class corpOutpostServiceDetail extends ACorp
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
        $outpostList = $this->outpostList();
        if (false === $outpostList) {
            return false;
        }
        $ret = true;
        foreach ($outpostList as $this->outpostID) {
            try {
                // Need to add extra stuff to normal parameters to make walking work.
                $apiParams = $this->params;
                // This tells API server which outpost we want.
                $apiParams['itemID'] = (string)$this->outpostID['stationID'];
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
                    $con = new YapealNetworkConnection();
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
                $this->prepareTables();
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
                        if ($result === false) {
                            $ret = false;
                        }
                    }
                }
                $this->xr->close();
            } catch (YapealApiErrorException $e) {
                // Any API errors that need to be handled in some way are handled in
                // this function.
                $this->handleApiError($e);
                $ret = false;
                continue;
            } catch (ADODB_Exception $e) {
                Logger::getLogger('yapeal')
                      ->warn($e);
                $ret = false;
                continue;
            }
        }
        return $ret;
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
     * Get per corp list of outposts from corpOutpostList.
     *
     * @return array|bool List of stationIDs for this corp's outposts or FALSE if
     * error or no outposts found for corporation.
     */
    protected function outpostList()
    {
        try {
            $con = DBConnection::connect(YAPEAL_DSN);
            $sql = 'select `stationID`';
            $sql .= ' from ';
            $sql .= '`' . YAPEAL_TABLE_PREFIX . $this->section . 'OutpostList'
                . '`';
            $sql .= ' where `ownerID`=' . $this->ownerID;
            $list = $con->GetAll($sql);
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->warn($e);
            return false;
        }
        if (count($list) == 0) {
            return false;
        }
        // Randomize order so no one Outpost can starve the rest in case of
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
        try {
            $con = DBConnection::connect(YAPEAL_DSN);
            // Empty out old data then upsert (insert) new.
            $sql = 'DELETE FROM `';
            $sql .= YAPEAL_TABLE_PREFIX . $this->section . $this->api . '`';
            $sql .= ' where `ownerID`=' . $this->ownerID;
            $con->Execute($sql);
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->warn($e);
            return false;
        }
        return true;
    }
    /**
     * @var integer Holds current Outpost ID.
     */
    private $outpostID;
}

