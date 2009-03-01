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
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
}
if (isset($_GET['install']) && $_GET['install'] == "go") {
  // Setup Progress
  include_once('inc'.$DS.'install'.$DS.'go.php');
} elseif (isset($_GET['install']) && $_GET['install'] == "step3") {
  // Config Page
  include_once('inc'.$DS.'install'.$DS.'char_select.php');
} elseif (isset($_GET['install']) && $_GET['install'] == "step2") {
  // Config Page
  include_once('inc'.$DS.'install'.$DS.'config.php');
} elseif (isset($_GET['install']) && $_GET['install'] == "step1") {
  // Requirements check page
  include_once('inc'.$DS.'install'.$DS.'req.php');
} elseif (isset($_GET['install']) && $_GET['install'] == "welcome") {
  // Welcome Page
  include_once('inc'.$DS.'install'.$DS.'welcome.php');
} else {
  header("Location: ".$_SERVER['SCRIPT_NAME']."?lang=".GetBrowserLang()."&install=welcome");
};
?>
