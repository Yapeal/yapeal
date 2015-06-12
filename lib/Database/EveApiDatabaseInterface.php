<?php
/**
 * Contains EveApiDatabaseInterface Interface.
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
namespace Yapeal\Database;

use LogicException;
use PDO;
use Psr\Log\LoggerInterface;
use Yapeal\Sql\CommonSqlQueries;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class AbstractCommonEveApi
 */
interface EveApiDatabaseInterface
{
    /**
     * @param PDO              $pdo
     * @param LoggerInterface  $logger
     * @param CommonSqlQueries $csq
     */
    public function __construct(
        PDO $pdo,
        LoggerInterface $logger,
        CommonSqlQueries $csq
    );
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @api
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    );
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @api
     * @throws LogicException
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        &$interval
    );
    /**
     * @param CommonSqlQueries $value
     *
     * @api
     * @return self
     */
    public function setCsq(CommonSqlQueries $value);
    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     *
     * @api
     */
    public function setLogger(LoggerInterface $logger);
    /**
     * @param PDO $value
     *
     * @api
     * @return self
     */
    public function setPdo(PDO $value);
}
