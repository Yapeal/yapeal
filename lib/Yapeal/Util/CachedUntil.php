<?php
/**
 * Contains CachedUntil class.
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

use DomainException;
use InvalidArgumentException;
use LengthException;
use LogicException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Yapeal\Database\DatabaseConnection;
use Yapeal\Database\QueryBuilder;

/**
 * CachedUntil class
 *
 * @property string $cachedUntil
 *
 * @package Yapeal\Util
 */
class CachedUntil extends ALimitedObject implements IGetBy
{
    /**
     * @var \ADOConnection Holds an instance of the DB connection.
     */
    protected $con;
    /**
     * @var QueryBuilder Holds instance query builder.
     */
    protected $qb;
    /**
     * @var string Holds the table name of the query that is being built.
     */
    protected $tableName;
    /**
     * @var bool Set to TRUE if a database record exists.
     */
    private $recordExists;
    /**
     * Constructor
     *
     * @param mixed               $id     Id of cached time wanted.
     * @param bool                $create When $create is set to FALSE will throw
     *                                    DomainException if $id does NOT exist in database.
     * @param \ADOConnection|null $con    DB connection instance to use.
     * @param QueryBuilder|null   $qb     QueryBuilder instance to use.
     *
     * @throws DomainException If $create is FALSE and a database record for
     * $id does NOT exist a DomainException will be thrown.
     * @throws InvalidArgumentException If required parameters in $id are NOT
     * correct data types throws InvalidArgumentException.
     * @throws LengthException If $id is missing any require parameter throws a
     * LengthException.
     * @throws RuntimeException Throws RuntimeException if fails to get database
     * connection.
     */
    public function __construct(
        $id = null,
        $create = true,
        \ADOConnection $con = null,
        QueryBuilder $qb = null
    ) {
        $this->setTableName(YAPEAL_TABLE_PREFIX . 'util' . basename(__CLASS__));
        $this->setCon($con);
        $this->setQb($qb);
        // Get a list of column names and their ADOdb generic types.
        $this->colTypes = $this->qb->getColumnTypes();
        // Was $id set?
        $required = array('api' => 'C', 'ownerID' => 'I');
        // Check if $id is valid.
        foreach ($required as $k => $v) {
            if (!isset($id[$k])) {
                $mess =
                    'Missing required parameter $id["'
                    . $k
                    . '"] to constructor in '
                    . __CLASS__;
                throw new LengthException($mess);
            }
            switch ($v) {
                case 'C':
                case 'X':
                    if (!is_string($id[$k])) {
                        $mess = '$id["' . $k . '"] must be a string';
                        throw new InvalidArgumentException($mess);
                    }
                    break;
                case 'I':
                    if (0 != strlen(str_replace(range(0, 9), '', $id[$k]))) {
                        $mess = '$id["' . $k . '"] must be an integer';
                        throw new InvalidArgumentException($mess);
                    }
                    break;
            }
        }
        // Check if record already exists in database table.
        if (false === $this->getItemById($id)) {
            // If record doesn't exists should it be created?
            if (true == $create) {
                // Add required columns from $id to object.
                foreach ($required as $k => $v) {
                    $this->$k = $id[$k];
                }
            } else {
                $mess = 'No cached time for API = ' . $id['api'];
                $mess .= ' exists for ownerID = ' . $id['ownerID'];
                throw new DomainException($mess);
            }
        }
        // See if any columns beyond required were include and insure they get
        // set/updated as well.
        if (count($id) > count($required)) {
            foreach (array_diff_key($id, $required) as $k => $v) {
                $this->$k = $v;
            }
        }
    }
    /**
     * Checks for cachedUntil datetime in database to see if it's expired or
     * even exists.
     *
     * Cache is considered expired if any of the following are true:
     *
     * For any database connection exceptions.
     * No existing database record found.
     * At or past cachedUntil datetime.
     *
     * @param string               $api   Which API is data is needed for.
     * @param string|int           $owner Which owner the API data is needed for.
     * @param \ADOConnection|null  $con
     * @param LoggerInterface|null $logger
     *
     * @return bool Returns TRUE if it is time to get the API.
     */
    public static function isExpired(
        $api,
        $owner = 0,
        \ADOConnection $con = null,
        LoggerInterface $logger = null
    ) {
        $now = time();
        $sql =
            'select `cachedUntil`'
            . ' from `'
            . YAPEAL_TABLE_PREFIX
            . 'utilCachedUntil`'
            . ' where'
            . ' `ownerID`='
            . (int)$owner;
        try {
            if (is_null($con)) {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            }
            $sql .= ' and `api`=' . $con->qstr($api);
            $result = (string)$con->GetOne($sql);
        } catch (\ADODB_Exception $e) {
            $logger->log(LogLevel::WARNING, $e->getMessage());
            return true;
        }
        if (empty($result)) {
            return true;
        }
        $cachedUntil = strtotime($result . ' +0000');
        if ($now >= $cachedUntil) {
            return true;
        }
        return false;
    }
    /**
     * Destructor used to make sure to release DB connection correctly. Used
     * more for peace of mind than any actual need.
     */
    public function __destruct()
    {
        $this->con = null;
    }
    /**
     * Used to get cachedUntil time from cachedUntil table by ID.
     *
     * @param mixed $id Id of time wanted.
     *
     * @return bool Returns TRUE if time was retrieved.
     */
    public function getItemById($id)
    {
        $this->recordExists = false;
        $sql =
            'select `'
            . implode('`,`', array_keys($this->colTypes))
            . '` from `'
            . $this->tableName
            . '` where `ownerID`='
            . (int)$id['ownerID']
            . ' and `api`=';
        try {
            $sql .= $this->con->qstr($id['api']);
            $result = $this->con->GetRow($sql);
        } catch (\ADODB_Exception $e) {
            $this->logger->log(LogLevel::WARNING, $e->getMessage());
            return false;
        }
        if (false !== $result) {
            $this->properties = $result;
            $this->recordExists = true;
        }
        return $this->recordExists;
    }
    /**
     * Used to get item from table by name.
     *
     * @param mixed $name Name of record wanted.
     *
     * @return bool TRUE if item was retrieved else FALSE.
     *
     * @throws LogicException Throws LogicException because there is no 'name'
     * type field for this database table.
     */
    public function getItemByName($name)
    {
        throw new LogicException('Not implemented for ' . __CLASS__);
    }
    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
    /**
     * Function used to check if database record already existed.
     *
     * @return bool Returns TRUE if the the database record already existed.
     */
    public function recordExists()
    {
        return $this->recordExists;
    }
    /**
     * @param \ADOConnection|null $con
     *
     * @return self Returns self to allow fluid interface.
     * @throws RuntimeException Throws RuntimeException if fails to get database
     * connection.
     */
    public function setCon(\ADOConnection $con = null)
    {
        if (is_null($con)) {
            try {
                $con = DatabaseConnection::connect(YAPEAL_DSN);
            } catch (\ADODB_Exception $e) {
                $mess = 'Failed to get database connection in ' . __CLASS__;
                throw new RuntimeException($mess);
            }
        }
        $this->con = $con;
        return $this;
    }
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
    /**
     * @param \Yapeal\Database\QueryBuilder $qb
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setQb($qb)
    {
        $this->qb = $qb;
        return $this;
    }
    /**
     * @param string $tableName
     *
     * @return self Returns self to allow fluid interface.
     */
    public function setTableName($tableName)
    {
        $this->tableName = (string)$tableName;
        return $this;
    }
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
}

