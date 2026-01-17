<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <!-- 
    Outstanding:
    1. belum dibuat untuk elemen reasonForUpdate
  -->

  <!-- depreciated -->
  <!-- <xsl:template match="__cgmark">
    <xsl:param name="select"/>
    <fo:change-bar-begin change-bar-class="{generate-id(.)}" change-bar-style="solid" change-bar-width="0.5pt" change-bar-offset="{$cgmarkIndent}"/>
      <xsl:choose>
        <xsl:when test="select">
          <xsl:apply-templates select="$select"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates/>
        </xsl:otherwise>
      </xsl:choose>
    <fo:change-bar-end change-bar-class="{generate-id(.)}"/>
  </xsl:template> -->

  <xsl:template name="cgmark_begin">
    <xsl:param name="changeMark" select="@changeMark"/>
    <xsl:param name="cgmarkOffset">
      <xsl:call-template name="get_cgmarkOffset">
        <xsl:with-param name="masterName" select="php:function('Ptdi\Mpub\Transformer\Pdf::get_PDF_MasterName')"/>
      </xsl:call-template>
    </xsl:param>
    <xsl:choose>
      <xsl:when test="parent::__cgmark"></xsl:when>
      <xsl:otherwise>        
        <xsl:if test="$changeMark = '1'">
          <fo:change-bar-begin change-bar-class="{generate-id(.)}" change-bar-style="solid" change-bar-width="0.5pt" change-bar-offset="{$cgmarkOffset}"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template name="cgmark_end">
    <xsl:param name="changeMark" select="@changeMark"/>
    <xsl:choose>
      <xsl:when test="parent::__cgmark"></xsl:when>
      <xsl:otherwise>
        <xsl:if test="$changeMark = '1'">
          <fo:change-bar-end change-bar-class="{generate-id(.)}"/>
        </xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
</xsl:transform>