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
 * Default text
 */
define("CHOSELANGUAGE","Choose Language");
define("NEXT","Next");
define("FAILED","Failed");
define("OK","Ok");
define("MISSING","Missing");
define("LOADED","Loaded");
define("YES","Yes");
define("NO","No");
define("SETUP","Setup");
define("DATABASE","Database");
define("HOST","Host");
define("USERNAME","Username");
define("PASSWORD","Password");
define("PREFIX","Prefix");
define("CONFIG","Config");
define("ERROR","Error");
define("DONE","Done");
define("CLOSED","Closed");
define("SELECTED","Selected");
define("CONNECTED","Connected");
define("LOGIN","Login");
define("UPDATE","Update");
define("ON","On");
define("OFF","Off");
define("BACK","Back");
/**
 * YAPEAL INSTALLER TEXT'S
 */
define("NOIGB_HEADLINE","No IGB Support");
define("NOIGB_TEXT",'This setup can only be run in a normal browser and not the IGB.<br />' . PHP_EOL
                               .'Press the link and you will be popped out of EVE and this setup will re-openned in a normal browser.<br />' . PHP_EOL);
define("NOIGB_YAPEAL_SETUP","Yapeal Setup");
/**
 * YAPEAL INSTALLER TEXT'S
 */
define("INSTALLER_WELCOME","Welcome");
define("INSTALLER_WELCOME_TEXT",'<h3>Welcome to Yapeal Setup.</h3><br />' . PHP_EOL
                               .'This setup will make Yapeal EVE API Library run on your site.<br />' . PHP_EOL
                               .'<br />' . PHP_EOL);
define("INSTALLER_PHP_VERSION","PHP version");
define("INSTALLER_PHP_EXT","PHP extension");
define("INSTALLER_REQ_PHP_EXT",'The required PHP extension ');
define("INSTALLER_IS_MISS"," is missing!");
define("INSTALLER_HOST_NOT_SUPORTED",'This web host does not support Yapeal.<br />' . PHP_EOL
                                    .'Solution: Rent a web host that meets the requirement<br />' . PHP_EOL
                                    .'or if it your own, then update/install the requirements.');
define("INSTALLER_CHMOD_CHECK_FAIL",'Some files or a folder was not writable.<br />' . PHP_EOL
                                   .'Chmod the file or folders correctly!');
define("INSTALLER_FILE","File");
define("INSTALLER_FILE_TO","file to");
define("INSTALLER_DIR","Dir");
define("INSTALLER_DIR_TO","dir to");
define("INSTALLER_REQ_CHECK","Requirement Check");
define("INSTALLER_REQUIRE","Require");
define("INSTALLER_RESULT","Result");
define("INSTALLER_STATUS","Status");
define("INSTALLER_WRITEABLE","Writeable");
define("INSTALLER_CHK_FILE_DIR_WRITE_PREM","Checking file and folder write permissions.");
define("INSTALLER_SETUP_HOW_YAPEAL","Setup how Yapeal should behave");
define("INSTALLER_SAVE_XML_FILES","Save XML files");
define("INSTALLER_SAVE_XML_DES",'      Turns on caching of API XML data to local files.<br />' . PHP_EOL
                               .'      "No" = Save web space but still adds to the database.' . PHP_EOL);
define("INSTALLER_GET_ACCOUNT_INFO","Get Account Info");
define("INSTALLER_GET_ACCOUNT_DES","Save characters from API Account info to database");
define("INSTALLER_GET_CHAR_INFO","Get Character Info");
define("INSTALLER_GET_CHAR_DES","Save Character info to database");
define("INSTALLER_GET_CORP_INFO","Get Corp Info");
define("INSTALLER_GET_CORP_DES","Save Corp info to database");
define("INSTALLER_GET_EVE_INFO","Get Eve Info");
define("INSTALLER_GET_EVE_DES","Save Eve info to database");
define("INSTALLER_GET_MAP_INFO","Get Map Info");
define("INSTALLER_GET_MAP_DES","Save Map info to database");
define("INSTALLER_API_SETUP","API Setup");
define("INSTALLER_GET_API_INFO_HERE","You can get your API info here");
define("INSTALLER_EVE_API_CENTER","EVE API Center");
define("INSTALLER_API_USERID","API User ID");
define("INSTALLER_API_LIMIT_KEY","Limited API Key");
define("INSTALLER_API_FULL_KEY","Full API Key");
define("INSTALLER_SETUP_PASS","Setup Password");
define("INSTALLER_SETUP_PASS_DES","This is a password you can use if you need to make changes to this setup, when you have completed this setup.");
define("INSTALLER_CHAR_SELECT","Character Select");
define("INSTALLER_ERROR_API_SERVER_OFFLINE","Error<br>EVE API Server if Offline. Please try later.");
define("INSTALLER_RUN_SETUP","Run Setup");
define("INSTALLER_ERROR_NO_API_INFO","You must provide API Info");
define("INSTALLER_PROGRESS","Progress");
define("INSTALLER_SETUP_DONE",'<h2>The setup is done.</h2>' . PHP_EOL
                             .'<br />' . PHP_EOL
                             .'You can now setup a Cronjob on yapeal.php to cache all the data.<br />' . PHP_EOL
                             .'<h3>NOTICE: yapeal.php can\'t run in a web browser.</h3>' . PHP_EOL);
define("INSTALLER_SETUP_FAILED",'<h2>The setup was not completed.</h2>' . PHP_EOL
                               .'<br />' . PHP_EOL
                               .'You might have mistyped some info.<br />' . PHP_EOL);
define("INSTALLER_CREATE_FILE","Create File");
define("INSTALLER_CREATE_ERROR","was not created or is not a valid ini file");
define("INSTALLER_CONNECT_TO","Connecting To");
define("INSTALLER_SELECT_DB","Select Database");
define("INSTALLER_CREATE_TABLE","Create Table");
define("INSTALLER_INSERT_INTO","Insert Into");
define("INSTALLER_DROP_TABLE","Drop Table");
define("INSTALLER_CLOSE_CONNECTION","Close Connection");
define("INSTALLER_WAS_NOT_FOUND","was not found");
define("INSTALLER_SELECT_CHAR","Select Character");
define("INSTALLER_NO_C_ACTION","Doing Nothing!");
define("INSTALLER_NO_C_ACTION_DES","You need to select another option than \"Do Nothing\"");
define("INSTALLER_MOVE_OLD_DATA","Move Old Data To");
define("INSTALLER_REMOVE_OLD_TABLES","Remove Old Tables");
define("INSTALLER_FROM_REVISION","From Revision: ");
/**
 * Yapeal Config Editor
 */
define("ED_UPDATE_DB","Update Database Settings");
define("ED_ACTION","Action");
define("ED_DO_NOTHING","Do Nothing");
define("ED_CLEAN_SETUP","Clean Setup");
define("ED_CLEAN_SETUP_DES","NOTISE:<br />To change the Database name or Prefix you need to use the <font class=\"warning\">\"Clean Setup\"</font>.<br />This will allso delete all data!");
define("ED_ACCOUNT_INFO","Account Info");
define("ED_CHAR_INFO","Character Info");
define("ED_CORP_INFO","Corp Info");
define("ED_EVE_INFO","Eve Info");
define("ED_MAP_INFO","Map Info");
define("ED_GET_INFO","Get Data");
define("ED_DISABLE","Disabled");
define("ED_REMOVE_ALL_DATA","Disable and Remove Data");
define("ED_DEBUGING","Debugging");
define("ED_ONLY_CHANGE_IF","Change only if you need a new one");
define("ED_UPDATE_CONFIG_TABLE","Update Config");
define("ED_UPDATE_FILE","Update File");
define("ED_UPDATING_DONE",'<h2>The update is done.</h2><br />');
define("ED_UPDATING_FAILED",'<h2>The update was not completed.</h2><br />' . PHP_EOL
                           .'You might have mistyped some info.<br />');
define("ED_GO_TO_CONFIG","Go To Config");
/**
 * Yapeal Config Editor
 */
define("UPD_NEW_UPDATE","New Update");
define("UPD_NEW_UPDATE_DES","There is a new update for your database.<br />" . PHP_EOL);
?>
