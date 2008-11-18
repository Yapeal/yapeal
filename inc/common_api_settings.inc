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
if (basename( __FILE__ )==basename($_SERVER['PHP_SELF'])) {
  exit();
};

$allApis=array();

/* ************************************************************************
 * CHAR API
 * ************************************************************************ */

$allApis.=array(
  'char'=>array(
    'AccountBalance'=>array(
      'types'=>array('accountID'=>'integer','accountKey'=>'integer',
        'balance'=>'decimal','ownerID'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'IndustryJobs'=>array(
      'types'=>array(
        'activityID'=>'integer','assemblyLineID'=>'integer',
        'beginProductionTime'=>'timestamp','charMaterialMultiplier'=>'decimal',
        'charTimeMultiplier'=>'decimal','completed'=>'integer',
        'completedStatus'=>'integer','completedSuccessfully'=>'integer',
        'containerID'=>'integer','containerLocationID'=>'integer',
        'containerTypeID'=>'integer','endProductionTime'=>'timestamp',
        'installedInSolarSystemID'=>'integer','installedItemCopy'=>'integer',
        'installedItemFlag'=>'integer','installedItemID'=>'integer',
        'installedItemLicensedProductionRunsRemaining'=>'integer',
        'installedItemlocationID'=>'integer',
        'installedItemMaterialLevel'=>'integer',
        'installedItemProductivityLevel'=>'integer',
        'installedItemQuantity'=>'integer','installedItemTypeID'=>'integer',
        'installerID'=>'integer','installTime'=>'timestamp','jobID'=>'integer',
        'licensedProductionRuns'=>'integer','materialMultiplier'=>'decimal',
        'outputFlag'=>'integer','outputLocationID'=>'integer',
        'outputTypeID'=>'integer','ownerID'=>'integer',
        'pauseProductionTime'=>'timestamp','runs'=>'integer',
        'timeMultiplier'=>'decimal'),
      'xpath'=>'//result/rowset/row'
    ),
    'MarketOrders'=>array(
      'types'=>array(
        'accountKey'=>'integer','bid'=>'integer','charID'=>'integer',
        'duration'=>'integer','escrow'=>'decimal','issued'=>'timestamp',
        'minVolume'=>'integer','orderID'=>'integer','orderState'=>'integer',
        'ownerID'=>'integer','price'=>'decimal','range'=>'integer',
        'stationID'=>'integer','typeID'=>'integer','volEntered'=>'integer',
        'volRemaining'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'WalletJournal'=>array(
      'beforeid'=>'beforeRefID',
      'types'=>array(
        'account'=>'integer','amount'=>'decimal','argID1'=>'integer',
        'argName1'=>'text','balance'=>'decimal','date'=>'timestamp',
        'ownerID'=>'integer','ownerID1'=>'integer','ownerID2'=>'integer',
        'ownerName1'=>'text','ownerName2'=>'text','reason'=>'text',
        'refID'=>'integer','refTypeID'=>'integer'),
      'walkdt'=>'date',
      'walkid'=>'refID',
      'xpath'=>'//result/rowset/row'
    ),
    'WalletTransactions'=>array(
      'beforeid'=>'beforeTransID',
      'types'=>array(
        'account'=>'integer','clientID'=>'integer','clientName'=>'text',
        'ownerID'=>'integer','price'=>'decimal','quantity'=>'integer',
        'stationID'=>'integer','stationName'=>'text',
        'transactionDateTime'=>'timestamp','transactionFor'=>'text',
        'transactionID'=>'integer','transactionType'=>'text',
        'typeID'=>'integer','typeName'=>'text'),
      'walkdt'=>'transactionDateTime',
      'walkid'=>'transactionID',
      'xpath'=>'//result/rowset/row'
    ),
    'AssetList'=>array(
      'types'=>array(
        'flag'=>'integer','itemID'=>'integer','lft'=>'integer',
        'locationID'=>'integer','ownerID'=>'integer','quantity'=>'integer',
        'rgt'=>'integer','singleton'=>'boolean','typeID'=>'integer'),
      'xpath'=>'//result'
    )
  )
);

/* ************************************************************************
 * CORP API
 * ************************************************************************ */

$allApis.=array(
  'corp'=>array(
    'AccountBalance'=>array(
      'types'=>array('accountID'=>'integer','accountKey'=>'integer',
        'balance'=>'decimal','ownerID'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'IndustryJobs'=>array(
      'types'=>array(
        'activityID'=>'integer','assemblyLineID'=>'integer',
        'beginProductionTime'=>'timestamp','charMaterialMultiplier'=>'decimal',
        'charTimeMultiplier'=>'decimal','completed'=>'integer',
        'completedStatus'=>'integer','completedSuccessfully'=>'integer',
        'containerID'=>'integer','containerLocationID'=>'integer',
        'containerTypeID'=>'integer','endProductionTime'=>'timestamp',
        'installedInSolarSystemID'=>'integer','installedItemCopy'=>'integer',
        'installedItemFlag'=>'integer','installedItemID'=>'integer',
        'installedItemLicensedProductionRunsRemaining'=>'integer',
        'installedItemlocationID'=>'integer',
        'installedItemMaterialLevel'=>'integer',
        'installedItemProductivityLevel'=>'integer',
        'installedItemQuantity'=>'integer','installedItemTypeID'=>'integer',
        'installerID'=>'integer','installTime'=>'timestamp','jobID'=>'integer',
        'licensedProductionRuns'=>'integer','materialMultiplier'=>'decimal',
        'outputFlag'=>'integer','outputLocationID'=>'integer',
        'outputTypeID'=>'integer','ownerID'=>'integer',
        'pauseProductionTime'=>'timestamp','runs'=>'integer',
        'timeMultiplier'=>'decimal'),
      'xpath'=>'//result/rowset/row'
    ),
    'MarketOrders'=>array(
      'types'=>array(
        'accountKey'=>'integer','bID'=>'integer','charID'=>'integer',
        'duration'=>'integer','escrow'=>'decimal','issued'=>'timestamp',
        'minVolume'=>'integer','orderID'=>'integer','orderState'=>'integer',
        'ownerID'=>'integer','price'=>'decimal','range'=>'integer',
        'stationID'=>'integer','typeID'=>'integer','volEntered'=>'integer',
        'volRemaining'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'WalletJournal'=>array(
      'beforeid'=>'beforeRefID',
      'types'=>array(
        'account'=>'integer','amount'=>'decimal','argID1'=>'integer',
        'argName1'=>'text','balance'=>'decimal','date'=>'timestamp',
        'ownerID'=>'integer','ownerID1'=>'integer','ownerID2'=>'integer',
        'ownerName1'=>'text','ownerName2'=>'text','reason'=>'text',
        'refID'=>'integer','refTypeID'=>'integer'),
      'walkdt'=>'date',
      'walkid'=>'refID',
      'xpath'=>'//result/rowset/row'
    ),
    'WalletTransactions'=>array(
      'beforeid'=>'beforeTransID',
      'types'=>array(
        'account'=>'integer','clientID'=>'integer','clientName'=>'text',
        'ownerID'=>'integer','price'=>'decimal','quantity'=>'integer',
        'stationID'=>'integer','stationName'=>'text',
        'transactionDateTime'=>'timestamp','transactionFor'=>'text',
        'transactionID'=>'integer','transactionType'=>'text',
        'typeID'=>'integer','typeName'=>'text'),
      'walkdt'=>'transactionDateTime',
      'walkid'=>'transactionID',
      'xpath'=>'//result/rowset/row'
    ),
    'AssetList'=>array(
      'types'=>array(
        'flag'=>'integer','itemID'=>'integer','lft'=>'integer',
        'locationID'=>'integer','ownerID'=>'integer','quantity'=>'integer',
        'rgt'=>'integer','singleton'=>'boolean','typeID'=>'integer'),
      'xpath'=>'//result'
    )
  )
);

/* ************************************************************************
 * EVE API
 * ************************************************************************ */

$allApis.=array(
  'eve'=>array(
    array(
      'AllianceList'=>array(
        'types'=>array(
          'allianceID'=>'integer','executorCorpID'=>'integer',
          'memberCount'=>'integer','name'=>'text','shortName'=>'text',
          'startDate'=>'timestamp'
        ),
        'xpath'=>'//result/rowset/row'
      ),
      'ConquerableStationList'=>array(
        'types'=>array(
          'corporationID'=>'integer','corporationName'=>'text',
          'solarSystemID'=>'integer','stationID'=>'integer','stationName'=>'text',
          'stationTypeID'=>'integer'
          ),
        'xpath'=>'//result/rowset/row'
      ),
      'ErrorList'=>array(
        'types'=>array('errorCode'=>'integer','errorText'=>'text'),
        'xpath'=>'//result/rowset/row'
      ),
      'RefTypes'=>array(
        'types'=>array('refTypeID'=>'integer','refTypeName'=>'text'),
        'xpath'=>'//result/rowset/row'
      )
    )
  )
);

?>
