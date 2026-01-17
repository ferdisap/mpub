<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <xsl:template match="identNumber">
    <xsl:param name="printManufatureCode" select="boolean(1)"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_controlAuthority"/>
    <fo:block>
      <xsl:apply-templates select="partAndSerialNumber"/>
      <xsl:if test="$printManufatureCode">
        <xsl:apply-templates select="manufacturerCode"/>
      </xsl:if>
      <xsl:apply-templates select="refs"/>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

  <!-- 
    serialNumber sebenernya bisa menggunakan range seperti fungsi pada get_applicability, tapi di sini hanya mengganti karakter '~' menjadi 'thru'  
  -->
  <xsl:template match="partAndSerialNumber">
    <fo:block>
      <fo:inline><xsl:value-of select="partNumber"/></fo:inline>
      <xsl:for-each select="serialNumber">
        <xsl:text>,&#160; SN: </xsl:text>
        <fo:inline>
          <xsl:choose>
            <xsl:when test="@serialNumberForm = 'single'">
              <xsl:value-of select="@serialNumberValue"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="php:function('preg_replace', '~', ' thru ', string(@serialNumberValue))"/>
            </xsl:otherwise>
          </xsl:choose>
        </fo:inline>
      </xsl:for-each>
    </fo:block>
  </xsl:template>

  <xsl:template match="manufacturerCode">
    <xsl:param name="useInterpreter" select="boolean(1)"/>
    <xsl:call-template name="cgmark_begin"/>
    <xsl:call-template name="add_controlAuthority"/>
    <xsl:call-template name="add_security"/>
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:choose>
        <xsl:when test="$useInterpreter">
          <xsl:variable name="companyName" select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','enterpriseCode', string(.), 'extension', 'abbreviation', 'default')" />
          <xsl:choose>
            <xsl:when test="$companyName">
              <xsl:value-of select="$companyName"/>
            </xsl:when>
            <xsl:otherwise>
              <xsl:value-of select="."/>
            </xsl:otherwise>
          </xsl:choose>          
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </fo:block>
    <xsl:call-template name="cgmark_end"/>
  </xsl:template>

</xsl:stylesheet>