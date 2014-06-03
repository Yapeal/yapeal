<?php
/**
 * Contains GetBy interface.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal;

/**
 * Interface to get record from database table by Id or when available other
 * methods. */
interface IGetBy
{
    /**
     * Used to get item from table by Id.
     *
     * For database tables that don't have an 'id' type field that can be use
     * they should throw a LogicException. To put it in other words this method is
     * optional for some tables because it doesn't make logical sense to implement
     * it.
     *
     * @param mixed $id Id of record wanted.
     *
     * @return bool Returns TRUE if item was retrieved.
     *
     * @throws \LogicException Should throw a LogicException if there is no 'id'
     * type field that can be use in retrieving the database table.
     */
    public function getItemById($id);
    /**
     * Used to get item from table by name.
     *
     * For database tables that don't have a 'name' type field that can be use
     * they should throw a LogicException. To put it in other words this method is
     * optional for some tables because it doesn't make logical sense to implement
     * it.
     *
     * @param mixed $name Name of record wanted.
     *
     * @return bool TRUE if item was retrieved else FALSE.
     *
     * @throws \LogicException Should throw a LogicException if there is no 'name'
     * type field that can be use in retrieving the database table.
     */
    public function getItemByName($name);
}

