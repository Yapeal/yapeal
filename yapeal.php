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
use Yapeal\Database\DBConnection;
use Yapeal\Filesystem\FilterFileFinder;

/**
 * @internal Only let this code be ran in CLI.
 */
if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', true, 403);
    $mess = basename(__FILE__) . ' only works with CLI version of PHP but tried'
        . ' to run it using ' . PHP_SAPI . ' instead.' . PHP_EOL;
    die($mess);
};
/**
 * @internal Only let this code be ran directly.
 */
$included = get_included_files();
if (count($included) > 1 || $included[0] != __FILE__) {
    $mess = basename(__FILE__)
        . ' must be called directly and can not be included.' . PHP_EOL;
    fwrite(STDERR, $mess);
    exit(1);
};
// Set the default timezone to GMT.
date_default_timezone_set('GMT');
// Get path constants so they can be used.
require_once __DIR__ . '/inc' . '/parseCommandLineOptions.php';
require_once __DIR__ . '/inc' . '/getSettingsFromIniFile.php';
require_once __DIR__ . '/inc' . '/usage.php';
require_once __DIR__ . '/inc' . '/showVersion.php';
require_once __DIR__ . '/inc' . '/setGeneralSectionConstants.php';
$shortOpts = array('c:', 'l:');
$longOpts = array('config:', 'log:');
// Parser command line options first in case user just wanted to see help.
$options = parseCommandLineOptions($shortOpts, $longOpts);
$exit = false;
if (isset($options['help'])) {
    usage(__FILE__, $shortOpts, $longOpts);
    exit(0);
};
if (isset($options['version'])) {
    showVersion(__FILE__);
    exit(0);
};
if (!empty($options['config'])) {
    $iniVars = getSettingsFromIniFile($options['config']);
} else {
    $iniVars = getSettingsFromIniFile();
};
if (empty($iniVars)) {
    exit(1);
};
// IDE only seems to like this form for path since need for this loader is going
// away ASAP not worrying about it.
require_once __DIR__ . '/class' . '/YapealAutoLoad.php';
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
setGeneralSectionConstants($iniVars);
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
    $sectionList =
        FilterFileFinder::getStrippedFiles(__DIR__ . '/class/', 'Section');
    if (count($sectionList) == 0) {
        $mess = 'No section classes were found check path setting';
        Logger::getLogger('yapeal')
              ->error($mess);
        exit(2);
    };
    //$sectionList = array_map('strtolower', $sectionList);
    // Randomize order in which API sections are tried if there is a list.
    if (count($sectionList) > 1) {
        shuffle($sectionList);
    };
    $sql = 'select `section`';
    $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilSections`';
    try {
        $con = DBConnection::connect(YAPEAL_DSN);
        $result = $con->GetCol($sql);
    } catch (ADODB_Exception $e) {
        Logger::getLogger('yapeal')
              ->fatal($e);
        exit(2);
    }
    if (count($result) == 0) {
        $mess = 'No sections were found in utilSections check database.';
        Logger::getLogger('yapeal')
              ->error($mess);
        exit(2);
    };
    $result = array_map('ucfirst', $result);
    $sectionList = array_intersect($sectionList, $result);
    // Now take the list of sections and call each in turn.
    foreach ($sectionList as $sec) {
        $class = 'Section' . $sec;
        try {
            /**
             * @var \ASection $instance
             */
            $instance = new $class();
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

