<?php
/* vim: set expandtab tabstop=2 shiftwidth=2 softtabstop=2: */

/**
 * Yapeal installer - Language file Danish.
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
define("CHOSELANGUAGE","V&aelig;lg Sprog");
define("NEXT","Næste");
define("FAILED","Fjelede");
define("OK","Ok");
define("MISSING","Mangler");
define("LOADED","Indlæst");
define("YES","Ja");
define("NO","Nej");
define("SETUP","Setup");
define("DATABASE","Database");
define("HOST","Vært");
define("USERNAME","Brugernavn");
define("PASSWORD","Kodeord");
define("PREFIX","Prefix");
define("CONFIG","Konfiguration");
define("ERROR","Fejl");
define("DONE","Ok");
define("CLOSED","Lukket");
define("SELECTED","Valgt");
define("CONNECTED","Forbindelse Oprettet");
define("LOGIN","Login");
define("UPDATE","Opdater");
define("ON","Aktiv");
define("OFF","Inaktiv");
define("BACK","Tilbage");
/**
 * YAPEAL INSTALLER TEXT'S
 */
define("NOIGB_HEADLINE","Ingen IGB Support");
define("NOIGB_TEXT",'Denne setup kan kun køres fra en normal webbrowser og ikke fra EVE IGB.<br />' . PHP_EOL
                               .'Klik på linket og du vil åbne denne side i en normal webbrowser.<br />' . PHP_EOL);
define("NOIGB_YAPEAL_SETUP","Yapeal Setup");
/**
 * YAPEAL INSTALLER TEXT'S
 */
define("INSTALLER_WELCOME","Velkommen");
define("INSTALLER_WELCOME_TEXT",'<h3>Velkommen til Yapeal Setup.</h3><br />' . PHP_EOL
                               .'Denne setup vil opsætte Yapeal EVE API Library til at kunne køre på din hjemmeside.<br />' . PHP_EOL
                               .'<br />' . PHP_EOL);
define("INSTALLER_PHP_VERSION","PHP version");
define("INSTALLER_PHP_EXT","PHP extension");
define("INSTALLER_REQ_PHP_EXT",'Den påkrævende PHP extension ');
define("INSTALLER_IS_MISS"," mangler!");
define("INSTALLER_HOST_NOT_SUPORTED",'Denne web host kan ikke køre Yapeal.<br />' . PHP_EOL
                                    .'Løsning: Lej en web host som har de påkrævende funktioner<br />' . PHP_EOL
                                    .'eller hvis det er din egen, så opdater/installer de påkrævende funktioner.');
define("INSTALLER_CHMOD_CHECK_FAIL",'Nogle filer eller mapper var skrive beskyttet.<br />' . PHP_EOL
                                   .'Chmod filerne eller mapperne rigtig!');
define("INSTALLER_FILE","Fil");
define("INSTALLER_FILE_TO","fil til");
define("INSTALLER_DIR","Mappe");
define("INSTALLER_DIR_TO","mappe til");
define("INSTALLER_REQ_CHECK","Påkrævnings Kontrol");
define("INSTALLER_REQUIRE","Påkrævet");
define("INSTALLER_RESULT","Resultat");
define("INSTALLER_STATUS","Status");
define("INSTALLER_WRITEABLE","Skrivbarhed");
define("INSTALLER_CHK_FILE_DIR_WRITE_PREM","Kontroler filer og mapper om de er skrivebeskyttet.");
define("INSTALLER_SETUP_HOW_YAPEAL","Konfigurere hvad Yapeal skal gøre");
define("INSTALLER_SAVE_XML_FILES","Gem XML filer");
define("INSTALLER_SAVE_XML_DES",'      Gemmer API XML data i databasen og på hjemmrsiden.<br />' . PHP_EOL
                               .'      "Nej" = Spare hjemmeside plads men tilføjet stadig til databasen.' . PHP_EOL);
define("INSTALLER_GET_ACCOUNT_INFO","Hent Account Info");
define("INSTALLER_GET_ACCOUNT_DES","Gemmer karakter fra API Account til databasen");
define("INSTALLER_GET_CHAR_INFO","Hent Karanter Info");
define("INSTALLER_GET_CHAR_DES","Gemmer Karanter info til databasen");
define("INSTALLER_GET_CORP_INFO","Hent Corp Info");
define("INSTALLER_GET_CORP_DES","Gemmer Corp info til databasen");
define("INSTALLER_GET_EVE_INFO","Hent EvE Info");
define("INSTALLER_GET_EVE_DES","Gemmer EvE info til databasen");
define("INSTALLER_GET_MAP_INFO","Hent Kort Info");
define("INSTALLER_GET_MAP_DES","Gemmer Kort info til databasen");
define("INSTALLER_API_SETUP","API Setup");
define("INSTALLER_GET_API_INFO_HERE","Du kan finde dine API info her");
define("INSTALLER_EVE_API_CENTER","EVE API Center");
define("INSTALLER_API_USERID","API User ID");
define("INSTALLER_API_KEY","API Key");
define("INSTALLER_SETUP_PASS","Setup Kodeord");
define("INSTALLER_SETUP_PASS_DES","Denne kodeord bliver brugt når du vil ændre indstillingerne efter denne setup er færdig.");
define("INSTALLER_CHAR_SELECT","Vælg Karakter");
define("INSTALLER_ERROR_API_SERVER_OFFLINE","Fejl<br>EVE API Server er Offline. Prøv senere.");
define("INSTALLER_RUN_SETUP","Kør Setup");
define("INSTALLER_ERROR_NO_API_INFO","API Info mangler");
define("INSTALLER_PROGRESS","Forløb");
define("INSTALLER_SETUP_DONE",'<h2>Setup er færdig.</h2>' . PHP_EOL
                             .'<br />' . PHP_EOL
                             .'Du kan nu oprette en cronjob på yapeal.php for at hente og gemme dataerne.<br />' . PHP_EOL
                             .'<h3>INFO: yapeal.php kan ikke køre i en webbrowser.</h3>' . PHP_EOL);
define("INSTALLER_SETUP_FAILED",'<h2>Setup fejlede</h2>' . PHP_EOL
                               .'<br />' . PHP_EOL
                               .'Du har måske indtastet nogle forkerte info.<br />' . PHP_EOL);
define("INSTALLER_CREATE_FILE","Opret Fil");
define("INSTALLER_CREATE_ERROR","var ikke oprettet eller ikke en godkendt ini fil");
define("INSTALLER_CONNECT_TO","Opret Forbindelse Til");
define("INSTALLER_SELECT_DB","Vælg Database");
define("INSTALLER_CREATE_TABLE","Opret Tabel");
define("INSTALLER_INSERT_INTO","Indsæt i");
define("INSTALLER_DROP_TABLE","Drop Tabel");
define("INSTALLER_CLOSE_CONNECTION","Luk Forbindelse");
define("INSTALLER_WAS_NOT_FOUND","var ikke fundet");
define("INSTALLER_SELECT_CHAR","Vælg Karakter");
define("INSTALLER_NO_C_ACTION","Gør Intet!");
define("INSTALLER_NO_C_ACTION_DES","Du skal vælge noget andet end \"Gør Intet\"");
define("INSTALLER_MOVE_OLD_DATA","Kopier Data Til");
define("INSTALLER_REMOVE_OLD_TABLES","Fjerner Gamle Tabeller");
define("INSTALLER_OLD_TABLE","Gammel Tabel -> ");
/**
 * Yapeal Config Editor
 */
define("ED_UPDATE_DB","Opdater Database Opsætning");
define("ED_ACTION","Handling");
define("ED_DO_NOTHING","Gør Intet");
define("ED_CLEAN_SETUP","Ren Setup");
define("ED_CLEAN_SETUP_DES","BEMÆRKNING:<br />For at ændre Database navn eller Prefix skal du bruge <font class=\"warning\">\"Ren Setup\"</font>.<br />Dette vil også slette alle dataer!");
define("ED_ACCOUNT_INFO","Account Info");
define("ED_CHAR_INFO","Karanter Info");
define("ED_CORP_INFO","Corp Info");
define("ED_EVE_INFO","Eve Info");
define("ED_MAP_INFO","Kort Info");
define("ED_GET_INFO","Hent Data");
define("ED_DISABLE","Inaktiv");
define("ED_REMOVE_ALL_DATA","Inaktiver og fjern data");
define("ED_DEBUGING","Fejlretning");
define("ED_ONLY_CHANGE_IF","Skift kun hvis nødvendig");
define("ED_UPDATE_FILE","Opdater Fil");
define("ED_UPDATING_DONE",'<h2>Opdateringen er færdig.</h2><br />');
define("ED_UPDATING_FAILED",'<h2>Opdateringen fejlede.</h2><br />' . PHP_EOL
                           .'Du har måske indtastet nogle forkerte info.<br />');
define("ED_GO_TO_CONFIG","Gå Til Konfiguration");
define("ED_UPDATING_FROM_REV","Opdater Fra Revision");
define("ED_UPDATING_TO_REV","Opdater Til Revision");
define("ED_TO_REV","Til Revision");
define("ED_ERROR_NO_DB_SETUP","Der var ingen Database eller en påkrævet tabel mangler!");
define("ED_ERROR_NO_DB_SETUP_DES",'Bruger konfigurationen fra yapeal.ini<br />' . PHP_EOL
                                 .'Den påkrævede tabel mangler eller opsætningen til databasen er opsat forkert i yapeal.ini<br />' . PHP_EOL
                                 .'<div class="warning">Error: ');
define("ED_ERROR_NO_DB_SETUP_DES2",'</div>');
define("ED_ERROR_NO_DB_SETUP_SOLUTION",'<hr />' . PHP_EOL
.'Løsning for denne problem:<br />' . PHP_EOL
.'Enten slet yapeal.ini filen<br />' . PHP_EOL
.'Eller hvis databasen eksister samt tabellen<br />' . PHP_EOL
.'Rediger yapeal.ini filen manuel så database infoen passer.');
/**
 * Yapeal Config Editor
 */
define("UPD_NEW_UPDATE","Ny Opdatering");
define("UPD_NEW_UPDATE_DES","Der er en ny opdatering til databasen.<br />" . PHP_EOL);
define("UPD_ALTER_TABLE","Ændre Tabel");
define("UPD_START_UPDATE","Start Opdatering");
define("UPD_END_UPDATE","Slut Opdatering");
define("UPD_REVISION","Revision");
define("UPD_CHAR_API_PULL_SELECT","Karakter API træk vælger");
define("UPD_CHAR_API_PULL_SELECT_DES","Vælg hvilke API den enkeldte karakter skal hente");
define("UPD_GET_charCharacterSheet_DES","Hent Karakter Ark");
define("UPD_GET_charAccountBalance_DES","Hent Konto Balance fra karakter");
define("UPD_GET_charAssetList_DES","Hent Beholdnings Liste fra karakter");
define("UPD_GET_charIndustryJobs_DES","Hent Industri Opgaver fra karakter");
define("UPD_GET_charKillLog_DES","Hent Kill Log fra karakter");
define("UPD_GET_charMarketOrders_DES","Hent Market Ordere fra karakter");
define("UPD_GET_charSkillQueue_DES","Hent Skill Queue fra karakter");
define("UPD_GET_charStandings_DES","Hent Standings fra karakter");
define("UPD_GET_charWalletJournal_DES","Hent Konto Journal fra karakter");
define("UPD_GET_charWalletTransactions_DES","Hent Konto Transaktioner fra karakter");
define("UPD_CORP_API_PULL_SELECT","Corporation API træk vælger");
define("UPD_CORP_API_PULL_SELECT_DES","Vælg hvilke API corporation skal hente");
define("UPD_GET_corpCorporationSheet_DES","Hent Corporation Ark");
define("UPD_GET_corpAccountBalance_DES","Hent Konto Balance fra corporation");
define("UPD_GET_corpAssetList_DES","Hent Beholdnings Liste fra corporation");
define("UPD_GET_corpIndustryJobs_DES","Hent Industri Opgaver fra corporation");
define("UPD_GET_corpKillLog_DES","Hent Kill Log fra corporation");
define("UPD_GET_corpMarketOrders_DES","Hent Market Ordere fra corporation");
define("UPD_GET_corpMemberTracking_DES","Hent Medlem Liste fra corporation");
define("UPD_GET_corpStandings_DES","Hent Standings fra corporation");
define("UPD_GET_corpStarbaseList_DES","Hent Starbase Liste fra corporation");
define("UPD_GET_corpWalletJournal_DES","Hent Konto Journal fra corporation");
define("UPD_GET_corpWalletTransactions_DES","Hent Konto Transaktioner fra corporation");
?>
