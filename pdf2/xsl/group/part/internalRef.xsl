<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- <xsl:include href="./attribute/cgmark.xsl" /> -->
  <!-- 
    Tidak bisa kasi include karena ini xsl ini di call oleh para.xsl/figure.xsl, etc
   -->
  <xsl:param name="dmOwner"/>

  <xsl:template match="internalRef">
    <xsl:param name="internalRefId" select="@internalRefId"/>
    <a>
      <xsl:call-template name="cgmark"/>
      <xsl:attribute name="href">
        <xsl:value-of select="$dmOwner"/>,<xsl:value-of select="$internalRefId"/>
      </xsl:attribute>
      <xsl:apply-templates/>
    </a>
  </xsl:template>

  <xsl:template name="internalRefTargetType">
    <xsl:param name="internalRefTargetType" select="@internalRefTargetType"/>
    <xsl:choose>
      <xsl:when test="$internalRefTargetType == 'irtt01'">
        <xsl:text>Fig.&#160;</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="getPosition">
    
  </xsl:template>
</xsl:stylesheet>