<?php
/**
 * Common include file used to setup environment for Yapeal.
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
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
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
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
  $mess = basename(__FILE__) . ' must be included it can not be ran directly';
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  } else {
    fwrite(STDERR, $mess . PHP_EOL);
    fwrite(STDOUT, 'error' . PHP_EOL);
    exit(1);
  };
};
// Set the default timezone to GMT.
date_default_timezone_set('GMT');
// Set some minimal error settings for now.
presetErrorHandling();
if (!defined('DS')) {
  /**
   * Define short name for directory separator which always uses unix '/'.
   * @ignore
   */
  define('DS', '/');
};
if (!defined('YAPEAL_INC')) {
  // Used to over come path issues caused by how script is ran.
  $dir = str_replace('\\', DS, dirname(__FILE__));
  // Get path constants so they can be used.
  require_once $dir . DS . 'common_paths.php';
} else {
  require_once YAPEAL_INC . 'common_paths.php';
};
// Grab revision settings
require_once YAPEAL_BASE . 'revision.php';
// Start auto loader.
require_once YAPEAL_CLASS . 'YapealAutoLoad.php';
require_once YAPEAL_INC . 'getSettingsFromIniFile.php';
// Get array used to set constants.
$iniVars = getSettingsFromIniFile($iniFile);
/**
 * Define constants from settings in configuration.
 */
// Cache settings.
setCacheSectionConstants($iniVars['Cache']);
// Database settings.
setDatabaseSectionConstants($iniVars['Database']);
// General settings.
setGeneralSectionConstants($iniVars);
// Logging settings.
setLoggingSectionConstants($iniVars['Logging']);
setupCustomErrorAndExceptionSettings();
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
 * function used to set constants from [Cache] section of the configuration file.
 *
 * @param array $section A list of settings for this section of configuration.
 */
function setCacheSectionConstants(array $section) {
  if (!defined('YAPEAL_CACHE_OUTPUT')) {
    /**
     * Used to decide how API XML should be cached.
     */
    define('YAPEAL_CACHE_OUTPUT', $section['cache_output']);
  };
}// function setCacheSectionConstants
/**
 * Function used to set constants from [Database] section of the configuration
 * file.
 *
 * @param array $section A list of settings for this section of configuration.
 */
function setDatabaseSectionConstants(array $section) {
  if (!defined('YAPEAL_DSN')) {
    // Put all the pieces of the ADOdb DSN together.
    $dsn = $section['driver'] . $section['username'] . ':';
    $dsn .= $section['password'] . '@' . $section['host'];
    $dsn .= '/' . $section['database'] . $section['suffix'];
    /**
     * Defines the DSN used for ADOdb connection.
     */
    define('YAPEAL_DSN', $dsn);
  };
  if (!defined('YAPEAL_TABLE_PREFIX')) {
    /**
     * Defines the table prefix used for all Yapeal tables.
     */
    define('YAPEAL_TABLE_PREFIX', $section['table_prefix']);
  };
}// function setDatabaseSectionConstants
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
 * Function used to set constants from [Logging] section of the configuration
 * file.
 *
 * @param array $section A list of settings for this section of configuration.
 */
function setLoggingSectionConstants(array $section) {
  if (!defined('YAPEAL_LOG_LEVEL')) {
    define('YAPEAL_LOG_LEVEL', $section['log_level']);
  };
  if (!defined('YAPEAL_ERROR_LOG')) {
    define('YAPEAL_ERROR_LOG', YAPEAL_LOG . $section['error_log']);
  };
  if (!defined('YAPEAL_NOTICE_LOG')) {
    define('YAPEAL_NOTICE_LOG', YAPEAL_LOG . $section['notice_log']);
  };
  if (!defined('YAPEAL_STRICT_LOG')) {
    define('YAPEAL_STRICT_LOG', YAPEAL_LOG . $section['strict_log']);
  };
  if (!defined('YAPEAL_WARNING_LOG')) {
    define('YAPEAL_WARNING_LOG', YAPEAL_LOG . $section['warning_log']);
  };
}// function setLoggingSectionConstants
/**
 * Function used to setup error and exception logging.
 */
function setupCustomErrorAndExceptionSettings() {
  ini_set('error_log', YAPEAL_ERROR_LOG);
  ini_set('log_errors', 1);
  // Start using custom error handler.
  set_error_handler(array('YapealErrorHandler', 'handle'));
  error_reporting(YAPEAL_LOG_LEVEL);
  // Setup exception observers.
  $logObserver = new LoggingExceptionObserver(YAPEAL_WARNING_LOG);
  $printObserver = new PrintingExceptionObserver();
  // Attach (start) our custom printing and logging of exceptions.
  YapealApiException::attach($logObserver);
  YapealApiException::attach($printObserver);
  ADODB_Exception::attach($logObserver);
  ADODB_Exception::attach($printObserver);
}// function setupCustomErrorAndExceptionSettings
?>
