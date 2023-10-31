<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template name="id">
    <xsl:param name="id" select="@id"/>
    
    <xsl:if test="$id">
      <xsl:attribute name="id">
        <xsl:value-of select="@id"/>
      </xsl:attribute>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>