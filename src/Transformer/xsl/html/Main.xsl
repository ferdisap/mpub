<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  xmlns:php="http://php.net/xsl">
  <xsl:output method="html" encoding="utf-8" indent="yes" />

  <!-- D:\... -->
  <!-- <xsl:param name="base_uri"/> -->

  <xsl:include href="./doctype/icnMetadataFile.xsl" />

  <xsl:template match="/">
    <xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;</xsl:text>
    <html>
      <head>
        <!-- root node name eg: dmodule, icnMetadataFile, etc -->
        <meta name="doctype" content="{name(./*)}"></meta>
        <xsl:apply-templates select="./*/child::*[1]"/>
      </head>
      <body>
        <xsl:apply-templates select="./*/child::*[2]"/>
      </body>
    </html>
  </xsl:template>
</xsl:transform>