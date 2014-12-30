<?php
/**
 * Contains EventDispatcher class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014 Michael Cummings
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
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Event;

use InvalidArgumentException;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Yapeal\Container\ContainerInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class EventDispatcher
 */
class EventDispatcher extends SymfonyEventDispatcher implements
    EventDispatcherInterface
{
    /**
     * @param ContainerInterface $dic A ContainerInterface instance
     * @param EveApiEventInterface $eae
     */
    public function __construct(
        ContainerInterface $dic,
        EveApiEventInterface $eae = null
    )
    {
        $this->container = $dic;
        $this->setEveApiEvent($eae);
    }
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
     * @throws InvalidArgumentException
     */
    public function addListenerService($eventName, $callback, $priority = 0)
    {
        if (!is_array($callback) || 2 !== count($callback)) {
            $mess = 'Expected an array("service", "method") argument';
            throw new InvalidArgumentException($mess);
        }
        $this->listenerIds[$eventName][] = [
            $callback[0],
            $callback[1],
            $priority
        ];
    }
    /**
     * Adds a service as event subscriber
     *
     * @param string $serviceId The service ID of the subscriber service
     * @param string $class     The service's class name (which must implement
     *                          EventSubscriberInterface)
     */
    public function addSubscriberService($serviceId, $class)
    {
        /**
         * @type EventSubscriberInterface $class
         */
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            if (is_string($params)) {
                $this->listenerIds[$eventName][] = [$serviceId, $params, 0];
            } elseif (is_string($params[0])) {
                $this->listenerIds[$eventName][] = [
                    $serviceId,
                    $params[0],
                    isset($params[1])
                        ? $params[1] : 0
                ];
            } else {
                foreach ($params as $listener) {
                    $this->listenerIds[$eventName][] = [
                        $serviceId,
                        $listener[0],
                        isset($listener[1])
                            ? $listener[1] : 0
                    ];
                }
            }
        }
    }
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     * @param Event  $event     The event to pass to the event
     *                          handlers/listeners. If not supplied, an empty
     *                          Event instance is created.
     *
     * @return Event
     *
     * @api
     */
    public function dispatch($eventName, Event $event = null)
    {
        $this->lazyLoad($eventName);
        return parent::dispatch($eventName, $event);
    }
    /**
     * Dispatches an event to all registered listeners.
     *
     * @param string $eventName                    The name of the event to
     *                                             dispatch. The name of the
     *                                             event is the name of the
     *                                             method that is invoked on
     *                                             listeners.
     * @param EveApiReadWriteInterface $data
     *
     * @return EveApiEventInterface
     *
     * @api
     */
    public function dispatchEveApiEvent(
        $eventName,
        EveApiReadWriteInterface &$data
    )
    {
        $this->lazyLoad($eventName);
        return parent::dispatch(
            $eventName,
            $this->getEveApiEvent()
                 ->setData($data)
        );
    }
    /**
     * Gets the listeners of a specific event or all listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return array The event listeners for the specified event, or all event
     *               listeners by event name
     */
    public function getListeners($eventName = null)
    {
        if (null === $eventName) {
            foreach (array_keys($this->listenerIds) as $serviceEventName) {
                $this->lazyLoad($serviceEventName);
            }
        } else {
            $this->lazyLoad($eventName);
        }
        return parent::getListeners($eventName);
    }
    /**
     * Checks whether an event has any registered listeners.
     *
     * @param string $eventName The name of the event
     *
     * @return bool true if the specified event has any listeners, false
     *              otherwise
     */
    public function hasListeners($eventName = null)
    {
        if (null === $eventName) {
            return (bool)count($this->listenerIds)
                   || (bool)count(
                $this->listeners
            );
        }
        if (isset($this->listenerIds[$eventName])) {
            return true;
        }
        return parent::hasListeners($eventName);
    }
    /**
     * @param string   $eventName
     * @param callable $listener
     */
    public function removeListener($eventName, $listener)
    {
        $this->lazyLoad($eventName);
        if (isset($this->listenerIds[$eventName])) {
            foreach ($this->listenerIds[$eventName] as $i => $args) {
                // ServiceID and method.
                $key = $args[0] . '.' . $args[1];
                if (isset($this->listeners[$eventName][$key])
                    &&
                    $listener === [$this->listeners[$eventName][$key], $args[1]]
                ) {
                    unset($this->listeners[$eventName][$key]);
                    if (empty($this->listeners[$eventName])) {
                        unset($this->listeners[$eventName]);
                    }
                    unset($this->listenerIds[$eventName][$i]);
                    if (empty($this->listenerIds[$eventName])) {
                        unset($this->listenerIds[$eventName]);
                    }
                }
            }
        }
        parent::removeListener($eventName, $listener);
    }
    /**
     * @param EveApiEventInterface|null $value
     *
     * @return self
     */
    public function setEveApiEvent(EveApiEventInterface $value = null)
    {
        if (null === $value) {
            $value = new EveApiEvent();
        }
        $this->eveApiEvent = $value;
        return $this;
    }
    /**
     * @return Event|EveApiEventInterface
     */
    protected function getEveApiEvent()
    {
        return $this->eveApiEvent;
    }
    /**
     * Lazily loads listeners for this event from the dependency injection
     * container.
     *
     * @param string $eventName The name of the event to dispatch. The name of
     *                          the event is the name of the method that is
     *                          invoked on listeners.
     */
    protected function lazyLoad($eventName)
    {
        if (isset($this->listenerIds[$eventName])) {
            foreach ($this->listenerIds[$eventName] as $args) {
                list($serviceId, $method, $priority) = $args;
                $listener = $this->container[$serviceId];
                $key = $serviceId . '.' . $method;
                if (!isset($this->listeners[$eventName][$key])) {
                    $this->addListener(
                        $eventName,
                        [$listener, $method],
                        $priority
                    );
                } elseif ($listener !== $this->listeners[$eventName][$key]) {
                    parent::removeListener(
                        $eventName,
                        [$this->listeners[$eventName][$key], $method]
                    );
                    $this->addListener(
                        $eventName,
                        [$listener, $method],
                        $priority
                    );
                }
                $this->listeners[$eventName][$key] = $listener;
            }
        }
    }
    /**
     * The container from where services are loaded
     * @type ContainerInterface
     */
    protected $container;
    /**
     * @type Event|EveApiEventInterface $eveApiEvent
     */
    protected $eveApiEvent;
    /**
     * The service IDs of the event listeners and subscribers
     * @type array $listenerIds
     */
    protected $listenerIds = [];
    /**
     * @type callable[] $listeners
     */
    protected $listeners = [];
    /**
     * @type callable[] $sorted
     */
    protected $sorted = [];
}
