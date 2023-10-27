<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">
  
  <xsl:include href="attribute/id.xsl" />
  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:include href="helper/position.xsl"/>
  <xsl:include href="./group/textElemGroup.xsl" />
  <xsl:include href="./group/listElemGroup.xsl" />
  <xsl:include href="./element/levelledPara.xsl"/>
  <!-- <xsl:include href="./element/descrCrew.xsl"/> -->
  <xsl:include href="./element/warningcautionnote.xsl"/>
  
  <xsl:param name="padding_levelPara_1"/>
  <xsl:param name="padding_levelPara_2"/>
  <xsl:param name="padding_levelPara_3"/>
  <xsl:param name="padding_levelPara_4"/>
  <xsl:param name="padding_levelPara_5"/>

  <xsl:param name="fontsize_levelledPara_title_1"/>
  <xsl:param name="fontsize_levelledPara_title_2"/>
  <xsl:param name="fontsize_levelledPara_title_3"/>
  <xsl:param name="fontsize_levelledPara_title_4"/>
  <xsl:param name="fontsize_levelledPara_title_5"/>

  <xsl:param name="dmOwner"/>
  <xsl:param name="absolute_asset_path"/>
  
  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <xsl:template match="dmodule">
    <xsl:apply-templates select="//content/crew"/>
  </xsl:template>
  
  <xsl:template match="crew">
    <xsl:apply-templates select="descrCrew"/>
  </xsl:template>

  <xsl:template match="descrCrew">
    <!-- kalau ada halaman yang bermasalah, coba hapus div ini -->
    <!-- <div> -->
      <xsl:apply-templates/>
    <!-- </div> -->
  </xsl:template>

  <xsl:template match="crewDrill">
    <div>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </div>
  </xsl:template>

  <xsl:template match="subCrewDrill">
    <span style="border:1px solid red">
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="crewDrillStep">
    <xsl:param name="memorizeStepFlag">
      <xsl:choose>
        <xsl:when test="@memorizeStepsFlag">
          <xsl:text>font-weight:bold</xsl:text>
        </xsl:when>
      </xsl:choose>
    </xsl:param>
    <xsl:param name="separatorStyle">
      <xsl:choose>
        <xsl:when test="@separatorStyle = 'line'">
          <xsl:text>- </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>.</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:param>
    <xsl:param name="orderedStepsFlag">
      <xsl:choose>
        <xsl:when test="ancestor-or-self::*[@orderedStepsFlag][1]">
          <xsl:value-of select="ancestor-or-self::*[@orderedStepsFlag][1]/@orderedStepsFlag"/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>0</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:param>
    <span>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates>
        <xsl:with-param name="memorizeStepFlag" select="$memorizeStepFlag"/>
        <xsl:with-param name="separatorStyle" select="$separatorStyle"/>
        <xsl:with-param name="orderedStepsFlag" select="$orderedStepsFlag"/>
      </xsl:apply-templates>
    </span>
    <style>
      td {
        border:1px solid red;
      }
    </style>
  </xsl:template>

  <xsl:template match="challengeAndResponse">
    <xsl:param name="memorizeStepFlag"/>
    <xsl:param name="separatorStyle"/>
    <xsl:param name="orderedStepsFlag"/>
    <xsl:variable name="num">
      <xsl:for-each select="ancestor::crewDrillStep">
        <xsl:if test="$orderedStepsFlag = '1'">
          <xsl:variable name="qty" select="count(ancestor::*[@orderedStepsFlag = '1'])"/>
          <xsl:if test="($qty mod 2)">
            <xsl:number/>
          </xsl:if>
          <xsl:if test="not($qty mod 2)">
            <xsl:number format="a"/>
          </xsl:if>
        </xsl:if>
        <xsl:if test="$orderedStepsFlag = '0'">
          <xsl:text>-</xsl:text>
        </xsl:if>
      </xsl:for-each>
    </xsl:variable>
    <!-- <xsl:param name="separatorStyle" select="../@separatorStyle"/> -->
    <!-- <xsl:variable name="num">
      <xsl:for-each select="ancestor::crewDrillStep">
        <xsl:choose>
            <xsl:when test="ancestor::*[@orderedStepsFlag]">
              <xsl:variable name="ord" select="ancestor::*[@orderedStepsFlag][1]/@orderedStepsFlag"/>
              <xsl:if test="$ord = '1'">
                <xsl:variable name="qty" select="count(ancestor::*[@orderedStepsFlag = '1'])"/>
                <xsl:if test="($qty mod 2)">
                  <xsl:number/>
                </xsl:if>
                <xsl:if test="not($qty mod 2)">
                  <xsl:number format="a"/>
                </xsl:if>
              </xsl:if>
              <xsl:if test="$ord = '0'">
                <xsl:text>-</xsl:text>
              </xsl:if>
            </xsl:when>
            <xsl:otherwise>
              <xsl:text>-</xsl:text>
            </xsl:otherwise>
        </xsl:choose>
      </xsl:for-each>
    </xsl:variable> -->
    
    <!-- <xsl:variable name="separator">
      <xsl:choose>
        <xsl:when test="$separatorStyle = 'line'">
          <xsl:text>- </xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>.</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable> -->
    <!-- <table style="width:100%;"> -->
    <!-- <table style="width:100%;page-break-inside: avoid;"> -->
    <table style="width:100%;page-break-inside: avoid;">
      <tr>
        <td style="width:5%"><xsl:value-of select="$num"/></td>
        <td style="width:95%;text-align:left">
            <span challenge="true" separator="{$separatorStyle}">
              <xsl:apply-templates select="challenge"/>
            </span>
            <span response="true">
              <xsl:apply-templates select="response"/>
            </span>
            <xsl:text> | </xsl:text>
            <span crewmember="true">
              <xsl:text>&#160;</xsl:text>
              <xsl:apply-templates select="descendant::crewMemberGroup"/>
            </span>
            <br/>
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="challenge">
    <xsl:param name="separator"/>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="crewMemberGroup">
    <span captionline="true" calign="T" style="font-size:5" fillcolor="0,255,255" textcolor="0,0,0">
      <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::getCrewMember', .)"/>
    </span>
  </xsl:template>

</xsl:stylesheet>
