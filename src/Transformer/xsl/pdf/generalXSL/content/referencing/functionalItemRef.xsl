<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <!-- Outstanding:
    1. Belum mengakomodir @functionalItemType
    2. Belum mengakomodir @installationIdent
    3. Belum mengakomodir @contextIdent
    4. Belum mengakomodir @manufactureCodeValue
    5. Belum mengakomodir @itemOriginator
    6. Belum mengakomodir <refs>
  -->

  <xsl:template match="functionalItemRef">
    <xsl:call-template name="cgmark_begin"/>
    <fo:inline>
      <xsl:call-template name="id"/>
      <xsl:call-template name="add_inline_applicability"/>
      <xsl:call-template name="add_inline_controlAuthority"/>
      <xsl:call-template name="add_inline_security"/>
      <xsl:choose>
        <xsl:when test="@functionalItemNumber">
          <xsl:value-of select="zoneNumber"/>
        </xsl:when>
        <xsl:when test="name">
          <xsl:value-of select="name"/>
        </xsl:when>
        <xsl:when test="shortName">
          <xsl:value-of select="shortName"/>
        </xsl:when>
      </xsl:choose>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

</xsl:stylesheet>