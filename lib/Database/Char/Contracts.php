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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Char;

use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;

/**
 * Class Contracts
 */
class Contracts extends AbstractCharSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @internal param int $key
     * @return self
     */
    protected function preserverToContracts(
        $xml,
        $ownerID
    ) {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'contractID' => null,
            'issuerID' => null,
            'issuerCorpID' => null,
            'assigneeID' => null,
            'acceptorID' => null,
            'startStationID' => null,
            'endStationID' => null,
            'type' => null,
            'status' => null,
            'title' => null,
            'forCorp' => null,
            'availability' => null,
            'dateIssued' => '1970-01-01 00:00:01',
            'dateExpired' => '1970-01-01 00:00:01',
            'dateAccepted' => '1970-01-01 00:00:01',
            'numDays' => null,
            'dateCompleted' => '1970-01-01 00:00:01',
            'price' => null,
            'reward' => null,
            'collateral' => null,
            'buyout' => null,
            'volume' => null
        ];
        $this->attributePreserveData($xml, $columnDefaults, 'charContracts');
        return $this;
    }
    /**
     * @type int $mask
     */
    protected $mask = 67108864;
}
