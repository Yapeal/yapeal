#!/usr/bin/php -Cq
<?php
/**
 * Contains code used to delete old cached XML.
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
 * @since      2012-01-15
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
    highlight_file(__FILE__);
    exit();
};
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
/**
 * Define short name for directory separator which always uses unix '/'.
 *
 * @ignore
 */
define('DS', '/');
$inc = dirname(__DIR__) . '/inc' . DIRECTORY_SEPARATOR;
// Get path constants so they can be used.
require_once $inc . 'parseCommandLineOptions.php';
require_once $inc . 'getSettingsFromIniFile.php';
require_once $inc . 'usage.php';
require_once $inc . 'showVersion.php';
$adoDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ext'
    . DIRECTORY_SEPARATOR . 'ADOdb' . DIRECTORY_SEPARATOR;
require_once $adoDir . 'adodb.inc.php';
require_once dirname(__DIR__) . '/class' . '/FilterFileFinder.php';
$shortOpts = array('c:', 'd:', 'p:', 's:', 't:', 'u:');
$longOpts = array(
    'config:',
    'database:',
    'driver:',
    'password:',
    'server:',
    'suffix:',
    'table-prefix:',
    'username:',
    'xml:'
);
$options = parseCommandLineOptions($shortOpts, $longOpts);
if (isset($options['help'])) {
    usage(__FILE__, $shortOpts, $longOpts);
    exit(0);
};
if (isset($options['version'])) {
    showVersion(__FILE__);
    exit(0);
};
if (!empty($options['config'])) {
    $dbSettings = getSettingsFromIniFile($options['config'], 'Database');
    $cacheSettings = getSettingsFromIniFile($options['config'], 'Cache');
    unset($options['config']);
} else {
    $dbSettings = getSettingsFromIniFile(null, 'Database');
    $cacheSettings = getSettingsFromIniFile(null, 'Cache');
};
if (isset($options['xml'])) {
    $sections = explode(' ', $options['xml']);
    unset($options['xml']);
} else {
    $sections = array('account', 'char', 'corp', 'eve', 'map', 'server');
};
// Need settings from Cache section as well.
$required = array('cache_length', 'cache_output');
$mess = '';
foreach ($required as $setting) {
    if (empty($cacheSettings[$setting])) {
        $mess .= 'Missing required setting ' . $setting . PHP_EOL;
    };
}
if (!empty($mess)) {
    fwrite(STDERR, $mess);
    exit(2);
};
try {
    switch ($cacheSettings['cache_output']) {
        case 'both':
            cleanDatabase(
                $sections,
                $cacheSettings['cache_length'],
                $dbSettings,
                $options
            );
            cleanFiles($sections, $cacheSettings['cache_length']);
            break;
        case 'database':
            cleanDatabase(
                $sections,
                $cacheSettings['cache_length'],
                $dbSettings,
                $options
            );
            break;
        case 'file':
            cleanFiles($sections, $cacheSettings['cache_length']);
            break;
        case 'none':
            break;
        default:
            $mess =
                'Unknown "cache_output" mode: ' . $cacheSettings['cache_output']
                . PHP_EOL;
            fwrite(STDERR, $mess);
            exit(2);
    };
} catch (Exception $e) {
    $mess = 'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
    if ($e->getCode()) {
        $mess .= '     Code: ' . $e->getCode() . PHP_EOL;
    };
    $mess .= '     File: ' . $e->getFile() . '(' . $e->getLine() . ')'
        . PHP_EOL;
    $mess .= '    Trace:' . PHP_EOL;
    $mess .= $e->getTraceAsString() . PHP_EOL;
    $mess .= str_pad(' END TRACE ', 30, '-', STR_PAD_BOTH) . PHP_EOL;
    fwrite(STDERR, $mess);
    exit(2);
}
$mess = 'Cache has been cleaned.' . PHP_EOL;
fwrite(STDOUT, $mess);
exit(0);
/**
 * This is used to delete any database records from the XML cache that are more
 * than a configurable number of days old.
 *
 * @param array $sections    List of sections that will be cleaned.
 * @param int   $cacheLength Anything older than this number in days is deleted.
 * @param array $dbSettings  Database settings from ini file.
 * @param array $options     Command line options.
 */
function cleanDatabase(
    array $sections,
    $cacheLength,
    array $dbSettings,
    array $options
) {
    // Merge the configuration file Database settings with ones from command line.
    // Settings from command line will override any from file.
    $options = array_merge($dbSettings, $options);
    $required = array('database', 'host', 'password', 'username');
    $mess = '';
    foreach ($required as $setting) {
        if (empty($options[$setting])) {
            $mess .= 'Missing required setting ' . $setting . PHP_EOL;
        };
    }
    if (!empty($mess)) {
        fwrite(STDERR, $mess);
        exit(2);
    };
    $dsn =
        'mysqli://' . $options['username'] . ':' . $options['password'] . '@';
    $dsn .= $options['host'] . '/' . $options['database'];
    if (isset($options['suffix'])) {
        $dsn .= $options['suffix'];
    } else {
        $dsn .= '?new';
    };
    if (!isset($options['table_prefix'])) {
        $prefix = '';
    } else {
        $prefix = $dbSettings['table_prefix'];
    }
    $cacheLength = time() - $cacheLength * 86400;
    $dateTime = gmdate('Y-m-d H:i:s', strtotime($cacheLength));
    // Get connection to DB.
    $db = ADONewConnection($dsn);
    foreach ($sections as $section) {
        $sql = 'delete from `' . $prefix . 'utilXmlCache`';
        $sql .= ' where `section`=' . $db->qstr($section);
        $sql .= ' and `modified`<' . $db->qstr($dateTime);
        $db->Execute($sql);
    }
}


/**
 * This is used to delete any cached XML files that are more than a configurable
 * number of days old.
 *
 * @param array $sections    List of sections that will be cleaned.
 * @param int   $cacheLength Anything older than this number in days is deleted.
 */
function cleanFiles($sections, $cacheLength)
{
    $timeStamp = time() - $cacheLength * 86400;
    // Clear out any old cached file information before starting.
    clearstatcache(true);
    foreach ($sections as $section) {
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'cache'
            . DIRECTORY_SEPARATOR . $section . DS;
        $files = new FilterFileFinder($path, 'xml', FilterFileFinder::SUFFIX);
        foreach ($files as $file) {
            $mTime = @filemtime($file);
            $cTime = @filectime($file);
            // Find old files that haven't been modified and ones that have.
            if (($mTime === false && $cTime < $timeStamp)
                || $mTime < $timeStamp
            ) {
                @unlink($file);
            };
        }
    }
}



