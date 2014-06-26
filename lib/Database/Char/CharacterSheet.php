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

use PDO;
use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\AttributesDatabasePreserver;
use Yapeal\Database\DatabasePreserverInterface;
use Yapeal\Database\ValuesDatabasePreserver;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class CharacterSheet
 */
class CharacterSheet extends AbstractCommonEveApi
{
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    ) {
        $this->getLogger()
             ->info(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        $active = $this->getActiveCharacters();
        if (empty($active)) {
            $this->getLogger()
                 ->info('No active characters found');
            return;
        }
        $aPreserver = new AttributesDatabasePreserver(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq()
        );
        $vPreserver = new ValuesDatabasePreserver(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq()
        );
        foreach ($active as $char) {
            /**
             * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
             */
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->cacheNotExpired(
                $this->getApiName(),
                $this->getSectionName(),
                $char['characterID']
            )
            ) {
                continue;
            }
            $data->setEveApiArguments($char)
                 ->setEveApiXml();
            if (!$this->gotApiLock($data)) {
                continue;
            }
            $retrievers->retrieveEveApi($data);
            if ($data->getEveApiXml() === false) {
                $mess = sprintf(
                    'Could NOT retrieve any data from Eve API %1$s/%2$s for %3$s',
                    strtolower($this->getSectionName()),
                    $this->getApiName(),
                    $char['characterID']
                );
                $this->getLogger()
                     ->debug($mess);
                continue;
            }
            $this->xsltTransform($data);
            if ($this->isInvalid($data)) {
                $mess = sprintf(
                    'The data retrieved from Eve API %1$s/%2$s for %3$s is invalid',
                    strtolower($this->getSectionName()),
                    $this->getApiName(),
                    $char['characterID']
                );
                $this->getLogger()
                     ->warning($mess);
                $data->setEveApiName('Invalid' . $this->getApiName());
                $preservers->preserveEveApi($data);
                continue;
            }
            $preservers->preserveEveApi($data);
            $this->preserve(
                $data->getEveApiXml(),
                $char['characterID'],
                $aPreserver,
                $vPreserver
            );
            $this->updateCachedUntil($data, $interval, $char['characterID']);
        }
    }
    /**
     * @var string
     */
    protected $xsl = <<<XSL
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
    /**
     * @return array
     */
    protected function getActiveCharacters()
    {
        $sql = $this->csq->getActiveRegisteredCharacters($this->getMask());
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of active characters';
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            return array();
        }
    }
    /**
     * @return string
     */
    protected function getApiName()
    {
        if (empty($this->apiName)) {
            $this->apiName = basename(str_replace('\\', '/', __CLASS__));
        }
        return $this->apiName;
    }
    /**
     * @return int
     */
    protected function getMask()
    {
        return $this->mask;
    }
    /**
     * @return string
     */
    protected function getSectionName()
    {
        if (empty($this->sectionName)) {
            $this->sectionName = basename(str_replace('\\', '/', __DIR__));
        }
        return $this->sectionName;
    }
    /**
     * @param string                     $xml
     * @param string                     $ownerID
     * @param DatabasePreserverInterface $aPreserver
     * @param DatabasePreserverInterface $vPreserver
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        DatabasePreserverInterface $aPreserver = null,
        DatabasePreserverInterface $vPreserver = null
    ) {
        if (is_null($aPreserver)) {
            $aPreserver = new AttributesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        if (is_null($vPreserver)) {
            $vPreserver = new ValuesDatabasePreserver(
                $this->getPdo(),
                $this->getLogger(),
                $this->getCsq()
            );
        }
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToCharacterSheet($vPreserver, $xml, $ownerID);
            $this->preserverToAttributeEnhancers($aPreserver, $xml, $ownerID);
            $this->preserverToAttributes($vPreserver, $xml, $ownerID);
            $this->preserverToSkills($aPreserver, $xml, $ownerID);
            $this->preserverToCertificates($aPreserver, $xml, $ownerID);
            $this->preserverTocorporationRoles($aPreserver, $xml, $ownerID);
            $this->preserverTocorporationRolesAtHQ($aPreserver, $xml, $ownerID);
            $this->preserverTocorporationRolesAtBase(
                $aPreserver,
                $xml,
                $ownerID
            );
            $this->preserverTocorporationRolesAtOther(
                $aPreserver,
                $xml,
                $ownerID
            );
            $this->preserverToCorporationTitles($aPreserver, $xml, $ownerID);
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
        }
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToAttributeEnhancers(
        DatabasePreserverInterface $aPreserver,
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
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//attributeEnhancers/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $vPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToAttributes(
        DatabasePreserverInterface $vPreserver,
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
        $vPreserver->setTableName('charAttributes')
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//attributes/*');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCertificates(
        DatabasePreserverInterface $aPreserver,
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
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//certificates/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $vPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCharacterSheet(
        DatabasePreserverInterface $vPreserver,
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
        $vPreserver->setTableName('charCharacterSheet')
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml);
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCorporationRoles(
        DatabasePreserverInterface $aPreserver,
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => null
        );
        $tableName = 'charCorporationRoles';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//corporationRoles/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCorporationRolesAtBase(
        DatabasePreserverInterface $aPreserver,
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => null
        );
        $tableName = 'charCorporationRolesAtBase';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//corporationRolesAtBase/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCorporationRolesAtHQ(
        DatabasePreserverInterface $aPreserver,
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => null
        );
        $tableName = 'charCorporationRolesAtHQ';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//corporationRolesAtHQ/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCorporationRolesAtOther(
        DatabasePreserverInterface $aPreserver,
        $xml,
        $ownerID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'roleID' => null,
            'roleName' => null
        );
        $tableName = 'charCorporationRolesAtOther';
        $sql = $this->getCsq()
                    ->getDeleteFromTableWithOwnerID($tableName, $ownerID);
        $this->getLogger()
             ->info($sql);
        $this->getPdo()
             ->exec($sql);
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//corporationRolesAtOther/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToCorporationTitles(
        DatabasePreserverInterface $aPreserver,
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
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//corporationTitles/row');
        return $this;
    }
    /**
     * @param DatabasePreserverInterface $aPreserver
     * @param string                     $xml
     * @param string                     $ownerID
     *
     * @return self
     */
    protected function preserverToSkills(
        DatabasePreserverInterface $aPreserver,
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
        $aPreserver->setTableName($tableName)
                   ->setColumnDefaults($columnDefaults)
                   ->preserveData($xml, '//skills/row');
        return $this;
    }
    /**
     * @var int $mask
     */
    private $mask = 8;
}
