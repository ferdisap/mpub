<?xml version="1.0" encoding="UTF-8"?>

<!-- 
  Outstanding:
  1. controlIndicatorSpec@controlIndicatorNumber tidak di gunakan kecuali hanya diperlukan saat data modul lainnya pakai elemen controlIndicatorRef yang targetnya elemen yang ada @controlIndicatorNumber dan ada @id nya
-->

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="controlIndicatorRepository">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="controlIndicatorGroup" />
    </fo:block>
  </xsl:template>

  <xsl:template match="controlIndicatorGroup">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>

      <xsl:apply-templates select="internalRef"/>
  
      <fo:table width="100%" margin-top="14pt">
        <fo:table-column column-number="1" column-width="10%"/>
        <fo:table-column column-number="2" column-width="90%"/>
        <fo:table-header>
          <fo:table-row>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>No.</fo:block>
            </fo:table-cell>
            <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
              <fo:block>Detail</fo:block>
            </fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body>
          <xsl:apply-templates select="controlIndicatorSpec"/>
        </fo:table-body>
      </fo:table>

      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="controlIndicatorSpec">
    <fo:table-row>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
        <xsl:apply-templates select="controlIndicatorKey"/>
      </fo:table-cell>
      <fo:table-cell padding="4pt" padding-left="0pt" text-align="left">
        <xsl:apply-templates select="controlIndicatorName"/>
        <xsl:apply-templates select="shortName"/>
        <xsl:apply-templates select="controlIndicatorDescr"/>
      </fo:table-cell>
      <xsl:call-template name="cgmark_end"/>
    </fo:table-row>
  </xsl:template>

  <xsl:template match="controlIndicatorKey">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:value-of select="."/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="controlIndicatorName">
    <fo:block font-weight="bold">
      <xsl:call-template name="add_id"/>
      <xsl:if test="not(parent::*/shortName)">
        <xsl:attribute name="margin-bottom">3pt</xsl:attribute>
      </xsl:if>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="shortName[parent::controlIndicatorSpec]">
    <fo:block font-weight="bold" margin-bottom="3pt">
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:text>(</xsl:text>
      <xsl:value-of select="."/>
      <xsl:tex>)</xsl:tex>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="controlIndicatorDescr">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="controlIndicatorFunction">
    <fo:block margin-bottom="3pt">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority" />
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="internalRef[parent::controlIndicatorGroup]">
    <fo:block>
      <xsl:call-template name="style-para"/>
      <xsl:text>See </xsl:text>
      <xsl:call-template name="add_internalRef"/>
      <xsl:text>.</xsl:text>
    </fo:block>
  </xsl:template>
</xsl:transform>