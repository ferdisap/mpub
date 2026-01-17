<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="style-entry-header">
    <xsl:param name="rowsep"/>
    <xsl:param name="colsep"/>
    
    <xsl:if test="$rowsep = '1'">
      <xsl:attribute name="border-bottom">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$colsep = '1'">
      <xsl:variable name="index"><xsl:number/></xsl:variable>
      <xsl:if test="$index = '1'">
        <xsl:attribute name="border-left">1pt solid black</xsl:attribute>
      </xsl:if>
      <xsl:attribute name="border-right">1pt solid black</xsl:attribute>
    </xsl:if>

    <xsl:attribute name="padding-top">4pt</xsl:attribute>
    <xsl:attribute name="padding-bottom">0pt</xsl:attribute>
    <xsl:attribute name="padding-right">4pt</xsl:attribute>
    <xsl:attribute name="padding-left">4pt</xsl:attribute>
  </xsl:template>

  <xsl:template name="style-entry">
    <xsl:param name="rowsep"/>
    <xsl:param name="colsep"/>
    
    <xsl:if test="$rowsep = '1'">
      <xsl:attribute name="border-bottom">1pt solid black</xsl:attribute>
    </xsl:if>
    <xsl:if test="$colsep = '1'">
      <xsl:variable name="index"><xsl:number/></xsl:variable>
      <xsl:if test="$index = '1'">
        <xsl:attribute name="border-left">1pt solid black</xsl:attribute>
      </xsl:if>
      <xsl:attribute name="border-right">1pt solid black</xsl:attribute>
    </xsl:if>
    
    <xsl:variable name="rowPos">
      <xsl:number count="row"/>
    </xsl:variable>
    <xsl:choose>
      <xsl:when test="$rowPos = 1">
        <xsl:attribute name="padding-top">5pt</xsl:attribute>
      </xsl:when>
      <xsl:otherwise>
        <xsl:attribute name="padding-top">0pt</xsl:attribute>
      </xsl:otherwise>
    </xsl:choose>

    <xsl:attribute name="padding-bottom">0pt</xsl:attribute>
    <xsl:attribute name="padding-right">4pt</xsl:attribute>
    <xsl:attribute name="padding-left">4pt</xsl:attribute>
  </xsl:template>

</xsl:transform>