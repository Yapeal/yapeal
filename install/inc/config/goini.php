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
 * @subpackage Setup
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
}
/*
 * Get config info
 */
$config = $_POST['config'];
/*
 * Set $output
 */
$output = '';
/*
 * Set $stop
 */
$stop = 0;
/*
 * Set set password and host
 */
if (isset($ini)) {
  if (empty($config['config_pass'])) {
    $inputpass = $conf['password'];
  } else {
    $inputpass = md5($config['config_pass']);
  }
  $host = $ini['Database']['host'];
} else {
  $inputpass = md5($config['config_pass']);
  $host = $config['DB_Host'];
}; // if isset $ini
/*
 * Check DB connection
 */
if (DBHandler('CON',$host)) {
  /*
   * Create/Update setup password
   */
  $data = array(
            array('Name'=>'password','Value'=>$inputpass)
          );
  $types = array('Name'=>'C','Value'=>'C');
  DBHandler('UPD', 'utilConfig', $data, $types);
}; // if DBHandler('CON')

/*
 * Create/Update yapeal.ini
 */
if ($stop==0) {
  require("inc".$ds."ini_creator.php");
}; // if $stop = 0

/*
 * Generate Page
 */
OpenSite(PROGRESS);
if (isset($ini)) {
  configMenu();
}; // if isset $ini
echo '<table>'
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">'.PROGRESS.'</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    . $output . PHP_EOL
    .'</table>' . PHP_EOL;
if ($stop==0) {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
        .INI_UPDATING_DONE . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
        .INI_SETUP_DONE . PHP_EOL;
  }; // if isset $ini
  echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configini" method="post">' . PHP_EOL
      .'<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
      .'<input type="submit" value="'.GO_TO.INI_SETUP.'" />' . PHP_EOL
      .'</form>' . PHP_EOL;
} else {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
        .INI_UPDATING_FAILED . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
        .INI_SETUP_FAILED . PHP_EOL;
  }; // if isset $ini
  echo '<hr />' . PHP_EOL
      .GOBACK . PHP_EOL;
};
CloseSite();
?>
