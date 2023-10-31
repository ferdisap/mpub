<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml"/>
  
  <!-- <xsl:include href="title.xsl"/> -->
  <!-- <xsl:include href="listItem.xsl"/>  -->

  <xsl:template match="sequentialList">
    <xsl:if test="title">
      <span><br style="line-height:1.25"/><b><xsl:value-of select="title"/></b></span>
    </xsl:if>
    <!-- <ol style="line-height:0.5"> -->
    <ol>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates />
    </ol>         
  </xsl:template> 

  <xsl:template match="randomList">
    <xsl:if test="title">
      <span><br style="line-height:1.25"/><b><xsl:value-of select="title"/></b></span>
    </xsl:if>
    <!-- <ul style="line-height:0.5"> -->
    <ul>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </ul>         
  </xsl:template> 
  
  <xsl:template match="listItem">
    <li style="line-height:1.25">
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </li>
  </xsl:template>

  <xsl:template match="note">
    <div class="d-flex justify-content-center">
      <div class="note">
        <div class="heading"><span>NOTE</span></div>
        <xsl:apply-templates/>
      </div>
    </div>
  </xsl:template>

  <xsl:template match="title">
    <b>foo<xsl:apply-templates/></b>
  </xsl:template>


</xsl:stylesheet>