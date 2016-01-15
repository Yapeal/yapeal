<?php
/**
 * Contains CommonSqlQueries class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2016 Michael Cummings
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
 * @copyright 2014-2016 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Sql;

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
     * Get account corporationIDs for corps without a corporation type key.
     *
     * @return string
     */
    public function getAccountCorporationIDsExcludingCorporationKeys()
    {
        $sql
            = <<<'SQL'
SELECT DISTINCT acc."corporationID"
 FROM "%1$s"."%2$saccountCharacters" AS acc
 WHERE
 acc."corporationID" NOT IN (
 SELECT ac."corporationID"
 FROM "%1$s"."%2$saccountCharacters" AS ac
 JOIN "%1$s"."%2$saccountKeyBridge" AS akb
 ON (ac."characterID"=akb."characterID")
 JOIN "%1$s"."%2$saccountAPIKeyInfo" AS aaki
 ON (akb."keyID"=aaki."keyID")
 WHERE
 aaki."type"='Corporation'
 AND acc."corporationID" = ac."corporationID"
)
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
            $this->databaseName,
            $this->tablePrefix
        );
    }
    /**
     * @return string
     */
    public function getActiveApis()
    {
        return sprintf(
            'SELECT * FROM "%1$s"."%2$sutilEveApi" WHERE "active"=1 ORDER BY RAND()',
            $this->databaseName,
            $this->tablePrefix
        );
    }
    /**
     * @param string $ownerID
     *
     * @return string
     */
    public function getActiveMailBodiesWithOwnerID($ownerID)
    {
        $sql
            = <<<'SQL'
SELECT "messageID"
 FROM "%1$s"."%2$scharMailMessages" AS cmm
 WHERE "ownerID"=%3$s
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
            $this->databaseName,
            $this->tablePrefix,
            $ownerID
        );
    }
    /**
     * @return string
     */
    public function getActiveRegisteredAccountStatus()
    {
        $sql
            = <<<'SQL'
SELECT urk."keyID",urk."vCode"
 FROM "%1$s"."%2$sutilRegisteredKey" AS urk
 JOIN "%1$s"."%2$saccountAPIKeyInfo" AS aaki
 ON (urk."keyID" = aaki."keyID")
 WHERE
 aaki."type" IN ('Account','Character')
 AND urk."active"=1
 AND (urk."activeAPIMask" & aaki."accessMask" & 33554432) <> 0
SQL;
        return sprintf($sql, $this->databaseName, $this->tablePrefix);
    }
    /**
     * @param int $mask
     *
     * @return string
     */
    public function getActiveRegisteredCharacters($mask)
    {
        $sql
            = <<<'SQL'
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
 AND urk."active"=1
 AND (urk."activeAPIMask" & aaki."accessMask" & %3$s) <> 0
 AND aaki."expires" > now()
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
            $this->databaseName,
            $this->tablePrefix,
            $mask
        );
    }
    /**
     * @param int $mask
     *
     * @return string
     */
    public function getActiveRegisteredCorporations($mask)
    {
        $sql
            = <<<'SQL'
SELECT ac."corporationID",urk."keyID",urk."vCode"
 FROM "%1$s"."%2$saccountKeyBridge" AS akb
 JOIN "%1$s"."%2$saccountAPIKeyInfo" AS aaki
 ON (akb."keyID" = aaki."keyID")
 JOIN "%1$s"."%2$sutilRegisteredKey" AS urk
 ON (akb."keyID" = urk."keyID")
 JOIN "%1$s"."%2$saccountCharacters" AS ac
 ON (akb."characterID" = ac."characterID")
 WHERE
 aaki."type" = 'Corporation'
 AND urk."active"=1
 AND (urk."activeAPIMask" & aaki."accessMask" & %3$s) <> 0
 AND aaki."expires" > now()
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
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
            'SELECT "keyID","vCode" FROM "%1$s"."%2$sutilRegisteredKey" WHERE "active"=1',
            $this->databaseName,
            $this->tablePrefix
        );
    }
    /**
     * @param int    $mask
     * @param string $ownerID
     *
     * @return string
     */
    public function getActiveStarbaseTowers($mask, $ownerID)
    {
        $sql
            = <<<'SQL'
SELECT sl."itemID",ac."corporationID",urk."keyID",urk."vCode"
 FROM "%1$s"."%2$saccountKeyBridge" AS akb
 JOIN "%1$s"."%2$saccountAPIKeyInfo" AS aaki
 ON (akb."keyID" = aaki."keyID")
 JOIN "%1$s"."%2$sutilRegisteredKey" AS urk
 ON (akb."keyID" = urk."keyID")
 JOIN "%1$s"."%2$saccountCharacters" AS ac
 ON (akb."characterID" = ac."characterID")
 JOIN "%1$s"."%2$scorpStarbaseList" AS sl
 ON (ac."corporationID" = sl."ownerID")
 WHERE
 aaki."type" = 'Corporation'
 AND urk."active"=1
 AND sl."ownerID"=%4$s
 AND (urk."activeAPIMask" & aaki."accessMask" & %3$s) <> 0
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
            $this->databaseName,
            $this->tablePrefix,
            $mask,
            $ownerID
        );
    }
    /**
     * @param string $hash
     *
     * @return string
     */
    public function getApiLock($hash)
    {
        return sprintf('SELECT GET_LOCK(\'%1$s\',5)', $hash);
    }
    /**
     * Used by 'yc D:U'
     *
     * @return string
     */
    public function getCreateAddOrModifyColumnProcedure()
    {
        $sql
            = <<<'SQL'
CREATE PROCEDURE "{database}"."AddOrModifyColumn"(
    IN param_database_name  VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    IN param_table_name     VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    IN param_column_name    VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci,
    IN param_column_details VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci)
    BEGIN
        IF NOT EXISTS(SELECT NULL
                      FROM
                          "information_schema"."COLUMNS"
                      WHERE
                          "COLUMN_NAME" COLLATE utf8_unicode_ci = param_column_name AND
                          "TABLE_NAME" COLLATE utf8_unicode_ci = param_table_name AND
                          "table_schema" COLLATE utf8_unicode_ci = param_database_name)
        THEN
/* Create the full statement to execute */
            SET @StatementToExecute = concat('ALTER TABLE "',
                                             param_database_name, '"."',
                                             param_table_name,
                                             '" ADD COLUMN "',
                                             param_column_name, '" ',
                                             param_column_details) $$
/* Prepare and execute the statement that was built */
            PREPARE DynamicStatement FROM @StatementToExecute$$
            EXECUTE DynamicStatement$$
/* Cleanup the prepared statement */
            DEALLOCATE PREPARE DynamicStatement$$
        ELSE
/* Create the full statement to execute */
            SET @StatementToExecute = concat('ALTER TABLE "',
                                             param_database_name, '"."',
                                             param_table_name,
                                             '" MODIFY COLUMN "',
                                             param_column_name, '" ',
                                             param_column_details) $$
/* Prepare and execute the statement that was built */
            PREPARE DynamicStatement FROM @StatementToExecute$$
            EXECUTE DynamicStatement$$
/* Cleanup the prepared statement */
            DEALLOCATE PREPARE DynamicStatement$$
        END IF$$
    END;
SQL;
        return $sql;
    }
    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getDeleteFromTable($tableName)
    {
        return sprintf(
            'DELETE FROM "%1$s"."%2$s%3$s"',
            $this->databaseName,
            $this->tablePrefix,
            $tableName
        );
    }
    /**
     * @param string $tableName
     * @param string $ownerID
     *
     * @return string
     */
    public function getDeleteFromTableWithOwnerID($tableName, $ownerID)
    {
        return sprintf(
            'DELETE FROM "%1$s"."%2$s%3$s" WHERE "ownerID"= \'%4$s\'',
            $this->databaseName,
            $this->tablePrefix,
            $tableName,
            $ownerID
        );
    }
    /**
     * @param string $tableName
     * @param string $keyID
     *
     * @return string
     */
    public function getDeleteFromTableWithKeyID($tableName, $keyID)
    {
        return sprintf(
            'DELETE FROM "%1$s"."%2$s%3$s" WHERE "keyID"= \'%4$s\'',
            $this->databaseName,
            $this->tablePrefix,
            $tableName,
            $keyID
        );
    }
    /**
     * @return string
     */
    public function getDropAddOrModifyColumnProcedure()
    {
        return 'DROP PROCEDURE IF EXISTS "{database}"."AddOrModifyColumn";';
    }
    /**
     * Get alliance corporationIDs for corps excluding any in accountCharacters.
     *
     * @return string
     */
    public function getMemberCorporationIDsExcludingAccountCorporations()
    {
        $sql
            = <<<'SQL'
SELECT DISTINCT emc."corporationID"
 FROM "%1$s"."%2$seveMemberCorporations" AS emc
 WHERE
 emc."corporationID" NOT IN (
 SELECT ac."corporationID"
 FROM "%1$s"."%2$saccountCharacters" AS ac
 WHERE
 emc."corporationID" = ac."corporationID"
)
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
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
        $rowPrototype
            = '(' . implode(',', array_fill(0, count($columnNameList), '?'))
              . ')';
        $rows = implode(',', array_fill(0, $rowCount, $rowPrototype));
        $updates = [];
        foreach ($columnNameList as $column) {
            $updates[] = '"' . $column . '"=VALUES("' . $column . '")';
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
        $sql
            = <<<'SQL'
SELECT "expires"
 FROM "%1$s"."%2$sutilCachedUntil"
 WHERE
 "apiName" = '%3$s'
 AND "sectionName" = '%4$s'
 AND "ownerID" = %5$s
SQL;
        return sprintf(
            str_replace(["\n", "\r\n"], '', $sql),
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
        return $this->getUpsert(
            'utilCachedUntil',
            ['apiName', 'expires', 'ownerID', 'sectionName'],
            1
        );
    }
    /**
     * @return string
     */
    public function getUtilLatestDatabaseVersion()
    {
        return sprintf(
            'SELECT MAX("version") FROM "%1$s"."%2$sutilDatabaseVersion"',
            $this->databaseName,
            $this->tablePrefix
        );
    }
    /**
     * @return string
     */
    public function getUtilLatestDatabaseVersionUpdate()
    {
        return $this->getUpsert('utilDatabaseVersion', ['version'], 1);
    }
    /**
     * @type string $databaseName
     */
    protected $databaseName;
    /**
     * @type string $tablePrefix
     */
    protected $tablePrefix;
}
