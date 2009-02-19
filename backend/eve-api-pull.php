#!/usr/bin/php
<?php
/**
 * This is just a backward compatibly wrapper to keep from breaking everyone's stuff.
 *
 * Everyone needs to update thier stuff to use the new yapeal.php file in the
 * main directory as this file will be deprecated very soon.
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
 * @deprecated This file is being replaced by moving the code to yapeal.php as
 * of revision 561. Please update your stuff to use the new location as this file
 * will be dropped in the near future once all the documention can be updated.
 */
/**
 * @internal Only let this code be ran directly.
 */
if (basename(__FILE__) != basename($_SERVER['PHP_SELF'])) {
  exit();
};
$dir = realpath(dirname(__FILE__));
// Move up to 'root' directory to read yapeal.php
$ds = DIRECTORY_SEPARATOR;
$path = $dir . $ds . '..' . $ds . 'yapeal.php';
require_once realpath($path);
?>
