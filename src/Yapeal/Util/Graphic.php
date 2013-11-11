<?php
/**
 * Contains Graphic class.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2013, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Util;

use Yapeal\Database\DatabaseConnection;
use Yapeal\Database\QueryBuilder;

/**
 * Wrapper class for utilGraphic table.
 *
 * @package    Yapeal\Util
 * @deprecated This class and table were once use by an old legacy web installer
 *             that has NOT existed in Yapeal for a long time. This is probably
 *             the last version to include them.
 */
class Graphic extends ALimitedObject implements IGetBy
{
    /**
     * @var string Holds an instance of the DB connection.
     */
    protected $con;
    /**
     * Holds query builder object.
     *
     * @var object
     */
    protected $qb;
    /**
     * @var string Holds the table name of the query is being built.
     */
    protected $tableName;
    /**
     * Set to TRUE if a database record exists.
     *
     * @var bool
     */
    private $recordExists;
    /**
     * Constructor
     *
     * @param int|string $id     Id of user wanted.
     * @param bool       $create When $create is set to FALSE will throw DomainException
     *                           if $id does not exist in database.
     *
     * @throws \InvalidArgumentException If $id isn't a number throws an
     * InvalidArgumentException.
     * @throws \DomainException If $create is FALSE and a database record for $id
     * does not exist a DomainException will be thrown.
     * @throws \RuntimeException Throws RuntimeException if fails to get database
     * connection.
     */
    public function __construct($id = null, $create = true)
    {
        $this->tableName = YAPEAL_TABLE_PREFIX . 'util' . __CLASS__;
        try {
            // Get a database connection.
            $this->con = DatabaseConnection::connect(YAPEAL_DSN);
        } catch (\ADODB_Exception $e) {
            $mess = 'Failed to get database connection in ' . __CLASS__;
            throw new \RuntimeException($mess);
        }
        // Get a new query builder object.
        $this->qb = new QueryBuilder($this->tableName, YAPEAL_DSN);
        // Get a list of column names and their ADOdb generic types.
        $this->colTypes = $this->qb->getColumnTypes();
        // Was $id set?
        if (!empty($id)) {
            // If $id has any characters other than 0-9 it's not an ownerID.
            if (0 == strlen(str_replace(range(0, 9), '', $id))) {
                if (false === $this->getItemById($id)) {
                    if (true == $create) {
                        // If $id is a number and doesn't exist yet set ownerID with it.
                        $this->properties['ownerID'] = $id;
                    } else {
                        $mess = 'Unknown owner ' . $id;
                        throw new \DomainException($mess);
                    }; // else ...
                };
            } else {
                $mess = 'Parameter $id must be an integer';
                throw new \InvalidArgumentException($mess);
            }
        }
    }
    /**
     * Destructor used to make sure to release ADOdb resource correctly more for
     * peace of mind than actual need.
     */
    public function __destruct()
    {
        $this->con = null;
    }
    /**
     * Used to get graphic from Graphic table by owner ID.
     *
     * @param int $id Id of graphic owner wanted.
     *
     * @return bool TRUE if graphic was retrieved.
     */
    public function getItemById($id)
    {
        $sql = 'select `' . implode('`,`', array_keys($this->colTypes)) . '`';
        $sql .= ' from `' . $this->tableName . '`';
        $sql .= ' where `ownerID`=' . $id;
        try {
            $result = $this->con->GetRow($sql);
            if (!empty($result)) {
                $this->properties = $result;
                $this->recordExists = true;
            } else {
                $this->recordExists = false;
            };
        } catch (\ADODB_Exception $e) {
            \Logger::getLogger('yapeal')
                ->warn($e);
            $this->recordExists = false;
        }
        return $this->recordExists;
    }
    // function getItemById
    /**
     * Used to get item from table by name.
     *
     * @param string $name Name of record wanted.
     *
     * @return bool TRUE if item was retrieved else FALSE.
     *
     * @throws \LogicException Throws LogicException because there is no 'name' type
     * field for this database table.
     */
    public function getItemByName($name)
    {
        throw new \LogicException('Not implemented for '
            . __CLASS__
            . ' table');
    }
    // function getItemByName
    /**
     * Function used to check if database record already existed.
     *
     * @return bool Returns TRUE if the the database record already existed.
     */
    public function recordExists()
    {
        return $this->recordExists;
    }
    // function recordExists
    /**
     * Used to set default for column.
     *
     * @param string $name  Name of the column.
     * @param mixed  $value Value to be used as default for column.
     *
     * @return bool Returns TRUE if column exists in table and default was set.
     */
    public function setDefault($name, $value)
    {
        return $this->qb->setDefault($name, $value);
    }
    // function setDefault
    /**
     * Used to set defaults for multiple columns.
     *
     * @param array $defaults List of column names and new default values.
     *
     * @return bool Returns TRUE if all column defaults could be set, else FALSE.
     */
    public function setDefaults(array $defaults)
    {
        return $this->qb->setDefaults($defaults);
    }
    // function setDefaults
    /**
     * Used to store data into table.
     *
     * @return bool Return TRUE if store was successful.
     */
    public function store()
    {
        if (false === $this->qb->addRow($this->properties)) {
            return false;
        }
        return $this->qb->store();
    }
    // function store
}

