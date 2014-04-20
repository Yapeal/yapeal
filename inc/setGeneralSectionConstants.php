<?php
/**
 * Contains setGeneralSectionConstants function.
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
/**
 * Function used to set constants from general area (not in a section) of the
 * configuration file.
 *
 * @param array $section A list of settings for this section of configuration.
 */
function setGeneralSectionConstants(array $section)
{
    if (!defined('YAPEAL_APPLICATION_AGENT')) {
        $curl = curl_version();
        $user_agent = $section['application_agent'];
        $user_agent .= ' Yapeal/1.1.x-alpha';
        $user_agent .= ' (' . PHP_OS . ' ' . php_uname('m') . ')';
        $user_agent .= ' libcurl/' . $curl['version'];
        $user_agent = trim($user_agent);
        /**
         * Used as default user agent in network connections.
         */
        define('YAPEAL_APPLICATION_AGENT', $user_agent);
    };
    if (!defined('YAPEAL_REGISTERED_MODE')) {
        /**
         * Determines how utilRegisteredKey, utilRegisteredCharacter, and
         * utilRegisteredCorporation tables are used, it also allows some columns in
         * this tables to be optional depending on value.
         */
        define('YAPEAL_REGISTERED_MODE', $section['registered_mode']);
    };
}

