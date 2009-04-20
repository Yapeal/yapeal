<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Setup Progress.
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
$config = $_POST['config'];
/*
 * Get Character infoes
 */
$charinfo = explode("^-_-^",$config['api_char_info']);
$charname = $charinfo[0];
$charid = $charinfo[1];
$corpname = $charinfo[2];
$corpid = $charinfo[3];
/*
 * Build Selected Char APIs
 */
$charSelAPIs = '';
$count = 0;
if (isset($config['charAPIs']) && !empty($config['charAPIs'])) {
  foreach ($config['charAPIs'] as $data) {
    if (!empty($data)) {
      if ($count==0) {
        $charSelAPIs .= $data;
        $count++;
      } else {
        $charSelAPIs .= ' '.$data;
      };
    };
  }
};
/*
 * Build Selected Corp APIs
 */
$corpSelAPIs = '';
$count = 0;
if (isset($config['corpAPIs']) && !empty($config['corpAPIs'])) {
  foreach ($config['corpAPIs'] as $data) {
    if (!empty($data)) {
      if ($count==0) {
        $corpSelAPIs .= $data;
        $count++;
      } else {
        $corpSelAPIs .= ' '.$data;
      };
    };
  }
};
// Stopper
$stop = 0;
$output = "";
/*
 * Check DB connection
 */
if (DBHandler('CON',$ini['Database']['host'])) {
  /*
   * Create/Update utilConfig
   */
  $data = array(
            array('Name'=>'creatorAPIKey','Value'=>$config['api_key']),
            array('Name'=>'creatorAPIuserID','Value'=>$config['api_user_id']),
            array('Name'=>'creatorCharacterID','Value'=>$charid),
            array('Name'=>'creatorCorporationID','Value'=>$corpid),
            array('Name'=>'creatorCorporationName','Value'=>$corpname),
            array('Name'=>'creatorName','Value'=>$charname),
            array('Name'=>'charAPIs','Value'=>$charSelAPIs),
            array('Name'=>'corpAPIs','Value'=>$corpSelAPIs)
          );
  $types = array('Name'=>'C','Value'=>'C');
  DBHandler('UPD', 'utilConfig', $data, $types);
  /*
   * Create/Update utilRegisteredCharacter
   */
  $data = array(
            array('activeAPI'=>$charSelAPIs,'characterID'=>$charid,'corporationID'=>$corpid,'corporationName'=>$corpname,'isActive'=>1,'name'=>$charname,'userID'=>$config['api_user_id'])
          );
  $types = array('activeAPI'=>'X','characterID'=>'I','corporationID'=>'I','corporationName'=>'C','isActive'=>'I','name'=>'C','userID'=>'I');
  DBHandler('UPD', 'utilRegisteredCharacter', $data, $types);
  /*
   * Create/Update utilRegisteredCorporation
   */
  $data = array(
            array('activeAPI'=>$corpSelAPIs,'characterID'=>$charid,'corporationID'=>$corpid,'isActive'=>1)
          );
  $types = array('activeAPI'=>'X','characterID'=>'I','corporationID'=>'I','isActive'=>'I');
  DBHandler('UPD', 'utilRegisteredCorporation', $data, $types);
  /*
   * Create/Update utilRegisteredUser
   */
  $data = array(
            array('fullApiKey'=>$config['api_key'],'userID'=>$config['api_user_id'])
          );
  $types = array('fullApiKey'=>'C','userID'=>'I');
  DBHandler('UPD', 'utilRegisteredUser', $data, $types);
}; // if DBHandler('CON')
/*
 * Generate Page
 */
OpenSite(PROGRESS);
configMenu();
echo '<table>'
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">'.PROGRESS.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    . $output . PHP_EOL
    .'</table>' . PHP_EOL;
if ($stop==0) {
  echo '<hr />' . PHP_EOL
      .API_UPDATING_DONE . PHP_EOL
      .'<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configapi" method="post">' . PHP_EOL
      .'<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'<input type="submit" value="'.GO_TO.TEST_CHAR.'" />' . PHP_EOL
      .'</form>' . PHP_EOL;
} else {
  echo '<hr />' . PHP_EOL
      .API_UPDATING_FAILED . PHP_EOL
      .'<hr />' . PHP_EOL
      .GOBACK . PHP_EOL;
};
CloseSite();
?>
