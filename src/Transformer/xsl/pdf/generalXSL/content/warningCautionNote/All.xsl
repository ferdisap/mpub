<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

<!-- 
  Outstanding:
  1. @cautionType @warningType dan @noteType belum difungsikan
  2. @vitalWarningFlag belum difungsikan
  3. tidak fully comply S1000D
 -->
  <xsl:template match="note|warning|caution">
    <xsl:param name="blockIndent">
      <xsl:call-template name="get_blockIndent"/>
    </xsl:param>
    <xsl:call-template name="cgmark_begin"/>
    <fo:block-container width="85%" page-break-inside="avoid" start-indent="0.5cm">
      <xsl:call-template name="style-warningcautionnote" />
      <fo:block start-indent="{$blockIndent}" border="1pt solid black" background-color="white" padding="3pt">
        <xsl:call-template name="add_applicability" />
        <xsl:call-template name="add_controlAuthority" />
        <xsl:call-template name="add_security" />
  
        <xsl:choose>
          <xsl:when test="symbol/@infoEntityIdent">
            <fo:block text-align="center">
              <xsl:apply-templates select="symbol"/>
            </fo:block>
          </xsl:when>
          <xsl:otherwise>
            <xsl:if test="name() = 'warning'">
              <fo:block text-align="center" font-weight="bold" text-decoration="underline">WARNING</fo:block>
            </xsl:if>
            <xsl:if test="name() = 'caution'">
              <fo:block text-align="center" font-weight="bold">CAUTIONs</fo:block>
            </xsl:if>
            <xsl:if test="name() = 'note'">
              <fo:block text-align="left" font-weight="bold">Note</fo:block>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
          
        <xsl:for-each select="*[name(.) != 'symbol']">
          <fo:block>
            <xsl:apply-templates select="."/>
          </fo:block>
        </xsl:for-each>
        <xsl:text>  </xsl:text>
      </fo:block>
    </fo:block-container>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <!-- 
    Outstanding for referenced warning and caution
    1. Belum mengakomodir external store (CIR) warning/caution karena sedang mempelajari CIR
   -->
  <xsl:template name="add_warning">
    <xsl:param name="id" select="@warningRefs"/>
    <xsl:if test="$id">
      <xsl:apply-templates select="//warningAndCautions/warning[@id = $id]"/>
    </xsl:if>
  </xsl:template>
  <xsl:template name="add_caution">
    <xsl:param name="id" select="@cautionRefs"/>
    <xsl:if test="$id">
      <xsl:apply-templates select="//warningAndCautions/caution[@id = $id]"/>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>