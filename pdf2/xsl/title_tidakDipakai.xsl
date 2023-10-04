<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="./group/textElemGroup.xsl" />
  <xsl:include href="attribute/cgmark.xsl" />

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:param name="level">1</xsl:param>
  <xsl:param name="prefixnum"></xsl:param>
  <xsl:param name="indentation"></xsl:param>

  <xsl:template match="title">
    <span>
    <!-- <xsl:element name="h{$level}"> -->
      <xsl:call-template name="cgmark"/>
      
      <xsl:if test="$prefixnum">
        <xsl:value-of select="$prefixnum"/>
      </xsl:if>

      <xsl:call-template name="indentation">
        <xsl:with-param name="ind" select="$indentation"/>
      </xsl:call-template>
      <xsl:apply-templates/>
    <!-- </xsl:element> -->
    </span>
  </xsl:template>

  <xsl:template name="indentation">
    <xsl:param name="ind"/>
    <xsl:if test="$ind > 0">
      <xsl:text>&#160;</xsl:text>
      <xsl:call-template name="indentation">
        <xsl:with-param name="ind" select="$ind - 1"/>
      </xsl:call-template>
    </xsl:if>
  </xsl:template>
  
</xsl:stylesheet>