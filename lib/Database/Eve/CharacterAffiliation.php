<?php
/**
 * Contains CharacterAffiliation class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2015 Michael Cummings
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
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database\Eve;

use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\EveSectionNameTrait;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class CharacterAffiliation
 */
class CharacterAffiliation extends AbstractCommonEveApi
{
    use EveApiNameTrait, EveSectionNameTrait, AttributesDatabasePreserverTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @return bool
     * @throws \LogicException
     */
    public function oneShot(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        &$interval
    ) {
        $ids = $data->getEveApiArgument('ids');
        if (null === $ids || '' === $ids) {
            $mess = sprintf(
                'Data is missing required "ids" parameter for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess);
            return false;
        }
        return parent::oneShot($data, $retrievers, $preservers, $interval);
    }
    /**
     * @param string $xml
     *
     * @return self
     * @throws \LogicException
     */
    protected function preserveToCharacterAffiliation($xml)
    {
        $columnDefaults = [
            'characterID' => null,
            'characterName' => null,
            'corporationID' => null,
            'corporationName' => null,
            'allianceID' => null,
            'allianceName' => null,
            'factionID' => null,
            'factionName' => null
        ];
        $tableName = 'eveCharacterAffiliation';
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName
        );
        return $this;
    }
}
