<?php
/**
 * Contents Custom Yapeal API exception class.
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
require_once YAPEAL_CLASS . 'IYapealSubject.php';
if (!defined('ADODB_ERROR_HANDLER_TYPE')) define('ADODB_ERROR_HANDLER_TYPE', E_USER_ERROR);
define('ADODB_ERROR_HANDLER', 'adodb_throw');
/**
 * Base class used for all Database type exception.
 *
 * @package Yapeal
 * @subpackage ADOdb
 * @uses YapealSubject
 */
class ADODB_Exception extends Exception implements YapealSubject {
  /**
   * @var array Hold the references to our observers.
   */
  protected static $observers = array();
  /**
   * @var string the RDBMS you are connecting to
   */
  var $dbms;
  /**
   * @var string the name of the calling function (in uppercase)
   */
  var $fn;
  /**
   * @var string Holds the SQL? Is this even used?
   */
  var $sql = '';
  /**
   * @var mixed Holds? Is this used?
   */
  var $params = '';
  /**
   * @var string name of the host for this connection
   */
  var $host = '';
  /**
   * @var string name of the database for this connection
   */
  var $database = '';
  /**
   * Constructor
   *
   * @param string $dbms            the RDBMS you are connecting to
   * @param string $fn              the name of the calling function (in uppercase)
   * @param integer $errno          the native error number from the database
   * @param string $errmsg          the native error msg from the database
   * @param mixed $p1               $fn specific parameter - see below
   * @param mixed $p2               $fn specific parameter - see below
   * @param object $thisConnection  additional connection information
   */
  public function __construct($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection) {
		switch($fn) {
      case 'EXECUTE':
        $this->sql = $p1;
        $this->params = $p2;
        $s = "$dbms error: [$errno: $errmsg] in $fn(\"$p1\")\n";
        break;
      case 'PCONNECT':
      case 'CONNECT':
        $user = $thisConnection->user;
        $s = "$dbms error: [$errno: $errmsg] in $fn($p1, '$user', '****', $p2)\n";
        break;
      default:
        $s = "$dbms error: [$errno: $errmsg] in $fn($p1, $p2)\n";
        break;
		};
		$this->dbms = $dbms;
		if ($thisConnection) {
			$this->host = $thisConnection->host;
			$this->database = $thisConnection->database;
		};
		$this->fn = $fn;
		$this->msg = $errmsg;
		if (!is_numeric($errno)) {
      $errno = -1;
    };
		parent::__construct($s,$errno);
    $this->notify();
  }
  /**
   * Used by observers to register so they can be notified.
   *
   * @param YapealObserver $observer The observer being added.
   */
  public static function attach(YapealObserver $observer) {
    $idx = spl_object_hash($observer);
    self::$observers[$idx] = $observer;
  }
  /**
   * Used by observers to unregister from being notified.
   *
   * @param YapealObserver $observer The observer being removed.
   */
  public static function detach(YapealObserver $observer) {
    $idx = spl_object_hash($observer);
    if (array_key_exists($idx, self::$observers)) {
      unset(self::$observers[$idx]);
    };
  }
  /**
   * Used to notify all the observers.
   */
  public function notify() {
    foreach (self::$observers as $observer) {
      $observer->YapealUpdate($this);
    };
  }
}
/**
 * Default Error Handler. This will be called with the following params
 *
 * @param string $dbms            the RDBMS you are connecting to
 * @param string $fn              the name of the calling function (in uppercase)
 * @param integer $errno          the native error number from the database
 * @param string $errmsg          the native error msg from the database
 * @param mixed $p1               $fn specific parameter - see below
 * @param mixed $p2               $fn specific parameter - see below
 * @param object $thisConnection  additional connection information
 */
function adodb_throw($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection) {
  global $ADODB_EXCEPTION;
  if (error_reporting() == 0) {
    return;
  };// obey @ protocol
  if (is_string($ADODB_EXCEPTION)) {
    $errfn = $ADODB_EXCEPTION;
  } else {
    $errfn = 'ADODB_EXCEPTION';
  };
  throw new $errfn($dbms, $fn, $errno, $errmsg, $p1, $p2, $thisConnection);
}
?>
