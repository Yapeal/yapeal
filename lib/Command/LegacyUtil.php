<?php
/**
 * Contains LegacyUtil class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of Yapeal
 * Copyright (C) 2014 Michael Cummings
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
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Command;

/**
 * Class LegacyUtil
 */
class LegacyUtil
{
    /**
     * Function used to get settings from an 'ini' configuration file.
     *
     * @param string $file    Path and name of the ini file to get.
     * @param string $section Name of a section if only one is wanted.
     *
     * @return array Returns list of settings from file.
     */
    function getSettingsFromIniFile($file = null, $section = null)
    {
        // Check if given custom configuration file.
        if (empty($file) || !is_string($file)) {
            $file = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'yapeal.ini';
        } else {
            $mess = 'Using custom configuration file ' . $file . PHP_EOL;
            fwrite(STDOUT, $mess);
        }
        if (!(is_readable($file) && is_file($file))) {
            $mess =
                'The ' . $file . ' configuration file is missing!' . PHP_EOL;
            fwrite(STDERR, $mess);
            return array();
        }
        // Grab the info from ini file.
        $settings = parse_ini_file($file, true);
        if (empty($settings)) {
            $mess = 'The ' . $file . ' configuration file contains no settings!'
                . PHP_EOL;
            fwrite(STDERR, $mess);
            return array();
        }
        if (isset($section)) {
            if (isset($settings[$section]) && !empty($settings[$section])) {
                return $settings[$section];
            };
            return array();
        }
        return $settings;
    }
    /**
     * Function used to parser command line options.
     *
     * This function was made to save having to make new one every time it might be
     * needed. It also helps with consistent option handle which should lead to less
     * user as well as programmer confusion.
     *
     * The -h, --help and -V, --version options don't have to be included in the
     * parameters as they will always be include automatically.
     *
     * Note that with older versions of PHP long options aren't available so all
     * required options must have a short form that can be used.
     *
     * @param array $shortOptions An array of short options to accept. The elements
     *                            should be in the same format as the short option parameter for setopt() i.e.
     *                            can be a single character followed by optional single colon for options that
     *                            have required values or double colons for ones that take an optional value.
     * @param array $longOptions  A simple array of long option names to accept. The
     *                            same options as setopt() work i.e. single colon for required values and
     *                            double colons for ones that take a optional value.
     *
     * @return array Returns an array of options or an empty array.
     */
    function parseCommandLineOptions(
        array $shortOptions = null,
        array $longOptions = null
    ) {
        if (!function_exists('getopt')) {
            return array();
        }
        $shortOptions = array_merge($shortOptions, array('h', 'V'));
        $shortOptions = implode('', $shortOptions);
        $longOptions = array_merge($longOptions, array('help', 'version'));
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $options = getopt($shortOptions, $longOptions);
        } else {
            $options = getopt($shortOptions);
        }
        $settings = array();
        if (empty($options)) {
            return $settings;
        }
        $optionsOnlyMap = array(
            'h' => 'help',
            'help' => 'help',
            'V' => 'version',
            'version' => 'version'
        );
        $optionsWithValuesMap = array(
            'c' => 'config',
            'config' => 'config',
            'd' => 'database',
            'database' => 'database',
            'driver' => 'driver',
            'l' => 'log-config',
            'log' => 'log-config',
            'p' => 'password',
            'password' => 'password',
            's' => 'host',
            'server' => 'host',
            'suffix' => 'suffix',
            'table-prefix' => 'table_prefix',
            'u' => 'username',
            'username' => 'username'
        );
        $optionsWithListMap = array(
            'privileges' => 'privileges',
            'xml' => 'xml'
        );
        foreach ($options as $opt => $value) {
            if (array_key_exists($opt, $optionsOnlyMap)) {
                $settings[$optionsOnlyMap[$opt]] = true;
                continue;
            }
            if (array_key_exists($opt, $optionsWithValuesMap)) {
                if (is_array($value)) {
                    // If option is used multiple times use the last value.
                    $value = $value[count($value) - 1];
                }
                $settings[$optionsWithValuesMap[$opt]] = (string)$value;
                continue;
            }
            if (array_key_exists($opt, $optionsWithListMap)) {
                if (is_array($value)) {
                    // If option is used multiple times combined them.
                    $value = implode(' ', $value);
                }
                if (isset($settings[$optionsWithListMap[$opt]])) {
                    // Append to the existing list from short option.
                    $settings[$optionsWithListMap[$opt]] .= ' ' . $value;
                } else {
                    $settings[$optionsWithListMap[$opt]] = $value;
                }
                continue;
            }
        }
        return $settings;
    }
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
            $userAgent = $section['application_agent'];
            $userAgent .= ' Yapeal/1.1.x-alpha';
            $userAgent .= ' (' . PHP_OS . ' ' . php_uname('m') . ')';
            $userAgent .= ' libcurl/' . $curl['version'];
            $userAgent = trim($userAgent);
            /**
             * Used as default user agent in network connections.
             */
            define('YAPEAL_APPLICATION_AGENT', $userAgent);
        }
        if (!defined('YAPEAL_REGISTERED_MODE')) {
            /**
             * Determines how utilRegisteredKey, utilRegisteredCharacter, and
             * utilRegisteredCorporation tables are used, it also allows some columns in
             * this tables to be optional depending on value.
             */
            define('YAPEAL_REGISTERED_MODE', $section['registered_mode']);
        }
    }
    /**
     * Function to show version information of script.
     *
     * @param string $file Name of script file.
     */
    function showVersion($file)
    {
        $mess = basename($file) . ' 1.1.x-alpha' . PHP_EOL . PHP_EOL
            . 'Copyright (c) 2008-2014, Michael Cummings.' . PHP_EOL
            . 'License LGPLv3+: GNU LGPL version 3 or later' . PHP_EOL
            . ' <http://www.gnu.org/copyleft/lesser.html>.' . PHP_EOL
            . 'See COPYING.md and COPYING-LESSER.md for more details.' . PHP_EOL
            . 'This program comes with ABSOLUTELY NO WARRANTY.' . PHP_EOL
            . PHP_EOL;
        fwrite(STDOUT, $mess);
    }
    /**
     * Function use to show the usage message on command line.
     *
     * Note that the 'h' and 'V' options and their corresponding long versions will
     * be included automatically without having to add them to the parameters.
     *
     * @param string $file         Name of script file.
     * @param array  $shortOptions An array of short options to accept. The elements
     *                             should be in the same format as the short option parameter for setopt() i.e.
     *                             can be a single character followed by optional single colon for options that
     *                             have required values or double colons for ones that take an optional value.
     * @param array  $longOptions  A simple array of long option names to accept. The
     *                             same options as setopt() work i.e. single colon for required values and
     *                             double colons for ones that take a optional value.
     *
     */
    function usage($file, array $shortOptions = null, array $longOptions = null)
    {
        $shortOptions = array_merge($shortOptions, array('h', 'V'));
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $longOptions = array_merge($longOptions, array('help', 'version'));
        } else {
            $longOptions = array();
        }
        $file = basename($file);
        $cutLine = 78;
        $ragLine = $cutLine - 5;
        $mess = PHP_EOL . 'Usage: ' . $file;
        $mess .= ' [[-h | -V] | Options ...]' . PHP_EOL . PHP_EOL;
        $desc =
            'The script reads database settings from [Database] section of the';
        $desc .= ' configuration file, either the default one in';
        $desc .= ' < yapeal_base>/config/yapeal.ini or the custom one from the -c or';
        $desc .= ' --config OPTION.';
        $desc .= ' Command line options have priority over settings in the';
        $desc .= ' configuration file and the configuration file has priority over';
        $desc .= ' the defaults. If no configuration file is found, either default';
        $desc .= ' or custom, or if some of the settings are missing from it the';
        $desc .= ' corresponding command line options becomes required. For example';
        $desc .= ' if the configuration file had all but the "password" setting then';
        $desc .= ' the -p or --password option would no longer be optional but';
        $desc .= ' required on the command line.';
        // Make text ragged right with forced word wrap at 80 characters.
        $desc = wordwrap($desc, $ragLine, PHP_EOL);
        $desc = wordwrap($desc, $cutLine, PHP_EOL, true);
        $mess .= $desc . PHP_EOL . PHP_EOL;
        $desc =
            'Mandatory arguments to long options are mandatory for short options';
        $desc .= ' as well. For most OPTIONs if they are used more than once only the';
        $desc .= ' last value will be used. Exceptions to this will be noted below.';
        $desc = wordwrap($desc, $ragLine, PHP_EOL);
        $desc = wordwrap($desc, $cutLine, PHP_EOL, true);
        $mess .= $desc . PHP_EOL . PHP_EOL;
        $mess .= 'Options:' . PHP_EOL;
        $options = array();
        $options['c:'] = array(
            'op' => '  -c, --config=FILE',
            'desc' =>
                'Read configuration from FILE. This is an optional setting to allow the use'
                . ' of a custom configuration file. FILE must be in "ini" format. Defaults'
                . ' to <yapeal_base>/config/yapeal.ini.'
        );
        $options['d:'] = array(
            'op' => '  -d, --database=DB',
            'desc' =>
                'DB is the database name to use for the operation.'
        );
        $options['driver:'] = array(
            'op' => '  --driver=DRIVER',
            'desc' =>
                'DRIVER is only use during testing and should only be used if directed to'
                . ' do so by a developer. Optional setting that defaults to mysql://.'
        );
        $options['h'] =
            array('op' => '  -h, --help', 'desc' => 'Show this help.');
        $options['l:'] = array(
            'op' => '  -l, --log-config=LOG',
            'desc' =>
                'LOG should be the path and name of a file that holds logging configuration'
                . ' settings. The file can be in either INI or XML format. Optional setting'
                . ' that defaults to <yapeal_base>/config/logger.xml'
        );
        $options['p:'] = array(
            'op' => '  -p, password=PASS',
            'desc' =>
                'PASS is the password for the database server.'
        );
        $options['privileges:'] = array(
            'op' => '  --privileges=PRIVS',
            'desc' =>
                'Optional PRIVS list. It is either a quoted space separated list of'
                . ' privileges names to use or can be used multiple times and the values'
                . ' from each one will be appended to the list. For example you can either'
                . ' do ' . $file . ' --privileges="alter create" OR'
                . ' ' . $file
                . ' --privileges="alter" --privileges="create". This option'
                . ' should rarely be needed as Yapeal uses the default list "alter create'
                . ' delete drop index insert select" which includes all the privileges'
                . ' normally needed.'
        );
        $options['s:'] = array(
            'op' => '  -s, --server=HOST',
            'desc' =>
                'HOST is the database server name to use.'
        );
        $options['suffix:'] = array(
            'op' => '  --suffix=SUFFIX',
            'desc' =>
                'SUFFIX is another optional setting only used during testing. Only use if'
                . ' directed to by a developer. Defaults to ?new.'
        );
        $options['table-prefix:'] = array(
            'op' => '  --table-prefix=PREFIX',
            'desc' =>
                'Append PREFIX to all the table names. This is an optional setting that is'
                . ' mostly useful when combining Yapeal tables with the tables from an'
                . ' application in the same database. Defaults to empty string.'
        );
        $options['u:'] = array(
            'op' => '  -u, --username=USER',
            'desc' =>
                'USER is the user name for the database server.'
        );
        $options['V'] = array(
            'op' => '  -V, --version',
            'desc' =>
                'Show version and licensing information.'
        );
        $options['xml:'] = array(
            'op' => '  --xml=XML',
            'desc' =>
                'Optional XML section list. It is either a quoted space separated list of'
                . ' xml section names to use or can be used multiple times and the values'
                . ' from each one will be appended to the list. For example you can either'
                . ' do ' . $file . ' -xml="util account" OR'
                . ' ' . $file
                . ' -xml="util" -xml="account". This option should rarely be'
                . ' needed as Yapeal uses the default list "util account char corp eve map'
                . ' server" which includes all the sections normally needed.'
        );
        $width = 0;
        foreach ($options as $k => $v) {
            if (!(in_array($k, $shortOptions) || in_array($k, $longOptions))) {
                continue;
            }
            if (strlen($v['op']) > $width) {
                $width = strlen($v['op']);
            }
        }
        $width += 4;
        $break = PHP_EOL . str_pad('', $width);
        $descCut = $cutLine - $width;
        $descRag = $descCut - 5;
        foreach ($options as $k => $v) {
            if (!(in_array($k, $shortOptions) || in_array($k, $longOptions))) {
                continue;
            }
            $option = str_pad($v['op'], $width);
            // Make description text ragged right with forced word wrap at full width.
            $desc = wordwrap($v['desc'], $descRag, PHP_EOL);
            $desc = wordwrap($desc, $descCut, PHP_EOL, true);
            $option .= str_replace(PHP_EOL, $break, $desc);
            $mess .= $option . PHP_EOL . PHP_EOL;
        }
        fwrite(STDOUT, $mess);
    }
}
