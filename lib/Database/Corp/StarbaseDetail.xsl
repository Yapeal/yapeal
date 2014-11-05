<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml" version="1.0" encoding="utf-8"
                omit-xml-declaration="no" standalone="no" indent="no"/>
    <xsl:include href="../common.xsl"/>
    <xsl:template match="combatSettings">
        <xsl:element name="{name(.)}">
            <xsl:attribute name="key">ownerID,itemID</xsl:attribute>
            <xsl:attribute name="columns">onAggressionEnabled,onCorporationWarEnabled,onStandingDropStanding,onStatusDropEnabled,onStatusDropStanding,useStandingFromOwnerID</xsl:attribute>
            <xsl:element name="row">
                <xsl:attribute name="onAggressionEnabled">
                    <xsl:value-of select="onAggression/@enabled"/>
                </xsl:attribute>
                <xsl:attribute name="onCorporationWarEnabled">
                    <xsl:value-of select="onCorporationWar/@enabled"/>
                </xsl:attribute>
                <xsl:attribute name="onStandingDropStanding">
                    <xsl:value-of select="onStandingDrop/@standing"/>
                </xsl:attribute>
                <xsl:attribute name="onStatusDropEnabled">
                    <xsl:value-of select="onStatusDrop/@enabled"/>
                </xsl:attribute>
                <xsl:attribute name="onStatusDropStanding">
                    <xsl:value-of select="onStatusDrop/@standing"/>
                </xsl:attribute>
                <xsl:attribute name="useStandingsFromOwnerID">
                    <xsl:value-of select="useStandingsFrom/@ownerID"/>
                </xsl:attribute>
            </xsl:element>
        </xsl:element>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="generalSettings">
        <xsl:element name="{name(.)}">
            <xsl:attribute name="key">ownerID,itemID</xsl:attribute>
            <xsl:attribute name="columns">allowAllianceMembers,allowCorporationMembers,deployFlags,usageFlags</xsl:attribute>
            <xsl:element name="row">
                <xsl:attribute name="allowAllianceMembers">
                    <xsl:value-of select="allowAllianceMembers"/>
                </xsl:attribute>
                <xsl:attribute name="allowCorporationMembers">
                    <xsl:value-of select="allowCorporationMembers"/>
                </xsl:attribute>
                <xsl:attribute name="deployFlags">
                    <xsl:value-of select="deployFlags"/>
                </xsl:attribute>
                <xsl:attribute name="usageFlags">
                    <xsl:value-of select="usageFlags"/>
                </xsl:attribute>
            </xsl:element>
        </xsl:element>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="combatSettings/*[not(name() = 'row')]"/>
    <xsl:template match="generalSettings/*[not(name() = 'row')]"/>
</xsl:transform>
