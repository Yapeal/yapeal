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

use PDO;
use PDOException;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class AbstractAccountKey
 *
 * @property-read int $mask
 * @property-read int $maxKeyRange
 */
abstract class AbstractAccountKey extends AbstractCommonEveApi
{
    /**
     * @var int
     */
    protected $mask;
    /**
     * @var int
     */
    protected $maxKeyRange;
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
             ->debug(
             sprintf(
                 'Starting autoMagic for %1$s/%2$s',
                 $this->getSectionName(),
                 $this->getApiName()
             )
            );
        $active = $this->getActiveKeys();
        if (empty($active)) {
            $this->getLogger()
                 ->info('No active registered corporations found');
            return;
        }
        foreach ($active as $key) {
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->getSectionName() == 'Char') {
                $ownerID = $key['characterID'];
            } else {
                $ownerID = $key['corporationID'];
            }
            if ($this->cacheNotExpired(
                     $this->getApiName(),
                         $this->getSectionName(),
                         $ownerID
            )
            ) {
                continue;
            }
            foreach (range(1000, $this->getMaxKeyRange()) as $account) {
                $data->addEveApiArgument('accountKey', $account);
                if (strpos($this->getApiName(), 'wallet')) {
                    $data->addEveApiArgument('rowCount', '2560');
                }
                $data->setEveApiArguments($key)
                     ->setEveApiXml();
                if (!$this->oneShot($data, $retrievers, $preservers)) {
                    continue;
                }
            }
            $this->updateCachedUntil($data, $interval, $ownerID);
        }
    }
    /**
     * @return array
     */
    protected function getActiveKeys()
    {
        if ($this->getSectionName() == 'Char') {
            $sql = $this->csq->getActiveRegisteredCharacters($this->getMask());
        } else {
            $sql =
                $this->csq->getActiveRegisteredCorporations($this->getMask());
        }
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
     * @throws \LogicException
     * @return int
     */
    protected function getMask()
    {
        if (is_null($this->mask)) {
            throw new \LogicException('Trying to use mask when NOT set');
        }
        return $this->mask;
    }
    /**
     * @return int
     */
    protected function getMaxKeyRange()
    {
        return $this->maxKeyRange;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     *
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
        $key = $data->getEveApiArguments();
        if ($this->getSectionName() == 'Char') {
            $ownerID = $key['characterID'];
        } else {
            $ownerID = $key['corporationID'];
        }
        $accountKey = $key['accountKey'];
        /**
         * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
         */
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID
            );
            $this->getLogger()
                 ->notice($mess);
            return false;
        }
        $this->xsltTransform($data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'The data retrieved from Eve API %1$s/%2$s for %3$s is invalid',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID
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
     * @param string $xml
     * @param string $ownerID
     * @param int    $accountKey
     *
     * @return self
     */
    abstract protected function preserve(
        $xml,
        $ownerID,
        $accountKey = 1000
    );
}
