<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="para">
    <xsl:param name="usefootnote" select="'yes'"/>
    <xsl:choose>
      <!-- pakai entity #ln; jika ingin new line -->
      <xsl:when test="parent::listItem or parent::listItemTerm or parent::listItemDefinition ">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>
      </xsl:when>
      <xsl:when test="parent::footnote or parent::response or parent::crewProcedureName">
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
        </span>      
      </xsl:when>
      <xsl:when test="parent::entry">
        <!-- karena div kan tidak ada vertical space -->
        <div>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </div>      
      </xsl:when>
      <xsl:when test="parent::controlAuthorityText">
        <span>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates/>
        </span>      
      </xsl:when>
      <xsl:otherwise>
        <!-- <p style="page-break-inside: avoid;border:1px solid red"> -->
        <!-- <p style="page-break-inside: avoid;"> -->
        <p>
          <xsl:call-template name="id"/>
          <xsl:call-template name="cgmark"/>
          <xsl:apply-templates>
            <xsl:with-param name="usefootnote" select="$usefootnote"/>
          </xsl:apply-templates> 
        </p>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="symbol">
    <xsl:variable name="infoEntityIdent">
      <xsl:value-of select="$absolute_path_csdbInput"/>
      <xsl:value-of select="@infoEntityIdent"/>
    </xsl:variable>
    <img src="{$infoEntityIdent}">
      <xsl:call-template name="cgmark"/>
      <xsl:if test="@reproductionWidth">
        <xsl:attribute name="width"><xsl:value-of select="@reproductionWidth"/></xsl:attribute>
      </xsl:if>
      <xsl:if test="@reproductionHeight">
        <xsl:attribute name="height"><xsl:value-of select="@reproductionHeight"/></xsl:attribute>
      </xsl:if>
    </img>
  </xsl:template>
  
</xsl:stylesheet>