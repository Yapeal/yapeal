<?php
/**
 * Contains ContainerAwareEventDispatcher class.
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
 */
namespace Yapeal\Event;

use DomainException;
use InvalidArgumentException;
use Yapeal\Container\ContainerInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class ContainerAwareEventDispatcher
 *
 * Lazily loads listeners and subscribers from the dependency injection
 * container.
 */
class ContainerAwareEventDispatcher extends EventDispatcher implements
    ContainerAwareEventDispatcherInterface
{
    /**
     * @param ContainerInterface $dic A ContainerInterface instance
     * @param EveApiEventInterface $eae
     * @param LogEventInterface    $lei
     */
    public function __construct(
        ContainerInterface $dic,
        EveApiEventInterface $eae = null,
        LogEventInterface $lei
    )
    {
        $this->container = $dic;
        $this->setEveApiEvent($eae)
             ->setLogEvent($lei);
    }
    /**
     * @inheritdoc
     *
     * @throws DomainException
     * @api
     */
    public function addListenerService(
        $eventName,
        array $callback,
        $priority = 0
    )
    {
        if (2 !== count($callback) || !is_string($callback[0])
            || !is_string($callback[1])
        ) {
            $mess = 'Expected an array("serviceName", "methodName") argument';
            throw new DomainException($mess);
        }
        $eventName = (string)$eventName;
        $priority = (int)$priority;
        // Use a hash so we can maintain a single list of unique service listeners.
        $hash = md5($callback[0] . $callback[1] . $priority);
        $refCount = 0;
        if (isset($this->serviceListeners[$hash])) {
            $refCount = $this->serviceListeners[$hash][3];
        }
        // Re-adding the same service listener to the same event moves it to the
        // end of the priority queue for that event. This only matters if there
        // are multiple listeners for an event with the same priority.
        if (isset($this->serviceIds[$eventName])) {
            $key = array_search($hash, $this->serviceIds[$eventName]);
            if (false !== $key) {
                unset($this->serviceIds[$eventName][$key]);
                --$refCount;
            }
        }
        $this->serviceIds[$eventName][] = $hash;
        $this->serviceListeners[$hash] = [
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
     * @throws DomainException
     * @api
     */
    public function addSubscriberService($serviceId, $class)
    {
        if (!method_exists($class, 'getSubscribedEvents')) {
            $mess = 'Class MUST has a public static getSubscribedEvents()';
            throw new DomainException($mess);
        }
        foreach ($class::getSubscribedEvents() as $eventName => $params) {
            // Single method using default priority.
            if (is_string($params)) {
                $this->addListenerService($eventName, [$serviceId, $params]);
                continue;
            }
            // Single method with optional priority.
            if (is_string($params[0])) {
                if (isset($params[1])) {
                    $this->addListenerService(
                        $eventName,
                        [$serviceId, $params[0]],
                        $params[1]
                    );
                } else {
                    $this->addListenerService(
                        $eventName,
                        [$serviceId, $params[0]]
                    );
                }
                continue;
            }
            // Multiple methods with optional priorities.
            foreach ($params as $listener) {
                if (isset($listener[1])) {
                    $this->addListenerService(
                        $eventName,
                        [$serviceId, $listener[0]],
                        $listener[1]
                    );
                } else {
                    $this->addListenerService(
                        $eventName,
                        [$serviceId, $listener[0]]
                    );
                }
                $this->addListenerService(
                    $eventName,
                    [$serviceId, $listener[0]],
                    (null !== $listener[1])
                        ? (int)$listener[1] : 0
                );
            }
        }
        return $this;
    }
    /**
     * @inheritdoc
     *
     * @param PriorityQueue $queue
     *
     * @api
     */
    public function dispatch(
        $eventName,
        EventInterface $event = null,
        PriorityQueue $queue = null
    )
    {
        $this->lazyLoad($eventName);
        return parent::dispatch($eventName, $event);
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function dispatchEveApiEvent(
        $eventName,
        EveApiReadWriteInterface &$data
    )
    {
        return $this->dispatch(
            $eventName,
            $this->getEveApiEvent()
                 ->setData($data)
        );
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function dispatchLogEvent(
        $eventName,
        $level,
        $message,
        array $context = []
    )
    {
        return $this->dispatch(
            $eventName,
            $this->getLogEvent()
                 ->setLevel($level)
                 ->setMessage($message)
                 ->setContext($context)
        );
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function getListeners($eventName = '')
    {
        $this->lazyLoad($eventName);
        return parent::getListeners($eventName);
    }
    /**
     * @inheritdoc
     *
     * @api
     */
    public function hasListeners($eventName = '')
    {
        $this->lazyLoad($eventName);
        return parent::hasListeners($eventName);
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
     * @param LogEventInterface $value
     *
     * @return self
     */
    public function setLogEvent(LogEventInterface $value)
    {
        $this->logEvent = $value;
        return $this;
    }
    /**
     * @return EveApiEventInterface
     */
    protected function getEveApiEvent()
    {
        return $this->eveApiEvent;
    }
    /**
     * @return LogEventInterface
     */
    protected function getLogEvent()
    {
        return $this->logEvent;
    }
    /**
     * @param string|null $eventName
     *
     * @return self
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    protected function lazyLoad($eventName = null)
    {
        if (!empty($eventName)) {
            $eventNames = [(string)$eventName];
        } else {
            $eventNames = array_keys($this->serviceIds);
        }
        sort($eventNames);
        foreach ($eventNames as $eventName) {
            if (!isset($this->serviceIds[$eventName])) {
                continue;
            }
            foreach ($this->serviceIds[$eventName] as $hash) {
                $listenerName = $this->serviceListeners[$hash][0];
                $method = $this->serviceListeners[$hash][1];
                $priority = $this->serviceListeners[$hash][2];
                if (!isset($this->container[$listenerName])) {
                    $mess = 'Unknown service ' . $listenerName;
                    throw new InvalidArgumentException($mess);
                }
                $instance = $this->container[$listenerName];
                $this->addListener($eventName, [$instance, $method], $priority);
            }
        }
        return $this;
    }
    /**
     * @type ContainerInterface $container The container from where services
     * are loaded.
     */
    protected $container;
    /**
     * @type EveApiEventInterface $eveApiEvent
     */
    protected $eveApiEvent;
    /**
     * @type LogEventInterface $logEvent
     */
    protected $logEvent;
    /**
     * @type array $serviceIds A list of service hashes indexed by event names.
     */
    protected $serviceIds = [];
    /**
     * @type array $serviceListeners List of service listeners indexed by unique
     * hash.
     */
    protected $serviceListeners = [];
}
