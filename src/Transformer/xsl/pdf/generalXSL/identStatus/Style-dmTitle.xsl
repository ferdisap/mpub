<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="style-dmTitle">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="length_unit">
      <xsl:call-template name="get_layout_unit_length"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>

    <xsl:variable name="fontSize">
      <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:variable>
    
    <xsl:attribute name="start-indent">
      <xsl:text>-</xsl:text>
      <xsl:call-template name="get_stIndent">
        <xsl:with-param name="masterName" select="php:function('Ptdi\Mpub\Transformer\Pdf::get_PDF_MasterName')"/>
      </xsl:call-template>
      <xsl:value-of select="$length_unit"/>
    </xsl:attribute>

    <xsl:choose>
      <xsl:when test="$fontSize = '10pt'">
        <xsl:attribute name="font-size">14<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="margin-bottom">10<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="margin-top">4<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="font-size">16<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="margin-bottom">12<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="margin-top">6<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
    
    <xsl:attribute name="text-align">center</xsl:attribute>
    <xsl:attribute name="font-weight">bold</xsl:attribute>
    
  </xsl:template>
</xsl:transform>
