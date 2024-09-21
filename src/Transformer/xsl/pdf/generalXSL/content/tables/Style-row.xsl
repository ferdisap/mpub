<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template name="style-row">
    <xsl:if test="parent::tbody">
      <xsl:attribute name="padding-top">4pt</xsl:attribute>
      <xsl:attribute name="padding-bottom">4pt</xsl:attribute>
    </xsl:if>
    <xsl:if test="parent::thead">
      <xsl:attribute name="font-weight">bold</xsl:attribute>
    </xsl:if>
  </xsl:template>
  
</xsl:transform>