<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <xsl:template match="amperage">
    <xsl:apply-templates/>
    <xsl:if test="@unitOfMeasure">
      <xsl:value-of select="@unitOfMeasure"/>
    </xsl:if>
  </xsl:template>

</xsl:transform>