<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes" />
  
  <xsl:param name="dmOwner"/>

  <xsl:template match="footnote">
    <!-- syaratnya, jangan tambah line-height di footnote ini, karena akan berdampak ke text
    selanjutnya yang bukan footnote -->
    
      <span isfootnote="true" style="font-size:6;text-align:justify">
        <xsl:attribute name="id">
          <xsl:choose>
            <xsl:when test="@id">
              <xsl:value-of select="@id"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="generate-id()"/>
            </xsl:otherwise>
          </xsl:choose>
        </xsl:attribute>
        <xsl:call-template name="id" />
        <xsl:call-template name="cgmark" />
        <xsl:apply-templates />
    </span>
  </xsl:template>

  <xsl:template match="footnoteRef">
    <xsl:param name="internalRefId" select="@internalRefId"/>
    <a style="text-decoration:none">
      <xsl:attribute name="href"><xsl:value-of select="$dmOwner"/>,<xsl:value-of select="$internalRefId"/></xsl:attribute>
      <xsl:variable name="pos">
        <xsl:for-each select="//footnote[@id = $internalRefId]">
          <xsl:number/>
        </xsl:for-each>
      </xsl:variable>
      <sup>[<xsl:value-of select="$pos"/>]</sup>
    </a>
  </xsl:template>

</xsl:stylesheet>