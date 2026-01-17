<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xmlgraphics.apache.org/fop/extensions"
  xmlns:php="http://php.net/xsl">

  <!-- Outstanding
    1. contactPerson @prefixPerson tidak digunakan
  -->

  <xsl:template match="enterpriseSpec">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="enterpriseName"/>
      <xsl:apply-templates select="businessUnit"/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>


  

  <!-- diapnggil di frontmatter.xsl -->
  <xsl:template match="businessUnit">
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:apply-templates select="businessUnitName"/>
      <xsl:apply-templates select="businessUnitAddress"/>
      <xsl:apply-templates select="contactPerson"/>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="businessUnitAddress">
    <xsl:param name="fontSize"/>
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:if test="$fontSize">
        <xsl:attribute name="font-size"><xsl:value-of select="$fontSize"/></xsl:attribute>
      </xsl:if>
      <xsl:variable name="address">
        <xsl:if test="department">
          <xsl:text></xsl:text><xsl:value-of select="department"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="street">
          <xsl:text></xsl:text><xsl:value-of select="street"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:value-of select="city"/>
        <xsl:text>, </xsl:text>
        <xsl:value-of select="country"/>
        <xsl:text>, </xsl:text>
        <xsl:if test="state">
          <xsl:value-of select="state"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="province">
          <xsl:value-of select="province"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="building">
          <xsl:value-of select="building"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="room">
          <xsl:value-of select="room"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="postOfficeBox">
          <xsl:value-of select="postOfficeBox"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="postalZipCode">
          <xsl:value-of select="postalZipCode"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
        <xsl:if test="phoneNumber">
          <xsl:text>Phone: </xsl:text>
          <xsl:for-each select="phoneNumber">
            <xsl:value-of select="phoneNumber"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="faxNumber">
          <xsl:text>Fax: </xsl:text>
          <xsl:for-each select="faxNumber">
            <xsl:value-of select="faxNumber"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="email">
          <xsl:text>Email: </xsl:text>
          <xsl:for-each select="email">
            <xsl:value-of select="email"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="internet">
          <xsl:text>Web: </xsl:text>
          <xsl:for-each select="internet">
            <xsl:value-of select="internet"/>
            <xsl:text>, </xsl:text>
          </xsl:for-each>
        </xsl:if>
        <xsl:if test="SITA">
          <xsl:value-of select="SITA"/>
          <xsl:text>, </xsl:text>
        </xsl:if>
      </xsl:variable>
      <xsl:value-of select="php:function('preg_replace', '/,\s?$/','',$address)"/>
      <xsl:text>.</xsl:text>
      <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

  <xsl:template match="contactPerson">
    <xsl:param name="fontSize"/>
    <fo:block>
      <xsl:call-template name="add_id"/>
      <xsl:call-template name="cgmark_begin"/>
      <xsl:call-template name="add_controlAuthority"/>
      <xsl:call-template name="add_security"/>
      <xsl:if test="$fontSize">
        <xsl:attribute name="font-size"><xsl:value-of select="$fontSize"/></xsl:attribute>
      </xsl:if>
      <xsl:variable name="cp">
        <xsl:apply-templates select="lastName"/>
          <xsl:if test="middleName">
            <xsl:text> </xsl:text>
            <xsl:apply-templates select="middleName"/>
          </xsl:if>
          <xsl:if test="firstName">
            <xsl:text> </xsl:text>
            <xsl:apply-templates select="firstName"/>
          </xsl:if>
          <xsl:if test="jobTitle">
            <xsl:text>, </xsl:text>
            <xsl:apply-templates select="jobTitle"/>
            <xsl:text>,</xsl:text>
          </xsl:if>
          <xsl:if test="contactPersonAddress">
            <xsl:apply-templates select="contactPersonAddress"/>
            <xsl:text>, </xsl:text>
          </xsl:if>
        </xsl:variable>
        <xsl:value-of select="php:function('preg_replace', '/,\s?$/','',$cp)"/>
        <xsl:text>.</xsl:text>
        <xsl:call-template name="cgmark_end"/>
    </fo:block>
  </xsl:template>

</xsl:transform>