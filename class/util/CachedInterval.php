<?php
/**
 * Contains CachedInterval class.
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
 * Wrapper class for utilCachedInterval table.
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class CachedInterval extends ALimitedObject implements IGetBy {
  /**
   * @var string Holds an instance of the DB connection.
   */
  protected $con;
  /**
   * @var string Holds the table name of the query is being built.
   */
  protected $tableName;
  /**
   * Holds query builder object.
   * @var object
   */
  protected $qb;
  /**
   * List of all section APIs
   * @var array
   */
  private $apiList;
  /**
   * Set to TRUE if a database record exists.
   * @var bool
   */
  private $recordExists;
  /**
   * Constructor
   *
   * @param mixed $id Id of cached time wanted.
   * @param bool $create When $create is set to FALSE will throw DomainException
   * if $id doesn't exist in database.
   *
   * @throws DomainException If $create is FALSE and a database record for $id
   * doesn't exist a DomainException will be thrown.
   * @throws InvalidArgmentException If required parameters in $id aren't
   * correct data types throws InvalidArgmentException.
   * @throws LengthException If $id is missing any require parameter throws a
   * LengthException.
   */
  public function __construct($id = NULL, $create = TRUE) {
    $this->tableName = YAPEAL_TABLE_PREFIX . 'util' . __CLASS__;
    try {
      // Get a database connection.
      $this->con = YapealDBConnection::connect(YAPEAL_DSN);
    }
    catch (ADODB_Exception $e) {
      $mess = 'Failed to get database connection in ' . __CLASS__;
      throw new RuntimeException($mess, 1);
    }
    // Get a new query builder object.
    $this->qb = new YapealQueryBuilder($this->tableName, YAPEAL_DSN);
    // Get a list of column names and their ADOdb generic types.
    $this->colTypes = $this->qb->getColumnTypes();
    // Was $id set?
    $required = array('api' => 'C', 'section' => 'C');
    // Check if $id is valid.
    foreach ($required as $k => $v) {
      if (!isset($id[$k])) {
        $mess = 'Missing required parameter $id["' . $k . '"]';
        $mess .= ' to constructor in ' . __CLASS__;
        throw new LengthException($mess, 1);
      };// if !isset $id[$k] ...
      switch ($v) {
        case 'C':
        case 'X':
          if (!is_string($id[$k])) {
            $mess = '$id["' . $k . '"] must be a string';
            throw new InvalidArgmentException($mess, 2);
          };// if !is_string $params[$k] ...
          break;
        case 'I':
          if (0 != strlen(str_replace(range(0,9),'',$id[$k]))) {
            $mess = '$id["' . $k . '"] must be an integer';
            throw new InvalidArgmentException($mess, 3);
          };// if 0 == strlen(...
          break;
      };// switch $v ...
    };// foreach $required ...
    // Check if record already exists in database table.
    if (FALSE === $this->getItemByName($id)) {
      // If record doesn't exists should it be created?
      if (TRUE == $create) {
        // Add required columns from $id to object.
        foreach ($required as $k => $v) {
          $this->$k = $id[$k];
        };// foreach $required ...
      } else {
        $mess = 'No cache interval for api = ' . $id['api'];
        $mess .= ' exists for section = ' . $id['section'];
        throw new DomainException($mess, 4);
      };// else TRUE == $create ...
    };// if FALSE === $this->getItemById($id) ...
    // See if any columns beyond required were include and insure they get
    // set/updated as well.
    if (count($id) > count($required)) {
      foreach (array_diff_key($id, $required) as $k => $v) {
        $this->$k = $v;
      };// foreach array_diff_key($id, $required) ...
    };// if count($id) ...
  }// function __construct
  /**
   * Destructor used to make sure to release ADOdb resource correctly more for
   * peace of mind than actual need.
   */
  public function __destruct() {
    $this->con = NULL;
  }// function __destruct
  /**
   * Used to get item from table by ID..
   *
   * @param mixed $id Id of item wanted.
   *
   * @return bool TRUE if item was retrieved.
   *
   * @throws LogicException Throws LogicException because there is no 'id' type
   * field for this database table.
   */
  public function getItemById($id) {
    throw new LogicException('Not implimented for ' . __CLASS__ . ' table', 1);
  }// function getItemById
  /**
   * Used to get item from table by name.
   *
   * @param mixed $name Name of record wanted.
   *
   * @return bool TRUE if item was retrieved else FALSE.
   *
   * @throws DomainException If $name not in $this->sectionList.
   */
  public function getItemByName($name) {
    //if (!in_array(ucfirst($name), $this->sectionList)) {
    //  $mess = 'Unknown section: ' . $name;
    //  throw new DomainException($mess, 8);
    //};// if !in_array...
    $sql = 'select `' . implode('`,`', array_keys($this->colTypes)) . '`';
    $sql .= ' from `' . $this->tableName . '`';
    $sql .= ' where';
    try {
      $sql .= ' `section`=' . $this->con->qstr($name['section']);
      $sql .= ' `api`=' . $this->con->qstr($name['api']);
      $result = $this->con->GetRow($sql);
      if (count($result) == 0) {
        $this->recordExists = FALSE;
      } else {
        $this->properties = $result;
        $this->recordExists = TRUE;
      };
      $this->properties = $result;
      $this->recordExists = TRUE;
    }
    catch (ADODB_Exception $e) {
      $this->recordExists = FALSE;
    }
    return $this->recordExists();
  }// function getItemByName
  /**
   * Function used to check if database record already existed.
   *
   * @return bool Returns TRUE if the the database record already existed.
   */
  public function recordExists() {
    return $this->recordExists;
  }// function recordExists
  /**
   * Used to reset an interval to the default value.
   */
  public function resetToDefault(){
    $ci = array(
      'account' => array('Characters' => 900),
      'char' => array(
        'AccountBalance' => 900, 'AssetList' => 86400,
        'CalenderEventAttandees' => 3600, 'CharacterSheet' => 3600,
        'ContactList' => 900, 'ContactNotifications' => 21600,
        'FacWarStats' => 3600, 'IndustryJobs' => 900, 'KillLog' => 3600,
        'MailBodies' => 1800, 'MailingLists' => 21600, 'MailMessages' => 1800,
        'MarketOrders' => 3600, 'Medals' => 3600, 'Notifications' => 1800,
        'Research' => 900, 'SkilInTraining' => 300, 'SkillQueue' => 900,
        'Standings' => 3600, 'UpcomingCalenderEvent' => 900,
        'WalletJournal' => 3600, 'WalletTransactions' => 3600
      ),
      'corp' => array(
        'AccountBalance' => 900, 'AssetList' => 86400,
        'CalenderEventAttandees' => 3600, 'ContactList' => 900,
        'ContainerLog' => 3600, 'CorporationSheet' => 21600,
        'FacWarStats' => 3600, 'IndustryJobs' => 900, 'KillLog' => 3600,
        'MarketOrders' => 3600, 'Medals' => 3600, 'MemberMedals' => 3600,
        'MemberSecurity' => 3600, 'MemberSecurityLog' => 3600,
        'MemberTracking' => 21600, 'OutpostList' => 3600,
        'OutpostServiceDetail' => 3600, 'Shareholders' => 3600,
        'Standings' => 3600, 'StarbaseDetail' => 3600, 'StarbaseList' => 3600,
        'Titles' => 3600, 'WalletJournal' => 3600, 'WalletTransactions' => 3600
      ),
      'eve' => array(
        'AllianceList' => 3600, 'CertificateTree' => 86400,
        'CharacterInfo' => 3600, 'ConquerableStationList' => 3600,
        'ErrorList' => 86400, 'FacWarStats' => 3600, 'FacWarTopStats' => 3600,
        'RefTypes' => 86400, 'SkillTree' => 86400
      ),
      'map' => array(
        'FacWarSystems' => 3600, 'Jumps' => 3600, 'Kills' => 3600,
        'Sovereignty' => 3600
      ),
      'server' => array('ServerStatus' => 180)
    );
    if (isset($ci[$this->section]) && isset($ci[$this->section][$this->api])) {
      $this->properties['interval'] = $ci[$this->section][$this->api];
    } else {
      $this->properties['interval'] = 3600;
    };// else isset $ci[$this->section] && ...
  }
  /**
   * Used to set default for column.
   *
   * @param string $name Name of the column.
   * @param mixed $value Value to be used as default for column.
   *
   * @return bool Returns TRUE if column exists in table and default was set.
   */
  public function setDefault($name, $value) {
    return $this->qb->setDefault($name, $value);
  }// function setDefault
  /**
   * Used to set defaults for multiple columns.
   *
   * @param array $defaults List of column names and new default values.
   *
   * @return bool Returns TRUE if all column defaults could be set, else FALSE.
   */
  public function setDefaults(array $defaults) {
    return $this->qb->setDefaults($defaults);
  }// function setDefaults
  /**
   * Used to store data into table.
   *
   * @return bool Return TRUE if store was successful.
   */
  public function store() {
    if (FALSE === $this->qb->addRow($this->properties)) {
      return FALSE;
    };// if FALSE === ...
    return $this->qb->store();
  }// function store
}
?>
