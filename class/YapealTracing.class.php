<?php
/**
 * Stuff used to trace program flow in Yapeal.
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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
$sectionFile = basename(__FILE__);
if ($sectionFile == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Used to track tracing of execution in Yapeal.
 *
 * @package Yapeal
 * @subpackage Tracing
 */
class YapealTracing {
  /**
   * @var string Used to accumulate all the tracing messages for file.
   */
  private $fileTrace = '';
  /**
   * @var string Used to accumulate all the tracing messages for database.
   */
  private $dbTrace = '';
  /**
   * Constructor
   */
  public function __construct() {
    require_once YAPEAL_INC . 'elog.inc';
    if (defined('YAPEAL_DEBUG')) {
      $this->fileTrace = str_pad(' Trace log ', 75, '-', STR_PAD_BOTH) . PHP_EOL;
    };
  }
  /**
   * destructor outputs the trace to log file.
   */
  public function __destruct() {
    if (!empty($this->fileTrace)) {
      elog(PHP_EOL . $this->fileTrace, YAPEAL_TRACE_LOG);
    };
    if (!empty($this->dbTrace)) {
      // This is where the code to store trace into database will go.
    };
  }
  /**
   * Function used to check for tracing and output a message if enabled.
   *
   * @param integer $section Which section we're check about tracing for.
   * @param integer $level What trace level we should output message at.
   *
   * @return bool TRUE if this type and level of tracing is on.
   */
  function activeTrace($section, $level) {
    if (YAPEAL_TRACE_ACTIVE && (YAPEAL_TRACE_SECTION & $section) == $section &&
      YAPEAL_TRACE_LEVEL >= $level) {
      return TRUE;
    }; // if YAPEAL_TRACE&&...
    return FALSE;
  }
  /**
   * Function used to check for tracing and output a message if enabled.
   *
   * @param integer $section Which section we're check about tracing for.
   * @param string $message Message to output if tracing is on for this section.
   */
  function logTrace($section, $message) {
    $sections = array(
      YAPEAL_TRACE_ACCOUNT => 'ACCOUNT: ',
      YAPEAL_TRACE_CHAR => 'CHAR: ',
      YAPEAL_TRACE_CORP => 'CORP: ',
      YAPEAL_TRACE_EVE => 'EVE: ',
      YAPEAL_TRACE_MAP => 'MAP: ',
      YAPEAL_TRACE_SERVER => 'SERVER: ',
      YAPEAL_TRACE_API => 'API: ',
      YAPEAL_TRACE_CACHE => 'CACHE: ',
      YAPEAL_TRACE_CURL => 'CURL: ',
      YAPEAL_TRACE_DATABASE => 'DATABASE: ',
      YAPEAL_TRACE_REQUEST => 'REQUEST: '
    );
    $mess = $sections[$section] . $message .PHP_EOL;
    if (YAPEAL_TRACE_OUTPUT == 'file' || YAPEAL_TRACE_OUTPUT == 'both') {
      $this->fileTrace .= $mess;
    };
    // Chance of causing an infinite loop if we tried to put database trace into
    // the database.
    if ((YAPEAL_TRACE_OUTPUT == 'database' || YAPEAL_TRACE_OUTPUT == 'both') &&
      $section != YAPEAL_TRACE_DATABASE) {
      $this->dbTrace .= $mess;
    };
  }
  /**
   * flushes the trace to log file.
   */
  public function flushTrace() {
    if (!empty($this->fileTrace)) {
      elog(PHP_EOL . $this->fileTrace, YAPEAL_TRACE_LOG);
      $this->fileTrace = '';
    };
    if (!empty($this->dbTrace)) {
      // This is where the code to store trace into database will go.
      $this->dbTrace = '';
    };
  }
}
// Define some constants used with tracing.
// Lower 16 bits used for API.
/**
 * Used to turn on tracing for all areas.
 */
define('YAPEAL_TRACE_ALL', 2147483647);
/**
 * Use when you want all tracing off but trace_active is still on.
 */
define('YAPEAL_TRACE_NONE', 0);
/**
 * Use to turn on account section tracing.
 */
define('YAPEAL_TRACE_ACCOUNT', 1);
/**
 * Use to turn on char section tracing.
 */
define('YAPEAL_TRACE_CHAR', 2);
/**
 * Use to turn on corp section tracing.
 */
define('YAPEAL_TRACE_CORP', 4);
/**
 * Use to turn on eve section tracing.
 */
define('YAPEAL_TRACE_EVE', 8);
/**
 * Use to turn on map section tracing.
 */
define('YAPEAL_TRACE_MAP', 16);
/**
 * Use to turn on server section tracing.
 */
define('YAPEAL_TRACE_SERVER', 32);
// Upper bits used for Yapeal.
/**
 * Use to turn on api tracing.
 */
define('YAPEAL_TRACE_API', 65536);
/**
 * Use to turn on cache tracing.
 */
define('YAPEAL_TRACE_CACHE', 131072);
/**
 * Use to turn on curl tracing.
 */
define('YAPEAL_TRACE_CURL', 262144);
/**
 * Use to turn on database tracing.
 */
define('YAPEAL_TRACE_DATABASE', 524288);
/**
 * Use to turn on files tracing.
 */
define('YAPEAL_TRACE_FILES', 1048576);
/**
 * Use to turn on request tracing.
 */
define('YAPEAL_TRACE_REQUEST', 2097152);
?>
