<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl">

  <xsl:output method="xml" omit-xml-declaration="yes"/>

  <!-- <xsl:include href="attribute/id.xsl" />
  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:include href="helper/position.xsl"/>
  <xsl:include href="./group/textElemGroup.xsl" />
  <xsl:include href="./group/listElemGroup.xsl" />
  <xsl:include href="./element/levelledPara.xsl"/>
  <xsl:include href="./element/warningcautionnote.xsl"/> -->
  <!-- <xsl:include href="./element/descrCrew.xsl"/> -->
  
  <!-- <xsl:param name="padding_levelPara_1"/>
  <xsl:param name="padding_levelPara_2"/>
  <xsl:param name="padding_levelPara_3"/>
  <xsl:param name="padding_levelPara_4"/>
  <xsl:param name="padding_levelPara_5"/> -->

  <!-- <xsl:param name="fontsize_levelledPara_title_1"/>
  <xsl:param name="fontsize_levelledPara_title_2"/>
  <xsl:param name="fontsize_levelledPara_title_3"/>
  <xsl:param name="fontsize_levelledPara_title_4"/>
  <xsl:param name="fontsize_levelledPara_title_5"/>

  <xsl:param name="dmOwner"/>
  <xsl:param name="absolute_asset_path"/> -->

  <xsl:template match="dmodules">
    <xsl:apply-templates select="//content/crew"/>
  </xsl:template>
  
  <xsl:template match="dmodule">
    <div>aaa</div>
  </xsl:template>

  <xsl:template match="crew">
    <xsl:apply-templates select="descrCrew"/>
  </xsl:template>

  <xsl:template match="descrCrew">
    <!-- kalau ada halaman yang bermasalah, coba hapus div ini -->
    <div>
      <xsl:apply-templates/>
      <!-- aa -->
    </div>
  </xsl:template>

  <xsl:template match="crewDrill">
    <br/>
    <div>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </div>
    <br/>
  </xsl:template>

  <xsl:template match="title[parent::crewDrill]">
    <h4><xsl:apply-templates/></h4>
  </xsl:template>

  <xsl:template match="subCrewDrill">
    <span>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
      <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::set_last_crewDrillStep',0)"/> -->
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
            <xsl:variable name="ifpos" select="parent::if/count(preceding-sibling:crewDrillStep)"/>
            <xsl:variable name="steppos"><xsl:number/></xsl:variable>
            <!-- <xsl:number format="{$format}" value="count(parent::case/preceding-sibling::crewDrillStep | preceding-sibling::case/crewDrillStep) + $pos"/> -->
            <xsl:number/>
          </xsl:when>
          <xsl:otherwise>
            <!-- <xsl:number format="{$format}" value="count(preceding-sibling::crewDrillStep | preceding-sibling::case/crewDrillStep) + 1"/> -->
            <xsl:number/>
          </xsl:otherwise>
          <!-- <xsl:when test="parent::case">
            <xsl:variable name="pos"><xsl:number/></xsl:variable>
            <xsl:number format="{$format}" value="count(parent::case/preceding-sibling::crewDrillStep | preceding-sibling::case/crewDrillStep) + $pos"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:number format="{$format}" value="count(preceding-sibling::crewDrillStep | preceding-sibling::case/crewDrillStep) + 1"/>
          </xsl:otherwise> -->
        </xsl:choose>
      </xsl:if>

      <xsl:if test="$orderedStepsFlag = '0'">
        <xsl:text>-</xsl:text>
      </xsl:if>
    </xsl:variable>

    <table style="width:100%;page-break-inside: avoid;">
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <tr>
        <td style="width:7%;text-align:left"><xsl:value-of select="$num"/></td>
        <td style="width:93%;text-align:left;">
          <xsl:apply-templates>
            <xsl:with-param name="memorizeStepFlag" select="$memorizeStepFlag"/>
            <xsl:with-param name="separatorStyle" select="$separatorStyle"/>
          </xsl:apply-templates>
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="if | elseIf">
    <xsl:apply-templates/>
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
        </td>
      </tr>
    </table>
  </xsl:template>

  <xsl:template match="challenge">
    <xsl:param name="separator"/>
    <xsl:apply-templates/>
  </xsl:template>

  <xsl:template match="crewMemberGroup">
    <!-- jangan diatur font-size karena akan membuat menambah y dan pagebreak setelahnya sehinggal ada sisa halaman kosong panjang, walaupun size lebih kecil -->
    <!-- <span captionline="true" calign="T" style="font-size:7" fillcolor="255,255,255" textcolor="0,0,0"> -->
    <span captionline="true" calign="T" fillcolor="255,255,255" textcolor="0,0,0">
      <xsl:text>[</xsl:text>
      <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::getCrewMember', .)"/>
      <xsl:text>]</xsl:text>
    </span>
    <!-- <xsl:value-of select="php:function('Ptdi\Mpub\Pdf2\DMC::getCrewMember', .)"/> -->
  </xsl:template>

  <xsl:template match="case">
    <span>
      <xsl:call-template name="id"/>
      <xsl:call-template name="cgmark"/>
      <xsl:apply-templates/>
    </span>
  </xsl:template>

  <xsl:template match="caseCond">
    <br/>
    <span>
      <xsl:apply-templates/>
    </span>
    <br/>
  </xsl:template>

  <xsl:template match="crewProcedureName">
    <span><xsl:apply-templates/></span>
  </xsl:template>

</xsl:stylesheet>
