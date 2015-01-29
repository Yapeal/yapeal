<?php
/**
 * Contains AbstractCommonEveApi class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2015 Michael Cummings
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
 * @copyright 2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\EveApi;

use FilePathNormalizer\FilePathNormalizerTrait;
use LogicException;
use PDO;
use PDOException;
use Psr\Log\LoggerAwareTrait;
use SimpleXMLElement;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class AbstractCommonEveApi
 */
abstract class AbstractCommonEveApi
{
    use LoggerAwareTrait, EveApiToolsTrait, FilePathNormalizerTrait;
    /**
     * @param string $apiName
     * @param string $sectionName
     * @param string $ownerID
     *
     * @return bool
     * @throws LogicException
     */
    protected function cacheNotExpired($apiName, $sectionName, $ownerID = '0')
    {
        $mess = sprintf(
            'Checking if cache expired on table %1$s%2$s for ownerID = %3$s',
            $sectionName,
            $apiName,
            $ownerID
        );
        $this->getYed()
            ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        $sql = $this->getCsq()
                    ->getUtilCachedUntilExpires(
                        $apiName,
                        $sectionName,
                        $ownerID
                    );
        $this->getYed()
            ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        $stmt = $this->getPdo()
                     ->query($sql);
        $expires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (0 === count($expires)) {
            $mess = 'No UtilCachedUntil record found for ownerID = ' . $ownerID;
            $this->getYed()
                ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return false;
        }
        if (1 < count($expires)) {
            $mess
                =
                'Multiple UtilCachedUntil record found for ownerID = '
                . $ownerID;
            $this->getYed()
                 ->dispatchLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return false;
        }
        if (strtotime($expires[0]['expires'] . '+00:00') < time()) {
            $mess
                =
                'Expired UtilCachedUntil record found for ownerID = '
                . $ownerID;
            $this->getYed()
                ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return false;
        }
        return true;
    }
    /**
     * @param EveApiReadWriteInterface $data
     *
     * @throws LogicException
     * @return bool
     */
    protected function gotApiLock(EveApiReadWriteInterface &$data)
    {
        $sql = $this->getCsq()
                    ->getApiLock($data->getHash());
        $this->getYed()
             ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            $lock = (bool)$stmt->fetchColumn();
            if (false !== $lock) {
                $mess = sprintf(
                    'Got lock for %1$s/%2$s',
                    $data->getEveApiSectionName(),
                    $data->getEveApiName()
                );
                $this->getYed()
                     ->dispatchLogEvent(
                         'Yapeal.Log.log',
                         Logger::DEBUG,
                         $mess
                     );
            }
            return $lock;
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Could NOT get lock for %1$s/%2$s',
                $data->getEveApiSectionName(),
                $data->getEveApiName()
            );
            $this->getYed()
                 ->dispatchLogEvent(
                     'Yapeal.Log.log',
                     Logger::WARNING,
                     $mess,
                     ['exception' => $exc]
                 );
            return false;
        }
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $ownerID
     *
     * @throws LogicException
     */
    protected function updateCachedUntil(
        EveApiReadWriteInterface &$data,
        $ownerID
    ) {
        if (false === $data->getEveApiXml()) {
            return;
        }
        $simple = new SimpleXMLElement($data->getEveApiXml());
        if (null === $simple->currentTime[0]) {
            return;
        }
        $dateTime = gmdate(
            'Y-m-d H:i:s',
            strtotime($simple->currentTime[0] . '+00:00')
            + $data->getCacheInterval()
        );
        $row = [
            $data->getEveApiName(),
            $dateTime,
            $ownerID,
            $data->getEveApiSectionName()
        ];
        $sql = $this->getCsq()
                    ->getUtilCachedUntilUpsert();
        $pdo = $this->getPdo();
        try {
            $pdo->beginTransaction();
            $statement = $pdo->prepare($sql);
            $statement->execute($row);
            $pdo->commit();
        } catch (PDOException $exc) {
            $pdo->rollBack();
            $mess = sprintf(
                'Could NOT update cached until time of Eve API %1$s/%2$s for ownerID = %3$s',
                $data->getEveApiSectionName(),
                $data->getEveApiName(),
                $ownerID
            );
            $this->getYed()
                ->dispatchLogEvent('Yapeal.Log.log', Logger::NOTICE, $mess);
            $mess = 'Database error message was ' . $exc->getMessage();
            $this->getYed()
                 ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return;
        }
        $mess = sprintf(
            'Updated cached until time of Eve API %1$s/%2$s for ownerID = %3$s',
            $data->getEveApiSectionName(),
            $data->getEveApiName(),
            $ownerID
        );
        $this->getYed()
            ->dispatchLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
    }
}
