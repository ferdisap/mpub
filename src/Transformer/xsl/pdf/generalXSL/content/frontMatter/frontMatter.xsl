<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <!-- <xsl:template match="content[name(child::*) = 'frontMatter']">
    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', //identAndStatusSection/dmAddress/dmIdent, '', '')"/>
    <fo:block-container id="{$dmIdent}">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:apply-templates/>
    </fo:block-container>
  </xsl:template> -->

  <xsl:template match="frontMatter">
    <xsl:apply-templates/>
  </xsl:template>

  </xsl:transform>