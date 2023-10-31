<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="pmRef">
    <xsl:variable name="ident">
      <xsl:apply-templates select="pmRefIdent"/>
    </xsl:variable>
    <xsl:variable name="mediaFormat">
      <xsl:apply-templates select="pmRefAddressItems/pubMedia/@pubMediaType"/>
    </xsl:variable>
    <xsl:variable name="mediaLocation">
      <xsl:apply-templates select="pmRefAddressItems/pubMedia/@mediaLocation"/>
    </xsl:variable>
    
    <a>
      <xsl:call-template name="cgmark"/>
      <xsl:attribute name="href"><xsl:value-of select="$mediaLocation"/><xsl:value-of select="$ident"/>.<xsl:value-of select="$mediaFormat"/></xsl:attribute>
      <xsl:value-of select="$ident"/>
      <xsl:text>&#160;</xsl:text>
      <xsl:apply-templates select="pmRefAddressItems"/>
    </a>
  </xsl:template>

  <xsl:template match="pmRefIdent">
    <xsl:text>PMC-</xsl:text>
    <xsl:value-of select="pmCode/@modelIdentCode"/>-<xsl:value-of select="pmCode/@pmIssuer"/>-<xsl:value-of select="pmCode/@pmNumber"/>-<xsl:value-of select="pmCode/@pmVolume"/>
    <xsl:if test="issueInfo">
      <xsl:text>_</xsl:text>
      <xsl:value-of select="issueInfo/@issueNumber"/>
      <xsl:text>-</xsl:text>
      <xsl:value-of select="issueInfo/@inWork"/>
    </xsl:if>
    <xsl:if test="language">
      <xsl:text>_</xsl:text>
      <xsl:value-of select="language/@languageIsoCode"/>
      <xsl:text>-</xsl:text>
      <xsl:value-of select="language/@countryIsoCode"/>
    </xsl:if>
  </xsl:template>

  <xsl:template match="pmRefAddressItems">
    <xsl:apply-templates select="pmTitle"/>
    <xsl:if test="issueDate">
      <xsl:apply-templates select="issueDate"/>
    </xsl:if>
  </xsl:template>

  <xsl:template match="@pubMediaType">
    <xsl:if test=". = 'paper' or . = 'PDF'">
      <xsl:text>pdf</xsl:text>
    </xsl:if>
  </xsl:template>
  
  <xsl:template match="@mediaLocation">
    <xsl:value-of select="."/>
  </xsl:template>

</xsl:stylesheet>