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
OpenSite(SETUP,true);
echo '<form name="go_no_go" action="'.$_SERVER['SCRIPT_NAME'].'?lang='.$_GET['lang'].'&amp;edit=select" method="post">' . PHP_EOL
    .'<!-- Database Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.ED_UPDATE_DB.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.HOST.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Host]" value="'.$ini_yapeal['Database']['host'].'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.USERNAME.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Username]" value="'.$ini_yapeal['Database']['username'].'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PASSWORD.':</td>' . PHP_EOL
    .'    <td><input type="password" name="config[DB_Password]" value="'.$ini_yapeal['Database']['password'].'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.DATABASE.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Database]" value="'.$ini_yapeal['Database']['database'].'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.PREFIX.':</td>' . PHP_EOL
    .'    <td><input type="text" name="config[DB_Prefix]" value="'.$ini_yapeal['Database']['table_prefix'].'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_ACTION.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_action]">' . PHP_EOL
    .'        <option value="0">'.ED_DO_NOTHING.'</option>' . PHP_EOL
    .'        <option value="1">'.ED_UPDATE_DB.'</option>' . PHP_EOL
    .'        <option value="2">'.ED_CLEAN_SETUP.'</option>' . PHP_EOL
    .'      </select><br />' . PHP_EOL
    .'      '. ED_CLEAN_SETUP_DES . PHP_EOL
    .'    </td>' . PHP_EOL
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
    .'        <option value="0"'; if ($ini_yapeal['Api']['cache_xml']==false) { echo ' selected="selected"'; } echo '>'.NO.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($ini_yapeal['Api']['cache_xml']==1) { echo ' selected="selected"'; } echo '>'.YES.'</option>' . PHP_EOL
    .'      </select><br />' . PHP_EOL
    . INSTALLER_SAVE_XML_DES
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_ACCOUNT_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_account]">' . PHP_EOL
    .'        <option value="'; if ($conf['accountData']==2) { echo '2'; } else { echo '0'; } echo '"'; if ($conf['accountData']==0 || $conf['accountData']==2) { echo ' selected="selected"'; } echo '>'.ED_DISABLE.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($conf['accountData']==1) { echo ' selected="selected"'; } echo '>'.ED_GET_INFO.'</option>' . PHP_EOL
    .'        <option value="2">'.ED_REMOVE_ALL_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_CHAR_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_char]">' . PHP_EOL
    .'        <option value="'; if ($conf['charData']==2) { echo '2'; } else { echo '0'; } echo '"'; if ($conf['charData']==0 || $conf['charData']==2) { echo ' selected="selected"'; } echo '>'.ED_DISABLE.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($conf['charData']==1) { echo ' selected="selected"'; } echo '>'.ED_GET_INFO.'</option>' . PHP_EOL
    .'        <option value="2">'.ED_REMOVE_ALL_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_CORP_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_corp]">' . PHP_EOL
    .'        <option value="'; if ($conf['corpData']==2) { echo '2'; } else { echo '0'; } echo '"'; if ($conf['corpData']==0 || $conf['corpData']==2) { echo ' selected="selected"'; } echo '>'.ED_DISABLE.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($conf['corpData']==1) { echo ' selected="selected"'; } echo '>'.ED_GET_INFO.'</option>' . PHP_EOL
    .'        <option value="2">'.ED_REMOVE_ALL_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_EVE_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_eve]">' . PHP_EOL
    .'        <option value="'; if ($conf['eveData']==2) { echo '2'; } else { echo '0'; } echo '"'; if ($conf['eveData']==0 || $conf['eveData']==2) { echo ' selected="selected"'; } echo '>'.ED_DISABLE.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($conf['eveData']==1) { echo ' selected="selected"'; } echo '>'.ED_GET_INFO.'</option>' . PHP_EOL
    .'        <option value="2">'.ED_REMOVE_ALL_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_MAP_INFO.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[db_map]">' . PHP_EOL
    .'        <option value="'; if ($conf['mapData']==2) { echo '2'; } else { echo '0'; } echo '"'; if ($conf['mapData']==0 || $conf['mapData']==2) { echo ' selected="selected"'; } echo '>'.ED_DISABLE.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($conf['mapData']==1) { echo ' selected="selected"'; } echo '>'.ED_GET_INFO.'</option>' . PHP_EOL
    .'        <option value="2">'.ED_REMOVE_ALL_DATA.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.ED_DEBUGING.':</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="config[debug]">' . PHP_EOL
    .'        <option value="0"'; if ($ini_yapeal['Logging']['trace_active']=='0') { echo ' selected="selected"'; } echo '>'.OFF.'</option>' . PHP_EOL
    .'        <option value="1"'; if ($ini_yapeal['Logging']['trace_active']=='1') { echo ' selected="selected"'; } echo '>'.ON.'</option>' . PHP_EOL
    .'      </select>' . PHP_EOL
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
    .'      <td><input size="10" type="text" id="api_user_id" name="config[api_user_id]" value="'.$conf['creatorAPIuserID'].'" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">'.INSTALLER_API_LIMIT_KEY.':</td>' . PHP_EOL
    .'      <td><input class="input_text" type="text" id="api_limit_key" name="config[api_limit_key]" value="'.$conf['creatorAPIlimitedApiKey'].'" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">'.INSTALLER_API_FULL_KEY.':</td>' . PHP_EOL
    .'      <td><input class="input_text" type="text" id="api_full_key" name="config[api_full_key]" value="'.$conf['creatorAPIfullApiKey'].'" onblur="api_get_chars()" /></td>' . PHP_EOL
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
    .'      <td><input type="password" id="config_pass" name="config[config_pass]" value="" /> '.ED_ONLY_CHANGE_IF.'</td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'</table><br />' . PHP_EOL
    .'<div  id="submit_select"><input type="submit" value="'.NEXT.'" /></div>' . PHP_EOL
    .'</form>' . PHP_EOL
    .'<script type="text/javascript">' . PHP_EOL
    .'  load_Edit_Setup_JavaScript('.$conf['creatorCharacterID'].');' . PHP_EOL
    .'</script>' . PHP_EOL;
CloseSite();
?>
