<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer server Ajax script.
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
if (file_exists("../inc/language/lang_".$_GET['lang'].".php")) {
  include("../inc/language/lang_".$_GET['lang'].".php");
} else {
  include("../inc/language/lang_en.php");
}
if ($_GET['id'] != "" && $_GET['key']) {
  //echo 'id = '.$_GET['id'].'<br>key = '.$_GET['key'];
  $params = array();
  $params['userID'] = $_GET['id'];
  $params['apiKey'] = $_GET['key'];
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
      echo '<center><font class="warning">'.ERROR.': '.$xml->error.'</font></center>';
    } else {
      $characters = array();
			if (isset($_GET['char'])) { $onChange = ' onchange="Select_Character2();"'; } else { $onChange = ' onchange="Select_Character();"'; };
      echo PHP_EOL .'        <select id="api_char_choise" name="config[api_char_info]"'.$onChange.'>' . PHP_EOL
                   .'          <option value="">'.INSTALLER_SELECT_CHAR.'</option>' . PHP_EOL;
      foreach ($xml->result->rowset->row as $row) {
        if ($_GET['char']==$row['characterID']) { $sel = ' selected="selected"'; } else { $sel = ''; };
				echo '          <option value="'.$row['name'].'^-_-^'.$row['characterID'].'^-_-^'.$row['corporationName'].'^-_-^'.$row['corporationID'].'"'.$sel.'>'.$row['name'].'</option>' . PHP_EOL;
      };
      echo '        </select>' . PHP_EOL;
    };
  } else {
    echo '<center><font class="warning">'.INSTALLER_ERROR_API_SERVER_OFFLINE.'</font></center>';
  };
} else {
  echo 'ERROR';
};
?>
