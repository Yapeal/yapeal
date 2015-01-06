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

use DomainException;
use InvalidArgumentException;
use Yapeal\Container\ContainerInterface;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class ContainerAwareEventDispatcher
 *
 * Lazily loads listeners and subscribers from the dependency injection
 * container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Bernhard Schussek <bschussek@gmail.com>
 * @author Jordan Alliot <jordan.alliot@gmail.com>
 */
class ContainerAwareEventDispatcher extends EventDispatcher implements
    ContainerAwareEventDispatcherInterface
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
     * @inheritdoc
     */
    public function addListenerService(
        $eventName,
        array $callback,
        $priority = 0
    )
    {
        if (2 !== count($callback)) {
            $mess
                = 'For $callback expected an array("service", "method") argument';
            throw new InvalidArgumentException($mess);
        }
        $this->listenerIds[$eventName][] = [
            $callback[0],
            $callback[1],
            $priority
        ];
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function addSubscriberService($serviceId, $class)
    {
        $method = 'getSubscribedEvents';
        if (!$this->hasRequiredMethod($class, $method)) {
            $mess = sprintf(
                'Class %1$s MUST have public static function %2$s()',
                $class,
                $method
            );
            throw new DomainException($mess);
        }
        foreach ($class::$method() as $eventName => $params) {
            if (is_string($params)) {
                $this->addListenerService($eventName, [$serviceId, $params], 0);
            } elseif (is_string($params[0])) {
                $this->addListenerService(
                    $eventName,
                    [$serviceId, $params[0]],
                    isset($params[1]) ? $params[1] : 0
                );
            } else {
                foreach ($params as $listener) {
                    $this->addListenerService(
                        $eventName,
                        [$serviceId, $listener[0]],
                        isset($listener[1])
                            ? $listener[1] : 0
                    );
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
    public function dispatch($eventName, EventInterface $event = null)
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
        $this->lazyLoad($eventName);
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
    public function dispatchLogEvent($eventName, LogEventInterface $event)
    {
        $this->lazyLoad($eventName);
        return $this->dispatch($eventName, $event);
    }
    /**
     * @inheritdoc
     *
     * @api
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
     * @inheritdoc
     *
     * @api
     */
    public function hasListeners($eventName = null)
    {
        if (null === $eventName) {
            return (bool)count($this->listenerIds)
                   || (bool)count($this->listeners);
        }
        if (isset($this->listenerIds[$eventName])) {
            return true;
        }
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
     * @return EveApiEventInterface
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
     *
     * @throws InvalidArgumentException
     */
    protected function lazyLoad($eventName)
    {
        if (isset($this->listenerIds[$eventName])) {
            foreach ($this->listenerIds[$eventName] as $args) {
                list($serviceId, $method, $priority) = $args;
                $listener = $this->container[$serviceId];
                $this->addListener(
                    $eventName,
                    [$listener, $method],
                    $priority
                );
            }
        }
    }
    /**
     * @type ContainerInterface $container The container from where services
     *       are loaded
     */
    protected $container;
    /**
     * @type EveApiEventInterface $eveApiEvent
     */
    protected $eveApiEvent;
    /**
     * @type array $listenerIds The service IDs of the event listeners and
     *       subscribers
     */
    protected $listenerIds = [];
}
