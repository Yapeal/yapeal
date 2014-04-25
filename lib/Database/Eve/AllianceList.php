<?php
/**
 * Contains AllianceList class.
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
namespace Yapeal\Database\Eve;

use Yapeal\Database\AbstractEve;
use Yapeal\Database\DBConnection;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store AllianceList API.
 */
class AllianceList extends AbstractEve
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
     * Per API parser for XML.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
        // Get a new query instance.
        $this->corporations = new QueryBuilder(
            YAPEAL_TABLE_PREFIX . $this->section . 'MemberCorporations',
            YAPEAL_DSN
        );
        // Save some overhead for tables that are truncated or in some way emptied.
        $this->corporations->useUpsert(false);
        try {
            while ($this->reader->read()) {
                switch ($this->reader->nodeType) {
                    case \XMLReader::ELEMENT:
                        switch ($this->reader->localName) {
                            case 'row':
                                $row = array();
                                // Grab allianceID for memberCorporation table.
                                $allianceID =
                                    $this->reader->getAttribute('allianceID');
                                // Walk through attributes and add them to row.
                                while ($this->reader->moveToNextAttribute()) {
                                    $row[$this->reader->name] =
                                        $this->reader->value;
                                }
                                $qb->addRow($row);
                                // Process member corporations.
                                if ($this->reader->isEmptyElement != 1) {
                                    $this->rowset($allianceID);
                                }
                                break;
                            default: // Nothing to do.
                        }
                        break;
                    case \XMLReader::END_ELEMENT:
                        if ($this->reader->localName == 'result') {
                            // Insert any leftovers.
                            if (count($qb) > 0) {
                                $qb->store();
                            }
                            $qb = null;
                            // Insert any leftovers.
                            if (count($this->corporations) > 0) {
                                $this->corporations->store();
                            }
                            $this->corporations = null;
                            return true;
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
            $sql = 'truncate table `';
            $sql .= YAPEAL_TABLE_PREFIX . $this->section . $this->api . '`';
            $con->Execute($sql);
            // Empty out old data then upsert (insert) new.
            $sql = 'truncate table `';
            $sql .= YAPEAL_TABLE_PREFIX . $this->section . 'MemberCorporations'
                . '`';
            $con->Execute($sql);
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                   ->warn($e);
            return false;
        }
        return true;
    }
    /**
     * Used to store XML to rowset tables.
     *
     * @param string $allianceID ID of alliance that member corps belong to.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function rowset($allianceID)
    {
        while ($this->reader->read()) {
            switch ($this->reader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->reader->localName) {
                        case 'row':
                            $row = array('allianceID' => $allianceID);
                            // Walk through attributes and add them to row.
                            while ($this->reader->moveToNextAttribute()) {
                                $row[$this->reader->name] =
                                    $this->reader->value;
                            }
                            $this->corporations->addRow($row);
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
     * @var QueryBuilder Query instance for corporation rows to be added to table.
     */
    private $corporations;
}

