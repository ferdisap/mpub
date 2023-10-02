<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:include href="./group/textElemGroup.xsl" />
  <xsl:include href="./group/listElemGroup.xsl" />

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  <xsl:template match="para">
    <xsl:choose>
      <xsl:when test="ancestor::listItem">
        <span>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>
      </xsl:when>
      <xsl:otherwise>
        <p>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </p>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- untuk tes doang -->
  <xsl:template match="span">
    <xsl:apply-templates/>
  </xsl:template>
</xsl:stylesheet>