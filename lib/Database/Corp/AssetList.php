<?php
/**
 * Contains AssetList class.
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

use Yapeal\Database\AbstractCorp;
use Yapeal\Database\DBConnection;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store corp AssetList API.
 */
class AssetList extends AbstractCorp
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
     * Method used to determine if Need to use upsert or insert for API.
     *
     * @return bool
     */
    protected function needsUpsert()
    {
        return false;
    }
    /**
     * Navigates XML and build nested sets to be added to table.
     *
     * The function adds addition columns to preserve the parent child
     * relationships of location->hangers, location->containers, location->items,
     * location->hanger->items, etc. by using the nested set method.
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
        while ($this->reader->read()) {
            switch ($this->reader->nodeType) {
                case \XMLReader::ELEMENT:
                    switch ($this->reader->localName) {
                        case 'row':
                            // Add some of the inherit values to $row and update them as needed.
                            $row = array(
                                'lft' => $inherit['index']++,
                                'lvl' => $inherit['level'],
                                'locationID' => $inherit['locationID']
                            );
                            // Walk through attributes and add them to row.
                            while ($this->reader->moveToNextAttribute()) {
                                $row[$this->reader->name] =
                                    $this->reader->value;
                                // Save any new location so children can inherit it.
                                if ($this->reader->name == 'locationID') {
                                    $inherit['locationID'] =
                                        $this->reader->value;
                                }
                            }
                            // Move back up to element.
                            $this->reader->moveToElement();
                            // Check if parent node.
                            if ($this->reader->isEmptyElement != 1) {
                                // Save parent on stack.
                                $this->stack[] = $row;
                                // Continue on to process children.
                                break;
                            }
                            // Add 'rgt' and increment value.
                            $row['rgt'] = $inherit['index']++;
                            // The $row is complete and ready to add.
                            $this->qb->addRow($row);
                            break;
                        case 'rowset':
                            // Level increases with each parent rowset.
                            ++$inherit['level'];
                            break;
                        default:
                            break;
                    }
                    break;
                case \XMLReader::END_ELEMENT:
                    switch ($this->reader->localName) {
                        case 'result':
                            // Return the final index value to parserAPI().
                            return $inherit['index'];
                            break;
                        case 'row':
                            $row = array_pop($this->stack);
                            // Add 'rgt' and increment value.
                            $row['rgt'] = $inherit['index']++;
                            // The $row is complete and ready to add.
                            $this->qb->addRow($row);
                            break;
                        case 'rowset':
                            // Level decrease with end of each parent rowset.
                            --$inherit['level'];
                            break;
                    }
                    break;
            }
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return $inherit['index'];
    }
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
        $this->qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Save some overhead for tables that are truncated or in some way emptied.
        $this->qb->useUpsert(false);
        // Set any column defaults needed.
        $this->qb->setDefault('ownerID', $this->ownerID);
        $this->qb->setDefault('rawQuantity', 0);
        // Generate owner node as root for tree. It has to be added after all the
        // others to have corrected 'rgt'.
        $row = array(
            'flag' => '0',
            'itemID' => $this->ownerID,
            'lft' => '0',
            'locationID' => '0',
            'lvl' => '0',
            'ownerID' => $this->ownerID,
            'quantity' => '1',
            'singleton' => '0',
            'typeID' => '2'
        );
        $inherit = array('locationID' => '0', 'index' => 2, 'level' => 0);
        try {
            // Move through all the rows and add them to database.
            // The returned value is the updated 'rgt' value for the root node.
            $row['rgt'] = $this->nestedSet($inherit);
            // Add the root node with updated 'rgt'.
            $this->qb->addRow($row);
            // Insert root node and any leftovers.
            $this->qb->store();
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                   ->warn($e);
            return false;
        }
        return true;
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
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                   ->warn($e);
            return false;
        }
        return true;
    }
    /**
     * @var QueryBuilder Holds queryBuilder instance.
     */
    private $qb;
    /**
     * @var array Holds a stack of parent nodes until after their children are
     * processed.
     */
    private $stack = array();
}

