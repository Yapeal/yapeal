<?php
/**
 * Class used to fetch and store Corp IndustryJobs API.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
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
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
  exit();
};
/**
 * Class used to fetch and store corp IndustryJobs API.
 *
 * @package Yapeal
 * @subpackage Api_corporation
 */
class corpIndustryJobs extends ACorporation {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'IndustryJobs';
  /**
   * @var array Holds the database column names and ADOdb types.
   */
  private $types = array('activityID' => 'I', 'assemblyLineID' => 'I',
    'beginProductionTime' => 'T', 'charMaterialMultiplier' => 'N',
    'charTimeMultiplier' => 'N', 'completed' => 'I', 'completedStatus' => 'I',
    'completedSuccessfully' => 'I', 'containerID' => 'I',
    'containerLocationID' => 'I', 'containerTypeID' => 'I',
    'endProductionTime' => 'T', 'installedInSolarSystemID' => 'T',
    'installedItemCopy' => 'I', 'installedItemFlag' => 'I',
    'installedItemID' => 'I',
    'installedItemLicensedProductionRunsRemaining' => 'I',
    'installedItemLocationID' => 'I', 'installedItemMaterialLevel' => 'I',
    'installedItemProductivityLevel' => 'I', 'installedItemQuantity' => 'I',
    'installedItemTypeID' => 'I', 'installerID' => 'I', 'installTime' => 'T',
    'jobID' => 'I', 'licensedProductionRuns' => 'I',
    'materialMultiplier' => 'N', 'outputFlag' => 'I', 'outputLocationID' => 'I',
    'outputTypeID' => 'I', 'ownerID' => 'I', 'pauseProductionTime' => 'T',
    'runs' => 'I', 'timeMultiplier' => 'N'
  );
  /**
   * @var string Xpath used to select data from XML.
   */
  private $xpath = '//row';
  /**
   * Used to store XML to IndustryJobs table.
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = FALSE;
    $tableName = $this->tablePrefix . $this->api;
    if ($this->xml instanceof SimpleXMLElement) {
      $mess = 'Xpath for ' . $tableName . ' from corp section in ' . __FILE__;
      $tracing->activeTrace(YAPEAL_TRACE_CORP, 2) &&
      $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
      $datum = $this->xml->xpath($this->xpath);
      if (count($datum) > 0) {
        try {
          $extras = array('ownerID' => $this->corporationID);
          $mess = 'multipleUpsertAttributes for ' . $tableName;
          $mess .= ' from corp section in ' . __FILE__;
          $tracing->activeTrace(YAPEAL_TRACE_CORP, 1) &&
          $tracing->logTrace(YAPEAL_TRACE_CORP, $mess);
          multipleUpsertAttributes($datum, $this->types, $tableName,
            YAPEAL_DSN, $extras);
        }
        catch (ADODB_Exception $e) {
          return FALSE;
        }
        $ret = TRUE;
      } else {
      $mess = 'There was no XML data to store for ' . $tableName;
      $mess .= ' from corp section in ' . __FILE__;
      trigger_error($mess, E_USER_NOTICE);
      $ret = FALSE;
      };// else count $datum ...
      try {
        // Update CachedUntil time since we should have a new one.
        $cuntil = (string)$this->xml->cachedUntil[0];
        $data = array( 'tableName' => $tableName,
          'ownerID' => $this->corporationID, 'cachedUntil' => $cuntil
        );
        $mess = 'Upsert for '. $tableName;
        $mess .= ' from corp section in ' . __FILE__;
        $tracing->activeTrace(YAPEAL_TRACE_CACHE, 0) &&
        $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
        upsert($data, $cachetypes, YAPEAL_TABLE_PREFIX . 'utilCachedUntil',
          YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        // Already logged nothing to do here.
      }
    };// if $this->xml ...
    return $ret;
  }// function apiStore
}
?>
