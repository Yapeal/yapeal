<?php
/**
 * Contains PriorityQueue class.
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

use DomainException;
use SplPriorityQueue;

/**
 * Class PriorityQueue
 */
class PriorityQueue extends SplPriorityQueue
{
    /**
     *
     */
    public function __construct()
    {
        $this->setExtractFlags(self::EXTR_DATA);
    }
    /**
     *
     */
    public function __clone()
    {
        $this->setExtractFlags(self::EXTR_DATA);
    }
    /**
     * @param mixed $priority1
     * @param mixed $priority2
     *
     * @return int
     * @throws DomainException
     */
    public function compare($priority1, $priority2)
    {
        if (2 != count($priority1) || 2 != count($priority2)) {
            $mess = 'Expect priorities to be array("priority", "index")';
            throw new DomainException($mess);
        }
        if ($priority1[0] == $priority2[0]) {
            if ($priority1[1] == $priority2[1]) {
                return 0;
            }
            return ($priority2[1] > $priority1[1]) ? 1 : -1;
        }
        return ($priority1[0] > $priority2[0]) ? 1 : -1;
    }
}
