<?php
/**
 * Contains AccountSection class.
 *
 * PHP version 5.5
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
namespace Yapeal\EveApi\Account;

use LogicException;
use PDO;
use PDOException;
use Yapeal\EveApi\AbstractCommonEveApi;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;

/**
 * Class AccountSection
 */
class AccountSection extends AbstractCommonEveApi
{
    /**
     * @param EveApiEventInterface   $event
     * @param string                 $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws LogicException
     */
    public function startEveApi(EveApiEventInterface $event, $eventName, EventMediatorInterface $yem)
    {
        $this->setYem($yem);
        $data = $event->getData();
        $this->getYem()
             ->triggerLogEvent(
                 'Yapeal.Log.log',
                 Logger::DEBUG,
                 $this->getReceivedEventMessage($data, $eventName, __CLASS__)
             );
        $active = $this->getActive();
        if (0 === count($active)) {
            $mess = 'No active registered keys found';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::INFO, $mess);
            return $this->getYem()
                        ->triggerEveApiEvent('Yapeal.EveApi.end', $data);
        }
        $untilInterval = $data->getCacheInterval();
        foreach ($active as $key) {
            $ownerID = $key['keyID'];
            if ($this->cacheNotExpired($data->getEveApiName(), $data->getEveApiSectionName(), $ownerID)) {
                continue;
            }
            // Set arguments, reset interval, and clear xml data.
            $data->setEveApiArguments($key)
                 ->setCacheInterval($untilInterval)
                 ->setEveApiXml();
            if (!$this->oneShot($data)) {
                continue;
            }
            $this->updateCachedUntil($data, $ownerID);
        }
        return $this->getYem()
                    ->triggerEveApiEvent('Yapeal.EveApi.end', $data);
    }
    /**
     * @return array
     * @throws LogicException
     */
    protected function getActive()
    {
        $sql =
            $this->getCsq()
                 ->getActiveRegisteredKeys();
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            $stmt =
                $this->getPdo()
                     ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT select from utilRegisteredKeys';
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            $mess = 'Database error message was ' . $exc->getMessage();
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            return [];
        }
    }
}
