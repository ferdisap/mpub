<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">
  
  <xsl:template name="style-warningcautionnote">
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
        <xsl:attribute name="margin-top">9<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="margin-bottom">6<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="margin-top">11<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:attribute name="margin-bottom">8<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>

    <xsl:choose>
      <xsl:when test="name(.) = 'caution'">
        <xsl:attribute name="padding-top">0.15cm</xsl:attribute>
        <xsl:attribute name="padding-bottom">0.15cm</xsl:attribute>
        <xsl:attribute name="padding-left">0.25cm</xsl:attribute>
        <xsl:attribute name="padding-right">0.25cm</xsl:attribute>
        <xsl:attribute name="background-image">url('<xsl:value-of select="$cautionPath"/>')</xsl:attribute>
      </xsl:when>
      <xsl:when test="name(.) = 'warning'">
        <xsl:attribute name="padding-top">0.15cm</xsl:attribute>
        <xsl:attribute name="padding-bottom">0.15cm</xsl:attribute>
        <xsl:attribute name="padding-left">0.25cm</xsl:attribute>
        <xsl:attribute name="padding-right">0.25cm</xsl:attribute>
        <xsl:attribute name="background-image">url('<xsl:value-of select="$warningPath"/>')</xsl:attribute>
      </xsl:when>
    </xsl:choose>

  </xsl:template>

</xsl:transform>