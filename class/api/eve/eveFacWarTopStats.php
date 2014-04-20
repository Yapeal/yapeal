<?php
/**
 * Contains FacWarTopStats class.
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
 * @author     Claus G. Pedersen <satissis@gmail.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings, Claus G. Pedersen
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
use Yapeal\Database\AEve;
use Yapeal\Database\DBConnection;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store eve FacWarTopStats API.
 */
class eveFacWarTopStats extends AEve
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
     * Handles totals from XML Note.
     *
     * @param string $table Name of the table to parse.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parseSubTable($table)
    {
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'rowset':
                            // Check if empty.
                            if ($this->xr->isEmptyElement == true) {
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
                            $this->rowset($table . $subTable);
                            break;
                    }
                    break;
                case XMLReader::END_ELEMENT:
                    switch ($this->xr->localName) {
                        case 'characters':
                        case 'corporations':
                        case 'factions':
                            return true;
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
     * API parser for XML.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section;
        try {
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'characters':
                            case 'corporations':
                            case 'factions':
                                // Check if empty.
                                if ($this->xr->isEmptyElement == true) {
                                    break;
                                }
                                // Parse node into its own table.
                                $this->parseSubTable(
                                    $tableName . ucfirst($this->xr->localName)
                                );
                                break;
                            default: // Nothing to do here.
                        }; // $this->xr->localName ...
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->xr->localName == 'result') {
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
            'CharactersKillsLastWeek',
            'CharactersKillsTotal',
            'CharactersKillsYesterday',
            'CharactersVictoryPointsLastWeek',
            'CharactersVictoryPointsTotal',
            'CharactersVictoryPointsYesterday',
            'CorporationsKillsLastWeek',
            'CorporationsKillsTotal',
            'CorporationsKillsYesterday',
            'CorporationsVictoryPointsLastWeek',
            'CorporationsVictoryPointsTotal',
            'CorporationsVictoryPointsYesterday',
            'FactionsKillsLastWeek',
            'FactionsKillsTotal',
            'FactionsKillsYesterday',
            'FactionsVictoryPointsLastWeek',
            'FactionsVictoryPointsTotal',
            'FactionsVictoryPointsYesterday'
        );
        foreach ($tables as $table) {
            try {
                $con = DBConnection::connect(YAPEAL_DSN);
                // Empty out old data then upsert (insert) new.
                $sql = 'TRUNCATE TABLE `';
                $sql .= YAPEAL_TABLE_PREFIX . $this->section . $table . '`';
                $con->Execute($sql);
            } catch (ADODB_Exception $e) {
                Logger::getLogger('yapeal')
                      ->warn($e);
                return false;
            }
        }
        return true;
    }
    /**
     * Used to store XML to rowset tables.
     *
     * @param string $tableName Name of the table for this rowset.
     *
     * @return bool Returns TRUE if store was successful.
     */
    protected function rowset($tableName)
    {
        //$tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst($table);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
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

