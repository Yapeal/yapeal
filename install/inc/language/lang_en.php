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
 */

/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
}
/**
 * One word text
 */
define("BACK","Back");
define("CHOSELANGUAGE","Choose Language");
define("CONFIG","Config");
define("CONNECTED","Connected");
define("DATABASE","Database");
define("DEBUGING","Debugging");
define("DISABLED","Disabled");
define("DONE","Done");
define("ERROR","Error");
define("FAILED","Failed");
define("FILE","file");
define("GOBACK",'<a href="javascript:history.go(-1)">Go Back</a>');
define("HOST","Host");
define("LOADED","Loaded");
define("LOGIN","Login");
define("MISSING","Missing");
define("NEXT","Next");
define("NO","No");
define("OFF","Off");
define("OK","Ok");
define("ON","On");
define("PASSWORD","Password");
define("REQUIRE_","Require");
define("RESULT","Result");
define("PREFIX","Prefix");
define("PROGRESS","Progress");
define("SETUP","Setup");
define("STATUS","Status");
define("UPDATE","Update");
define("USERNAME","Username");
define("WELCOME","Welcome");
define("YES","Yes");

/**
 * No IGB section
 */
define("NOIGB_HEADLINE","No IGB Support");
define("NOIGB_TEXT",'This setup can only be run in a normal browser and not the IGB.<br />' . PHP_EOL
                   .'Press the link and you will be popped out of EVE and this setup will re-openned in a normal browser.<br />' . PHP_EOL);
define("NOIGB_YAPEAL_SETUP","Yapeal Setup");

/**
 * Yapeal Setup/Config
 */
define("ACCOUNT_INFO","Account Info");
define("API_KEY","API Key");
define("API_SETUP","API Setup");
define("API_USERID","API User ID");
define("CHAR_API_PULL_SELECT","Character API Pull Select");
define("CHAR_API_PULL_SELECT_DES","Select what API data, that is needed to be pulled from this character");
define("CHAR_INFO","Character Info");
define("CHAR_SELECT","Character Select");
define("CHECKING_TABLES_FROM","Checking Tables From");
define("CHK_FILE_DIR_WRITE_PREM","Checking file and folder write permissions.");
define("CHMOD_CHECK_FAIL",'Some files or a folder was not writable.<br />' . PHP_EOL
                         .'Chmod the file or folders correctly!');
define("CONFIG_MENU","Config Menu");
define("CONNECT_TO","Connecting To");
define("CORP_API_PULL_SELECT","Corporation API Pull Select");
define("CORP_API_PULL_SELECT_DES","Select what corporation API data, that is needed to be pulled");
define("CORP_INFO","Corp Info");
define("CREATE_FILE","Create File");
define("CREATE_TABLES_FROM","Create Tables From");
define("CREATED_SQL_ON_MISSED_STUFF",".sql file have been created.<br>".PHP_EOL
                                    ."This contain the missed tables.<br>".PHP_EOL
                                    ."Use it to create the tables manual.");
define("DB_SETTING","Database Settings");
define("DB_SETUP_DONE",'<h2>Database setup is done.</h2>' . PHP_EOL);
define("DB_SETUP_FAILED",'<h2>Database setup was not completed.</h2><br />' . PHP_EOL
                        .'You might have mistyped some info.<br />' . PHP_EOL);
define("DB_UPDATING_DONE",'<h2>Database update is done.</h2><br />');
define("DB_UPDATING_FAILED",'<h2>Database update was not completed.</h2><br />' . PHP_EOL
                           .'You might have mistyped some info.<br />');
define("API_UPDATING_DONE",'<h2>Test character creation/update is done.</h2>');
define("API_UPDATING_FAILED",'<h2>Test character creation/update was not completed.</h2><br />' . PHP_EOL
                           .'You might have mistyped some info.<br />');
define("DB_WARNING_CHANGE_DB_NAME_PREFIX_DES","WARNING:<br />" . PHP_EOL
                                             ."If you change the Host, Database or Prefix," . PHP_EOL
                                             ."your old tables and data is still at your old location.<br />" . PHP_EOL
                                             ."You will need to move the data and drop the tables manual");
define("ERROR_API_SERVER_OFFLINE","Error<br>EVE API Server if Offline. Please try later.");
define("ERROR_NO_API_INFO","You must provide API Info");
define("EVE_API_CENTER","EVE API Center");
define("EVE_INFO","Eve Info");
define("FINISH_SETUP","Finish Setup");
define("GET_API_INFO_HERE","You can get your API info here");
define("GET_CHAR_LIST","Get Character List");
define("GET_charAccountBalance_DES","Get Account Balance from character");
define("GET_charAssetList_DES","Get Asset List from character");
define("GET_charCharacterSheet_DES","Get Character Sheet");
define("GET_charIndustryJobs_DES","Get Industry Jobs from character");
define("GET_charKillLog_DES","Get Kill Log from character");
define("GET_charMarketOrders_DES","Get Market Orders from character");
define("GET_charSkillQueue_DES","Get Skill Queue from character");
define("GET_charStandings_DES","Get Standings from character");
define("GET_charWalletJournal_DES","Get Wallet Journal from character");
define("GET_charWalletTransactions_DES","Get Wallet Transactions from character");
define("GET_corpAccountBalance_DES","Get Account Balance from corporation");
define("GET_corpAssetList_DES","Get Asset List from corporation");
define("GET_corpCorporationSheet_DES","Get Corporation Sheet");
define("GET_corpIndustryJobs_DES","Get Industry Jobs from corporation");
define("GET_corpKillLog_DES","Get Kill Log from corporation");
define("GET_corpMarketOrders_DES","Get Market Orders from corporation");
define("GET_corpMemberTracking_DES","Get Member Tracking from corporation");
define("GET_corpStandings_DES","Get Standings from corporation");
define("GET_corpStarbaseList_DES","Get Starbase List from corporation");
define("GET_corpWalletJournal_DES","Get Wallet Journal from corporation");
define("GET_corpWalletTransactions_DES","Get Wallet Transactions from corporation");
define("GET_DATA","Get Data");
define("GO_TO","Go To ");
define("HOST_NOT_SUPORTED",'This web host does not support Yapeal.<br />' . PHP_EOL
                           .'Solution: Rent a web host that meets the requirement<br />' . PHP_EOL
                           .'or if it\'s your own, then update/install the requirements.');
define("INI_CREATE_ERROR","was not created or is not a valid ini file");
define("INI_SETUP","yapeal.ini Setup");
define("INI_SETUP_DONE",'<h2>yapeal.ini setup is done.</h2><br />' . PHP_EOL
                        .'You can now setup a Cronjob on yapeal.php to cache all the data.<br />' . PHP_EOL
                        .'<h3>NOTICE: yapeal.php can\'t run in a web browser.</h3>' . PHP_EOL);
define("INI_SETUP_FAILED",'<h2>yapeal.ini setup was not completed.</h2><br />' . PHP_EOL
                         .'You might have mistyped some info.<br />' . PHP_EOL);
define("INI_UPDATING_DONE","<h2>yapeal.ini update is done.</h2><br />");
define("INI_UPDATING_FAILED",'<h2>yapeal.ini update was not completed.</h2><br />' . PHP_EOL
                            .'You might have mistyped some info.<br />');
define("IS_MISS"," is missing!");
define("MAP_INFO","Map Info");
define("NEW_UPDATE","New Update");
define("NEW_UPDATE_DES","There is a new update for your database.<hr />" . PHP_EOL);
define("ONLY_CHANGE_PASS_IF","Change only if you need a new one");
define("PHPVERSION","PHP version");
define("PHPEXT","PHP extension");
define("REQ_CHECK","Requirement Check");
define("REQ_PHP_EXT",'The required PHP extension ');
define("SAVE_XML_FILES","Save XML files");
define("SAVE_XML_DES",'      Turns on caching of API XML data to local files.<br />' . PHP_EOL
                     .'      "No" = Save web space but still adds to the database.' . PHP_EOL);
define("SETUP_PASS","Setup Password");
define("SETUP_PASS_DES","This is a password you can use if you need to make changes<br />" . PHP_EOL
                       ."to this setup, when you have completed this setup.");
define("SETUP_PASS_DES_BLANK","<br />" . PHP_EOL . "Leave blank to disable the login section.");
define("TEST_CHAR","Test Character");
define("TEST_CHAR_DES","This is only meant to be used to test Yapeal.<br />" . PHP_EOL
                      ."If you need info on how to add characters to Yapeal so it can pull the info from it,<br />" . PHP_EOL
                      ."look at install/inc/config/configapi.php to see how this page is done<br />" . PHP_EOL
                      ."and install/inc/config/goapi.php to see how it input the data to Yapeal." . PHP_EOL);
define("TYPE_DIR","Dir");
define("TYPE_DIR_TO","dir to");
define("TYPE_FILE","File");
define("TYPE_FILE_TO","file to");
define("UPDATE_FILE","Update File");
define("UPDATE_TABLES_FROM","Update Tables From");
define("WELCOME_TEXT",'<h3>Welcome to Yapeal Setup.</h3><br />' . PHP_EOL
                     .'This setup will make Yapeal EVE API Library run on your site.<br />' . PHP_EOL
                     .'<br />' . PHP_EOL);
define("WRITEABLE","Writable");
define("XML_NOT_FOUND_OR_BAD",".xml file was not found<br>".PHP_EOL."or a bad XML file");

?>
