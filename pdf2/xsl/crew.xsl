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

  <xsl:template name="crewDrill">
    <xsl:call-template name="id"/>
    <xsl:call-template name="cgmark"/>
    <!-- <xsl:variable name="orderedStepsFlag" select="@orderedStepsFlag"/> -->
    <xsl:apply-templates/>
    <xsl:for-each select="subCrewDrill">
      <xsl:call-template name="crewDrill"/>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="crewDrillStep">
    <xsl:variable name="olul">
      <xsl:variable name="v">
        <xsl:choose>
          <xsl:when test="@orderedStepsFlag">
            <xsl:value-of select="@orderedStepsFlag"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:value-of select="ancestor::*[@orderedStepsFlag = '0']"/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:variable>
      <xsl:choose>
        <xsl:when test="$v = '0'">
          <xsl:text>ul</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>ol</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>
    
    <xsl:for-each select="*">
      <xsl:choose>
        <xsl:when test="name() = 'challengeAndResponse'">
          <!-- <xsl:element name="{$olul}">
            <xsl:apply-templates select="."/>
          </xsl:element> -->
          <xsl:apply-templates select="."/>
        </xsl:when>
        <xsl:otherwise>
          <xsl:apply-templates select="."/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:for-each>
  </xsl:template>

  <xsl:template match="challengeAndResponse">
    <xsl:param name="memorizeStepFlag" select="../@memorizeStepFlag"/>
    <xsl:param name="separatorStyle" select="../@separatorStyle"/>
    <xsl:variable name="num">
      <xsl:number/>
    </xsl:variable>
    <xsl:variable name="separator">
      <xsl:if test="$separatorStyle = 'dot'">
        <xsl:text>.</xsl:text>
      </xsl:if>
      <xsl:if test="$separatorStyle = 'line'">
        <xsl:text>-</xsl:text>
      </xsl:if>
    </xsl:variable>
    <table style="width:100%">
      <tr>
        <td style="width:5%;border:1px solid red"><xsl:value-of select="$num"/></td>
        <td style="width:65%;text-align:left;border:1px solid red"><xsl:apply-templates select="challenge"/><span separator="{$separator}">&#160;</span></td>
        <td style="width:10%;border:1px solid red"><xsl:apply-templates select="response"/></td>
        <td style="width:20%;border:1px solid red"><xsl:apply-templates select="descendant::crewMember"/></td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="crewMember">
    <xsl:apply-templates select="@crewMemberType"/>
  </xsl:template>

</xsl:stylesheet>