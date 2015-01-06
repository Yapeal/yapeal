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
use ReflectionClass;
use ReflectionMethod;

/**
 * Class EventDispatcher
 *
 * Listeners are registered on the manager and events are dispatched through the
 * manager.
 *
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Bernhard Schussek <bschussek@gmail.com>
 * @author  Fabien Potencier <fabien@symfony.com>
 * @author  Jordi Boggiano <j.boggiano@seld.be>
 * @author  Jordan Alliot <jordan.alliot@gmail.com>
 *
 * @api
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * @inheritDoc
     *
     * @api
     */
    public function addListener($eventName, array $callback, $priority = 0)
    {
        if (2 !== count($callback)) {
            $mess = 'Expected an array("class", "method") argument';
            throw new InvalidArgumentException($mess);
        }
        $this->removeListener($eventName, $callback, $priority);
        $this->listeners[$eventName][$priority][] = $callback;
        unset($this->sorted[$eventName]);
        return $this;
    }
    /**
     * @inheritDoc
     *
     * @api
     */
    public function addSubscriber($class)
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
                $this->addListener($eventName, [$class, $params]);
            } elseif (is_string($params[0])) {
                $this->addListener(
                    $eventName,
                    [$class, $params[0]],
                    isset($params[1]) ? $params[1] : 0
                );
            } else {
                foreach ($params as $listener) {
                    $this->addListener(
                        $eventName,
                        [$class, $listener[0]],
                        isset($listener[1]) ? $listener[1] : 0
                    );
                }
            }
        }
        return $this;
    }
    /**
     * @inheritDoc
     *
     * @api
     */
    public function dispatch($eventName, EventInterface $event = null)
    {
        if (null === $event) {
            $event = new Event();
        }
        if (!isset($this->listeners[$eventName])) {
            return $event;
        }
        $this->doDispatch($this->getListeners($eventName), $eventName, $event);
        return $event;
    }
    /**
     * @inheritDoc
     *
     * @api
     */
    public function getListeners($eventName = null)
    {
        if (null !== $eventName) {
            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }
            return $this->sorted[$eventName];
        }
        foreach (array_keys($this->listeners) as $eventName) {
            if (!isset($this->sorted[$eventName])) {
                $this->sortListeners($eventName);
            }
        }
        return array_filter($this->sorted);
    }
    /**
     * @inheritDoc
     *
     * @api
     */
    public function hasListeners($eventName = null)
    {
        return (bool)count($this->getListeners($eventName));
    }
    /**
     * @inheritDoc
     *
     * @api
     */
    public function removeListener(
        $eventName,
        array $callback,
        $priority = null
    )
    {
        if (!isset($this->listeners[$eventName])) {
            return $this;
        }
        if (2 !== count($callback)) {
            $mess = 'Expected an array("class", "method") argument';
            throw new InvalidArgumentException($mess);
        }
        if (null === $priority) {
            $priority = array_keys($this->listeners[$eventName]);
        }
        if (!is_array($priority)) {
            $priority = (array)$priority;
        }
        $this->doRemoveListener($eventName, $callback, $priority);
        return $this;
    }
    /**
     * @inheritDoc
     *
     * @api
     */
    public function removeSubscriber($class)
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
            if (is_array($params) && is_array($params[0])) {
                foreach ($params as $listener) {
                    $this->removeListener(
                        $eventName,
                        [$class, $listener[0]]
                    );
                }
            } else {
                $this->removeListener(
                    $eventName,
                    [$class, is_string($params) ? $params : $params[0]]
                );
            }
        }
    }
    /**
     * Triggers the listeners of an event.
     *
     * This method can be overridden to add functionality that is executed
     * for each listener.
     *
     * @param callable[] $listeners The event listeners.
     * @param string     $eventName The name of the event to dispatch.
     * @param Event      $event     The event object to pass to the event
     *                              handlers/listeners.
     */
    protected function doDispatch($listeners, $eventName, Event $event)
    {
        foreach ($listeners as $listener) {
            call_user_func($listener, $event, $eventName, $this);
            if ($event->isPropagationStopped()) {
                break;
            }
        }
    }
    /**
     * @param string $eventName
     * @param array  $callback
     * @param array  $priority
     */
    protected function doRemoveListener(
        $eventName,
        array $callback,
        array $priority
    )
    {
        if (empty($priority)) {
            return;
        }
        foreach ($priority as $pri) {
            if (!isset($this->listeners[$eventName][$pri])) {
                continue;
            }
            foreach ($this->listeners[$eventName][$pri] as $key =>
                     $listener) {
                // Match class and method.
                if ($listener[0] === $callback[0]
                    && $listener[1] == $callback[1]
                ) {
                    unset($this->listeners[$eventName][$pri][$key],
                        $this->sorted[$eventName]);
                    if (empty($this->listeners[$eventName][$pri])) {
                        unset($this->listeners[$eventName][$pri]);
                    }
                    if (empty($this->listeners[$eventName])) {
                        unset($this->listeners[$eventName]);
                    }
                }
            }
        };
    }
    /**
     * @param string|object $class
     * @param string        $method
     *
     * @return bool
     * @throws DomainException
     * @throws InvalidArgumentException
     */
    protected function hasRequiredMethod($class, $method)
    {
        if (!is_string($method)) {
            $mess
                = 'Method name MUST be a string, but given ' . gettype($class);
            throw new InvalidArgumentException($mess);
        }
        if (empty($method)) {
            $mess = 'Method name can NOT be empty';
            throw new DomainException($mess);
        }
        if (!(is_string($class) || is_object($class))) {
            $mess = 'Class MUST be a string or instance, but given ' . gettype(
                    $class
                );
            throw new InvalidArgumentException($mess);
        }
        if (!(new ReflectionClass($class))->hasMethod($method)) {
            return false;
        }
        $rfl = new ReflectionMethod($class, $method);
        if (!($rfl->isStatic() && $rfl->isPublic())) {
            return false;
        }
        return true;
    }
    /**
     * Sorts the internal list of listeners for the given event by priority.
     *
     * @param string $eventName The name of the event.
     */
    protected function sortListeners($eventName)
    {
        $this->sorted[$eventName] = [];
        if (isset($this->listeners[$eventName])) {
            krsort($this->listeners[$eventName]);
            $this->sorted[$eventName] = call_user_func_array(
                'array_merge',
                $this->listeners[$eventName]
            );
        }
    }
    /**
     * @type array $listeners
     */
    protected $listeners = [];
    /**
     * @type array $sorted
     */
    protected $sorted = [];
}
