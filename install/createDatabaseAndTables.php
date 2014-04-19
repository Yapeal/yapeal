#!/usr/bin/env php
<?php
/**
 * Contains code used to create database and add tables.
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
 * Define short name for directory separator which always uses unix '/'.
 *
 * @ignore
 */
require_once dirname(__DIR__) . '/revision.php';
require_once dirname(__DIR__) . '/inc/parseCommandLineOptions.php';
require_once dirname(__DIR__) . '/inc/getSettingsFromIniFile.php';
require_once dirname(__DIR__) . '/inc/usage.php';
require_once dirname(__DIR__) . '/inc/showVersion.php';
$shortOpts = array('c:', 'd:', 'p:', 's:', 't:', 'u:');
$longOpts = array(
    'config:',
    'database:',
    'driver:',
    'password:',
    'server:',
    'suffix:',
    'table-prefix:',
    'username:'
);
$options = parseCommandLineOptions($shortOpts, $longOpts);
if (isset($options['help'])) {
    usage(__FILE__, $shortOpts, $longOpts);
    exit(0);
}
if (isset($options['version'])) {
    showVersion(__FILE__);
    exit(0);
}
if (!empty($options['config'])) {
    $section = getSettingsFromIniFile($options['config'], 'Database');
    unset($options['config']);
} else {
    $section = getSettingsFromIniFile(null, 'Database');
}
// Merge the configuration file settings with ones from command line.
// Settings from command line will override any from file.
$options = array_merge($section, $options);
$required = array('database', 'host', 'password', 'username');
$mess = '';
foreach ($required as $setting) {
    if (empty($options[$setting])) {
        $mess .= 'Missing required setting ' . $setting . PHP_EOL;
    }
}
if (!empty($mess)) {
    fwrite(STDERR, $mess);
    exit(2);
}
$mysqli = new mysqli(
    $options['host'], $options['username'], $options['password'], ''
);
if (mysqli_connect_error()) {
    $mess = 'Could NOT connect to MySQL. MySQL error was ('
        . mysqli_connect_errno() . ') ' . mysqli_connect_error();
    fwrite(STDERR, $mess);
    exit(2);
}
$fileNames = array(
    'Database',
    'AccountTables',
    'CharTables',
    'CorpTables',
    'EveTables',
    'MapTables',
    'ServerTables',
    'UtilTables'
);
$templates = array(';', '{database}', '{table_prefix}');
$replacements = array('', $options['database'], $options['table_prefix']);
foreach ($fileNames as $file) {
    $file = __DIR__ . '/sql/Create' . $file . '.sql';
    if (!is_file($file)) {
        $mess = 'Could not find SQL file ' . $file . PHP_EOL;
        fwrite(STDERR, $mess);
        continue;
    }
    $sqlStatements = file_get_contents($file);
    if (false === $sqlStatements) {
        $mess = 'Could not get contents of SQL file ' . $file;
        fwrite(STDERR, $mess);
        exit(2);
    }
    // Split up SQL into statements.
    $sqlStatements = explode(';', $sqlStatements);
    // Replace {database}, {table_prefix}, and ';' in statements.
    $sqlStatements = str_replace($templates, $replacements, $sqlStatements);
    foreach ($sqlStatements as $line => $sql) {
        $sql = trim($sql);
        // 5 is a 'magic' number that I think is shorter than any sql statement.
        if (strlen($sql) < 5) {
            continue;
        }
        if ($mysqli->query($sql) === false) {
            $mess = 'The following SQL statement failed on statement: ' . $line
                . PHP_EOL
                . $sql . PHP_EOL
                . '(' . $mysqli->errno . ') ' . $mysqli->error . PHP_EOL;
            fwrite(STDERR, $mess);
            $mysqli->close();
            exit(2);
        }
    }
}
$mysqli->close();
$mess =
    'All database tables have been installed or updated as needed.' . PHP_EOL;
fwrite(STDOUT, $mess);
exit(0);
