<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:output method="xml" omit-xml-declaration="yes" />

  <xsl:param name="absolute_path_csdbInput"></xsl:param>
  <xsl:param name="prefixnum"></xsl:param>

  <xsl:template match="figure">
    <xsl:variable name="figTitle">
      <xsl:value-of select="title"/>
    </xsl:variable>
    <xsl:variable name="qtyGra">
      <xsl:value-of select="count((graphic))"/>
    </xsl:variable>

    <div style="text-align:center;">
      <!-- untuk elemen figure-->
      <xsl:call-template name="cgmark"/>

      <xsl:for-each select="graphic">
        <xsl:variable name="graIndex"><xsl:number/></xsl:variable>
        <xsl:variable name="infoEntityIdent">
          <xsl:value-of select="$absolute_path_csdbInput"/>
          <xsl:value-of select="@infoEntityIdent"/>
        </xsl:variable>

        <div style="text-align:center;">
          
          <div>
            <!-- untuk elemen graphic -->
            <xsl:call-template name="cgmark"/>

            <img src="{$infoEntityIdent}">
              <xsl:if test="@reproductionWidth">
                <xsl:attribute name="width"><xsl:value-of select="@reproductionWidth"/></xsl:attribute>
              </xsl:if>
              <xsl:if test="@reproductionHeight">
                <xsl:attribute name="height"><xsl:value-of select="@reproductionHeight"/></xsl:attribute>
              </xsl:if>
            </img>
          </div>
          
          <!-- <span changemark="1" reasonforupdaterefids="rfu-003"> -->
          <span>
            <xsl:if test="parent::figure/title/@changeMark = '1'">
              <xsl:call-template name="cgmark">
                <xsl:with-param name="changeMark" select="parent::figure/title/@changeMark"/>
                <xsl:with-param name="changeType" select="parent::figure/title/@changeType"/>
                <xsl:with-param name="reasonForUpdateRefIds" select="parent::figure/title/@reasonForUpdateRefIds"/>
              </xsl:call-template>
            </xsl:if>
            <xsl:text>Fig.&#160;</xsl:text>
            <xsl:value-of select="$prefixnum"/>&#160;<xsl:value-of select="$figTitle"/>
          </span>

          <xsl:if test="$qtyGra > 1">
            <span>
              <xsl:text>&#160;&#40;sheet&#160;</xsl:text>
              <xsl:value-of select="$graIndex"/>
              <xsl:text>&#160;of&#160;</xsl:text>
              <xsl:value-of select="$qtyGra"/>
              <xsl:text>&#41;</xsl:text>
            </span>          
          </xsl:if>

        </div>

        <xsl:if test="$qtyGra > 1">
          <br/><br/>
        </xsl:if>
      </xsl:for-each>

    </div>
  </xsl:template>
</xsl:stylesheet>