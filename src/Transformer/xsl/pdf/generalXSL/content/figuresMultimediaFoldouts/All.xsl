<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <!-- 
    Outstanding:
    1. belum dibuat untuk element multimedia
    2. belum dibuat untuk element foldouts
   -->

  <xsl:template match="symbol">
    <fo:inline>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit" max-width="100%">
        <xsl:call-template name="style-icn"/>
      </fo:external-graphic>
    </fo:inline>
  </xsl:template>

  <xsl:template match="figure">
    <fo:block text-align="center" margin-bottom="11pt" page-break-inside="avoid">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="graphic"/>
      <xsl:apply-templates select="legend"/>
      <!-- tidak diperlukan lagi karena sudah dilakukan di elemen graphic
      <xsl:apply-templates select="title">
        <xsl:with-param name="prefix">Fig <xsl:number level="any"/><xsl:text>&#160;&#160;</xsl:text></xsl:with-param>
      </xsl:apply-templates> -->
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="graphic[parent::figure] | graphic[parent::table]">
    <fo:block margin-bottom="11pt">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:if test="@applicRefId or @controlAuthorityRefs or @securityClassification or @derivativeClassificationRefId or @commercialClassification or @caveat">
        <fo:block text-align="center">
          <xsl:call-template name="add_applicability"/>
          <xsl:call-template name="add_controlAuthority"/>
          <xsl:call-template name="add_security"/>
        </fo:block>
      </xsl:if>
      <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit" max-width="100%" max-height="90%">
        <xsl:call-template name="style-icn"/>
      </fo:external-graphic>
      <fo:block text-align="right" font-size="8pt" margin-bottom="6pt"><xsl:value-of select="@infoEntityIdent"/></fo:block>
      <xsl:if test="parent::*/title">
        <fo:block margin-top="3pt">
          <xsl:call-template name="captionGraphic"/>
        </fo:block>
      </xsl:if>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>
  
  <xsl:template match="graphic">
    <fo:block margin-bottom="11pt">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:if test="@applicRefId or @controlAuthorityRefs or @securityClassification or @derivativeClassificationRefId or @commercialClassification or @caveat">
        <fo:block text-align="center">
          <xsl:call-template name="add_applicability"/>
          <xsl:call-template name="add_controlAuthority"/>
          <xsl:call-template name="add_security"/>
        </fo:block>
      </xsl:if>
      <fo:external-graphic src="url('{unparsed-entity-uri(@infoEntityIdent)}')" content-width="scale-to-fit" max-width="100%" max-height="90%">
        <xsl:call-template name="style-icn"/>
      </fo:external-graphic>
      <fo:block text-align="right" font-size="8pt" margin-bottom="6pt"><xsl:value-of select="@infoEntityIdent"/></fo:block>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <!-- dipanggil juga di internalRef -->
  <xsl:template name="captionGraphic">
    <xsl:param name="onlyPrefix" select="'no'"/>
    <xsl:param name="prefix">
      <xsl:value-of select="php:function('ucfirst', name(parent::*))"/>
      <xsl:text>&#160;</xsl:text>
      <xsl:for-each select="..">
        <xsl:number level="any"/>
      </xsl:for-each>
      <xsl:text>&#160;</xsl:text>
      <xsl:if test="count(parent::*/graphic) > 1">
        <xsl:text>sheet </xsl:text>
        <xsl:value-of select="position()"/>
        <xsl:text>&#160;</xsl:text>
      </xsl:if>
    </xsl:param>
    <xsl:choose>
      <xsl:when test="$onlyPrefix = 'no'">
        <xsl:apply-templates select="parent::*/title">
          <xsl:with-param name="prefix" select="$prefix"/>
        </xsl:apply-templates>
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="$prefix"/>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>