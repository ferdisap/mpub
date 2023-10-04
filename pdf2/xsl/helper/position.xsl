<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- xsl:number mencari posisi sesuai urutannya pada data module -->
  <!-- position() mencari posisi sesuai urutan hasil matched="" atau select="" -->

  <xsl:template name="getPosition">
    <xsl:param name="xpath"/>
    <xsl:param name="idCompared"/>
    <xsl:param name="includedParent" select="'no'"/>
    <xsl:param name="parentName" select="'levelledPara'"/>
  
    <xsl:variable name="pos">
      <xsl:for-each select="$xpath">
        <xsl:if test="@id = $idCompared">
          <xsl:if test="$includedParent = 'yes'">
            <xsl:call-template name="checkParent"><xsl:with-param name="parentName" select="$parentName"/></xsl:call-template>
          </xsl:if><xsl:number/>
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    
    <xsl:value-of select="$pos"/>
  </xsl:template>
  
  <xsl:template name="checkParent">
    <xsl:param name="parentName" select="'levelledPara'"/>
    <xsl:if test="name(parent::*) = $parentName">
      <xsl:call-template name="getParentPosition">
        <xsl:with-param name="parentName" select="$parentName"/>
      </xsl:call-template><xsl:value-of select="'.'"/>
    </xsl:if>
  </xsl:template>
  
  <xsl:template name="getParentPosition">
    <xsl:param name="compared" select=".."/>
    <xsl:param name="parentName" select="'levelledPara'"/>
    <xsl:if test="$parentName = 'levelledPara'">
      <xsl:for-each select="../../levelledPara">
        <xsl:if test=". = $compared">
          <xsl:call-template name="checkParent">
            <xsl:with-param name="parentName" select="$parentName"/>
          </xsl:call-template><xsl:number/>
        </xsl:if>
      </xsl:for-each>
    </xsl:if>
  </xsl:template>

</xsl:stylesheet>