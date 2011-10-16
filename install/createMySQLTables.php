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
require_once YAPEAL_ADODB . 'adodb.inc.php';
require_once YAPEAL_ADODB . 'adodb-xmlschema03.inc.php';
require_once YAPEAL_INC . 'parseCommandLineOptions.php';
require_once YAPEAL_INC . 'getSettingsFromIniFile.php';
require_once YAPEAL_INC . 'usage.php';
// If function getopts available get any command line parameters.
if (function_exists('getopt')) {
  $shortOpts = array('c:', 'd:', 'p:', 's:', 't:', 'u:');
  $longOpts = array('config:', 'database:', 'driver:', 'password:', 'server:',
    'suffix:', 'table-prefix:', 'username:', 'xml:');
  $options = parseCommandLineOptions($shortOpts, $longOpts);
  $exit = FALSE;
  if (isset($options['help'])) {
    $file = basename(__FILE__);
    usage($file, $shortOpts, $longOpts);
    $exit = TRUE;
  };
  if (isset($options['version'])) {
    $mess = basename(__FILE__);
    if (YAPEAL_VERSION != 'svnversion') {
      $mess .= ' ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
      $mess .= YAPEAL_DATE . PHP_EOL . PHP_EOL;
    } else {
      $rev = str_replace(array('$', 'Rev:'), '', '$Rev$');
      $date = str_replace(array('$', 'Date::'), '', '$Date:: 2011-10-12 08:03:54 -0700$');
      $mess .= $rev . '(svn)' . $date . PHP_EOL . PHP_EOL;
    };
    $mess .= 'Copyright (c) 2008-2011, Michael Cummings.' . PHP_EOL;
    $mess .= 'License LGPLv3+: GNU LGPL version 3 or later';
    $mess .= ' <http://www.gnu.org/copyleft/lesser.html>.' . PHP_EOL;
    $mess .= 'See COPYING and COPYING-LESSER for more details.' . PHP_EOL;
    $mess .= 'This program comes with ABSOLUTELY NO WARRANTY.' . PHP_EOL . PHP_EOL;
    fwrite(STDOUT, $mess);
    $exit = TRUE;
  };
  if ($exit == TRUE) {
    exit(0);
  };
};// if function_exists getopt ...
if (!empty($options['config'])) {
  $section = getSettingsFromIniFile($options['config'], 'Database');
  unset($options['config']);
} else {
  $section = getSettingsFromIniFile(NULL, 'Database');
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
$dsn = 'mysql://' . $options['username'] . ':' . $options['password'] . '@';
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
?>
