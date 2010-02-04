<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal Setup - Config page.
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
 * @author     Claus Pedersen <satissis@gmail.com>
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2010, Claus Pedersen, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
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
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
}
/**
 * Check login info
 */
require_once('inc'.DS.'config'.DS.'login.php');
/*
 * Check for updates if yapeal.ini is created
 */
if (isset($ini)) {
  foreach ($schemas as $schemaName=>$schemaVersion) {
    if (conRev($conf[$schemaName.'Version']) < $schemaVersion) {
      $newupdate = true;
    }; // if conRev($conf[$schemasName.'Version']) < $schemasVersion
  }; // foreach $schemas
  if ((isset($newupdate) || $setupversion > conRev($ini['version'])) && !isset($_POST['updatedb'])) {
    OpenSite('New Update');
    echo '<h2>New Update</h2>' . PHP_EOL
      . 'There is a new update for your database.<hr />' . PHP_EOL
      . '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=dodb" method="post">' . PHP_EOL
      . '  <input type="hidden" name="updatedb" value="true" />' . PHP_EOL
      . '  <input type="submit" value="Update" />' . PHP_EOL
      . '</form>' . PHP_EOL;
    CloseSite();
    exit;
  }; // if isset $newupdate
}; // if isset $ini
/**
 * Link handler
 **
 * Create/Update yapeal.ini
 */
if (isset($_GET['funk']) && $_GET['funk'] == "doini") {
  require_once('inc'.DS.'config'.DS.'goini.php');
/*
 * Edit yapeal.ini values
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "configini") {
  require_once('inc'.DS.'config'.DS.'configini.php');
/*
 * Do test character stuff
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "doapi") {
  require_once('inc'.DS.'config'.DS.'goapi.php');
/*
 * Select a test character
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "csel") {
  require_once('inc'.DS.'config'.DS.'char_select.php');
/*
 * Input API info for test character
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "configapi") {
  require_once('inc'.DS.'config'.DS.'configapi.php');
/*
 * Create/Update database
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "dodb") {
  require_once('inc'.DS.'config'.DS.'godb.php');
/*
 * Input DB info
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "configdb") {
  require_once('inc'.DS.'config'.DS.'configdb.php');
/*
 * Show requirement page
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "req") {
  require_once('inc'.DS.'config'.DS.'req.php');
/*
 * Show welcome page
 */
} elseif (isset($_GET['funk']) && $_GET['funk'] == "welcome") {
  require_once('inc'.DS.'config'.DS.'welcome.php');
/*
 * Find what page to start with
 */
} else {
  if (isset($ini)) {
    header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?funk=configini');
  } else {
    header('Location: ' . $_SERVER['SCRIPT_NAME'] . '?funk=welcome');
  }; // if isset $ini
};
?>
