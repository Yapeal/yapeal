<?php
/**
 * Contains EveApiEventEmitterTrait Trait.
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
namespace Yapeal\Event;

use EventMediator\ContainerMediatorInterface;
use LogicException;
use Yapeal\Log\Logger;
use Yapeal\Log\MessageBuilderTrait;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Trait EveApiEventEmitterTrait
 */
trait EveApiEventEmitterTrait
{
    use MessageBuilderTrait;
    /**
     * @param EventMediatorInterface $value
     *
     * @return self Fluent interface.
     */
    public function setYem(EventMediatorInterface $value)
    {
        $this->yem = $value;
        return $this;
    }
    /**
     * @param EveApiReadWriteInterface $data
     * @param string                   $eventSuffix
     *
     * @return bool
     * @throws \LogicException
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
            $this->getYem()
                 ->triggerLogEvent('Yapeal.Log.log', Logger::DEBUG, $this->getEmittingEventMessage($data, $eventName));
            $event =
                $this->getYem()
                     ->triggerEveApiEvent($eventName, $data);
            $data = $event->getData();
            if ($event->isSufficientlyHandled()) {
                $this->getYem()
                     ->triggerLogEvent(
                         'Yapeal.Log.log',
                         Logger::INFO,
                         $this->getSufficientlyHandledEventMessage($data, $eventName)
                     );
                continue;
            }
        }
        if (null === $event || !$event->isSufficientlyHandled()) {
            $this->getYem()
                 ->triggerLogEvent(
                     'Yapeal.Log.log',
                     Logger::WARNING,
                     $this->getNonHandledEventMessage($data, $eventSuffix)
                 );
            return false;
        }
        return true;
    }
    /**
     * @return EventMediatorInterface
     * @throws LogicException
     */
    protected function getYem()
    {
        if (!$this->yem instanceof ContainerMediatorInterface) {
            $mess = 'Tried to use yem before it was set';
            throw new LogicException($mess);
        }
        return $this->yem;
    }
    /**
     * @type EventMediatorInterface $yem
     */
    protected $yem;
}
