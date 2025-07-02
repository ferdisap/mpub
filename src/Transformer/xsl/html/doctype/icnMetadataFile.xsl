<?xml version="1.0" encoding="UTF-8"?>
<xsl:transform version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
  <xsl:output method="html" encoding="utf-8" indent="yes" />

  <xsl:template match="imfIdentAndStatusSection">
    <xsl:apply-templates select="imfAddress"/>
    <xsl:apply-templates select="imfStatus"/>
    <title><xsl:value-of select="imfAddress/imfAddressItems/icnTitle"/></title>
  </xsl:template>

  <xsl:template match="imfAddress">
    <meta name="imfIdentIcn" content="{imfIdent/imfCode/@imfIdentIcn}"/>
    <meta name="issueNumber" content="{imfIdent/issueInfo/@issueNumber}"/>
    <meta name="inWork" content="{imfIdent/issueInfo/@inWork}"/>
    <xsl:for-each select="imfAddressItems/legacyIdentGroup/legacyIdent">   
      <meta name="legacyIdent" content="{@legacyOrigin};{.}"/>
    </xsl:for-each>
    <xsl:for-each select="imfAddressItems/icnKeywordGroup/icnKeyword">   
      <meta name="icnKeyword" content="{.}"/>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="imfStatus">
    <meta name="securityClassification" content="{security/@securityClassification}"/>
    <meta name="responsiblePartnerCompany" content="{responsiblePartnerCompany/@enterpriseCode};{responsiblePartnerCompany/enterpriseName}"/>
    <meta name="originator" content="{originator/@enterpriseCode};{originator/enterpriseName}"/>
    <meta name="brexDmRef" content="{php:function('Ptdi\Mpub\Main\CSDBStatic::resolve_dmIdent', brexDmRef/dmRef/dmRefIdent)}"/>
    <!-- TBD for <qualityAssurance> -->
    <!-- TBD for <remarks> -->
  </xsl:template>

  <xsl:template match="imfContent">
    <div class="imfContent">
      <xsl:apply-templates select="icnVariation"></xsl:apply-templates>
    </div>
  </xsl:template>

  <!-- Harus ada @fileExtension -->
  <xsl:template match="icnVariation">
    <xsl:variable name="mime" select="php:function('Ptdi\Mpub\Transformer\Html::mimeByExt', string(@fileExtension))"/>
    <xsl:choose>
      <xsl:when test="contains($mime, 'image')">
        <xsl:call-template name="icnImage">
          <xsl:with-param name="mime" select="$mime"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="contains($mime, 'video')">
        <xsl:call-template name="icnVideo">
          <xsl:with-param name="mime" select="$mime"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="contains($mime, 'audio')">
        <xsl:call-template name="icnAudio">
          <xsl:with-param name="mime" select="$mime"/>
        </xsl:call-template>
      </xsl:when>
      <xsl:when test="contains($mime, 'model')">
        <xsl:call-template name="icnModel">
          <xsl:with-param name="mime" select="$mime"/>
        </xsl:call-template>
      </xsl:when>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="icnContents">
    <xsl:param name="mapName"/>
    <map name="{$mapName}">
      <xsl:apply-templates select="icnObjectGroup"/>
    </map>
  </xsl:template>

  <xsl:template match="icnObjectGroup">
    <xsl:apply-templates select="descendant-or-self::icnObject[not(child::icnObject)]"/>
  </xsl:template>

  <xsl:template match="icnObject">
    <area id="{@icnObjectIdent}" coords="{@objectCoordinates}" shape="{@icnObjectType}" alt="{@icnObjectName}"/>
    <xsl:if test="boolean(parent::icnObject)">
      <xsl:apply-templates select="parent::icnObject"/>
    </xsl:if>
  </xsl:template>

  <xsl:template name="icnImage">    
    <xsl:param name="mime"/>
    <xsl:variable name="icnFilename" select="concat('ICN-',php:function('strtoupper',string(//imfIdent/imfCode/@imfIdentIcn)),'.',@fileExtension)"/>      
    <div class="icnVariation" mime="{$mime}">
      <img src="s1000d:{$icnFilename}" usemap="#{$icnFilename}"/>
      <xsl:apply-templates select="icnContents">
        <xsl:with-param name="mapName" select="$icnFilename"/>
      </xsl:apply-templates>
    </div>
  </xsl:template>

  <xsl:template name="icnVideo"></xsl:template>    
  <xsl:template name="icnAudio"></xsl:template>    
  <xsl:template name="icnModel"></xsl:template>    
  
</xsl:transform>