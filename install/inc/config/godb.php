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
if (isset($_POST['config'])) {
  $config = $_POST['config'];
};
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
  $host = $ini['Database']['host'];
  $prefix = $ini['Database']['table_prefix'];
  if (isset($config['DB_Prefix'])) { $prefix = $config['DB_Prefix']; }
  if (isset($_POST['updatedb'])) {
    $dbaction = UPDATE_TABLES_FROM;
  } else {
    $dbaction = CHECKING_TABLES_FROM;
  };
} else {
  $host = $config['DB_Host'];
  $prefix = $config['DB_Prefix'];
  $dbaction = CREATE_TABLES_FROM;
}; // if isset $ini
/*
 * Check DB connection
 */
if (DBHandler('CON',$host)) {
  /*
   * Create/Update Tables
   */
  foreach ($schemas as $schemaFile=>$schemaVersion) {
    /*
     * Create/Update Tables
     */
    $saveErrHandlers = $con->IgnoreErrors();
    $output .= '  <tr>' . PHP_EOL;
    $output .= '    <td class="tableinfolbl" style="text-align: left;">'.DATABASE.': '.$dbaction.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$schemaFile.'.xml '.FILE.'</td>' . PHP_EOL;
    $schema = new adoSchema($con);
    //$schema->debug = false;
    $schema->ExecuteInline(FALSE);
    $schema->ContinueOnError(FALSE);
    $schema->SetUpgradeMethod('ALTER');
    $schema->SetPrefix($prefix, FALSE);
    $xml = file_get_contents($schemaFile . '.xml');
    $sql = $schema->ParseSchemaString($xml);
    $schema->SaveSQL(YAPEAL_CACHE . $schemaFile . '.sql');
    $result = $schema->ExecuteSchema($sql);
    if ($result == 2) {
      $output .= '    <td class="good">'.DONE.'</td>' . PHP_EOL;
    } elseif ($result == 1) {
      $output .= '    <td class="warning">'.ERROR.'<br />'.$schemaFile.CREATED_SQL_ON_MISSED_STUFF.'</td>' . PHP_EOL;
 		$schema->SaveSQL(YAPEAL_CACHE . $schemaFile . '_missed_tables.sql');
      $stop += 1;
    } else {
      $output .= '    <td class="warning">'.FAILED.'<br />'.$schemaFile.XML_NOT_FOUND_OR_BAD.'</td>' . PHP_EOL;
 		$schema->SaveSQL(YAPEAL_CACHE . $schemaFile . '_failed_tables.sql');
      $stop += 1;
    }; // if $result
    $schema = NULL;
    $con->IgnoreErrors($saveErrHandlers);
  };
}; // if DBHandler('CON')
/*
 * Generate yapeal.ini
 */
if (isset($ini) && $stop==0) {
  require("inc".$ds."ini_creator.php");
}; // if isset $ini
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
if ($stop == 0) {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
        .DB_UPDATING_DONE . PHP_EOL
        .'<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configdb" method="post">' . PHP_EOL
        .'<input type="hidden" name="lang" value="'.$_POST['lang'].'" />' . PHP_EOL
        .'<input type="submit" value="'.GO_TO.DB_SETTING.'" />' . PHP_EOL
        .'</form>' . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
        .DB_SETUP_DONE . PHP_EOL
        .'<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configini" method="post">' . PHP_EOL
        .inputHiddenPost()
        .'<input type="submit" value="'.NEXT.'" />' . PHP_EOL
        .'</form>' . PHP_EOL;
  }; // if isset $ini
} else {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
        .DB_UPDATING_FAILED . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
        .DB_SETUP_FAILED . PHP_EOL;
  }; // if isset $ini
  echo '<hr />' . PHP_EOL
      .GOBACK . PHP_EOL;
};
CloseSite();
?>
