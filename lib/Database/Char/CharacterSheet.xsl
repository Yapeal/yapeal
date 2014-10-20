<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml" version="1.0" encoding="utf-8"
                omit-xml-declaration="no" standalone="no" indent="no"/>
    <xsl:variable name="lower">abcdefghijklmnopqrstuvwxyz</xsl:variable>
    <xsl:variable name="upper">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>
    <xsl:template match="result">
        <xsl:copy>
            <!-- Elements without child nodes or attributes -->
            <xsl:apply-templates select="child::*[not(*|@*)]">
                <xsl:sort select="translate(name(),$upper,$lower)"
                          data-type="text" order="ascending"/>
            </xsl:apply-templates>
            <!-- Elements with child nodes but no attributes -->
            <xsl:apply-templates select="child::*[* and not(@*)]">
                <xsl:sort select="translate(name(),$upper,$lower)"
                          data-type="text" order="ascending"/>
            </xsl:apply-templates>
            <!--
            Elements with child nodes and attributes but without 'name' or 'key'
            attributes (ie NOT rowsets)
            -->
            <xsl:apply-templates
                    select="child::*[* and @* and not(@name|@key)]">
                <xsl:sort select="translate(@name,$upper,$lower)"
                          data-type="text" order="ascending"/>
            </xsl:apply-templates>
            <!-- Elements with 'name' or 'key' attribute (ie rowsets) -->
            <xsl:apply-templates select="child::*[@name|@key]">
                <xsl:sort select="translate(@name,$upper,$lower)"
                          data-type="text" order="ascending"/>
            </xsl:apply-templates>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="rowset">
        <xsl:choose>
            <xsl:when test="@name">
                <xsl:element name="{@name}">
                    <xsl:copy-of select="@key"/>
                    <xsl:copy-of select="@columns"/>
                    <xsl:variable name="column">
                        <xsl:value-of select="@key"/>
                    </xsl:variable>
                    <xsl:apply-templates select="child::*">
                        <xsl:sort select="@*[name() = $column]"
                                  data-type="number"/>
                    </xsl:apply-templates>
                </xsl:element>
            </xsl:when>
            <xsl:otherwise>
                <xsl:copy-of select="."/>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="attributeEnhancers[not(*) and not(@*)]"/>
    <xsl:template match="attributeEnhancers[* and not(@*)]">
        <xsl:element name="{name(.)}">
            <xsl:attribute name="key">bonusName</xsl:attribute>
            <xsl:attribute name="columns">
                <xsl:value-of
                        select="normalize-space('augmentatorValue,augmentatorName,bonusName')"/>
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
    <xsl:template match="attributeEnhancers/*[not(name()='row')]">
        <row bonusName="{name(.)}">
            <xsl:attribute name="augmentatorName">
                <xsl:value-of select="./augmentatorName"/>
            </xsl:attribute>
            <xsl:attribute name="augmentatorValue">
                <xsl:value-of select="./augmentatorValue"/>
            </xsl:attribute>
        </row>
    </xsl:template>
</xsl:transform>
