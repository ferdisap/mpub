<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template match="caption">
    &#160;<span captionline="true" style="font-weight:bold" calgin="B">
      <xsl:call-template name="cgmark"/>

      <xsl:if test="@captionWidth">
        <xsl:attribute name="width"><xsl:value-of select="@captionWidth"/></xsl:attribute>
      </xsl:if>

      <xsl:if test="@captionHeight">
        <xsl:attribute name="height"><xsl:value-of select="@captionHeight"/></xsl:attribute>
      </xsl:if>

      <xsl:call-template name="color"/>

      <xsl:apply-templates select="captionLine"/>

    </span>&#160;
  </xsl:template>

  <!-- belum selesai -->
  <xsl:template name="color">
    <xsl:variable name="amber">266,193,7</xsl:variable>
    <xsl:variable name="black">0,0,0</xsl:variable>
    <xsl:variable name="green">20,163,57</xsl:variable>
    <xsl:variable name="grey">128,128,128</xsl:variable>
    <xsl:variable name="red">255,0,0</xsl:variable>
    <xsl:variable name="white">255,255,255</xsl:variable>
    <xsl:variable name="yellow">255,255,0</xsl:variable>
    <xsl:choose>
      <xsl:when test="@color = 'co01'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$green"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co02'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$amber"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co03'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$yellow"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co04'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$red"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co07'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$white"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co08'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$grey"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co62'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$yellow"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$white"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co66'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$red"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$black"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co67'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$red"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$white"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co81'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$yellow"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co82'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$white"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co83'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$red"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co84'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$green"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="@color = 'co85'">
        <xsl:attribute name="fillcolor"><xsl:value-of select="$black"/></xsl:attribute>
        <xsl:attribute name="textcolor"><xsl:value-of select="$amber"/></xsl:attribute>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>