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
/*
 * make a short value for Directory Separators
 */
$ds = DIRECTORY_SEPARATOR;
/*
 * Require the function file
 */
require_once('inc'.$ds.'function.php');
/*
 * Languages.
 */
$knownlang = array('da' => 'Danish','en' => 'English'/* ,'ru' => 'Russian' */);
/*
 * Set Language
 */
if (isset($_POST['lang'])) {
  GetLang($_POST['lang']);
} else {
  $_POST['lang'] = GetBrowserLang();
  GetLang($_POST['lang']);
};
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
require_once('inc'.$ds.'values.php');
/*
 * Require the mainfile file that handle all the basic stuff that need on all pages
 */
require_once('inc'.$ds.'mainfile.php');
/*
 * Check if the browser is IGB (Ingame Browser)
 */
if (checkIGB()) {
  // Generate IGB error site
	OpenSite(NOIGB_HEADLINE);
  echo NOIGB_TEXT
    .'<a href="shellexec:'.$_SERVER['SCRIPT_NAME'].'">'.NOIGB_YAPEAL_SETUP.'</a>' . PHP_EOL;
  CloseSite();
  // if not the Ingame Browser
} else {
  // Welcome Page
  require_once('inc'.$ds.'config'.$ds.'main.php');
};
if (isset($con) && $con->IsConnected()) {
  $con->Close();
}
?>
