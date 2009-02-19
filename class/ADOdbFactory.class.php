<?php
/**
 * Contents ADOdbFactory class.
 *
 * The original code for this was lifted from the original adodb-exception.inc.php.
 * @version V5.07 18 Dec 2008   (c) 2000-2008 John Lim (jlim#natsoft.com). All rights reserved.
 * I added the observer pattern code to it to make it integrate better with Yapeal's
 * logging and exception handling.
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
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * A factory to manage ADOdb connections to databases.
 *
 * @package Yapeal
 * @subpackage ADOdb
 */
class ADOdbFactory {
  /**
   * @var object
   */
  protected static $instance;
  /**
   * @var array
   */
  private $connections;
  /**
   * Only way to make instance is through {@link getInstance() getInstance()}.
   */
  private function __construct() {
    $this->connections = array();
  }
  /**
   * No backdoor through cloning either.
   */
  private function __clone() {}
  /**
   * Used to get an instance of the class.
   *
   * @return ADOdbFactory Returns an instance of the class.
   */
  public static function getInstance() {
    if (!(self::$instance instanceof self)) {
      self::$instance = new self();
    };
    return self::$instance;
  }
  /**
   * Use to get a ADOdb connection object.
   *
   * This method will create a new ADOdb connection for each DSN it is passed and
   * return the same connection for any other requests for the same DSN. It was
   * developed to get around some problems with how ADOdb handles connections
   * that wasn't compatable with what I needed.
   *
   * @param string $dsn An ADOdb compatible connection string.
   * @param string $section Which API section connection is for.
   *
   * @return object Returns an ADOdb connection.
   *
   * @throws InvalidArgumentException for bad missing or if $dsn isn't a string.
   * @throws ADODB_Exception Passes through any problem with actual connection.
   */
  public function factory($dsn, $section='') {
    global $tracing;
    if (empty($dsn) || !is_string($dsn) || !is_string($section)) {
      throw new InvalidArgumentException('Bad value passed for $dsn');
    };
    $hash = sha1($dsn . $section);
    if (!array_key_exists($hash,$this->connections)) {
      require_once YAPEAL_CLASS . 'ADODB_Exception.class.php';
      require_once YAPEAL_ADODB . 'adodb.inc.php';
      $mess = 'Before NewADOConnection in ' . basename(__FILE__);
      $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 0) &&
      $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
      $con = NewADOConnection($dsn);
      $con->Execute('set names utf8');
      $this->connections[$hash] = $con;
    };
    return $this->connections[$hash];
  }
}
?>
