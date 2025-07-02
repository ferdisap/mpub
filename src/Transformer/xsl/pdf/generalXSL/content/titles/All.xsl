<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <xsl:template match="title[parent::levelledPara]">
    <xsl:param name="masterName">
      <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Pdf::get_PDF_MasterName')"/>
    </xsl:param>
    <xsl:param name="level">
      <xsl:text>s</xsl:text>
      <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::checkLevelByPrefix', $lvl)"/>
    </xsl:param>

    <fo:block page-break-inside="avoid" page-break-after="avoid">
      <xsl:call-template name="style-title">
        <xsl:with-param name="masterName" select="$masterName"/>
        <xsl:with-param name="level" select="$level"/>
      </xsl:call-template>

      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>

  <xsl:template match="title">
    <xsl:param name="prefix"/>
    <xsl:param name="masterName" select="$masterName"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block page-break-inside="avoid" page-break-after="avoid">
      <!-- <xsl:call-template name="style-title"/>
        <xsl:value-of select="$prefix"/>
      <xsl:apply-templates/> -->
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="title[parent::table]">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block margin-top="6pt" page-break-before="avoid">
      <xsl:variable name="prefix">
        <xsl:text>Table </xsl:text>
        <xsl:number level="any" count="title"/>
        <xsl:text>&#160;&#160;</xsl:text>
      </xsl:variable>
      <xsl:value-of select="$prefix"/>
      <xsl:apply-templates select="title"/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="title[parent::crewDrill] | title[parent::subCrewDrill]">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block margin-bottom="6pt" font-weight="bold">
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <!-- <xsl:template match="title[parent::table] | title[parent::figure]">
    <xsl:param name="prefix"/>
    <xsl:call-template name="cgmark_begin"/>
    <fo:inline>
      <xsl:call-template name="add_inline_controlAuthority"/>
      <xsl:call-template name="add_inline_security"/>
      <xsl:value-of select="$prefix"/>
      <xsl:apply-templates/>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template> -->


</xsl:stylesheet>