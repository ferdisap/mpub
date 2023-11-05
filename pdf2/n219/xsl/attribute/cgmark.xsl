<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template name="cgmark">
    <xsl:param name="changeMark" select="@changeMark"/>
    <xsl:param name="changeType" select="@changeType"/>
    <xsl:param name="reasonForUpdateRefIds" select="@reasonForUpdateRefIds"/>

    <xsl:if test="$changeMark">

      <xsl:attribute name="changeMark">
        <xsl:value-of select="$changeMark"/>
      </xsl:attribute>
      <xsl:attribute name="changeType">
        <xsl:value-of select="$changeType"/>
      </xsl:attribute>
      <xsl:attribute name="reasonForUpdateRefIds">
        <xsl:value-of select="$reasonForUpdateRefIds"/>
      </xsl:attribute>

    </xsl:if>
  </xsl:template>

</xsl:stylesheet>