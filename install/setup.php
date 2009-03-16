<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal Setup
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

/*
 * Set what version we are using
 */
$setupversion = 643;
/*
 * make a short value for Directory Separators
 */
$DS = DIRECTORY_SEPARATOR;
/*
 * Require the function file
 */
require_once('inc'.$DS.'function.php');
/*
 * Set Language
 */
if (isset($_POST['lang'])) {
  GetLang($_POST['lang']);
} else {
  $_POST['lang'] = GetBrowserLang();
  GetLang($_POST['lang']);
};
/*****************************************************************
 * Define what APIs the Character can pull with a description
 * To use:
 * If you have added a new char API pull, add it down here.
 * array('The name of the API' => the description on that it is.);
 * The description should be defined in the language files!
 *****************************************************************/
$charAPIs = array('charAccountBalance'     => UPD_GET_charAccountBalance_DES,
                  'charAssetList'          => UPD_GET_charAssetList_DES,
                  'charCharacterSheet'     => UPD_GET_charCharacterSheet_DES,
                  'charIndustryJobs'       => UPD_GET_charIndustryJobs_DES,
                  'charMarketOrders'       => UPD_GET_charMarketOrders_DES,
                  'charStandings'          => UPD_GET_charStandings_DES,
                  'charWalletJournal'      => UPD_GET_charWalletJournal_DES,
                  'charWalletTransactions' => UPD_GET_charWalletTransactions_DES);
/*****************************************************************
 * Define what APIs the Corporation can pull with a description
 * To use:
 * If you have added a new corp API pull, add it down here.
 * array('The name of the API' => the description on that it is.);
 * The description should be defined in the language files!
 *****************************************************************/
$corpAPIs = array('corpAccountBalance'     => UPD_GET_corpAccountBalance_DES,
                  'corpAssetList'          => UPD_GET_corpAssetList_DES,
                  'corpCorporationSheet'   => UPD_GET_corpCorporationSheet_DES,
                  'corpIndustryJobs'       => UPD_GET_corpIndustryJobs_DES,
                  'corpMarketOrders'       => UPD_GET_corpMarketOrders_DES,
                  'corpMemberTracking'     => UPD_GET_corpMemberTracking_DES,
                  'corpStandings'          => UPD_GET_corpStandings_DES,
                  'corpStarbaseList'       => UPD_GET_corpStarbaseList_DES,
                  'corpWalletJournal'      => UPD_GET_corpWalletJournal_DES,
                  'corpWalletTransactions' => UPD_GET_corpWalletTransactions_DES);


////////////////////////////////
// Check if Browser is EVE IGB
////////////////////////////////

// Parse agent string by spliting on the '/'
$parts = explode("/", @$_SERVER['HTTP_USER_AGENT']);
// Test for Eve Minibrowser also test against broken Shiva IGB Agent
if (($parts[0] == "EVE-minibrowser") or ($parts[0] == "Python-urllib")) {
  // IGB always sends this set to yes, or no,
  // so if it is missing, we smell something.
  if (!isset($_SERVER['HTTP_EVE_TRUSTED'])) {
    $IGB = false;
  };
  // return true at this point, User Agent matches,
  // and no phishy headers
  $IGB = true;
} else {
  // User Agent, does not match required.
  $IGB = false;
};
// If Ingame Browser
if ($IGB) {
  $log = <<<LOGTEXT
--------------------
Trying to open setup.php from the EVE Ingame Browser.
Retunring error and give a shellexec link to an normal browser.
--------------------
LOGTEXT;
  c_logging($log,date('Y-m-d_H.i.s',time()),'IGB');
  OpenSite(NOIGB_HEADLINE);
  echo NOIGB_TEXT
    .'<a href="shellexec:'.$_SERVER['SCRIPT_NAME'].'">'.NOIGB_YAPEAL_SETUP.'</a>' . PHP_EOL;
  CloseSite();
  // If not the Ingame Browser
} else {
  // Check if there is an existing yapeal.ini file.
  // If so, then tell open Yapeal config updater.
  if (file_exists('..'.$DS.'config'.$DS.'yapeal.ini')) {
    // Config Updater
    require_once('inc'.$DS.'update'.$DS.'main.php');
  } else{
    // Welcome Page
    require_once('inc'.$DS.'install'.$DS.'main.php');
  };
};
?>
