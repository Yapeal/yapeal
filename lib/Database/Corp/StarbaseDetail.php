<?php
/**
 * Contains MemberTrackingExtended class.
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
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Corp;

use PDO;
use PDOException;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;

/**
 * Class MemberTrackingExtended
 */
class StarbaseDetail extends AbstractCorpSection
{
    /**
     * @var int $mask
     */
    protected $mask = 3355443;
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
          <xsl:template match="combatSettings">
              <xsl:element name="{name(.)}">
                  <xsl:attribute name="key">ownerID,posID</xsl:attribute>
                  <xsl:attribute name="columns">onAggressionEnabled,onCorporationWarEnabled,onStandingDropStanding,onStatusDropEnabled,onStatusDropStanding,useStandingFromOwnerID</xsl:attribute>
              <row>
                  <xsl:attribute name="onAggressionEnabled"><xsl:value-of select="onAggression/@enabled"/></xsl:attribute>
                  <xsl:attribute name="onCorporationWarEnabled"><xsl:value-of select="onCorporationWar/@enabled"/></xsl:attribute>
                  <xsl:attribute name="onStandingDropStanding"><xsl:value-of select="onStandingDrop/@standing"/></xsl:attribute>
                  <xsl:attribute name="onStatusDropEnabled"><xsl:value-of select="onStatusDrop/@enabled"/></xsl:attribute>
                  <xsl:attribute name="onStatusDropStanding"><xsl:value-of select="onStatusDrop/@standing"/></xsl:attribute>
                  <xsl:attribute name="useStandingsFromOwnerID"><xsl:value-of select="useStandingsFrom/@ownerID"/></xsl:attribute>
              </row>
              </xsl:element>
                   <xsl:apply-templates/>
          </xsl:template>
          <xsl:template match="useStandingsFrom|onStandingDrop|onStatusDrop|onAggression|onCorporationWar"/>
          <xsl:template match="@*|node()">
              <xsl:copy>
                  <xsl:apply-templates select="@*|node()"/>
              </xsl:copy>
          </xsl:template>
      </xsl:transform>
XSL;
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
             ->debug(
             sprintf(
                 'Starting autoMagic for %1$s/%2$s',
                 $this->getSectionName(),
                 $this->getApiName()
             )
            );
        /**
         * Update Starbase List
         */
        $class =
            new StarbaseList($this->getPdo(), $this->getLogger(), $this->getCsq(
            ));
        $class->autoMagic(
              $data,
                  $retrievers,
                  $preservers,
                  $interval
        );
        $active = $this->getActiveCorporations();
        if (empty($active)) {
            $this->getLogger()
                 ->info('No active registered corporations found');
            return;
        }
        foreach ($active as $corp) {
            $data->setEveApiSectionName(strtolower($this->getSectionName()))
                 ->setEveApiName($this->getApiName());
            if ($this->cacheNotExpired(
                     $this->getApiName(),
                         $this->getSectionName(),
                         $corp['corporationID']
            )
            ) {
                continue;
            }
            $data->setEveApiArguments($corp)
                 ->setEveApiXml();
            $towers = $this->getTowers($corp['corporationID']);
            if (empty($towers)) {
                $this->getLogger()
                     ->info('No Starbase Towers found');
                return;
            }
            foreach ($towers as $tower) {
                $data->addEveApiArgument('itemID', $tower['itemID']);
                if (!$this->oneShot($data, $retrievers, $preservers)) {
                    continue;
                }
            }
            $this->updateCachedUntil($data, $interval, $corp['corporationID']);
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
    protected function getTowers($corpID = 0)
    {
        $sql = $this->csq->getActiveStarbaseTowers($corpID);
        $this->getLogger()
             ->debug($sql);
        try {
            $stmt = $this->getPdo()
                         ->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            $mess = 'Could NOT get a list of towers';
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            return array();
        }
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param null   $itemID
     *
     * @return self
     */
    protected function preserve(
        $xml,
        $ownerID,
        $itemID = null
    ) {
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->preserverToStarbaseDetail($xml, $ownerID, $itemID);
            $this->preserverToStarbaseDetailFuel($xml, $ownerID, $itemID);
            $this->preserverToStarbaseDetailCombatSettings(
                 $xml,
                     $ownerID,
                     $itemID
            );
            $this->preserverToStarbaseDetailGeneralSettings(
                 $xml,
                     $ownerID,
                     $itemID
            );
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
        }
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $posID
     *
     * @return self
     */
    protected function preserverToStarbaseDetailFuel(
        $xml,
        $ownerID,
        $posID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'posID' => $posID,
            'typeID' => null,
            'quantity' => null
        );
        $this->getValuesDatabasePreserver()
             ->setTableName('corpFuel')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml);
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $posID
     *
     * @return self
     */
    protected function preserverToStarbaseDetail(
        $xml,
        $ownerID,
        $posID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'posID' => $posID,
            'onlineTimestamp' => null,
            'state' => null,
            'stateTimestamp' => null
        );
        $this->getvaluesDatabasePreserver()
             ->setTableName('corpStarbaseDetail')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml);
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $posID
     *
     * @return self
     */
    protected function preserverToStarbaseDetailCombatSettings(
        $xml,
        $ownerID,
        $posID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'posID' => $posID,
            'onAggressionEnabled' => null,
            'onCorporationWarEnabled' => null,
            'onStandingDropStanding' => null,
            'onStatusDropEnabled' => null,
            'onStatusDropStanding' => null,
            'useStandingsFromOwnerID' => null,
        );
        $this->getAttributesDatabasePreserver()
             ->setTableName('corpCombatSettings')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml);
        return $this;
    }
    /**
     * @param string $xml
     * @param string $ownerID
     * @param        $posID
     *
     * @return self
     */
    protected function preserverToStarbaseDetailGeneralSettings(
        $xml,
        $ownerID,
        $posID
    ) {
        $columnDefaults = array(
            'ownerID' => $ownerID,
            'posID' => $posID,
            'usageFlags' => null,
            'deployFlags' => null,
            'allowCorporationMembers' => null,
            'allowAllianceMembers' => null
        );
        $this->getAttributesDatabasePreserver()
             ->setTableName('corpCombatSettings')
             ->setColumnDefaults($columnDefaults)
             ->preserveData($xml);
        return $this;
    }
}
