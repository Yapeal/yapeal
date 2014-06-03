#!/usr/bin/env php
<?php
/**
 * Used to get information from Eve-online API and store in database.
 *
 * This script expects to be ran from a command line or from a crontab job. The
 *  script can optionally be pass a config file name with -c option.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * as Yapeal.
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
 * @since      revision 561
 */
use Yapeal\Caching\EveApiXmlCache;
use Yapeal\Command\LegacyUtil;
use Yapeal\Database\DBConnection;
use Yapeal\Database\Util\AccessMask;
use Yapeal\Database\Util\CachedInterval;

// Set the default timezone to GMT.
date_default_timezone_set('GMT');
// IDE only seems to like this form for path since need for this loader is going
// away ASAP not worrying about it.
require_once __DIR__ . '/bin' . '/YapealAutoLoad.php';
YapealAutoLoad::activateAutoLoad();
// Include Composer's auto-loader for all the classes that are being moved.
/*
 * Find auto loader from one of
 * vendor/bin/
 * OR ./
 * OR bin/
 * OR lib/Yapeal/
 * OR vendor/yapeal/yapeal/bin/
 */
(@include_once dirname(__DIR__) . '/autoload.php')
|| (@include_once __DIR__ . '/vendor/autoload.php')
|| (@include_once dirname(__DIR__) . '/vendor/autoload.php')
|| (@include_once dirname(dirname(__DIR__)) . '/vendor/autoload.php')
|| (@include_once dirname(dirname(dirname(__DIR__))) . '/autoload.php')
|| die('Could not find required auto class loader. Aborting ...');
$legacyUtil = new LegacyUtil();
$shortOpts = array('c:', 'l:');
$longOpts = array('config:', 'log:');
// Parser command line options first in case user just wanted to see help.
$options = $legacyUtil->parseCommandLineOptions($shortOpts, $longOpts);
$exit = false;
if (isset($options['help'])) {
    $legacyUtil->usage(__FILE__, $shortOpts, $longOpts);
    exit(0);
};
if (isset($options['version'])) {
    $legacyUtil->showVersion(__FILE__);
    exit(0);
};
if (!empty($options['config'])) {
    $iniVars = $legacyUtil->getSettingsFromIniFile($options['config']);
} else {
    $iniVars = $legacyUtil->getSettingsFromIniFile();
};
if (empty($iniVars)) {
    exit(1);
};
/**
 * Define constants and properties from settings in configuration.
 */
if (!empty($options['log-config'])) {
    YapealErrorHandler::setLoggingSectionProperties(
        $iniVars['Logging'],
        $options['log-config']
    );
    unset($options['config']);
} else {
    YapealErrorHandler::setLoggingSectionProperties($iniVars['Logging']);
};
YapealErrorHandler::setupCustomErrorAndExceptionSettings();
EveApiXmlCache::setCacheSectionProperties($iniVars['Cache']);
DBConnection::setDatabaseSectionConstants($iniVars['Database']);
$legacyUtil->setGeneralSectionConstants($iniVars);
unset($iniVars);
try {
    /**
     * Give ourselves a 'soft' limit of 10 minutes to finish.
     */
    define('YAPEAL_MAX_EXECUTE', strtotime('10 minutes'));
    /**
     * This is used to have the same time on all APIs that error out and need to
     * be tried again.
     */
    define('YAPEAL_START_TIME', gmdate('Y-m-d H:i:s', YAPEAL_MAX_EXECUTE));
    /* ************************************************************************
     * Generate section list
     * ************************************************************************/
    $sql = 'select `activeAPIMask`,`section`';
    $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilSections`';
    $sql .= ' where isActive = 1';
    $sql .= ' and activeAPIMask > 0';
    try {
        $con = DBConnection::connect(YAPEAL_DSN);
        $result = $con->GetAll($sql);
    } catch (ADODB_Exception $e) {
        Logger::getLogger('yapeal')
              ->fatal($e);
        exit(2);
    }
    if (count($result) == 0) {
        $mess = 'No active sections were found in utilSections.';
        Logger::getLogger('yapeal')
              ->info($mess);
        exit(2);
    };
    shuffle($result);
    $am = new AccessMask();
    // Now take the list of sections and call each in turn.
    foreach ($result as $section) {
        $class = '\\Yapeal\\Database\\Section\\' . ucfirst($section['section']);
        if (!class_exists($class)) {
            $mess = 'Could NOT find class: ' . $class;
            Logger::getLogger('yapeal')
                  ->info($mess);
            continue;
        }
        try {
            /**
             * @var \Yapeal\Database\AbstractSection $instance
             */
            $instance = new $class($am, $section['activeAPIMask']);
            $instance->pullXML();
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->fatal($e);
        }
        // Going to sleep for a tenth of a second to let DB time to flush etc
        // between sections.
        usleep(100000);
    }
    /* ************************************************************************
     * Final admin stuff
     * ************************************************************************/
    // Reset cache intervals
    CachedInterval::resetAll();
    // Release all the ADOdb connections.
    DBConnection::releaseAll();
} catch (Exception $e) {
    $mess = 'Uncaught exception in ' . basename(__FILE__);
    Logger::getLogger('yapeal')
          ->fatal($mess);
    Logger::getLogger('yapeal')
          ->fatal($e);
    exit(1);
}
exit(0);

