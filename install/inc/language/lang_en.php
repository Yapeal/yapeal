<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Language file English.
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
};
$langStrings = array(
'BACK' => 'Back', 'CHOSELANGUAGE' => 'Choose Language', 'CONFIG' => 'Config',
'CONNECTED' => 'Connected', 'DATABASE' => 'Database', 'DEBUGING' => 'Debugging',
'DISABLED' => 'Disabled', 'DONE' => 'Done', 'ERROR' => 'Error',
'FAILED' => 'Failed', 'FILE' => 'file',
'GOBACK' => '<a href="javascript:history.go(-1)">Go Back</a>',
'HOST' => 'Host', 'LOADED' => 'Loaded', 'LOGIN' => 'Login',
'MISSING' => 'Missing', 'NEXT' => 'Next', 'NO' => 'No', 'OFF' => 'Off',
'OK' => 'Ok', 'ON' => 'On', 'PASSWORD' => 'Password', 'REQUIRE_' => 'Require',
'RESULT' => 'Result', 'PREFIX' => 'Prefix', 'PROGRESS' => 'Progress',
'SETUP' => 'Setup', 'STATUS' => 'Status', 'UPDATE' => 'Update',
'USERNAME' => 'Username', 'WELCOME' => 'Welcome', 'YES' => 'Yes',
'NOIGB_HEADLINE' => 'No IGB Support',
'NOIGB_TEXT' => 'This setup can only be run in a normal browser and not the IGB.<br />' . PHP_EOL
                   .'Press the link and you will be popped out of EVE and this setup will re-openned in a normal browser.<br />' . PHP_EOL,
'NOIGB_YAPEAL_SETUP' => 'Yapeal Setup',
'ACCOUNT_INFO' => 'Account Info', 'API_KEY' => 'API Key',
'API_SETUP' => 'API Setup', 'API_USERID' => 'API User ID',
'CHAR_API_PULL_SELECT' => 'Character API Pull Select',
'CHAR_API_PULL_SELECT_DES' => 'Select what API data, that is needed to be pulled from this character',
'CHAR_INFO' => 'Character Info', 'CHAR_SELECT' => 'Character Select',
'CHECKING_TABLES_FROM' => 'Checking Tables From',
'CHK_FILE_DIR_WRITE_PREM' => 'Checking file and folder write permissions.',
'CHMOD_CHECK_FAIL' => 'Some files or a folder was not writable.<br />' . PHP_EOL
                         .'Chmod the file or folders correctly!',
'CONFIG_MENU' => 'Config Menu', 'CONNECT_TO' => 'Connecting To',
'CORP_API_PULL_SELECT' => 'Corporation API Pull Select',
'CORP_API_PULL_SELECT_DES' => 'Select what corporation API data, that is needed to be pulled',
'CORP_INFO' => 'Corp Info', 'CREATE_FILE' => 'Create File',
'CREATE_TABLES_FROM' => 'Create Tables From',
'CREATED_SQL_ON_MISSED_STUFF' => '.sql file have been created.<br>' . PHP_EOL
                                    .'This contain the missed tables.<br>' . PHP_EOL
                                    .'Use it to create the tables manual.',
'DB_SETTING' => 'Database Settings',
'DB_SETUP_DONE' => '<h2>Database setup is done.</h2>' . PHP_EOL,
'DB_SETUP_FAILED' => '<h2>Database setup was not completed.</h2><br />' . PHP_EOL
                        .'You might have mistyped some info.<br />' . PHP_EOL,
'DB_UPDATING_DONE' => '<h2>Database update is done.</h2><br />',
'DB_UPDATING_FAILED' => '<h2>Database update was not completed.</h2><br />' . PHP_EOL
                           .'You might have mistyped some info.<br />',
'API_UPDATING_DONE' => '<h2>Test character creation/update is done.</h2>',
'API_UPDATING_FAILED' => '<h2>Test character creation/update was not completed.</h2><br />' . PHP_EOL
                           .'You might have mistyped some info.<br />',
'DB_WARNING_CHANGE_DB_NAME_PREFIX_DES' => 'WARNING:<br />' . PHP_EOL
                                             . 'If you change the Host, Database or Prefix,' . PHP_EOL
                                             . 'your old tables and data is still at your old location.<br />' . PHP_EOL
                                             . 'You will need to move the data and drop the tables manual',
'ERROR_API_SERVER_OFFLINE' => 'Error<br>EVE API Server if Offline. Please try later.',
'ERROR_NO_API_INFO' => 'You must provide API Info',
'EVE_API_CENTER' => 'EVE API Center', 'EVE_INFO' => 'Eve Info',
'FINISH_SETUP' => 'Finish Setup',
'GET_API_INFO_HERE' => 'You can get your API info here',
'GET_CHAR_LIST' => 'Get Character List',
'GET_charAccountBalance_DES' => 'Get Account Balance from character',
'GET_charAssetList_DES' => 'Get Asset List from character',
'GET_charCharacterSheet_DES' => 'Get Character Sheet',
'GET_charIndustryJobs_DES' => 'Get Industry Jobs from character',
'GET_charKillLog_DES' => 'Get Kill Log from character',
'GET_charMarketOrders_DES' => 'Get Market Orders from character',
'GET_charSkillQueue_DES' => 'Get Skill Queue from character',
'GET_charStandings_DES' => 'Get Standings from character',
'GET_charWalletJournal_DES' => 'Get Wallet Journal from character',
'GET_charWalletTransactions_DES' => 'Get Wallet Transactions from character',
'GET_corpAccountBalance_DES' => 'Get Account Balance from corporation',
'GET_corpAssetList_DES' => 'Get Asset List from corporation',
'GET_corpCorporationSheet_DES' => 'Get Corporation Sheet',
'GET_corpIndustryJobs_DES' => 'Get Industry Jobs from corporation',
'GET_corpKillLog_DES' => 'Get Kill Log from corporation',
'GET_corpMarketOrders_DES' => 'Get Market Orders from corporation',
'GET_corpMemberTracking_DES' => 'Get Member Tracking from corporation',
'GET_corpStandings_DES' => 'Get Standings from corporation',
'GET_corpStarbaseDetail_DES' => 'Get Starbase Details from corporation',
'GET_corpStarbaseList_DES' => 'Get Starbase List from corporation',
'GET_corpWalletJournal_DES' => 'Get Wallet Journal from corporation',
'GET_corpWalletTransactions_DES' => 'Get Wallet Transactions from corporation',
'GET_DATA' => 'Get Data', 'GO_TO' => 'Go To ',
'HOST_NOT_SUPORTED' => 'This web host does not support Yapeal.<br />' . PHP_EOL
                           .'Solution: Rent a web host that meets the requirement<br />' . PHP_EOL
                           .'or if it\'s your own, then update/install the requirements.',
'INI_CREATE_ERROR' => 'was not created or is not a valid ini file',
'INI_SETUP' => 'yapeal.ini Setup',
'INI_SETUP_DONE' => '<h2>yapeal.ini setup is done.</h2><br />' . PHP_EOL
                        .'You can now setup a Cronjob on yapeal.php to cache all the data.<br />' . PHP_EOL
                        .'<h3>NOTICE: yapeal.php can\'t run in a web browser.</h3>' . PHP_EOL,
'INI_SETUP_FAILED' => '<h2>yapeal.ini setup was not completed.</h2><br />' . PHP_EOL
                         .'You might have mistyped some info.<br />' . PHP_EOL,
'INI_UPDATING_DONE' => '<h2>yapeal.ini update is done.</h2><br />',
'INI_UPDATING_FAILED' => '<h2>yapeal.ini update was not completed.</h2><br />' . PHP_EOL
                            .'You might have mistyped some info.<br />',
'IS_MISS' => ' is missing!', 'MAP_INFO' => 'Map Info',
'NEW_UPDATE' => 'New Update',
'NEW_UPDATE_DES' => 'There is a new update for your database.<hr />' . PHP_EOL,
'ONLY_CHANGE_PASS_IF' => 'Change only if you need a new one',
'PHPVERSION' => 'PHP version', 'PHPEXT' => 'PHP extension',
'REQ_CHECK' => 'Requirement Check', 'REQ_PHP_EXT' => 'The required PHP extension ',
'SAVE_XML_FILES' => 'Save XML files',
'SAVE_XML_DES' => '      Turns on caching of API XML data to local files.<br />' . PHP_EOL
                     .'      "No" = Save web space but still adds to the database.' . PHP_EOL,
'SETUP_PASS' => 'Setup Password',
'SETUP_PASS_DES' => 'This is a password you can use if you need to make changes<br />' . PHP_EOL
                       .'to this setup, when you have completed this setup.',
'SETUP_PASS_DES_BLANK' => '<br />' . PHP_EOL . 'Leave blank to disable the login section.',
'TEST_CHAR' => 'Test Character',
'TEST_CHAR_DES' => 'This is only meant to be used to test Yapeal.<br />' . PHP_EOL
                      .'If you need info on how to add characters to Yapeal so it can pull the info from it,<br />' . PHP_EOL
                      .'look at install/inc/config/configapi.php to see how this page is done<br />' . PHP_EOL
                      .'and install/inc/config/goapi.php to see how it input the data to Yapeal.' . PHP_EOL,
'TYPE_DIR' => 'Dir', 'TYPE_DIR_TO' => 'dir to', 'TYPE_FILE' => 'File',
'TYPE_FILE_TO' => 'file to', 'UPDATE_FILE' => 'Update File',
'UPDATE_TABLES_FROM' => 'Update Tables From',
'WELCOME_TEXT' => '<h3>Welcome to Yapeal Setup.</h3><br />' . PHP_EOL
                     .'This setup will make Yapeal EVE API Library run on your site.<br />' . PHP_EOL
                     .'<br />' . PHP_EOL,
'WRITEABLE' => 'Writable',
'XML_NOT_FOUND_OR_BAD' => '.xml file was not found<br>' . PHP_EOL . 'or a bad XML file'
);
?>
