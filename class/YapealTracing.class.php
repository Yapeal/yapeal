<?php
/**
 * Stuff used to trace program flow in Yapeal.
 *
 * LICENSE: This file is part of Yapeal.
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
 * @copyright Copyright (c) 2008, Michael Cummings
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
/* *************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* *************************************************************************/
/* *************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* *************************************************************************/
/**
 * Used to track tracing of execution in Yapeal.
 *
 * @package Yapeal
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
    if (YAPEAL_TRACE && (YAPEAL_TRACE_SECTION & $section) == $section &&
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
}
?>
