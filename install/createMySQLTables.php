#!/usr/bin/php -Cq
<?php
/**
 * Contains code used to test if user has privileges on a MySQL database.
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
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @subpackage Install
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
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
  header('HTTP/1.0 403 Forbidden', TRUE, 403);
  $mess = basename(__FILE__) . ' only works with CLI version of PHP but tried';
  $mess = ' to run it using ' . PHP_SAPI . ' instead';
  die($mess);
};
/**
 * @internal Only let this code be ran directly.
 */
$included = get_included_files();
if (count($included) > 1 || $included[0] != __FILE__) {
  $mess = basename(__FILE__) . ' must be called directly and can not be included';
  fwrite(STDERR, $mess);
  fwrite(STDOUT, 'error' . PHP_EOL);
  exit(1);
};
/**
 * Define short name for directory separator which always uses unix '/'.
 * @ignore
 */
define('DS', '/');
// Used to over come path issues caused by how script is ran on server.
$baseDir = str_replace('\\', DS, realpath(dirname(__FILE__) . DS. '..')) . DS;
// Pull in Yapeal revision constants.
require_once $baseDir . 'revision.php';
// Get path constants so they can be used.
require_once $baseDir . 'inc' . DS . 'common_paths.php';
// Load ADO classes that are needed.
//require_once YAPEAL_ADODB . 'adodb-exceptions.inc.php';
require_once YAPEAL_ADODB . 'adodb.inc.php';
require_once YAPEAL_ADODB . 'adodb-xmlschema03.inc.php';
// If function getopts available get any command line parameters.
if (function_exists('getopt')) {
  $options = parseCommandLineOptions($argv);
};// if function_exists getopt ...
if (!empty($options['config'])) {
  $section = getSettingsFromIniFileForDatabaseSection($options['config']);
  unset($options['config']);
} else {
  $section = getSettingsFromIniFileForDatabaseSection();
};
if (isset($options['xml'])) {
  $sections = explode(' ', $options['xml']);
  unset($options['xml']);
} else {
  $sections = array('util', 'account', 'char', 'corp', 'eve', 'map', 'server');
};
// Merge the configuration file settings with ones from command line.
// Settings from command line will override any from file.
$options = array_merge($section, $options);
$required = array('database', 'host', 'password', 'username');
$mess = '';
foreach ($required as $setting) {
  if (empty($options[$setting])) {
    $mess .= 'Missing required setting ' . $setting;
    $mess .= ' in section [Database].' . PHP_EOL;
  };
};// foreach $required ...
if (!empty($mess)) {
  fwrite(STDERR, $mess);
  exit(2);
};
if (isset($options['driver'])) {
  $dsn = $options['driver'];
} else {
  $dsn = 'mysql://';
};
$dsn .= $options['username'] . ':' . $options['password'] . '@';
$dsn .= $options['host'] . '/' . $options['database'];
if (isset($options['suffix'])) {
  $dsn .= $options['suffix'];
} else {
  $dsn .= '?new';
};
$ret = 0;
try {
  // Get connection to DB.
  $db = ADONewConnection($dsn);
  foreach ($sections as $section) {
    $file = realpath(YAPEAL_INSTALL . $section . '.xml');
    if (!is_file($file)) {
      $mess = 'Could not find XML file ' . $file;
      fwrite(STDERR, $mess);
      continue;
    };
    $xml = file_get_contents($file);
    if (FALSE === $xml) {
      $mess = 'Could not get contents of XML file ' . $file;
      fwrite(STDERR, $mess);
      continue;
    };
    // Get new Schema.
    $schema = new adoSchema($db);
    // Some settings for Schema.
    $schema->ExecuteInline(FALSE);
    $schema->ContinueOnError(FALSE);
    $schema->SetUpgradeMethod('ALTER');
    if (isset($options['table_prefix'])) {
      $schema->SetPrefix($options['table_prefix'], FALSE);
    };
    $sql = $schema->ParseSchemaString($xml);
    $result = $schema->ExecuteSchema($sql);
    $save = $schema->SaveSQL(YAPEAL_CACHE . 'ADOdb' . DS . $section . '.sql');
    if (FALSE === $save) {
      $mess = 'Could not save ' . YAPEAL_CACHE . 'ADOdb' . DS . $section . '.sql' . PHP_EOL;
      fwrite(STDERR, $mess);
    };
    if ($result == 2) {
      ++$ret;
    } else if ($result == 1) {
      $mess = 'Error executing schema for ' . $section . PHP_EOL;
      fwrite(STDERR, $mess);
    } else {
      $mess = 'Failed to execute schema for ' . $section . PHP_EOL;
      fwrite(STDERR, $mess);
    };
    $schema = NULL;
  };// foreach $sections as $section ...
  if (count($sections) != $ret) {
    $mess .= 'There were problems during processing please check any error';
    $mess .= ' messages from above and correct.' . PHP_EOL;
    fwrite(STDERR, $mess);
    exit(2);
  };
} catch (Exception $e) {
  $mess =  'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
  if ($e->getCode()) {
    $mess .= '     Code: ' . $e->getCode() . PHP_EOL;
  };
  $mess .= '     File: ' . $e->getFile() . '(' . $e->getLine() . ')' . PHP_EOL;
  $mess .= '    Trace:' . PHP_EOL;
  $mess .= $e->getTraceAsString() . PHP_EOL;
  $mess .= str_pad(' END TRACE ', 30, '-', STR_PAD_BOTH) . PHP_EOL;
  fwrite(STDERR, $mess);
  exit(2);
}
$mess = 'All database tables have been installed or updated as needed.' . PHP_EOL;
fwrite(STDOUT, $mess);
exit(0);
/**
 * Function used to get 'ini' configuration file.
 *
 * @param string $file Path and name of the ini file to get.
 *
 * @return array Returns list of settings from file.
 */
function getSettingsFromIniFileForDatabaseSection($file = NULL) {
  // Check if given custom configuration file.
  if (empty($file) || !is_string($file)) {
    // Default assumes that this file and yapeal.ini file are in 'neighboring'
    // directories.
    $file = YAPEAL_CONFIG . 'yapeal.ini';
  } else {
    $mess = 'Using custom configuration file ' . $file . PHP_EOL;
    fwrite(STDOUT, $mess);
  };
  if (!(is_readable($file) && is_file($file))) {
    $mess = 'The ' . $file . ' configuration file is missing!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  // Grab the info from ini file.
  $settings = parse_ini_file($file, TRUE);
  if (empty($settings)) {
    $mess = 'The ' . $file . ' configuration file contains no settings!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  if (empty($settings['Database'])) {
    $mess = 'No settings for [Database] section found in configuration file!' . PHP_EOL;
    fwrite(STDERR, $mess);
    return array();
  };
  // Can't use default from configuration file for this or adodb-xmlschema has a
  // fit and refuses to work.
  unset($settings['Database']['driver']);
  return $settings['Database'];
}// function getSettingsFromIniFileForDatabaseSection
/**
 * Function used to parser command line options.
 *
 * @param array $argv Array of arguments passed to script.
 *
 * @return mixed Returns settings list.
 */
function parseCommandLineOptions($argv) {
  $shortOpts = 'c:d:hp:s:t:u:Vx:';
  if (version_compare(PHP_VERSION, '5.3.0', '>=')
    || strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
    $longOpts = array('config:', 'database:', 'driver:', 'help', 'password:',
      'server:', 'suffix:', 'table-prefix:', 'username:', 'version', 'xml:');
    $options = getopt($shortOpts, $longOpts);
  } else {
    $options = getopt($shortOpts);
  };
  $settings = array('config' => NULL, 'xml' =>
    'util account char corp eve map server');
  if (empty($options)) {
    return $settings;
  };
  $exit = FALSE;
  foreach ($options as $opt => $value) {
    switch ($opt) {
      case 'c':
      case 'config':
        if (is_array($value)) {
          // If option is used multiple times use the last value.
          $value = $value[count($value) - 1];
        };
        $settings['config'] = (string)$value;
        break;
      case 'd':
      case 'database':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['database'] = (string)$value;
        break;
      case 'driver':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['driver'] = (string)$value;
        break;
      case 'p':
      case 'password':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['password'] = (string)$value;
        break;
      case 's':
      case 'server':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['host'] = (string)$value;
        break;
      case 'suffix':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['suffix'] = (string)$value;
        break;
      case 't':
      case 'table-prefix':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['table_prefix'] = (string)$value;
        break;
      case 'u':
      case 'username':
        if (is_array($value)) {
          $value = $value[count($value) - 1];
        };
        $settings['username'] = (string)$value;
        break;
      case 'x':
      case 'xml':
        if (is_array($value)) {
          // If option is used multiple times combined them.
          $value = implode(' ', $value);
        };
        $settings['xml'] = (string)$value;
        break;
      case 'h':
      case 'help':
        usage($argv);
        // Fall through is intentional.
      case 'V':
      case 'version':
        $mess = basename($argv[0]);
        if (YAPEAL_VERSION != 'svnversion') {
          $mess .= ' ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
          $mess .= YAPEAL_DATE . PHP_EOL . PHP_EOL;
        } else {
          $rev = str_replace(array('$', 'Rev:'), '', '$Rev$');
          $date = str_replace(array('$', 'Date:'), '', '$Date$');
          $mess .= $rev . '(svn)' . $date . PHP_EOL;
        };
        $mess .= 'Copyright (c) 2008-2011, Michael Cummings.' . PHP_EOL;
        $mess .= 'License LGPLv3+: GNU LGPL version 3 or later' . PHP_EOL;
        $mess .= ' <http://www.gnu.org/copyleft/lesser.html>.' . PHP_EOL;
        $mess .= 'See COPYING and COPYING-LESSER for more details.' . PHP_EOL;
        $mess .= 'This program comes with ABSOLUTELY NO WARRANTY.' . PHP_EOL . PHP_EOL;
        fwrite(STDOUT, $mess);
        $exit = TRUE;
        break;
    };// switch $opt
  };// foreach $options...
  if ($exit == TRUE) {
    exit;
  };
  return $settings;
};// function parseCommandLineOptions
/**
 * Function use to show the usage message on command line.
 *
 * @param array $argv Array of arguments passed to script.
 */
function usage($argv) {
  $ragLine = 76;
  $cutLine = 80;
  $mess = PHP_EOL . 'Usage: ' . basename($argv[0]);
  $mess .= ' [OPTION]...' . PHP_EOL . PHP_EOL;
  $desc = 'The script reads database settings from [Database] section of the';
  $desc .= ' configuration file, either the default one in';
  $desc .= ' /Where/Installed/Yapeal/config/yapeal.ini or the custom one from';
  $desc .= ' -c OPTION. The other OPTIONs -d, -p, -s, -t, -u can be used to';
  $desc .= ' override any settings found in the configuration file. If no';
  $desc .= ' configuration file is found, either default or custom, or some';
  $desc .= ' of the settings are missing from it the OPTIONs -d, -p, -s, -t, -u';
  $desc .= ' become required. For example the configuration file has all but';
  $desc .= ' the "password" setting. The -p option will be required on the';
  $desc .= ' command line.';
  // Make text ragged right with forced word wrap at 80 characters.
  $desc = wordwrap($desc, $ragLine, PHP_EOL);
  $desc = wordwrap($desc, $cutLine, PHP_EOL, TRUE);
  $mess .= $desc . PHP_EOL . PHP_EOL;
  $desc = 'Short version of OPTIONs have the same value requirements of the';
  $desc .= ' corresponding long ones. For all OPTIONs except -x if they are';
  $desc .= ' used more than once only the last value will be used.';
  $desc = wordwrap($desc, $ragLine, PHP_EOL);
  $desc = wordwrap($desc, $cutLine, PHP_EOL, TRUE);
  $mess .= $desc . PHP_EOL . PHP_EOL;
  $mess .= 'OPTIONs:' . PHP_EOL;
  $options = array();
  $options[] = array('pp' => '-c, --config=FILE', 'desc' =>
    'Read configuration from FILE. This is an optional setting to allow using a'
    . ' custom configuration file. File must be in "ini" format. Defaults to'
    . ' /Where/Installed/Yapeal/config/yapeal.ini.');
  $options[] = array('pp' => '-d, --database', 'desc' =>
    'The database name the table(s) will be added to.');
  $options[] = array('pp' => '--driver=DRIVER', 'desc' =>
    'DRIVER is only use during testing and should only be used if directed to'
    . ' by a developer. Optional setting that defaults to mysql://.');
  $options[] = array('pp' => '-h, --help', 'desc' => 'Show this help.');
  $options[] = array('pp' => '-p, password=SECRET', 'desc' =>
    'Use SECRET as the password for the database.');
  $options[] = array('pp' => '-s, --server=LOCALHOST', 'desc' =>
    'LOCALHOST is the database server name to use.');
  $options[] = array('pp' => '--suffix=SUFFIX', 'desc' =>
    'SUFFIX is another optional setting only used during testing. Only use if'
    . ' directed to by developer. Defaults to ?new.');
  $options[] = array('pp' => '-t, --table-prefix=PREFIX', 'desc' =>
    'Append PREFIX to all the table names. This is an optional setting that is'
    . ' mostly useful when combining Yapeal tables with the tables from an'
    . ' application in the same database. Dafaults to empty string.');
  $options[] = array('pp' => '-u, --username=USER', 'desc' =>
    'Use USER as the user name for the database.');
  $options[] = array('pp' => '-V, --version', 'desc' =>
    'Show version and licensing information.');
  $options[] = array('pp' => '-x, --xml=XML', 'desc' =>
    'Optional XML file list. It is either a quoted space separated list of xml'
    . ' file names to use or can be used multiple times and the values from'
    . ' each one will be appended to the list. For example you can either do'
    . ' createMySQLTables.php -x "util account" or'
    . ' createMySQLTables.php -x "util" -x "account". This option should rarely'
    . ' be needed as Yapeal uses the default list "util account char corp eve'
    . ' map server" which includes all the files normally needed.');
  $width = 0;
  foreach ($options as $option) {
    // find widest parameter in list.
    if (strlen($option['pp']) > $width) {
      $width = strlen($option['pp']);
    };
  };// foreach $options ...
  // Give another six spaces for padding.
  $width += 6;
  foreach ($options as $option) {
    $desc = str_pad('  ' . $option['pp'], $width);
    $desc .= $option['desc'];
    // Make text ragged right with forced word wrap at 80 characters.
    $desc = wordwrap($desc, $ragLine, PHP_EOL);
    $desc = wordwrap($desc, $cutLine, PHP_EOL, TRUE);
    $mess .= $desc . PHP_EOL . PHP_EOL;
  };// foreach $options ...
  fwrite(STDOUT, $mess);
};// function usage
?>
