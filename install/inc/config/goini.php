<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */
/**
 * Yapeal installer - Setup Progress.
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
  /*
   * Update utilSections to be active or deactive
   */
  DBHandler('UPDUS', 'utilSections', $config['api_account'], 'account');
  DBHandler('UPDUS', 'utilSections', $config['api_char'], 'char');
  DBHandler('UPDUS', 'utilSections', $config['api_corp'], 'corp');
  DBHandler('UPDUS', 'utilSections', $config['api_eve'], 'eve');
  DBHandler('UPDUS', 'utilSections', $config['api_map'], 'map');
}; // if DBHandler('CON')

/*
 * Create/Update yapeal.ini
 */
if ($stop==0) {
  require("inc".DS."ini_creator.php");
}; // if $stop = 0

/*
 * Generate Page
 */
OpenSite('Progress');
if (isset($ini)) {
  configMenu();
}; // if isset $ini
echo '<table summary="">'
    .'  <tr>' . PHP_EOL
    .'    <th colspan="3">Progress</th>' . PHP_EOL
    .'  </tr>' . PHP_EOL
    . $output . PHP_EOL
    .'</table>' . PHP_EOL;
if ($stop==0) {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
      . '<h2>Yapeal Setup is updated.</h2><br />' . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
      . '<h2>yapeal.ini setup is done.</h2><br />' . PHP_EOL
      . 'You can now setup a Cronjob on yapeal.php to cache all the data.<br />' . PHP_EOL
      . '<h3>NOTICE: yapeal.php can\'t run in a web browser.</h3>' . PHP_EOL;
  }; // if isset $ini
  echo '<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configini" method="post">' . PHP_EOL
      .'<input type="submit" value="Go to Yapeal Setup" />' . PHP_EOL
      .'</form>' . PHP_EOL;
} else {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
      . '<h2>Yapeal Setup update was not completed.</h2><br />' . PHP_EOL
      . 'You might have mistyped some info.<br />' . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
      . '<h2>Yapeal Setup was not completed.</h2><br />' . PHP_EOL
      . 'You might have mistyped some info.<br />' . PHP_EOL;
  }; // if isset $ini
  echo '<hr />' . PHP_EOL
      .'<a href="javascript:history.go(-1)">Go Back</a>' . PHP_EOL;
};
CloseSite();
?>
