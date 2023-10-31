<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template match="title">
    <xsl:param name="prefix"/>
    <xsl:param name="parentName" select="name(parent::*)"/>

    <xsl:for-each select="..">
      <xsl:if test="name()=$parentName or not($prefix)">

        <!-- get the prefix (numberedPar) and determine the level title para -->
        <xsl:variable name="numberedPar">
          <xsl:call-template name="checkParent"/>
          <xsl:number/>
        </xsl:variable>
        <xsl:variable name="strLength">
          <xsl:value-of select="string-length(translate($numberedPar, '.', ''))"/>
        </xsl:variable>
        <xsl:variable name="h">
          <xsl:choose>
            <xsl:when test="$strLength = 1">h1</xsl:when>
            <xsl:when test="$strLength = 2">h2</xsl:when>
            <xsl:when test="$strLength = 3">h3</xsl:when>
            <xsl:when test="$strLength = 4">h4</xsl:when>
            <xsl:when test="$strLength = 5">h5</xsl:when>
            <xsl:otherwise>span</xsl:otherwise>
          </xsl:choose>
        </xsl:variable>

        <xsl:for-each select="child::title">
          <xsl:element name="{$h}">
            <xsl:call-template name="id"/>
            <xsl:call-template name="cgmark"/>

            <!-- set font size -->
            <xsl:if test="$parentName = 'levelledPara'">
              <xsl:choose>
                <xsl:when test="$h = 'h1'">
                  <xsl:attribute name="style">
                    <xsl:text>font-size:</xsl:text><xsl:value-of select="$fontsize_levelledPara_title_1"/>
                  </xsl:attribute>
                </xsl:when>
                <xsl:when test="$h = 'h2'">
                  <xsl:attribute name="style"><xsl:text>font-size:</xsl:text><xsl:value-of select="$fontsize_levelledPara_title_2"/></xsl:attribute>
                </xsl:when>
                <xsl:when test="$h = 'h3'">
                  <xsl:attribute name="style"><xsl:text>font-size:</xsl:text><xsl:value-of select="$fontsize_levelledPara_title_3"/></xsl:attribute>
                </xsl:when>
                <xsl:when test="$h = 'h4'">
                  <xsl:attribute name="style"><xsl:text>font-size:</xsl:text><xsl:value-of select="$fontsize_levelledPara_title_4"/></xsl:attribute>
                </xsl:when>
                <xsl:when test="$h = 'h5'">
                  <xsl:attribute name="style"><xsl:text>font-size:</xsl:text><xsl:value-of select="$fontsize_levelledPara_title_5"/></xsl:attribute>
                </xsl:when>
                <xsl:otherwise>
                  <xsl:attribute name="style"><xsl:text>font-size:</xsl:text><xsl:value-of select="'10'"/></xsl:attribute>
                </xsl:otherwise>
              </xsl:choose>
              
              <!-- set bookmark -->
              <xsl:attribute name="bookmarklvl">
                <xsl:value-of select="$strLength"/>
              </xsl:attribute>              
              <xsl:attribute name="bookmarktxt">
                <xsl:value-of select="$numberedPar"/>
                <xsl:text>&#160;&#160;</xsl:text>
                <xsl:value-of select="text()"/>
              </xsl:attribute>

              <!-- applying text -->
              <xsl:value-of select="$numberedPar"/>
              <xsl:text>&#160;&#160;&#160;</xsl:text>
              <xsl:apply-templates/>

            </xsl:if>



          </xsl:element>
        </xsl:for-each>
      </xsl:if>
    </xsl:for-each>

  </xsl:template>
</xsl:stylesheet>