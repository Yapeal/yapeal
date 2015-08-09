<?php
/**
 * Contains EventMediator class.
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

use EventMediator\PimpleContainerMediator;
use Yapeal\Log\Logger;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class EventMediator
 */
class EventMediator extends PimpleContainerMediator implements EventMediatorInterface
{
    /**
     * @param string                   $eventName
     * @param EveApiReadWriteInterface $data
     * @param EveApiEventInterface     $event
     *
     * @return EveApiEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function triggerEveApiEvent(
        $eventName,
        EveApiReadWriteInterface $data,
        EveApiEventInterface $event = null
    ) {
        if (null === $event) {
            $event = new EveApiEvent();
        }
        $event->setData($data);
        return $this->trigger($eventName, $event);
    }
    /**
     * @param string            $eventName
     * @param mixed             $level
     * @param string            $message
     * @param array             $context
     * @param LogEventInterface $event
     *
     * @return LogEventInterface
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function triggerLogEvent(
        $eventName,
        $level = Logger::DEBUG,
        $message = '',
        array $context = [],
        LogEventInterface $event = null
    ) {
        if (null === $event) {
            $event = new LogEvent();
        }
        $event->setLevel($level)
              ->setMessage($message)
              ->setContext($context);
        return $this->trigger($eventName, $event);
    }
}
