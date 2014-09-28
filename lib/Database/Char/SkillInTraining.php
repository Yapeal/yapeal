<?php
/**
 * Contains SkillInTraining class.
 *
 * PHP version 5.4
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal
 * which can be used to access the Eve Online API data and place it into a
 * database. Copyright (C) 2014 Michael Cummings
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
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @author    Matt Emerick-Law <matt@emericklaw.co.uk>
 */
namespace Yapeal\Database\Char;

use Yapeal\Database\EveApiNameTrait;
use Yapeal\Database\ValuesDatabasePreserverTrait;

/**
 * Class SkillInTraining
 */
class SkillInTraining extends AbstractCharSection
{
    use EveApiNameTrait, ValuesDatabasePreserverTrait;
    /**
     * @param string $xml
     * @param string $ownerID
     *
     * @return self
     */
    protected function preserverToSkillInTraining(
        $xml,
        $ownerID
    ) {
        $columnDefaults = [
            'ownerID' => $ownerID,
            'currentTQTime' => '1970-01-01 00:00:01',
            'offset' => null,
            'trainingEndTime' => '1970-01-01 00:00:01',
            'trainingStartTime' => '1970-01-01 00:00:01',
            'trainingTypeID' => '0',
            'trainingStartSP' => '0',
            'trainingDestinationSP' => '0',
            'trainingToLevel' => '0',
            'skillInTraining' => '0'
        ];
        $this->valuesPreserveData($xml, $columnDefaults, 'charSkillInTraining');
        return $this;
    }
    /**
     * @type int $mask
     */
    protected $mask = 131072;
    /**
     * @type string
     */
    protected $xsl = <<<'XSL'
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="xml"
        version="1.0"
        encoding="utf-8"
        omit-xml-declaration="no"
        standalone="no"
        indent="yes"/>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="currentTQTime">
        <xsl:element name="currentTQTime"><xsl:value-of select="."/></xsl:element>
        <xsl:element name="offset"><xsl:value-of select="@offset"/></xsl:element>
    </xsl:template>
</xsl:transform>
XSL;
}
