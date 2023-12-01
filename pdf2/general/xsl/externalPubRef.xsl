<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template match="externalPubRef">
    <div>
      <xsl:call-template name="cgmark_externalPubRef"/>
      <xsl:text>This page contains the content of external publication. Refer to: </xsl:text>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="externalPubRefIdent">
    <xsl:apply-templates select="externalPubCode"/>
    <xsl:apply-templates select="externalPubTitle"/>
    <xsl:apply-templates select="externalPubIssueInfo"/>
  </xsl:template>

  <xsl:template match="externalPubCode">
    <a>
      <xsl:attribute name="href">
        <xsl:value-of select="."/>
      </xsl:attribute>
      <xsl:if test="@pubCodingScheme">
        <xsl:value-of select="@pubCodingScheme"/>
        <xsl:text>: </xsl:text>
      </xsl:if>
      <xsl:apply-templates/>
    </a>  
    <br/>
  </xsl:template>
  
  <xsl:template match="externalPubTitle">
    <h1>
      <xsl:apply-templates/>
    </h1>  
  </xsl:template>
  
  <xsl:template match="externalPubIssueInfo">
    <xsl:apply-templates/>
  </xsl:template>


  <xsl:template name="cgmark_externalPubRef">
    <xsl:param name="changeMark" select="@changeMark"/>
    <xsl:param name="changeType" select="@changeType"/>
    <xsl:param name="reasonForUpdateRefIds" select="@reasonForUpdateRefIds"/>

    <xsl:if test="$changeMark">

      <xsl:attribute name="changeMark">
        <xsl:value-of select="$changeMark"/>
      </xsl:attribute>
      <xsl:attribute name="changeType">
        <xsl:value-of select="$changeType"/>
      </xsl:attribute>
      <xsl:attribute name="reasonForUpdateRefIds">
        <xsl:value-of select="$reasonForUpdateRefIds"/>
      </xsl:attribute>

    </xsl:if>
  </xsl:template>
  
</xsl:stylesheet>