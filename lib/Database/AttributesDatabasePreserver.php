<?php
/**
 * Contains AttributesDatabasePreserver Class.
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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

/**
 * Class AttributesDatabasePreserver
 */
class AttributesDatabasePreserver implements
    DatabasePreserverInterface, LoggerAwareInterface
{
    /**
     * @param PDO              $pdo
     * @param LoggerInterface  $logger
     * @param CommonSqlQueries $csq
     * @param string           $tableName
     * @param string[]         $columnDefaults
     */
    public function __construct(
        PDO $pdo,
        LoggerInterface $logger,
        CommonSqlQueries $csq,
        $tableName = '',
        array $columnDefaults = array()
    ) {
        $this->setPdo($pdo)
             ->setLogger($logger)
             ->setCsq($csq);
        $this->setTableName($tableName)
             ->setColumnDefaults($columnDefaults);
    }
    /**
     * @param string $xml
     * @param string $xPath
     *
     * @return self
     */
    public function preserveData(
        $xml,
        $xPath = '//row'
    ) {
        $simple = new SimpleXMLElement($xml);
        foreach ($simple->xpath($xPath) as $aRow) {
            $row = array();
            foreach ($aRow->attributes() as $key => $value) {
                $row[$key] = (string)$value;
            }
            $this->addRow($row);
        }
        $this->flush();
    }
    /**
     * @param string[] $columnDefaults
     *
     * @return self
     */
    public function setColumnDefaults(array $columnDefaults)
    {
        $this->columnDefaults = $columnDefaults;
        return $this;
    }
    /**
     * @param CommonSqlQueries $value
     *
     * @return self
     */
    public function setCsq($value)
    {
        $this->csq = $value;
        return $this;
    }
    /**
     * @param LoggerInterface $value
     *
     * @return self
     */
    public function setLogger(LoggerInterface $value)
    {
        $this->logger = $value;
        return $this;
    }
    /**
     * @param PDO $value
     *
     * @return self
     */
    public function setPdo(PDO $value)
    {
        $this->pdo = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setTableName($value)
    {
        $this->tableName = $value;
        return $this;
    }
    /**
     * @var string[] $columnDefaults
     */
    protected $columnDefaults;
    /**
     * @var CommonSqlQueries
     */
    protected $csq;
    /**
     * @var LoggerInterface
     */
    protected $logger;
    /**
     * @var int
     */
    protected $maxRowCount = 1000;
    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @var int $rowCount
     */
    protected $rowCount = 0;
    /**
     * @var string $rowPrototype
     */
    protected $rowPrototype;
    /**
     * @var string $tableName
     */
    protected $tableName;
    /**
     * @var array $upsertRows ;
     */
    protected $upsertRows;
    /**
     * @param array $row
     *
     * @return self
     */
    protected function addRow(array $row)
    {
        if (empty($row)) {
            return $this;
        }
        $defaults = $this->getColumnDefaults();
        // Fill-in any missing columns like ownerID.
        $newRow = array_replace($defaults, $row);
        // Replace empty values with any existing defaults.
        foreach ($defaults as $key => $value) {
            if (is_null($value)) {
                continue;
            }
            if (strlen($newRow[$key]) == 0) {
                $newRow[$key] = $value;
            }
        }
        $this->upsertRows[] = $newRow;
        if (++$this->rowCount >= $this->maxRowCount) {
            $this->flush();
        }
        return $this;
    }
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
     * @return self
     */
    protected function flush()
    {
        if ($this->rowCount == 0) {
            return $this;
        }
        $data = $this->flattenArray($this->upsertRows);
        $this->upsertRows = array();
        $mess = sprintf(
            'Have %1$s row(s) to upsert into %2$s table',
            $this->rowCount,
            $this->getTableName()
        );
        $this->getLogger()
            ->info($mess);
        $sql = $this->getCsq()
                    ->getUpsert(
                        $this->getTableName(),
                        $this->getColumnNameList(),
                        $this->rowCount
                    );
        $mess = preg_replace('/(,\(\?(?:,\?)*\))+/', ',...', $sql);
        $this->getLogger()
            ->info($mess);
        $this->rowCount = 0;
        $stmt = $this->getPdo()
                     ->prepare($sql);
        $stmt->execute($data);
        return $this;
    }
    /**
     * @return string[]
     */
    protected function getColumnDefaults()
    {
        return $this->columnDefaults;
    }
    /**
     * @return array
     */
    protected function getColumnNameList()
    {
        return array_keys($this->getColumnDefaults());
    }
    /**
     * @return CommonSqlQueries
     */
    protected function getCsq()
    {
        return $this->csq;
    }
    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }
    /**
     * @return PDO
     */
    protected function getPdo()
    {
        return $this->pdo;
    }
    /**
     * @return string
     */
    protected function getTableName()
    {
        return $this->tableName;
    }
}
