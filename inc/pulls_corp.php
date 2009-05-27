<?php
/**
 * Used to get corp information from Eve-online API.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal.
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
 * @copyright Copyright (c) 2008-2009, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
/**
 * @internal Only let this code be included or required not ran directly.
 */
$sectionFile = basename(__FILE__);
if ($sectionFile == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/****************************************************************************
* Per corp API pulls
****************************************************************************/
$availableApis = array('corpAccountBalance', 'corpAssetList',
  'corpCorporationSheet', 'corpIndustryJobs', 'corpKillLog',
  'corpMarketOrders', 'corpMemberTracking', 'corpStandings',
  'corpStarbaseDetail', 'corpStarbaseList', 'corpWalletJournal',
  'corpWalletTransactions'
);
$apis = explode(' ', $activeAPI);
if (count($apis) == 0) {
  $mess = 'No active APIs listed for ' . $corpID;
  $mess .= ' in ' . $sectionFile;
  trigger_error($mess, E_USER_NOTICE);
  continue;
};
$serverName = 'Tranquility';
require_once YAPEAL_CLASS . 'api' . $ds . 'ACorporation.php';
foreach ($apis as $api) {
  $api = trim($api);
  if (!in_array($api, $availableApis)) {
    $mess = 'Invalid API ' . $api . ' in database table for ' . $corpID;
    $mess .= ' in ' . $sectionFile;
    trigger_error($mess, E_USER_WARNING);
    continue;
  };
  require_once YAPEAL_CLASS . 'api' . $ds . $api . '.php';
  $tableName = YAPEAL_TABLE_PREFIX . $api;
  $mess = 'Before dontWait for ' . $tableName . $corpID;
  $mess .= ' in ' . $sectionFile;
  $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
  // Should we wait to get API data
  if (dontWait($tableName, (int)$corpID)) {
    // Set it so we wait a bit before trying again if something goes wrong.
    $data = array('tableName' => $tableName,
      'ownerID' => (int)$corpID, 'cachedUntil' => YAPEAL_START_TIME);
    $mess = 'Before upsert for ' . $tableName . ' in ' . $sectionFile;
    $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
    $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
    try {
      upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
        YAPEAL_DSN);
    }
    catch(ADODB_Exception $e) {}
  } else {
    continue;
  };// else dontWait ...
  $params = array('apiKey' => $apiKey, 'characterID' => (int)$charID,
    'corporationID' => (int)$corpID, 'serverName' => $serverName,
    'userID' => (int)$userID
  );
  $mess = 'Before instance for ' . $tableName . $corpID;
  $mess .= ' in ' . $sectionFile;
  $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
  $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
  $instance = new $api($params);
  $instance->apiFetch();
  $instance->apiStore();
  $instance = null;
};// foreach $apis ...
?>
