<?php
/**
 * Contains EveApiToolsTrait Trait.
 *
 * PHP version 5.4
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
namespace Yapeal\EveApi;

use LogicException;
use PDO;
use Yapeal\Event\EventDispatcherInterface;
use Yapeal\Sql\CommonSqlQueries;

/**
 * Trait EveApiToolsTrait
 */
trait EveApiToolsTrait
{
    /**
     * @param CommonSqlQueries $value
     *
     * @return self
     */
    public function setCsq(CommonSqlQueries $value)
    {
        $this->csq = $value;
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
     * @param EventDispatcherInterface $value
     *
     * @return self
     */
    public function setYed(EventDispatcherInterface $value)
    {
        $this->yed = $value;
        return $this;
    }
    /**
     * @throws LogicException
     * @return CommonSqlQueries
     */
    protected function getCsq()
    {
        if (!$this->csq instanceof CommonSqlQueries) {
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
        if (!$this->pdo instanceof PDO) {
            $mess = 'Tried to use pdo before it was set';
            throw new LogicException($mess);
        }
        return $this->pdo;
    }
    /**
     * @return EventDispatcherInterface|\Yapeal\Event\ContainerAwareEventDispatcherInterface
     * @throws LogicException
     */
    protected function getYed()
    {
        if (!$this->yed instanceof EventDispatcherInterface) {
            $mess = 'Tried to use yed before it was set';
            throw new LogicException($mess);
        }
        return $this->yed;
    }
    /**
     * @type CommonSqlQueries $csq
     */
    protected $csq;
    /**
     * @type PDO $pdo
     */
    protected $pdo;
    /**
     * @type EventDispatcherInterface|\Yapeal\Event\ContainerAwareEventDispatcherInterface $yed
     */
    protected $yed;
}
