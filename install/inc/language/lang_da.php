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
 * @subpackage Setup
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
$langStrings = array(
  'BACK' => 'Tilbage', 'CHOSELANGUAGE' => 'Vælg Sprog',
  'CONFIG' => 'Konfiguration',   'CONNECTED' => 'Forbindelse Oprettet', 'DATABASE' => 'Database',
  'DEBUGING', 'Debugging', 'DISABLED' => 'Inaktiv', 'DONE' => 'Færdig',
  'ERROR' => 'Fejl', 'FAILED' => 'Fjelede', 'FILE' => 'fil',
  'GOBACK' => '<a href="javascript:history.go(-1)">Go Back</a>',
  'HOST' => 'Host', 'LOADED' => 'Indlæst', 'LOGIN' => 'Login',
  'MISSING' => 'Mangler', 'NEXT' => 'Næste', 'NO' => 'Nej', 'OFF' => 'Fra',
  'OK' => 'Ok', 'ON' => 'Til', 'PASSWORD' => 'Kodeord',
  'REQUIRE_' => 'Påkrævet', 'RESULT' => 'Resultat', 'PREFIX' => 'Prefix',
  'PROGRESS' => 'Forløb', 'SETUP' => 'Setup', 'STATUS' => 'Status',
  'UPDATE' => 'Opdater', 'USERNAME' => 'Brugernavn', 'WELCOME' => 'Velkommen',
  'YES' => 'Ja', 'NOIGB_HEADLINE' => 'Ingen IGB Support',
  'NOIGB_TEXT' => 'Denne setup kan kun køres fra en normal webbrowser og ikke fra EVE IGB.<br />' . PHP_EOL
                     .'Klik på linket og du vil åbne denne side i en normal webbrowser.<br />' . PHP_EOL,
  'NOIGB_YAPEAL_SETUP' => 'Yapeal Setup',
  'ACCOUNT_INFO' => 'Account Info', 'API_KEY' => 'API Key',
  'API_SETUP' => 'API Setup', 'API_USERID' => 'API Bruger ID',
  'CHAR_API_PULL_SELECT' => 'Karakter API træk vælger',
  'CHAR_API_PULL_SELECT_DES' => 'Vælg hvilke API den enkeldte karakter skal hente',
  'CHAR_INFO' => 'Karanter Info', 'CHAR_SELECT' => 'Vælg Karakter',
  'CHECKING_TABLES_FROM' => 'Kontroler Tabeller Fra',
  'CHK_FILE_DIR_WRITE_PREM' => 'Kontroler filer og mapper om de er skrivebeskyttet.',
  'CHMOD_CHECK_FAIL' => 'Nogle filer eller mapper var skrive beskyttet.<br />' . PHP_EOL
                           .'Chmod filerne eller mapperne rigtig!',
  'CONFIG_MENU' => 'Konfigurations Menu', 'CONNECT_TO' => 'Opret Forbindelse Til',
  'CORP_API_PULL_SELECT' => 'Corporation API træk vælger',
  'CORP_API_PULL_SELECT_DES' => 'Vælg hvilke API corporation skal hente',
  'CORP_INFO' => 'Corp Info', 'CREATE_FILE' => 'Opret Fil',
  'CREATE_TABLES_FROM' => 'Opretter Tabeller Fra',
  'CREATED_SQL_ON_MISSED_STUFF' => '.sql fil er oprettet.<br>'.PHP_EOL
                                      .'Denne indeholder de manglende tabeller.<br>'.PHP_EOL
                                      .'Brug den til at oprette tabellerne manuelt.',
  'DB_SETTING' => 'Database Opsætning',
  'DB_SETUP_DONE' => '<h2>Database setup er færdig.</h2>' . PHP_EOL,
  'DB_SETUP_FAILED' => '<h2>Database setup blev ikke færdig.</h2><br />' . PHP_EOL
                          .'Du har måske indtastet nogle forkerte info.<br />' . PHP_EOL,
  'DB_UPDATING_DONE' => '<h2>Database opdatering er færdig.</h2><br />',
  'DB_UPDATING_FAILED' => '<h2>Database opdatering blev ikke færdig.</h2><br />' . PHP_EOL
                             .'Du har måske indtastet nogle forkerte info.<br />',
  'API_UPDATING_DONE' => '<h2>Test karakter oprettelse/opdatering er færdig.</h2>',
  'API_UPDATING_FAILED' => '<h2>Test karakter oprettelse/opdatering blev ikke færdig.</h2><br />' . PHP_EOL
                             .'Du har måske indtastet nogle forkerte info.<br />',
  'DB_WARNING_CHANGE_DB_NAME_PREFIX_DES' => 'ADVARSEL:<br />' . PHP_EOL
                                               .'Hvis du ændre Host, Database or Prefix,' . PHP_EOL
                                               .'vil dine gamle tabeller stadig være der.<br />' . PHP_EOL
                                               .'Du skal selv flytte dataerne og slette de gamle tabeller.',
  'ERROR_API_SERVER_OFFLINE' => 'Fejl<br>EVE API Server er Offline. Prøv igen senere.',
  'ERROR_NO_API_INFO' => 'Du mangler API Info',
  'EVE_API_CENTER' => 'EVE API Center', 'EVE_INFO' => 'Eve Info',
  'FINISH_SETUP' => 'Færdigør Setup',
  'GET_API_INFO_HERE' => 'Du kan finde dine API info her',
  'GET_CHAR_LIST' => 'Hent Karakter Liste',
  'GET_charAccountBalance_DES' => 'Hent Konto Balance fra karakter',
  'GET_charAssetList_DES' => 'Hent Beholdnings Liste fra karakter',
  'GET_charCharacterSheet_DES' => 'Hent Karakter Ark',
  'GET_charIndustryJobs_DES' => 'Hent Industri Opgaver fra karakter',
  'GET_charKillLog_DES' => 'Hent Kill Log fra karakter',
  'GET_charMarketOrders_DES' => 'Hent Market Ordere fra karakter',
  'GET_charSkillQueue_DES' => 'Hent Skill Queue fra karakter',
  'GET_charStandings_DES' => 'Hent Standings fra karakter',
  'GET_charWalletJournal_DES' => 'Hent Konto Journal fra karakter',
  'GET_charWalletTransactions_DES' => 'Hent Konto Transaktioner fra karakter',
  'GET_corpAccountBalance_DES' => 'Hent Konto Balance fra corporation',
  'GET_corpAssetList_DES' => 'Hent Beholdnings Liste fra corporation',
  'GET_corpCorporationSheet_DES' => 'Hent Corporation Ark',
  'GET_corpIndustryJobs_DES' => 'Hent Industri Opgaver fra corporation',
  'GET_corpKillLog_DES' => 'Hent Kill Log fra corporation',
  'GET_corpMarketOrders_DES' => 'Hent Market Ordere fra corporation',
  'GET_corpMemberTracking_DES' => 'Hent Medlem Liste fra corporation',
  'GET_corpStandings_DES' => 'Hent Standings fra corporation',
  'GET_corpStarbaseList_DES' => 'Hent Starbase Liste fra corporation',
  'GET_corpWalletJournal_DES' => 'Hent Konto Journal fra corporation',
  'GET_corpWalletTransactions_DES' => 'Hent Konto Transaktioner fra corporation',
  'GET_DATA' => 'Hent Data', 'GO_TO' => 'Gå Til ',
  'HOST_NOT_SUPORTED' => 'Denne web host kan ikke køre Yapeal.<br />' . PHP_EOL
                            .'Løsning: Lej en web host som har de påkrævende funktioner<br />' . PHP_EOL
                            .'eller hvis det er din egen, så opdater/installer de påkrævende funktioner.',
  'INI_CREATE_ERROR' => 'blev ikke oprettet eller var ikke en godkendt ini fil',
  'INI_SETUP' => 'yapeal.ini Opsætning',
  'INI_SETUP_DONE' => '<h2>yapeal.ini opsætning er færdig</h2><br />' . PHP_EOL
                          .'Du kan nu oprette en cronjob på yapeal.php for at hente og gemme dataerne.<br />' . PHP_EOL
                          .'<h3>INFO: yapeal.php kan ikke køre i en webbrowser.</h3>' . PHP_EOL,
  'INI_SETUP_FAILED' => '<h2>yapeal.ini opsætning blev ikke færdig.</h2><br />' . PHP_EOL
                           .'Du har måske indtastet nogle forkerte info.<br />' . PHP_EOL,
  'INI_UPDATING_DONE' => '<h2>yapeal.ini opdatering er færdig.</h2><br />',
  'INI_UPDATING_FAILED' => '<h2>yapeal.ini opdatering blev ikke færdig.</h2><br />' . PHP_EOL
                              .'Du har måske indtastet nogle forkerte info.<br />',
  'IS_MISS' => ' mangler!', 'MAP_INFO' => 'Map Info',
  'NEW_UPDATE' => 'Ny Opdatering',
  'NEW_UPDATE_DES' => 'Der er en ny opdatering til Yapeal databasen.<hr />' . PHP_EOL,
  'ONLY_CHANGE_PASS_IF' => 'Skift kun hvis du skal bruge en ny.',
  'PHPVERSION' => 'PHP version', 'PHPEXT' => 'PHP extension',
  'REQ_CHECK' => 'Påkrævenings Kontrol',
  'REQ_PHP_EXT' => 'Den påkrævende PHP extension ',
  'SAVE_XML_FILES' => 'Gem XML filer',
  'SAVE_XML_DES' => '      Gemmer API XML data i databasen og på hjemmrsiden.<br />' . PHP_EOL
                       .'      "Nej" = Spare hjemmeside plads men tilføjet stadig til databasen.' . PHP_EOL,
  'SETUP_PASS' => 'Opsæt Kodeord',
  'SETUP_PASS_DES' => 'Denne kodeord bliver brugt når du vil ændre indstillingerne<br />' . PHP_EOL
                         .'efter denne setup er færdig.',
  'SETUP_PASS_DES_BLANK' => '<br />' . PHP_EOL . 'Lad denne være tom hvis du vil deaktiver konfigurations login.',
  'TEST_CHAR' => 'Test Karakter',
  'TEST_CHAR_DES' => 'Dette er kun til at teste Yapeal med.<br />' . PHP_EOL
                        .'hvis du skal bruge information om hvordan du tilføjer karakter til Yapeal,<br />' . PHP_EOL
                        .'kik i install/inc/config/configapi.php for at se hvordan denne side er lavet<br />' . PHP_EOL
                        .'og install/inc/config/goapi.php for at se hvordan dataerne bliver tilføjet til Yapeal.' . PHP_EOL,
  'TYPE_DIR' => 'Mappe', 'TYPE_DIR_TO' => 'mappen til', 'TYPE_FILE' => 'Fil',
  'TYPE_FILE_TO' => 'filen til', 'UPDATE_FILE' => 'Opdater Fil',
  'UPDATE_TABLES_FROM' => 'Opdater Tabellerne Fra',
  'WELCOME_TEXT' => '<h3>Velkommen til Yapeal Setup.</h3><br />' . PHP_EOL
                       .'Denne setup vil opsætte Yapeal EVE API Library til at kunne køre på din hjemmeside.<br />' . PHP_EOL
                       .'<br />' . PHP_EOL,
  'WRITEABLE' => 'Skrivebeskyttelse',
  'XML_NOT_FOUND_OR_BAD' => '.xml fil blev ikke fundet<br>'.PHP_EOL.'eller en dårlig XML fil'
);
?>
