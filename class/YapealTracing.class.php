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
   * @var string Used to accumulate all the tracing messages before outputting.
   */
  private $trace = '';
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
    if (!empty($this->trace)) {
      elog(PHP_EOL . $this->trace, YAPEAL_TRACE_LOG . '1');
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
      YAPEAL_TRACE_DATABASE => 'DATABASE: ',
      YAPEAL_TRACE_CHAR => 'CHAR: ',
      YAPEAL_TRACE_CORP => 'CORP: ',
      YAPEAL_TRACE_EVE => 'EVE: ',
      YAPEAL_TRACE_MAP => 'MAP: ',
      YAPEAL_TRACE_REQUEST => 'REQUEST: ',
      YAPEAL_TRACE_SERVER => 'SERVER: '
    );
    print_r($sections) . "\n";
    $mess = $sections[$section] . $message;
    $this->trace .= $mess . PHP_EOL;
    print_on_command($mess);
  }
}
?>
