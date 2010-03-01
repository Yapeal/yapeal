<?php
/**
 * Contains Sections class.
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
 * @copyright  Copyright (c) 2008-2009, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eve-online.com/
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
 * Wrapper class for utilSections table.
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class Sections extends ALimitedObject implements IGetBy {
  /**
   * List of all sections
   * @var array
   */
  private $sectionList;
  /**
   * Set to TRUE if a database record exists.
   * @var bool
   */
  private $recordExists;
  /**
   * Table name
   * @var string
   */
  private $table;
  /**
   * Constructor
   *
   * @param mixed $id Id of Section wanted.
   * @param bool $create When $create is set to FALSE will throw DomainException
   * if $id doesn't exist in database.
   *
   * @throws InvalidArgumentException If $id isn't a number or string throws an
   * InvalidArgumentException.
   * @throws DomainException If $create is FALSE and a database record for $id
   * doesn't exist a DomainException will be thrown.
   */
  public function __construct($id = NULL, $create = TRUE) {
    $this->sectionList = FilterFileFinder::getStrippedFiles(YAPEAL_CLASS, 'Section');
    $this->table = YAPEAL_TABLE_PREFIX . 'utilSections';
    $okeys = YapealDBConnection::getOptionalColumns($this->table, YAPEAL_DSN);
    $rkeys = YapealDBConnection::getRequiredColumns($this->table, YAPEAL_DSN);
    // Make an array of required and optional fields
    $this->types = array_merge($rkeys, $okeys);
    // Was $id set?
    if (!empty($id)) {
      // If $id is a number and doesn't exist yet set sectionID with it.
      // If $id has any characters other than 0-9 it's not a sectionID.
      if (0 == strlen(str_replace(range(0,9), '', $id))) {
        if (FALSE === $this->getItemById($id)) {
          if (TRUE == $create) {
            $this->properties['sectionID'] = $id;
          } else {
            $mess = 'Unknown section ' . $id;
            throw new DomainException($mess, 1);
          };// else ...
        };
        // else if it's a string ...
      } else if (is_string($id)) {
        if (FALSE === $this->getItemByName($id)) {
          if (TRUE == $create) {
            $this->properties['sectionName'] = $id;
          } else {
            $mess = 'Unknown section ' . $id;
            throw new DomainException($mess, 2);
          };// else ...
        };
      } else {
        $mess = 'Parameter $id must be an integer or a string';
        throw new InvalidArgumentException($mess, 3);
      };// else ...
    };// if !empty $id ...
  }// function __construct
  /**
   * Used to get section from Sections table by section ID.
   *
   * @param $id Id of section wanted.
   *
   * @return bool TRUE if section was retrieved.
   */
  public function getItemById($id) {
    $sql = 'select `' . implode('`,`', $this->types) . '`';
    $sql .= ' from `' . $this->table . '`';
    $sql .= ' where `sectionID`=' . $id;
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $result = $con->GetRow($sql);
      $this->properties = $result;
      $this->recordExists = TRUE;
    }
    catch (ADODB_Exception $e) {
      $this->recordExists = FALSE;
    }
    return $this->recordExists;
  }// function getItemById
  /**
   * Used to get item from table by name.
   *
   * @param $name Name of record wanted.
   *
   * @return bool TRUE if item was retrieved else FALSE.
   *
   * @throws DomainException If $name not in $this->sectionList.
   */
  public function getItemByName($name) {
    if (!in_array(ucfirst($name), $this->sectionList)) {
      $mess = 'Unknown section: ' . $name;
      throw new DomainException($mess, 4);
    };// if !in_array...
    $sql = 'select `' . implode('`,`', $this->types) . '`';
    $sql .= ' from `' . $this->table . '`';
    try {
      $con = YapealDBConnection::connect(YAPEAL_DSN);
      $sql .= ' where `SectionName`=' . $con->qstr($name);
      $result = $con->GetRow($sql);
      $this->properties = $result;
      $this->recordExists = TRUE;
    }
    catch (ADODB_Exception $e) {
      $this->recordExists = FALSE;
    }
    return $this->recordExists;
  }// function getItemByName
  /**
   * Used to store data into table.
   *
   * @return bool Return TRUE if store was successful.
   */
  public function store() {
    try {
      YapealDBConnection::upsert($this->properties,
        $this->table, YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      return FALSE;
    }
    return TRUE;
  }// function store
}
?>
