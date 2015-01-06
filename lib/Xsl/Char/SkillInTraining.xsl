<xsl:transform xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="xml" version="1.0" encoding="utf-8"
                omit-xml-declaration="no" standalone="no" indent="yes"/>
    <xsl:include href="../common.xsl"/>
    <xsl:template match="currentTQTime">
        <xsl:element name="currentTQTime">
            <xsl:value-of select="."/>
        </xsl:element>
        <xsl:choose>
            <xsl:when test="@offset">
                <xsl:element name="offset">
                    <xsl:value-of select="@offset"/>
                </xsl:element>
            </xsl:when>
        </xsl:choose>
    </xsl:template>
</xsl:transform>
