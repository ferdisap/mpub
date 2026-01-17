<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
    1. @reproductionScale belum difungsikan
   -->

  <xsl:template name="style-icn">
    <xsl:if test="@reproductionHeight">
      <xsl:attribute name="height"><xsl:value-of select="string(@reproductionHeight)"/></xsl:attribute>
    </xsl:if>
    <xsl:if test="@reproductionWidth">
      <xsl:attribute name="height"><xsl:value-of select="string(@reproductionWidth)"/></xsl:attribute>
    </xsl:if>
  </xsl:template>

</xsl:transform>