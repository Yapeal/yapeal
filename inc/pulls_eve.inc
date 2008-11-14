<?php
/**
 * Used to get eve information from Eve-online API
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

require_once YAPEAL_INC.'common_db.inc';
require_once YAPEAL_INC.'elog.inc';
require_once YAPEAL_INC.'eap_functions.inc';

/* **************************************************************************
 * Global API pulls
 * **************************************************************************/

$con=connect(DSN_EVE_WRITER);

$apis=array(
  'AllianceList'=>array(
    'types'=>array(
      'allianceid'=>'I','executorcorpid'=>'I',
      'membercount'=>'I','name'=>'C','shortname'=>'C',
      'startdate'=>'T'
    ),
    'xpath'=>'//result/rowset/row'
  ),
  'ConquerableStationList'=>array(
    'types'=>array(
      'corporationid'=>'I','corporationname'=>'C',
      'solarsystemid'=>'I','stationid'=>'I','stationname'=>'C',
      'stationtypeid'=>'I'
      ),
    'xpath'=>'//result/rowset/row'
  ),
  'ErrorList'=>array(
    'types'=>array('errorcode'=>'I','errortext'=>'C'),
    'xpath'=>'//result/rowset/row'
  ),
  'RefTypes'=>array(
    'types'=>array('reftypeid'=>'I','reftypename'=>'C'),
    'xpath'=>'//result/rowset/row'
  )
);
foreach ($apis as $api=>$settings) {
  try {
    // Should we wait to get API data
    $xml=dontWait($api)?getAPIinfo($api,YAPEAL_API_EVE):false;
    if ($xml instanceof SimpleXMLElement) {
      // Update cacheduntil time.
      $cuntil=(string)$xml->cachedUntil[0];
      $data=array('tablename'=>$api,'ownerid'=>0,'cacheduntil'=>$cuntil);
      upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
      $datum=$xml->xpath($settings['xpath']);
      if (count($datum)>0) {
        // Empty out old data then upsert (insert) new
        $sql='truncate table '.mb_strtolower($api);
        print_on_command('Before trucate');
        $con->Execute($sql);
        print_on_command('After trucate');
        multipleUpsertAttributes($datum,$settings['types'],mb_strtolower($api),DSN_EVE_WRITER);
      };// if count $datum ...
    };// if $xml instanceof SimpleXMLElement
  }
  catch (EVE_API_File_Exception $e) {
    // Set cacheduntil to wait 15 minutes if we couldn't get XML from API
    $cuntil=gmdate('Y-m-d H:i:s',strtotime('15 minutes'));
    $data=array('tablename'=>$api,'ownerid'=>0,'cacheduntil'=>$cuntil);
    upsert($data,$cachetypes,'cacheduntil',DSN_UTIL_WRITER);
  }
  catch (EVE_API_Exception $e) {
    // Do nothing use observers to log info
  }
  catch (ADODB_Exception $e) {
    // Do nothing use observers to log info
  }
};// foreach $apis ...
?>
