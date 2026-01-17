<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="style-table">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>

    <xsl:param name="orient"/>
    <xsl:param name="level"/>
    <xsl:param name="orientation">
      <xsl:call-template name="get_orientation">
        <xsl:with-param name="masterName" select="php:function('Ptdi\Mpub\Transformer\Pdf::get_PDF_MasterName')"/>
      </xsl:call-template>
    </xsl:param>

    <xsl:variable name="fontSize">
      <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$area_unit"/>
    </xsl:variable>

    <xsl:choose>
      <xsl:when test="$fontSize = '10pt'">
        <xsl:call-template name="style-table-default-fontSize10pt">
          <xsl:with-param name="masterName" select="$masterName"/>
          <xsl:with-param name="area_unit" select="$area_unit"/>
          <xsl:with-param name="orient" select="$orient"/>
          <xsl:with-param name="level" select="$level"/>
          <xsl:with-param name="orientation" select="$orientation"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="margin-top">3<xsl:value-of select="$area_unit"/></xsl:attribute>
        <xsl:if test="$orientation = 'port'">
          <xsl:if test="$orient = 'land'">
            <xsl:attribute name="reference-orientation">90</xsl:attribute>
          </xsl:if>
        </xsl:if>
        <xsl:if test="$orientation = 'land'">
          <xsl:if test="$orient = 'port'">
            <xsl:attribute name="reference-orientation">90</xsl:attribute>
          </xsl:if>
        </xsl:if>
        <xsl:choose>
          <xsl:when test="following-sibling::para">
            <xsl:attribute name="margin-bottom">18<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="following-sibling::title">
            <xsl:attribute name="margin-bottom">22<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <!-- compliance to Chap 6.2.2 page 14 randomlist paragraph -->
          <xsl:when test="parent::*/following-sibling::levelledPara/title">
            <xsl:choose>
              <xsl:when test="$level = 's0' or $level = 's1'">
                <xsl:attribute name="margin-bottom">21<xsl:value-of select="$area_unit"/></xsl:attribute>
              </xsl:when>
              <xsl:when test="$level = 's2'">
                <xsl:attribute name="margin-bottom">21<xsl:value-of select="$area_unit"/></xsl:attribute>
              </xsl:when>
              <xsl:when test="$level = 's3' or $level = 's4'">
                <xsl:attribute name="margin-bottom">18<xsl:value-of select="$area_unit"/></xsl:attribute>
              </xsl:when>
              <xsl:when test="$level = 's5'">
                <xsl:attribute name="margin-bottom">18<xsl:value-of select="$area_unit"/></xsl:attribute>
              </xsl:when>
            </xsl:choose>
          </xsl:when>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="style-table-default-fontSize10pt">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>

    <xsl:param name="orient"/>
    <xsl:param name="level"/>
    <xsl:param name="orientation">
      <xsl:call-template name="get_orientation">
        <xsl:with-param name="masterName" select="php:function('Ptdi\Mpub\Transformer\Pdf::get_PDF_MasterName')"/>
      </xsl:call-template>
    </xsl:param>

    <xsl:variable name="fontSize">
      <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$area_unit"/>
    </xsl:variable>
    
    <xsl:attribute name="margin-top">3<xsl:value-of select="$area_unit"/></xsl:attribute>
    <xsl:if test="$orientation = 'port'">
      <xsl:if test="$orient = 'land'">
        <xsl:attribute name="reference-orientation">90</xsl:attribute>
      </xsl:if>
    </xsl:if>
    <xsl:if test="$orientation = 'land'">
      <xsl:if test="$orient = 'port'">
        <xsl:attribute name="reference-orientation">90</xsl:attribute>
      </xsl:if>
    </xsl:if>
    <xsl:choose>
      <xsl:when test="following-sibling::para">
        <xsl:attribute name="margin-bottom">18<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="following-sibling::title">
        <xsl:attribute name="margin-bottom">22<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <!-- compliance to Chap 6.2.2 page 14 randomlist paragraph -->
      <xsl:when test="parent::*/following-sibling::levelledPara/title">
        <xsl:choose>
          <xsl:when test="$level = 's0' or $level = 's1'">
            <xsl:attribute name="margin-bottom">21<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="$level = 's2'">
            <xsl:attribute name="margin-bottom">21<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="$level = 's3' or $level = 's4'">
            <xsl:attribute name="margin-bottom">18<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="$level = 's5'">
            <xsl:attribute name="margin-bottom">18<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
        </xsl:choose>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template name="style-tbody">
    <xsl:if test="tfoot or preceding-sibling::tfoot or descendant::footnote">
      <xsl:attribute name="border-bottom">1pt solid black</xsl:attribute>
    </xsl:if>
  </xsl:template>

</xsl:transform>