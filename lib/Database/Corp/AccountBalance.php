<?php
/**
 * Contains AccountBalance class.
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
namespace Yapeal\Database\Corp;

use PDO;
use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\ApiNameTrait;
use Yapeal\Database\AttributesDatabasePreserver;
use Yapeal\Database\DatabasePreserverInterface;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class AccountBalance
 */
class AccountBalance extends AbstractCommonEveApi
{
    use ApiNameTrait;
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
        $active = $this->getActiveCorporations();
        if (empty($active)) {
            $this->getLogger()
                 ->info('No active characters found');
            return;
        }
        $preserver = new AttributesDatabasePreserver(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq()
        );
        foreach ($active as $corp) {
            /**
             * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
             */
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $corp['corporationID']
            )
            ) {
                continue;
            }
            $accountKeys = range(1000, 1006);
            foreach ($accountKeys as $key) {
                $corp['accountKey'] = $key;
                $data->setEveApiArguments($corp)
                     ->setEveApiXml();
                if (!$this->gotApiLock($data)) {
                    continue;
                }
                $retrievers->retrieveEveApi($data);
                if ($data->getEveApiXml() === false) {
                    $mess = sprintf(
                        'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s division %4$s',
                        strtolower($this->getSectionName()),
                        $this->getApiName(),
                        $corp['corporationID'],
                        $key
                    );
                    $this->getLogger()
                         ->debug($mess);
                    continue 2;
                }
                $this->xsltTransform($data);
                if ($this->isInvalid($data)) {
                    $mess = sprintf(
                        'The data retrieved from Eve API %1$s/%2$s for %3$s division %4$s is invalid',
                        strtolower($this->getSectionName()),
                        $this->getApiName(),
                        $corp['corporationID'],
                        $key
                    );
                    $this->getLogger()
                         ->warning($mess);
                    $data->setEveApiName('Invalid' . $this->getApiName());
                    $preservers->preserveEveApi($data);
                    continue 2;
                }
                $preservers->preserveEveApi($data);
                $this->preserve(
                    $data->getEveApiXml(),
                    $corp['corporationID'],
                    $key,
                    $preserver
                );
            }
            $this->updateCachedUntil($data, $interval, $corp['corporationID']);
        }
    }
    /**
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
     * @param int                        $key
     * @param DatabasePreserverInterface $preserver
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        $key,
        DatabasePreserverInterface $preserver = null
    ) {
        if (is_null($preserver)) {
            $preserver = new AttributesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToAccountBalance($preserver, $xml, $ownerID, $key);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
        }
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $preserver
     * @param string                     $xml
     * @param string                     $ownerID
     * @param int                        $key
     *
     * @return self
     */
    protected function preserverToAccountBalance(
        DatabasePreserverInterface $preserver,
        $xml,
        $ownerID,
        $key
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'accountID' => null,
            'accountKey' => $key,
            'balance' => null
        );
        $preserver->setTableName('corpAccountBalance')
                  ->setColumnDefaults($columnDefaults)
                  ->preserveData($xml);
        return $this;
    }
    /**
     * @var int $mask
     */
    private $mask = 1;
}
