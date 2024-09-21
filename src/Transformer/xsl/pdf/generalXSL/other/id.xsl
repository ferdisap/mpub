<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <!-- xsl:number mencari posisi sesuai urutannya pada data module -->
  <!-- position() mencari posisi sesuai urutan hasil matched="" atau select="" -->

  <xsl:template name="add_id">
    <xsl:param name="attributeName">id</xsl:param>
    <xsl:param name="id"/>
    <xsl:param name="force"/>
    <xsl:choose>
      <xsl:when test="string(@id) != ''">
        <xsl:attribute name="{$attributeName}"><xsl:value-of select="@id"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="$id">
        <xsl:attribute name="{$attributeName}"><xsl:value-of select="$id"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="$force = 'yes'">
        <xsl:choose>
          <xsl:when test="@referredFragment">
            <xsl:attribute name="{$attributeName}"><xsl:value-of select="string(@referredFragment)"/></xsl:attribute>
          </xsl:when>
          <xsl:otherwise>
            <xsl:if test="string(@id) = ''">
              <xsl:attribute name="{$attributeName}"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
            </xsl:if>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <!-- 
    $force adalah boolean 
   -->
  <xsl:template name="add_referredFragment">
    <xsl:param name="attributeName">id</xsl:param>
    <xsl:param name="referredFragment"/>
    <xsl:param name="force"/>
    <xsl:choose>
      <xsl:when test="string(@referredFragment) != ''">
        <xsl:attribute name="{$attributeName}"><xsl:value-of select="string(@referredFragment)"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="$referredFragment">
        <xsl:attribute name="{$attributeName}"><xsl:value-of select="$referredFragment"/></xsl:attribute>
      </xsl:when>
      <xsl:when test="boolean($force)">
        <xsl:if test="string(@referredFragment) = ''">
          <xsl:attribute name="{$attributeName}"><xsl:value-of select="generate-id(.)"/></xsl:attribute>
        </xsl:if>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

</xsl:stylesheet>