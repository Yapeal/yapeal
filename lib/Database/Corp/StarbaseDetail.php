<?php
/**
 * Contains MemberTrackingExtended class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014 Michael Cummings
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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Corp;

use LogicException;
use PDO;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\ValuesDatabasePreserverTrait;
use Yapeal\Event\EveApiEvent;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class MemberTrackingExtended
 */
class StarbaseDetail extends AbstractCorpSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait, ValuesDatabasePreserverTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @throws LogicException
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    )
    {
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::START, $data);
        $this->getLogger()
             ->debug(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        /**
         * Update Starbase List
         */
        (
        new StarbaseList(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq(),
            $this->getYed()
        )
        )->autoMagic($data, $retrievers, $preservers, $interval);
        $activeCorps = $this->getActiveCorporations();
        if (empty($activeCorps)) {
            $this->getLogger()
                 ->info('No active registered corporations found');
            return;
        }
        foreach ($activeCorps as $corp) {
            $corpID = $corp['corporationID'];
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $corpID
            )
            ) {
                continue;
            }
            $activeTowers = $this->getActiveTowers($corpID);
            if (empty($activeTowers)) {
                $mess = sprintf(
                    'No active Starbase(s) found for %1$s',
                    $corpID
                );
                $this->getLogger()
                     ->info($mess);
                continue;
            }
            $untilInterval = $interval;
            foreach ($activeTowers as $tower) {
                $data->setEveApiSectionName(strtolower($this->getSectionName()))
                     ->setEveApiName($this->getApiName());
                $data->setEveApiArguments($tower)
                     ->setEveApiXml();
                $untilInterval = $interval;
                if (!$this->oneShot(
                    $data,
                    $retrievers,
                    $preservers,
                    $untilInterval
                )
                ) {
                    continue 2;
                }
                $this->getYed()
                    ->dispatchEveApiEvent(EveApiEvent::POST_PRESERVE, $data);
                if ($untilInterval != $interval) {
                    continue;
                }
            }
            $this->updateCachedUntil(
                $data->getEveApiXml(),
                $untilInterval,
                $corpID
            );
        }
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::DONE, $data);
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     *
     * @throws LogicException
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        &$interval
    )
    {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $corpID = $data->getEveApiArgument('corporationID');
        $itemID = $data->getEveApiArgument('itemID');
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::PRE_RETRIEVE, $data);
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $corpID
            );
            $this->getLogger()
                 ->notice($mess);
            return false;
        }
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::PRE_TRANSFORM, $data);
        $this->xsltTransform($data);
        $this->getYed()
             ->dispatchEveApiEvent(EveApiEvent::PRE_VALIDATE, $data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'The data retrieved from Eve API %1$s/%2$s for %3$s is invalid',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $corpID
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return false;
        }
        $this->getYed()
            ->dispatchEveApiEvent(EveApiEvent::PRE_PRESERVE, $data);
        $preservers->preserveEveApi($data);
        // No need / way to preserve XML errors to the database with normal
        // preserve.
        if ($this->isEveApiXmlError($data, $interval)) {
            return true;
        }
        $this->preserve($data->getEveApiXml(), $corpID, $itemID);
        return true;
    }
    /**
     * @param string $ownerID
     *
     * @throws LogicException
     * @return array
     */
    protected function getActiveTowers($ownerID)
    {
        $sql = $this->csq->getActiveStarbaseTowers($this->getMask(), $ownerID);
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Could NOT get a list of Starbases for %1$s',
                $ownerID
            );
            $this->getLogger()
                 ->warning($mess, ['exception' => $exc]);
            return [];
        }
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param null   $itemID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserve($xml, $ownerID, $itemID = null)
    {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToStarbaseDetail($xml, $ownerID, $itemID);
            $this->preserverToFuel($xml, $ownerID, $itemID);
            $this->preserverToCombatSettings($xml, $ownerID, $itemID);
            $this->preserverToGeneralSettings($xml, $ownerID, $itemID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess, ['exception' => $exc]);
            $this->getPdo()
                 ->rollBack();
        }
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $itemID
     *
     * @return self
     */
    protected function preserverToCombatSettings($xml, $ownerID, $itemID)
    {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'itemID' => $itemID,
            'onAggressionEnabled' => '0',
            'onCorporationWarEnabled' => '0',
            'onStandingDropStanding' => '0',
            'onStatusDropEnabled' => '0',
            'onStatusDropStanding' => '0',
            'useStandingsFromOwnerID' => '0',
        ];
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'corpCombatSettings',
            '//combatSettings/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $itemID
     *
     * @return self
     */
    protected function preserverToFuel($xml, $ownerID, $itemID)
    {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'itemID' => $itemID,
            'typeID' => '0',
            'quantity' => '0'
        ];
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'corpFuel',
            '//fuel/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $itemID
     *
     * @return self
     */
    protected function preserverToGeneralSettings($xml, $ownerID, $itemID)
    {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'itemID' => $itemID,
            'usageFlags' => '0',
            'deployFlags' => '0',
            'allowCorporationMembers' => '0',
            'allowAllianceMembers' => '0'
        ];
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            'corpGeneralSettings',
            '//generalSettings/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $itemID
     *
     * @return self
     */
    protected function preserverToStarbaseDetail($xml, $ownerID, $itemID)
    {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'itemID' => $itemID,
            'onlineTimestamp' => '1970-01-01 00:00:01',
            'state' => '0',
            'stateTimestamp' => '1970-01-01 00:00:01'
        ];
        $this->valuesPreserveData($xml, $columnDefaults, 'corpStarbaseDetail');
        return $this;
    }
    /**
     * @type int $mask
     */
    protected $mask = 131072;
}
