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
/**
 * Get config if it's set
 */
if (isset($_POST['config'])) {
  $config = $_POST['config'];
}; // if isset $_POST['config']
/*
 * Set some values
 */
if (isset($_POST['getlist'])) {
  $apiuserid = $config['api_user_id'];
  $apikey = $config['api_key'];
  $loadcharsel = true;
} else {
  $apiuserid = '';
  $apikey = '';
  if (isset($conf['creatorAPIuserID']) && isset($conf['creatorAPIKey'])) {
    $apiuserid = $conf['creatorAPIuserID'];
    $apikey = $conf['creatorAPIKey'];
    $loadcharsel = true;
  };
}; // if isset $_POST['getlist']
/*
 * Create site
 */
OpenSite(TEST_CHAR);
configMenu();
echo '<h2>'.TEST_CHAR.'</h2>' . PHP_EOL
    .TEST_CHAR_DES
    .'<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configapi" method="post">' . PHP_EOL
    .'<!-- Api Setup -->' . PHP_EOL
    .'<table>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <th colspan="2">'.API_SETUP.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td colspan="2" style="text-align: center;">'.GET_API_INFO_HERE.':<br /><a href="http://myeve.eve-online.com/api/default.asp">'.EVE_API_CENTER.'</a></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.API_USERID.':</td>' . PHP_EOL
    .'    <td><input size="10" type="text" name="config[api_user_id]" value="'.$apiuserid.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'  <tr>' . PHP_EOL
    .'    <td class="tableinfolbl">'.API_KEY.':</td>' . PHP_EOL
    .'    <td><input class="input_text" type="text" name="config[api_key]" value="'.$apikey.'" /></td>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    .'</table>' . PHP_EOL
    .'<input type="hidden" name="getlist" value="true" />' . PHP_EOL
    .'<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
    .'<input type="submit" value="'.GET_CHAR_LIST.'" />' . PHP_EOL
    .'</form>' . PHP_EOL;
if (isset($loadcharsel)) {
  echo '<br />' . PHP_EOL;
  require_once('inc'.$ds.'config'.$ds.'char_select.php');
}
echo '<br />' . PHP_EOL;
CloseSite();
?>
