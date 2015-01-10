<?php
/**
 * Contains EventSubscriberInterface Interface.
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

use DomainException;

/**
 * Class EventDispatcher
 *
 * Listeners are registered on the manager and events are dispatched through the
 * manager.
 *
 * @api
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @api
     */
    public function addListener($eventName, array $callback, $priority = 0)
    {
        if (2 !== count($callback) || !is_object($callback[0])
            || !is_string($callback[1])
        ) {
            $mess = 'Expected an array("instance", "methodName") argument';
            throw new DomainException($mess);
        }
        $eventName = (string)$eventName;
        $priority = (int)$priority;
        // Use a hash so we can maintain a single list of unique listeners.
        $hash = md5(spl_object_hash($callback[0]) . $callback[1] . $priority);
        $refCount = 0;
        if (isset($this->listeners[$hash])) {
            $refCount = $this->listeners[$hash][3];
        }
        // Re-adding the same listener to the same event moves it to the
        // end of the priority queue for that event. This only matters if there
        // are multiple listeners for an event with the same priority.
        if (isset($this->listenerIds[$eventName])) {
            $key = array_search($hash, $this->listenerIds[$eventName]);
            if (false !== $key) {
                unset($this->listenerIds[$eventName][$key]);
                --$refCount;
            }
        }
        $this->listenerIds[$eventName][] = $hash;
        $this->listeners[$hash] = [
            $callback[0],
            $callback[1],
            $priority,
            ++$refCount
        ];
        return $this;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function addSubscriber(EventSubscriberInterface $class)
    {
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            // Single method using default priority.
            if (is_string($params)) {
                $this->addListener($eventName, [$class, $params]);
                continue;
            }
            // Single method with optional priority.
            if (is_string($params[0])) {
                if (isset($params[1])) {
                    $this->addListener(
                        $eventName,
                        [$class, $params[0]],
                        $params[1]
                    );
                } else {
                    $this->addListener($eventName, [$class, $params[0]]);
                }
                continue;
            }
            // Multiple methods with optional priorities.
            foreach ($params as $listener) {
                if (isset($listener[1])) {
                    $this->addListener(
                        $eventName,
                        [$class, $listener[0]],
                        $listener[1]
                    );
                } else {
                    $this->addListener($eventName, [$class, $listener[0]]);
                }
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function dispatch(
        $eventName,
        EventInterface $event = null,
        PriorityQueue $queue = null
    )
    {
        $eventName = (string)$eventName;
        if (null === $event) {
            $event = new Event();
        }
        if (!isset($this->listenerIds[$eventName])) {
            return $event;
        }
        if (null === $queue) {
            $queue = new PriorityQueue();
        }
        foreach ($this->listenerIds[$eventName] as $index => $hash) {
            $object = $this->listeners[$hash][0];
            $method = $this->listeners[$hash][1];
            $priority = $this->listeners[$hash][2];
            $queue->insert([$object, $method], [$priority, $index]);
        }
        foreach ($queue as $listener) {
            call_user_func($listener, $event, $eventName, $this);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
        return $event;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function getListeners($eventName = '', PriorityQueue $queue = null)
    {
        if (0 == count($this->listenerIds)) {
            return [];
        }
        if (null === $queue) {
            $queue = new PriorityQueue();
        }
        if (!empty($eventName)) {
            $eventNames = [(string)$eventName];
        } else {
            $eventNames = array_keys($this->listenerIds);
        }
        $events = [];
        sort($eventNames);
        foreach ($eventNames as $eventName) {
            if (!isset($this->listenerIds[$eventName])) {
                continue;
            }
            $mpq = clone $queue;
            foreach ($this->listenerIds[$eventName] as $index => $hash) {
                $object = $this->listeners[$hash][0];
                $method = $this->listeners[$hash][1];
                $priority = $this->listeners[$hash][2];
                $mpq->insert([$object, $method], [$priority, $index]);
            }
            $events = array_merge($events, iterator_to_array($mpq, false));
        }
        return $events;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function hasListeners($eventName = '')
    {
        return (bool)count($this->getListeners($eventName));
    }
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @api
     */
    public function removeListener(
        $eventName,
        array $callback,
        $priority = 0
    )
    {
        if (2 !== count($callback) || !is_object($callback[0])
            || !is_string($callback[1])
        ) {
            $mess = 'Expected an array("instance", "methodName") argument';
            throw new DomainException($mess);
        }
        $priority = (int)$priority;
        $hash = md5(spl_object_hash($callback[0]) . $callback[1] . $priority);
        if (!isset($this->listeners[$hash])) {
            return $this;
        }
        $eventName = (string)$eventName;
        $refCount = $this->listeners[$hash][3];
        $key = array_search($hash, $this->listenerIds[$eventName]);
        if (false !== $key) {
            unset($this->listenerIds[$eventName][$key]);
            --$refCount;
        }
        if (empty($this->listenerIds[$eventName])) {
            unset($this->listenerIds[$eventName]);
        } else {
            // Reindex listeners to keep them neater.
            $this->listenerIds[$eventName] = array_values(
                $this->listenerIds[$eventName]
            );
        }
        if ($refCount < 1) {
            unset($this->listeners[$hash]);
        } else {
            $this->listeners[$hash][3] = $refCount;
        }
        return $this;
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function removeSubscriber(EventSubscriberInterface $class)
    {
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            // Single method using default priority.
            if (is_string($params)) {
                $this->removeListener($eventName, [$class, $params]);
                continue;
            }
            // Single method with optional priority.
            if (is_string($params[0])) {
                if (isset($params[1])) {
                    $this->removeListener(
                        $eventName,
                        [$class, $params[0]],
                        $params[1]
                    );
                } else {
                    $this->removeListener($eventName, [$class, $params[0]]);
                }
                continue;
            }
            // Multiple methods with optional priorities.
            foreach ($params as $listener) {
                if (isset($listener[1])) {
                    $this->removeListener(
                        $eventName,
                        [$class, $listener[0]],
                        $listener[1]
                    );
                } else {
                    $this->removeListener($eventName, [$class, $listener[0]]);
                }
            }
        }
        return $this;
    }
    /**
     * @param string $eventName
     * @param array  $callback
     * @param int    $priority
     *
     * @return bool
     * @throws DomainException
     */
    protected function hasListener(
        $eventName,
        array $callback,
        $priority = 0
    )
    {
        if (2 !== count($callback) || !is_object($callback[0])
            || !is_string($callback[1])
        ) {
            $mess = 'Expected an array("instance", "methodName") argument';
            throw new DomainException($mess);
        }
        $priority = (int)$priority;
        $eventName = (string)$eventName;
        $hash = md5(spl_object_hash($callback[0]) . $callback[1] . $priority);
        if ('Yapeal.Log.log' != $eventName
            && in_array($hash, $this->listenerIds[$eventName])
        ) {
            print 'Have listener ' . PHP_EOL;
            return true;
        }
        print 'No listener' . PHP_EOL;
        return false;
    }
    /**
     * @type array $listenersIds A list of listeners hashes indexed by event
     * names.
     */
    protected $listenerIds = [];
    /**
     * @type array $listeners List of listeners indexed by unique hash.
     */
    protected $listeners = [];
    /**
     * @type array $sorted
     */
    protected $sorted = [];
}
