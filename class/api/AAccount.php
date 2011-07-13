<?php
/**
 * Contains abstract class for account section.
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
 * Abstract class for Account APIs.
 *
 * @package Yapeal
 * @subpackage Api_account
 */
abstract class AAccount extends AApiRequest {
  /**
   * Constructor
   *
   * @param array $params Holds the required parameters like userID, apiKey, etc
   * used in HTML POST parameters to API servers which varies depending on API
   * 'section' being requested.
   *
   * @throws LengthException for any missing required $params.
   */
  public function __construct(array $params) {
    $required = array('apiKey' => 'C', 'userID' => 'I');
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
    $this->ownerID = $params['userID'];
    $this->params = $params;
  }// function __construct
  /**
   * Per API section function that returns API proxy.
   *
   * For a description of how to design a format string look at the description
   * from {@link AApiRequest::sprintfn sprintfn}. The 'section' and 'api' will
   * be available as well as anything included in $params for __construct().
   *
   * @return string Returns the URL for proxy as string if found else it will
   * return the default string needed to use API server directly.
   */
  protected function getProxy() {
    $default = 'https://api.eveonline.com/' . $this->section;
    $default .= '/' . $this->api . '.xml.aspx';
    $sql = 'select proxy from ';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $tables = array(
        '`' . YAPEAL_TABLE_PREFIX . 'utilRegisteredUser` where `userID`=' .
        $this->params['userID'],
        '`' . YAPEAL_TABLE_PREFIX . 'utilSections` where `section`=' .
        $con->qstr($this->section)
      );
      // Look for a set proxy in each table.
      foreach ($tables as $table) {
        $result = $con->GetOne($sql . $table);
        // 4 is random and not magic. It just sounded good.
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
        case 200:// Current security level not high enough. (Wrong API key)
          $mess = 'Deactivating Eve API: ' . $this->api;
          $mess .= ' for ' . $this->params['userID'];
          $mess .= ' as did not give the required full API key';
          trigger_error($mess, E_USER_WARNING);
          $user = new RegisteredUser($this->params['userID'], FALSE);
          $user->deleteActiveAPI($this->api);
          if (FALSE === $user->store()) {
            $mess = 'Could not deactivate ' . $this->api;
            $mess .= ' for ' . $this->params['userID'];
            trigger_error($mess, E_USER_WARNING);
          };// if !$user->store() ...
          break;
        case 202:// API key authentication failure.
        case 203:// Authentication failure.
        case 204:// Authentication failure.
        case 205:// Authentication failure (final pass).
        case 210:// Authentication failure.
        case 212:// Authentication failure (final pass).
          $mess = 'Deactivating userID: ' . $this->params['userID'];
          $mess .= ' as their Eve API information is incorrect';
          trigger_error($mess, E_USER_WARNING);
          $user = new RegisteredUser($this->params['userID'], FALSE);
          $user->isActive = 0;
          if (FALSE === $user->store()) {
            $mess = 'Could not deactivate userID: ' . $this->params['userID'];
            trigger_error($mess, E_USER_WARNING);
          };// if !$user->store() ...
          break;
        case 211:// Login denied by account status.
          // The user's account isn't active deactivate it.
          $mess = 'Deactivating userID: ' . $this->params['userID'];
          $mess .= ' as their Eve account is currently suspended';
          trigger_error($mess, E_USER_WARNING);
          $user = new RegisteredUser($this->params['userID'], FALSE);
          $user->isActive = 0;
          if (FALSE === $user->store()) {
            $mess = 'Could not deactivate userID: ' . $this->params['userID'];
            trigger_error($mess, E_USER_WARNING);
          };// if !$user->store() ...
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
      };// switch $code ...
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
    return TRUE;
  }// function handleApiError
  /**
   * Simple <rowset> per API parser for XML.
   *
   * Most common API style is a simple <rowset>. This implementation allows most
   * API classes to be empty except for a constructor which sets $this->api and
   * calls their parent constructor.
   *
   * @return bool Returns TRUE if XML was parsered correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    if (in_array('prepareTables', get_class_methods($this))) {
      $qb->useUpsert(FALSE);
    };
    // Set any column defaults needed.
    $qb->setDefault('userID', $this->ownerID);
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'row':
                // Walk through attributes and add them to row.
                while ($this->xr->moveToNextAttribute()) {
                  $row[$this->xr->name] = $this->xr->value;
                };// while $this->xr->moveToNextAttribute() ...
                $qb->addRow($row);
                break;
            };// switch $this->xr->localName ...
            break;
          case XMLReader::END_ELEMENT:
            if ($this->xr->localName == 'result') {
              // Insert any leftovers.
              if (count($qb) > 0) {
                $qb->store();
              };// if count $rows ...
              $qb = NULL;
              return TRUE;
            };// if $this->xr->localName == 'row' ...
            break;
        };// switch $this->xr->nodeType
      };// while $xr->read() ...
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    trigger_error($mess, E_USER_WARNING);
    return FALSE;
  }// function parserAPI
}
?>
