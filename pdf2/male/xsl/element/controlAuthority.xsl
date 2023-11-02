<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


<xsl:include href="../attribute/cgmark.xsl" />
<xsl:include href="../attribute/id.xsl" />
<xsl:include href="../group/listElemGroup.xsl" />
<xsl:include href="../group/textElemGroup.xsl" />
<xsl:include href="para.xsl" />

<!-- template ini akan digunakan untuk di footer -->
<xsl:output method="xml" omit-xml-declaration="yes"/>

<xsl:template match="controlAuthority">
  <span>
    <xsl:call-template name="id"/>
    <xsl:call-template name="cgmark"/>
    <xsl:apply-templates/>
  </span>
</xsl:template>

</xsl:stylesheet>
