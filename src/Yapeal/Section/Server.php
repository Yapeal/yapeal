<?php
/**
 * Contains Section Server class.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2013, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Section;

use Logger;
use Yapeal\Database\DatabaseConnection;
use Yapeal\Util\CachedUntil;

/**
 * Class used to pull Eve APIs for server section.
 *
 * @package Yapeal\Section
 */
class Server extends ASection
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->section = strtolower(basename(__CLASS__));
        parent::__construct();
    }
    /**
     * Function called by Yapeal.php to start section pulling XML from servers.
     *
     * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
     */
    public function pullXML()
    {
        if ($this->abort === true) {
            return false;
        };
        $apiCount = 0;
        $apiSuccess = 0;
        $apis = $this->am->maskToAPIs($this->mask, $this->section);
        if (count($apis) == 0) {
            return false;
        };
        // Randomize order in which APIs are tried if there is a list.
        if (count($apis) > 1) {
            shuffle($apis);
        };
        try {
            foreach ($apis as $api) {
                // If the cache for this API has expire try to get update.
                if (CachedUntil::cacheExpired($api) === true) {
                    ++$apiCount;
                    $class = $this->section . $api;
                    $hash = hash('sha1', $class);
                    // These are passed on to the API class instance and used as part of
                    // hash for lock.
                    $params = array();
                    // Use lock to keep from wasting time trying to do API that another
                    // Yapeal is already working on.
                    try {
                        $con = DatabaseConnection::connect(YAPEAL_DSN);
                        $sql = 'select get_lock(' . $con->qstr($hash) . ',5)';
                        if ($con->GetOne($sql) != 1) {
                            if (Logger::getLogger('yapeal')
                                ->isInfoEnabled()
                            ) {
                                $mess =
                                    'Failed to get lock for ' . $class . $hash;
                                Logger::getLogger('yapeal')
                                    ->info($mess);
                            };
                            continue;
                        }; // if $con->GetOne($sql) ...
                    } catch (\ADODB_Exception $e) {
                        continue;
                    }
                    // Give each API 60 seconds to finish. This should never happen but is
                    // here to catch runaways.
                    set_time_limit(60);
                    $instance = new $class($params);
                    if ($instance->apiStore()) {
                        ++$apiSuccess;
                    };
                    $instance = null;
                }; // if CachedUntil::cacheExpired...
                // See if Yapeal has been running for longer than 'soft' limit.
                if (YAPEAL_MAX_EXECUTE < time()) {
                    if (Logger::getLogger('yapeal')
                        ->isInfoEnabled()
                    ) {
                        $mess =
                            'Yapeal has been working very hard and needs a break';
                        Logger::getLogger('yapeal')
                            ->info($mess);
                    };
                    exit;
                }; // if YAPEAL_MAX_EXECUTE < time() ...
            }; // foreach $apis ...
        } catch (\ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                ->warn($e);
        }
        // Only truly successful if API was fetched and stored.
        if ($apiCount == $apiSuccess) {
            return true;
        } else {
            return false;
        }
    }
}

