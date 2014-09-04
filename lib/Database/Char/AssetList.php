<?php
/**
 * Contains AssetList class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
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
        $row['lft'] = $idx;
        if ($row->count()) {
            foreach ($row->children() as $descendant) {
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
            if (is_null($value) || strlen($row[$key]) != 0) {
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
        if (!empty($simple->result)) {
            $row = $simple->result->row[0];
            $row['itemID'] = $ownerID;
            $this->addNesting($simple->result->row[0]);
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
    /**
     * @var string
     */
    protected $xsl = <<<'XSL'
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
        version="1.0"
        encoding="utf-8"
        omit-xml-declaration="no"
        standalone="no"
        indent="yes"/>
    <xsl:template match="rowset[@name='assets']">
        <xsl:element name="row">
            <xsl:attribute name="itemID">0</xsl:attribute>
            <xsl:attribute name="locationID">0</xsl:attribute>
            <xsl:attribute name="typeID">25</xsl:attribute>
            <xsl:attribute name="quantity">1</xsl:attribute>
            <xsl:attribute name="flag">0</xsl:attribute>
            <xsl:attribute name="singleton">1</xsl:attribute>
            <xsl:attribute name="rawQuantity">-1</xsl:attribute>
            <xsl:attribute name="lvl">0</xsl:attribute>
            <xsl:apply-templates>
                <xsl:sort select="@locationID" data-type="number"/>
                <xsl:sort select="@itemID" data-type="number"/>
            </xsl:apply-templates>
        </xsl:element>
    </xsl:template>
    <xsl:template match="rowset[@name='contents']">
            <xsl:apply-templates>
                <xsl:sort select="@itemID" data-type="number"/>
            </xsl:apply-templates>
    </xsl:template>
    <xsl:template match="//row">
        <xsl:element name="row">
            <xsl:if test="not(@locationID)">
                <xsl:attribute name="locationID">
                    <xsl:value-of select="ancestor::*[@locationID][1]/@locationID"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:copy-of select="@*"/>
            <xsl:attribute name="lvl">
               <xsl:value-of select="count(ancestor::row[*])+1"/>
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
</xsl:transform>
XSL;
}
