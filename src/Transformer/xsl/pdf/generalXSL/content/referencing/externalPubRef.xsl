<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="externalPubRef">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>

    <!-- targetnya adalah id di fo:flow, see pm.xsl -->
    <xsl:variable name="filename" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_externalPubRefIdent', descendant::externalPubRefIdent, php:function('strtoupper', string(externalPubRefIdent/externalPubCode/@pubCodingScheme)))"/>
    <fo:basic-link color="{$colorLink}" text-decoration="underline">
      <xsl:attribute name="internal-destination">
        <xsl:value-of select="$filename"/>
      </xsl:attribute>
      <fo:inline font-family="monospace">
        <xsl:value-of select="$filename"/>
      </fo:inline>
    </fo:basic-link>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

</xsl:transform>