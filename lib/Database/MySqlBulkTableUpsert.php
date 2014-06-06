<?php
/**
 * Contains MySqlBulkTableUpsert class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database;

/**
 * Class MySqlBulkTableUpsert
 */
class MySqlBulkTableUpsert
{
    /**
     * @param DatabaseInterface $connection
     */
    public function __construct(DatabaseInterface $connection)
    {
        $this->setConnection($connection);
    }
    /**
     * @param array $row
     *
     * @return self
     */
    public function addRow(array $row)
    {
        if (empty($row)) {
            return $this;
        }
        return $this;
    }
    /**
     * $return self
     */
    public function flush()
    {
        return $this;
    }
    /**
     * @param array <string,array<string,string|int>> $value
     *
     * @return self
     */
    public function setColumnsMetadata(array $value)
    {
        $this->columnsMetadata = $value;
        return $this;
    }
    /**
     * @param DatabaseInterface $value
     *
     * @return self
     */
    public function setConnection(DatabaseInterface $value)
    {
        $this->connection = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setDatabaseName($value)
    {
        if (!is_string($value)) {
            $mess = 'Database name MUST be a string but was given ' . gettype(
                    $value
                );
            throw new \InvalidArgumentException($mess);
        }
        $this->databaseName = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setTableName($value)
    {
        if (!is_string($value)) {
            $mess =
                'Table name MUST be a string but was given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->tableName = $value;
        return $this;
    }
    /**
     * @var array
     */
    protected $columnDefaults;
    /**
     * Contains per column info about the column in a table.
     *
     * An example of some typical columns:
     * ```
     * $columnsMetadata = array(
     *     'name' => array(
     *         'type' => PDO::PARAM_STR
     *     ),
     *     'factionName' => array(
     *         'type' => PDO::PARAM_STR,
     *         'default' => ''
     *     ), ...
     * );
     * ```
     *
     * @type array<string,array<string,string|int>> $columnsMetadata
     */
    protected $columnsMetadata = array();
    /**
     * @var DatabaseInterface
     */
    protected $connection;
    /**
     * @var string
     */
    protected $databaseName;
    /**
     * @var int
     */
    protected $maxRowCount = 1000;
    /**
     * @var int
     */
    protected $rowCount = 0;
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var string[]
     */
    protected $upsertRows;
    /**
     * @throws \LogicException
     * @return string[]
     */
    protected function getColumnNameList()
    {
        return array_keys($this->getColumnsMetadata());
    }
    /**
     * @throws \LogicException
     * @return array<string,array<string,string|int>>
     */
    protected function getColumnsMetadata()
    {
        if (empty($this->columnsMetadata)) {
            $mess = 'Tried to use column metadata when it was NOT set';
            throw new \LogicException($mess);
        }
        return $this->columnsMetadata;
    }
    /**
     * @return DatabaseInterface
     */
    protected function getConnection()
    {
        return $this->connection;
    }
    /**
     * @throws \LogicException
     * @return string
     */
    protected function getDatabaseName()
    {
        if (empty($this->databaseName)) {
            $mess = 'Tried to use database name when it was NOT set';
            throw new \LogicException($mess);
        }
        return $this->databaseName;
    }
    /**
     * @throws \LogicException
     * @return string
     */
    protected function getTableName()
    {
        if (empty($this->tableName)) {
            $mess = 'Tried to use table name when it was NOT set';
            throw new \LogicException($mess);
        }
        return $this->tableName;
    }
    /**
     * @param array $keys
     *
     * @return string
     */
    protected function getUpsertEnd(array $keys)
    {
        $updates = array();
        foreach ($keys as $key) {
            $updates[] = '"' . $key . '"=values("' . $key . '")';
        }
        $updates = implode(',', $updates);
        return ' on duplicate key update ' . $updates;
    }
    /**
     * @param array $keys
     *
     * @return string
     */
    protected function getUpsertStart(array $keys)
    {
        $upsertStart = 'insert into "{database}"."{table_prefix}'
            . $this->tableName . '" (' . implode(',', $keys) . ') values ';
        return $upsertStart;
    }
    /**
     * @throws \LogicException
     * @return self
     */
    protected function initColumnDefaults()
    {
        $defaults = array();
        foreach ($this->getColumnsMetadata() as $key => $value) {
            if (isset($value['default'])) {
                $defaults[$key] = $value['default'];
            }
        }
        $this->columnDefaults = $defaults;
        return $this;
    }
}
