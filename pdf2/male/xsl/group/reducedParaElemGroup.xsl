<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- <xsl:include href="./attribute/cgmark.xsl" /> -->
  <!-- 
    Tidak bisa kasi include karena ini xsl ini di call oleh para.xsl/figure.xsl, etc
   -->
   
  <xsl:include href="part/caption.xsl"/>
  
  <xsl:param name="dmOwner"/>

  <xsl:output method="xml"/>
  
  <xsl:template match="changeInline">
    <span>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="emphasis">
    <xsl:choose>
      <xsl:when test="@emphasisType = 'em01'">
        <b style="border:1px solid red"><xsl:apply-templates/></b>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em02'">
        <i><xsl:apply-templates/></i>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em03'">
        <u>value</u>
      </xsl:when>
      <xsl:when test="@emphasisType = 'em05'">
        <del>value</del>
      </xsl:when>
      <xsl:otherwise>
        <span><xsl:apply-templates/></span>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
  
  <xsl:template match="subScript">
    <sub>subScript</sub>
  </xsl:template>
  <xsl:template match="superScript">
    <sup>superScript</sup>
  </xsl:template>

  <xsl:template match="reducedRandomList">
    <ul>
      <xsl:call-template name="cgmark"/>
      <xsl:for-each select="reducedRandomListItem">
        <li>
          <xsl:apply-templates/>
        </li>
      </xsl:for-each>
    </ul>         
  </xsl:template> 



</xsl:stylesheet>