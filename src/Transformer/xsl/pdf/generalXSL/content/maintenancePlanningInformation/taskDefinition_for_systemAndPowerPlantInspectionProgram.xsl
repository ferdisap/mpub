<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

   <xsl:template name="add_taskDefinition_for_systemAndPowerPlantInspectionProgram">
    <fo:block>
      <fo:table width="100%" border="1pt solid black">
        <fo:table-column column-number="1" column-width="7%"/>
        <fo:table-column column-number="2" column-width="13%"/>
        <fo:table-column column-number="3" column-width="6%"/>
        <fo:table-column column-number="4" column-width="10%"/>
        <fo:table-column column-number="5" column-width="5%"/>
        <fo:table-column column-number="6" column-width="6%"/>
        <fo:table-column column-number="7" column-width="53%"/>
        
        <fo:table-header border="1pt solid black">
          <fo:table-row>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Task No.</fo:block></fo:table-cell>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Ref</fo:block></fo:table-cell>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Met</fo:block></fo:table-cell>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Freq</fo:block></fo:table-cell>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Zon</fo:block></fo:table-cell>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Acc</fo:block></fo:table-cell>
            <fo:table-cell padding-top="4pt" padding-bottom="4pt" border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Description</fo:block></fo:table-cell>
          </fo:table-row>
        </fo:table-header>
        <fo:table-body border="1pt solid black">
          <xsl:for-each select="taskDefinition">
            <xsl:call-template name="cgmark_begin"/>
            <xsl:if test="@applicRefId or controlAuthorityRefs or @securityClassification or @commercialClassification or @caveat">
              <fo:table-row keep-together="always">
                <fo:table-cell number-columns-spanned="7" padding-bottom="4pt">
                  <xsl:call-template name="add_applicability"/>
                  <xsl:call-template name="add_controlAuthority"/> 
                  <xsl:call-template name="add_security"/>
                </fo:table-cell>
              </fo:table-row>
            </xsl:if>
            <fo:table-row text-align="center">
              <fo:table-cell border-right="1pt solid black">
                <fo:block>
                  <xsl:value-of select="@taskIdent"/>
                </fo:block>
              </fo:table-cell>
              <fo:table-cell border-right="1pt solid black">
                <fo:block>
                  <xsl:apply-templates select="refs"/>
                </fo:block>
              </fo:table-cell>
              <fo:table-cell border-right="1pt solid black">
                <fo:block>
                  <xsl:variable name="taskCode" select="string(@taskCode)"/>
                  <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','taskCode', string($taskCode), 'abbreviation', 'default')" />
                </fo:block>
              </fo:table-cell>
              <fo:table-cell border-right="1pt solid black">
                <fo:block>
                  <xsl:variable name="thresUom" select="string(limit/threshold/@thresholdUnitOfMeasure)"/>
                  <xsl:apply-templates select="limit/threshold"/>
                  <xsl:text>&#160;</xsl:text>
                  <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','thresholdUnitOfMeasure', string($thresUom), 'abbreviation', 'extension' ,'default')" />
                </fo:block>
              </fo:table-cell>
              <fo:table-cell border-right="1pt solid black">
                <xsl:if test="not(preliminaryRqmts/productionMaintData/workAreaLocationGroup/zoneRef)">
                  <fo:block/>
                </xsl:if>
                <xsl:for-each select="preliminaryRqmts/productionMaintData/workAreaLocationGroup/zoneRef">
                  <fo:block>
                    <xsl:apply-templates select="."/>
                  </fo:block>
                </xsl:for-each>
              </fo:table-cell>
              <fo:table-cell border-right="1pt solid black">
                <fo:block>
                  <xsl:apply-templates select="preliminaryRqmts/productionMaintData/workAreaLocationGroup/accessPointRef"/>
                </fo:block>
              </fo:table-cell>
              <fo:table-cell text-align="left" padding-left="2pt" padding-right="2pt">
                <fo:block><xsl:apply-templates select="task"/></fo:block>
              </fo:table-cell>
            </fo:table-row>
            <xsl:if test="@applicRefId or controlAuthorityRefs or @securityClassification or @commercialClassification or @caveat">
              <fo:table-row keep-together="always">
                <fo:table-cell number-columns-spanned="7" padding-bottom="4pt">
                  <fo:block></fo:block>
                </fo:table-cell>
              </fo:table-row>
            </xsl:if>
            <xsl:call-template name="cgmark_end"/>
          </xsl:for-each>
        </fo:table-body>
      </fo:table>
    </fo:block>
   </xsl:template>

</xsl:stylesheet>