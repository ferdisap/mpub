<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">


  <xsl:template name="style-para">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="level"/>

    <xsl:variable name="defaultFontSize">
      <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:value-of select="$area_unit"/>
    </xsl:variable>

    <xsl:choose>
      <xsl:when test="$defaultFontSize = '10pt'">
        <xsl:call-template name="style-para-default-fontSize10pt">
          <xsl:with-param name="masterName" select="$masterName"/>
          <xsl:with-param name="area_unit" select="$area_unit"/>
          <xsl:with-param name="level" select="$level"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:otherwise>        
        <!-- <xsl:if test="(name(parent::*) = 'listItem' and following-sibling::para) or following-sibling::para"> -->
          <!-- <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute> -->
        <!-- </xsl:if> -->
        <xsl:choose>    
          <!-- compliance to S1000D v5.0 chap 6.2.2 page 7, para 2.4 par1 (sidehead 4 atau sidehead 5 itu leading 11pt)-->
          <xsl:when test="(parent::listItem and following-sibling::para) or following-sibling::para">
            <!-- kalau margin-bottom 20pt itu sepertinya ketinggian Saya belum tahu bagaimana equuivalensi antara leading baseline-to-baseline dan margin-bottom -->
            <!-- <xsl:attribute name="margin-bottom">20<xsl:value-of select="$area_unit"/></xsl:attribute> -->
            <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="parent::listItem and preceding-sibling::para and not(following-sibling::para) and parent::listItem/following-sibling::listItem[parent::randomList]">
            <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="following-sibling::*">
            <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <!-- compliance to S1000D v5.0 chap 6.2.2 page 7, para 2.4 par3 dan table 3 (leading text paragraph to heading)-->
          <xsl:when test="($level = 'c1' or $level = 'c2' or $level = 's0' or $level = 's1') and not(following-sibling::*) and parent::*/following-sibling::levelledPara">
            <xsl:attribute name="margin-bottom">17<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:when test="($level = 's2') and parent::*/following-sibling::levelledPara">
            <xsl:attribute name="margin-bottom">15<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>      
          <xsl:when test="($level = 's3' or $level = 's4') and parent::*/following-sibling::levelledPara">
            <xsl:attribute name="margin-bottom">13<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>      
          <xsl:when test="($level = 's5') and parent::*/following-sibling::levelledPara">
            <xsl:attribute name="margin-bottom">9<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:when>
          <xsl:otherwise>
            <xsl:attribute name="margin-bottom">9<xsl:value-of select="$area_unit"/></xsl:attribute>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="style-para-default-fontSize10pt">
    <xsl:param name="masterName">
      <xsl:call-template name="get_PDF_MasterName"/>
    </xsl:param>
    <xsl:param name="area_unit">
      <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>
    <xsl:param name="level"/>
    
    <!-- <xsl:if test="(name(parent::*) = 'listItem' and following-sibling::para) or following-sibling::para"> -->
      <!-- <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute> -->
    <!-- </xsl:if> -->
    <xsl:choose>    
      <!-- compliance to S1000D v5.0 chap 6.2.2 page 7, para 2.4 par1 (sidehead 4 atau sidehead 5 itu leading 11pt)-->
      <xsl:when test="(parent::listItem and following-sibling::para) or following-sibling::para">
        <!-- kalau margin-bottom 20pt itu sepertinya ketinggian Saya belum tahu bagaimana equuivalensi antara leading baseline-to-baseline dan margin-bottom -->
        <!-- <xsl:attribute name="margin-bottom">20<xsl:value-of select="$area_unit"/></xsl:attribute> -->
        <xsl:attribute name="margin-bottom">9<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="parent::listItem and preceding-sibling::para and not(following-sibling::para) and parent::listItem/following-sibling::listItem[parent::randomList]">
        <xsl:attribute name="margin-bottom">9<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="following-sibling::*">
        <xsl:attribute name="margin-bottom">9<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <!-- compliance to S1000D v5.0 chap 6.2.2 page 7, para 2.4 par3 dan table 3 (leading text paragraph to heading)-->
      <xsl:when test="($level = 'c1' or $level = 'c2' or $level = 's0' or $level = 's1') and not(following-sibling::*) and parent::*/following-sibling::levelledPara">
        <xsl:attribute name="margin-bottom">15<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="($level = 's2') and parent::*/following-sibling::levelledPara">
        <xsl:attribute name="margin-bottom">13<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>      
      <xsl:when test="($level = 's3' or $level = 's4') and parent::*/following-sibling::levelledPara">
        <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>      
      <xsl:when test="($level = 's5') and parent::*/following-sibling::levelledPara">
        <xsl:attribute name="margin-bottom">6<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="margin-bottom">6<xsl:value-of select="$area_unit"/></xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="style-warningcautionnotePara">
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
        <xsl:if test="following-sibling::*">
          <xsl:attribute name="margin-bottom">9<xsl:value-of select="$area_unit"/></xsl:attribute>    
        </xsl:if>
      </xsl:when>
      <xsl:otherwise>
        <xsl:if test="following-sibling::*">
          <xsl:attribute name="margin-bottom">11<xsl:value-of select="$area_unit"/></xsl:attribute>    
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>

  </xsl:template>

</xsl:transform>