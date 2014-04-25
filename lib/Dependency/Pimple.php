<?php
/**
 * This file was part of Pimple.
 *
 * PHP 5.4
 *
 * I (Michael Cummings) decided to make some mostly cosmetic changes to the code
 * plus ran into some dependence issues in Composer with some other stuff I was
 * using so I decided it was just as easy to make Pimple part of Yapeal.
 *
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (c) 2009 Fabien Potencier
 * Copyright (C) 2014 Michael Cummings
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @copyright 2009,2014 Fabien Potencier, Michael Cummings
 * @author    Fabien Potencier <fabien@symfony.com>
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @link      http://dependence.sensiolabs.org/ Pimple
 */
namespace Yapeal\Dependency;

/**
 * Pimple main class.
 *
 * @author Fabien Potencier
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @since  2.0.0-alpha5
 */
class Pimple implements DependenceInterface
{
    /**
     * Instantiate the container.
     *
     * Objects and parameters can be passed as argument to the constructor.
     *
     * @param array $values The parameters or objects.
     *
     * @throws \RuntimeException
     */
    public function __construct(array $values = array())
    {
        $this->factories = new \SplObjectStorage();
        $this->protected = new \SplObjectStorage();
        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }
    /**
     * Extends an object definition.
     *
     * Useful when you want to extend an existing object definition,
     * without necessarily loading that object.
     *
     * @param string   $id       The unique identifier for the object
     * @param callable $callable A service definition to extend the original
     *
     * @return callable The wrapped callable
     *
     * @throws \InvalidArgumentException if the identifier is not defined or not a service definition
     */
    public function extend($id, $callable)
    {
        if (!isset($this->keys[$id])) {
            throw new \InvalidArgumentException(
                sprintf('Identifier "%s" is not defined.', $id)
            );
        }
        if (!is_object($this->values[$id])
            || !method_exists(
                $this->values[$id],
                '__invoke'
            )
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Identifier "%s" does not contain an object definition.',
                    $id
                )
            );
        }
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException(
                'Extension service definition is not a Closure or invokable object.'
            );
        }
        $factory = $this->values[$id];
        /** @var object|callable $extended */
        $extended = function ($c) use ($callable, $factory) {
            return $callable($factory($c), $c);
        };
        if (isset($this->factories[$factory])) {
            $this->factories->detach($factory);
            $this->factories->attach($extended);
        }
        return $this[$id] = $extended;
    }
    /**
     * Marks a callable as being a factory service.
     *
     * @param callable|object $callable A service definition to be used as a factory
     *
     * @return callable The passed callable
     *
     * @throws \InvalidArgumentException Service definition has to be a closure of an invokable object
     */
    public function factory($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException(
                'Service definition is not a Closure or invokable object.'
            );
        }
        $this->factories->attach($callable);
        return $callable;
    }
    /**
     * Returns all defined value names.
     *
     * @return array An array of value names
     */
    public function keys()
    {
        return array_keys($this->values);
    }
    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return Boolean
     */
    public function offsetExists($id)
    {
        return isset($this->keys[$id]);
    }
    /**
     * Gets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or an object
     *
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    public function offsetGet($id)
    {
        if (!isset($this->keys[$id])) {
            throw new \InvalidArgumentException(
                sprintf('Identifier "%s" is not defined.', $id)
            );
        }
        if (
            isset($this->raw[$id])
            || !is_object($this->values[$id])
            || isset($this->protected[$this->values[$id]])
            || !method_exists($this->values[$id], '__invoke')
        ) {
            return $this->values[$id];
        }
        if (isset($this->factories[$this->values[$id]])) {
            return $this->values[$id]($this);
        }
        $this->frozen[$id] = true;
        $this->raw[$id] = $this->values[$id];
        return $this->values[$id] = $this->values[$id]($this);
    }
    /**
     * Sets a parameter or an object.
     *
     * Objects must be defined as Closures.
     *
     * Allowing any PHP callable leads to difficult to debug problems
     * as function names (strings) are callable (creating a function with
     * the same name as an existing parameter would break your container).
     *
     * @param  string $id    The unique identifier for the parameter or object
     * @param  mixed  $value The value of the parameter or a closure to define an object
     *
     * @throws \RuntimeException Prevent override of a frozen service
     */
    public function offsetSet($id, $value)
    {
        if (isset($this->frozen[$id])) {
            throw new \RuntimeException(
                sprintf('Cannot override frozen service "%s".', $id)
            );
        }
        $this->values[$id] = $value;
        $this->keys[$id] = true;
    }
    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        if (isset($this->keys[$id])) {
            if (is_object($this->values[$id])) {
                unset($this->factories[$this->values[$id]], $this->protected[$this->values[$id]]);
            }
            unset($this->values[$id], $this->frozen[$id], $this->raw[$id], $this->keys[$id]);
        }
    }
    /**
     * Protects a callable from being interpreted as a service.
     *
     * This is useful when you want to store a callable as a parameter.
     *
     * @param callable|object $callable A callable to protect from being evaluated
     *
     * @return callable The passed callable
     *
     * @throws \InvalidArgumentException Service definition has to be a closure of an invokable object
     */
    public function protect($callable)
    {
        if (!is_object($callable) || !method_exists($callable, '__invoke')) {
            throw new \InvalidArgumentException(
                'Callable is not a Closure or invokable object.'
            );
        }
        $this->protected->attach($callable);
        return $callable;
    }
    /**
     * Gets a parameter or the closure defining an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or the closure defining an object
     *
     * @throws \InvalidArgumentException if the identifier is not defined
     */
    public function raw($id)
    {
        if (!isset($this->keys[$id])) {
            throw new \InvalidArgumentException(
                sprintf('Identifier "%s" is not defined.', $id)
            );
        }
        if (isset($this->raw[$id])) {
            return $this->raw[$id];
        }
        return $this->values[$id];
    }
    private $factories;
    private $frozen = array();
    private $keys = array();
    private $protected;
    private $raw = array();
    private $values = array();
}
