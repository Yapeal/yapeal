<?php
/**
 * Use to centeralize all API settings used by pull_*.inc files
 *
 * LICENSE: This file is part of Yapeal.
 *
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
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
$allApis= array(
  'AccountBalance' => array(
    'types' => array(
      'accountID' => 'I',
      'accountKey' => 'I',
      'balance' => 'N',
      'ownerID' => 'I'
    ) ,
    'xpath' => '//row'
  ) ,
  'AllianceList' => array(
    'types' => array(
      'allianceID' => 'I',
      'executorCorpID' => 'I',
      'memberCount' => 'I',
      'name' => 'C',
      'shortName' => 'C',
      'startDate' => 'T'
    ) ,
    'xpath' => '//row'
  ) ,
  'AssetList' => array(
    'types' => array(
      'flag' => 'I',
      'itemID' => 'I',
      'lft' => 'I',
      'locationID' => 'I',
      'ownerID' => 'I',
      'quantity' => 'I',
      'rgt' => 'I',
      'singleton' => 'L',
      'typeID' => 'I'
    ) ,
    'xpath' => '//result'
  ) ,
  'CharacterSheet' => array(
    'types' => array(
      'balance' => 'N',
      'bloodLine' => 'C',
      'characterID' => 'I',
      'cloneName' => 'C',
      'cloneSkillPoints' => 'I',
      'corporationID' => 'I',
      'corporationName' => 'C',
      'gender' => 'C',
      'name' => 'C',
      'race' => 'C',
      'charisma' => 'I',
      'intelligence' => 'I',
      'memory' => 'I',
      'perception' => 'I',
      'willpower' => 'I'
    ) ,
    'xpath' => '//result'
  ) ,
  'ConquerableStationList' => array(
    'types' => array(
      'corporationID' => 'I',
      'corporationName' => 'C',
      'solarSystemID' => 'I',
      'stationID' => 'I',
      'stationName' => 'C',
      'stationTypeID' => 'I'
    ) ,
    'xpath' => '//row'
  ) ,
  'ErrorList' => array(
    'types' => array(
      'errorCode' => 'I',
      'errorC' => 'C'
    ) ,
    'xpath' => '//row'
  ) ,
  'IndustryJobs' => array(
    'types' => array(
      'activityID' => 'I',
      'assemblyLineID' => 'I',
      'beginProductionTime' => 'T',
      'charMaterialMultiplier' => 'N',
      'charTimeMultiplier' => 'N',
      'completed' => 'I',
      'completedStatus' => 'I',
      'completedSuccessfully' => 'I',
      'containerID' => 'I',
      'containerLocationID' => 'I',
      'containerTypeID' => 'I',
      'endProductionTime' => 'T',
      'installedInSolarSystemID' => 'I',
      'installedItemCopy' => 'I',
      'installedItemFlag' => 'I',
      'installedItemID' => 'I',
      'installedItemLicensedProductionRunsRemaining' => 'I',
      'installedItemLocationID' => 'I',
      'installedItemMaterialLevel' => 'I',
      'installedItemProductivityLevel' => 'I',
      'installedItemQuantity' => 'I',
      'installedItemTypeID' => 'I',
      'installerID' => 'I',
      'installTime' => 'T',
      'jobID' => 'I',
      'licensedProductionRuns' => 'I',
      'materialMultiplier' => 'N',
      'outputFlag' => 'I',
      'outputLocationID' => 'I',
      'outputTypeID' => 'I',
      'ownerID' => 'I',
      'pauseProductionTime' => 'T',
      'runs' => 'I',
      'timeMultiplier' => 'N'
    ) ,
    'xpath' => '//row'
  ) ,
  'MarketOrders' => array(
    'types' => array(
      'bid' => 'I',
      'charID' => 'I',
      'duration' => 'I',
      'escrow' => 'N',
      'issued' => 'T',
      'minVolume' => 'I',
      'orderID' => 'I',
      'orderState' => 'I',
      'ownerID' => 'I',
      'price' => 'N',
      'range' => 'I',
      'stationID' => 'I',
      'typeID' => 'I',
      'volEntered' => 'I',
      'volRemaining' => 'I'
    ) ,
    'xpath' => '//row'
  ) ,
  'RefTypes' => array(
    'types' => array(
      'refTypeID' => 'I',
      'refTypeName' => 'C'
    ) ,
    'xpath' => '//row'
  ) ,
  'WalletJournal' => array(
    'beforeid' => 'beforeRefID',
    'types' => array(
      'account' => 'I',
      'amount' => 'N',
      'argID1' => 'I',
      'argName1' => 'C',
      'balance' => 'N',
      'date' => 'T',
      'ownerID' => 'I',
      'ownerID1' => 'I',
      'ownerID2' => 'I',
      'ownerName1' => 'C',
      'ownerName2' => 'C',
      'reason' => 'C',
      'refID' => 'I',
      'refTypeID' => 'I'
    ) ,
    'walkdt' => 'date',
    'walkid' => 'refID',
    'xpath' => '//row'
  ) ,
  'WalletTransactions' => array(
    'beforeid' => 'beforeTransID',
    'types' => array(
      'account' => 'I',
      'clientID' => 'I',
      'clientName' => 'C',
      'ownerID' => 'I',
      'price' => 'N',
      'quantity' => 'I',
      'stationID' => 'I',
      'stationName' => 'C',
      'transactionDateTime' => 'T',
      'transactionFor' => 'C',
      'transactionID' => 'I',
      'transactionType' => 'C',
      'typeID' => 'I',
      'typeName' => 'C'
    ) ,
    'walkdt' => 'transactionDateTime',
    'walkid' => 'transactionID',
    'xpath' => '//row'
  ) ,
  'skills' => array(
    'types' => array(
      'ownerID' => 'I',
      'level' => 'I',
      'skillpoints' => 'I',
      'typeID' => 'I',
      'unpublished' => 'L'
    ) ,
    'xpath' => '//rowset[@name="skills"]/row'
  )
);
?>
