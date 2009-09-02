<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal installer - Setup.
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
 * @copyright  Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
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
    . '    <td colspan="2" style="text-align: center;" class="warning">WARNING:<br />' . PHP_EOL
    . 'If you change the Host, Database or Prefix,' . PHP_EOL
    . 'your old tables and data is still at your old location.<br />' . PHP_EOL
    . 'You will need to move the data and drop the tables manual'.'</td>' . PHP_EOL
    . '  </tr>' . PHP_EOL;
  $passnologin = '';
  $passchange = ' Change only if you need a new one';
} else {
  $db_host =    'localhost';
  $db_user =    'username';
  $db_pass =    '';
  $db_name =    'yapeal';
  $db_prefix =  '';
  $db_info =  '';
  $passnologin = '<br />' . PHP_EOL . 'Leave blank to disable the login section.';
  $passchange = '';
}; // if isset $ini
/**
 * Create site
 */
OpenSite('Database Settings');
if (isset($ini)) {
  configMenu();
}; // if isset $ini
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=dodb" method="post">' . PHP_EOL
    .'<!-- Database Setup -->' . PHP_EOL
    .'<table summary="">' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">Database Settings</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Host:</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Host]" value="'.$db_host.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Username:</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Username]" value="'.$db_user.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Password:</td>' . PHP_EOL
    .'    <td><input type="password" name="config[DB_Password]" value="'.$db_pass.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Database:</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Database]" value="'.$db_name.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Prefix:</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Prefix]" value="'.$db_prefix.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$db_info
    .'</table>' . PHP_EOL;
if (isset($ini)) {
  echo '<input type="submit" value="Update" />' . PHP_EOL;
} else {
  echo inputHiddenPost()
      .'<input type="submit" value="Next" />' . PHP_EOL;
}; // if isset $ini
echo '</form>' . PHP_EOL;
CloseSite();
?>
