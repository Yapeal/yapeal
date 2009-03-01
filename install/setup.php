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
 */
$DS = DIRECTORY_SEPARATOR;
require_once('inc'.$DS.'function.php');

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
  if (isset($_GET['lang'])) {
    GetLang($_GET['lang']);
    OpenSite(NOIGB_HEADLINE,false,false);
    echo NOIGB_TEXT
      .'<a href="shellexec:'.$_SERVER['SCRIPT_NAME'].'">'.NOIGB_YAPEAL_SETUP.'</a>' . PHP_EOL;
    CloseSite();
  } else {
    header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=".GetBrowserLang());
  };
  // If not the Ingame Browser
} else {
  // Check if there is an existing yapeal.ini file.
  // If so, then tell open Yapeal config updater.
  if (file_exists('..'.$DS.'config'.$DS.'yapeal.ini')) {
    GetLang($_GET['lang']);
    // Config Updater
    require_once('inc'.$DS.'update'.$DS.'main.php');
  } else{
    GetLang($_GET['lang']);
    // Welcome Page
    require_once('inc'.$DS.'install'.$DS.'main.php');
  };
};
?>
