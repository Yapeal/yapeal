<?php
/**
 * Contains Yapeal's custom error handler.
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
 * @copyright  Copyright (c) 2008-2010, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Yapeal's custom error handler.
 *
 * @package Yapeal
 * @subpackage Error
 */
class YapealErrorHandler {
  /**
   * @var string Holds text of error message.
   */
  private $message = '';
  /**
   * @var string Holds file name where error message will be written.
   */
  private $filename = '';
  /**
   * @var integer Holds line number where error happenned.
   */
  private $line = 0;
  /**
   * @var array Holds array of all varables.
   */
  private $vars = array();
  /**
   * Constructor
   */
  public function __construct($message, $filename, $linenum, $vars) {
    $this->message = $message;
    $this->filename = $filename;
    $this->line = $linenum;
    $this->vars = $vars;
  }
  /**
   * Method that PHP will call to handle errors.
   *
   * @param integer $errno Holds one of E_USER_ERROR, E_USER_WARNING, etc.
   * @param string $errmsg Text of error message.
   * @param string $filename Name of file where error message will be stored.
   * @param integer $line Line number where error happened.
   * @param array $vars Array contenting all the defined variables and constants.
   */
  public static function handle($errno, $errmsg, $filename, $line, $vars) {
    // obey @ protocol
    if (error_reporting() == 0) {
      return FALSE;
    };
    $self = new self($errmsg, $filename, $line, $vars);
    if (($errno & YAPEAL_LOG_LEVEL) == $errno) {
      switch ($errno) {
        case E_USER_ERROR:
        case E_ERROR:
          return $self->handleError();
        case E_USER_NOTICE:
        case E_NOTICE:
          return $self->handleNotice();
        case E_STRICT:
          return $self->handleStrict();
        case E_USER_WARNING:
        case E_WARNING:
          return $self->handleWarning();
      };// switch $errno ...
    };// if $errno & ...
    return FALSE;
  }
  /**
   * Called to handle error type messages.
   */
  private function handleError() {
    ob_start();
    debug_print_backtrace();
    $backtrace = ob_get_flush();
    $body = PHP_EOL;
    if ($this->line) {
      $body .= <<<EOT
ERROR:
  Message: {$this->message}
     File: {$this->filename}
     Line: {$this->line}
Backtrace:
{$backtrace}
EOT;
    } else {
      $body .= <<<EOT
ERROR:
  Message: {$this->message}
     File: {$this->filename}
Backtrace:
{$backtrace}
EOT;
    };
    self::print_on_command($body);
    self::elog($body, YAPEAL_ERROR_LOG);
    exit(1);
  }
  /**
   * Called to handle notice type messages.
   */
  private function handleNotice() {
    $body = PHP_EOL;
    if ($this->line) {
      $body .= <<<EOT
NOTICE:
  Message: {$this->message}
     File: {$this->filename}
     Line: {$this->line}
EOT;
    } else {
      $body .= <<<EOT
NOTICE:
  Message: {$this->message}
     File: {$this->filename}
EOT;
    };
    self::print_on_command($body);
    return self::elog($body, YAPEAL_NOTICE_LOG);
  }
  /**
   * Called to handle strict type messages.
   */
  private function handleStrict() {
    $body = PHP_EOL;
    if ($this->line) {
      $body .= <<<EOT
STRICT:
  Message: {$this->message}
     File: {$this->filename}
     Line: {$this->line}
EOT;
    } else {
      $body .= <<<EOT
NOTICE:
  Message: {$this->message}
     File: {$this->filename}
EOT;
    };
    self::print_on_command($body);
    return self::elog($body, YAPEAL_STRICT_LOG);
  }
  /**
   * Called to handle warning type messages.
   */
  private function handleWarning() {
    $body = PHP_EOL;
    if ($this->line) {
      $body .= <<<EOT
WARNING:
  Message: {$this->message}
     File: {$this->filename}
     Line: {$this->line}
EOT;
    } else {
      $body .= <<<EOT
WARNING:
  Message: {$this->message}
     File: {$this->filename}
EOT;
    };
    self::print_on_command($body);
    return self::elog($body, YAPEAL_WARNING_LOG);
  }
  /**
   * Used to send message to a log file.
   *
   * @param string $str Message to be sent to log file.
   * @param string $filename File to use for logging message.
   */
  static function elog($str, $filename = YAPEAL_ERROR_LOG) {
    $mess = '[' . gmdate('Y-m-d H:i:s') . substr(microtime(FALSE) , 1, 4) . '] ';
    $mess .= $str . PHP_EOL;
    error_log($mess, 3, $filename);
  }// function elog
  /**
   * Only prints message if in command line mode.
   *
   * @param string $str Message to be printed.
   * @param bool $newline PHP_EOL will be added to end of $str
   * @param bool $timestamp Add Timestamp in front of $str
   *
   * @return void
   */
  static function print_on_command($str, $newline = TRUE, $timestamp = TRUE) {
    if (PHP_SAPI == 'cli') {
      $mess = '';
      if ($timestamp) {
        $mess .= '[' . gmdate('Y-m-d H:i:s') . substr(microtime(FALSE) , 1, 4) . '] ';
      };
      $mess .= $str;
      if ($newline) {
        $mess .= PHP_EOL;
      };
      fwrite(STDERR, $mess);
    }
  }// function print_on_command
}
?>
