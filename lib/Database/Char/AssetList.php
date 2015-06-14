<?php
/**
 * Contains AssetList class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database.
 * Copyright (C) 2014-2015 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License
 * for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A
 * copy of the GNU GPL should also be available in the GNU-GPL.md file.
 *
 * @copyright 2014-2015 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 */
namespace Yapeal\Database\Char;

use LogicException;
use SimpleXMLElement;
use Yapeal\Database\EveApiNameTrait;

/**
 * Class AssetList
 *
 * @package Yapeal\Database\Char
 */
class AssetList extends AbstractCharSection
{
    use EveApiNameTrait;
    /**
     * @param SimpleXMLElement $row
     * @param int              $idx
     *
     * @return int
     */
    protected function addNesting(SimpleXMLElement $row, $idx = 0)
    {
        /**
         * @type SimpleXMLElement $row
         */
        $row['lft'] = $idx;
        if ($row->count()) {
            $children = $row->children();
            foreach ($children as $descendant) {
                $idx = $this->addNesting($descendant, ++$idx);
            }
        }
        $row['rgt'] = ++$idx;
        $this->addRow($row);
        return $idx;
    }
    /**
     * @param SimpleXMLElement $row
     *
     * @return self
     */
    protected function addRow(SimpleXMLElement $row)
    {
        // Replace empty values with any existing defaults.
        foreach ($this->columnDefaults as $key => $value) {
            if (null === $value || '' !== (string)$row[$key]) {
                $this->columns[] = (string)$row[$key];
                continue;
            }
            $this->columns[] = (string)$value;
        }
        if (++$this->rowCount > $this->maxRowCount) {
            $this->flush(
                $this->columns,
                array_keys($this->columnDefaults),
                'charAssetList',
                $this->rowCount
            );
            $this->columns = [];
            $this->rowCount = 0;
        }
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToAssetList($xml, $ownerID)
    {
        $this->columnDefaults = [
            'ownerID' => $ownerID,
            'flag' => '0',
            'itemID' => null,
            'lft' => null,
            'lvl' => null,
            'locationID' => null,
            'quantity' => '1',
            'rawQuantity' => '0',
            'rgt' => null,
            'singleton' => '0',
            'typeID' => null
        ];
        $tableName = 'charAssetList';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $simple = new SimpleXMLElement($xml);
        if (0 !== $simple->result[0]->count()) {
            $simple->result[0]->row[0]['itemID'] = $ownerID;
            $this->addNesting($simple->result[0]->row[0]);
            $this->flush(
                $this->columns,
                array_keys($this->columnDefaults),
                $tableName,
                $this->rowCount
            );
            $this->columns = [];
            $this->rowCount = 0;
        }
        return $this;
    }
    /**
     * @type array $columnDefaults
     */
    protected $columnDefaults;
    /**
     * @type string[] $columns
     */
    protected $columns = [];
    /**
     * @type int $mask
     */
    protected $mask = 2;
    /**
     * @type int $maxRowCount
     */
    protected $maxRowCount = 1000;
    /**
     * @type int $rowCount
     */
    protected $rowCount = 0;
}
