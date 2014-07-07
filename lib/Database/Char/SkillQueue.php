<?php
/**
 * Contains SkillQueue class.
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
namespace Yapeal\Database\Char;

use LogicException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;

/**
 * Class SkillQueue
 */
class SkillQueue extends AbstractCharSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToSkillQueue(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'queuePosition' => null,
            'typeID' => null,
            'level' => null,
            'startSP' => null,
            'endSP' => null,
            'startTime' => '1972-01-01 00:00:01',
            'endTime' => '1972-01-01 00:00:01'
        );
        $tableName = 'charSkillQueue';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData($xml, $columnDefaults, $tableName);
        return $this;
    }
    /**
     * @var int $mask
     */
    protected $mask = 262144;
}
