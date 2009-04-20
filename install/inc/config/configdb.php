<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Setup.
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
/*
 * Handle what values to use if $ini is true or false
 */
if (isset($ini)) {
  $db_host =    $ini['Database']['host'];
  $db_user =    $ini['Database']['username'];
  $db_pass =    $ini['Database']['password'];
  $db_name =    $ini['Database']['database'];
  $db_prefix =  $ini['Database']['table_prefix'];
  $db_info =  '  <tr>' . PHP_EOL
               .'    <td colspan="2" style="text-align: center;" class="warning">'.DB_WARNING_CHANGE_DB_NAME_PREFIX_DES.'</td>' . PHP_EOL
               .'  </tr>' . PHP_EOL;
  $passnologin = '';
  $passchange = ' '.ONLY_CHANGE_PASS_IF;
} else {
  $db_host =    'localhost';
  $db_user =    'username';
  $db_pass =    '';
  $db_name =    'yapeal';
  $db_prefix =  '';
  $db_info =  '';
  $passnologin = SETUP_PASS_DES_BLANK;
  $passchange = '';
}; // if isset $ini
/**
 * Create site
 */
OpenSite(DB_SETTING);
if (isset($ini)) {
  configMenu();
}; // if isset $ini
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=dodb" method="post">' . PHP_EOL
    .'<!-- Database Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.DB_SETTING.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.HOST.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Host]" value="'.$db_host.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.USERNAME.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Username]" value="'.$db_user.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PASSWORD.':</td>' . PHP_EOL
    .'    <td><input type="password" name="config[DB_Password]" value="'.$db_pass.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.DATABASE.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Database]" value="'.$db_name.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PREFIX.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Prefix]" value="'.$db_prefix.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$db_info
    .'</table>' . PHP_EOL;
if (isset($ini)) {
  echo '<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'<input type="submit" value="'.UPDATE.'" />' . PHP_EOL;
} else {
  echo inputHiddenPost()
      .'<input type="submit" value="'.NEXT.'" />' . PHP_EOL;
}; // if isset $ini
echo '</form>' . PHP_EOL;
CloseSite();
?>
