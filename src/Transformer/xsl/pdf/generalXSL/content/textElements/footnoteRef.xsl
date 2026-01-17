<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <xsl:template match="footnoteRef">
    <xsl:param name="id" select="string(@internalRefId)"/>
    <fo:inline color="#3366CC" font-size="8pt" vertical-align="super">
      <xsl:call-template name="add_inline_controlAuthority"/>
      <fo:basic-link internal-destination="{$id}">
        <xsl:for-each select="//*[string(@id) = $id]">
          <xsl:number level="any"/>
        </xsl:for-each>
      </fo:basic-link>
    </fo:inline>
  </xsl:template>
  
</xsl:transform>