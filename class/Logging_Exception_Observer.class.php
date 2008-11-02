<?php
/**
 * Includes Logging_Exception_Observer class.
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
if (basename( __FILE__ )==basename($_SERVER['PHP_SELF'])) {
  exit();
};

/* *************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * *************************************************************************/

/**
 * Find path for includes
 */
// Move up and over to 'inc' directory to read common_backend.inc
$path=realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR;
$path.='..'.DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'common_backend.inc';
require_once realpath($path);

/* *************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * *************************************************************************/

require_once YAPEAL_CLASS.'Exception_Observer.class.php';
require_once YAPEAL_CLASS.'Observable_Exception.class.php';
require_once YAPEAL_INC.'elog.inc';

/**
 * Logs any exceptions its observing to a log file.
 */
class Logging_Exception_Observer implements Exception_Observer {
  protected $_filename=YAPEAL_ERROR_LOG;
  public function __construct($filename=null) {
    if ((null!==$filename)&&is_string($filename)) {
      $this->_filename=$filename;
    };
  }
  public function update(Observable_Exception $e) {
    $message=<<<MESS
EXCEPTION:
     Code: {$e->getCode()}
  Message: {$e->getMessage()}
     File: {$e->getFile()}
     Line: {$e->getLine()}
Backtrace:
{$e->getTraceAsString()}
\t--- END TRACE ---PHP_EOL
MESS;
    elog($message,$this->_filename);
  }
}
?>