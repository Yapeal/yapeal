<?php
/**
 * Contains WalletJournal class.
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

use PDO;
use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\AttributesDatabasePreserver;
use Yapeal\Database\DatabasePreserverInterface;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class WalletJournal
 */
class WalletJournal extends AbstractCommonEveApi
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
            $char['accountKey'] = '1000';
            $char['rowCount'] = '2560';
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
                $char['accountKey']
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
     * @param string                     $accountKey
     * @param DatabasePreserverInterface $preserver
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        $accountKey,
        DatabasePreserverInterface $preserver = null
    ) {
        if (is_null($preserver)) {
            $preserver = new AttributesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        $this->preserverToWalletJournal(
            $preserver,
            $xml,
            $ownerID,
            $accountKey
        );
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $preserver
     * @param string                     $xml
     * @param string                     $ownerID
     * @param string                     $accountKey
     *
     * @return self
     */
    protected function preserverToWalletJournal(
        DatabasePreserverInterface $preserver,
        $xml,
        $ownerID,
        $accountKey
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'accountKey' => $accountKey,
            'date' => null,
            'refID' => null,
            'refTypeID' => null,
            'owner1TypeID' => null,
            'ownerID1' => null,
            'ownerName1' => null,
            'owner2TypeID' => null,
            'ownerID2' => null,
            'ownerName2' => null,
            'argID1' => null,
            'argName1' => null,
            'amount' => null,
            'balance' => null,
            'reason' => null,
            'taxReceiverID' => '0',
            'taxAmount' => '0'
        );
        $preserver->setTableName('charWalletJournal')
                  ->setColumnDefaults($columnDefaults)
                  ->preserveData($xml);
        return $this;
    }
    /**
     * @var int $mask
     */
    private $mask = 2097152;
}
