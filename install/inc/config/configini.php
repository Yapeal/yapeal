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
  /*
   * Get values from ini
   */
  $cache_xml = $ini['Cache']['cache_xml'];
  $account_active = DBHandler('USACTIVE', 'utilSections', $config['api_account'], 'account');
  $char_active = DBHandler('USACTIVE', 'utilSections', $config['api_char'], 'char');
  $corp_active = DBHandler('USACTIVE', 'utilSections', $config['api_corp'], 'corp');
  $eve_active = DBHandler('USACTIVE', 'utilSections', $config['api_eve'], 'eve');
  $map_active = DBHandler('USACTIVE', 'utilSections', $config['api_map'], 'map');
  /*
   * Show debuging
   */
  $debug = '  <tr>' . PHP_EOL
    . '    <td class="tableinfolbl">Debugging:</td>' . PHP_EOL
    . '    <td>' . PHP_EOL
    . '      <select name="config[api_debug]">' . PHP_EOL
    . '        <option value="0"'; if ($ini['Logging']['trace_active']=='0') { $debug .= ' selected="selected"'; } $debug .= '>Off</option>' . PHP_EOL
    . '        <option value="1"'; if ($ini['Logging']['trace_active']=='1') { $debug .= ' selected="selected"'; } $debug .= '>On</option>' . PHP_EOL
    . '      </select>' . PHP_EOL
    . '    </td>' . PHP_EOL
    . '  </tr>' . PHP_EOL;
  /*
   * Set some setup password description
   */
  $passnologin = '';
  $passchange = ' Change only if you need a new one';
} else {
  /*
   * Set standard values
   */
  $cache_xml = false;
  $account_active = false;
  $char_active = false;
  $corp_active = false;
  $eve_active = false;
  $map_active = false;
  /*
   * Hide debuging
   */
  $debug = '';
  /*
   * Set some setup password description
   */
  $passnologin = '<br />' . PHP_EOL . 'Leave blank to disable the login section.';
  $passchange = '';
}; // if isset $ini
/**
 * Create site
 */
OpenSite('Setup');
if (isset($ini)) {
  configMenu();
}; // if isset $ini
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=doini" method="post">' . PHP_EOL
   . '<!-- Config Setup -->' . PHP_EOL
   . '<table summary="">' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <th colspan="2">Yapeal Setup</th>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Save XML files:</td>' . PHP_EOL
   . '    <td>' . PHP_EOL
   . '      <select name="config[api_cache_xml]">' . PHP_EOL
   . '        <option value="0"'; if ($cache_xml==false) { echo ' selected="selected"'; } echo '>No</option>' . PHP_EOL
   . '        <option value="1"'; if ($cache_xml==true) { echo ' selected="selected"'; } echo '>Yes</option>' . PHP_EOL
   . '      </select><br />' . PHP_EOL
   . '       Turns on caching of API XML data to local files.<br />' . PHP_EOL
   . '      "No" = Save web space but still adds to the database.' . PHP_EOL
   . '    </td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Account Info:</td>' . PHP_EOL
   . '    <td>' . PHP_EOL
   . '      <select name="config[api_account]">' . PHP_EOL
   . '        <option value="0"'; if ($account_active==false) { echo ' selected="selected"'; } echo '>Disabled</option>' . PHP_EOL
   . '        <option value="1"'; if ($account_active==true) { echo ' selected="selected"'; } echo '>Get Data</option>' . PHP_EOL
   . '      </select>' . PHP_EOL
   . '    </td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Character Info:</td>' . PHP_EOL
   . '    <td>' . PHP_EOL
   . '      <select name="config[api_char]">' . PHP_EOL
   . '        <option value="0"'; if ($char_active==false) { echo ' selected="selected"'; } echo '>Disabled</option>' . PHP_EOL
   . '        <option value="1"'; if ($char_active==true) { echo ' selected="selected"'; } echo '>Get Data</option>' . PHP_EOL
   . '      </select>' . PHP_EOL
   . '    </td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Corp Info:</td>' . PHP_EOL
   . '    <td>' . PHP_EOL
   . '      <select name="config[api_corp]">' . PHP_EOL
   . '        <option value="0"'; if ($corp_active==false) { echo ' selected="selected"'; } echo '>Disabled</option>' . PHP_EOL
   . '        <option value="1"'; if ($corp_active==true) { echo ' selected="selected"'; } echo '>Get Data</option>' . PHP_EOL
   . '      </select>' . PHP_EOL
   . '    </td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Eve Info:</td>' . PHP_EOL
   . '    <td>' . PHP_EOL
   . '      <select name="config[api_eve]">' . PHP_EOL
   . '        <option value="0"'; if ($eve_active==false) { echo ' selected="selected"'; } echo '>Disabled</option>' . PHP_EOL
   . '        <option value="1"'; if ($eve_active==true) { echo ' selected="selected"'; } echo '>Get Data</option>' . PHP_EOL
   . '      </select>' . PHP_EOL
   . '    </td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Map Info:</td>' . PHP_EOL
   . '    <td>' . PHP_EOL
   . '      <select name="config[api_map]">' . PHP_EOL
   . '        <option value="0"'; if ($map_active==false) { echo ' selected="selected"'; } echo '>Disabled</option>' . PHP_EOL
   . '        <option value="1"'; if ($map_active==true) { echo ' selected="selected"'; } echo '>Get Data</option>' . PHP_EOL
   . '      </select>' . PHP_EOL
   . '    </td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . $debug
   . '</table>' . PHP_EOL
   . '<br />' . PHP_EOL
   . '<!-- Config Password Setup -->' . PHP_EOL
   . '<table summary="">' . PHP_EOL
   . '  <tr>' . PHP_EOL
   . '    <th colspan="2">Setup Password</th>' . PHP_EOL
   . '  </tr>' . PHP_EOL;
if (!isset($ini)) {
  echo '  <tr>' . PHP_EOL
     . '    <td colspan="2" style="text-align: center;">This is a password you can use if you need to make changes<br />' . PHP_EOL
     . 'to this setup, when you have completed this setup.'.$passnologin.'</td>' . PHP_EOL
     . '  </tr>' . PHP_EOL;
}
echo '  <tr>' . PHP_EOL
   . '    <td class="tableinfolbl">Password:</td>' . PHP_EOL
   . '    <td><input type="password" name="config[config_pass]" value="" />'.$passchange.'</td>' . PHP_EOL
   . '  </tr>' . PHP_EOL
   . '</table>' . PHP_EOL;
if (isset($ini)) {
  echo '<input type="submit" value="Update" />' . PHP_EOL;
} else {
  echo inputHiddenPost()
    . '<input type="submit" value="Finish Setup" />' . PHP_EOL;
}; // if isset $ini
echo '</form>' . PHP_EOL;
CloseSite();
?>
