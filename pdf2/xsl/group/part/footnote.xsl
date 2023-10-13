<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  
  <xsl:template match="footnote" >
    <!-- syaratnya, jangan tambah line-height di footnote ini, karena akan berdampak ke text selanjutnya yang bukan footnote -->
    <!-- <sup>
    <a href="www.google.com">aaa</a>
    </sup> -->
    <!-- <span forfootnote="true"> -->
      <!-- aaa -->
      <!-- <span isfootnote="true" id="fnt-001" style="font-size:6;"> -->
      <!-- <xsl:text>&#91;?f&#93;</xsl:text> -->
        <span isfootnote="true" id="{generate-id()}" style="font-size:6;text-align:justify">
        <xsl:call-template name="id"/>
        <xsl:call-template name="cgmark"/>
        <xsl:apply-templates/>
      </span>
    <!-- </span> -->
  </xsl:template>
  
</xsl:stylesheet>