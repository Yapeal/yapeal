<?php
/**
 * Contains corp ContainerLog class.
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
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
use Yapeal\Database\QueryBuilder;

/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
    highlight_file(__FILE__);
    exit();
};
/**
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
    $mess = basename(__FILE__)
        . ' must be included it can not be ran directly.' . PHP_EOL;
    if (PHP_SAPI != 'cli') {
        header('HTTP/1.0 403 Forbidden', true, 403);
        die($mess);
    } else {
        fwrite(STDERR, $mess);
        exit(1);
    }
};
/**
 * Class used to fetch and store corp ContainerLog API.
 *
 * @package    Yapeal
 * @subpackage Api_corp
 */
class corpContainerLog extends ACorp
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
    }// function __construct
    /**
     * Simple <rowset> per API parser for XML.
     *
     * Most common API style is a simple <rowset>. This implementation allows most
     * API classes to be empty except for a constructor which sets $this->api and
     * calls their parent constructor.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Set any column defaults needed.
        $qb->setDefault('ownerID', $this->ownerID);
        $row = array();
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
                                        case 'newConfiguration':
                                        case 'oldConfiguration':
                                        case 'quantity':
                                        case 'typeID':
                                            // Fix empty values with zero.
                                            if ($this->xr->value == '') {
                                                $row[$this->xr->name] = 0;
                                            }
                                            break;
                                        default: // Nothing to do.
                                    }; // switch $this->xr->name ...
                                }; // while $this->xr->moveToNextAttribute() ...
                                $qb->addRow($row);
                                break;
                        }; // switch $this->xr->localName ...
                        break;
                    case XMLReader::END_ELEMENT:
                        if ($this->xr->localName == 'result') {
                            // Insert any leftovers.
                            if (count($qb) > 0) {
                                $qb->store();
                            }; // if count $rows ...
                            $qb = null;
                            return true;
                        }; // if $this->xr->localName == 'row' ...
                        break;
                }; // switch $this->xr->nodeType
            }; // while $xr->read() ...
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
    // function parserAPI
}

