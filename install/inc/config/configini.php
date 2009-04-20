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
  /*
   * Get values from ini
   */
  $cache_xml = $ini['Api']['cache_xml'];
  $account_active = $ini['Api']['account_active'];
  $char_active = $ini['Api']['char_active'];
  $corp_active = $ini['Api']['corp_active'];
  $eve_active = $ini['Api']['eve_active'];
  $map_active = $ini['Api']['map_active'];
  /*
   * Show debuging
   */
  $debug = '  <tr>' . PHP_EOL
          .'    <td class="tableinfolbl">'.DEBUGING.':</td>' . PHP_EOL
          .'    <td>' . PHP_EOL
          .'      <select name="config[api_debug]">' . PHP_EOL
          .'        <option value="0"'; if ($ini['Logging']['trace_active']=='0') { $debug .= ' selected="selected"'; } $debug .= '>'.OFF.'</option>' . PHP_EOL
          .'        <option value="1"'; if ($ini['Logging']['trace_active']=='1') { $debug .= ' selected="selected"'; } $debug .= '>'.ON.'</option>' . PHP_EOL
          .'      </select>' . PHP_EOL
          .'    </td>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
  /*
   * Set some setup password description
   */
  $passnologin = '';
  $passchange = ' '.ONLY_CHANGE_PASS_IF;
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
  $passnologin = SETUP_PASS_DES_BLANK;
  $passchange = '';
}; // if isset $ini
/**
 * Create site
 */
OpenSite(SETUP);
if (isset($ini)) {
  configMenu();
}; // if isset $ini
echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=doini" method="post">' . PHP_EOL
    .'<!-- Config Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.INI_SETUP.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.SAVE_XML_FILES.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[api_cache_xml]">' . PHP_EOL
    .'        <option value="0"'; if ($cache_xml==false) { echo ' selected="selected"'; } echo '>'.NO.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($cache_xml==true) { echo ' selected="selected"'; } echo '>'.YES.'</option>' . PHP_EOL
    .'      </select><br />' . PHP_EOL
    . SAVE_XML_DES
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ACCOUNT_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[api_account]">' . PHP_EOL
    .'        <option value="0"'; if ($account_active==false) { echo ' selected="selected"'; } echo '>'.DISABLED.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($account_active==true) { echo ' selected="selected"'; } echo '>'.GET_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.CHAR_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[api_char]">' . PHP_EOL
    .'        <option value="0"'; if ($char_active==false) { echo ' selected="selected"'; } echo '>'.DISABLED.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($char_active==true) { echo ' selected="selected"'; } echo '>'.GET_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.CORP_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[api_corp]">' . PHP_EOL
    .'        <option value="0"'; if ($corp_active==false) { echo ' selected="selected"'; } echo '>'.DISABLED.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($corp_active==true) { echo ' selected="selected"'; } echo '>'.GET_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.EVE_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[api_eve]">' . PHP_EOL
    .'        <option value="0"'; if ($eve_active==false) { echo ' selected="selected"'; } echo '>'.DISABLED.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($eve_active==true) { echo ' selected="selected"'; } echo '>'.GET_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.MAP_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[api_map]">' . PHP_EOL
    .'        <option value="0"'; if ($map_active==false) { echo ' selected="selected"'; } echo '>'.DISABLED.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($map_active==true) { echo ' selected="selected"'; } echo '>'.GET_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .$debug
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<!-- Config Password Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.SETUP_PASS.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" style="text-align: center;">'.SETUP_PASS_DES.$passnologin.'</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PASSWORD.':</td>' . PHP_EOL
    .'    <td><input type="password" name="config[config_pass]" value="" />'.$passchange.'</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'</table>' . PHP_EOL;
if (isset($ini)) {
  echo '<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'<input type="submit" value="'.UPDATE.'" />' . PHP_EOL;
} else {
  echo inputHiddenPost()
      .'<input type="submit" value="'.FINISH_SETUP.'" />' . PHP_EOL;
}; // if isset $ini
echo '</form>' . PHP_EOL;
CloseSite();
?>
