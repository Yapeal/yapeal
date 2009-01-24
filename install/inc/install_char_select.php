<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Character Select for no JavaScript User.
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
OpenSite('Character Selection');
if ($_POST['api_user_id'] !="" && ($_POST['api_limit_key'] !="" || $_POST['api_full_key'] !="")) {
  $params = array();
  $params['userID'] = $_POST['api_user_id'];
  if ($_POST['api_full_key'] !="") {
    $params['apiKey'] = $_POST['api_full_key'];
  } else {
    $params['apiKey'] = $_POST['api_limit_key'];
  };
  
  // poststring
  if (count($params) > 0) {
    $poststring = http_build_query($params, NULL, '&');
  } else {
    $poststring = "";
  };
  $ch = curl_init();
  $header = array(
    "Accept: text/xml,application/xml,application/xhtml+xml;q=0.9,text/html;q=0.8,text/plain;q=0.7,image/png;q=0.6,*/*;q=0.5",
    "Accept-Language: en-us;q=0.9,en;q=0.8,*;q=0.7",
    "Accept-Charset: utf-8;q=0.9,windows-1251;q=0.7,*;q=0.6",
    "Keep-Alive: 300"
  );
  $options = array(CURLOPT_URL => 'http://api.eve-online.com/account/Characters.xml.aspx',
                   CURLOPT_ENCODING => '',
                   CURLOPT_FOLLOWLOCATION => TRUE,
                   CURLOPT_HEADER => FALSE,
                   CURLOPT_MAXREDIRS => 5,
                   CURLOPT_RETURNTRANSFER => TRUE,
                   CURLOPT_SSL_VERIFYHOST => FALSE,
                   CURLOPT_SSL_VERIFYPEER => FALSE,
                   CURLOPT_VERBOSE => FALSE,
                   CURLOPT_USERAGENT => 'PHPApi',
                   CURLOPT_HTTPHEADER => $header,
                   CURLOPT_POST => TRUE,
                   CURLOPT_POSTFIELDS => $poststring
                  );
  curl_setopt_array($ch, $options);
  $content = curl_exec($ch);
  curl_close($ch);
  //echo $content.'<hr>';
  // create our xml parser
  try {
    $xml = new SimpleXMLElement($content);
  }
  catch (Exception $e) {
  }

  if ($xml) {
    if (isset($xml->error)) {
      echo '<center><font class="warning">Error: '.$xml->error.'</font></center>';
    } else {
      $characters = array();
      echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?install=go" method="post">' . PHP_EOL
          .'<input type="hidden" name="DB_Host_Main" value="'.$_POST['DB_Host_Main'].'" />' . PHP_EOL
          .'<input type="hidden" name="DB_Username_Main" value="'.$_POST['DB_Username_Main'].'" />' . PHP_EOL
          .'<input type="hidden" name="DB_Password_Main" value="'.$_POST['DB_Password_Main'].'" />' . PHP_EOL
          .'<input type="hidden" name="DB_Database_Main" value="'.$_POST['DB_Database_Main'].'" />' . PHP_EOL
          .'<input type="hidden" name="cache_xml" value="'.DisableChecker($_POST['cache_xml']).'" />' . PHP_EOL
          .'<input type="hidden" name="db_account" value="'.DisableChecker($_POST['db_account']).'" />' . PHP_EOL
          .'<input type="hidden" name="db_char" value="'.DisableChecker($_POST['db_char']).'" />' . PHP_EOL
          .'<input type="hidden" name="db_corp" value="'.DisableChecker($_POST['db_corp']).'" />' . PHP_EOL
          .'<input type="hidden" name="db_eve" value="'.DisableChecker($_POST['db_eve']).'" />' . PHP_EOL
          .'<input type="hidden" name="db_map" value="'.DisableChecker($_POST['db_map']).'" />' . PHP_EOL
          .'<input type="hidden" name="api_user_id" value="'.$_POST['api_user_id'].'" />' . PHP_EOL
          .'<input type="hidden" name="api_limit_key" value="'.$_POST['api_limit_key'].'" />' . PHP_EOL
          .'<input type="hidden" name="api_full_key" value="'.$_POST['api_full_key'].'" />' . PHP_EOL
          .'<table>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <th colspan="2">Character Select</th>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      foreach ($xml->result->rowset->row as $row) {
        echo '<tr>' . PHP_EOL
            .'  <td width="15" style="vertical-align:middle;">' . PHP_EOL
            .'    <input type="radio" name="api_char_info" value="'.$row['name'].'^-_-^'.$row['characterID'].'^-_-^'.$row['corporationName'].'^-_-^'.$row['corporationID'].'" />' . PHP_EOL
            .'  </td>' . PHP_EOL
            .'  <td>' . PHP_EOL
            .'    <table style="background-color: #606060;">' . PHP_EOL
            .'      <tr>' . PHP_EOL
            .'        <td style="width: 64px;"><img src="http://img.eve.is/serv.asp?s=64&amp;c='.$row['characterID'].'" width="64" height="64" /></td>' . PHP_EOL
            .'        <td>'.$row['name'].'<br />'.$row['corporationName'].'</td>' . PHP_EOL
            .'      </tr>' . PHP_EOL
            .'    </table>' . PHP_EOL
            .'  </td>' . PHP_EOL
            .'</tr>' . PHP_EOL;
      };
      echo '<tr style="text-align:center;">' . PHP_EOL
          .'  <td colspan="2"><input type="submit" value="Run Install" /></td>' . PHP_EOL
          .'</tr>' . PHP_EOL
          .'</table>' . PHP_EOL
          .'</form>' . PHP_EOL;
    };
  } else {
    echo '<center><font class="warning">Error<br>EVE API Server if Offline. Please try later.</font></center>';
  };
} else {
  echo 'You must provide API Info';
};
CloseSite();
?>
