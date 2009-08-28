<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal installer - Character Select for non-JavaScript User.
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
/**
 * Check if api info is set
 */
if ($apiuserid !="" && $apikey !="") {
  /**
   * Build cURL query
   */
  $params = array();
  $params['userID'] = $apiuserid;
  $params['apiKey'] = $apikey;

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
                   CURLOPT_HEADER => TRUE,
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
  /**
   * Execute cURL query and get xml
   */
  $xml = getApiInfo($ch);
  curl_close($ch);
  /**
   * Check if $xml is false and output server is down if it is.
   */
  if ($xml) {
    /**
     * Handle xml data
     */
    if (isset($xml->error)) {
      /*
       * Show XML error is there is one
       */
      echo '<font class="warning">Error: '.$xml->error.'</font>';
    } else {
      /**
       * Set hidden post values
       */
      echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=doapi" method="post">' . PHP_EOL;
      $exceptions = array('getlist');
      echo inputHiddenPost($exceptions);
      echo '<table summary="Table show Eve characters with thier pictures and corporation symbols">' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <th colspan="2">Character Select</th>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      /**
       * Generate character list
       */
      foreach ($xml->result->rowset->row as $row) {
        if (isset($conf['creatorCharacterID']) && $conf['creatorCharacterID']==$row['characterID']) { $checked = ' checked="checked"'; } else { $checked = ''; };
        echo '<tr>' . PHP_EOL
            .'  <td style="width: 15px; vertical-align:middle;">' . PHP_EOL
            .'    <input type="radio" name="config[api_char_info]" value="'
            .$row['name'] . '^-_-^' . $row['characterID'] . '^-_-^'
            .$row['corporationName'] . '^-_-^' . $row['corporationID'] . '"'
            .$checked . ' />' . PHP_EOL
            .'  </td>' . PHP_EOL
            .'  <td>' . PHP_EOL
            .'    <table class="notable" style="width: 100%;" summary="">' . PHP_EOL
            .'      <tr>' . PHP_EOL
            .'        <td style="width: 66px;">'
            .'<img src="http://img.eve.is/serv.asp?s=64&amp;c='
            .$row['characterID'] . '" width="64" height="64"'
            .' alt="' . $row['name'] . '"'
            .' title="' . $row['name'] . '" /></td>' . PHP_EOL
            .'        <td>' . $row['name'] . '<br />'
            .$row['corporationName'] . '</td>' . PHP_EOL
            .'        <td style="width: 66px;">'
            .'<img src="http://www.evecorplogo.net/logo.php?id='
            .$row['corporationID'] . '&amp;bgc=606060" width="64" height="64"'
            .' alt="' . $row['corporationName'] . '"'
            .' title="' . $row['corporationName'] . '" /></td>' . PHP_EOL
            .'      </tr>' . PHP_EOL
            .'    </table>' . PHP_EOL
            .'  </td>' . PHP_EOL
            .'</tr>' . PHP_EOL;
      };
      echo '</table><br />' . PHP_EOL;
      /*
       * Get the selected Character APIs from the database config
       */
      if (isset($conf['charAPIs'])) {
        $charSelectedAPIs = explode(" ",$conf['charAPIs']);
      } else {
        $charSelectedAPIs = array();
      }; // if is set $conf['charAPIs']
      echo '<table summary="">' . PHP_EOL
        .'  <tr>' . PHP_EOL
        .'    <th colspan="2">Character API Pull Select</th>' . PHP_EOL
        .'  </tr>' . PHP_EOL
        .'  <tr>' . PHP_EOL
        .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">'
        .'Select which API data you wish to pull for this character</td>' . PHP_EOL
        .'  </tr>' . PHP_EOL;
      /*
       * Build the Character API list
       */
      foreach ($charAPIs as $API=>$APIDes) {
			  if (in_array($API,$charSelectedAPIs)) {
          $check = ' checked="checked"';
        } else {
          $check = '';
        };// if (in_array($API,$corpSelectedAPIs))
				echo '  <tr>' . PHP_EOL
          .'    <td width="15" style="vertical-align:middle;">' . PHP_EOL
          .'      <input type="checkbox" name="config[charAPIs][]" value="'.$API.'"'.$check.' />' . PHP_EOL
          .'    </td>' . PHP_EOL
          .'    <td>'.$APIDes.'</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      };
      /*
       * Get Character API proxy from the database config
       */
      if (isset($conf['charProxy']) && !empty($conf['charProxy'])) {
        $charProxy = $conf['charProxy'];
      } else {
        $charProxy = 'http://api.eve-online.com/%2$s/%1$s.xml.aspx';
      };// if is set $conf['charAPIs']
      /*
       * List API Selection for Character
       */
      echo '  <tr>' . PHP_EOL
          .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">Character API Proxy</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <td colspan="2" class="tableinfolbl" style="text-align:left;">'
          .'<input type="text" name="config[charProxy]" value="' . $charProxy . '" size="60" /><br />' . PHP_EOL
          .'%2$s and %1$s is automatic filled in by Yapeal. <br />' . PHP_EOL
          .'%2$s is filled with the API section.<br />' . PHP_EOL
          .'and %1$s with the API name.<br />' . PHP_EOL
          .'An example of the default proxy setting for Medals in the API char section would look like this:<br />' . PHP_EOL
          .'http://api.eve-online.com/char/Medals.xml.aspx</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL
          .'</table>' . PHP_EOL;
      /*
       * Get the selected Corporation APIs from the database config
       */
      if (isset($conf['corpAPIs'])) {
        $corpSelectedAPIs = explode(" ",$conf['corpAPIs']);
      } else {
        $corpSelectedAPIs = array();
      }; // if is set $conf['corpAPIs']
      echo '<table summary="">' . PHP_EOL
        .'  <tr>' . PHP_EOL
        .'    <th colspan="2">Corporation API Pull Select</th>' . PHP_EOL
        .'  </tr>' . PHP_EOL
        .'  <tr>' . PHP_EOL
        .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">'
        .'Select which API data you wish to pull for this corporation</td>' . PHP_EOL
        .'  </tr>' . PHP_EOL;
      /*
       * Build the Corporation API list
       */
      foreach ($corpAPIs as $API=>$APIDes) {
			  if (in_array($API,$corpSelectedAPIs)) {
          $check = ' checked="checked"';
        } else {
          $check = '';
        };// if (in_array($API,$corpSelectedAPIs))
				echo '  <tr>' . PHP_EOL
          .'    <td width="15" style="vertical-align:middle;">' . PHP_EOL
          .'      <input type="checkbox" name="config[corpAPIs][]" value="'.$API.'"'.$check.' />' . PHP_EOL
          .'    </td>' . PHP_EOL
          .'    <td>'.$APIDes.'</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      };
      /*
       * Get Corporation API proxy from the database config
       */
      if (isset($conf['corpProxy']) && !empty($conf['corpProxy'])) {
        $corpProxy = $conf['corpProxy'];
      } else {
        $corpProxy = 'http://api.eve-online.com/%2$s/%1$s.xml.aspx';
      }; // if is set $conf['charAPIs']
      /*
       * List API Selection for Corporation
       */
      echo '  <tr>' . PHP_EOL
          .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">Corporation API Proxy</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <td colspan="2" class="tableinfolbl" style="text-align:left;">'
          .'<input type="text" name="config[corpProxy]" value="' . $corpProxy . '" size="60" /><br />' . PHP_EOL
          .'%2$s and %1$s is automatic filled in by Yapeal. <br />' . PHP_EOL
          .'%2$s is filled with the API section.<br />' . PHP_EOL
          .'and %1$s with the API name.<br />' . PHP_EOL
          .'An example of the default proxy setting for Medals in the API corp section would look like this:<br />' . PHP_EOL
          .'http://api.eve-online.com/corp/Medals.xml.aspx</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      echo '</table>' . PHP_EOL
      /*
       * Add Submit button
       */
          .'<input type="hidden" name="config[api_user_id]" value="'.$apiuserid.'" />' . PHP_EOL
          .'<input type="hidden" name="config[api_key]" value="'.$apikey.'" />' . PHP_EOL
          .'<input type="submit" value="Update" />' . PHP_EOL
          .'</form>' . PHP_EOL;
    };
  } else {
    /**
     * Api server is offline
     */
    echo '<font class="warning">Error<br />EVE API Server if Offline. Please try later.</font>';
  };
} else {
  /**
   * Api info not set
   */
  echo '<font class="warning">You must provide API Info</font>';
};
?>
