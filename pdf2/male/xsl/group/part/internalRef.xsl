<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- <xsl:include href="./attribute/cgmark.xsl" /> -->
  <!-- 
    Tidak bisa kasi include karena ini xsl ini di call oleh para.xsl/figure.xsl, etc
   -->
  <xsl:param name="dmOwner"/>

  <xsl:template match="internalRef">
    <a>
      <xsl:call-template name="cgmark"/>
      <xsl:attribute name="href"><xsl:value-of select="$dmOwner"/>,<xsl:value-of select="@internalRefId"/></xsl:attribute>
      <xsl:call-template name="irtt"/>
    </a>
  </xsl:template>

  <xsl:template name="irtt">
    <xsl:choose>
      <!-- irtt01: Figure -->
      <xsl:when test="@internalRefTargetType = 'irtt01'">
        <xsl:text>Fig.&#160;</xsl:text>
        <xsl:call-template name="getPosition">
          <xsl:with-param name="xpath" select="//figure"/>
          <xsl:with-param name="idCompared" select="@internalRefId"/>
        </xsl:call-template>
        <xsl:apply-templates/>
      </xsl:when>
      <!-- irtt02: Table -->
      <xsl:when test="@internalRefTargetType = 'irtt02'">
        <xsl:text>Table.&#160;</xsl:text><xsl:call-template name="getPosition">
          <xsl:with-param name="xpath" select="//table"/>
          <xsl:with-param name="idCompared" select="@internalRefId"/>
        </xsl:call-template>
        <xsl:text>&#160;</xsl:text>
        <xsl:apply-templates/>
      </xsl:when>
      <!-- irtt03: MMa -->
      <xsl:when test="@internalRefTargetType = 'irtt03'">
        <xsl:text>Mma.&#160;</xsl:text>
        <xsl:call-template name="getPosition">
          <xsl:with-param name="xpath" select="//multimedia"/>
          <xsl:with-param name="idCompared" select="@internalRefId"/>
        </xsl:call-template>
        <xsl:text>&#160;</xsl:text>
        <xsl:apply-templates/> <!-- Text inside <internalRef> -->
      </xsl:when>
      <!-- irtt04: suply desc -->
      <xsl:when test="@internalRefTargetType = 'irtt04'">
        <xsl:choose>
          <xsl:when test="boolean(//supplyDesc[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//supplyDesc[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//supplyDesc[@id = @internalRefId]/name)">
            <xsl:apply-templates select="//supplyDesc[@id = @internalRefId]/name"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- irtt05: support eq -->
      <xsl:when test="@internalRefTargetType = 'irtt05'">
        <xsl:choose>
          <xsl:when test="boolean(//supportEquipDescr[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//supportEquipDescr[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//supportEquipDescr[@id = @internalRefId]/name)">
            <xsl:apply-templates select="//supportEquipDescr[@id = @internalRefId]/name"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- irtt:06: spare descr -->
      <xsl:when test="@internalRefTargetType = 'irtt06'">
        <xsl:choose>
          <xsl:when test="boolean(//spareDescr[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//spareDescr[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//spareDescr[@id = @internalRefId]/name)">
            <xsl:apply-templates select="//spareDescr[@id = @internalRefId]/name"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- irtt07: para -->
      <xsl:when test="@internalRefTargetType = 'irtt07'">
        <xsl:variable name="refId" select="@internalRefId"/>
        <xsl:text>&#160;</xsl:text>
        <xsl:value-of select="'para. '"/>
        
        <!-- prefix -->
        <xsl:value-of select="number(//identAndStatusSection/descendant::dmCode[1]/@assyCode)"/>
        <xsl:text>.</xsl:text>
        
        <xsl:call-template name="getPosition">
          <xsl:with-param name="xpath" select="//levelledPara"/>
          <xsl:with-param name="idCompared" select="$refId"/>
          <xsl:with-param name="includedParent" select="'yes'"/>
          <xsl:with-param name="parentName" select="'levelledPara'"/>
        </xsl:call-template>

        <xsl:if test="child::*">
          <xsl:copy>(<xsl:apply-templates/>)</xsl:copy>
        </xsl:if>
      </xsl:when>
      <!-- irtt08: step -->
      <xsl:when test="@internalRefTargetType = 'irtt08'">
        <xsl:text>Step.&#160;</xsl:text>
        <xsl:call-template name="getPosition">
          <xsl:with-param name="xpath" select="//proceduralStep"/>
        </xsl:call-template>
      </xsl:when>
      <!-- irtt09: graphic -->
      <xsl:when test="@internalRefTargetType = 'irtt09'">
        <xsl:variable name="internalRefId" select="@internalRefId"/>
        <xsl:value-of select="'Fig. '"/>
        <xsl:call-template name="getPositionGraphic">
          <!-- foreach figure; jika figure "ini" sama dengan figure "compared", maka... -->
          <xsl:with-param name="xpath" select="//graphic/.."/>
          <xsl:with-param name="compared" select="//graphic[@id = $internalRefId]/.."/>
        </xsl:call-template>
        <xsl:text>,&#160;</xsl:text>
        <xsl:apply-templates/>
      </xsl:when>
      <!-- irtt10: multimedia object -->
      <xsl:when test="@internalRefTargetType = 'irtt10'">
        <!-- Decide Prefix Name of multimedia (3D. X....) -->
        <xsl:call-template name="multimediaTypePrefix"/>
        <!-- get the position of multimediaObject -->
        <xsl:text>&#160;</xsl:text>
        <xsl:call-template name="getPosition">
          <xsl:with-param name="xpath" select="//multimediaObject"/>
          <xsl:with-param name="idCompared" select="@internalRefId"/>
        </xsl:call-template>
        <xsl:text>&#160;</xsl:text>
        <!-- Text inside <internalRef> -->
        <xsl:apply-templates/> 
      </xsl:when>
      <!-- irtt:11: zona -->
      <xsl:when test="@internalRefTargetType = 'irtt13'">        
        <xsl:choose>
          <xsl:when test="boolean(//zoneRef[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//zoneRef[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//zoneRef[@id = @internalRefId]/name)">
            <xsl:apply-templates select="//zoneRef[@id = @internalRefId]/name"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- irtt14: worklocation -->
      <xsl:when test="@internalRefTargetType = 'irtt14'">        
        <xsl:choose>
          <xsl:when test="boolean(//workLocation[@id = @internalRefId]/workArea)">
            <xsl:apply-templates select="//workLocation[@id = @internalRefId]/workArea"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- irtt15: sb -->
      <xsl:when test="@internalRefTargetType = 'irtt15'">        
        <xsl:choose>
          <xsl:when test="boolean(//sbmaterialSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbmaterialSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbSupportEquipSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbSupportEquipSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualSupportEquip[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbIndividualSupportEquip[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbExternalSupportEquipSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbExternalSupportEquipSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbSupplySet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbSupplySet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualSupply[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbIndividualSupply[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbExternalSupplySet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbExternalSupplySet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbSpareSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbSpareSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualSpare[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbIndividualSpare[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbExternalSpareSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbExternalSpareSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbRemovedSpareSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbRemovedSpareSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:when test="boolean(//sbIndividualRemovedSpareSet[@id = @internalRefId]/shortName)">
            <xsl:apply-templates select="//sbIndividualRemovedSpareSet[@id = @internalRefId]/shortName"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- irtt16: access point -->
      <xsl:when test="@internalRefTargetType = 'irtt16'">        
        <xsl:choose>
          <xsl:when test="boolean(//accessPointRef[@id = @internalRefId]/name)">
            <xsl:apply-templates select="//accessPointRef[@id = @internalRefId]/name"/>
          </xsl:when>
          <xsl:otherwise>
            <xsl:apply-templates/>
          </xsl:otherwise>
        </xsl:choose>
      </xsl:when>
      <!-- Other internalRefTargetType -->
      <xsl:otherwise>
        <xsl:apply-templates/>
      </xsl:otherwise>
    </xsl:choose>
      
  </xsl:template>

  <!-- <xsl:template name="internalRefTargetType">
    <xsl:param name="internalRefTargetType" select="@internalRefTargetType"/>
    <xsl:choose>
      <xsl:when test="$internalRefTargetType == 'irtt01'">
        <xsl:text>Fig.&#160;</xsl:text>
      </xsl:when>
    </xsl:choose>
  </xsl:template> -->

  <!-- <xsl:template name="getPosition">
    
  </xsl:template> -->
</xsl:stylesheet>