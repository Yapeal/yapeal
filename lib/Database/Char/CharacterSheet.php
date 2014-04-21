<?php
/**
 * Contains CharacterSheet class.
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

use Yapeal\Database\AbstractChar;
use Yapeal\Database\DBConnection;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store CharacterSheet API.
 */
class CharacterSheet extends AbstractChar
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
     * Used to store XML to CharacterSheet's attributeEnhancers table.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function attributeEnhancers()
    {
        $tableName =
            YAPEAL_TABLE_PREFIX . $this->section . ucfirst(__FUNCTION__);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
        $row = array();
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'charismaBonus':
                        case 'intelligenceBonus':
                        case 'memoryBonus':
                        case 'perceptionBonus':
                        case 'willpowerBonus':
                            $row = array('ownerID' => $this->ownerID);
                            $row['bonusName'] = $this->xr->localName;
                            break;
                        case 'augmentatorName':
                        case 'augmentatorValue':
                            $name = $this->xr->localName;
                            $this->xr->read();
                            $row[$name] = $this->xr->value;
                            break;
                        default: // Nothing to do here.
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    switch ($this->xr->localName) {
                        case 'charismaBonus':
                        case 'intelligenceBonus':
                        case 'memoryBonus':
                        case 'perceptionBonus':
                        case 'willpowerBonus':
                            $qb->addRow($row);
                            break;
                        case 'attributeEnhancers':
                            return $qb->store();
                        default: // Nothing to do here.
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
     * Handles attributes table.
     *
     * @return bool Returns TRUE if data stored to database table.
     */
    protected function attributes()
    {
        $tableName =
            YAPEAL_TABLE_PREFIX . $this->section . ucfirst(__FUNCTION__);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
        $row = array('ownerID' => $this->ownerID);
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'charisma':
                        case 'intelligence':
                        case 'memory':
                        case 'perception':
                        case 'willpower':
                            $name = $this->xr->localName;
                            $this->xr->read();
                            $row[$name] = $this->xr->value;
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    if ($this->xr->localName == 'attributes') {
                        $qb->addRow($row);
                        return $qb->store();
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
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        $qb->setDefault('allianceName', '');
        $row = array();
        try {
            while ($this->xr->read()) {
                switch ($this->xr->nodeType) {
                    case \XMLReader::ELEMENT:
                        switch ($this->xr->localName) {
                            case 'allianceID':
                            case 'allianceName':
                            case 'ancestry':
                            case 'balance':
                            case 'bloodLine':
                            case 'characterID':
                            case 'cloneName':
                            case 'cloneSkillPoints':
                            case 'corporationID':
                            case 'corporationName':
                            case 'DoB':
                            case 'factionID':
                            case 'factionName':
                            case 'gender':
                            case 'name':
                            case 'race':
                                // Grab node name.
                                $name = $this->xr->localName;
                                if (($name == 'allianceName'
                                        || $name == 'factionName')
                                    && $this->xr->isEmptyElement == true
                                ) {
                                    $row[$name] = '';
                                } else {
                                    // Move to text node.
                                    $this->xr->read();
                                    $row[$name] = $this->xr->value;
                                }
                                break;
                            case 'attributes':
                            case 'attributeEnhancers':
                                // Check if empty.
                                if ($this->xr->isEmptyElement == true) {
                                    break;
                                }
                                // Grab node name.
                                $subTable = $this->xr->localName;
                                // Check for method with same name as node.
                                if (!is_callable(array($this, $subTable))) {
                                    $mess = 'Unknown what-to-be rowset '
                                        . $subTable;
                                    $mess .= ' found in ' . $this->api;
                                    \Logger::getLogger('yapeal')
                                           ->warn($mess);
                                    return false;
                                }
                                $this->$subTable();
                                break;
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
                                    \Logger::getLogger('yapeal')
                                           ->warn($mess);
                                    return false;
                                }
                                if ($subTable == 'skills') {
                                    $this->$subTable();
                                } else {
                                    $this->rowset($subTable);
                                }
                                break;
                            default: // Nothing to do here.
                        }
                        break;
                    case \XMLReader::END_ELEMENT:
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
        $tables = array(
            'Attributes',
            'AttributeEnhancers',
            'Certificates',
            'CorporationRoles',
            'CorporationRolesAtBase',
            'CorporationRolesAtHQ',
            'CorporationRolesAtOther',
            'CorporationTitles',
            'Skills'
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
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . ucfirst($table);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
        $qb->setDefault('ownerID', $this->ownerID);
        $row = array();
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'row':
                            // Walk through attributes and add them to row.
                            while ($this->xr->moveToNextAttribute()) {
                                $row[$this->xr->name] = $this->xr->value;
                            }
                            $qb->addRow($row);
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
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
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
    /**
     * Used to store XML to CharacterSheet's skills table.
     *
     * @return Bool Return TRUE if store was successful.
     */
    protected function skills()
    {
        $tableName =
            YAPEAL_TABLE_PREFIX . $this->section . ucfirst(__FUNCTION__);
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $qb->useUpsert(false);
        $defaults = array(
            'level' => 0,
            'ownerID' => $this->ownerID,
            'published' => 1
        );
        $qb->setDefaults($defaults);
        $row = array();
        while ($this->xr->read()) {
            switch ($this->xr->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->xr->localName) {
                        case 'row':
                            // Walk through attributes and add them to row.
                            while ($this->xr->moveToNextAttribute()) {
                                $row[$this->xr->name] = $this->xr->value;
                            }
                            $qb->addRow($row);
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
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
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
}

