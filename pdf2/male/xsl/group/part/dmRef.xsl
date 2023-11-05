<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="dmRef">
    <xsl:variable name="ident">
      <xsl:apply-templates select="dmRefIdent"/>
    </xsl:variable>
    <a>
      <xsl:call-template name="cgmark"/>
      <xsl:attribute name="href"><xsl:value-of select="$ident"/>,<xsl:value-of select="@referredFragment"/></xsl:attribute>
      <xsl:value-of select="$ident"/>
      <xsl:text>&#160;</xsl:text>
      <xsl:apply-templates select="dmRefAddressItems"/>
    </a>
  </xsl:template>

  <xsl:template match="dmRefIdent">
    <!-- <xsl:value-of select="dmCode/@modelIdentCode"/>-<xsl:value-of select="dmCode/@systemDiffCode"/>-<xsl:value-of select="dmCode/@systemCode"/>-<xsl:value-of select="dmCode/@subSystemCode"/><xsl:value-of select="dmCode/@subSubSystemCode"/>-<xsl:value-of select="dmCode/@assyCode"/>-<xsl:value-of select="dmCode/@disassyCode"/><xsl:value-of select="dmCode/@disassyCodeVariant"/>-<xsl:value-of select="dmCode/@infoCode"/><xsl:value-of select="dmCode/@infoCodeVariant"/>-<xsl:value-of select="dmCode/@itemLocationCode"/> -->
    <!-- <xsl:if test="issueInfo">
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
    </xsl:if> -->
    <xsl:value-of select="php:function('Ptdi\Mpub\CSDB::resolve_dmIdent', ., null, 'DMC-', '')"/> 
  </xsl:template>

  <xsl:template match="dmRefAddressItems">
    <xsl:apply-templates select="dmTitle"/>
    <xsl:if test="issueDate">
      <xsl:apply-templates select="issueDate"/>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>