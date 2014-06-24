<?php
/**
 * Contains CharacterSheet class.
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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Char;

use PDO;
use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\AttributesDatabasePreserver;
use Yapeal\Database\DatabasePreserverInterface;
use Yapeal\Database\ValuesDatabasePreserver;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class CharacterInfoExtended
 */
class CharacterInfoExtended extends AbstractCommonEveApi
{
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    ) {
        $this->getLogger()
             ->info(
             sprintf(
                 'Starting autoMagic for %1$s/%2$s',
                 $this->getSectionName(),
                 $this->getApiName()
             )
            );
        $active = $this->getActiveCharacters();
        if (empty($active)) {
            $this->getLogger()
                 ->info('No active characters found');
            return;
        }
        $aPreserver = new AttributesDatabasePreserver(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq()
        );
        $vPreserver = new ValuesDatabasePreserver(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq()
        );
        foreach ($active as $char) {
            /**
             * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
             */
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->cacheNotExpired(
                     $this->getApiName(),
                         $this->getSectionName(),
                         $char['characterID']
            )
            ) {
                continue;
            }
            $data->setEveApiArguments($char)
                 ->setEveApiXml();
            $retrievers->retrieveEveApi($data);
            if ($data->getEveApiXml() === false) {
                $mess = sprintf(
                    'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s',
                    strtolower($this->getSectionName()),
                    $this->getApiName(),
                    $char['characterID']
                );
                $this->getLogger()
                     ->debug($mess);
                continue;
            }
            $this->transformRowset($data);
            if ($this->isInvalid($data)) {
                $mess = sprintf(
                    'The data retrieved from Eve API %1$s/%2$s for %3$s is invalid',
                    strtolower($this->getSectionName()),
                    $this->getApiName(),
                    $char['characterID']
                );
                $this->getLogger()
                     ->warning($mess);
                $data->setEveApiName('Invalid' . $this->getApiName());
                $preservers->preserveEveApi($data);
                continue;
            }
            $preservers->preserveEveApi($data);
            $this->preserve(
                 $data->getEveApiXml(),
                     $char['characterID'],
                     $aPreserver,
                     $vPreserver
            );
            $this->updateCachedUntil($data, $interval, $char['characterID']);
        }
    }
    /**
     * @return array
     */
    protected function getActiveCharacters()
    {
        $sql = $this->csq->getActiveRegisteredCharacters($this->getMask());
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of active characters';
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            return array();
        }
    }
    /**
     * @return string
     */
    protected function getApiName()
    {
        if (empty($this->apiName)) {
            $this->apiName = basename(str_replace('\\', '/', __CLASS__));
        }
        return $this->apiName;
    }
    /**
     * @return int
     */
    protected function getMask()
    {
        return $this->mask;
    }
    /**
     * @return string
     */
    protected function getSectionName()
    {
        if (empty($this->sectionName)) {
            $this->sectionName = basename(str_replace('\\', '/', __DIR__));
        }
        return $this->sectionName;
    }
    /**
     * @param string                     $xml
     * @param string                     $ownerID
     * @param DatabasePreserverInterface $aPreserver
     * @param DatabasePreserverInterface $vPreserver
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        DatabasePreserverInterface $aPreserver = null,
        DatabasePreserverInterface $vPreserver = null
    ) {
        if (is_null($aPreserver)) {
            $aPreserver = new AttributesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        if (is_null($vPreserver)) {
            $vPreserver = new ValuesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        $this->preserverToEmploymentHistory($vPreserver, $xml, $ownerID);
        $this->preserverToCharacterInfo($aPreserver, $xml);
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToEmploymentHistory(
        DatabasePreserverInterface $aPreserver,
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'recordID' => null,
            'corporationID' => null,
            'startDate' => null,
            'ownerID' => $ownerID
        );
        $aPreserver->setTableName('eveEmploymentHistory')
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//employmentHistory/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $vPreserver
     * @param string                     $xml
     *
     * @return self
     */
    protected function preserverToCharacterInfo(
        DatabasePreserverInterface $vPreserver,
        $xml
    ) {
        $columnDefaults = array(
            'characterID' => null,
            'characterName' => null,
            'race' => null,
            'bloodline' => null,
            'accountBalance' => 0,
            'skillPoints' => 0,
            'shipName' => '',
            'shipTypeID' => 0,
            'shipTypeName' => '',
            'corporationID' => null,
            'corporationDate' => null,
            'allianceID' => null,
            'allianceDate' => null,
            'lastKnownLocation' => '',
            'securityStatus' => 0
        );
        $vPreserver->setTableName('eveCharacterInfo')
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml);
        return $this;
    }
    /**
     * @var int $mask
     */
    private $mask = 16777216;
}
