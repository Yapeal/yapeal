<?php
/**
 * Contains UtilRegisterKey class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database. Copyright (C) 2014 Michael Cummings
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
namespace Yapeal;

use InvalidArgumentException;
use LogicException;
use PDO;
use Yapeal\Database\CommonSqlQueries;

require_once __DIR__ . '/bootstrap.php';
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
     * @param PDO              $pdo
     * @param CommonSqlQueries $csq
     */
    public function __construct(
        PDO $pdo,
        CommonSqlQueries $csq
    ) {
        $this->setPdo($pdo);
        $this->setCsq($csq);
    }
    /**
     * @return string
     */
    public function getActiveAPIMask()
    {
        return $this->activeAPIMask;
    }
    /**
     * @return string
     */
    public function getIsActive()
    {
        return (string)$this->isActive;
    }
    /**
     * @return string
     */
    public function getKeyID()
    {
        return $this->keyID;
    }
    /**
     * @return string
     */
    public function getVCode()
    {
        return $this->vCode;
    }
    /**
     * Method used to persist changes to the database.
     *
     * NOTE: After calling this method the MySQL PDO connection will be
     * switched to ANSI mode and use UTF-8.
     *
     * @see UtilRegisteredKey
     *
     * @throws LogicException
     * @return self
     */
    public function save()
    {
        $columnsNames = ['activeAPIMask', 'isActive', 'keyID', 'vCode'];
        $this->initPdo();
        $sql = $this->getCsq()
                    ->getUpsert('utilRegisterKey', $columnsNames, 1);
        $stmt = $this->getPdo()
                     ->prepare($sql);
        $columns = [
            $this->getActiveAPIMask(),
            $this->getIsActive(),
            $this->getKeyID(),
            $this->getVCode()
        ];
        $stmt->execute($columns);
        return $this;
    }
    /**
     * @param string $value
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
            $mess =
                'ActiveAPIMask MUST be an integer or integer string but was given '
                . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        if (!$this->isIntString($value)) {
            $mess =
                'ActiveAPIMask MUST be an integer or integer string but was given '
                . $value;
            throw new InvalidArgumentException($mess);
        }
        $this->activeAPIMask = $value;
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
     * @param string $value
     *
     * @return self
     */
    public function setIsActive($value)
    {
        $this->isActive = (bool)$value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setKeyID($value)
    {
        if (is_int($value)) {
            $value = (string)$value;
        }
        if (!is_string($value)) {
            $mess =
                'KeyID MUST be an integer or integer string but was given '
                . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        if (!$this->isIntString($value)) {
            $mess =
                'KeyID MUST be an integer or integer string but was given '
                . $value;
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
     * @param string $value
     *
     * @throws InvalidArgumentException
     * @return self
     */
    public function setVCode($value)
    {
        if (!is_string($value)) {
            $mess =
                'VCode MUST be a string but was given '
                . gettype($value);
            throw new InvalidArgumentException($mess);
        }
        $this->vCode = $value;
        return $this;
    }
    /**
     * @throws LogicException
     * @return CommonSqlQueries
     */
    protected function getCsq()
    {
        if (empty($this->csq)) {
            $mess = 'Tried to use csq before it was set';
            throw new LogicException($mess);
        }
        return $this->csq;
    }
    /**
     * @throws LogicException
     * @return PDO
     */
    protected function getPdo()
    {
        if (empty($this->pdo)) {
            $mess = 'Tried to use pdo before it was set';
            throw new LogicException($mess);
        }
        return $this->pdo;
    }
    protected function initPdo()
    {
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec("set session sql_mode='ANSI,TRADITIONAL'");
        $this->pdo->exec(
            'set session transaction isolation level serializable'
        );
        $this->pdo->exec("set session time_zone='+00:00'");
        $this->pdo->exec('set names utf8');
    }
    /**
     * @param string $value
     *
     * @return bool
     */
    protected function isIntString($value)
    {
        if (strlen($value) == 0) {
            return false;
        }
        if (strlen(
                str_replace(
                    ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                    '',
                    $value
                )
            ) !== 0
        ) {
            return false;
        }
        return true;
    }
    /**
     * @type string $activeAPIMask
     */
    protected $activeAPIMask;
    /**
     * @var CommonSqlQueries
     */
    protected $csq;
    /**
     * @type string $isActive
     */
    protected $isActive;
    /**
     * @type string $keyID
     */
    protected $keyID;
    /**
     * @var PDO
     */
    protected $pdo;
    /**
     * @type string $vCode
     */
    protected $vCode;
}
