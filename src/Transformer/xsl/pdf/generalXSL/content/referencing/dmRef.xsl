<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- 
    kalau dmRef nya frontmatter atau yang @systemCode s/d @disAssycode nya angka 0, maka dmRef tertulis '' (kosong)
    see dmTitle.xsl, get_chapter
   -->
  <xsl:template match="dmRef">
    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', ., '', '')"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>

    <!-- targetnya adalah id di fo:flow, see pm.xsl dan dmodule.xsl -->
    <xsl:variable name="filename" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', descendant::dmRefIdent)"/>
    <fo:basic-link color="{$colorLink}" text-decoration="underline">
      <xsl:attribute name="internal-destination">
        <xsl:value-of select="$filename"/>
      </xsl:attribute>

      <xsl:call-template name="get_chapter">
        <xsl:with-param name="dmIdent" select="dmRefIdent"/>
      </xsl:call-template>
      
    </fo:basic-link>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

</xsl:transform>