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
OpenSite('Setup',true);
echo '<form name="go_no_go" action="'.$_SERVER['SCRIPT_NAME'].'?install=step3" method="post">' . PHP_EOL
    .'<!-- Database Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">Database Setup</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" style="text-align: center;">Multiple database setup is not supported at this time.<br />It maybe be added in a later release.</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Host:</td>' . PHP_EOL
    .'    <td><input type="text" name="DB_Host_Main" value="localhost" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Username:</td>' . PHP_EOL
    .'    <td><input type="text" name="DB_Username_Main" value="username" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Password:</td>' . PHP_EOL
    .'    <td><input type="password" name="DB_Password_Main" value="password" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Database:</td>' . PHP_EOL
    .'    <td><input type="text" name="DB_Database_Main" value="yapeal" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<!-- Config Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">Config</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" style="text-align: center;">Setup how Yapeal should behave</td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Save XML files:</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="cache_xml">' . PHP_EOL
    .'        <option value="0">No</option>' . PHP_EOL
    .'        <option value="1">Yes</option>' . PHP_EOL
    .'      </select><br />' . PHP_EOL
    .'      Turns on caching of API XML data to local files.<br />' . PHP_EOL
    .'      "No" = Save web space but still adds to the database.' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Get Account Info:</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="db_account" disabled="disabled">' . PHP_EOL
    .'        <option value="0">No</option>' . PHP_EOL
    .'        <option value="1">Yes</option>' . PHP_EOL
    .'      </select>Save Account info to database' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Get Character Info:</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="db_char">' . PHP_EOL
    .'        <option value="0">No</option>' . PHP_EOL
    .'        <option value="1">Yes</option>' . PHP_EOL
    .'      </select>Save Character info to database' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Get Corp Info:</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="db_corp">' . PHP_EOL
    .'        <option value="0">No</option>' . PHP_EOL
    .'        <option value="1">Yes</option>' . PHP_EOL
    .'      </select>Save Corp info to database' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Get EvE Info:</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="db_eve">' . PHP_EOL
    .'        <option value="0">No</option>' . PHP_EOL
    .'        <option value="1">Yes</option>' . PHP_EOL
    .'      </select>Save EvE info to database' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">Get Map Info:</td>' . PHP_EOL
    .'    <td>' . PHP_EOL
    .'      <select name="db_map" disabled="disabled">' . PHP_EOL
    .'        <option value="0">No</option>' . PHP_EOL
    .'        <option value="1">Yes</option>' . PHP_EOL
    .'      </select>Save Map info to database' . PHP_EOL
    .'    </td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'</table>' . PHP_EOL
    .'<br />' . PHP_EOL
    .'<!-- Api Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tbody id="api_setup_table">' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <th colspan="2">API Setup</th>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td colspan="2" style="text-align: center;">You can get your API info here:<br /><a href="http://myeve.eve-online.com/api/default.asp">EVE API Center</a></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">API User ID:</td>' . PHP_EOL
    .'      <td><input size="10" type="text" id="api_user_id" name="api_user_id" value="" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">Limited API Key:</td>' . PHP_EOL
    .'      <td><input class="input_text" type="text" id="api_limit_key" name="api_limit_key" value="" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'    <tr>' . PHP_EOL
    .'      <td class="tableinfolbl">Full API Key:</td>' . PHP_EOL
    .'      <td><input class="input_text" type="text" id="api_full_key" name="api_full_key" value="" onblur="api_get_chars()" /></td>' . PHP_EOL
    .'    </tr>' . PHP_EOL
    .'  </tbody>' . PHP_EOL
    .'</table><br />' . PHP_EOL
    .'<div id="submit_select"><input type="submit" value="Next" /></div>' . PHP_EOL
    .'</form>' . PHP_EOL
    .'<script type="text/javascript">' . PHP_EOL
		.'  load_Install_Setup_JavaScript();' . PHP_EOL
    .'</script>' . PHP_EOL;
CloseSite();
?>
