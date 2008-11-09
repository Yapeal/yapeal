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
      'types'=>array('accountid'=>'integer','accountkey'=>'integer',
        'balance'=>'decimal','ownerid'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'IndustryJobs'=>array(
      'types'=>array(
        'activityid'=>'integer','assemblylineid'=>'integer',
        'beginproductiontime'=>'timestamp','charmaterialmultiplier'=>'decimal',
        'chartimemultiplier'=>'decimal','completed'=>'integer',
        'completedstatus'=>'integer','completedsuccessfully'=>'integer',
        'containerid'=>'integer','containerlocationid'=>'integer',
        'containertypeid'=>'integer','endproductiontime'=>'timestamp',
        'installedinsolarsystemid'=>'integer','installeditemcopy'=>'integer',
        'installeditemflag'=>'integer','installeditemid'=>'integer',
        'installeditemlicensedproductionrunsremaining'=>'integer',
        'installeditemlocationid'=>'integer',
        'installeditemmateriallevel'=>'integer',
        'installeditemproductivitylevel'=>'integer',
        'installeditemquantity'=>'integer','installeditemtypeid'=>'integer',
        'installerid'=>'integer','installtime'=>'timestamp','jobid'=>'integer',
        'licensedproductionruns'=>'integer','materialmultiplier'=>'decimal',
        'outputflag'=>'integer','outputlocationid'=>'integer',
        'outputtypeid'=>'integer','ownerid'=>'integer',
        'pauseproductiontime'=>'timestamp','runs'=>'integer',
        'timemultiplier'=>'decimal'),
      'xpath'=>'//result/rowset/row'
    ),
    'MarketOrders'=>array(
      'types'=>array(
        'accountkey'=>'integer','bid'=>'integer','charid'=>'integer',
        'duration'=>'integer','escrow'=>'decimal','issued'=>'timestamp',
        'minvolume'=>'integer','orderid'=>'integer','orderstate'=>'integer',
        'ownerid'=>'integer','price'=>'decimal','range'=>'integer',
        'stationid'=>'integer','typeid'=>'integer','volentered'=>'integer',
        'volremaining'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'WalletJournal'=>array(
      'beforeid'=>'beforeRefID',
      'types'=>array(
        'account'=>'integer','amount'=>'decimal','argid1'=>'integer',
        'argname1'=>'text','balance'=>'decimal','date'=>'timestamp',
        'ownerid'=>'integer','ownerid1'=>'integer','ownerid2'=>'integer',
        'ownername1'=>'text','ownername2'=>'text','reason'=>'text',
        'refid'=>'integer','reftypeid'=>'integer'),
      'walkdt'=>'date',
      'walkid'=>'refID',
      'xpath'=>'//result/rowset/row'
    ),
    'WalletTransactions'=>array(
      'beforeid'=>'beforeTransID',
      'types'=>array(
        'account'=>'integer','clientid'=>'integer','clientname'=>'text',
        'ownerid'=>'integer','price'=>'decimal','quantity'=>'integer',
        'stationid'=>'integer','stationname'=>'text',
        'transactiondatetime'=>'timestamp','transactionfor'=>'text',
        'transactionid'=>'integer','transactiontype'=>'text',
        'typeid'=>'integer','typename'=>'text'),
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
      'types'=>array('accountid'=>'integer','accountkey'=>'integer',
        'balance'=>'decimal','ownerid'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'IndustryJobs'=>array(
      'types'=>array(
        'activityid'=>'integer','assemblylineid'=>'integer',
        'beginproductiontime'=>'timestamp','charmaterialmultiplier'=>'decimal',
        'chartimemultiplier'=>'decimal','completed'=>'integer',
        'completedstatus'=>'integer','completedsuccessfully'=>'integer',
        'containerid'=>'integer','containerlocationid'=>'integer',
        'containertypeid'=>'integer','endproductiontime'=>'timestamp',
        'installedinsolarsystemid'=>'integer','installeditemcopy'=>'integer',
        'installeditemflag'=>'integer','installeditemid'=>'integer',
        'installeditemlicensedproductionrunsremaining'=>'integer',
        'installeditemlocationid'=>'integer',
        'installeditemmateriallevel'=>'integer',
        'installeditemproductivitylevel'=>'integer',
        'installeditemquantity'=>'integer','installeditemtypeid'=>'integer',
        'installerid'=>'integer','installtime'=>'timestamp','jobid'=>'integer',
        'licensedproductionruns'=>'integer','materialmultiplier'=>'decimal',
        'outputflag'=>'integer','outputlocationid'=>'integer',
        'outputtypeid'=>'integer','ownerid'=>'integer',
        'pauseproductiontime'=>'timestamp','runs'=>'integer',
        'timemultiplier'=>'decimal'),
      'xpath'=>'//result/rowset/row'
    ),
    'MarketOrders'=>array(
      'types'=>array(
        'accountkey'=>'integer','bid'=>'integer','charid'=>'integer',
        'duration'=>'integer','escrow'=>'decimal','issued'=>'timestamp',
        'minvolume'=>'integer','orderid'=>'integer','orderstate'=>'integer',
        'ownerid'=>'integer','price'=>'decimal','range'=>'integer',
        'stationid'=>'integer','typeid'=>'integer','volentered'=>'integer',
        'volremaining'=>'integer'),
      'xpath'=>'//result/rowset/row'
    ),
    'WalletJournal'=>array(
      'beforeid'=>'beforeRefID',
      'types'=>array(
        'account'=>'integer','amount'=>'decimal','argid1'=>'integer',
        'argname1'=>'text','balance'=>'decimal','date'=>'timestamp',
        'ownerid'=>'integer','ownerid1'=>'integer','ownerid2'=>'integer',
        'ownername1'=>'text','ownername2'=>'text','reason'=>'text',
        'refid'=>'integer','reftypeid'=>'integer'),
      'walkdt'=>'date',
      'walkid'=>'refID',
      'xpath'=>'//result/rowset/row'
    ),
    'WalletTransactions'=>array(
      'beforeid'=>'beforeTransID',
      'types'=>array(
        'account'=>'integer','clientid'=>'integer','clientname'=>'text',
        'ownerid'=>'integer','price'=>'decimal','quantity'=>'integer',
        'stationid'=>'integer','stationname'=>'text',
        'transactiondatetime'=>'timestamp','transactionfor'=>'text',
        'transactionid'=>'integer','transactiontype'=>'text',
        'typeid'=>'integer','typename'=>'text'),
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
          'allianceid'=>'integer','executorcorpid'=>'integer',
          'membercount'=>'integer','name'=>'text','shortname'=>'text',
          'startdate'=>'timestamp'
        ),
        'xpath'=>'//result/rowset/row'
      ),
      'ConquerableStationList'=>array(
        'types'=>array(
          'corporationid'=>'integer','corporationname'=>'text',
          'solarsystemid'=>'integer','stationid'=>'integer','stationname'=>'text',
          'stationtypeid'=>'integer'
          ),
        'xpath'=>'//result/rowset/row'
      ),
      'ErrorList'=>array(
        'types'=>array('errorcode'=>'integer','errortext'=>'text'),
        'xpath'=>'//result/rowset/row'
      ),
      'RefTypes'=>array(
        'types'=>array('reftypeid'=>'integer','reftypename'=>'text'),
        'xpath'=>'//result/rowset/row'
      )
    )
  )
);

?>
