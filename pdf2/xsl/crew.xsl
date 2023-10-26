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
    <span>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="challengeAndResponse">
    <xsl:param name="memorizeStepFlag" select="../@memorizeStepFlag"/>
    <xsl:param name="separatorStyle" select="../@separatorStyle"/>
    <xsl:variable name="num">
      <xsl:for-each select="ancestor::crewDrillStep">
        <xsl:choose>
            <xsl:when test="ancestor::*[@orderedStepsFlag]">
              <xsl:variable name="ord" select="ancestor::*[@orderedStepsFlag][1]/@orderedStepsFlag"/>
              <xsl:if test="$ord = '1'">
                <!-- disini jika ingin format number nya 'a' atau '1' -->
                <!-- <xsl:number/> -->
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
    </xsl:variable>
    
    <xsl:variable name="separator">
      <xsl:if test="$separatorStyle = 'dot'">
        <xsl:text>.</xsl:text>
      </xsl:if>
      <xsl:if test="$separatorStyle = 'line'">
        <xsl:text>- </xsl:text>
      </xsl:if>
    </xsl:variable>
    <table style="width:100%">
      <tr>
        <td style="width:70%;">
          <xsl:apply-templates select="challenge">
            <xsl:with-param name="num" select="$num"/>
            <xsl:with-param name="separator" select="$separator"/>
          </xsl:apply-templates>
        </td>
        <td style="width:10%;"><xsl:apply-templates select="response"/></td>
        <td style="width:20%;text-align:center"><xsl:apply-templates select="descendant::crewMemberGroup"/></td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="challenge">
    <xsl:param name="num"/>
    <xsl:param name="separator"/>
    <table style="width:100%">
      <tr>
        <td style="width:10%"><xsl:value-of select="$num"/>&#160;</td>
        <td style="width:90%;text-align:left">
          <xsl:apply-templates/>
          <span separator="{$separator}">&#160;</span>
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="crewMemberGroup">
    <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::getCrewMember', .)"/>
  </xsl:template>

</xsl:stylesheet>