<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="name">
    <xsl:call-template name="cgmark_begin"/>
    <fo:inline>
      <xsl:call-template name="add_inline_controlAuthority" />
      <xsl:apply-templates/>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="shortname">
    <xsl:call-template name="cgmark_begin"/>
    <fo:inline>
      <xsl:call-template name="add_inline_controlAuthority" />
      <xsl:apply-templates/>
    </fo:inline>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

</xsl:transform>