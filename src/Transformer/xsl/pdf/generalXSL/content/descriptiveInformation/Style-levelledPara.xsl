<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="style-levelledPara">
    <xsl:param name="level"/>
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
        <xsl:call-template name="style-levelledPara-default-fontSize10pt">
          <xsl:with-param name="masterName" select="$masterName"/>
          <xsl:with-param name="area_unit" select="$area_unit"/>
          <xsl:with-param name="level" select="$level"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <!-- compliance to S1000D v5.0 chap 6.2.2 page 5, table 3 colom 5 (leading text paragraph to the heading) -->
          <xsl:when test="position() != '1'">
            <xsl:attribute name="margin-top">
              <xsl:choose>
                <xsl:when test="($level = 's0') or ($level = 's1') or ($level = 'c1') or ($level = 'c2')">17<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:when test="$level = 's2'">15<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:when test="$level = 's3'">13<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:when test="$level = 's4'">13<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:when test="$level = 's5'">9<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:otherwise>0<xsl:value-of select="$area_unit"/></xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
          </xsl:when>
          <!-- compliance to S1000D v5.0 chap 6.2.2 page 5, table 3 colom 3 (leading to next lower level of heading) -->
          <xsl:when test="parent::levelledPara">
            <xsl:attribute name="margin-top">
              <xsl:choose>
                <xsl:when test="($level = 's0') or ($level = 'c1') or ($level = 'c2')">18<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:when test="$level = 's1'">4<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:when test="($level = 's2') or ($level = 's3') or ($level = 's4')">1<xsl:value-of select="$area_unit"/></xsl:when>
                <xsl:otherwise>0<xsl:value-of select="$area_unit"/></xsl:otherwise>
              </xsl:choose>
            </xsl:attribute>
          </xsl:when>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>

  </xsl:template>

  <xsl:template name="style-levelledPara-default-fontSize10pt">
    <xsl:param name="level"/>
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>

    <xsl:choose>
      <!-- compliance to S1000D v5.0 chap 6.2.2 page 5, table 3 colom 5 (leading text paragraph to the heading) -->
      <xsl:when test="position() != '1'">
        <xsl:attribute name="margin-top">
          <xsl:choose>
            <xsl:when test="($level = 's0') or ($level = 's1') or ($level = 'c1') or ($level = 'c2')">17<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:when test="$level = 's2'">15<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:when test="$level = 's3'">13<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:when test="$level = 's4'">13<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:when test="$level = 's5'">9<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:otherwise>0<xsl:value-of select="$area_unit"/></xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
      </xsl:when>
      <!-- compliance to S1000D v5.0 chap 6.2.2 page 5, table 3 colom 3 (leading to next lower level of heading) -->
      <xsl:when test="parent::levelledPara">
        <xsl:attribute name="margin-top">
          <xsl:choose>
            <xsl:when test="($level = 's0') or ($level = 'c1') or ($level = 'c2')">18<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:when test="$level = 's1'">4<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:when test="($level = 's2') or ($level = 's3') or ($level = 's4')">1<xsl:value-of select="$area_unit"/></xsl:when>
            <xsl:otherwise>0<xsl:value-of select="$area_unit"/></xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
      </xsl:when>
    </xsl:choose>
  </xsl:template>
  
</xsl:transform>