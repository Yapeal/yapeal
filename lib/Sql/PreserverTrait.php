<?php
/**
 * Contains PreserverTrait Trait.
 *
 * PHP version 5.5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Sql;

use SimpleXMLIterator;
use Yapeal\Log\Logger;

/**
 * Trait PreserverTrait
 */
trait PreserverTrait
{
    /**
     * @return \Yapeal\Sql\CommonSqlQueries
     */
    abstract protected function getCsq();
    /**
     * @return \PDO
     */
    abstract protected function getPdo();
    /**
     * @return \Yapeal\Event\EventMediatorInterface
     */
    abstract protected function getYem();
    /**
     * @param string $xml
     * @param array  $columnDefaults
     * @param string $tableName
     * @param string $xPath
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function attributePreserveData($xml, array $columnDefaults, $tableName, $xPath = '//row')
    {
        $maxRowCount = 1000;
        $rows = (new SimpleXMLIterator($xml))->xpath($xPath);
        if (0 === count($rows)) {
            return $this;
        }
        $rowChunks = array_chunk($rows, $maxRowCount, true);
        $columnNames = array_keys($columnDefaults);
        foreach ($rowChunks as $chunk) {
            $columns = $this->processXmlRows($columnDefaults, $chunk);
            $sql =
                $this->getCsq()
                     ->getUpsert($tableName, $columnNames, count($chunk));
            $mess = preg_replace('/(,\(\?(?:,\?)*\))+/', ',...', $sql);
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
            $this->flush($columns, $columnNames, $tableName);
        }
        return $this;
    }
    /**
     * @param string[] $columns
     * @param string[] $columnNames
     * @param string   $tableName
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function flush(array $columns, array $columnNames, $tableName)
    {
        if (0 === count($columns)) {
            return $this;
        }
        $rowCount = count($columns) / count($columnNames);
        $mess = sprintf('Have %1$s row(s) to upsert into %2$s table', $rowCount, $tableName);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $sql =
            $this->getCsq()
                 ->getUpsert($tableName, $columnNames, $rowCount);
        $mess = preg_replace('/(,\(\?(?:,\?)*\))+/', ',...', $sql);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
        $mess = substr(implode(',', $columns), 0, 255);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $stmt =
            $this->getPdo()
                 ->prepare($sql);
        $stmt->execute($columns);
        return $this;
    }
    /**
     * @param array               $columnDefaults
     * @param SimpleXMLIterator[] $rows
     *
     * @return array
     */
    protected function processXmlRows(array $columnDefaults, array $rows)
    {
        $columns = [];
        /**
         * @type \SimpleXMLElement $row
         */
        foreach ($rows as $row) {
            // Replace empty values with any existing defaults.
            foreach ($columnDefaults as $key => $value) {
                if ('' !== (string)$row[$key]) {
                    $columns[] = (string)$row[$key];
                    continue;
                }
                $columns[] = (string)$value;
            }
        }
        return $columns;
    }
    /**
     * @param string $xml
     * @param array  $columnDefaults
     * @param string $tableName
     * @param string $xPath
     *
     * @return self Fluent interface.
     * @throws \DomainException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    protected function valuesPreserveData($xml, array $columnDefaults, $tableName, $xPath = '//result/*')
    {
        $columns = (new SimpleXMLIterator($xml))->xpath($xPath);
        /**
         * @type SimpleXMLIterator $column
         */
        foreach ($columns as $column) {
            $columnName = $column->getName();
            if (!array_key_exists($columnName, $columnDefaults)) {
                break;
            }
            if ('' !== (string)$column || null === $columnDefaults[$columnName]) {
                $columnDefaults[$columnName] = (string)$column;
            }
        }
        $this->flush(
            array_values($columnDefaults),
            array_keys($columnDefaults),
            $tableName
        );
    }
}
