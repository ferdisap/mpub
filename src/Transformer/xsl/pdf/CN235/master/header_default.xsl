<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="header-odd-default-A4">
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container width="100%" height="1.5cm">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block>
        <fo:inline-container inline-progression-dimension="54.9%" text-align="left">
          <xsl:call-template name="get_logo">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
          <fo:block>
            <xsl:call-template name="getPmEntryTitle"/>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="44.9%">
          <fo:block text-align="right">
            <xsl:call-template name="getPMCode"/>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block border-bottom="1pt solid black"></fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="header-odd-last-default-A4">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="header-odd-default-A4">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="header-odd-default-A4L">
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container height="100%" reference-orientation="270">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block>
        <fo:inline-container inline-progression-dimension="54.9%" text-align="left">
          <xsl:call-template name="get_logo">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
          <fo:block>
            <xsl:call-template name="getPmEntryTitle"/>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="44.9%">
          <fo:block text-align="right">
            <xsl:call-template name="getPMCode"/>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block border-bottom="1pt solid black"></fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="header-odd-last-default-A4L">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="header-odd-default-A4L">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="header-even-default-A4">
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container width="100%" height="1.5cm">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block>
        <fo:inline-container inline-progression-dimension="44.9%">
          <fo:block text-align="left">
            <xsl:call-template name="getPMCode"/>
          </fo:block>
        </fo:inline-container>
        
        <fo:inline-container inline-progression-dimension="54.9%" text-align="right">
          <xsl:call-template name="get_logo">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
          <fo:block>
            <xsl:call-template name="getPmEntryTitle"/>
          </fo:block>
        </fo:inline-container>
        <!-- <fo:inline-container inline-progression-dimension="24.9%">
          <fo:block></fo:block>
        </fo:inline-container> -->
      </fo:block>
      <fo:block border-bottom="1pt solid black"></fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="header-even-last-default-A4">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="header-even-default-A4">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="header-even-default-A4L">
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container height="100%" reference-orientation="270">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block>
        <fo:inline-container inline-progression-dimension="44.9%">
          <fo:block text-align="left">
            <xsl:call-template name="getPMCode"/>
          </fo:block>
        </fo:inline-container>
        
        <fo:inline-container inline-progression-dimension="54.9%" text-align="right">
          <xsl:call-template name="get_logo">
            <xsl:with-param name="entry" select="$entry"/>
          </xsl:call-template>
          <fo:block>
            <xsl:call-template name="getPmEntryTitle"/>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block border-bottom="1pt solid black"></fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="header-even-last-default-A4L">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="header-even-default-A4L">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

</xsl:transform>