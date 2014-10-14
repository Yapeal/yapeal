<?php
/**
 * Contains ValuesDatabasePreserverTrait Trait.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database;

use LogicException;
use Psr\Log\LoggerInterface;
use SimpleXMLIterator;

/**
 * Trait ValuesDatabasePreserverTrait
 */
trait ValuesDatabasePreserverTrait
{
    /**
     * @param string $xml
     * @param array  $columnDefaults
     * @param string $tableName
     * @param string $xPath
     *
     * @return self
     */
    public function valuesPreserveData(
        $xml,
        array $columnDefaults,
        $tableName,
        $xPath = '//result/*'
    ) {
        $columns = (new SimpleXMLIterator($xml))->xpath($xPath);
        /**
         * @var SimpleXMLIterator $column
         */
        foreach ($columns as $column) {
            $columnName = $column->getName();
            if (!array_key_exists($columnName, $columnDefaults)) {
                break;
            }
            if (is_null($columnDefaults[$columnName]) || strlen($column) != 0) {
                $columnDefaults[$columnName] = (string)$column;
            }
            $this->getLogger()
                 ->debug($columnName . ' => ' . $columnDefaults[$columnName]);
        }
        $this->flush(
            array_values($columnDefaults),
            array_keys($columnDefaults),
            $tableName
        );
    }
    /**
     * @param string[] $columns
     * @param string[] $columnNames
     * @param string   $tableName
     * @param int      $rowCount
     *
     * @return self
     */
    abstract protected function flush(
        array $columns,
        array $columnNames,
        $tableName,
        $rowCount = 1
    );
    /**
     * @throws LogicException
     * @return LoggerInterface
     */
    abstract protected function getLogger();
}
