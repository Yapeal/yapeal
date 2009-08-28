<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup
 *
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know as Yapeal.
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
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 * @subpackage Setup
 */
/**
 * @internal Allow viewing of the source code in web browser.
 */
if (isset($_REQUEST['viewSource'])) {
  highlight_file(__FILE__);
  exit();
};
/**
 * @internal Only let this code be ran directly.
 */
if (basename(__FILE__) != basename($_SERVER['PHP_SELF'])) {
  exit();
};
// Used to over come path issues caused by how script is ran on server.
$dir = realpath(dirname(__FILE__));
chdir($dir);
// Define shortened name for DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);
try {
/*
 * Require the function file
 */
require_once('inc' . DS . 'function.php');
/*
 * Require the value file
 *
 * VALUES IN THE FILE:
 * 1. Version of the database that we are using.
 * 2. PerAPI for the Character
 * 3. PerAPI for the Corporation
 * 4. Define where error log files are located
 * 5. Define error log level
 */
require_once('inc' . DS . 'values.php');
/*
 * Require the mainfile file that handle all the basic stuff that need on all pages
 */
require_once('inc' . DS . 'mainfile.php');
/*
 * Check if the browser is IGB (Ingame Browser)
 */
if (checkIGB()) {
  // Generate IGB error site
	OpenSite('No IGB Support');
  echo 'This setup can only be run in a normal browser and not the IGB.<br />' . PHP_EOL .
    'Press the link and you will be popped out of EVE and this setup will re-openned in a normal browser.<br />' . PHP_EOL .
    '<a href="shellexec:' . $_SERVER['SCRIPT_NAME'] . '">' .
    'Yapeal Setup</a>' . PHP_EOL;
  CloseSite();
  // if not the Ingame Browser
} else {
  // Welcome Page
  require_once('inc' . DS . 'config' . DS . 'main.php');
};
if (isset($con) && $con->IsConnected()) {
  $con->Close();
}
}
catch (Exception $e) {
  elog('Uncaught exception in ' . basename(__FILE__), YAPEAL_ERROR_LOG);
  $message = <<<MESS
EXCEPTION:
     Code: {$e->getCode() }
  Message: {$e->getMessage() }
     File: {$e->getFile() }
     Line: {$e->getLine() }
Backtrace:
{$e->getTraceAsString() }
\t--- END TRACE ---
MESS;
  elog($message, YAPEAL_ERROR_LOG);
}
?>
