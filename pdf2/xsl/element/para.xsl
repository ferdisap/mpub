<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="para">
    <xsl:choose>
      <xsl:when test="ancestor::listItem">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>
      </xsl:when>
      <xsl:when test="parent::footnote">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>      
      </xsl:when>
      <xsl:otherwise>
        <p style="page-break-inside: avoid;">
        <!-- <p> -->
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </p>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
</xsl:stylesheet>