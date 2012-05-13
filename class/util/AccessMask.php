<?php
/**
 * Contains AccessMask class.
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
 * Wrapper class for utilAccessMask table.
 *
 * Unlike the other wrapper classes this one is read only.
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class AccessMask {
  /**
   * List of all accessMasks
   * @var array
   */
  private static $maskList;
  /**
   * Constructor
   *
   * @throws RuntimeException Throws a RuntimeException if connection to
   * database fails or can't get data from table.
   */
  public function __construct() {
    // If list is empty grab it from database.
    if (empty(self::$maskList)) {
      try {
        // Get a database connection.
        $con = YapealDBConnection::connect(YAPEAL_DSN);
      }
      catch (ADODB_Exception $e) {
        $mess = 'Failed to get database connection in ' . __CLASS__;
        throw new RuntimeException($mess);
      }
      $sql = 'select `api`,`description`,`mask`,`section`';
      $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'util' . __CLASS__ . '`';
      try {
        self::$maskList = $con->GetAll($sql);
      }
      catch (ADODB_Exception $e) {
        $mess = 'Failed to get data from table in ' . __CLASS__;
        throw new RuntimeException($mess);
      }
      // If the table is empty add a default for APIKeyInfo mask only.
      if (empty(self::$maskList)) {
        self::$maskList = array(
          array('api' => 'APIKeyInfo', 'mask' => 1, 'section' => 'account',
            'description' => 'Used to get information about a keyID')
        );
      };
    };// if empty self::$maskList ...
  }// function __construct
  /**
   * Converts a list of API names to a mask value.
   *
   * @param mixed $apis A string of comma separated API names or an array of
   * names to use for mask.
   * @param string $section Name of the section the APIs belongs to.
   *
   * @return int Returns a mask value.
   *
   * @throws InvalidArgumentException If $apis isn't a string or an array will
   * throw an InvalidArgumentException.
   * @throws DomainException If number of masks found don't match number of APIs
   * given a DomainException is thrown.
   */
  public function apisToMask($apis, $section = NULL) {
    if (is_string($apis)) {
      $apis = explode(',', $apis);
    } elseif (is_array($apis)) {
      // Re-index array with simple numeric index.
      $apis = array_values($apis);
    } else {
      $mess = 'Must use either string or array for $apis';
      throw new InvalidArgumentException($mess, 1);
    };
    $acnt = count($apis);
    $cnt = 0;
    $mask = 0;
    // Try to find API without using section.
    if (empty($section)) {
      foreach ($apis as $api) {
        foreach (self::$maskList as $row) {
          if ($row['api'] == $api) {
            $mask |= $row['mask'];
            ++$cnt;
          };
        };// foreach self::$maskList ...
      };// foreach $apis ...
      // Use section to limit API search.
    } else {
      foreach ($apis as $api) {
        foreach (self::$maskList as $row) {
          if ($row['section'] == $section && $row['api'] == $api ) {
            $mask |= $row['mask'];
            ++$cnt;
          };
        };// foreach self::$maskList ...
      };// foreach $apis ...
    };// else empty($section) ...
    // No API match found.
    if ($cnt == 0) {
      $mess = 'All of the APIs ' . implode(', ', $apis) . ' are unknown';
      if (!empty($section)) {
        $mess .= ' in section ' . $section;
      };
      throw new DomainException($mess);
      // Some APIs unknown.
    } elseif ($cnt < $acnt) {
      $diff = array_diff($apis, $this->maskToAPIs($mask, $section));
      $mess = 'The APIs: ' . implode(', ', $diff) . ' are unknown';
      if (!empty($section)) {
        $mess .= ' in section ' . $section;
      };
      throw new DomainException($mess);
      // Found right number of APIs.
    } elseif ($cnt == $acnt) {
      return $mask;
    };
    // Found API in multiple sections and the correct section was unknown.
    $mess = 'Multiple API matches found, $section parameter is required to';
    $mess .= ' determine which ' . implode(',', $apis) . ' are wanted';
    throw new DomainException($mess);
  }// function apisToMask
  /**
   * Returns the whole access mask list for all the known APIs.
   *
   * @return array Returns the access mask list.
   */
  public function getMaskList() {
    return self::$maskList;
  }// function getMaskList
  /**
   * Returns the access mask list for a single section of known APIs.
   *
   * @param string $section The section the access mask list is wanted for.
   * @param int $status The Yapeal supported status each API must be to be
   * included. It should be one or more of AccessMask::NOT_WORKING,
   * AccessMask::XSD_ONLY, AccessMask::WIP, AccessMask::TESTING,
   * AccessMask::COMPLETE.
   *
   * @return array Returns the access mask list for a section.
   */
  public function getSectionMaskList($section, $status = AccessMask::COMPLETE) {
    $mask = $this->getSectionToMask($section, $status);
    return $this->maskToAPIs($mask, $section);
  }// function getSectionMaskList
  /**
   * Converts section name to an API mask value.
   *
   * @param string $section The section access mask is wanted for.
   * @param int $status The Yapeal supported status each API must be to be
   * included. It should be one or more of AccessMask::NOT_WORKING,
   * AccessMask::XSD_ONLY, AccessMask::WIP, AccessMask::TESTING,
   * AccessMask::COMPLETE.
   *
   * @return array Returns the access mask for section.
   */
  public function getSectionToMask($section, $status = AccessMask::COMPLETE) {
    $mask = 0;
    foreach (self::$maskList as $row) {
      if ($row['section'] == $section) {
        if (($row['status'] & $status) > 0) {
          $mask |= $row['mask'];
        };
      };
    };// foreach self::$maskList ...
    return $mask;
  }// function getSectionToMask
  /**
   * Converts a mask to list of API names.
   *
   * @param mixed $mask A integer mask or an array of mask values to convert to
   * a list of names.
   * @param string $section Name of the section the APIs belongs to.
   *
   * @return mixed Return an array of API names or FALSE on error.
   *
   * @throws InvalidArgumentException If $mask isn't a integer or an array will
   * throw a InvalidArgumentException.
   */
  public function maskToAPIs($mask, $section) {
    if (is_array($mask)) {
      $mask = array_reduce($mask, array($this, 'reduceOR'), 0);
    } elseif (!is_int($mask)) {
      $mess = 'Must use either integer or array of integers for $mask';
      throw new InvalidArgumentException($mess, 6);
    };
    $apis = array();
    foreach (self::$maskList as $row) {
      if ($row['section'] == $section && ($mask & $row['mask']) > 0) {
        $apis[] = $row['api'];
      };// if $mask ...
    };// foreach self::$maskList ...
    return $apis;
  }// function maskToAPIs
  /**
   * Used by maskToAPIs to 'or' together masks for array_reduce().
   *
   * @param int $x First value to be ORed together.
   * @param int $y Second value to be ORed together.
   *
   * @return int Returns $x | $y
   */
  protected function reduceOR($x, $y) {
    return $x | $y;
  }// function reduceOR
  /**
   * Constants used with status.
   */
  const NOT_WORKING = 1;
  const XSD_ONLY = 2;
  const WIP = 4;
  const TESTING = 8;
  const COMPLETE = 16;
}

