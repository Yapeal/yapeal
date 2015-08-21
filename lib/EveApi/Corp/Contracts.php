<?php
/**
 * Contains Contracts class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi\Corp;

use LogicException;
use Yapeal\Log\Logger;
use Yapeal\Sql\PreserverTrait;

/**
 * Class Contracts
 */
class Contracts extends CorpSection
{
    use PreserverTrait;
    /**
     *
     */
    public function __construct()
    {
        $this->mask = 8388608;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self Fluent interface.
     * @throws LogicException
     */
    protected function preserveToContracts($xml, $ownerID)
    {
        $columnDefaults = [
            'acceptorID' => null,
            'assigneeID' => null,
            'availability' => null,
            'buyout' => null,
            'collateral' => null,
            'contractID' => null,
            'dateAccepted' => null,
            'dateCompleted' => null,
            'dateExpired' => null,
            'dateIssued' => null,
            'endStationID' => null,
            'forCorp' => null,
            'issuerCorpID' => null,
            'issuerID' => null,
            'numDays' => null,
            'ownerID' => $ownerID,
            'price' => null,
            'reward' => null,
            'startStationID' => null,
            'status' => null,
            'title' => null,
            'type' => null,
            'volume' => null
        ];
        $tableName = 'corpContracts';
        $sql =
            $this->getCsq()
                 ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData($xml, $columnDefaults, $tableName);
        return $this;
    }
}
