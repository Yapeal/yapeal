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
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
</xsl:transform>
