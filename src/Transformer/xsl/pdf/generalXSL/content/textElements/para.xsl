<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="reducedPara">
    <fo:block page-break-before="avoid">
      <xsl:call-template name="style-para"/>
      <xsl:apply-templates/>
    </fo:block>
  </xsl:template>

  <xsl:template match="reducedRandomListItemPara">
    <fo:block page-break-before="avoid">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:call-template name="style-para"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="para">
    <xsl:param name="level"/>
    <xsl:call-template name="add_id"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="style-para">
        <xsl:with-param name="level" select="$level"/>
      </xsl:call-template>
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <xsl:template match="para[parent::controlAuthorityText]">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <xsl:apply-templates/>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>
  
  <xsl:template match="simplePara">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block page-break-before="avoid">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="style-para"/>
      <xsl:apply-templates/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>
  
  <!-- supaya inline, not block -->
  <xsl:template match="para[parent::footnote]">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_inline_applicability"/>
    <xsl:call-template name="add_inline_controlAuthority"/>
    <xsl:call-template name="add_inline_security"/>
    <xsl:apply-templates/>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>
  
  <xsl:template match="notePara|warningAndCautionPara">
    <fo:block margin-top="11pt" text-align="left">
      <xsl:call-template name="style-warningcautionnotePara"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
      <xsl:apply-templates/>     
    </fo:block>
  </xsl:template>

  <xsl:template match="para[parent::challenge] | para[parent::response] | para[parent::crewProcedureName]">
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_applicability"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>

    <xsl:choose>
      <xsl:when test="following-sibling::*">
        <fo:block>
          <xsl:call-template name="add_id"/>
          <xsl:apply-templates/>
        </fo:block>
      </xsl:when>
      <xsl:otherwise>
        <fo:inline>
          <xsl:call-template name="add_id"/>
          <xsl:apply-templates/>
        </fo:inline>
      </xsl:otherwise>
    </xsl:choose>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

    <xsl:template match="para[parent::listItem]">
    <fo:block margin-bottom="3pt">
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_applicability"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>
</xsl:transform>