<?php
/**
 * Contains abstract ApiRequest class.
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
 * @subpackage AApiRequest
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
 * Abstract class to hold common methods for API classes.
 *
 * @package    Yapeal
 * @subpackage Api
 */
abstract class AApiRequest {
  /**
   * @var string Holds the name of the API. Normally set in constructor of the
   * final derived instance class.
   */
  protected $api;
  /**
   * @var string Holds the ownerID to be used when updating cachedUntil table.
   */
  protected $ownerID = 0;
  /**
   * @var array Holds the required parameters like keyID, vCode, etc used in
   * HTML POST parameters to API servers which varies depending on API 'section'
   * being requested.
   */
  protected $params;
  /**
   * @var string Holds the API section name. Normally set in constructor of the
   * final derived instance class.
   */
  protected $section;
  /**
   * @var XMLReader Holds instance of XMLReader.
   */
  protected $xr;
  /**
   * Used to store XML to MySQL table(s).
   *
   * @return Bool Return TRUE if store was successful.
   */
  public function apiStore() {
    // First get a new cache instance.
    $cache = new YapealApiCache($this->api, $this->section, $this->ownerID, $this->params);
    try {
      // Get valid cached copy if there is one.
      $result = $cache->getCachedApi();
      // If XML is not cached need to try to get it from API server or proxy.
      if (FALSE === $result) {
        $proxy = $this->getProxy();
        $con = new YapealNetworkConnection();
        $result = $con->retrieveXml($proxy, $this->params);
        // FALSE means there was an error and it has already been report just
        // need to return to caller.
        if (FALSE === $result) {
          return FALSE;
        };
        // Cache the received result.
        $cache->cacheXml($result);
        // Check if XML is valid.
        if (FALSE === $cache->isValid()) {
          // No use going any farther if the XML isn't valid.
          return FALSE;
        };
      };// if FALSE === $result ...
      if (in_array('prepareTables', get_class_methods($this->section . $this->api))) {
        if ($this->prepareTables() !== TRUE) {
          $mess = 'Could not prepare ' . $this->section . $this->api;
          $mess .= ' API tables to accept new data for ' . $this->ownerID;
          Logger::getLogger('yapeal')->warn($mess);
        };
      };
      // Create XMLReader.
      $this->xr = new XMLReader();
      // Pass XML to reader.
      $this->xr->XML($result);
      // Outer structure of XML is processed here.
      while ($this->xr->read()) {
        if ($this->xr->nodeType == XMLReader::ELEMENT &&
          $this->xr->localName == 'result') {
          $result = $this->parserAPI();
        };// if $this->xr->nodeType ...
      };// while $this->xr->read() ...
      return $result;
    }
    catch (YapealApiErrorException $e) {
      // Any API errors that need to be handled in some way are handled in this
      // function.
      $this->handleApiError($e);
      return FALSE;
    }
    catch (ADODB_Exception $e) {
      // Catch any uncaught ADOdb exceptions here.
      $mess = 'Uncaught ADOdb exception' . PHP_EOL;
      Logger::getLogger('yapeal')->warn($mess);
      return FALSE;
    }
  }// function apiStore
  /**
   * Abstract per API section function that returns API proxy.
   *
   * @return mixed Returns the URL for proxy as string if found else FALSE.
   */
  abstract protected function getProxy();
  /**
   * Abstract method to handles some Eve API error codes in special ways.
   *
   * Normally implemented in abstract section class that extends this class.
   *
   * @param object $e Eve API exception returned.
   *
   * @return bool Returns TRUE if handled the error else FALSE.
   */
  abstract protected function handleApiError($e);
  /**
   * Simple <rowset> per API parser for XML.
   *
   * Most common API style is a simple <rowset>. This implementation allows most
   * API classes to be empty except for a constructor which sets $this->api and
   * calls their parent constructor.
   *
   * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
   */
  protected function parserAPI() {
    $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
    // Get a new query instance.
    $qb = new YapealQueryBuilder($tableName, YAPEAL_DSN);
    // Save some overhead for tables that are truncated or in some way emptied.
    if (in_array('prepareTables', get_class_methods($this))) {
      $qb->useUpsert(FALSE);
    };
    if ($this->ownerID != 0) {
      $qb->setDefault('ownerID', $this->ownerID);
    };
    try {
      while ($this->xr->read()) {
        switch ($this->xr->nodeType) {
          case XMLReader::ELEMENT:
            switch ($this->xr->localName) {
              case 'row':
                $row = array();
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
      Logger::getLogger('yapeal')->warn($e);
      return FALSE;
    }
    $mess = 'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
    Logger::getLogger('yapeal')->warn($mess);
    return FALSE;
  }// function parserAPI
  /**
   * Version of sprintf for cases where named arguments are desired (php syntax)
   *
   * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
   *
   * with sprintfn: sprintfn('second: %second$s ; first: %first$s', array(
   *  'first' => '1st',
   *  'second'=> '2nd'
   * ));
   * Original idea taken from post by nate at frickenate dot com which can be
   * found in
   * {@link http://us.php.net/manual/en/function.sprintf.php#94608 sprinf description}
   *
   * @param string $format sprintf format string, with any number of named
   * arguments.
   * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements
   * to be made.
   *
   * @return mixed Returns result of sprintf call, or FALSE on error.
   */
  protected static function sprintfn($format, array $args = array()) {
    // Mapping of argument names to their corresponding sprintf numeric argument
    // value.
    $arg_nums = array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);
    // Find the next named argument. Each search starts at the end of the
    // previous replacement.
    for ($pos = 0;
      preg_match('/(?<=%)([a-zA-Z_]\w*)(?=\$)/', $format, $match,
        PREG_OFFSET_CAPTURE, $pos);
      $pos = $arg_pos + strlen($replace)) {
      $arg_pos = $match[0][1];
      $arg_len = strlen($match[0][0]);
      $arg_key = $match[1][0];
      // Programmer did not supply a value for the named argument found in the
      // format string.
      if (!array_key_exists($arg_key, $arg_nums)) {
        $mess = 'Missing argument "' . $arg_key . '"' . PHP_EOL;
        Logger::getLogger('yapeal')->warn($mess);
        return FALSE;
      };// if ! array_key_exists(...
      // Replace the named argument with the corresponding numeric one.
      $replace = $arg_nums[$arg_key];
      $format = substr_replace($format, $replace, $arg_pos, $arg_len);
      // Skip to end of replacement for next iteration.
      // Moved this into for loop increment where it belonged.
      //$pos = $arg_pos + strlen($replace);
    };// for $pos = 0; ...
    return vsprintf($format, array_values($args));
  }// function sprintfn
}

