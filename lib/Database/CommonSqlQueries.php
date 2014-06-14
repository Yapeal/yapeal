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
     * @param string[] $columnNameList
     *
     * @return string
     */
    public function getUpsertEnd(array $columnNameList)
    {
        $updates = array();
        foreach ($columnNameList as $column) {
            $updates[] = '"' . $column . '"=values("' . $column . '")';
        }
        $updates = implode(',', $updates);
        return ' on duplicate key update ' . $updates;
    }
    /**
     * @param string   $tableName
     * @param string[] $columnNameList
     *
     * @return string
     */
    public function getUpsertStart($tableName, array $columnNameList)
    {
        $upsertStart =
            'insert into "{database}"."{table_prefix}' . $tableName . '" ('
            . implode(',', $columnNameList) . ') values ';
        return $upsertStart;
    }
    /**
     * @return string
     */
    public function getUtilCachedUntilUpsert()
    {
        $columnNameList =
            array('apiName', 'cachedUntil', 'ownerID', 'sectionName');
        return $this->getUpsertStart('utilCachedUntil', $columnNameList)
        . '(?,?,?,?)' . $this->getUpsertEnd($columnNameList);
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
