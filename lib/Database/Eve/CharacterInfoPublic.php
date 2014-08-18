<?php
/**
 * Contains CharacterInfoPublic class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Eve;

use PDOException;
use Yapeal\Database\Char\AbstractCharSection;
use Yapeal\Database\EveSectionNameTrait;

/**
 * Class CharacterInfoPublic
 */
class CharacterInfoPublic extends AbstractCharSection
{
    use EveSectionNameTrait;
    /**
     * @return string
     */
    protected function getApiName()
    {
        return 'CharacterInfo';
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToCharacterInfo($xml);
            $this->preserverToEmploymentHistory($xml, $ownerID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
        }
        return $this;
    }
    /**
     * @param string $xml
     *
     * @return self
     */
    protected function preserverToCharacterInfo(
        $xml
    ) {
        $columnDefaults = array(
            'characterID' => null,
            'characterName' => null,
            'race' => null,
            'bloodline' => null,
            'accountBalance' => '0',
            'skillPoints' => '0',
            'nextTrainingEnds' => '1970-01-01 00:00:01',
            'shipName' => '',
            'shipTypeID' => '0',
            'shipTypeName' => '',
            'corporationID' => null,
            'corporation' => null,
            'corporationDate' => null,
            'allianceID' => '0',
            'alliance' => '',
            'allianceDate' => '1970-01-01 00:00:01',
            'lastKnownLocation' => '',
            'securityStatus' => '0'
        );
        $this->getValuesDatabasePreserver()
             ->setTableName('eveCharacterInfo')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml);
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToEmploymentHistory(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'recordID' => null,
            'corporationID' => null,
            'corporationName' => null,
            'startDate' => null,
            'ownerID' => $ownerID
        );
        $this->getAttributesDatabasePreserver()
             ->setTableName('eveEmploymentHistory')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml, '//employmentHistory/row');
        return $this;
    }
    /**
     * @var int $mask
     */
    protected $mask = 8388608;
}
