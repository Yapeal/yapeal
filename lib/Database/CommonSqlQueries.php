<?php
/**
 * Contains CommonSqlQueries class.
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
 * Class CommonSqlQueries
 */
class CommonSqlQueries
{
    /**
     * @param string $databaseName
     * @param string $tablePrefix
     */
    public function __construct($databaseName, $tablePrefix)
    {
        $this->databaseName = $databaseName;
        $this->tablePrefix = $tablePrefix;
    }
    /**
     * @return string
     */
    public function getActiveApis()
    {
        return sprintf(
            'SELECT * FROM "%1$s"."%2$sutilEveApi" WHERE "isActive"=1 ORDER BY RAND()',
            $this->databaseName,
            $this->tablePrefix
        );
    }
    /**
     * @param int $mask
     *
     * @return string
     */
    public function getActiveRegisteredCharacters($mask)
    {
        $sql = <<<'SQL'
SELECT ac."characterID",urk."keyID",urk."vCode"
 FROM "%1$s"."%2$saccountKeyBridge" AS akb
 JOIN "%1$s"."%2$saccountAPIKeyInfo" AS aaki
 ON (akb."keyID" = aaki."keyID")
 JOIN "%1$s"."%2$sutilRegisteredKey" AS urk
 ON (akb."keyID" = urk."keyID")
 JOIN "%1$s"."%2$saccountCharacters" AS ac
 ON (akb."characterID" = ac."characterID")
 WHERE
  aaki."type" IN ('Account','Character')
  AND urk."isActive"=1
  AND (urk."activeAPIMask" & aaki."accessMask" & %3$s) <> 0
SQL;
        return sprintf(
            str_replace(array("\n", "\r\n"), '', $sql),
            $this->databaseName,
            $this->tablePrefix,
            $mask
        );
    }
    /**
     * @return string
     */
    public function getActiveRegisteredKeys()
    {
        return sprintf(
            'SELECT "keyID","vCode" FROM "%1$s"."%2$sutilRegisteredKey" WHERE "isActive"=1',
            $this->databaseName,
            $this->tablePrefix
        );
    }
    /**
     * @param string $tableName
     * @param array  $columnNameList
     * @param string $rowCount
     *
     * @return string
     */
    public function getUpsert($tableName, array $columnNameList, $rowCount)
    {
        $columns = implode('","', $columnNameList);
        $rowPrototype =
            '(' . implode(',', array_fill(0, count($columnNameList), '?'))
            . ')';
        $rows = implode(',', array_fill(0, $rowCount, $rowPrototype));
        $updates = array();
        foreach ($columnNameList as $column) {
            $updates[] = '"' . $column . '"=values("' . $column . '")';
        }
        $updates = implode(',', $updates);
        $sql = sprintf(
            'INSERT INTO "%1$s"."%2$s%3$s" ("%4$s") VALUES %5$s ON DUPLICATE KEY UPDATE %6$s',
            $this->databaseName,
            $this->tablePrefix,
            $tableName,
            $columns,
            $rows,
            $updates
        );
        return $sql;
    }
    /**
     * @param string $apiName
     * @param string $sectionName
     * @param string $ownerID
     *
     * @return string
     */
    public function getUtilCachedUntilExpires($apiName, $sectionName, $ownerID)
    {
        return sprintf(
            'SELECT "expires" FROM "%1$s"."%2$sutilCachedUntil" WHERE "apiName" = \'%3$s\' AND "sectionName" = \'%4$s\' AND "ownerID" = %5$s',
            $this->databaseName,
            $this->tablePrefix,
            $apiName,
            $sectionName,
            $ownerID
        );
    }
    /**
     * @return string
     */
    public function getUtilCachedUntilUpsert()
    {
        $columnNameList =
            array('apiName', 'expires', 'ownerID', 'sectionName');
        return $this->getUpsert('utilCachedUntil', $columnNameList, 1);
    }
    /**
     * @var string
     */
    protected $databaseName;
    /**
     * @var string
     */
    protected $tablePrefix;
}