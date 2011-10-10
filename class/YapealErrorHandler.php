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
/**
 * Yapeal's custom error handler.
 *
 * @package Yapeal
 * @subpackage Error
 */
class YapealErrorHandler {
  /**
   * @var string Holds file name where error happened.
   */
  private $filename = '';
  /**
   * @var bool Used to decide if errors are to be logged to an array.
   */
  private static $keep = FALSE;
  /**
   * @var integer Holds line number where error happened.
   */
  private $line = 0;
  /**
   * @var array Used to hold list of errors if $keepLog is TRUE.
   */
  private static $list = array();
  /**
   * @var string Holds text of error message.
   */
  private $message = '';
  /**
   * @var array Holds array of all varables.
   */
  private $vars = array();
  /**
   * Constructor
   *
   * @param string $message Text of error message.
   * @param string $filename Name of file where error happened.
   * @param integer $linenum Line number where error happened.
   * @param array $vars Array containing all the defined variables and constants.
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
   * @param string $filename Name of file where error happened.
   * @param integer $line Line number where error happened.
   * @param array $vars Array containing all the defined variables and constants.
   */
  public static function handle($errno, $errmsg, $filename, $line, $vars) {
    // obey @ protocol
    if (error_reporting() == 0) {
      return FALSE;
    };
    // Let PHP handle any errors Yapeal is not set to handle.
    if (($errno & YAPEAL_LOG_LEVEL) != $errno) {
      return FALSE;
    };
    if (self::$keep === TRUE) {
      self::$list[] = array('type' => $errno, 'message' => $errmsg,
        'filename' => $filename, 'line' => $line,
        'time' => gmdate('Y-m-d H:i:s') . substr(microtime(FALSE), 1, 4));
    };// if self::$keep === TRUE ...
    $self = new self($errmsg, $filename, $line, $vars);
    switch ($errno) {
      case E_USER_ERROR:
      case E_ERROR:
        $self->handleError();
        break;
      case E_USER_NOTICE:
      case E_NOTICE:
        $self->handleNotice();
        break;
      case E_STRICT:
        $self->handleStrict();
        break;
      case E_USER_WARNING:
      case E_WARNING:
        $self->handleWarning();
        break;
    };// switch $errno ...
    return TRUE;
  }
  /**
   * Called to handle error type messages.
   */
  private function handleError() {
    $body =  '  ERROR: ' . $this->message . PHP_EOL;
    $body .= '   File: ' . $this->filename;
    if ($this->line) {
      $body .= '(' . $this->line . ')';
    };// if $this->line ...
    $body .= PHP_EOL;
    $body .= '  Trace:' . PHP_EOL;
    ob_start();
    debug_print_backtrace();
    $backtrace = ob_get_flush();
    $body .= $backtrace . PHP_EOL;
    $body .= str_pad(' END TRACE ', 30, '-', STR_PAD_BOTH);
    self::print_on_command($body);
    self::elog($body, YAPEAL_ERROR_LOG);
    exit(1);
  }// function handleError
  /**
   * Called to handle notice type messages.
   */
  private function handleNotice() {
    $body =  ' NOTICE: ' . $this->message . PHP_EOL;
    $body .= '   File: ' . $this->filename;
    if ($this->line) {
      $body .= '(' . $this->line . ')';
    };// if $this->line ...
    self::print_on_command($body);
    return self::elog($body, YAPEAL_NOTICE_LOG);
  }// function handleNotice
  /**
   * Called to handle strict type messages.
   */
  private function handleStrict() {
    $body =  ' STRICT: ' . $this->message . PHP_EOL;
    $body .= '   File: ' . $this->filename;
    if ($this->line) {
      $body .= '(' . $this->line . ')';
    };// if $this->line ...
    self::print_on_command($body);
    return self::elog($body, YAPEAL_STRICT_LOG);
  }// function handleStrict
  /**
   * Called to handle warning type messages.
   */
  private function handleWarning() {
    $body =  'WARNING: ' . $this->message . PHP_EOL;
    $body .= '   File: ' . $this->filename;
    if ($this->line) {
      $body .= '(' . $this->line . ')';
    };// if $this->line ...
    self::print_on_command($body);
    return self::elog($body, YAPEAL_WARNING_LOG);
  }// function handleWarning
  /**
   * Used to send message to a log file.
   *
   * @param string $str Message to be sent to log file.
   * @param string $filename File to use for logging message.
   */
  static function elog($str, $filename = YAPEAL_ERROR_LOG) {
    $mess = '[' . gmdate('Y-m-d H:i:s') . substr(microtime(FALSE), 1, 4) . '] ';
    $mess .= PHP_EOL . $str . PHP_EOL;
    error_log($mess, 3, $filename);
  }// function elog
  /**
   * Only prints message if in command line mode.
   *
   * @param string $str Message to be printed.
   */
  static function print_on_command($str) {
    if (PHP_SAPI !== 'cli') {
      return;
    };
    $mess = '[' . gmdate('Y-m-d H:i:s') . substr(microtime(FALSE), 1, 4) . '] ';
    $mess .= PHP_EOL . $str . PHP_EOL;
    fwrite(STDERR, $mess);
  }// function print_on_command
  /**
   * Retrieves error list.
   *
   * @param bool $clear Will clear error list if TRUE.
   *
   * @return array Returns a list of errors.
   */
  public static function getList($clear = FALSE) {
    $list = self::$list;
    if ($clear === TRUE) {
      self::$list = array();
    };
    return $list;
  }// function getList
  /**
   * Used to turn on/off logging errors to an internal list that can be retrieved.
   *
   * @param bool $keep When TRUE all errors will be keep in an internal list.
   */
  public static function setKeep($keep = FALSE) {
    if (is_bool($keep)) {
      self::$keep = $keep;
    };
  }// function setKeep
}
?>
