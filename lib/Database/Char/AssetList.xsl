<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml" version="1.0" encoding="utf-8"
                omit-xml-declaration="no" standalone="no" indent="yes"/>
    <xsl:include href="../common.xsl"/>
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
                    <xsl:value-of
                            select="ancestor::*[@locationID][1]/@locationID"/>
                </xsl:attribute>
            </xsl:if>
            <xsl:copy-of select="@*"/>
            <xsl:attribute name="lvl">
                <xsl:value-of select="count(ancestor::row[*])+1"/>
            </xsl:attribute>
            <xsl:apply-templates/>
        </xsl:element>
    </xsl:template>
</xsl:transform>
