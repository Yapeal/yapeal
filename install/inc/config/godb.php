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
 * @author     Claus Pedersen <satissis@gmail.com>
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
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
    $dbaction = 'Update Tables From';
  } else {
    $dbaction = 'Checking Tables From';
  };
} else {
  $host = $config['DB_Host'];
  $prefix = $config['DB_Prefix'];
  $dbaction = 'Create Tables From';
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
    $output .= '    <td class="tableinfolbl" style="text-align: left;">Database: '.$dbaction.'</td>' . PHP_EOL;
    $output .= '    <td class="notis">'.$schemaFile.'.xml file</td>' . PHP_EOL;
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
      $output .= '    <td class="good">Done</td>' . PHP_EOL;
    } elseif ($result == 1) {
      $output .= '    <td class="warning">Error<br />'.$schemaFile.'.sql file have been created.<br />' . PHP_EOL
			  .'This contain the missed tables.<br />' . PHP_EOL
				.'Use it to create the tables manual.'.'</td>' . PHP_EOL;
 		  $schema->SaveSQL(YAPEAL_CACHE . $schemaFile . '_missed_tables.sql');
      $stop += 1;
    } else {
      $output .= '    <td class="warning">Failed<br />'.$schemaFile
			  . '.xml file was not found<br />' . PHP_EOL . 'or a bad XML file'.'</td>' . PHP_EOL;
 		  $schema->SaveSQL(YAPEAL_CACHE . $schemaFile . '_failed_tables.sql');
      $stop += 1;
    }; // if $result
    $schema = NULL;
    $con->IgnoreErrors($saveErrHandlers);
  };
}; // if DBHandler('CON')

/* ***********************************************************
 * THIS WILL ONLY APPLY IF VERSION IS OLDER THAN REVISION 801
 * Convert old yapeal.ini data to database data
 * Convert old perAPIs data into new format on both char and corp
 * ***********************************************************/
if (isset($ini) && $stop==0 && isset($_POST['updatedb']) && conRev($ini['version'])<=800) {
  // Convert yapeal ini data
  DBHandler('UPDUS', 'utilSections', convertToBool($ini['Api']['account_active']), 'account');
  DBHandler('UPDUS', 'utilSections', convertToBool($ini['Api']['char_active']), 'char');
  DBHandler('UPDUS', 'utilSections', convertToBool($ini['Api']['corp_active']), 'corp');
  DBHandler('UPDUS', 'utilSections', convertToBool($ini['Api']['eve_active']), 'eve');
  DBHandler('UPDUS', 'utilSections', convertToBool($ini['Api']['map_active']), 'map');

  //Convert perAPIs char
  $query = "SELECT `activeAPI`, `characterID` FROM ".$prefix."utilRegisteredCharacter";
  try {
    $rs = $con->Execute($query);
    if ($rs) {
      $data=array();
      while ($arr = $rs->FetchRow()) {
        $data[] = array('activeAPI'=>str_replace('char', '', $arr['activeAPI']), 'characterID'=>$arr['characterID']);
      } // while
      $rs->Close();
      $types=array('activeAPI'=>'X', 'characterID'=>'I');
      $query = BuildInsertUpdateQuery($data, $types, $prefix . 'utilRegisteredCharacter');
      $con->Execute($query);
    }; // if $rs
  } catch (ADODB_Exception $e) {
    trigger_error($e->getMessage(),E_USER_NOTICE);
  }

  //Convert perAPIs corp
  $query = "SELECT `activeAPI`, `corporationID` FROM ".$prefix."utilRegisteredCorporation";
  try {
    $rs = $con->Execute($query);
    if ($rs) {
      $data=array();
      while ($arr = $rs->FetchRow()) {
        $data[] = array('activeAPI'=>str_replace('corp', '', $arr['activeAPI']), 'corporationID'=>$arr['corporationID']);
      } // while
      $rs->Close();
      $types=array('activeAPI'=>'X', 'corporationID'=>'I');
      $query = BuildInsertUpdateQuery($data, $types, $prefix . 'utilRegisteredCorporation');
      $con->Execute($query);
    }; // if $rs
  } catch (ADODB_Exception $e) {
    trigger_error($e->getMessage(),E_USER_NOTICE);
  }
  // Generate yapeal.ini
  if (isset($ini) && $stop==0) {
    $data = array(
              array('Name'=>'charAPIs','Value'=>str_replace('char', '', $charSelAPIs)),
              array('Name'=>'corpAPIs','Value'=>str_replace('corp', '', $corpSelAPIs))
            );
    $types = array('Name'=>'C','Value'=>'C');
    DBHandler('UPD', 'utilConfig', $data, $types);
  }; // if $stop = 0
};
/*
 * Generate yapeal.ini
 */
if (isset($ini) && $stop==0) {
  require("inc".DS."ini_creator.php");
}; // if isset $ini
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
if ($stop == 0) {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
        .'<h2>Database update is done.</h2><br />' . PHP_EOL
        .'<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configdb" method="post">' . PHP_EOL
        .'<input type="submit" value="Go To Database Settings" />' . PHP_EOL
        .'</form>' . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
        .'<h2>Database setup is done.</h2>' . PHP_EOL
        .'<form action="'.$_SERVER['SCRIPT_NAME'].'?funk=configini" method="post">' . PHP_EOL
        .inputHiddenPost()
        .'<input type="submit" value="Next" />' . PHP_EOL
        .'</form>' . PHP_EOL;
  }; // if isset $ini
} else {
  if (isset($ini)) {
    echo '<hr />' . PHP_EOL
      .'<h2>Database update was not completed.</h2><br />' . PHP_EOL
      .'You might have mistyped some info.<br />' . PHP_EOL;
  } else {
    echo '<hr />' . PHP_EOL
        .'<h2>Database setup was not completed.</h2><br />' . PHP_EOL
				.'You might have mistyped some info.<br />' . PHP_EOL;
  }; // if isset $ini
  echo '<hr />' . PHP_EOL
      .'<a href="javascript:history.go(-1)">Go Back</a>' . PHP_EOL;
};
CloseSite();
?>
