<?php
/**
 * Contains Section Char class.
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
 * Class used to pull Eve APIs for char section.
 *
 * @package Yapeal
 * @subpackage Api_sections
 */
class SectionChar {
  /**
   * @var array Holds the list of APIs for this section.
   */
  private $apiList = array();
  /**
   * @var string Hold proxy string to pass to this section's APIs.
   */
  private $proxy = '';
  /**
   * @var string Hold section name.
   */
  private $section = '';
  /**
   * @var string Holds the Eve server name.
   */
  private $serverName = '';
  /**
   * Constructor
   *
   * @param string $proxy Allows overriding API server for example to use a
   * different proxy on a per char/corp basis. It should contain a url format
   * string made to used in sprintf() to replace %1$s with $api and %2$s with
   * $section as needed to complete the url. For example:
   * 'http://api.eve-online.com/%2$s/%1$s.xml.aspx' for normal Eve API server.
   * @param array $allowedAPIs An array of admin allowed APIs in this section.
   * Used to limit which APIs out of the list of APIs from this section will be
   * fetched.
   */
  public function __construct($proxy, $allowedAPIs) {
    $this->section ='char';
    $path = YAPEAL_CLASS . 'api' . DS;
    $knownApis = FilterFileFinder::getStrippedFiles($path, $this->section);
    $this->apiList = array_intersect($allowedAPIs, $knownApis);
    $this->proxy = $proxy;
    $this->serverName = 'Tranquility';
  }
  /**
   * Function called by Yapeal.php to start section pulling XML from servers.
   *
   * @return bool Returns TRUE if all APIs were pulled cleanly else FALSE.
   */
  public function pullXML() {
    global $tracing;
    global $cachetypes;
    $apiCount = 0;
    $apiSuccess = 0;
    try {
      $charList = $this->getRegisteredCharacters();
      if (empty($charList)) {
        $mess = 'No characters for char section';
        trigger_error($mess, E_USER_NOTICE);
        return FALSE;
      };// if empty $charList ...
      // Ok now that we have a list of chars that need updated
      // we can check API for updates to their information.
      foreach ($charList as $char) {
        extract($char);
        /* **********************************************************************
        * Per char API pulls
        * **********************************************************************/
        $mess = 'Pulling XML for char ' . $charID;
        $tracing->activeTrace(YAPEAL_TRACE_CHAR, 1) &&
        $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
        $apis = array_intersect($this->apiList, explode(' ', $activeAPI));
        foreach ($apis as $api) {
          ++$apiCount;
          $class = $this->section . $api;
          $tableName = YAPEAL_TABLE_PREFIX . $class;
          $mess = 'Before dontWait for ' . $tableName . $charID;
          $mess .= ' in ' . basename(__FILE__);
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          // Should we wait to get API data
          if (dontWait($tableName, $charID)) {
            // Set it so we wait a bit before trying again if something goes wrong.
            $data = array('tableName' => $tableName,
              'ownerID' => $charID, 'cachedUntil' => YAPEAL_START_TIME);
            $mess = 'Before upsert for ' . $api . ' in ' . basename(__FILE__);
            $tracing->activeTrace(YAPEAL_TRACE_CACHE, 1) &&
            $tracing->logTrace(YAPEAL_TRACE_CACHE, $mess);
            try {
              YapealDBConnection::upsert($data, $cachetypes,
                YAPEAL_TABLE_PREFIX . 'utilCachedUntil', YAPEAL_DSN);
            }
            catch(ADODB_Exception $e) {}
          } else {
            continue;
          };// else dontWait ...
          $params = array('apiKey' => $apiKey, 'characterID' => $charID,
            'serverName' => $this->serverName, 'userID' => $userID);
          // Use section proxy setting if doesn't have own.
          if (empty($proxy)) {
            $proxy = $this->proxy;
          };
          $mess = 'Before instance for ' . $tableName . $charID;
          $mess .= ' in ' . basename(__FILE__);
          $tracing->activeTrace(YAPEAL_TRACE_CHAR, 2) &&
          $tracing->logTrace(YAPEAL_TRACE_CHAR, $mess);
          $instance = new $class($proxy, $params);
          if ($instance->apiFetch() && $instance->apiStore()) {
            ++$apiSuccess;
          };
          $instance = null;
          // See if we've taken to long to run and exit if TRUE.
          if (YAPEAL_MAX_EXECUTE < time()) {
            $mess = 'Yapeal took to long to execute';
            trigger_error($mess, E_USER_NOTICE);
            exit;
          };// if YAPEAL_START_TIME < $cuntil ...
        };// foreach $apis ...
      }; // foreach $charList
    }
    catch (ADODB_Exception $e) {
      // Do nothing use observers to log info
    }
    // Only truly successful if API was fetched and stored.
    if ($apiCount == $apiSuccess) {
      return TRUE;
    } else {
      return FALSE;
    }// else $apiCount == $apiSuccess ...
  }// function pullXML
  /**
   * Gets a list of characters that are active from RegisteredCharacter.
   *
   * @return array Returns the list of active characters.
   *
   * @throws ADODB_Exception for any errors.
   */
  function getRegisteredCharacters() {
    global $tracing;
    $con = YapealDBConnection::connect(YAPEAL_DSN);
    /* Generate a list of character(s) we need to do updates for */
    $sql = 'select u.userID "userID",u.fullApiKey "apiKey",';
    $sql .= 'chr.characterID "charID",chr.activeAPI,chr.proxy';
    $sql .= ' from ';
    $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCharacter` as chr,';
    $sql .= '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredUser` as u';
    $sql .= ' where';
    $sql .= ' chr.isActive=1';
    $sql .= ' and u.isActive=1';
    $sql .= ' and chr.userID=u.userID';
    $mess = 'Before GetAll active characters in ' . basename(__FILE__);
    $tracing->activeTrace(YAPEAL_TRACE_DATABASE, 2) &&
    $tracing->logTrace(YAPEAL_TRACE_DATABASE, $mess);
    return $con->GetAll($sql);
  }// function getRegisteredCharacters
}
?>
