<?php
/**
 * Contains RegisteredKey class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package    Yapeal
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
use Yapeal\Database\YapealQueryBuilder;

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
        header('HTTP/1.0 403 Forbidden', true, 403);
        die($mess);
    };
    fwrite(STDERR, $mess);
    exit(1);
};
/**
 * Wrapper class for utilRegisteredKey table.
 *
 * @property int $isActive
 * @property int $activeAPIMask
 *
 * @package    Yapeal
 * @subpackage Wrappers
 */
class RegisteredKey extends ALimitedObject implements IGetBy
{
    /**
     * Constructor
     *
     * @param int|string $id     Id of key wanted.
     * @param bool       $create When $create is set to FALSE will throw DomainException
     *                           if $id does not exist in database.
     *
     * @throws InvalidArgumentException If $id isn't a number throws an
     * InvalidArgumentException.
     * @throws DomainException If $create is FALSE and a database record for $id
     * does not exist a DomainException will be thrown.
     * @throws RuntimeException Throws RuntimeException if fails to get database
     * connection.
     */
    public function __construct($id = null, $create = true)
    {
        $this->tableName = YAPEAL_TABLE_PREFIX . 'util' . __CLASS__;
        try {
            // Get a database connection.
            $this->con = YapealDBConnection::connect(YAPEAL_DSN);
        } catch (ADODB_Exception $e) {
            $mess = 'Failed to get database connection in ' . __CLASS__;
            throw new RuntimeException($mess);
        }
        // Get a new access mask object.
        $this->am = new AccessMask();
        // Get a new query builder object.
        $this->qb = new YapealQueryBuilder($this->tableName, YAPEAL_DSN);
        // Get a list of column names and their ADOdb generic types.
        $this->colTypes = $this->qb->getColumnTypes();
        // Was $id set?
        if (!empty($id)) {
            // If $id has any characters other than 0-9 it's not an keyID.
            if (0 == strlen(str_replace(range(0, 9), '', $id))) {
                if (false === $this->getItemById($id)) {
                    if (true == $create) {
                        // If $id is a number and doesn't exist yet set keyID with it.
                        $this->properties['keyID'] = $id;
                    } else {
                        $mess = 'Unknown key ' . $id;
                        throw new DomainException($mess);
                    }; // else ...
                };
            } else {
                $mess = 'Parameter $id must be an integer';
                throw new InvalidArgumentException($mess);
            }; // else ...
        }; // if !empty $id ...
    }
    /**
     * Destructor used to make sure to release ADOdb resource correctly more for
     * peace of mind than actual need.
     */
    public function __destruct()
    {
        $this->con = null;
    }
    /**
     * Used to add an API to the list in activeAPIMask.
     *
     * @param string $name    Name of the API to add without 'char','corp', etc i.e.
     *                        'corpAccountBalance' would just be 'AccountBalance'.
     * @param string $section Name of the section the API belongs to.
     *
     * @return bool Returns TRUE if $name already exists else FALSE.
     *
     * @throws DomainException Throws DomainException if $name could not be found.
     */
    public function addActiveAPI($name, $section = null)
    {
        // APIKeyInfo is always on and does not have a mask value.
        if ($name == 'APIKeyInfo') {
            return true;
        };
        // If no section parameter see if key type is known from accountAPIKeyInfo.
        if (empty($section) && !empty($this->type)) {
            if ($this->type == 'Character') {
                $section = 'char';
            } elseif ($this->type == 'Corporation') {
                $section = 'corp';
            } else {
                // Else Account
                $section = strtolower($this->type);
            };
        }; // if empty($section) ...
        $mask = $this->am->apisToMask($name, $section);
        if (($this->properties['activeAPIMask'] & $mask) > 0) {
            return true;
        };
        $this->properties['activeAPIMask'] |= $mask;
        return false;
    }
    /**
     * Used to delete an API from the list in activeAPI.
     *
     * @param string $name    Name of the API to delete without 'char','corp', etc
     *                        i.e. 'corpAccountBalance' would just be 'AccountBalance'.
     * @param string $section Name of the section the API belongs to.
     *
     * @return bool Returns TRUE if $name existed else FALSE.
     *
     * @throws DomainException Throws DomainException if $name could not be found.
     */
    public function deleteActiveAPI($name, $section = null)
    {
        // APIKeyInfo is always on and does not have a mask value.
        if ($name == 'APIKeyInfo') {
            return false;
        };
        // If no section parameter see if key type is known from accountAPIKeyInfo.
        if (empty($section) && !empty($this->type)) {
            if ($this->type == 'Character') {
                $section = 'char';
            } elseif ($this->type == 'Corporation') {
                $section = 'corp';
            } else {
                // Else Account
                $section = strtolower($this->type);
            };
        }; // if empty($section) ...
        $mask = $this->am->apisToMask($name, $section);
        if (($this->properties['activeAPIMask'] & $mask) > 0) {
            $this->properties['activeAPIMask'] ^= $mask;
            $ret = true;
        } else {
            $ret = false;
        }; // if $this->properties['activeAPIMask'] ...
        return $ret;
    }
    /**
     * Used to get key from RegisteredKey table by key ID.
     *
     * @param int $id Id of key wanted.
     *
     * @return bool TRUE if key was retrieved.
     */
    public function getItemById($id)
    {
        $sql = 'select urk.`' . implode('`,urk.`', array_keys($this->colTypes));
        $sql .= '`,aaki.`type`';
        $sql .= ' from `' . $this->tableName . '` as urk';
        $sql .= ' left join `' . YAPEAL_TABLE_PREFIX
            . 'accountAPIKeyInfo` as aaki';
        $sql .= ' on (urk.`keyID` = aaki.`keyID`)';
        $sql .= ' where urk.`keyID`=' . $id;
        try {
            $result = $this->con->GetRow($sql);
            if (!empty($result)) {
                // Split out type for existing keys or NULL.
                $this->type = $result['type'];
                unset($result['type']);
                $this->properties = $result;
                $this->recordExists = true;
            } else {
                $this->recordExists = false;
                // Get accessMask from accountAPIKeyInfo if available.
                $sql = 'select `accessMask`';
                $sql .= ' from `' . YAPEAL_TABLE_PREFIX . 'accountAPIKeyInfo`';
                $sql .= ' where `keyID`=' . $id;
                $result = $this->con->GetOne($sql);
                if (!empty($result)) {
                    $this->properties['activeAPIMask'] = (string)$result;
                };
            };
        } catch (ADODB_Exception $e) {
            Logger::getLogger('yapeal')
                  ->warn($e);
            $this->recordExists = false;
        }
        return $this->recordExists;
    }
    /**
     * Used to get item from table by name.
     *
     * @param string $name Name of record wanted.
     *
     * @return bool TRUE if item was retrieved else FALSE.
     *
     * @throws LogicException Throws LogicException because there is no 'name' type
     * field for this database table.
     */
    public function getItemByName($name)
    {
        throw new LogicException('Not implemented for ' . __CLASS__ . ' table');
    }
    /**
     * Function used to check if database record already existed.
     *
     * @return bool Returns TRUE if the the database record already existed.
     */
    public function recordExists()
    {
        return $this->recordExists;
    }// function __construct
    /**
     * Used to set default for column.
     *
     * @param string $name  Name of the column.
     * @param mixed  $value Value to be used as default for column.
     *
     * @return bool Returns TRUE if column exists in table and default was set.
     */
    public function setDefault($name, $value)
    {
        return $this->qb->setDefault($name, $value);
    }// function __destruct
    /**
     * Used to set defaults for multiple columns.
     *
     * @param array $defaults List of column names and new default values.
     *
     * @return bool Returns TRUE if all column defaults could be set, else FALSE.
     */
    public function setDefaults(array $defaults)
    {
        return $this->qb->setDefaults($defaults);
    }// function addActiveAPI
    /**
     * Used to store data into table.
     *
     * @return bool Return TRUE if store was successful.
     */
    public function store()
    {
        if (false === $this->qb->addRow($this->properties)) {
            return false;
        }; // if FALSE === ...
        return $this->qb->store();
    }// function deleteActiveAPI
    /**
     * Hold an instance of the AccessMask class.
     *
     * @var object
     */
    protected $am; // function getItemById
    /**
     * Holds an instance of the DB connection.
     *
     * @var object
     */
    protected $con; // function getItemByName
    /**
     * Holds query builder object.
     *
     * @var object
     */
    protected $qb; // function recordExists
    /**
     * Holds the table name of the query that is being built.
     *
     * @var string
     */
    protected $tableName; // function setDefault
    /**
     * Set to TRUE if a database record exists.
     *
     * @var bool
     */
    private $recordExists; // function setDefaults
    /**
     * Holds the type returned when querying accountAPIKeyInfo.
     * $var string
     */
    private $type;
    // function store
}

