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
 * @copyright  Copyright (c) 2008-2012, Michael Cummings
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
 * @internal Only let this code be included.
 */
if (count(get_included_files()) < 2) {
  $mess = basename(__FILE__)
    . ' must be included it can not be ran directly.' . PHP_EOL;
  if (PHP_SAPI != 'cli') {
    header('HTTP/1.0 403 Forbidden', TRUE, 403);
    die($mess);
  };
  fwrite(STDERR, $mess);
  exit(1);
};
/**
 * Class used to call internal maintenance scripts in Yapeal.
 *
 * @package Yapeal
 * @subpackage maintenance
 */
class SectionMaint extends ASection {
  /**
   * @var array Holds the list of maintenance scripts.
   */
  //private $scriptList;
  /**
   * Constructor
   *
   */
  public function __construct() {
    $this->section = strtolower(str_replace('Section', '', __CLASS__));
    parent::__construct();
    //$this->section = strtolower(str_replace('Section', '', __CLASS__));
    //$path = YAPEAL_CLASS . $this->section . DS;
    //$knownScripts = FilterFileFinder::getStrippedFiles($path, $this->section);
    //$this->scriptList = array_intersect($allowedScripts, $knownScripts);
  }
  /**
   * Function called by Yapeal.php to start section running maintenance scripts.
   *
   * @return bool Returns TRUE if all scripts ran cleanly else FALSE.
   */
  public function pullXML() {
    if ($this->abort === TRUE) {
      return FALSE;
    };
    $scriptCount = 0;
    $scriptSuccess = 0;
    if (count($this->scriptList) == 0) {
      if (Logger::getLogger('yapeal')->isInfoEnabled()) {
        $mess = 'None of the allowed scripts are currently active for '
          . $this->section;
        Logger::getLogger('yapeal')->info($mess);
      };
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
          //$params = array();
          // Use lock to keep from wasting time trying to running scripts that
          // another Yapeal is already working on.
          try {
            $con = YapealDBConnection::connect(YAPEAL_DSN);
            $sql = 'select get_lock(' . $con->qstr($hash) . ',5)';
            if ($con->GetOne($sql) != 1) {
              if (Logger::getLogger('yapeal')->isInfoEnabled()) {
                $mess = 'Failed to get lock for ' . $class . $hash;
                Logger::getLogger('yapeal')->info($mess);
              };
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
          if (Logger::getLogger('yapeal')->isInfoEnabled()) {
            $mess = 'Yapeal has been working very hard and needs a break';
            Logger::getLogger('yapeal')->info($mess);
          };
          exit;
        };// if YAPEAL_MAX_EXECUTE < time() ...
      };// foreach $scripts ...
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->warn($e);
    }
    // Only truly successful if all scripts ran successfully.
    if ($scriptCount == $scriptSuccess) {
      return TRUE;
    };
    return FALSE;
  }// function pullXML
}

