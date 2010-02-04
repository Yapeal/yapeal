<?php
/**
 * Yapeal Setup - Values
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
 * @copyright  Copyright (c) 2008-2010, Claus Pedersen, Michael Cummings
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
 * Set what version we are using
 */
$setupversion = 939;
/*
 * config tables used to test if this is an older version
 */
$cfgtables = array('utilconfig', 'utilConfig');
/*
 * This is schema xml files to use in AXMLS + it's current version
 */
$schemas = array('util'=>939, 'account'=>939, 'char'=>939, 'corp'=>939,
  'eve'=>939, 'map'=>939, 'server'=>939);
/*****************************************************************
 * Define what APIs the Character can pull with a description
 * To use:
 * If you have added a new char API pull, add it down here.
 * array('The name of the API' => the description on what it is.);
 * The description should be defined in the language files!
 *****************************************************************/
$charAPIs = array('AccountBalance'     => 'Get Account Balance from character',
                  'AssetList'          => 'Get Asset List from character',
                  'CharacterSheet'     => 'Get Character Sheet',
                  'IndustryJobs'       => 'Get Industry Jobs from character',
                  'KillLog'            => 'Get Kill Log from character',
                  'MailingLists'       => 'Get list of Mailing Lists subscriptions',
                  'MailMessages'       => 'Get list of Mail Messages headers',
                  'MarketOrders'       => 'Get Market Orders from character',
                  'Notifications'      => 'Get list of Notifications from character',
                  'SkillInTraining'    => 'Get Skill In Training from character',
                  'SkillQueue'         => 'Get Skill Queue from character',
                  'Standings'          => 'Get Standings from character',
                  'WalletJournal'      => 'Get Wallet Journal from character',
                  'WalletTransactions' => 'Get Wallet Transactions from character');
/*****************************************************************
 * Define what APIs the Corporation can pull with a description
 * To use:
 * If you have added a new corp API pull, add it down here.
 * array('The name of the API' => the description on what it is.);
 * The description should be defined in the language files!
 *****************************************************************/
$corpAPIs = array('AccountBalance'     => 'Get Account Balance from corporation',
                  'AssetList'          => 'Get Asset List from corporation',
                  'CorporationSheet'   => 'Get Corporation Sheet',
                  'IndustryJobs'       => 'Get Industry Jobs from corporation',
                  'KillLog'            => 'Get Kill Log from corporation',
                  'MarketOrders'       => 'Get Market Orders from corporation',
                  'MemberTracking'     => 'Get Member Tracking from corporation',
                  'Standings'          => 'Get Standings from corporation',
                  'StarbaseDetail'     => 'Get Starbase Details from corporation',
                  'StarbaseList'       => 'Get Starbase List from corporation',
                  'WalletJournal'      => 'Get Wallet Journal from corporation',
                  'WalletTransactions' => 'Get Wallet Transactions from corporation');
/*
 * Require common_paths.php to define the path in yapeal
 */
require_once('..' . DS . 'inc' . DS . 'common_paths.php');
/*
 * Require adodb.inc.php to be able to create connection to db
 */
require_once(YAPEAL_ADODB . 'adodb.inc.php');
/*
 * Require adodb-xmlschema03.inc.php to be able to use AXMLS
 */
require_once(YAPEAL_ADODB . 'adodb-xmlschema03.inc.php');
?>
