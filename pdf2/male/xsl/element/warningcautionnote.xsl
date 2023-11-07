<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>
  <!-- <xsl:param name="absolute_path_csdbInput"></xsl:param> -->
  
  <xsl:template match="warning">
    <xsl:variable name="warning_logo">
    <xsl:value-of select="$absolute_asset_path"/>
    <xsl:text>warning.jpg</xsl:text>
    </xsl:variable>
    <table style="text-align:center;width:100%">
      <tr>
        <td>
          <img src="{$warning_logo}" width="20mm"/>
        </td>
      </tr>
      <tr>
        <td style="width:15%">&#160;</td>
        <td style="width:70%;text-align:left">
          <xsl:apply-templates select="warningAndCautionPara"/>
        </td>
        <td style="width:15%">&#160;</td>
      </tr>
    </table>
    <!-- <br/><br/> -->
  </xsl:template>
  
  <xsl:template match="caution">
    <xsl:variable name="caution_logo">
    <xsl:value-of select="$absolute_asset_path"/>
    <xsl:text>caution.jpg</xsl:text>
    </xsl:variable>
    <table style="text-align:center;width:100%">
      <tr>
        <td>
          <img src="{$caution_logo}" width="20mm"/>
        </td>
      </tr>
      <tr>
        <td style="width:15%">&#160;</td>
        <td style="width:70%;text-align:left">
          <xsl:apply-templates select="warningAndCautionPara"/>
        </td>
        <td style="width:15%">&#160;</td>
      </tr>
    </table>
    <!-- <br/><br/> -->
  </xsl:template>
  
  <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\male\DMC_male::dump',$note_logo)"/> -->
  <xsl:template match="note">
    <xsl:variable name="note_logo">
      <xsl:value-of select="$absolute_path_csdbInput"/>
      <xsl:value-of select="symbol/@infoEntityIdent"/>
    </xsl:variable>
    <xsl:variable name="border">
      <xsl:if test="@noteType = 'warning'">
        <xsl:text>border-bottom:2px solid red</xsl:text>
      </xsl:if>
      <xsl:if test="@noteType = 'caution'">
        <xsl:text>border-bottom:2px solid orange</xsl:text>
      </xsl:if>
      <xsl:if test="@noteType = 'note'">
        <xsl:text>border-bottom:2px solid grey</xsl:text>
      </xsl:if>
    </xsl:variable>
    <div>
      <table style="text-align:justify;width:100%;" cellpadding="1mm">
        <tr>
          <td style="width:100%;text-align:justify">
            <xsl:if test="child::symbol/@infoEntityIdent">
              <img src="{$note_logo}" width="20mm"/>            
            </xsl:if>
            <xsl:text>&#160;</xsl:text>
          </td>
        </tr>
        <tr>
          <td style="width:100%;text-align:justify;{$border}">
            <xsl:apply-templates select="notePara"/>
          </td>
        </tr>
      </table>
    </div>
    <br/>
  </xsl:template>

  <xsl:template match="warningAndCautionPara">
    <p>
      <xsl:apply-templates/>
    </p>
  </xsl:template>
  <xsl:template match="notePara">
    <p>
      <xsl:apply-templates/>
    </p>
  </xsl:template>
</xsl:stylesheet>