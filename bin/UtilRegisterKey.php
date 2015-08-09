<?php
/**
 * Contains UtilRegisterKey class.
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
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal;

use InvalidArgumentException;
use LogicException;
use PDO;
use Yapeal\Exception\YapealDatabaseException;

/**
 * Class UtilRegisterKey
 *
 * WARNING: This class changes the PDO connection into MySQL's ANSI,TRADITIONAL
 * mode and makes other changes that may cause other queries in any other code
 * that reuses the connection after the changes to fail. For example if you use
 * things like back-tick quotes in queries they may cause the query to fail or
 * issue warnings. You can find out more about MySQL modes at
 * {@link http://dev.mysql.com/doc/refman/5.5/en/sql-mode.html}
 */
class UtilRegisterKey
{
    /**
     * @param PDO    $pdo
     * @param string $databaseName
     * @param string $tablePrefix
     *
     * @throws InvalidArgumentException
     */
    public function __construct(PDO $pdo, $databaseName = 'yapeal', $tablePrefix = '')
    {
        $this->setPdo($pdo)
             ->setDatabaseName($databaseName)
             ->setTablePrefix($tablePrefix);
    }
    /**
     * @return int
     * @throws LogicException
     */
    public function getActive()
    {
        if (null === $this->active) {
            $mess = ' Tried to access "active" before it was set';
            throw new LogicException($mess);
        }
        return (int)$this->active;
    }
    /**
     * @return string
     * @throws LogicException
     */
    public function getActiveAPIMask()
    {
        if (null === $this->activeAPIMask) {
            $mess = ' Tried to access "activeAPIMask" before it was set';
            throw new LogicException($mess);
        }
        return $this->activeAPIMask;
    }
    /**
     * @return string
     * @throws LogicException
     */
    public function getKeyID()
    {
        if (null === $this->keyID) {
            $mess = ' Tried to access "keyID" before it was set';
            throw new LogicException($mess);
        }
        return $this->keyID;
    }
    /**
     * @return string
     * @throws LogicException
     */
    public function getVCode()
    {
        if (null === $this->vCode) {
            $mess = ' Tried to access "vCode" before it was set';
            throw new LogicException($mess);
        }
        return $this->vCode;
    }
    /**
     * @return bool
     * @throws LogicException
     */
    public function isActive()
    {
        if (null === $this->active) {
            $mess = ' Tried to access "active" before it was set';
            throw new LogicException($mess);
        }
        return $this->active;
    }
    /**
     * Used to load an existing RegisteredKey row from database.
     *
     * @return UtilRegisterKey Fluent interface.
     * @throws LogicException
     * @throws YapealDatabaseException
     */
    public function load()
    {
        $stmt = $this->initPdo()
                     ->getPdo()
                     ->query($this->getExistingRegisteredKeyById());
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (1 !== count($result)) {
            $mess =
                sprintf('Expect to receive a single row for "%1$s" but got %2$s', $this->getKeyID(), count($result));
            throw new YapealDatabaseException($mess);
        }
        foreach (self::$columnsNames as $column) {
            $this->$column = $result[0][$column];
        }
        return $this;
    }
    /**
     * Method used to persist changes to the database.
     *
     * NOTE: After calling this method the MySQL PDO connection will be
     * switched to ANSI mode and use UTF-8.
     *
     * @see UtilRegisteredKey
     * @return UtilRegisterKey
     * @throws LogicException
     */
    public function save()
    {
        $stmt = $this->initPdo()
                     ->getPdo()
                     ->prepare($this->getUpsert());
        $columns = [
            $this->getActive(),
            $this->getActiveAPIMask(),
            $this->getKeyID(),
            $this->getVCode()
        ];
        $stmt->execute($columns);
        return $this;
    }
    /**
     * @param bool $value
     *
     * @return self
     */
    public function setActive($value = true)
    {
        $this->active = (bool)$value;
        return $this;
    }
    /**
     * @param string|int $value
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setActiveAPIMask($value)
    {
        if (is_int($value)) {
            $value = (string)$value;
        }
        if (!is_string($value)) {
            $mess = 'ActiveAPIMask MUST be an integer or integer string but was given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        if (!$this->isIntString($value)) {
            $mess = 'ActiveAPIMask MUST be an integer or integer string but was given ' . $value;
            throw new InvalidArgumentException($mess);
        }
        $this->activeAPIMask = $value;
        return $this;
    }
    /**
     * @param string $databaseName
     *
     * @return UtilRegisterKey
     * @throws InvalidArgumentException
     */
    public function setDatabaseName($databaseName)
    {
        if (!is_string($databaseName)) {
            $mess = 'DatabaseName MUST be a string but was given ' . gettype($databaseName);
            throw new InvalidArgumentException($mess);
        }
        $this->databaseName = $databaseName;
        return $this;
    }
    /**
     * @param string|int $value
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setKeyID($value)
    {
        if (is_int($value)) {
            $value = (string)$value;
        }
        if (!(is_string($value) && $this->isIntString($value))) {
            $mess = 'KeyID MUST be an integer or integer string but was given (' . gettype($value) . ') ' . $value;
            throw new InvalidArgumentException($mess);
        }
        $this->keyID = $value;
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
     * @param string $tablePrefix
     *
     * @return UtilRegisterKey
     * @throws InvalidArgumentException
     */
    public function setTablePrefix($tablePrefix)
    {
        if (!is_string($tablePrefix)) {
            $mess = 'TablePrefix MUST be a string but was given ' . gettype($tablePrefix);
            throw new InvalidArgumentException($mess);
        }
        $this->tablePrefix = $tablePrefix;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setVCode($value)
    {
        if (!is_string($value)) {
            $mess = 'VCode MUST be a string but was given ' . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        $this->vCode = $value;
        return $this;
    }
    /**
     * @return string
     * @throws LogicException
     */
    protected function getExistingRegisteredKeyById()
    {
        $columns = implode('","', self::$columnsNames);
        return sprintf(
            'SELECT "%4$s" FROM "%1$s"."%2$sutilRegisteredKey" WHERE "keyID"=%3$s',
            $this->databaseName,
            $this->tablePrefix,
            $this->getKeyID(),
            $columns
        );
    }
    /**
     * @throws LogicException
     * @return PDO
     */
    protected function getPdo()
    {
        if (!$this->pdo instanceof PDO) {
            $mess = 'Tried to use pdo before it was set';
            throw new LogicException($mess);
        }
        return $this->pdo;
    }
    /**
     * @return string
     */
    protected function getUpsert()
    {
        $columns = implode('","', self::$columnsNames);
        $rowPrototype = '(' . implode(',', array_fill(0, count(self::$columnsNames), '?')) . ')';
        $updates = [];
        foreach (self::$columnsNames as $column) {
            $updates[] = '"' . $column . '"=VALUES("' . $column . '")';
        }
        $updates = implode(',', $updates);
        $sql = sprintf(
            'INSERT INTO "%1$s"."%2$s%3$s" ("%4$s") VALUES %5$s ON DUPLICATE KEY UPDATE %6$s',
            $this->databaseName,
            $this->tablePrefix,
            'utilRegisteredKey',
            $columns,
            $rowPrototype,
            $updates
        );
        return $sql;
    }
    /**
     * @throws LogicException
     * @return self
     */
    protected function initPdo()
    {
        $pdo = $this->getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('SET SESSION SQL_MODE=\'ANSI,TRADITIONAL\'');
        $pdo->exec('SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE');
        $pdo->exec('SET SESSION TIME_ZONE=\'+00:00\'');
        $pdo->exec('SET NAMES UTF8');
        return $this;
    }
    /**
     * @param string $value
     *
     * @return bool
     */
    protected function isIntString($value)
    {
        $result = str_replace(
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            '',
            $value
        );
        return ('' === $result);
    }
    /**
     * @type array
     */
    protected static $columnsNames = [
        'active',
        'activeAPIMask',
        'keyID',
        'vCode'
    ];
    /**
     * @type bool $active
     */
    protected $active;
    /**
     * @type string $activeAPIMask
     */
    protected $activeAPIMask;
    /**
     * @type string $databaseName
     */
    protected $databaseName;
    /**
     * @type string $keyID
     */
    protected $keyID;
    /**
     * @type PDO $pdo
     */
    protected $pdo;
    /**
     * @type string $tablePrefix
     */
    protected $tablePrefix;
    /**
     * @type string $vCode
     */
    protected $vCode;
}
