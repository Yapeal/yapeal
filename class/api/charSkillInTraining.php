<?php
/**
 * Class used to fetch and store SkillInTraining API.
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
};
/**
 * Class used to fetch and store CharacterSheet API.
 *
 * @package Yapeal
 * @subpackage Api_character
 */
class charSkillInTraining  extends ACharacter {
  /**
   * @var string Holds the name of the API.
   */
  protected $api = 'SkillInTraining';
  /**
   * Used to store XML to SkillInTraining table.
   *
   * @return boolean Returns TRUE if item was saved to database.
   */
  public function apiStore() {
    global $tracing;
    global $cachetypes;
    $ret = 0;
    $tableName = $this->tablePrefix . $this->api;
    $datum = $this->xml->result;
    if (count($datum) > 0) {
      $data = array('currentTQTime' => YAPEAL_START_TIME, 'offset' => 0,
        'ownerID' => $this->characterID, 'skillInTraining' => 0,
        'trainingDestinationSP' => 0, 'trainingEndTime' => YAPEAL_START_TIME,
        'trainingStartSP' => 0, 'trainingStartTime' => YAPEAL_START_TIME,
        'trainingToLevel' => 0, 'trainingTypeID' => 0);
      foreach ($datum->children() as $k => $v) {
        $data[(string)$k] = (string)$v;
      };
      if (isset($datum->currentTQTime) &&
        isset($datum->currentTQTime['offset'])) {
        $data['offset'] = (string)$datum->currentTQTime['offset'];
      };
      $types = array('currentTQTime' => 'T', 'offset' => 'I', 'ownerID' => 'I',
        'skillInTraining' => 'I', 'trainingDestinationSP' => 'I',
        'trainingEndTime' => 'T', 'trainingStartSP' => 'I',
        'trainingStartTime' => 'T', 'trainingToLevel' => 'I',
        'trainingTypeID' => 'I'
      );
      try {
        $mess = 'Upsert for ' . $tableName;
        $mess .= ' in ' . basename(__FILE__);
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        YapealDBConnection::upsert($data, $types, $tableName, YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        return FALSE;
      }
      $ret = TRUE;
    } else {
    $mess = 'There was no XML data to store for ' . $tableName;
    trigger_error($mess, E_USER_NOTICE);
    $ret = FALSE;
    };// else count $datum ...
    return $ret;
  }// function apiStore()
}
?>
