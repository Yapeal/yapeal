<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Wellcome page.
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

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
}
OpenSite('Welcome');
echo '<h3>Welcome to Yapeal Installer.</h3><br />' . PHP_EOL
    .'This installer will setup Yapeal EVE API Library to run on your site.<br />' . PHP_EOL
    .'<br />' . PHP_EOL
    .'The setup will check if your web host has the requirements to support this library.<br />' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<form action="' . $_SERVER['SCRIPT_NAME'] . '?install=step1" method="post">' . PHP_EOL
    .'<input type="submit" value="Next" />' . PHP_EOL
    .'</form>' . PHP_EOL;
CloseSite();
?>
