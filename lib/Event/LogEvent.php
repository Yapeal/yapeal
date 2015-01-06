<?php
/**
 * Contains LogEvent class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database. Copyright (C) 2015 Michael Cummings
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
 * Class LogEvent
 */
class LogEvent extends Event implements LogEventInterface
{
    /**
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     */
    public function __construct($level, $message, array $context = [])
    {
        $this->setLevel($level)
             ->setMessage($message)
             ->setContext($context);
    }
    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }
    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    /**
     * @param array $value
     *
     * @return self
     */
    public function setContext(array $value)
    {
        $this->context = $value;
        return $this;
    }
    /**
     * @param mixed $value
     *
     * @return self
     */
    public function setLevel($value)
    {
        $this->level = $value;
        return $this;
    }
    /**
     * @param string $value
     *
     * @return self
     */
    public function setMessage($value)
    {
        $this->message = $value;
        return $this;
    }
    /**
     * @type array $context
     */
    protected $context;
    /**
     * @type mixed $level
     */
    protected $level;
    /**
     * @type string $message
     */
    protected $message;
}
