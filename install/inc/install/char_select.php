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
/**
 * Log where in the setup progress we are
 */
$logtime = $_POST['logtime'];
$logtimenow = date('H:i:s',time());
$logfile = basename(__FILE__);
$log = <<<LOGTEXT
--------------------------------------------------------------------------------
--------------------------------------------------------------------------------
Time: [$logtimenow]
Page: {$_SERVER['SCRIPT_NAME']}?{$_SERVER['QUERY_STRING']}
File: $logfile
--------------------------------------------------------------------------------
[$logtimenow] Check if post['c_action'] is  not = 0.
LOGTEXT;
c_logging($log,$logtime,$logtype);
// Check for c_action
check_c_action();
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Generate Page
LOGTEXT;
c_logging($log,$logtime,$logtype);
/**
 * Run the script if check_c_action(); didn't exit the script
 */
$config = $_POST['config'];
if ($_POST['c_action']==2) { $setupdatetext = '<h2>'.ED_UPDATING_TO_REV.' '.$setupversion.'</h2>' . PHP_EOL; } else { $setupdatetext = ''; }
OpenSite('Character Selection');
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Check if api info is set
LOGTEXT;
c_logging($log,$logtime,$logtype);
/**
 * Check if api info is set
 */
if ($config['api_user_id'] !="" && $config['api_full_key'] !="") {
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] Build cURL query
LOGTEXT;
  c_logging($log,$logtime,$logtype);
  /**
   * Build cURL query
   */
  $params = array();
  $params['userID'] = $config['api_user_id'];
  $params['apiKey'] = $config['api_full_key'];
  
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
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] Execute cURL query
LOGTEXT;
  c_logging($log,$logtime,$logtype);
  /**
   * Execute cURL query
   */
  $content = curl_exec($ch);
  curl_close($ch);
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] Try create our xml parser from cURL query result
LOGTEXT;
  c_logging($log,$logtime,$logtype);
  /**
   * Try create our xml parser from cURL query result
   */
  try {
    $xml = new SimpleXMLElement($content);
  }
  catch (Exception $e) {
  }

  if ($xml) {
    /**
     * Log where in the setup progress we are
     */
    $logtimenow = date('H:i:s',time());
    $log = <<<LOGTEXT
[$logtimenow] XML data created
LOGTEXT;
    c_logging($log,$logtime,$logtype);
    /**
     * Handle xml data
     */
    if (isset($xml->error)) {
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] XML error: {$xml->error}
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /**
       * xml data error
       */
      echo '<center><font class="warning">'.ERROR.': '.$xml->error.'</font></center>';
    } else {
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] Set hidden post values
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /**
       * Set hidden post values
       */
      echo $setupdatetext
          .'<form action="'.$_SERVER['SCRIPT_NAME'].'?install=go" method="post">' . PHP_EOL;
      foreach ($config as $cfgName=>$cfgValue) {
        echo '<input type="hidden" name="config['.$cfgName.']" value="'.$cfgValue.'" />' . PHP_EOL;
      }
      echo '<input type="hidden" name="logtime" value="'.$_POST['logtime'].'" />' . PHP_EOL
          .'<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
          .'<input type="hidden" name="c_action" value="'.$_POST['c_action'].'" />' . PHP_EOL
          .'<table>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <th colspan="2">'.INSTALLER_CHAR_SELECT.'</th>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] Generate character list
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /**
       * Generate character list
       */
      foreach ($xml->result->rowset->row as $row) {
        echo '<tr>' . PHP_EOL
            .'  <td width="15" style="vertical-align:middle;">' . PHP_EOL
            .'    <input type="radio" name="config[api_char_info]" value="'.$row['name'].'^-_-^'.$row['characterID'].'^-_-^'.$row['corporationName'].'^-_-^'.$row['corporationID'].'" checked="checked" />' . PHP_EOL
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
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] List API Selection for Character
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      echo '</table><br />' . PHP_EOL
      /*
       * List API Selection for Character
       */
          .'<table>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <th colspan="2">'.UPD_CHAR_API_PULL_SELECT.'</th>' . PHP_EOL
          .'  </tr>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">'.UPD_CHAR_API_PULL_SELECT_DES.'</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] Get the selected Character APIs from the database config
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /*
       * Get the selected Character APIs from the database config
       */
      if (isset($conf['charAPIs'])) {
        $charSelectedAPIs = explode(" ",$conf['charAPIs']);
      } else {
        $charSelectedAPIs = array();
      }; // if is set $conf['charAPIs']
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] Build the Character API list
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /*
       * Build the Character API list
       */
      foreach ($charAPIs as $API=>$APIDes) {
			  if (in_array($API,$charSelectedAPIs)) { $check = ' checked="checked"'; } else { $check = ''; }; // if (in_array($API,$corpSelectedAPIs))
				echo '  <tr>' . PHP_EOL
            .'    <td width="15" style="vertical-align:middle;">' . PHP_EOL
            .'      <input type="checkbox" name="config[charAPIs][]" value="'.$API.'"'.$check.' />' . PHP_EOL
            .'    </td>' . PHP_EOL
            .'    <td>'.$APIDes.'</td>' . PHP_EOL
            .'  </tr>' . PHP_EOL;
      }
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] List API Selection for Corporation
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /*
       * List API Selection for Corporation
       */
      echo '</table><br />' . PHP_EOL
          .'<table>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <th colspan="2">'.UPD_CORP_API_PULL_SELECT.'</th>' . PHP_EOL
          .'  </tr>' . PHP_EOL
          .'  <tr>' . PHP_EOL
          .'    <td colspan="2" class="tableinfolbl" style="text-align:center;">'.UPD_CORP_API_PULL_SELECT_DES.'</td>' . PHP_EOL
          .'  </tr>' . PHP_EOL;
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] Get the selected Corporation APIs from the database config
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /*
       * Get the selected Corporation APIs from the database config
       */
      if (isset($conf['corpAPIs'])) {
        $corpSelectedAPIs = explode(" ",$conf['corpAPIs']);
      } else {
        $corpSelectedAPIs = array();
      }; // if is set $conf['corpAPIs']
      /**
       * Log where in the setup progress we are
       */
      $logtimenow = date('H:i:s',time());
      $log = <<<LOGTEXT
[$logtimenow] Build the Corporation API list
LOGTEXT;
      c_logging($log,$logtime,$logtype);
      /*
       * Build the Corporation API list
       */
      foreach ($corpAPIs as $API=>$APIDes) {
			  if (in_array($API,$corpSelectedAPIs)) { $check = ' checked="checked"'; } else { $check = ''; }; // if (in_array($API,$corpSelectedAPIs))
				echo '  <tr>' . PHP_EOL
            .'    <td width="15" style="vertical-align:middle;">' . PHP_EOL
            .'      <input type="checkbox" name="config[corpAPIs][]" value="'.$API.'"'.$check.' />' . PHP_EOL
            .'    </td>' . PHP_EOL
            .'    <td>'.$APIDes.'</td>' . PHP_EOL
            .'  </tr>' . PHP_EOL;
      }
      echo '</table>' . PHP_EOL
      /*
       * Add Submit button
       */
          .'<input type="hidden" name="logtime" value="'.$_POST['logtime'].'" />' . PHP_EOL
          .'<input type="submit" value="'.UPDATE.'" />' . PHP_EOL
          .'</form>' . PHP_EOL;
    };
  } else {
    /**
     * Log where in the setup progress we are
     */
    $logtimenow = date('H:i:s',time());
    $log = <<<LOGTEXT
[$logtimenow] Api server is offline. Try again later
LOGTEXT;
    c_logging($log,$logtime,$logtype);
    /**
     * Api server is offline
     */
    echo '<center><font class="warning">'.INSTALLER_ERROR_API_SERVER_OFFLINE.'</font></center>';
  };
} else {
  /**
   * Log where in the setup progress we are
   */
  $logtimenow = date('H:i:s',time());
  $log = <<<LOGTEXT
[$logtimenow] Api info not set
LOGTEXT;
  c_logging($log,$logtime,$logtype);
  /**
   * Api info not set
   */
  echo INSTALLER_ERROR_NO_API_INFO;
};
CloseSite();
/**
 * Log where in the setup progress we are
 */
$logtimenow = date('H:i:s',time());
$log = <<<LOGTEXT
[$logtimenow] Generate Page Done
LOGTEXT;
c_logging($log,$logtime,$logtype);
?>
