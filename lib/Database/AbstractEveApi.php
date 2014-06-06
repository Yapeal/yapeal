<?php
/**
 * Contains AbstractEveApi class.
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

use PDO;

/**
 * Class AbstractEveApi
 */
abstract class AbstractEveApi
{
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
        $this->upsertRows[] = array_replace($this->columnDefaults, $row);
        if (++$this->rowCount >= $this->maxRowCount) {
            $this->flush();
        }
        return $this;
    }
    /**
     * $return self
     */
    public function flush()
    {
        $rows =
            implode(',', array_fill(0, $this->rowCount, $this->rowPrototype));
        $sql = $this->getUpsertStart() . $rows . $this->getUpsertEnd();
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $statement = $connection->prepare($sql);
            $statement->execute($this->flattenArray($this->upsertRows));
            $connection->commit();
        } catch (\PDOException $exc) {
            $connection->rollBack();
        }
        $this->rowCount = 0;
        $this->upsertRows = array();
        return $this;
    }
    /**
     * @param PDO $value
     *
     * @return self
     */
    public function setConnection(PDO $value)
    {
        $this->connection = $value;
        return $this;
    }
    /**
     * @var array
     */
    protected $columnDefaults = array();
    /**
     * @var PDO
     */
    protected $connection;
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
    protected $rowPrototype = '()';
    /**
     * @var string[]
     */
    protected $upsertRows;
    /**
     * @param array $array
     *
     * @return array
     */
    protected function flattenArray(array $array)
    {
        $return = array();
        array_walk_recursive(
            $array,
            function ($arr) use (&$return) {
                $return[] = $arr;
            }
        );
        return $return;
    }
    /**
     * @return PDO
     */
    protected function getConnection()
    {
        return $this->connection;
    }
    abstract protected function getUpsertEnd();
    abstract protected function getUpsertStart();
}
