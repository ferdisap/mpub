<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="footnote" >
    <span isfootnote="true" id="fnt-001" style="font-size:7">
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>
  
</xsl:stylesheet>