<?php
/**
 * Contains EveApiEvent class.
 *
 * PHP version 5.5
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

use EventMediator\Event;
use LogicException;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Class EveApiEvent
 */
class EveApiEvent extends Event implements EveApiEventInterface
{
    /**
     * @inheritdoc
     */
    public function getData()
    {
        if (!$this->data instanceof EveApiReadWriteInterface) {
            $mess = 'Tried to use data before it was set';
            throw new LogicException($mess);
        }
        return $this->data;
    }
    /**
     * @inheritdoc
     */
    public function setData(EveApiReadWriteInterface $value)
    {
        $this->data = $value;
        return $this;
    }
    /**
     * Set to indicate event was handled sufficiently while still allows additional listener(s) to have a chance to
     * handle the event as well.
     *
     * @return self Fluent interface.
     */
    public function setHandledSufficiently()
    {
        $this->handledSufficiently = true;
        return $this;
    }
    /**
     * Used to check if event was handled sufficiently by any listener(s).
     *
     * This should return true when a listener uses setHandledSufficiently() and/or eventHandled() methods for the
     * event.
     *
     * @return bool
     */
    public function isSufficientlyHandled()
    {
        return ($this->handledSufficiently || $this->handled);
    }
    /**
     * Holds the data instance.
     *
     * @type EveApiReadWriteInterface $data
     */
    protected $data;
    /**
     * Holds the handled sufficiently state.
     *
     * @type bool $handledSufficiently
     */
    protected $handledSufficiently = false;
}
