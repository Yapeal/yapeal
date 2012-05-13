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
 * @copyright  Copyright (c) 2008-2012, Michael Cummings
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
 */
define('DS', '/');
// Check if the base path for Yapeal has been set in the environment.
$dir = @getenv('YAPEAL_BASE');
if ($dir === FALSE) {
  // Used to overcome path issues caused by how script is ran.
  $dir = str_replace('\\', DS, dirname(__FILE__)) . DS;
};
// Get path constants so they can be used.
require_once $dir . 'inc' . DS . 'common_paths.php';
require_once YAPEAL_BASE . 'revision.php';
require_once YAPEAL_INC . 'parseCommandLineOptions.php';
require_once YAPEAL_INC . 'getSettingsFromIniFile.php';
require_once YAPEAL_INC . 'usage.php';
require_once YAPEAL_INC . 'showVersion.php';
require_once YAPEAL_INC . 'setGeneralSectionConstants.php';
$shortOpts = array('c:', 'l:');
$longOpts = array('config:', 'log:');
// Parser command line options first in case user just wanted to see help.
$options = parseCommandLineOptions($shortOpts, $longOpts);
$exit = FALSE;
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
require_once YAPEAL_CLASS . 'YapealAutoLoad.php';
YapealAutoLoad::activateAutoLoad();
/**
 * Define constants and properties from settings in configuration.
 */
if (!empty($options['log-config'])) {
  YapealErrorHandler::setLoggingSectionProperties($iniVars['Logging'],
    $options['log-config']);
  unset($options['config']);
} else {
  YapealErrorHandler::setLoggingSectionProperties($iniVars['Logging']);
};
YapealErrorHandler::setupCustomErrorAndExceptionSettings();
YapealApiCache::setCacheSectionProperties($iniVars['Cache']);
YapealDBConnection::setDatabaseSectionConstants($iniVars['Database']);
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
  $sectionList = FilterFileFinder::getStrippedFiles(YAPEAL_CLASS, 'Section');
  if (count($sectionList) == 0) {
    $mess = 'No section classes were found check path setting';
    Logger::getLogger('yapeal')->error($mess);
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
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    $result = $con->GetCol($sql);
  }
  catch(ADODB_Exception $e) {
    Logger::getLogger('yapeal')->fatal($e);
  }
  if (count($result) == 0) {
    $mess = 'No sections were found in utilSections check database.';
    Logger::getLogger('yapeal')->error($mess);
    exit(2);
  };
  $result = array_map('ucfirst', $result);
  $sectionList = array_intersect($sectionList, $result);
  // Now take the list of sections and call each in turn.
  foreach ($sectionList as $sec) {
    $class = 'Section' . $sec;
    try {
      $instance = new $class();
      $instance->pullXML();
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->fatal($e);
    }
    // Going to sleep for a tenth of a second to let DB time to flush etc
    // between sections.
    usleep(100000);
  };// foreach $section ...
  /* ************************************************************************
   * Final admin stuff
   * ************************************************************************/
  // Reset cache intervals
  CachedInterval::resetAll();
  // Release all the ADOdb connections.
  YapealDBConnection::releaseAll();
}
catch (Exception $e) {
  $mess = 'Uncaught exception in ' . basename(__FILE__);
  Logger::getLogger('yapeal')->fatal($mess);
  Logger::getLogger('yapeal')->fatal($e);
  exit(1);
}
exit(0);

