<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer
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
 */

require_once('inc/function.php');

////////////////////////////////
// Check if Browser is EVE IGB
////////////////////////////////

// Parse agent string by spliting on the '/'
$parts = explode("/", @$_SERVER['HTTP_USER_AGENT']);
// Test for Eve Minibrowser also test against broken Shiva IGB Agent
if (($parts[0] == "EVE-minibrowser") or ($parts[0] == "Python-urllib")) {
  // IGB always sends this set to yes, or no,
  // so if it is missing, we smell something.
  if (!isset($_SERVER['HTTP_EVE_TRUSTED'])) {
    $IGB = false;
  };
  // return true at this point, User Agent matches,
  // and no phishy headers
  $IGB = true;
} else {
  // User Agent, does not match required.
  $IGB = false;
};
// If Ingame Browser
if ($IGB) {
  echo '<center>This installer can only be runned in a normal browser and not the IGB.<br />' . PHP_EOL
      .'Press the link, you will be popped out EVE and this installer will re-openned in a normal browser.<br />' . PHP_EOL
      .'<a href="shellexec:http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'">Yapeal Installer</a></center>' . PHP_EOL;
  // If not the Ingame Browser
} else {
  // Check if there is an existing yapeal.ini file.
  // If so, then tell open Yapeal config updater.
  if (file_exists('../config/yapeal.ini')) {
    // Config Updater
    require_once('inc/install_update.php');
  } elseif (isset($_GET['install']) && $_GET['install'] == "go") {
    // Setup Progress
    require_once('inc/install_go.php');
  } elseif (isset($_GET['install']) && $_GET['install'] == "step3") {
    // Config Page
    require_once('inc/install_char_select.php');
  } elseif (isset($_GET['install']) && $_GET['install'] == "step2") {
    // Config Page
    require_once('inc/install_setup.php');
  } elseif (isset($_GET['install']) && $_GET['install'] == "step1") {
    // Requirements check page
    require_once('inc/install_req.php');
  } else {
    // Welcome Page
    require_once('inc/install_wellcome.php');
  };
};
?>
