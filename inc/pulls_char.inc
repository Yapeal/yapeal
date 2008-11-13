<?php
/**
 * Used to get corp information from Eve-online API
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

/* *************************************************************************
 * THESE SETTINGS MAY NEED TO BE CHANGED WHEN PORTING TO NEW SERVER.
 * *************************************************************************/

/**
 * Find path for includes
 */
// Assumes this file is in same directory as common_backend.inc
require_once realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'common_backend.inc';

/* *************************************************************************
 * NOTHING BELOW THIS POINT SHOULD NEED TO BE CHANGED WHEN PORTING TO NEW
 * SERVER. YOU SHOULD ONLY NEED TO CHANGE SETTINGS IN INI FILE.
 * *************************************************************************/

require_once 'DB/NestedSet.php';
require_once YAPEAL_INC.'common_db.inc';
require_once YAPEAL_INC.'elog.inc';
require_once YAPEAL_INC.'eap_functions.inc';

/****************************************************************************
 * Per character API pulls
 ****************************************************************************/

$con=connect(DSN_CHAR_WRITER);

/* Character Sheet */

$api='CharacterSheet';
$cachetypes=array('tablename'=>'C','ownerid'=>'I',
  'cacheduntil'=>'T');
$postdata=array('userID'=>$userid,'apiKey'=>$apikey,'characterID'=>$charid);
// Set the field types of query by name.
$types=array(
  'balance'=>'N','bloodline'=>'C','characterid'=>'I',
  'corporationid'=>'I','corporationname'=>'C','gender'=>'C',
  'name'=>'C','race'=>'C');
// Add attributes
$types.=array('charisma'=>'I','intelligence'=>'I',
  'memory'=>'I','perception'=>'I','willpower'=>'I');
try {
  // Should we wait to get API data
  // No randomness here because other tables depend on this data
  $xml=dontWait($api,$ownerid,false)?getAPIinfo($api,YAPEAL_API_CHAR,$postdata):false;
  if ($xml instanceof SimpleXMLElement) {
    // Update cacheduntil time
    $cuntil=(string)$xml->cachedUntil[0];
    $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
    upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
    // Main table first
    // Must be done first because of foreign key constraints in
    // other tables
    $datum=clone $xml->result[0]->children();
    // Get rid of child table stuff
    unset($datum->rowset,$datum->attributes,$datum->attributeEnhancers);
    $data=array();
    if (count($datum)>0) {
      foreach ($datum as $k=>$v) {
        $data[mb_strtolower($k)]=(string)$v;
      };
    };// if $datum>0
    // Append attributes there's no need to have another table for them.
    /* attributes */
    $dbTable='attributes';
    $datum=$xml->xpath('//result/attributes');
    if (count($datum)>0) {
      foreach ($datum[0]->children() as $k=>$v) {
        $data[mb_strtolower($k)]=(int)$v;
      };
    };// if $datum>0
    if (!count($data)) {
      upsert($data,$types,mb_strtolower($api),DSN_CHAR_WRITER);
    };

    /* Skills */
    $dbTable='skills';
    // Set the field types of query by name.
    $types=array('ownerid'=>'I','level'=>'I',
      'skillpoints'=>'I','typeid'=>'I','unpublished'=>'L');
    $datum=$xml->xpath('//result/rowset[@name="'.$dbTable.'"]/row');
    if (count($datum)>0) {
      $extras=array('ownerid'=>$ownerid,'unpublished'=>0);
      $dbTable=mb_strtolower($dbTable);
      multipleUpsertAttributes($datum,$types,$dbTable,DSN_CHAR_WRITER,$extras);
    };// if $datum>0

  };// if $xml instanceof SimpleXMLElement
}
catch (EVE_API_File_Exception $e) {
  // Set cacheduntil to wait 5 minutes if we couldn't get XML from API
  $cuntil=gmdate('Y-m-d H:i:s',strtotime('5 minutes'));
  $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
  upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
}
catch (EVE_API_Exception $e) {
  // Do nothing use observers to log info
}
catch (PEAR_Exception $e) {
  // Do nothing use observers to log info
}

$apis=array(
  'AccountBalance'=>array(
    'types'=>array('accountid'=>'I','accountkey'=>'I',
      'balance'=>'N','ownerid'=>'I'),
    'xpath'=>'//result/rowset/row'
  ),
  'IndustryJobs'=>array(
    'types'=>array(
      'activityid'=>'I','assemblylineid'=>'I',
      'beginproductiontime'=>'T','charmaterialmultiplier'=>'N',
      'chartimemultiplier'=>'N','completed'=>'I',
      'completedstatus'=>'I','completedsuccessfully'=>'I',
      'containerid'=>'I','containerlocationid'=>'I',
      'containertypeid'=>'I','endproductiontime'=>'T',
      'installedinsolarsystemid'=>'I','installeditemcopy'=>'I',
      'installeditemflag'=>'I','installeditemid'=>'I',
      'installeditemlicensedproductionrunsremaining'=>'I',
      'installeditemlocationid'=>'I',
      'installeditemmateriallevel'=>'I',
      'installeditemproductivitylevel'=>'I',
      'installeditemquantity'=>'I','installeditemtypeid'=>'I',
      'installerid'=>'I','installtime'=>'T','jobid'=>'I',
      'licensedproductionruns'=>'I','materialmultiplier'=>'N',
      'outputflag'=>'I','outputlocationid'=>'I',
      'outputtypeid'=>'I','ownerid'=>'I',
      'pauseproductiontime'=>'T','runs'=>'I',
      'timemultiplier'=>'N'),
    'xpath'=>'//result/rowset/row'
  ),
  'MarketOrders'=>array(
    'types'=>array(
      'accountkey'=>'I','bid'=>'I','charid'=>'I',
      'duration'=>'I','escrow'=>'N','issued'=>'T',
      'minvolume'=>'I','orderid'=>'I','orderstate'=>'I',
      'ownerid'=>'I','price'=>'N','range'=>'I',
      'stationid'=>'I','typeid'=>'I','volentered'=>'I',
      'volremaining'=>'I'),
    'xpath'=>'//result/rowset/row'
  )
);
$postdata=array('userID'=>$userid,'apiKey'=>$apikey,'characterID'=>$charid);
foreach ($apis as $api=>$settings) {
  try {
    // Should we wait to get API data
    $xml=dontWait($api,$ownerid)?getAPIinfo($api,YAPEAL_API_CHAR,$postdata):false;
    if ($xml instanceof SimpleXMLElement) {
      // Now update cacheduntil time
      $cuntil=(string)$xml->cachedUntil[0];
      $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
      upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
      $datum=$xml->xpath($settings['xpath']);
      if (count($datum)>0) {
        $extras=array('ownerid'=>$ownerid);
        multipleUpsertAttributes($datum,$settings['types'],
          mb_strtolower($api),DSN_CHAR_WRITER,$extras);
      };// if $datum>0
    };// if $xml instanceof SimpleXMLElement
  }
  catch (EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 15 minutes if we couldn't get XML from API
    $cuntil=gmdate('Y-m-d H:i:s',strtotime('15 minutes'));
    $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
    upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
  }
  catch (EVE_API_Error_Exception $e) {
    // Some error codes give us a new time to retry after that should be
    // used for cached until time.
    switch ($e->getCode()) {
      case 116:// Industry jobs already downloaded
      case 117:// Market orders already downloaded
        $cuntil=substr($e->getMessage(),-20,19);
        $data=array('tablename'=>$api,'ownerid'=>$ownerid,
          'cacheduntil'=>$cuntil);
        upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
        break;
      default:
        // Do nothing but logging by default
    };// case $e->getCode()
  }
  catch (EVE_API_Exception $e) {
    // Do nothing use observers to log info
  }
  catch (PEAR_Exception $e) {
    // Do nothing use observers to log info
  }
};// foreach $apis ...

$apis=array(
  'WalletJournal'=>array(
    'beforeid'=>'beforeRefID',
    'types'=>array(
      'account'=>'I','amount'=>'N','argid1'=>'I',
      'argname1'=>'C','balance'=>'N','date'=>'T',
      'ownerid'=>'I','ownerid1'=>'I','ownerid2'=>'I',
      'ownername1'=>'C','ownername2'=>'C','reason'=>'C',
      'refid'=>'I','reftypeid'=>'I'),
    'walkdt'=>'date',
    'walkid'=>'refID',
    'xpath'=>'//result/rowset/row'
  ),
  'WalletTransactions'=>array(
    'beforeid'=>'beforeTransID',
    'types'=>array(
      'account'=>'I','clientid'=>'I','clientname'=>'C',
      'ownerid'=>'I','price'=>'N','quantity'=>'I',
      'stationid'=>'I','stationname'=>'C',
      'transactiondatetime'=>'T','transactionfor'=>'C',
      'transactionid'=>'I','transactiontype'=>'C',
      'typeid'=>'I','typename'=>'C'),
    'walkdt'=>'transactionDateTime',
    'walkid'=>'transactionID',
    'xpath'=>'//result/rowset/row'
  )
);
$accounts=array(1000);
foreach ($apis as $api=>$settings) {
  foreach ($accounts as $account) {
    $postdata=array('userID'=>$userid,'apiKey'=>$apikey,
      'characterID'=>$charid,'accountKey'=>$account,
      (string)$settings['beforeid']=>0);
    try {
      // Continue to next if we're not ready at this time
      if (false===dontWait($api.$account,$ownerid)) {
        continue;
      };// if false===dontWait ...
      do {
        $xml=getAPIinfo($api,YAPEAL_API_CHAR,$postdata);
        if ($xml instanceof SimpleXMLElement) {
          // Now update cacheduntil time.
          $cuntil=(string)$xml->cachedUntil[0];
          // Note tablename
          $data=array('tablename'=>$api.$account,'ownerid'=>$ownerid,
            'cacheduntil'=>$cuntil);
          upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
          $datum=$xml->xpath($settings['xpath']);
          $cnt=count($datum);
          $extras=array('ownerid'=>$ownerid,'account'=>$account);
          if ($cnt>0) {
            multipleUpsertAttributes($datum,$settings['types'],
              mb_strtolower($api),DSN_CHAR_WRITER,$extras);
          } else {
            break;// Leave while if no records returned
          };// else $cnt>0
          // Get date/time of last record
          $lastDT=strtotime($datum[$cnt-1][$settings['walkdt']].' +0000');
          $oldest=strtotime('7 days ago');
          // If last record is less than a week old we might be able to
          // continue walking backwards thru records
          if ($oldest<$lastDT) {
            $walkid=(int)$datum[$cnt-1][$settings['walkid']];
            $postdata[(string)$settings['beforeid']]=$walkid;
            // Pause to let CCP figure out we got last 1000 records before
            // trying to getting another batch :P
            sleep(2);
          } else {
            // Leave while loop if we can't walk back anymore.
            $cnt=0;
          };// if $oldest<$lastDT
        };// if $xml instanceof SimpleXMLElement
      } while ($cnt==1000&&isset($postdata[$settings['beforeid']]));
      /*if ((YAPEAL_LOG_LEVEL&E_USER_NOTICE)==E_USER_NOTICE) {
        $mess='Cache until for '.$api.$account.': '.$cuntil;
        print_on_command($mess);
        trigger_error($mess,E_USER_NOTICE);
      };*/
      // API returning wrong cache until time need to set cacheduntil to
      // 60 minutes
      $cuntil=gmdate('Y-m-d H:i:s',strtotime('60 minutes'));
      $data=array('tablename'=>$api.$account,'ownerid'=>$ownerid,
        'cacheduntil'=>$cuntil);
      upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
    }
    catch (EVE_API_Error_Exception $e) {
      // Some error codes give us a new time to retry after that should be
      // used for cached until time.
      switch ($e->getCode()) {
        case 101:// Wallet exhausted
        case 103:// Already returned one week of data
          $cuntil=substr($e->getMessage(),-20,19);
          $data=array('tablename'=>$api.$account,'ownerid'=>$ownerid,
            'cacheduntil'=>$cuntil);
          upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
          break;
        default:
          // Do nothing but logging by default
      };// case $e->getCode()
    }
    catch (EVE_API_File_Exception $e) {
      // Set cacheduntil to wait 15 minutes if we couldn't get XML from API
      $cuntil=gmdate('Y-m-d H:i:s',strtotime('15 minutes'));
      $data=array('tablename'=>$api.$account,'ownerid'=>$ownerid,
        'cacheduntil'=>$cuntil);
      upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
    }
    catch (EVE_API_Exception $e) {
      // Do nothing use observers to log info
    }
    catch (PEAR_Exception $e) {
      // Do nothing use observers to log info
    }
  };// foreach $accounts ...
};// foreach $apis ...

/* Asset List */

// Done last because it can take several minutes to finish.
// TODO: This needs to be rewritten somehow so it can use multiUpsert that
// would make it much faster.
$api='AssetList';
// TODO: Table structure needs to be changed to work for both characters and
// corporations. corporationid changed to ownerid maybe would work.
$params=array(
  'flag'=>'flag','id'=> 'id','itemid'=>'itemid','lft'=>'l',
  'locationid'=>'locationid','lvl'=>'level','norder'=>'norder',
  'ownerid'=>'characterid','quantity'=>'quantity','rgt'=>'r',
  'rootid'=>'rootid','singleton'=>'singleton','typeid'=>'typeid'
);
/*try {
  $nestedSet=& DB_NestedSet::factory('MDB2',DSN_CHAR_WRITER,$params);
  $nestedSet->setAttr(array('node_table'=>'assetlist',
    'lock_table'=>'nested_locks','secondarySort'=>'locationid')
  );
  // Now get API pull and re-build asset list
  $postdata=array('userID'=>$userid,'apiKey'=>$apikey,
    'characterID'=>$charid);
  // Should we wait to get API data
  $xml=dontWait($api,$ownerid)?getAPIinfo($api,YAPEAL_API_CHAR,$postdata):false;
  if ($xml instanceof SimpleXMLElement) {
    // Now update cacheduntil time.
    $cuntil=(string)$xml->cachedUntil[0];
    $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
    upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
    $datum=$xml->xpath('//result/rowset/row');
    if (count($datum)>0) {
      // Clear out old tree for this corp
      $sql='delete';
      $sql.=' from '.mb_strtolower($api);
      $sql.=' where ownerid='.$ownerid;
      $con->exec($sql);
      // Use generated char node as root for tree.
      $chartype=get_charBloodLine($charid);
      $charHQ=get_BloodLineHQ($chartype);
      $nodeData=array(
        'locationid'=>$charHQ,'typeid'=>$chartype,'quantity'=>'1','flag'=>'0',
        'singleton'=>'0','itemid'=>$charid,'ownerid'=>$ownerid
      );
      $parent=$nestedSet->createRootNode($nodeData);
      $location=0;
      foreach ($datum as $row) {
        // Recursive function that does the magic
        addNode($row,$parent,$nestedSet,$ownerid,$location);
      };// foreach $datum ...
    };// if count $datum ...
  };// if $xml instanceof SimpleXMLElement
}
catch (EVE_API_File_Exception $e) {
  // Set cacheduntil to wait 15 minutes if we couldn't get XML from API
  $cuntil=gmdate('Y-m-d H:i:s',strtotime('15 minutes'));
  $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
  upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
}
catch (EVE_API_Error_Exception $e) {
  // Some error codes give us a new time to retry after that should be
  // used for cached until time.
  switch ($e->getCode()) {
    case 115:// Assets already downloaded
      $cuntil=substr($e->getMessage(),-20,19);
      $data=array('tablename'=>$api,'ownerid'=>$ownerid,'cacheduntil'=>$cuntil);
      upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
      break;
    default:
      // Do nothing but logging by default
  };// case $e->getCode()
}
catch (EVE_API_Exception $e) {
  // Do nothing use observers to log info
}
catch (PEAR_Exception $e) {
  // Do nothing use observers to log info
}*/
?>
