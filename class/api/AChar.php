<?php
/**
 * Contains abstract class for char section.
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
 * Abstract class for Char APIs.
 *
 * @package Yapeal
 * @subpackage Api_char
 */
abstract class AChar extends AApiRequest {
  /**
   * Constructor
   *
   * @param array $params Holds the required parameters like keyID, vCode, etc
   * used in HTML POST parameters to API servers which varies depending on API
   * 'section' being requested.
   *
   * @throws LengthException for any missing required $params.
   */
  public function __construct(array $params) {
    $required = array('characterID' => 'I', 'keyID' => 'I', 'vCode' => 'C');
    foreach ($required as $k => $v) {
      if (!isset($params[$k])) {
        $mess = 'Missing required parameter $params["' . $k . '"]';
        $mess .= ' to constructor for ' . $this->api;
        $mess .= ' in ' . __CLASS__;
        throw new LengthException($mess, 1);
      };// if !isset $params[$k] ...
      switch ($v) {
        case 'C':
        case 'X':
          if (!is_string($params[$k])) {
            $mess = '$params["' . $k . '"] must be a string for ' . $this->api;
            $mess .= ' in ' . __CLASS__;
            throw new LengthException($mess, 2);
          };// if !is_string $params[$k] ...
          break;
        case 'I':
          if (0 != strlen(str_replace(range(0,9),'',$params[$k]))) {
            $mess = '$params["' . $k . '"] must be an integer for ' . $this->api;
            $mess .= ' in ' . __CLASS__;
            throw new LengthException($mess, 3);
          };// if 0 == strlen(...
          break;
      };// switch $v ...
    };// foreach $required ...
    $this->ownerID = $params['characterID'];
    $this->params = $params;
  }// function __construct
  /**
   * Per API section function that returns API proxy.
   *
   * For a description of how to design a format string look at the description
   * from {@link AApiRequest::sprintfn sprintfn}. The 'section' and 'api' will
   * be available as well as anything included in $params for __construct().
   *
   * @return mixed Returns the URL for proxy as string if found else it will
   */
  protected function getProxy() {
    $default = 'https://api.eveonline.com/' . $this->section;
    $default .= '/' . $this->api . '.xml.aspx';
    $sql = 'select proxy from ';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $tables = array();
      // Only use utilRegisteredCharacter when YAPEAL_REGISTERED_MODE is
      // required or optional.
      if (YAPEAL_REGISTERED_MODE == 'required'
        || YAPEAL_REGISTERED_MODE == 'optional') {
        $tables[] = '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredCharacter`'
          . ' where `characterID`=' . $this->params['characterID'];
      };
      $tables[] = '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredKey`'
        . ' where `keyID`=' . $this->params['keyID'];
      $tables[] = '`' . YAPEAL_TABLE_PREFIX . 'utilSections`'
        . ' where `section`=' . $con->qstr($this->section);
      // Look for a set proxy in each table.
      foreach ($tables as $table) {
        $result = $con->GetOne($sql . $table);
        // 4 is random and not magic. It just sounded good and is shorter than
        // any legal URL.
        if (strlen($result) > 4) {
          break;
        };
      };// foreach ...
      if (empty($result)) {
        return $default;
      };// if empty $result ...
      // Need to make substitution array by adding api, section, and params.
      $subs = array('api' => $this->api, 'section' => $this->section);
      $subs = array_merge($subs, $this->params);
      $proxy = self::sprintfn($result, $subs);
      if (FALSE === $proxy) {
        return $default;
      };
      return $proxy;
    }
    catch (ADODB_Exception $e) {
      return $default;
    }
  }// function getProxy
  /**
   * Handles some Eve API error codes in special ways.
   *
   * @param object $e Eve API exception returned.
   *
   * @return bool Returns TRUE if handled the error else FALSE.
   */
  protected function handleApiError($e) {
    try {
      switch ($e->getCode()) {
        // All of these codes give a new cachedUntil time to use.
        case 101: // Wallet exhausted: retry after {0}.
        case 103: // Already returned one week of data: retry after {0}.
        case 115: // Assets already downloaded: retry after {0}.
        case 116: // Industry jobs already downloaded: retry after {0}.
        case 117: // Market orders already downloaded. retry after {0}.
        case 119: // Kills exhausted: retry after {0}.
          $cuntil = substr($e->getMessage() , -21, 20);
          // Wait at least 10 minutes. Needed since API servers sometimes return
          // times in the past :P
          if ($cuntil < YAPEAL_START_TIME) {
            $cuntil = YAPEAL_START_TIME;
          };
          $data = array( 'api' => $this->api, 'cachedUntil' => $cuntil,
            'ownerID' => $this->ownerID, 'section' => $this->section
          );
          $cu = new CachedUntil($data);
          $cu->store();
          break;
        case 105:// Invalid characterID.
        case 201:// Character does not belong to account.
        case 202:// API key authentication failure.
        case 203:// Authentication failure.
        case 204:// Authentication failure.
        case 205:// Authentication failure (final pass).
        case 210:// Authentication failure.
        case 212:// Authentication failure (final pass).
          if (YAPEAL_REGISTERED_MODE != 'ignored') {
            $mess = 'Deactivating characterID: ' . $this->params['characterID'];
            $mess .= ' as their Eve API information is incorrect';
            Logger::getLogger('yapeal')->warn($mess);
            // A new row for character will be created if needed. This allows
            // the 'optional' registered mode to work correctly.
            $char = new RegisteredCharacter($this->params['characterID']);
            $char->isActive = 0;
            // If new character need to set required columns.
            if (FALSE === $char->recordExists()) {
              $char->activeAPIMask = 0;
            };// if $char->recordExists() ...
            if (FALSE === $char->store()) {
              $mess = 'Could not deactivate characterID: ';
              $mess .= $this->params['characterID'];
              Logger::getLogger('yapeal')->warn($mess);
            };// if $char->store() ...
          };// if YAPEAL_REGISTERED_MODE ...
          // Always deactivates key no matter the registered mode.
          $mess = 'Deactivating keyID: ' . $this->params['keyID'];
          $mess .= ' as the Eve API information is incorrect';
          Logger::getLogger('yapeal')->warn($mess);
          $key = new RegisteredKey($this->params['keyID'], FALSE);
          $key->isActive = 0;
          if (FALSE === $key->store()) {
            $mess = 'Could not deactivate keyID: ' . $this->params['keyID'];
            Logger::getLogger('yapeal')->warn($mess);
          };// if $key->store() ...
          break;
        case 124:// Character not enlisted in Factional Warfare. (Key accessMask outdated)
          // The key access has changed deactivate API for character if
          // registered mode is not 'ignored'.
          if (YAPEAL_REGISTERED_MODE != 'ignored') {
            if (Logger::getLogger('yapeal')->isInfoEnabled()) {
              $mess = 'Deactivating Eve API: ' . $this->api;
              $mess .= ' for characterID: ' . $this->params['characterID'];
              $mess .= ' as they are not enlisted in factional warfare';
              Logger::getLogger('yapeal')->info($mess);
            };
            // A new row for character will be created if needed. This allows
            // the 'optional' registered mode to work correctly.
            $char = new RegisteredCharacter($this->params['characterID']);
            // If new character need to set required columns.
            if (FALSE === $char->recordExists()) {
              $char->isActive = 1;
            };// if $char->recordExists() ...
            $char->deleteActiveAPI($this->api);
            if (FALSE === $char->store()) {
              $mess = 'Could not deactivate ' . $this->api;
              $mess .= ' for ' . $this->params['characterID'];
              Logger::getLogger('yapeal')->warn($mess);
            };// if $char->store() ...
          };// if YAPEAL_REGISTERED_MODE ...
          break;
        case 211:// Login denied by account status.
          // The account is not active deactivate key and character too if
          // registered mode is not 'ignored'.
          if (YAPEAL_REGISTERED_MODE != 'ignored') {
            $mess = 'Deactivating characterID: ' . $this->params['characterID'];
            $mess .= ' as their Eve account is currently suspended';
            Logger::getLogger('yapeal')->warn($mess);
            // A new row for character will be created if needed. This allows
            // the 'optional' registered mode to work correctly.
            $char = new RegisteredCharacter($this->params['characterID']);
            $char->isActive = 0;
            if (FALSE === $char->store()) {
              $mess = 'Could not deactivate characterID: ';
              $mess .= $this->params['characterID'];
              Logger::getLogger('yapeal')->warn($mess);
            };// if $char->store() ...
          };// if YAPEAL_REGISTERED_MODE ...
          // Always deactivates key no matter the registered mode.
          $mess = 'Deactivating keyID: ' . $this->params['keyID'];
          $mess .= ' as the Eve account is currently suspended';
          Logger::getLogger('yapeal')->warn($mess);
          $key = new RegisteredKey($this->params['keyID'], FALSE);
          $key->isActive = 0;
          if (FALSE === $key->store()) {
            $mess = 'Could not deactivate keyID: ' . $this->params['keyID'];
            Logger::getLogger('yapeal')->warn($mess);
          };// if $key->store() ...
          break;
        case 221:// Illegal page request! (Key accessMask outdated)
          // The key access has changed deactivate API for character if
          // registered mode is not 'ignored'.
          if (YAPEAL_REGISTERED_MODE != 'ignored') {
            $mess = 'Deactivating Eve API: ' . $this->api;
            $mess .= ' for characterID: ' . $this->params['characterID'];
            $mess .= ' as this API is no longer allowed by owner with this key';
            Logger::getLogger('yapeal')->warn($mess);
            // A new row for character will be created if needed. This allows
            // the 'optional' registered mode to work correctly.
            $char = new RegisteredCharacter($this->params['characterID']);
            // If new character need to set some required columns.
            if (FALSE === $char->recordExists()) {
              $char->isActive = 1;
            };// if $char->recordExists() ...
            $char->deleteActiveAPI($this->api);
            if (FALSE === $char->store()) {
              $mess = 'Could not deactivate ' . $this->api;
              $mess .= ' for ' . $this->params['characterID'];
              Logger::getLogger('yapeal')->warn($mess);
            };// if $char->store() ...
          };// if YAPEAL_REGISTERED_MODE ...
          // The key access has changed deactivate API for key.
          $mess = 'Deactivating Eve API: ' . $this->api;
          $mess .= ' for keyID: ' . $this->params['keyID'];
          $mess .= ' as this API is no longer allowed by owner with this key';
          Logger::getLogger('yapeal')->warn($mess);
          $key = new RegisteredKey($this->params['keyID'], FALSE);
          $key->deleteActiveAPI($this->api, $this->section);
          if (FALSE === $key->store()) {
            $mess = 'Could not deactivate ' . $this->api;
            $mess .= ' for ' . $this->params['keyID'];
            Logger::getLogger('yapeal')->warn($mess);
          };// if !$key->store() ...
          break;
        case 222://Key has expired. Contact key owner for access renewal.
          $mess = 'Deactivating keyID: ' . $this->params['keyID'];
          $mess .= ' as it needs to be renewed by owner';
          Logger::getLogger('yapeal')->warn($mess);
          // Deactivate for char and corp sections by expiring the key.
          $sql = 'update `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo`';
          $sql .= ' set `expires` = "' . gmdate('Y-m-d H:i:s') . '"';
          $sql .= ' where `keyID` = ' . $this->params['keyID'];
          // Get a database connection.
          $con = YapealDBConnection::connect(YAPEAL_DSN);
          $con->Execute($sql);
          // Deactivate for account section.
          $key = new RegisteredKey($this->params['keyID'], FALSE);
          $key->isActive = 0;
          if (FALSE === $key->store()) {
            $mess = 'Could not deactivate keyID: ' . $this->params['keyID'];
            Logger::getLogger('yapeal')->warn($mess);
          };// if $key->store() ...
          break;
        case 901:// Web site database temporarily disabled.
        case 902:// EVE backend database temporarily disabled.
          $cuntil = gmdate('Y-m-d H:i:s', strtotime('6 hours'));
          $data = array( 'api' => $this->api, 'cachedUntil' => $cuntil,
            'ownerID' => $this->ownerID, 'section' => $this->section
          );
          $cu = new CachedUntil($data);
          $cu->store();
          break;
        default:
          return FALSE;
          break;
      }// switch $code ...
    }
    catch (ADODB_Exception $e) {
      Logger::getLogger('yapeal')->error($e);
      return FALSE;
    }
    return TRUE;
  }// function handleApiError
}

