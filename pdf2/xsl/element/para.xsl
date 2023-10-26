<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="para">
    <xsl:param name="usefootnote" select="'yes'"/>
    <xsl:choose>
      <xsl:when test="ancestor::listItem">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>
      </xsl:when>
      <xsl:when test="parent::footnote or parent::response">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>      
      </xsl:when>
      <xsl:when test="parent::challenge">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
          <!-- <xsl:text> asaasasa </xsl:text> -->
          <!-- coba untuk separator style -->
          <!-- <xsl:text> %s%</xsl:text>  -->
        </span>      
      </xsl:when>
      <xsl:otherwise>
        <!-- <p style="page-break-inside: avoid;border:1px solid red"> -->
        <p style="page-break-inside: avoid;">
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates>
            <xsl:with-param name="usefootnote" select="$usefootnote"/>
          </xsl:apply-templates> 
        </p>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
</xsl:stylesheet>