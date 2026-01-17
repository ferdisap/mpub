<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">
  
  <xsl:template name="style-listItem">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    
    <xsl:variable name="defaultFontSize">
      <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$area_unit"/>
    </xsl:variable>

    <xsl:choose>
      <xsl:when test="$defaultFontSize = '10pt'">
        <xsl:if test="parent::sequentialList or parent::definitionList">
          <xsl:attribute name="margin-top">9<xsl:value-of select="$area_unit"/></xsl:attribute>
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="parent::sequentialList or parent::definitionList">
          <xsl:attribute name="margin-top">11<xsl:value-of select="$area_unit"/></xsl:attribute>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>

  </xsl:template>

</xsl:transform>