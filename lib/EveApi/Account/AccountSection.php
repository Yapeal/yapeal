<?php
/**
 * Contains AccountSection class.
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
namespace Yapeal\EveApi\Account;

use LogicException;
use PDO;
use PDOException;
use Yapeal\EveApi\EveSectionNameTrait;
use Yapeal\EveApi\AbstractCommonEveApi;
use Yapeal\Event\EveApiEventInterface;
use Yapeal\Event\EventMediatorInterface;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class AccountSection
 */
class AccountSection extends AbstractCommonEveApi
{
    use EveSectionNameTrait;
    /**
     * @param EveApiEventInterface $event
     * @param string $eventName
     * @param EventMediatorInterface $yem
     *
     * @return EveApiEventInterface
     * @throws LogicException
     */
    public function eveApiStart(
        EveApiEventInterface $event,
        $eventName,
        EventMediatorInterface $yem
    ) {
        $this->setYem($yem);
        $data = $event->getData();
        $mess = sprintf(
            'Received %1$s event for %2$s/%3$s in %4$s',
            $eventName,
            ucfirst($data->getEveApiSectionName()),
            $data->getEveApiName(),
            __CLASS__
        );
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
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
            if ($this->cacheNotExpired(
                $data->getEveApiName(),
                $data->getEveApiSectionName(),
                $ownerID
            )
            ) {
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
     * @param EveApiReadWriteInterface $data
     *
     * @return bool
     * @throws LogicException
     */
    public function oneShot(EveApiReadWriteInterface $data)
    {
        $mess = sprintf(
            'Starting %1$s/%2$s::oneShot() for ownerID = %3$s',
            ucfirst($data->getEveApiSectionName()),
            $data->getEveApiName(),
            $data->getEveApiArgument('keyID')
        );
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
        if (!$this->gotApiLock($data)) {
            return false;
        }
        $eventSuffixes = ['retrieve', 'transform', 'validate', 'preserve'];
        foreach ($eventSuffixes as $eventSuffix) {
            $mess = sprintf('Emit %1$s events', $eventSuffix);
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            if (!$this->emitEvents($data, $eventSuffix)) {
                break;
            }
            if (false === $data->getEveApiXml()) {
                $mess
                    = sprintf(
                        'Eve API %1$s/%2$s data empty after %4$s event for ownerID = %3$s',
                        $data->getEveApiSectionName(),
                        $data->getEveApiName(),
                        $data->getEveApiArgument('keyID'),
                        $eventSuffix
                    );
                $this->getYem()
                     ->triggerLogEvent(
                         'Yapeal.Log.log',
                         Logger::NOTICE,
                         $mess
                     );
                return false;
            }
        }
        return true;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventSuffix
     *
     * @return bool
     * @throws LogicException
     */
    protected function emitEvents(EveApiReadWriteInterface $data, $eventSuffix)
    {
        // Yapeal.EveApi.Section.Api.Suffix, Yapeal.EveApi.Api.Suffix,
        // Yapeal.EveApi.Section.Suffix, Yapeal.EveApi.Suffix
        $eventNames = explode(
            ',',
            sprintf(
                '%3$s.%1$s.%2$s.%4$s,%3$s.%2$s.%4$s,%3$s.%1$s.%4$s,%3$s.%4$s',
                ucfirst($data->getEveApiSectionName()),
                $data->getEveApiName(),
                'Yapeal.EveApi',
                $eventSuffix
            )
        );
        $event = null;
        foreach ($eventNames as $eventName) {
            $mess = 'Emitting event ' . $eventName;
            if (!$this->getYem()
                      ->hasListeners($eventName)
            ) {
                continue;
            }
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
            $event = $this->getYem()
                          ->triggerEveApiEvent($eventName, $data);
            $data = $event->getData();
            if ($event->hasBeenHandled()) {
                $mess = 'Handled event ' . $eventName;
                $this->getYem()
                     ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $mess);
                break;
            }
        }
        if (null === $event || !$event->hasBeenHandled()) {
            $mess
                = sprintf(
                    'Nothing reported handling %4$s event of Eve API %1$s/%2$s for ownerID = %3$s',
                    lcfirst($data->getEveApiSectionName()),
                    $data->getEveApiName(),
                    $data->getEveApiArgument('keyID'),
                    $eventSuffix
                );
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::WARNING, $mess);
            return false;
        }
        return true;
    }
    /**
     * @throws LogicException
     * @return array
     */
    protected function getActive()
    {
        $sql = $this->getCsq()
                    ->getActiveRegisteredKeys();
        $this->getYem()
             ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $sql);
        try {
            $stmt = $this->getPdo()
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
