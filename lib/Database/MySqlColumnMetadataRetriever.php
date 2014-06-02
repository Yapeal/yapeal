<?php
/**
 * Contains MySqlColumnMetadataRetriever class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database;

/**
 * Class MySqlColumnMetadataRetriever
 */
class MySqlColumnMetadataRetriever
{
    /**
     * @param MetadataInterface $database
     * @param string            $tableName
     */
    public function __construct(MetadataInterface $database, $tableName)
    {
    }
    /**
     * @param string $value
     *
     * @throws \InvalidArgumentException
     * @return self
     */
    public function setTableName($value)
    {
        if (!is_string($value)) {
            $mess = 'Table name MUST be string but given ' . gettype($value);
            throw new \InvalidArgumentException($mess);
        }
        $this->tableName = $value;
        return $this;
    }
    /**
     * @type string
     */
    protected $tableName;
    /**
     * @return string
     */
    protected function getTableName()
    {
        return $this->tableName;
    }
}
