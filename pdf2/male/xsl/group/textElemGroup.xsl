<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <!-- <xsl:include href="./attribute/cgmark.xsl" /> -->
  <!-- 
    Tidak bisa kasi include karena ini xsl ini di call oleh para.xsl/figure.xsl, etc
   -->
   

  <xsl:include href="part/internalRef.xsl" />
  <xsl:include href="part/dmRef.xsl" />
  <xsl:include href="part/pmRef.xsl" />
  <xsl:include href="part/caption.xsl"/>
  <xsl:include href="part/footnote.xsl"/>
  <xsl:include href="part/inlineSignificantData.xsl"/>

  <xsl:param name="dmOwner"/>

  <xsl:output method="xml"/>
  
  <xsl:template match="changeInline">
    <span>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="emphasis">
    <xsl:choose>
      <xsl:when test="@emphasisType = 'em01'">
        <b><xsl:apply-templates/></b>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em02'">
        <i><xsl:apply-templates/></i>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em03'">
        <u><xsl:apply-templates/></u>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em05'">
        <del><xsl:apply-templates/></del>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em06'"> <!-- underline-bold -->
        <u style="font-weight:bold"><xsl:apply-templates/></u>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em07'"> <!-- underline-italic -->
        <u style="font-style:italic"><xsl:apply-templates/></u>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em08'"> <!-- bold-italic -->
        <b style="font-style:italic"><xsl:apply-templates/></b>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em09'"> <!-- bold-italic-underline -->
        <xsl:variable name="fontfamily" select="php:function('Ptdi\Mpub\Pdf2\male\DMC_male::getFontFamily')"/>
        <u style="font-family:{$fontfamily}b;font-style:italic;"><xsl:apply-templates/></u>
      </xsl:when>
      <xsl:otherwise>
        <span><xsl:apply-templates/></span>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="subScript">
    <sub><xsl:apply-templates/></sub>
  </xsl:template>
  <xsl:template match="superScript">
    <sup><xsl:apply-templates/></sup>
  </xsl:template>

</xsl:stylesheet>