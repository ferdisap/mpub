<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="content">
    <xsl:param name="idParentBookmark"/>
    <xsl:param name="masterName"/>
    <xsl:param name="stIndent">
      <xsl:call-template name="get_stIndent"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:call-template name="get_layout_unit_length"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>

    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', //identAndStatusSection/dmAddress/dmIdent, '', '')"/>
    <fo:block-container id="{$dmIdent}" start-indent="{$stIndent}">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>

      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_dmTitle">
        <xsl:with-param name="idBookmark" select="$dmIdent"/>
        <xsl:with-param name="idParentBookmark" select="$idParentBookmark"/>
      </xsl:call-template>
      <xsl:apply-templates select="crew|description|commonRepository"/>
    </fo:block-container>
  </xsl:template>

  <xsl:template match="content[name(child::maintPlanning)]">
    <xsl:param name="idParentBookmark"/>
    <xsl:param name="masterName"/>
    <xsl:param name="stIndent">
      <xsl:call-template name="get_stIndent"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      <xsl:call-template name="get_layout_unit_length"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
    </xsl:param>

    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', //identAndStatusSection/dmAddress/dmIdent, '', '')"/>
    <!-- <fo:block-container id="{$dmIdent}" reference-orientation="90"> -->
    <fo:block-container id="{$dmIdent}">
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>

      <xsl:call-template name="add_id"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_dmTitle">
        <xsl:with-param name="idBookmark" select="$dmIdent"/>
        <xsl:with-param name="idParentBookmark" select="$idParentBookmark"/>
      </xsl:call-template>
      <xsl:apply-templates select="maintPlanning"/>
    </fo:block-container>
  </xsl:template>

  <xsl:template match="content[name(child::frontMatter)]">
    <xsl:param name="idParentBookmark"/>
    <xsl:param name="masterName"/>
    <xsl:variable name="dmIdent" select="php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', //identAndStatusSection/dmAddress/dmIdent, '', '')"/>
    <fo:block-container>
      <xsl:call-template name="add_id"/>
      <xsl:attribute name="font-size">
        <xsl:call-template name="get_defaultFontSize"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
        <xsl:call-template name="get_layout_unit_area"><xsl:with-param name="masterName" select="$masterName"/></xsl:call-template>
      </xsl:attribute>

      <xsl:choose>
        <xsl:when test="not(frontMatter/frontMatterTitlePage)">
          <xsl:call-template name="add_dmTitle">
            <xsl:with-param name="idBookmark" select="$dmIdent"/>
            <xsl:with-param name="idParentBookmark" select="$idParentBookmark"/>
          </xsl:call-template>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Pdf::fillBookmark', $dmIdent, 'Cover', '' )"/>
        </xsl:otherwise>
      </xsl:choose>

      <xsl:call-template name="add_controlAuthority"/>

      <xsl:apply-templates/>
    </fo:block-container>
  </xsl:template>

  <xsl:template match="referencedApplicGroup">
    <!-- nothing to do -->
  </xsl:template>
  

</xsl:transform>