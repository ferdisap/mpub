<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:template match="caption">
    <!-- foo<table style="border:1px solid red; width:30mm">
      <tr style="height:30mm">
        <td style="height:10mm">
          foobar
        </td>
      </tr>
    </table> -->
    <!-- <span style="font-weight:bold" captionline="true" calign="B" height="10mm" width="25mm" fillcolor="255,0,0" textcolor="0,0,0">ENG FIRE</span> -->
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
    <xsl:choose>
      <xsl:when test="@color = 'co66'">
        <xsl:attribute name="fillcolor">255,0,0</xsl:attribute>
        <xsl:attribute name="textcolor">0,0,0</xsl:attribute>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>