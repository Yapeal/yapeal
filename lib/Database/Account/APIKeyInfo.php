<?php
/**
 * Contains APIKeyInfo class.
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
namespace Yapeal\Database\Account;

use PDO;
use PDOException;
use SimpleXMLIterator;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\AttributesDatabasePreserver;
use Yapeal\Database\DatabasePreserverInterface;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class APIKeyInfo
 */
class APIKeyInfo extends AbstractCommonEveApi
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
        $activeKeys = $this->getActiveKeys();
        if (empty($activeKeys)) {
            $this->getLogger()
                ->info('No active registered keys found');
            return;
        }
        /**
         * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
         */
        $data->setEveApiSectionName(strtolower($this->getSectionName()))
             ->setEveApiName($this->getApiName());
        foreach ($activeKeys as $key) {
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $key['keyID']
            )
            ) {
                continue;
            }
            $data->setEveApiArguments($key)
                 ->setEveApiXml();
            $retrievers->retrieveEveApi($data);
            if ($data->getEveApiXml() === false) {
                $mess =
                    'Could NOT retrieve Eve Api data for registered key '
                    . $key['keyID'];
                $this->getLogger()
                     ->debug($mess);
                continue;
            }
            $this->transformRowset($data);
            if ($this->isInvalid($data)) {
                $mess = 'Data retrieved is invalid for registered key '
                    . $key['keyID'];
                $this->getLogger()
                     ->warning($mess);
                $data->setEveApiName('Invalid' . $this->getApiName());
                $preservers->preserveEveApi($data);
                continue;
            }
            $preservers->preserveEveApi($data);
            $preserver = new AttributesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
            $this->preserveToCharacters($preserver, $data->getEveApiXml());
            $this->preserveToAPIKeyInfo(
                $preserver,
                $data->getEveApiXml(),
                $key['keyID']
            );
            $this->preserveToKeyBridge($data->getEveApiXml(), $key['keyID']);
            $this->updateCachedUntil($data, $interval, $key['keyID']);
        }
    }
    /**
     * @return array
     */
    protected function getActiveKeys()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredKeys();
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT select from utilRegisteredKeys';
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
     * @param DatabasePreserverInterface $preserver
     * @param string                     $xml
     * @param string                     $key
     *
     * @return self
     */
    protected function preserveToAPIKeyInfo(
        DatabasePreserverInterface $preserver,
        &$xml,
        $key
    ) {
        $columnDefaults = array(
            'keyID' => $key,
            'accessMask' => null,
            'expires' => '2038-01-19 03:14:07',
            'type' => null
        );
        $preserver->setTableName('accountAPIKeyInfo')
                  ->setColumnDefaults($columnDefaults)
                  ->preserveData($xml, '//key');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $preserver
     * @param string                     $xml
     */
    protected function preserveToCharacters(
        DatabasePreserverInterface $preserver,
        &$xml
    ) {
        $columnDefaults = array(
            'characterID' => null,
            'characterName' => null,
            'corporationID' => null,
            'corporationName' => null,
            'allianceID' => null,
            'allianceName' => null,
            'factionID' => null,
            'factionName' => null
        );
        $preserver->setTableName('accountCharacters')
                  ->setColumnDefaults($columnDefaults)
                  ->preserveData($xml, '//row');
    }
    /**
     * @param string $xml
     * @param string $key
     *
     * @return self
     */
    protected function preserveToKeyBridge(
        &$xml,
        $key
    ) {
        $simple = new SimpleXMLIterator($xml);
        $chars = $simple->xpath('//row');
        $rows = array();
        foreach ($chars as $aRow) {
            $rows[] = $key;
            $rows[] = $aRow['characterID'];
        }
        $sql = $this->getCsq()
                    ->getUpsert(
                        'accountKeyBridge',
                        array('keyID', 'characterID'),
                        count($chars)
                    );
        $this->getLogger()
             ->debug($sql);
        try {
            $this->getPdo()
                 ->beginTransaction();
            $stmt = $this->getPdo()
                         ->prepare($sql);
            $stmt->execute($rows);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = 'Failed to upsert row(s) into accountKeyBridge table';
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
        }
    }
}
