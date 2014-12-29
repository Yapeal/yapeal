<?php
/**
 * Contains EveApiEventInterface Interface.
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

use LogicException;
use Yapeal\Xml\EveApiReadWriteInterface;

/**
 * Interface EveApiEventInterface
 */
interface EveApiEventInterface extends EventInterface
{
    const DONE = 'eve_api.done';
    const POST_PRESERVE = 'eve_api.post_preserve';
    const PRE_PRESERVE = 'eve_api.pre_preserve';
    const PRE_RETRIEVE = 'eve_api.pre_retrieve';
    const PRE_TRANSFORM = 'eve_api.pre_transform';
    const PRE_VALIDATE = 'eve_api.pre_validate';
    const PRE_XML_ERROR = 'eve_api.pre_xml_error';
    const START = 'eve_api.start';
    /**
     * @return EveApiReadWriteInterface
     * @throws LogicException
     */
    public function getData();
    /**
     * @param EveApiReadWriteInterface $value
     *
     * @return self
     */
    public function setData(EveApiReadWriteInterface &$value);
}
