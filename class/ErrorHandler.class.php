<?php
/**
 * Used in Yapeal for custom error handling.
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
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/* *************************************************************************
* THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
* *************************************************************************/
/**
 * Find path for includes
 */
// Move up and over to 'inc' directory to read common_backend.inc
$path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
$path.= '..' . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR . 'common_backend.inc';
require_once realpath($path);
/* *************************************************************************
* NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
* SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
* *************************************************************************/
class ErrorHandler {
  public $message = '';
  public $filename = '';
  public $line = 0;
  public $vars = array();
  public function __construct($message, $filename, $linenum, $vars) {
    $this->message = $message;
    $this->filename = $filename;
    $this->line = $linenum;
    $this->vars = $vars;
  }
  public static function handle($errno, $errmsg, $filename, $line, $vars) {
    $self = new self($errmsg, $filename, $line, $vars);
    switch ($errno) {
      case E_USER_ERROR:
        return $self->handleError();
      case E_USER_WARNING:
      case E_WARNING:
        return $self->handleWarning();
      case E_USER_NOTICE:
      case E_NOTICE:
        return $self->handleNotice();
      default:
        return FALSE;
    };
  }
  public function handleError() {
    ob_start();
    debug_print_backtrace();
    $backtrace = ob_get_flush();
    $body = <<<EOT
ERROR:
  Message: {$this->message}
     File: {$this->filename}
     Line: {$this->line}
Backtrace: {$backtrace}
EOT;
    elog($body, YAPEAL_ERROR_LOG);
    exit(1);
  }
  public function handleWarning() {
    if ($this->line) {
      $body = <<<EOT
WARNING:
Message: {$this->message}
   File: {$this->filename}
   Line: {$this->line}
EOT;
      
    } else {
      $body = <<<EOT
WARNING:
Message: {$this->message}
   File: {$this->filename}
EOT;
      
    };
    return elog($body, YAPEAL_WARNING_LOG);
  }
  public function handleNotice() {
    if ($this->line) {
      $body = <<<EOT
NOTICE:
Message: {$this->message}
   File: {$this->filename}
   Line: {$this->line}
EOT;
      
    } else {
      $body = <<<EOT
NOTICE:
Message: {$this->message}
   File: {$this->filename}
EOT;
      
    };
    return elog($body, YAPEAL_NOTICE_LOG);
  }
}
set_error_handler(array(
  'ErrorHandler',
  'handle'
));
?>