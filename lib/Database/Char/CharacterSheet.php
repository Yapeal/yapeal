<?php
/**
 * Contains CharacterSheet class.
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
namespace Yapeal\Database\Char;

use LogicException;
use PDOException;
use Yapeal\Database\AttributesDatabasePreserverTrait;
use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\ValuesDatabasePreserverTrait;

/**
 * Class CharacterSheet
 */
class CharacterSheet extends AbstractCharSection
{
    use EveApiNameTrait, AttributesDatabasePreserverTrait, ValuesDatabasePreserverTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return bool
     */
    protected function preserve(
        $xml,
        $ownerID
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToCharacterSheet($xml, $ownerID)
                 ->preserverToAttributeEnhancers($xml, $ownerID)
                 ->preserverToAttributes($xml, $ownerID)
                 ->preserverToSkills($xml, $ownerID)
                 ->preserverToCertificates($xml, $ownerID)
                 ->preserverToCorporationRoles($xml, $ownerID)
                 ->preserverToCorporationTitles($xml, $ownerID);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s for %3$s',
                strtolower($this->getSectionName()),
                $this->getApiName(),
                $ownerID
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
            return false;
        }
        return true;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToAttributeEnhancers(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'augmentatorName' => null,
            'augmentatorValue' => null,
            'bonusName' => null,
            'ownerID' => $ownerID
        );
        $tableName = 'charAttributeEnhancers';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//attributeEnhancers/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToAttributes(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'charisma' => null,
            'intelligence' => null,
            'memory' => null,
            'ownerID' => $ownerID,
            'perception' => null,
            'willpower' => null
        );
        $this->valuesPreserveData(
            $xml,
            $columnDefaults,
            'charAttributes',
            '//attributes/*'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToCertificates(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'certificateID' => null
        );
        $tableName = 'charCertificates';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//certificates/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToCharacterSheet(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'allianceID' => '0',
            'allianceName' => null,
            'ancestry' => null,
            'balance' => null,
            'bloodLine' => null,
            'characterID' => $ownerID,
            'cloneName' => null,
            'cloneSkillPoints' => null,
            'corporationID' => null,
            'corporationName' => null,
            'DoB' => null,
            'factionID' => '0',
            'factionName' => null,
            'gender' => null,
            'name' => null,
            'race' => null
        );
        $this->valuesPreserveData(
            $xml,
            $columnDefaults,
            'charCharacterSheet',
            '//attributes/*'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToCorporationRoles(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => null
        );
        $tableSuffixes = ['', 'AtBase', 'AtHQ', 'AtOther'];
        foreach ($tableSuffixes as $suffix) {
            $tableName = 'charCorporationRoles' . $suffix;
            $sql = $this->getCsq()
                        ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
            $this->getLogger()
                 ->info($sql);
            $this->getPdo()
                 ->exec($sql);
            $this->attributePreserveData(
                $xml,
                $columnDefaults,
                $tableName,
                '//corporationRoles' . $suffix . '/row'
            );
        };
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToCorporationTitles(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'titleID' => null,
            'titleName' => null
        );
        $tableName = 'charCorporationTitles';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//corporationTitles/row'
        );
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @throws LogicException
     * @return self
     */
    protected function preserverToSkills(
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'level' => null,
            'ownerID' => $ownerID,
            'published' => null,
            'skillpoints' => null,
            'typeID' => null
        );
        $tableName = 'charSkills';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $this->attributePreserveData(
            $xml,
            $columnDefaults,
            $tableName,
            '//skills/row'
        );
        return $this;
    }
    /**
     * @var int $mask
     */
    protected $mask = 8;
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
    <xsl:template match="rowset">
        <xsl:choose>
            <xsl:when test="@name">
                <xsl:element name="{@name}">
                    <xsl:copy-of select="@key"/>
                    <xsl:copy-of select="@columns"/>
                    <xsl:apply-templates/>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy-of select="."/>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="attributeEnhancers">
        <xsl:element name="{name(.)}">
            <xsl:attribute name="key">bonusName</xsl:attribute>
            <xsl:attribute name="columns">augmentatorValue,augmentatorName,bonusName</xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="perceptionBonus|memoryBonus|willpowerBonus|intelligenceBonus|charismaBonus">
        <row bonusName="{name(.)}">
        <xsl:attribute name="augmentatorName"><xsl:value-of select="./augmentatorName"/></xsl:attribute>
        <xsl:attribute name="augmentatorValue"><xsl:value-of select="./augmentatorValue"/></xsl:attribute>
        </row>
    </xsl:template>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
</xsl:transform>
XSL;
}
