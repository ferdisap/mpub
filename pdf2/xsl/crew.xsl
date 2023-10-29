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
    <span>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="crewDrillStep">
    <xsl:param name="memorizeStepFlag">
      <xsl:choose>
        <xsl:when test="@memorizeStepsFlag = '1'">
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
    <xsl:param name="seqnum"/>
    <xsl:variable name="num">
      <xsl:choose>
        <xsl:when test="not($seqnum)">
          <xsl:if test="$orderedStepsFlag = '1'">
            <xsl:variable name="qty" select="count(ancestor::*[@orderedStepsFlag = '1'])"/>
            <xsl:variable name="format">
              <xsl:if test="($qty mod 2)">
                <xsl:text>1</xsl:text>
              </xsl:if>
              <xsl:if test="not($qty mod 2)">
                <xsl:text>a</xsl:text>
              </xsl:if>
            </xsl:variable>
            <xsl:choose>
              <xsl:when test="parent::if">
                <xsl:variable name="pos"><xsl:number/></xsl:variable>
                <xsl:for-each select="parent::if">
                  <xsl:number format="{$format}" value="count(preceding-sibling::crewDrillStep) + $pos"/>
                </xsl:for-each>
              </xsl:when>
              <xsl:when test="parent::elseIf">
                <xsl:variable name="pos"><xsl:number/></xsl:variable>
                <xsl:for-each select="parent::elseIf">
                  <xsl:number format="{$format}" value="count(preceding-sibling::crewDrillStep) + $pos"/>
                </xsl:for-each>
              </xsl:when>
              <xsl:otherwise>
                <xsl:number format="{$format}"/>
              </xsl:otherwise>
            </xsl:choose>
          </xsl:if>
          <xsl:if test="$orderedStepsFlag = '0'">
            <xsl:text>-</xsl:text>
          </xsl:if>
        </xsl:when>
        <xsl:otherwise>
          <xsl:value-of select="$seqnum"/>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:variable>

    <!-- <span>
    </span> -->
    <table style="width:100%;page-break-inside: avoid;">
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <tr>
        <td style="width:7%;text-align:right"><xsl:value-of select="$num"/></td>
        <td style="width:93%;text-align:left;">
          <xsl:apply-templates>
            <xsl:with-param name="memorizeStepFlag" select="$memorizeStepFlag"/>
            <xsl:with-param name="separatorStyle" select="$separatorStyle"/>
          </xsl:apply-templates>
          <!-- <xsl:if test='not(parent::elseIf or parent::if)'>
          </xsl:if>
          <xsl:if test='boolean(parent::elseIf or parent::if)'>
            <xsl:text>&#160;</xsl:text>
          </xsl:if> -->
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="if | elseIf">
    <table style="width:100%;page-break-inside: avoid;">
      <tr>
        <td style="width:7%;text-align:right">&#160;</td>
        <td style="width:93%;text-align:left;">
          <xsl:apply-templates/>
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="challengeAndResponse">
    <xsl:param name="memorizeStepFlag"/>
    <xsl:param name="separatorStyle"/>
    <table style="width:100%;page-break-inside: avoid;">
      <tr>
        <td style="text-align:left">
            <span challenge="true" separator="{$separatorStyle}" style="{$memorizeStepFlag}">
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
            <!-- <xsl:text>foobars</xsl:text> -->
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="challenge">
    <xsl:param name="separator"/>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="crewMemberGroup">
    <span captionline="true" calign="T" style="font-size:5" fillcolor="255,255,255" textcolor="0,0,0">
      <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::getCrewMember', .)"/>
    </span>
  </xsl:template>

  <xsl:template match="case">
    <span>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="caseCond">
    <span>
      <xsl:apply-templates/>
    </span>
    <br/>
  </xsl:template>

  <xsl:template match="crewProcedureName">
    <span><xsl:apply-templates/></span>
  </xsl:template>

</xsl:stylesheet>
