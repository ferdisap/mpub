<?xml version="1.0" encoding="UTF-8"?>

<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:v-bind="https://vuejs.org/bind"
  xmlns:v-on="https://vuejs.org/on"
  xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <xsl:template name="add_applicability">
    <xsl:param name="id" select="@applicRefId" />
    <xsl:param name="prefix"><xsl:text>Applicable to: </xsl:text></xsl:param>
    <xsl:if
      test="$id">
      <fo:block text-align="left" font-size="8pt">
        <xsl:value-of select="$prefix" />
        <xsl:value-of
          select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //applic[@id = $id])" />
        <!-- <xsl:value-of
          select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //@id[. = $id])" /> -->
      </fo:block>
    </xsl:if>
  </xsl:template>

  <xsl:template name="add_inline_applicability">
    <xsl:param name="id" select="@applicRefId" />
    <xsl:param name="prefix"><xsl:text>Applicable to: </xsl:text></xsl:param>
    <xsl:if
      test="$id">
      <fo:inline text-align="left" font-size="8pt">
        <xsl:value-of select="$prefix" />
        <xsl:value-of
          select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //@id[. = $id])" />
      </fo:inline>
    </xsl:if>
  </xsl:template>

  <xsl:template name="get_applicability">
    <xsl:param name="keepOneByOne" select="boolean(0)" />
    <xsl:param name="useDisplayName"
      select="boolean(1)" />
    <xsl:param name="useDisplayText" select="number(2)" />
    <xsl:param name="applic"/>
    <!-- <xsl:param name="document"/> -->
    <xsl:variable name="applicRefId"><xsl:value-of select="@applicRefId" /></xsl:variable>
    <xsl:choose>
      <xsl:when test="$applic">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', $applic, $keepOneByOne, $useDisplayName, $useDisplayText)" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //applic[@id = $applicRefId], $keepOneByOne, $useDisplayName, $useDisplayText)" />
      </xsl:otherwise>
    </xsl:choose>
    
    <!-- <xsl:choose>
      <xsl:when test="$document">
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', $document//applic[@id = $applicRefId], $keepOneByOne, $useDisplayName, $useDisplayText)" />
      </xsl:when>
      <xsl:otherwise>
        <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //applic[@id = $applicRefId], $keepOneByOne, $useDisplayName, $useDisplayText)" />
      </xsl:otherwise>
    </xsl:choose> -->
    <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Main\CSDBObject::getApplicability', //applic[@id = $applicRefId], $keepOneByOne, $useDisplayName, $useDisplayText)" /> -->
  </xsl:template>

</xsl:transform>