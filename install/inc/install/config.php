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
// Check for c_action
check_c_action();
/**
 * Run the script if check_c_action(); didn't exit the script
 */
OpenSite(SETUP,true);
echo '<form name="go_no_go" action="'.$_SERVER['SCRIPT_NAME'].'?lang='.$_GET['lang'].'&install=step3" method="post">' . PHP_EOL
    .'<!-- Database Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.DATABASE.' '.SETUP.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.HOST.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Host]" value="localhost" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.USERNAME.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Username]" value="username" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PASSWORD.':</td>' . PHP_EOL
    .'    <td><input type="password" name="config[DB_Password]" value="" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.DATABASE.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Database]" value="yapeal" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PREFIX.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Prefix]" value="yapeal" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<!-- Config Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.CONFIG.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" style="text-align: center;">'.INSTALLER_SETUP_HOW_YAPEAL.'</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.INSTALLER_SAVE_XML_FILES.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[cache_xml]">' . PHP_EOL
    .'        <option value="0">'.NO.'</option>' . PHP_EOL
    .'        <option value="1">'.YES.'</option>' . PHP_EOL
    .'      </select><br />' . PHP_EOL
    . INSTALLER_SAVE_XML_DES
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.INSTALLER_GET_ACCOUNT_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_account]">' . PHP_EOL
    .'        <option value="0">'.NO.'</option>' . PHP_EOL
    .'        <option value="1">'.YES.'</option>' . PHP_EOL
    .'      </select>'.INSTALLER_GET_ACCOUNT_DES . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.INSTALLER_GET_CHAR_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_char]">' . PHP_EOL
    .'        <option value="0">'.NO.'</option>' . PHP_EOL
    .'        <option value="1">'.YES.'</option>' . PHP_EOL
    .'      </select>'. INSTALLER_GET_CHAR_DES . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.INSTALLER_GET_CORP_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_corp]">' . PHP_EOL
    .'        <option value="0">'.NO.'</option>' . PHP_EOL
    .'        <option value="1">'.YES.'</option>' . PHP_EOL
    .'      </select>'. INSTALLER_GET_CORP_DES . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.INSTALLER_GET_EVE_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_eve]">' . PHP_EOL
    .'        <option value="0">'.NO.'</option>' . PHP_EOL
    .'        <option value="1">'.YES.'</option>' . PHP_EOL
    .'      </select>'. INSTALLER_GET_EVE_DES . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.INSTALLER_GET_MAP_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_map]">' . PHP_EOL
    .'        <option value="0">'.NO.'</option>' . PHP_EOL
    .'        <option value="1">'.YES.'</option>' . PHP_EOL
    .'      </select>'. INSTALLER_GET_MAP_DES . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<!-- Api Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tbody id="api_setup_table">' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <th colspan="2">'.INSTALLER_API_SETUP.'</th>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td colspan="2" style="text-align: center;">'.INSTALLER_GET_API_INFO_HERE.':<br /><a href="http://myeve.eve-online.com/api/default.asp">'.INSTALLER_EVE_API_CENTER.'</a></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">'.INSTALLER_API_USERID.':</td>' . PHP_EOL
    .'      <td><input size="10" type="text" id="api_user_id" name="config[api_user_id]" value="" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">'.INSTALLER_API_LIMIT_KEY.':</td>' . PHP_EOL
    .'      <td><input class="input_text" type="text" id="api_limit_key" name="config[api_limit_key]" value="" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">'.INSTALLER_API_FULL_KEY.':</td>' . PHP_EOL
    .'      <td><input class="input_text" type="text" id="api_full_key" name="config[api_full_key]" value="" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'  </tbody>' . PHP_EOL
    .'</table><br />' . PHP_EOL
    .'<!-- Api Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <th colspan="2">'.INSTALLER_SETUP_PASS.'</th>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td colspan="2" style="text-align: center;">'.INSTALLER_SETUP_PASS_DES.'</td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">'.PASSWORD.':</td>' . PHP_EOL
    .'      <td><input type="password" id="config_pass" name="config[config_pass]" value="" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'</table><br />' . PHP_EOL
    .'<input type="hidden" name="c_action" value="'.$_POST['c_action'].'" />' . PHP_EOL
    .'<div id="submit_select"><input type="submit" value="'.NEXT.'" /></div>' . PHP_EOL
    .'</form>' . PHP_EOL
    .'<script type="text/javascript">' . PHP_EOL
    .'  load_Install_Setup_JavaScript();' . PHP_EOL
    .'</script>' . PHP_EOL;
CloseSite();
?>
