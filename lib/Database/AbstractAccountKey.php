<?php
/**
 * Contains AbstractAccountKey class.
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
namespace Yapeal\Database;

use LogicException;
use PDO;
use PDOException;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class AbstractAccountKey
 *
 * @property-read int $mask
 * @property-read int $maxKeyRange
 */
abstract class AbstractAccountKey extends AbstractCommonEveApi
{
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
    ) {
        $this->getLogger()
             ->debug(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        if ($this->getSectionName() == 'Char') {
            $active = $this->getActiveCharacters();
            $ownerID = 'characterID';
        } else {
            $active = $this->getActiveCorporations();
            $ownerID = 'corporationID';
        }
        if (empty($active)) {
            $mess = sprintf(
                'No active registered keys found for %1$s/%2$s',
                $this->getSectionName(),
                $this->getApiName()
            );
            $this->getLogger()
                ->info($mess);
            return;
        }
        foreach ($active as $activeKey) {
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $activeKey[$ownerID]
            )
            ) {
                continue;
            }
            foreach (range(1000, $this->getMaxKeyRange()) as $accountKey) {
                $data->setEveApiSectionName(strtolower($this->getSectionName()))
                     ->setEveApiName($this->getApiName());
                $activeKey['accountKey'] = $accountKey;
                if (strpos($this->getApiName(), 'wallet')) {
                    $data->addEveApiArgument('rowCount', '2560');
                }
                $data->setEveApiArguments($activeKey)
                     ->setEveApiXml();
                if (!$this->oneShot($data, $retrievers, $preservers)) {
                    continue 2;
                }
            }
            $this->updateCachedUntil($data, $interval, $activeKey[$ownerID]);
        }
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     *
     * @throws LogicException
     * @return bool
     */
    public function oneShot(
        EveApiReadWriteInterface &$data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers
    ) {
        if (!$this->gotApiLock($data)) {
            return false;
        }
        if ($this->getSectionName() == 'Char') {
            $ownerID = $data->getEveApiArgument('characterID');
        } else {
            $ownerID = $data->getEveApiArgument('corporationID');
        }
        $accountKey = $data->getEveApiArgument('accountKey');
        /**
         * @var EveApiReadWriteInterface $data
         */
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s on account %4$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID,
                $accountKey
            );
            $this->getLogger()
                 ->notice($mess);
            return false;
        }
        $this->xsltTransform($data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'The data retrieved from Eve API %1$s/%2$s for %3$s on account %4$s is invalid',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID,
                $accountKey
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return false;
        }
        $preservers->preserveEveApi($data);
        $this->preserve(
            $data->getEveApiXml(),
            $ownerID,
            $accountKey
        );
        return true;
    }
    /**
     * @throws \LogicException
     * @return array
     */
    protected function getActiveCharacters()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredCharacters($this->getMask());
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
     * @throws \LogicException
     * @return array
     */
    protected function getActiveCorporations()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredCorporations($this->getMask());
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of active corporations';
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            return array();
        }
    }
    /**
     * @throws LogicException
     * @return int
     */
    protected function getMask()
    {
        if (is_null($this->mask)) {
            $mess = 'Tried to use mask when it was NOT set';
            throw new LogicException($mess);
        }
        return $this->mask;
    }
    /**
     * @throws LogicException
     * @return int
     */
    protected function getMaxKeyRange()
    {
        if (is_null($this->maxKeyRange)) {
            $mess = 'Tried to use max key range when it was NOT set';
            throw new LogicException($mess);
        }
        return $this->maxKeyRange;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param string $accountKey
     *
     * @throws LogicException
     * @return bool
     */
    protected function preserve(
        $xml,
        $ownerID,
        $accountKey
    ) {
        $pTo = 'preserverTo' . $this->getApiName();
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->$pTo($xml, $ownerID, $accountKey);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s on account %4$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID,
                $accountKey
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
            return false;
        }
        return true;
    }
    /**
     * @var int $mask
     */
    protected $mask;
    /**
     * @var int $maxKeyRange
     */
    protected $maxKeyRange;
}
