#!/usr/bin/php -Cq
<?php
/**
 * Used to get information from Eve-online API and store in database.
 *
 * This script expects to be ran from a command line or from a crontab job. The
 *  script can optionally be pass a config file name with -c option.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal.
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
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 * @since      revision 561
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
  fwrite(STDERR, $mess . PHP_EOL);
  fwrite(STDOUT, 'error' . PHP_EOL);
  exit(1);
};
// Set the default timezone to GMT.
date_default_timezone_set('GMT');
// Set some minimal error settings for now.
presetErrorHandling();
/**
 * Define short name for directory separator which always uses unix '/'.
 */
define('DS', '/');
// Check if the base path for Yapeal has been set in the environment.
$dir = @getenv('YAPEAL_BASE');
if ($dir === FALSE) {
  // Used to overcome path issues caused by how script is ran on server.
  $dir = str_replace('\\', DS, dirname(__FILE__));
};
/**
 * We know we are in the 'base' directory might as well set constant.
 *
 * @ignore
 */
define('YAPEAL_BASE', $dir . DS);
// Pull in Yapeal revision constants.
require_once YAPEAL_BASE . 'revision.php';
/**
 * Since we know that we are at 'base' directory we know where 'inc' should be
 * as well.
 *
 * @ignore
 */
define('YAPEAL_INC', YAPEAL_BASE . DS . 'inc' . DS);
// Pull in path constants.
require_once YAPEAL_INC . 'common_paths.php';
require_once YAPEAL_CLASS . 'YapealAutoLoad.php';
YapealAutoLoad::activateAutoLoad();
/**
 * @var mixed Holds path and name of ini configuration file when set.
 */
$iniFile = NULL;
// If function getopts available get any command line parameters.
if (function_exists('getopt')) {
  require_once YAPEAL_INC . 'parseCommandLineOptions.php';
  $shortOpts = array('c:');
  $longOpts = array('config:');
  // Parser command line options first in case user just wanted to see help.
  $options = parseCommandLineOptions($shortOpts, $longOpts);
  $exit = FALSE;
  if (isset($options['help'])) {
    usage();
    $exit = TRUE;
  };
  if (isset($options['version'])) {
    $mess = basename(__FILE__);
    if (YAPEAL_VERSION != 'svnversion') {
      $mess .= ' ' . YAPEAL_VERSION . ' (' . YAPEAL_STABILITY . ') ';
      $mess .= YAPEAL_DATE . PHP_EOL . PHP_EOL;
    } else {
      $rev = str_replace(array('$', 'Rev:'), '', '$Rev$');
      $date = str_replace(array('$', 'Date::'), '', '$Date: 2011-10-16 08:04:49 -0700$');
      $mess .= $rev . '(svn)' . $date . PHP_EOL . PHP_EOL;
    };
    $mess .= 'Copyright (c) 2008-2011, Michael Cummings.' . PHP_EOL;
    $mess .= 'License LGPLv3+: GNU LGPL version 3 or later' . PHP_EOL;
    $mess .= ' <http://www.gnu.org/copyleft/lesser.html>.' . PHP_EOL;
    $mess .= 'See COPYING and COPYING-LESSER for more details.' . PHP_EOL;
    $mess .= 'This program comes with ABSOLUTELY NO WARRANTY.' . PHP_EOL . PHP_EOL;
    fwrite(STDOUT, $mess);
    $exit = TRUE;
  };
  if ($exit == TRUE) {
    exit(0);
  };
};// if function_exists('getopt') ...
// Autoload does not work for functions.
require_once YAPEAL_INC . 'getSettingsFromIniFile.php';
if (!empty($options['config'])) {
  $iniVars = getSettingsFromIniFile($options['config']);
} else {
  $iniVars = getSettingsFromIniFile();
};
/**
 * Define constants and properties from settings in configuration.
 */
YapealErrorHandler::setLoggingSectionProperties($iniVars['Logging']);
YapealErrorHandler::setupCustomErrorAndExceptionSettings();
YapealApiCache::setCacheSectionProperties($iniVars['Cache']);
YapealDBConnection::setDatabaseSectionConstants($iniVars['Database']);
setGeneralSectionConstants($iniVars);
unset($iniVars);
try {
  /**
   * Give ourself a 'soft' limit of 10 minutes to finish.
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
  $sectionList = FilterFileFinder::getStrippedFiles(YAPEAL_CLASS, 'Section');
  if (count($sectionList) == 0) {
    $mess = 'No section classes were found check path setting';
    trigger_error($mess, E_USER_ERROR);
  };
  //$sectionList = array_map('strtolower', $sectionList);
  // Randomize order in which API sections are tried if there is a list.
  if (count($sectionList) > 1) {
    shuffle($sectionList);
  };
  $sql = 'select `section`';
  $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'utilSections`';
  try {
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $result = $con->GetCol($sql);
  }
  catch(ADODB_Exception $e) {
    // Nothing to do here was already report to logs.
  }
  $result = array_map('ucfirst', $result);
  if (count($result) == 0) {
    $mess = 'No sections were found in utilSections check database';
    trigger_error($mess, E_USER_ERROR);
  };
  $sectionList = array_intersect($sectionList, $result);
  // Now take the list of sections and call each in turn.
  foreach ($sectionList as $sec) {
    $class = 'Section' . $sec;
    try {
      $instance = new $class();
      $instance->pullXML();
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
    // Going to sleep for a second to let DB time to flush etc between sections.
    sleep(1);
  };// foreach $section ...
  /* ************************************************************************
   * Final admin stuff
   * ************************************************************************/
  // Release all the ADOdb connections.
  YapealDBConnection::releaseAll();
  // Reset cache intervals
  CachedInterval::resetAll();
}
catch (Exception $e) {
  require_once YAPEAL_CLASS . 'YapealErrorHandler.php';
  $mess = 'Uncaught exception in ' . basename(__FILE__);
  YapealErrorHandler::print_on_command($mess);
  YapealErrorHandler::elog($mess);
  $mess =  'EXCEPTION: ' . $e->getMessage() . PHP_EOL;
  if ($e->getCode()) {
    $mess .= '     Code: ' . $e->getCode() . PHP_EOL;
  };
  $mess .= '     File: ' . $e->getFile() . '(' . $e->getLine() . ')' . PHP_EOL;
  $mess .= '    Trace:' . PHP_EOL;
  $mess .= $e->getTraceAsString() . PHP_EOL;
  $mess .= str_pad(' END TRACE ', 30, '-', STR_PAD_BOTH);
  YapealErrorHandler::print_on_command($mess);
  YapealErrorHandler::elog($mess);
}
exit(0);
/**
 * Function used to preset error handling to some sensible defaults.
 *
 * Any errors that are triggered now are reported to the system default
 * logging location until we're done setting up some of the required vars and
 * we can start our own logging.
 */
function presetErrorHandling() {
  // Set some basic common settings so we know we'll get to see any errors etc.
  error_reporting(E_ALL);
  ini_set('ignore_repeated_errors', 0);
  ini_set('ignore_repeated_source', 0);
  ini_set('html_errors', 0);
  ini_set('display_errors', 1);
  ini_set('error_log', NULL);
  ini_set('log_errors', 0);
  ini_set('track_errors', 0);
}// function presetErrorHandling
/**
 * Function used to set constants from general area (not in a section) of the
 * configuration file.
 *
 * @param array $section A list of settings for this section of configuration.
 */
function setGeneralSectionConstants(array $section) {
  if (!defined('YAPEAL_APPLICATION_AGENT')) {
    $curl = curl_version();
    $user_agent = $section['application_agent'];
    $user_agent .= ' Yapeal/'. YAPEAL_VERSION . ' ' . YAPEAL_STABILITY;
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
}// function setGeneralSectionConstants
/**
 * Function use to show the usage message on command line.
 *
 * @ignore
 */
function usage() {
  $cutLine = 78;
  $ragLine = $cutLine - 5;
  $mess = PHP_EOL . 'Usage: ' . basename(__FILE__);
  $mess .= ' [OPTION]...' . PHP_EOL . PHP_EOL;
  $mess .= 'OPTIONs:' . PHP_EOL;
  $options = array();
  $options['c:'] = array('op' => '  -c, --config=FILE', 'desc' =>
    'Read configuration from FILE. This is an optional setting to allow the use'
    . ' of a custom configuration file. FILE must be in "ini" format. Defaults'
    . ' to <yapeal_base>/config/yapeal.ini.');
  $options['h'] = array('op' => '  -h, --help', 'desc' => 'Show this help.');
  $options['V'] = array('op' => '  -V, --version', 'desc' =>
    'Show version and licensing information.');
  $width = 0;
  foreach ($options as $k => $v) {
    if (strlen($v['op']) > $width) {
      $width = strlen($v['op']);
    };
  };// foreach $options ...
  $width += 4;
  $break = PHP_EOL . str_pad('', $width);
  $descCut = $cutLine - $width;
  $descRag = $descCut - 5;
  foreach ($options as $k => $v) {
    $option = str_pad($v['op'], $width);
    // Make description text ragged right with forced word wrap at full width.
    $desc = wordwrap($v['desc'], $descRag, PHP_EOL);
    $desc = wordwrap($v['desc'], $descCut, PHP_EOL, TRUE);
    $option .= str_replace(PHP_EOL, $break, $desc);
    $mess .= $option . PHP_EOL . PHP_EOL;
  };// foreach $options ...
  fwrite(STDOUT, $mess);
};// function usage
?>
