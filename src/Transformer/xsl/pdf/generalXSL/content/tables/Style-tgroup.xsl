<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">
  
  <xsl:template name="style-tgroup">
    <xsl:param name="pgwide"/>
    <xsl:param name="orient">
      <xsl:value-of select="string(parent::*/@orient)"/>
    </xsl:param>
    <xsl:param name="frame"/>

    <xsl:if test="($orient != 'land') and ($pgwide = '1')">
      <xsl:attribute name="table-layout">fixed</xsl:attribute>
      <xsl:attribute name="width">100%</xsl:attribute>
      <xsl:attribute name="start-indent">0cm</xsl:attribute>
      <xsl:attribute name="end-indent">0cm</xsl:attribute>
    </xsl:if>
    
    <xsl:if test="$frame = 'all'">
      <xsl:attribute name="border">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$frame = 'sides'">
      <xsl:attribute name="border-left">1pt solid black</xsl:attribute>
      <xsl:attribute name="border-right">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$frame = 'top'">
      <xsl:attribute name="border-top">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$frame = 'topbot'">
      <xsl:attribute name="border-top">1pt solid black</xsl:attribute>
      <xsl:attribute name="border-bottom">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$frame = 'bottom'">
      <xsl:attribute name="border-bottom">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$frame = 'none'">
      <xsl:attribute name="border">none</xsl:attribute>
    </xsl:if>    
  </xsl:template>

</xsl:transform>