<?php
/**
 * Contains Section maint class.
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
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2011, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
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
 * Class used to call internal maintenance scripts in Yapeal.
 *
 * @package Yapeal
 * @subpackage maintenance
 */
class SectionMaint {
  /**
   * @var array Holds the list of maintenance scripts.
   */
  private $scriptList;
  /**
   * @var string Hold section name.
   */
  private $section;
  /**
   * Constructor
   *
   * @param array $allowedScripts An array of admin allowed scripts in this
   * section. Used to limit which scripts out of the list of scripts from this
   * section will be ran.
   */
  public function __construct($allowedScripts) {
    $this->section = strtolower(str_replace('Section', '', __CLASS__));
    $path = YAPEAL_CLASS . $this->section . DS;
    $knownScripts = FilterFileFinder::getStrippedFiles($path, $this->section);
    $this->scriptList = array_intersect($allowedScripts, $knownScripts);
  }
  /**
   * Function called by Yapeal.php to start section running maintanance scripts.
   *
   * @return bool Returns TRUE if all scripts ran cleanly else FALSE.
   */
  public function pullXML() {
    $scriptCount = 0;
    $scriptSuccess = 0;
    if (count($this->scriptList) == 0) {
      $mess = 'None of the allowed scripts are currently active for ' . $this->section;
      trigger_error($mess, E_USER_NOTICE);
      return FALSE;
    };
    // Randomize order in which scripts are tried if there is a list.
    if (count($this->scriptList) > 1) {
      shuffle($this->scriptList);
    };
    try {
      foreach ($this->scriptList as $script) {
        // If timer has expired time to run script again.
        if (CachedUntil::cacheExpired($script) === TRUE) {
          ++$scriptCount;
          $class = $this->section . $script;
          $hash = hash('sha1', $class);
          // These are passed on to the script class instance and used as part
          // of hash for lock.
          $params = array();
          // Use lock to keep from wasting time trying to running scripts that
          // another Yapeal is already working on.
          try {
            $con = YapealDBConnection::connect(YAPEAL_DSN);
            $sql = 'select get_lock(' . $con->qstr($hash) . ',5)';
            if ($con->GetOne($sql) != 1) {
              $mess = 'Failed to get lock for ' . $class . $hash;
              trigger_error($mess, E_USER_NOTICE);
              continue;
            };// if $con->GetOne($sql) ...
          }
          catch(ADODB_Exception $e) {
            continue;
          }
          // Give each script 60 seconds to finish. This should never happen but
          // is here to catch runaways.
          set_time_limit(60);
          $instance = new $class();
          if ($instance->doWork()) {
            ++$scriptSuccess;
          };
          $instance = null;
        };// if CachedUntil::cacheExpired...
        // See if Yapeal has been running for longer than 'soft' limit.
        if (YAPEAL_MAX_EXECUTE < time()) {
          $mess = 'Yapeal has been working very hard and needs a break';
          trigger_error($mess, E_USER_NOTICE);
          exit;
        };// if YAPEAL_MAX_EXECUTE < time() ...
      };// foreach $scripts ...
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
    // Only truly successful if all scripts ran successfully.
    if ($scriptCount == $scriptSuccess) {
      return TRUE;
    } else {
      return FALSE;
    }// else $scriptCount == $scriptSuccess ...
  }// function pullXML
}
?>
