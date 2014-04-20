<?php
/**
 * Contains Contracts class.
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
use Yapeal\Database\AbstractCorp;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store corp StarbaseList API.
 */
class corpContracts extends AbstractCorp
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
     * Parsers the XML from API.
     *
     * Most common API style is a simple <rowset>. Journals are a little more
     * complex because of need to do walking back for older records.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        if (Logger::getLogger('yapeal')
                  ->isDebugEnabled()
        ) {
            Logger::getLogger('yapeal')
                  ->trace(__METHOD__);
        };
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance with autoStore off.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Set any column defaults needed.
        $qb->setDefault('ownerID', $this->ownerID);
        try {
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'row':
                                $row = array();
                                // Walk through attributes and add them to row.
                                while ($this->xr->moveToNextAttribute()) {
                                    // Allow QueryBuilder to handle NULL columns.
                                    if (($this->xr->name == 'dateAccepted'
                                            ||
                                            $this->xr->name == 'dateCompleted')
                                        && $this->xr->value == ''
                                    ) {
                                        continue;
                                    };
                                    $row[$this->xr->name] = $this->xr->value;
                                }
                                $qb->addRow($row);
                                break;
                        }
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->xr->localName == 'result') {
                            if (count($qb) > 0) {
                                $qb->store();
                            }
                            $qb = null;
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
}

