<?php
/**
 * Contains EventDispatcherInterface Interface.
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
namespace Yapeal\Event;

/**
 * Interface EventDispatcherInterface
 * The EventDispatcherInterface is the central point of Yapeal's event
 * listener system. Listeners are registered on the manager and events are
 * dispatched through the manager.
 *
 * @api
 */
interface EventDispatcherInterface
{
    /**
     * Adds an event listener that listens on the specified events.
     *
     * Each combination of $eventName, $listener, and $priority is unique.
     * Re-adding the same listener will cause it to leave the $eventName,
     * $priority queue and be added to the end of that queue again.
     * Example:
     *     $listeners[$eventName][$priority] = ['listener1', 'listener2'];
     * Re-add 'listener1' to the event with the same priority and it becomes:
     *     $listeners[$eventName][$priority] = ['listener2', 'listener1'];
     *
     * @param string $eventName   The event to listen for
     * @param array $callback     The listener to be added. Needs to be
     *                            array('instance','method')
     * @param int    $priority    The higher this value, the earlier an event
     *                            listener will be triggered in the chain
     *                            (defaults to 0)
     *
     * @return self
     * @api
     */
    public function addListener($eventName, array $callback, $priority = 0);
    /**
     * Adds an event subscriber.
     *
     * The subscriber is asked for all the events it is
     * interested in and is added as a listener for these events.
     *
     * @param EventSubscriberInterface $class The subscriber class instance.
     *
     * @return self
     * @api
     */
    public function addSubscriber(EventSubscriberInterface $class);
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string         $eventName The name of the event to dispatch. The
     *                                  name of the event is the name of the
     *                                  method that is invoked on listeners.
     * @param EventInterface $event     The event to pass to the event
     *                                  handlers/listeners. If not supplied, an
     *                                  empty Event instance is created.
     *
     * @return EventInterface
     *
     * @api
     */
    public function dispatch($eventName, EventInterface $event = null);
    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return array The event listeners for the specified event, or all event
     *               listeners by event name
     */
    public function getListeners($eventName = '');
    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return bool true if the specified event has any listeners, false
     *              otherwise
     */
    public function hasListeners($eventName = '');
    /**
     * Removes an event listener from the specified events.
     *
     * @param string   $eventName The event to remove a listener from
     * @param array    $callback  The listener to be removed. Needs to have
     *                            either [object, 'methodName'] or
     *                            ['className', 'methodName']
     * @param int|null $priority  Priority level of listener to be removed.
     *                            If it is the default value null then listener
     *                            will be removed for all found priorities.
     *
     * @return self
     * @api
     */
    public function removeListener(
        $eventName,
        array $callback,
        $priority = null
    );
    /**
     * Removes an event subscriber.
     *
     * @param EventSubscriberInterface $class The subscriber class instance.
     *
     * @return self
     * @api
     */
    public function removeSubscriber(EventSubscriberInterface $class);
}
