<?php
/**
 * Contains EveApiCacheInterface interface.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 * Copyright (C) 2013-2014  Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @copyright 2013-2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link      http://code.google.com/p/yapeal/
 * @link      http://www.eveonline.com/
 */
namespace Yapeal\Caching;

/**
 * EveApiCacheInterface interface.
 *
 * @package Yapeal\Caching
 */
interface EveApiCacheInterface
{
    /**
     * @param string $xml Eve Api XML to be cached.
     *
     * @return self Returns self to allow fluid interface.
     */
    public function cacheXml($xml);
    /**
     * @return bool Returns TRUE if the cached copy of XML was deleted else FALSE.
     */
    public function deleteCachedXml();
    /**
     * @return string|false Returns XML if data is available, else returns FALSE.
     */
    public function getCachedXml();
}
