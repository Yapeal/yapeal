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
OpenSite(INSTALLER_WELCOME);
echo INSTALLER_WELCOME_TEXT
    .'<form action="' . $_SERVER['SCRIPT_NAME'] . '?lang=' . $_GET['lang'] . '&amp;install=step1" method="post">' . PHP_EOL
    .'<select name="c_action">' . PHP_EOL
    .'<option value="0">'.ED_DO_NOTHING.'</option>' . PHP_EOL
    .'<option value="1">'.ED_CLEAN_SETUP.'</option>' . PHP_EOL
    .'<option value="2">'.UPDATE.'</option>' . PHP_EOL
    .'</select>' . PHP_EOL
    .'<input type="submit" value="'.NEXT.'" />' . PHP_EOL
    .'</form>' . PHP_EOL;
CloseSite();
?>
