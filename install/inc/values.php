<?php
/**
 * Yapeal Setup - Values
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
 * Set what version we are using
 */
$setupversion = 800;
/*
 * config tables used to test if this is an older version
 */
$cfgtables = array('utilconfig', 'utilConfig');
/*
 * This is schema xml files to use in AXMLS + it's current version
 */
$schemas = array('util'=>753, 'account'=>753, 'char'=>786, 'corp'=>800,
  'eve'=>753, 'map'=>753, 'server'=>753);
/*****************************************************************
 * Define what APIs the Character can pull with a description
 * To use:
 * If you have added a new char API pull, add it down here.
 * array('The name of the API' => the description on what it is.);
 * The description should be defined in the language files!
 *****************************************************************/
$charAPIs = array('charAccountBalance'     => GET_charAccountBalance_DES,
                  'charAssetList'          => GET_charAssetList_DES,
                  'charCharacterSheet'     => GET_charCharacterSheet_DES,
                  'charIndustryJobs'       => GET_charIndustryJobs_DES,
                  'charKillLog'            => GET_charKillLog_DES,
                  'charMarketOrders'       => GET_charMarketOrders_DES,
                  'charSkillQueue'         => GET_charSkillQueue_DES,
                  'charStandings'          => GET_charStandings_DES,
                  'charWalletJournal'      => GET_charWalletJournal_DES,
                  'charWalletTransactions' => GET_charWalletTransactions_DES);
/*****************************************************************
 * Define what APIs the Corporation can pull with a description
 * To use:
 * If you have added a new corp API pull, add it down here.
 * array('The name of the API' => the description on what it is.);
 * The description should be defined in the language files!
 *****************************************************************/
$corpAPIs = array('corpAccountBalance'     => GET_corpAccountBalance_DES,
                  'corpAssetList'          => GET_corpAssetList_DES,
                  'corpCorporationSheet'   => GET_corpCorporationSheet_DES,
                  'corpIndustryJobs'       => GET_corpIndustryJobs_DES,
                  'corpKillLog'            => GET_corpKillLog_DES,
                  'corpMarketOrders'       => GET_corpMarketOrders_DES,
                  'corpMemberTracking'     => GET_corpMemberTracking_DES,
                  'corpStandings'          => GET_corpStandings_DES,
                  'corpStarbaseDetail'     => GET_corpStarbaseDetail_DES,
                  'corpStarbaseList'       => GET_corpStarbaseList_DES,
                  'corpWalletJournal'      => GET_corpWalletJournal_DES,
                  'corpWalletTransactions' => GET_corpWalletTransactions_DES);
/*
 * Require common_paths.php to define the path in yapeal
 */
require_once('..' . $ds . 'inc' . $ds . 'common_paths.php');
/*
 * Require adodb.inc.php to be able to create connection to db
 */
require_once(YAPEAL_ADODB . 'adodb.inc.php');
/*
 * Require adodb-xmlschema03.inc.php to be able to use AXMLS
 */
require_once(YAPEAL_ADODB . 'adodb-xmlschema03.inc.php');
?>
