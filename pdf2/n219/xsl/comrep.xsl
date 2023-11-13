<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl">

  <xsl:include href="attribute/id.xsl" />
  <xsl:include href="attribute/cgmark.xsl" />
  <xsl:include href="helper/position.xsl" />
  <xsl:include href="./group/textElemGroup.xsl" />
  <xsl:include href="./group/listElemGroup.xsl" />
  <xsl:include href="./element/warningcautionnote.xsl" />
  <xsl:include href="./element/figure.xsl" />
  <!-- <xsl:include href="./group/part/internalRef.xsl"/> -->

  <xsl:param name="dmOwner" />
  <xsl:param name="absolute_asset_path" />
  <xsl:param name="fontsize_figure_title"/>

  <xsl:output method="xml" omit-xml-declaration="yes" />

  <xsl:template match="dmodule">
    <xsl:apply-templates select="//content" />
  </xsl:template>

  <xsl:template match="commonRepository">
    <div>
      <xsl:call-template name="id" />
      <xsl:call-template name="cgmark" />
      <h1>
        <xsl:attribute name="bookmarklvl">
          <xsl:value-of select="1" />
        </xsl:attribute>
        <xsl:attribute name="bookmarktxt">
          <xsl:value-of select="//identAndStatusSection/descendant::dmTitle/techName/text()" />
        </xsl:attribute>
        <xsl:apply-templates select="//identAndStatusSection/descendant::dmTitle/techName" />
      </h1>
      <xsl:apply-templates />
    </div>
  </xsl:template>

  <xsl:template match="controlIndicatorRepository">
    <div>
      <xsl:call-template name="id" />
      <xsl:call-template name="cgmark" />
      <xsl:apply-templates />
    </div>
  </xsl:template>

  <xsl:template match="controlIndicatorGroup">
    <span>
      <xsl:call-template name="id" />
      <xsl:call-template name="cgmark" />
      <xsl:apply-templates select="internalRef" />
      <br />
      <table style="width:100%">
        <xsl:for-each select="controlIndicatorSpec">
          <tr>
            <td style="width:7%;text-align:right">
              <xsl:apply-templates select="controlIndicatorKey" />
            </td>
            <td style="width:93%;">
              <span paddingleft="5">
                <xsl:apply-templates select="controlIndicatorName" />
                <br/>
                <xsl:apply-templates select="controlIndicatorDescr"/>
              </span>
              <br />
            </td>
          </tr>
        </xsl:for-each>
      </table>

    </span>
  </xsl:template>

  <xsl:template match="controlIndicatorFunction">
    <xsl:apply-templates/>
    <xsl:variable name="pos"><xsl:number/></xsl:variable>
    <xsl:if test="$pos != count(parent::*/controlIndicatorFunction)">
      <br/>
    </xsl:if>
    
  </xsl:template>
</xsl:stylesheet>