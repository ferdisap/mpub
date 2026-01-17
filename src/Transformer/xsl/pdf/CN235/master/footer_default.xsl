<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="footer-odd-default-A4">
    <xsl:param name="id"/>
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container width="100%">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block border-top="1pt solid black"></fo:block>
      <fo:block>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="left">
            <xsl:text>Applicable to: </xsl:text>
            <xsl:call-template name="getApplicabilityOnFooter">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="right">
            <xsl:call-template name="getDMCode">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block text-align="center">&#160;</fo:block>
      <fo:block text-align="center">
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block text-align="left">
            <xsl:call-template name="getDate">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
            <xsl:text> Page </xsl:text>
            <fo:page-number/>
            <!-- Date and Page <fo:page-number/> of <fo:page-number-citation-last ref-id="{$id}"/> -->
          </fo:block>
        </fo:inline-container>        
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block>
            <xsl:call-template name="getSecurity">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="29.9%">
          <fo:block></fo:block>
        </fo:inline-container>
      </fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="footer-odd-last-default-A4">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="footer-odd-default-A4">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="footer-odd-default-A4L">
    <xsl:param name="id"/>
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container height="100%" reference-orientation="270">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block border-top="1pt solid black"></fo:block>
      <fo:block>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="left">
            <xsl:text>Applicable to: </xsl:text>
            <xsl:call-template name="getApplicabilityOnFooter">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="right">
            <xsl:call-template name="getDMCode">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block text-align="center">&#160;</fo:block>
      <fo:block text-align="center">
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block text-align="left">
            <xsl:call-template name="getDate">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
            <xsl:text> Page </xsl:text>
            <fo:page-number/>
            <!-- Date and Page <fo:page-number/> of <fo:page-number-citation-last ref-id="{$id}"/> -->
          </fo:block>
        </fo:inline-container>        
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block>
            <xsl:call-template name="getSecurity">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="29.9%">
          <fo:block></fo:block>
        </fo:inline-container>
      </fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="footer-odd-last-default-A4L">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="footer-odd-default-A4L">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="footer-even-default-A4">
    <xsl:param name="id"/>
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container width="100%">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block border-top="1pt solid black"></fo:block>
      <fo:block>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="left">
            <xsl:call-template name="getDMCode">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="right">
            <xsl:text>Applicable to: </xsl:text>
            <xsl:call-template name="getApplicabilityOnFooter">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block text-align="center">&#160;</fo:block>
      <fo:block text-align="center">
        <fo:inline-container inline-progression-dimension="29.9%">
          <fo:block></fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block>
            <xsl:call-template name="getSecurity">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block text-align="right">
            <xsl:call-template name="getDate">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
            <xsl:text> Page </xsl:text>
            <fo:page-number/>
            <!-- Date and Page <fo:page-number/> of <fo:page-number-citation-last ref-id="{$id}"/> -->
          </fo:block>
        </fo:inline-container>
      </fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="footer-even-last-default-A4">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="footer-even-default-A4">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

  <xsl:template name="footer-even-default-A4L">
    <xsl:param name="id"/>
    <xsl:param name="entry"/>
    <xsl:param name="masterName"/>
    <fo:block-container height="100%" reference-orientation="270">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>
      <fo:block border-top="1pt solid black"></fo:block>
      <fo:block>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="left">
            <xsl:call-template name="getDMCode">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="49.9%">
          <fo:block text-align="right">
            <xsl:text>Applicable to: </xsl:text>
            <xsl:call-template name="getApplicabilityOnFooter">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
      </fo:block>
      <fo:block text-align="center">&#160;</fo:block>
      <fo:block text-align="center">
        <fo:inline-container inline-progression-dimension="29.9%">
          <fo:block></fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block>
            <xsl:call-template name="getSecurity">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
          </fo:block>
        </fo:inline-container>
        <fo:inline-container inline-progression-dimension="34.9%">
          <fo:block text-align="right">
            <xsl:call-template name="getDate">
              <xsl:with-param name="entry" select="$entry"/>
            </xsl:call-template>
            <xsl:text> Page </xsl:text>
            <fo:page-number/>
            <!-- Date and Page <fo:page-number/> of <fo:page-number-citation-last ref-id="{$id}"/> -->
          </fo:block>
        </fo:inline-container>
      </fo:block>
    </fo:block-container>
  </xsl:template>

  <xsl:template name="footer-even-last-default-A4L">
    <xsl:param name="masterName"/>
    <xsl:param name="entry"/>
    <xsl:call-template name="footer-even-default-A4L">
      <xsl:with-param name="masterName" select="$masterName"/>
      <xsl:with-param name="entry" select="$entry"/>
    </xsl:call-template>
  </xsl:template>

</xsl:transform>