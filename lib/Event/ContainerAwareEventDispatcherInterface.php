<?php
/**
 * Contains ContainerAwareEventDispatcherInterface Interface.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
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
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 *
 * Additional licence and copyright information:
 * @copyright 2004-2014 Fabien Potencier <fabien@symfony.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 */
namespace Yapeal\Event;

use InvalidArgumentException;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Interface ContainerAwareEventDispatcherInterface
 *
 * The ContainerAwareEventDispatcherInterface is the central point of Symfony's
 * event listener system. Listeners are registered on the manager and events
 * are dispatched through the manager.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
interface ContainerAwareEventDispatcherInterface extends
    EventDispatcherInterface
{
    /**
     * Adds a service as event listener
     *
     * @param string $eventName Event for which the listener is added
     * @param array  $callback  The service ID of the listener service & the
     *                          method name that has to be called
     * @param int    $priority  The higher this value, the earlier an event
     *                          listener will be triggered in the chain.
     *                          Defaults to 0.
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public function addListenerService(
        $eventName,
        array $callback,
        $priority = 0
    );
    /**
     * Adds a service as event subscriber
     *
     * @param string $serviceId The service ID of the subscriber service
     * @param string $class     The service's class name which SHOULD implement
     *                          EventSubscriberInterface, and MUST have a public
     *                          static function getSubscribedEvents()
     *
     * @return self
     */
    public function addSubscriberService($serviceId, $class);
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string                   $eventName The name of the event to
     *                                            dispatch. The name of the
     *                                            event is the name of the
     *                                            method that is invoked on
     *                                            listeners.
     * @param EveApiReadWriteInterface $data
     *
     * @return EveApiEventInterface
     *
     * @api
     */
    public function dispatchEveApiEvent(
        $eventName,
        EveApiReadWriteInterface &$data
    );
    /**
     * @param string            $eventName
     * @param LogEventInterface $event
     *
     * @return EventInterface|LogEventInterface
     */
    public function dispatchLogEvent($eventName, LogEventInterface $event);
}
